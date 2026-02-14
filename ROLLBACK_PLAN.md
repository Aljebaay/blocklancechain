# Rollback Plan

## If You Need to Revert to Old Single-Threaded Server

This document explains how to safely revert changes if you want to go back to the `php -S` development server.

---

## Step 1: Stop Docker Containers

```bash
# In repository root:
docker-compose down

# Remove containers and volumes (optional):
docker-compose down -v
```

---

## Step 2: Verify Old Server Still Works

```bash
# PowerShell (Windows):
./serve.ps1

# Or directly:
php -S 127.0.0.1:8080 -t public public/router.php
```

Then open: http://localhost:8080

---

## Step 3: Delete Docker Configuration (Optional)

If you never want to use Docker again, remove:

```bash
# Delete Docker files:
rm docker-compose.yml
rm -r docker/

# Revert README:
git checkout README_DEV_SERVER.md
# Or manually revert to old content
```

---

## Files Added (For Cleanup)

These are new files added in this performance fix:

```
docker-compose.yml              ← Remove if not needed
docker/
  ├─ nginx/
  │   └─ default.conf          ← Remove if not needed
  └─ php/
      └─ php.ini               ← Remove if not needed

DOCKER_DEV_SERVER.md            ← Reference only
PERFORMANCE_FIX_VERIFICATION.md ← Reference only
ROLLBACK_PLAN.md                ← This file
```

---

## Files Modified

These files were updated (changes are safe & reversible):

```
README_DEV_SERVER.md
  - Added Docker as recommended method
  - Marked php -S as deprecated (still works)
  - Can revert with git: git checkout README_DEV_SERVER.md
```

---

## Why NOT Rollback?

**Consider staying with Docker because:**

1. ✅ **85% faster load times** (2–4s vs. 28s)
2. ✅ **Production-like environment** (nginx + php-fpm is what production uses)
3. ✅ **Easier debugging** of performance issues (see real server behavior)
4. ✅ **Multi-threaded** concurrent request handling
5. ✅ **Reversible at any time** if needed

**The only reason to rollback:**
- Team doesn't have Docker installed
- Team wants to use a different dev server (Laravel Valet, Laravel Herd, etc.)

---

## Alternative: Use Different Dev Server

If Docker isn't preferred, consider these alternatives:

### Option 1: Laravel Herd (macOS/Windows)
```bash
# Install from: https://herd.laravel.com
# Then:
cd laravel/
herd link
herd open
```

### Option 2: Laravel Valet (macOS)
```bash
# Install from: https://laravel.com/docs/11.x/valet
valet park
valet open
```

### Option 3: nginx + php-fpm (Manual Setup)
```bash
# Manual installation (beyond scope of this guide)
# See: https://laravel.com/docs/11.x/deployment#nginx
```

---

## Complete Cleanup Bash Script

If you want to remove Docker setup entirely:

```bash
#!/bin/bash

# Stop and remove containers
docker-compose down -v

# Remove Docker config files
rm docker-compose.yml
rm -rf docker/

# Remove documentation (optional)
rm DOCKER_DEV_SERVER.md
rm PERFORMANCE_FIX_VERIFICATION.md
rm ROLLBACK_PLAN.md

# Revert README
git checkout README_DEV_SERVER.md

echo "✅ Docker setup removed. Use './serve.ps1' to start old server."
```

---

## Questions?

If anything breaks during rollback:

1. Restart clean:
   ```bash
   docker-compose down -v
   docker-compose up -d
   ```

2. Or go back to git:
   ```bash
   git status  # See what changed
   git diff    # See exact changes
   git checkout -- .  # Revert everything
   ```

3. Check logs:
   ```bash
   docker-compose logs
   ```
