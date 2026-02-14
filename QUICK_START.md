## 📖 Quick Reference: Homepage Performance Fix

### 🚀 START HERE (3 commands)

```bash
cd d:\myProjects\gigtodo\blocklancechain

# 1. Start multi-threaded dev server
docker-compose up -d

# 2. Open in browser
start http://localhost:8080

# 3. Verify: DevTools → Network → Reload (Ctrl+Shift+R)
#           Should load in 2-4 seconds (not 28s)
```

---

### 📊 EXPECTED RESULTS

| Measurement | Old | New | Status |
|---|---|---|---|
| Load time | 28s | 2s | ✅ 85% faster |
| Asset "Queueing" | 6–10s | <200ms | ✅ 50× faster |
| Assets loading | Sequential | Parallel | ✅ Waterfall overlaps |

---

### 🛑 STOP & CLEANUP

```bash
# Stop server
docker-compose down

# Remove containers and volumes
docker-compose down -v

# Switch back to old php -S (still works)
./serve.ps1
```

---

### 🔧 COMMON COMMANDS

```bash
# View logs
docker-compose logs -f app

# Run Laravel command in container
docker-compose exec app php artisan migrate

# MySQL access (from host)
mysql -h 127.0.0.1 -u root blocklancechain

# Shell access
docker-compose exec app sh
```

---

### 📚 FULL GUIDES

- **Full Setup Guide:** [DOCKER_DEV_SERVER.md](DOCKER_DEV_SERVER.md)
- **Performance Verification:** [PERFORMANCE_FIX_VERIFICATION.md](PERFORMANCE_FIX_VERIFICATION.md)
- **Rollback/Revert:** [ROLLBACK_PLAN.md](ROLLBACK_PLAN.md)
- **Technical Deep Dive:** [PERFORMANCE_FIX_SUMMARY.md](PERFORMANCE_FIX_SUMMARY.md)

---

### ℹ️ WHY THIS WORKS

**Old problem:** `php -S` is single-threaded. 30 asset requests = 30 second queue.

**New solution:** nginx + php-fpm handles 50+ concurrent requests in parallel.
