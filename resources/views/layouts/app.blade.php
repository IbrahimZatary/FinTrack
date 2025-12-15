<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>FinTrack - @yield('title')</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background:  #9361ee;
            font-family: 'Segoe UI', sans-serif;
        }
        
        .navbar-brand {
            font-weight: bold;
            color: #9361ee !important;
           
        }
        
        .card {
            border-radius: 10px;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        
        .btn-primary {
            background: #9361ee;
            border-color: #9361ee;
        }
        
        .btn-primary:hover {
            background: #9361ee;
            border-color: #9361ee;
        }
        
        .stat-card {
            border-left: 4px solid #9361ee;
        }
        
        .sidebar {
            background: #2d3748;
            min-height: 100vh;
            color: white;
        }
        
        .sidebar a {
            color: #9361ee;
            text-decoration: none;
            padding: 10px 15px;
            display: block;
            border-radius: 5px;
            margin: 5px 0;
        }
        
        .sidebar a:hover, .sidebar a.active {
            background: #4a5568;
            color: white;
        }
        
        .sidebar a i {
            width: 25px;
        }
        
        .category-color {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="/dashboard">
                <i class="fas fa-wallet me-2"></i>FinTrack
            </a>
            
            <div class="dropdown">
                <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-user me-1"></i> {{ Auth::user()->name }}
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="/dashboard"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}" id="logout-form">
                            @csrf
                            <a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fas fa-sign-out-alt me-2"></i> Logout
                            </a>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar d-none d-md-block">
                <div class="p-3">
                    <h5 class="mb-4">Menu</h5>
                    <a href="/dashboard" class="{{ request()->is('dashboard') ? 'active' : '' }}">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <a href="/expenses" class="{{ request()->is('expenses*') ? 'active' : '' }}">
                        <i class="fas fa-money-bill-wave"></i> Expenses
                    </a>
                    <a href="/categories" class="{{ request()->is('categories*') ? 'active' : '' }}">
                        <i class="fas fa-tags"></i> Categories
                    </a>
                    <a href="/budgets" class="{{ request()->is('budgets*') ? 'active' : '' }}">
                        <i class="fas fa-chart-pie"></i> Budgets
                    </a>
                    <a href="/analytics" class="{{ request()->is('analytics*') ? 'active' : '' }}">
                        <i class="fas fa-chart-line"></i> Analytics
                    </a>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-10">
                <div class="p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2>@yield('title')</h2>
                        @yield('header-buttons')
                    </div>
                    
                    @yield('content')
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        // Global utility functions
        function formatCurrency(amount) {
            return '$' + parseFloat(amount).toFixed(2);
        }
        
        function showAlert(type, message) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.querySelector('.container-fluid .col-md-10 .p-4').prepend(alertDiv);
            setTimeout(() => alertDiv.remove(), 5000);
        }
    </script>
    
    @stack('scripts')
</body>
</html>