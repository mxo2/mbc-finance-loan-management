<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MBC Finance - Loading</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
        }
        .container {
            text-align: center;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            font-weight: 300;
        }
        p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }
        .links {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn {
            padding: 12px 24px;
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 8px;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        .btn:hover {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
            transform: translateY(-2px);
        }
        .status {
            margin-top: 2rem;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🏦 MBC Finance</h1>
        <p>Welcome to our Financial Services Platform</p>
        
        <div class="links">
            <a href="/dashboard" class="btn">📊 Admin Dashboard</a>
            <a href="/loan" class="btn">💰 Loan Application</a>
            <a href="/register" class="btn">👤 Register</a>
            <a href="/login" class="btn">🔐 Login</a>
        </div>
        
        <div class="status">
            <strong>Status:</strong> Frontend is being updated. The main React application will be available shortly.<br>
            Use the links above to access specific services.
        </div>
    </div>
</body>
</html>