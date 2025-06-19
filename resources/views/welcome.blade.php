<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bus Booking System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .welcome-card {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 500px;
        }
        
        .welcome-card h1 {
            color: #667eea;
            margin-bottom: 1rem;
            font-weight: 600;
        }
        
        .welcome-card p {
            color: #6c757d;
            margin-bottom: 2rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 500;
            margin: 0 10px;
        }
        
        .btn-outline-primary {
            border: 2px solid #667eea;
            color: #667eea;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 500;
            margin: 0 10px;
        }
        
        .btn-outline-primary:hover {
            background: #667eea;
            color: white;
        }
        
        .feature-icon {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="welcome-card mx-auto">
            <i class="fas fa-bus feature-icon"></i>
            <h1>Bus Booking System</h1>
            <p>Welcome to our GPS-enabled Bus Booking and Tracking System. Track buses in real-time and manage your bookings efficiently.</p>
            
            <div class="d-flex justify-content-center flex-wrap">
                <a href="{{ route('login') }}" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
                <a href="{{ route('dashboard') }}" class="btn btn-outline-primary">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </div>
            
            <div class="row mt-4">
                <div class="col-md-4">
                    <i class="fas fa-map-marked-alt text-primary"></i>
                    <h6 class="mt-2">GPS Tracking</h6>
                    <small class="text-muted">Real-time bus location tracking</small>
                </div>
                <div class="col-md-4">
                    <i class="fas fa-ticket-alt text-primary"></i>
                    <h6 class="mt-2">Easy Booking</h6>
                    <small class="text-muted">Simple and fast booking process</small>
                </div>
                <div class="col-md-4">
                    <i class="fas fa-shield-alt text-primary"></i>
                    <h6 class="mt-2">Secure</h6>
                    <small class="text-muted">Safe and secure transactions</small>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>