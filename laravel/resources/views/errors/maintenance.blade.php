<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Mode - {{ $settings->site_name ?? 'GigZone' }}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; background: #f5f5f5; color: #333; }
        .container { text-align: center; padding: 2rem; }
        h1 { font-size: 2rem; margin-bottom: 0.5rem; }
        p { font-size: 1.1rem; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <h1>We'll be back soon</h1>
        <p>{{ $settings->site_name ?? 'The site' }} is currently undergoing maintenance. Please check back shortly.</p>
    </div>
</body>
</html>
