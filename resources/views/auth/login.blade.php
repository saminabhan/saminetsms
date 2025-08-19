{{-- resources/views/auth/login.blade.php --}}
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>تسجيل الدخول - SamiNetSMS</title>

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

            background-image: url('assets/images/back.jpg');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
            background-attachment: fixed;

            min-height: 100vh;
            margin: 0;

            display: flex;
            align-items: center;
            justify-content: center;
        }


        .login-card {
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 25px rgba(0,0,0,0.2);
            padding: 2rem;
            width: 100%;
            max-width: 420px;
            text-align: center;
        }

        .login-card img {
            max-width: 150px;
            height: auto;
            margin-bottom: 1rem;
        }

        .login-card h1 {
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: #333;
        }

        .form-control {
            border-radius: 8px;
            padding: 0.75rem 1rem;
            margin-bottom: 1rem;
        }

        .btn-primary {
            border-radius: 8px;
            font-weight: 600;
            padding: 0.75rem;
            width: 100%;
        }

        .alert {
            border-radius: 10px;
            font-size: 0.9rem;
        }

        a {
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .btn-custom {
            background-color: #4064aa;
            color: #ffffff;
            border-radius: 8px;
            font-weight: 600;
            padding: 0.75rem;
            width: 100%;
        }

            .btn-custom:hover,
            .btn-custom:focus,
            .btn-custom:active,
            .btn-custom:focus-visible {
                background-color: #7c7c7c;
                color: #ffffff;
                box-shadow: none;
            }

    </style>
</head>
<body>
    <div class="login-card">
        <img src="{{ asset('assets/images/sami-logo.png') }}" alt="SamiNetSMS Logo" class="img-fluid mb-3" style="max-width: 280px; height: auto;">
        <h1>تسجيل الدخول</h1>

        {{-- عرض الأخطاء --}}
        @if(session('error'))
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-1"></i> {{ session('error') }}
            </div>
        @endif

       <form method="POST" action="{{ route('login') }}">
    @csrf

    <input type="email" name="email" class="form-control text-start" placeholder="البريد الإلكتروني" value="{{ old('email') }}" required>
    @error('email')
        <div class="text-danger mb-2 text-end">{{ $message }}</div>
    @enderror

    <input type="password" name="password" class="form-control text-start" placeholder="كلمة المرور" required>
    @error('password')
        <div class="text-danger mb-2 text-end">{{ $message }}</div>
    @enderror

   <button type="submit" class="btn btn-custom d-flex align-items-center justify-content-center" id="loginButton">
        <i class="fas fa-sign-in-alt me-1"></i> تسجيل الدخول
    </button>



</form>

        <!-- <div class="mt-3">
            <a href="{{ route('password.request') }}">نسيت كلمة المرور؟</a>
        </div> -->
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const button = document.getElementById('loginButton');

            form.addEventListener('submit', function() {
                    button.disabled = true;

                button.innerHTML = `
                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    جاري التحقق...
                `;
            });
        });
    </script>

</body>
</html>
