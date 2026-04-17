<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Login - Satpam UNHAS</title>
    <link href="{{ asset('sb-admin-2/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <style>
        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Nunito', sans-serif;
        }

        .login-container {
            display: flex;
            height: 100vh;
            width: 100%;
        }

        .login-left {
            flex: 1;
            background-color: #9b1c1c; /* Unhas Red */
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            padding: 40px;
            text-align: center;
        }

        .login-right {
            flex: 1;
            background-color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
        }

        .logo-unhas {
            width: 150px;
            margin-bottom: 30px;
        }

        .left-title {
            font-weight: 800;
            font-size: 2.2rem;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .left-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .login-form-wrapper {
            width: 100%;
            max-width: 400px;
        }

        .welcome-title {
            font-weight: 800;
            color: #1e3a8a; /* Keeping a dark blue for contrast or match Unhas style */
            font-size: 2rem;
            margin-bottom: 5px;
        }

        .welcome-subtitle {
            color: #64748b;
            margin-bottom: 35px;
        }

        .form-label {
            font-weight: 700;
            font-size: 0.85rem;
            color: #1e293b;
            margin-bottom: 8px;
            display: block;
            text-transform: uppercase;
        }

        .input-group {
            position: relative;
            margin-bottom: 25px;
        }

        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }

        .form-control {
            width: 100%;
            padding: 15px 15px 15px 45px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            background-color: #eff6ff;
            font-size: 1rem;
            box-sizing: border-box;
            transition: 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: #9b1c1c;
            box-shadow: 0 0 0 3px rgba(155, 28, 28, 0.1);
        }

        .btn-submit {
            width: 100%;
            padding: 15px;
            border-radius: 10px;
            background: linear-gradient(90deg, #9b1c1c 0%, #7f1d1d 100%);
            color: white;
            border: none;
            font-weight: 800;
            font-size: 1rem;
            cursor: pointer;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 15px rgba(155, 28, 28, 0.3);
            transition: 0.3s;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(155, 28, 28, 0.4);
        }

        .alert-error {
            background-color: #fee2e2;
            color: #991b1b;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }

        @media (max-width: 900px) {
            .login-left {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <!-- Left Side (Red Background) -->
        <div class="login-left">
            <img src="{{ asset('img/logo_unhas.png') }}" alt="Logo UNHAS" class="logo-unhas">
            <div class="left-title">Sistem Presensi</div>
            <div class="left-subtitle">Satuan Pengamanan Universitas Hasanuddin</div>
        </div>

        <!-- Right Side (Form) -->
        <div class="login-right">
            <div class="login-form-wrapper">
                <h1 class="welcome-title">Selamat Datang</h1>
                <p class="welcome-subtitle">Silakan login untuk memulai sesi anda</p>

                @if ($errors->any())
                    <div class="alert-error">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="/login">
                    @csrf
                    <label class="form-label">NIP / Username</label>
                    <div class="input-group">
                        <i class="fas fa-user"></i>
                        <input type="text" name="nip" class="form-control" placeholder="198407032026013001" value="{{ old('nip') }}" required autofocus>
                    </div>

                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" class="form-control" placeholder="********" required>
                    </div>

                    <button type="submit" class="btn-submit">
                        Masuk Sekarang <i class="fas fa-chevron-right"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>