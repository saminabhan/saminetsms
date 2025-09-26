<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'نظام إرسال الرسائل النصية - SamiNetSMS')</title>
    
    <!-- Bootstrap RTL CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts - Cairo -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}" type="image/x-icon">

    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .navbar {
            background: linear-gradient(90deg, #1f2a38 0%, #2e3b4e 100%);
            margin-right: 16.666667%; /* مساحة للسايد بار */
            transition: margin-right 0.3s;
        }

        .navbar.expanded {
            margin-right: 0;
        }

        .sidebar {
            position: fixed;
            top: 0;
            right: 0;
            width: 16.666667%;
            height: 100vh;
            background: #1f2a38;
            z-index: 1040;
            padding-top: 1rem;
            overflow-y: auto;
            transition: right 0.3s;
        }

        .sidebar.collapsed {
            right: -16.666667%;
        }

        .sidebar .logo-container {
            text-align: center;
            padding: 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 1rem;
        }

        .sidebar .nav-link {
            color: #cfd8dc !important;
            border-radius: 8px;
            margin-bottom: 5px;
            padding: 0.5rem 1rem;
            transition: all 0.3s;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: #455a64;
            color: #fff !important;
        }

        .sidebar .nav-link i {
            color: #90a4ae;
        }

        .sidebar-toggle {
            position: absolute;
            top: 8px;
            right: 212px;
            z-index: 1050;
            background-color: transparent;
            color: white;
            border: none;
            padding: 8px 12px;
            transition: right 0.3s;
        }

        .sidebar-toggle.collapsed {
            right: 10px;
        }

        .main-content {
            margin-right: 16.666667%;
            padding: 1.5rem!important;
            transition: margin-right 0.3s;
            min-height: calc(100vh - 40px);
        }

        .main-content.expanded {
            margin-right: 0;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .btn {
            border-radius: 8px;
            font-weight: 600;
        }

        .table {
            border-radius: 10px;
            overflow: hidden;
        }

        .alert {
            border-radius: 10px;
            border: none;
        }

        .stats-card {
            background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
            color: white;
        }

        .stats-card.success {
            background: linear-gradient(135deg, #00b894 0%, #00a085 100%);
        }

        .stats-card.warning {
            background: linear-gradient(135deg, #fdcb6e 0%, #e17055 100%);
        }

        .stats-card.danger {
            background: linear-gradient(135deg, #fd79a8 0%, #e84393 100%);
        }

        footer {
            background-color: transparent;
            color: #b0bec5;
            text-align: center;
            padding: 0.5rem 0;
            font-size: 0.75rem;
            margin-top: auto;
            margin-right: 16.666667%;
            transition: margin-right 0.3s;
        }

        footer.expanded {
            margin-right: 0;
        }

        footer i {
            display: none;
        }

        /* Responsive styles */
        @media (max-width: 991.98px) {
            .navbar {
                margin-right: 0;
            }
            
            .sidebar {
                right: -16.666667%;
            }
            
            .sidebar-toggle {
                right: 10px;
            }
            
            .main-content {
                margin-right: 0;
                padding: 80px 15px 20px;
            }
            
            footer {
                margin-right: 0;
            }
        }

        /* Custom scrollbar for sidebar */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: #1f2a38;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: #455a64;
            border-radius: 3px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: #546e7a;
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Sidebar Toggle Button -->
    <button class="sidebar-toggle" id="sidebarToggleBtn">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="logo-container">
            <a href="{{ route('dashboard') }}">
                <img src="{{ asset('assets/images/click-logo-white.png') }}" alt="SamiNetSMS Logo" class="img-fluid" style="max-width: 115px; height: auto;">
            </a>
        </div>
        
        <ul class="nav flex-column mb-auto">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" 
                   href="{{ route('dashboard') }}">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    لوحة التحكم
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('subscribers.*') ? 'active' : '' }}" 
                   href="{{ route('subscribers.index') }}">
                    <i class="fas fa-users me-2"></i>
                    المشتركين
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('messages.*') ? 'active' : '' }}" 
                   href="{{ route('messages.index') }}">
                    <i class="fas fa-envelope me-2"></i>
                    الرسائل
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('invoices.*') ? 'active' : '' }}" 
                   href="{{ route('invoices.index') }}">
                    <i class="fas fa-file-invoice me-2"></i>
                    الفواتير
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('services.*') ? 'active' : '' }}" 
                   href="{{ route('services.index') }}">
                    <i class="fas fa-cogs me-2"></i>
                    الخدمات
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('service-categories.*') ? 'active' : '' }}" 
                   href="{{ route('service-categories.index') }}">
                    <i class="fas fa-tags me-2"></i>
                    فئات الخدمات
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('finance.*') ? 'active' : '' }}" 
                   href="{{ route('finance.index') }}">
                    <i class="fas fa-coins me-2"></i>
                    المالية
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('finance.debtors') ? 'active' : '' }}" 
                   href="{{ route('finance.debtors') }}">
                    <i class="fas fa-user-minus me-2"></i>
                    المدينون
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('finance.balances') ? 'active' : '' }}" 
                   href="{{ route('finance.balances') }}">
                    <i class="fas fa-scale-balanced me-2"></i>
                    أرصدة المشتركين
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('expense-categories.*') ? 'active' : '' }}" 
                   href="{{ route('expense-categories.index') }}">
                    <i class="fas fa-list me-2"></i>
                    فئات المصروفات
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('partners.*') ? 'active' : '' }}" 
                   href="{{ route('partners.index') }}">
                    <i class="fas fa-people-group me-2"></i>
                    الشركاء
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('withdrawals.*') ? 'active' : '' }}" 
                   href="{{ route('withdrawals.index') }}">
                    <i class="fas fa-arrow-down-wide-short me-2"></i>
                    السحوبات
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('distributors.*') ? 'active' : '' }}" 
                   href="{{ route('distributors.index') }}">
                    <i class="fas fa-user-tie me-2"></i>
                    الموزعون
                </a>
            </li>
            
            <!-- التحليلات Dropdown -->
            @php
                $analyticsActive = request()->routeIs('analytics.*');
            @endphp
            
            <li class="nav-item">
                <a class="nav-link d-flex justify-content-between align-items-center {{ $analyticsActive ? '' : 'collapsed' }}"
                   data-bs-toggle="collapse" href="#analyticsMenu" role="button" 
                   aria-expanded="{{ $analyticsActive ? 'true' : 'false' }}" aria-controls="analyticsMenu">
                    <span><i class="fas fa-chart-line me-2"></i> التحليلات</span>
                    <i class="fas fa-chevron-down"></i>
                </a>
                <div class="collapse {{ $analyticsActive ? 'show' : '' }}" id="analyticsMenu">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('analytics.index') ? 'active' : '' }}" 
                               href="{{ route('analytics.index') }}">
                                الإحصائيات والتحليلات العامة
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('analytics.financial') ? 'active' : '' }}" 
                               href="{{ route('analytics.financial') }}">
                                التقارير المالية
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('analytics.sales') ? 'active' : '' }}" 
                               href="{{ route('analytics.sales') }}">
                                تقرير المبيعات والعملاء
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('analytics.distributors') ? 'active' : '' }}" 
                               href="{{ route('analytics.distributors') }}">
                                تقرير الموزعين والمخزون
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('analytics.services') ? 'active' : '' }}" 
                               href="{{ route('analytics.services') }}">
                                تقرير الخدمات والحملات
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
        </ul>
    </nav>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark" id="navbar">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="color: rgba(255,255,255,0.9) !important;">
                            <i class="fas fa-user me-1"></i> مرحباً بك يا {{ Auth::user()->name }}!
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li>
                                <a class="dropdown-item" href="{{ route('account.settings') }}">
                                    <i class="fas fa-cog me-2"></i> إعدادات الحساب
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="{{ route('sessions.index') }}">
                                    <i class="fas fa-cog me-2"></i> جلسات دخول النظام
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="{{ route('logout') }}"
                                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="fas fa-sign-out-alt me-2"></i> تسجيل الخروج
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main content -->
    <main class="main-content" id="mainContent">
        <!-- Alerts -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>يرجى تصحيح الأخطاء التالية:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer id="footer">
        <div class="container-fluid animate__animated animate__fadeInUp animate__delay-0.5s">
            <p class="mb-0">© 2025 جميع الحقوق محفوظة لشبكة كليك نت.</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Sidebar toggle script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
            const navbar = document.getElementById('navbar');
            const mainContent = document.getElementById('mainContent');
            const footer = document.getElementById('footer');
            
            // Check if sidebar should be collapsed on page load (for mobile)
            if (window.innerWidth <= 991.98) {
                sidebar.classList.add('collapsed');
                sidebarToggleBtn.classList.add('collapsed');
                navbar.classList.add('expanded');
                mainContent.classList.add('expanded');
                footer.classList.add('expanded');
            }
            
            // Toggle sidebar
            sidebarToggleBtn.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
                sidebarToggleBtn.classList.toggle('collapsed');
                navbar.classList.toggle('expanded');
                mainContent.classList.toggle('expanded');
                footer.classList.toggle('expanded');
            });
            
            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 991.98) {
                    sidebar.classList.remove('collapsed');
                    sidebarToggleBtn.classList.remove('collapsed');
                    navbar.classList.remove('expanded');
                    mainContent.classList.remove('expanded');
                    footer.classList.remove('expanded');
                } else {
                    sidebar.classList.add('collapsed');
                    sidebarToggleBtn.classList.add('collapsed');
                    navbar.classList.add('expanded');
                    mainContent.classList.add('expanded');
                    footer.classList.add('expanded');
                }
            });
        });
    </script>

    <!-- Animate.css CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    @yield('scripts')
    @stack('scripts')
</body>
</html>