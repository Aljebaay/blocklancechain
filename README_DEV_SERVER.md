# Local server commands

## ✅ Recommended: Docker (Multi-threaded, Fast)

Multi-threaded PHP-FPM + nginx. Best for dev, closest to production. Fixes asset queueing (~29s → ~2-4s).

```bash
docker-compose up -d
# Open: http://localhost:8080
```

See [DOCKER_DEV_SERVER.md](DOCKER_DEV_SERVER.md) for full details.

---

## ⚠️ Legacy (Single-threaded, Slow)

These methods are **not recommended** but still work:

- `./serve.ps1` – Uses `php -S` (single-threaded, asset queueing issues)
- `./serve.ps1 -HostName 127.0.0.1 -Port 8080` – Custom port
- `php -S 127.0.0.1:8080 -t public public/router.php` – Direct PHP command

**Why avoid:** Single-threaded means concurrent asset requests queue, causing 6–10s delays per asset. Use Docker instead.

---

## Testing

- HTTP smoke checks: `composer smoke:http`
- Unit tests: `composer test`
- Test with Docker: `docker-compose exec app vendor/bin/phpunit`
