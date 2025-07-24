<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Bus Booking') }} - Dashboard</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Dashboard Styles -->
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }
          .sidebar {
            width: 250px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 0;
            position: fixed;
            height: 100vh;
            overflow-y: hidden;
            z-index: 1000;
            display: flex;
            flex-direction: column;
        }        .sidebar h2 {
            text-align: center;
            margin-bottom: 15px;
            font-weight: 600;
            font-size: 1.2rem;
            flex-shrink: 0;
        }
        
        .sidebar .profile {
            text-align: center;
            margin-bottom: 15px;
            padding: 0 20px;
            flex-shrink: 0;
        }
        
        .sidebar .profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-bottom: 5px;
        }
        
        .sidebar .profile p {
            margin: 0;
            font-weight: 500;
            font-size: 0.85rem;
        }
        
        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
            flex: 1;
            overflow-x : hidden;
        }
        
        .sidebar ul li {
            margin: 0;
        }
          .sidebar ul li a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 10px 25px;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
            font-size: 0.85rem;
        }
        
        .sidebar ul li a:hover,
        .sidebar ul li a.active {
            background-color: rgba(255, 255, 255, 0.1);
            border-left: 3px solid white;
            transform: translateX(5px);
        }
        
        .sidebar ul li a i {
            margin-right: 10px;
            width: 20px;
        }
        
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
            min-height: 100vh;
        }
        
        .content-header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .content-body {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            
            .main-content {
                margin-left: 0;
            }
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        @include('layouts.sidebar')
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif -->

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    
    @stack('scripts')

    <script>
        // Setup AJAX CSRF token
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        
        // Highlight active menu item
        document.addEventListener('DOMContentLoaded', function() {
            const currentPath = window.location.pathname;
            const menuLinks = document.querySelectorAll('.sidebar ul li a');
            
            menuLinks.forEach(link => {
                if (link.getAttribute('href') === currentPath) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>