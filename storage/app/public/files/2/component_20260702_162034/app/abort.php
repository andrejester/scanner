<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abort Page</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            background: linear-gradient(to right, #f3f4f6, #e5e7eb);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            text-align: center;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .icon {
            font-size: 50px;
            color: #e53e3e;
        }
        h1 {
            color: #111827;
            margin-bottom: 10px;
        }
        p {
            color: #6b7280;
            margin-bottom: 20px;
        }
        .buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .retry {
            background: #3b82f6;
            color: white;
        }
        .cancel {
            background: #f3f4f6;
            color: #111827;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">⚠️</div>
        <h1>Unauthorized</h1>
        <p><?= $message ?></p>
    </div>
</body>
</html>
