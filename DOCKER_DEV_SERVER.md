# Docker Dev Server Commands

## Start multi-threaded dev server (php-fpm + nginx)
```bash
docker-compose up -d
```

Then open: **http://localhost:8080**

## Stop the server
```bash
docker-compose down
```

## View logs
```bash
docker-compose logs -f app      # PHP-FPM logs
docker-compose logs -f nginx    # nginx logs
docker-compose logs -f db       # MySQL logs
```

## Shell access
```bash
docker-compose exec app sh
docker-compose exec nginx sh
```

## Run Laravel commands inside container
```bash
docker-compose exec app php artisan migrate
docker-compose exec app php artisan tinker
docker-compose exec app vendor/bin/phpunit
```

---

## Performance Improvements Expected

| Metric | Before (php -S) | After (Docker+nginx+php-fpm) |
|--------|-----------------|------------------------------|
| **Homepage Load Time** | ~29s | ~2–4s |
| **Asset Queueing** | 6–10s per asset | 0–200ms (parallel) |
| **Concurrent Requests** | 1 (serialized) | 10–50+ (parallel) |
| **Server Response** | Blocking single thread | Non-blocking multi-worker |

---

## Old Single-Threaded Method (DEPRECATED)

The old `php -S` development server is now deprecated. Do NOT use:
```bash
# ❌ OLD - Single-threaded, causes 6-10s asset queueing
php -S 127.0.0.1:8080 -t public public/router.php
./serve.ps1
./serve.bat
```

Use Docker instead:
```bash
# ✅ NEW - Multi-threaded, parallel asset serving
docker-compose up -d
```

---

## Troubleshooting

### Port 8080 already in use
```bash
# Change docker-compose.yml port mapping
# Change the first 8080 to a different port, e.g., 8081:80
```

### Containers won't start
```bash
docker-compose logs
# Check the error message and ensure Docker is running
```

### Cannot access MySQL from host
```bash
# MySQL is exposed on localhost:3306
mysql -h 127.0.0.1 -u root blocklancechain
```

### Need to rebuild images after dependency changes
```bash
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```
