param(
  [string]$HostName = '127.0.0.1',
  [int]$Port = 8080
)

$ErrorActionPreference = 'Stop'
$address = "$HostName`:$Port"
Write-Host "Starting PHP dev server at http://$address"
php -S $address -t public public/router.php
