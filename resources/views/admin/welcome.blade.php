<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Welcome Admin</title>

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">

    <!-- Vite for GLOBAL MANDATORY css and js-->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Font Awesome for icons --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .welcome-container {
            text-align: center;
            background: white;
            padding: 60px 40px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 100%;
        }
        
        .welcome-title {
            color: #dc3545;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 20px;
        }
        
        .welcome-message {
            color: #333;
            font-size: 1.2rem;
            margin-bottom: 30px;
        }
        
        .admin-info {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .admin-username {
            font-size: 1.1rem;
            color: #495057;
            margin: 0;
        }
        
        .logout-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s ease;
        }
        
        .logout-btn:hover {
            background-color: #c82333;
            color: white;
        }
    </style>
</head>
<body>
    <div class="welcome-container">
        <div class="welcome-icon">
            <i class="fas fa-user-shield" style="font-size: 4rem; color: #dc3545; margin-bottom: 20px;"></i>
        </div>
        
        <h1 class="welcome-title">Welcome Admin!</h1>
        
        <p class="welcome-message">
            You have successfully logged into the I-TRAC Admin System.
        </p>
        
        <div class="admin-info">
            <p class="admin-username">
                <strong>Logged in as:</strong> {{ session('admin_username') }}
            </p>
        </div>
        
        <a href="{{ route('admin.show.login') }}" class="logout-btn" onclick="session.clear();">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</body>
</html>
