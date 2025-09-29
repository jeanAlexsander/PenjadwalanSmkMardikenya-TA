<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login - Sistem Penjadwalan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body,
        html {
            height: 100%;
        }

        .login-container {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
        }

        .login-card {
            width: 100%;
            max-width: 400px;
            border: none;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            background: #ffffff;
        }

        .login-card .card-header {
            background-color: transparent;
            border-bottom: none;
            text-align: center;
            padding-top: 1.5rem;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="card login-card">
            <div class="card-header text-center">
                <h3 class="fw-bold">Login</h3>

                <!-- Tambahkan gambar logo di bawah judul -->
                <img src="{{ asset('images/logo.jpg') }}" alt="Logo" style="height: 170px; margin-top: 10px;">
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input
                            type="text"
                            name="username"
                            id="username"
                            class="form-control"
                            placeholder="Masukkan username"
                            required autofocus>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input
                            type="password"
                            name="password"
                            id="password"
                            class="form-control"
                            placeholder="Masukkan password"
                            required>
                    </div>

                    @if ($errors->any())
                    <div class="alert alert-danger">
                        {{ $errors->first() }}
                    </div>
                    @endif

                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <a href="{{ route('password.request') }}" class="text-decoration-none">Lupa Password?</a>

                        <button type="submit" class="btn btn-success text-white">
                            Masuk
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</body>

</html>