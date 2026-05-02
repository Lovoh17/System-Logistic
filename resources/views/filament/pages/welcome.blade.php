<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TraceLog - Sistema de Trazabilidad</title>
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            padding: 20px;
        }
        .container {
            text-align: center;
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            max-width: 600px;
        }
        h1 {
            color: #333;
            font-size: 2.5rem;
            margin-bottom: 20px;
        }
        .logo {
            font-size: 4rem;
            margin-bottom: 20px;
        }
        .admin-link {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 30px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            transition: background 0.3s;
        }
        .admin-link:hover {
            background: #764ba2;
        }
        .version {
            margin-top: 30px;
            color: #666;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">📦</div>
        <h1>TraceLog</h1>
        <p>Sistema de Trazabilidad Logística</p>
        <p>Bienvenido al sistema de gestión de trazabilidad</p>
        <a href="/admin" class="admin-link">🔐 Acceder al Panel Admin</a>
        <div class="version">
            Laravel {{ Illuminate\Foundation\Application::VERSION }} | PHP {{ PHP_VERSION }}
        </div>
    </div>
</body>
</html>
