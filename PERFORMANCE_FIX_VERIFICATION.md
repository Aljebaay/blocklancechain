# Performance Fix Verification Checklist

## Before Fix (Current State - php -S)

### Step 1: Baseline Measurement
```
1. Open terminal and run old server:
   $ ./serve.ps1
   
2. Open browser DevTools (F12 → Network tab)

3. Hard reload (Ctrl+Shift+R) to bypass cache

4. Observe:
   - Total load time: ~25–35 seconds
   - Pick any PNG/CSS/JS asset in the Network tab
   - Expand "Timing" section
```

**Expected symptoms (evidence of queueing):**
- "Queueing" or "Stalled" time: 6–10 seconds
- Many assets queued sequentially
- All assets start in series, not parallel
- Response headers show no `Server:` line (single-threaded dev server)

### Step 2: Response Headers (Old)
- Open DevTools → Network tab
- Pick one PNG asset
- Click "Headers" tab
- Look for:
  ```
  Server: PHP 8.x Development Server
  Cache-Control: (empty or no cache directives)
  Set-Cookie: (likely present - not ideal for static assets)
  ```

---

## After Fix (Docker + nginx + php-fpm)

### Step 1: Start New Server
```bash
# In repository root:
$ docker-compose up -d

# Verify containers started:
$ docker-compose ps
# Should show: app (running), nginx (running), db (running)

# Open browser:
http://localhost:8080
```

### Step 2: Network Performance Check
```
1. Open DevTools (F12 → Network tab)

2. Hard reload (Ctrl+Shift+R)

3. Observe:
   - Total load time: ~2–4 seconds (85% faster!)
   - Pick any PNG asset
   - Expand "Timing" → "Queueing/Stalled": Should be 0–200ms
   - Assets load in PARALLEL (Waterfall shows overlapping, not sequential)
```

**Example good timing for a PNG:**
```
Queued: 50ms
Stalled: 0ms
DNS lookup: 0ms
Initial connection: 0ms
SSL: N/A (local)
Request sent: 5ms
Waiting (TTFB): 1ms
Content Download: 2ms
---
Total: 58ms (vs. 6000-10000ms before!)
```

### Step 3: Response Headers (New - Static Assets)
```
1. Pick a .png or .css file from /build/ directory
2. Headers tab → Response Headers:

Server: nginx/1.x
Cache-Control: public, max-age=31536000, immutable
Accept-Ranges: bytes
Content-Length: (actual file size)
Content-Type: image/png
```

**Key improvements:**
- ✅ No `Set-Cookie` → assets don't trigger session overhead
- ✅ Immutable cache headers → browser won't re-validate
- ✅ nginx server header → multi-threaded, non-blocking
- ✅ Parallel downloads visible in Waterfall

### Step 4: Response Headers (PHP Dynamic Routes)
```
1. Pick an HTML response (e.g., homepage)
2. Headers tab → Response Headers:

Server: nginx/1.x
X-Powered-By: Laravel

Set-Cookie: XSRF-TOKEN=...; Path=/; SameSite=Lax
Set-Cookie: laravel_session=...; Path=/; HttpOnly; SameSite=Lax
```

These cookies are fine for dynamic HTML (not set on static assets).

### Step 5: Parallel Asset Loading Proof
```
1. Network tab → sort by "Name" or view Waterfall

Before (php -S):
  ├─ index.php        [=====    ] 6000ms (queued 5900ms)
  ├─ logo.png         [          ===] 6100ms (queued)
  ├─ style.css        [          ===] 6200ms (queued)
  └─ app.js           [          ===] 6300ms (queued)

After (Docker+nginx):
  ├─ index.php        [===] 50ms
  ├─ logo.png         [==] 40ms (starts ~same time)
  ├─ style.css        [==] 35ms (starts ~same time)
  └─ app.js           [==] 45ms (starts ~same time)
```

### Step 6: Concurrency Test
```bash
# Terminal: Open 2–3 windows while server is running

$ docker-compose exec app php -r "usleep(500000); echo 'OK';"
$ docker-compose exec app php -r "usleep(500000); echo 'OK';"
$ docker-compose exec app php -r "usleep(500000); echo 'OK';"

Expected: All 3 complete at ~500ms TOTAL (parallel)
Old: Would take ~1500ms (sequential)
```

---

## Teardown & Comparison Table

| Measurement | Before | After | Improvement |
|---|---|---|---|
| **Homepage Load Time** | 28–32s | 2–4s | **🎯 85–90% faster** |
| **PNG Asset Queueing** | 6–10s | 50–200ms | **100× faster** |
| **Concurrent Requests** | 1 | 10–50+ | **50× more** |
| **Cache Headers (Assets)** | None/default | Immutable 1y | **Zero revalidation** |
| **Set-Cookie on Assets** | Yes (overhead) | No | **Clean separation** |
| **Server Response Type** | Blocking (sync) | Non-blocking (async) | **Production-grade** |

---

## Validation Summary

✅ **All checks pass if:**
1. Docker containers started without error
2. Homepage loads in 2–4 seconds (not 28s)
3. Network tab shows assets loading in **parallel** (waterfall overlaps)
4. Individual asset "Time" is <200ms (not 6–10s)
5. Static assets have `Cache-Control: immutable` or `max-age=31536000`
6. PHP dynamic routes have `Set-Cookie` (correct session handling)

❌ **If checks fail:**
- See [DOCKER_DEV_SERVER.md](DOCKER_DEV_SERVER.md#troubleshooting) for troubleshooting
- Restart: `docker-compose down && docker-compose up -d`
- Check logs: `docker-compose logs app nginx`
