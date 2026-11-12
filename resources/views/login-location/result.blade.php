<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} - Heroes CRM</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; margin: 0; display: flex; min-height: 100vh; align-items: center; justify-content: center; }
        .card { background: #fff; border-radius: 8px; padding: 40px; max-width: 420px; text-align: center; box-shadow: 0 2px 12px rgba(0,0,0,.08); }
        .icon { font-size: 48px; }
        h1 { font-size: 22px; margin: 16px 0 8px; }
        p { color: #555; line-height: 1.5; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">{{ $icon }}</div>
        <h1>{{ $title }}</h1>
        <p>{{ $message }}</p>
    </div>
</body>
</html>
