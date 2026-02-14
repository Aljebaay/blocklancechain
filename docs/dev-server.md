# Local server commands

- Composer: `composer serve`
- PowerShell: `./serve.ps1`
- Custom host/port: `./serve.ps1 -HostName 127.0.0.1 -Port 8080`

Important: running `php -S 127.0.0.1:8080` without `-t public public/router.php` will return 404 after the new structure.
