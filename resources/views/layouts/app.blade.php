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

        .navbar-brand {
            font-weight: 700;
            color: #2c3e50 !important;
        }

        .sidebar {
    min-height: calc(100vh - 56px);
    background: #1f2a38; /* أزرق داكن/رمادي أنيق */
    padding-top: 1rem;
}

.sidebar .nav-link {
    color: #cfd8dc !important; /* أبيض رمادي فاتح للنصوص */
    border-radius: 8px;
    margin-bottom: 5px;
    padding: 0.5rem 1rem;
    transition: all 0.3s;
}

.sidebar .nav-link:hover,
.sidebar .nav-link.active {
    background-color: #455a64; /* تباين هادئ عند التحديد أو المرور */
    color: #fff !important;
}

.sidebar .nav-link i {
    color: #90a4ae; /* أيقونات رمادية هادئة */
}

.navbar {
    background: linear-gradient(90deg, #1f2a38 0%, #2e3b4e 100%);
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

        /* Sidebar responsive */
        @media (max-width: 991.98px) {
            .sidebar {
                position: fixed;
                top: 56px;
                right: -250px; /* البداية خارج الشاشة على اليمين */
                width: 250px;
                height: calc(100% - 56px);
                z-index: 1030;
                transition: right 0.3s; /* نطبق الانتقال على right فقط */
            }
            .sidebar.show {
                right: 0; /* تظهر على اليمين */
            }
            main.col-md-9, main.col-lg-10 {
                padding-left: 1rem;
                padding-right: 1rem;
            }
        }
footer {
    background-color: #1f2a38; /* نفس لون sidebar لكن أغمق شوي */
    color: #b0bec5; /* نص رمادي فاتح هادي */
    text-align: center;
    padding: 0.5rem 0;
    font-size: 0.75rem; /* أصغر شوي */
    margin-top: auto;
    border-top: 1px solid #2c3e50; /* خط رقيق يحدد الفوتر */
}

footer i {
    display: none; /* إزالة أيقونات غير ضرورية */
}
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark" >
        <div class="container-fluid">
            <button class="btn btn-light d-lg-none me-2" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
         <a class="navbar-brand d-flex align-items-center" href="{{ route('dashboard') }}">
            <i class="fas fa-sms me-2" style="color: rgba(255,255,255,0.9) !important; font-size: 1.5rem;"></i>
            <img src="{{ asset('assets/images/logo.png') }}" alt="SamiNetSMS Logo" class="img-fluid" style="max-width: 120px; height: auto;">
        </a>


</ul>


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

    <div class="container-fluid flex-grow-1">
        <div class="row">
            <!-- Sidebar -->
           <nav class="col-md-3 col-lg-2 sidebar d-flex flex-column p-3">
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
    </ul>

    <div class="mt-auto text-center" style="font-size: 0.75rem; color: #b0bec5;">
      <i class="fas fa-copyright"></i> جميع الحقوق محفوظة
    </div>
</nav>


            <!-- Main content -->
            <main class="col-md-9 col-lg-10 p-4">
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
        </div>
    </div>
   

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Sidebar toggle script -->
    <script>
        const sidebar = document.querySelector('.sidebar');
        const toggleBtn = document.getElementById('sidebarToggle');

        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('show');
        });
    </script>
<!-- Animate.css CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<!-- أضف هذا السطر قبل </body> أو في قسم السكريبتات -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @yield('scripts')
</body>
</html>
