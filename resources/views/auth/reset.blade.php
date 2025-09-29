<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CDN (sementara, jika belum terpasang via Vite) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container d-flex align-items-center justify-content-center" style="height: 100vh;">
        <div class="card shadow p-4" style="width: 100%; max-width: 400px;">
            <h4 class="text-center mb-3">Reset Password</h4>

            @if (session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.request') }}">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label">Masukkan Email Anda</label>
                    <input type="email" name="email" id="email" class="form-control" required autofocus>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="d-flex justify-content-between align-items-center">
                    <a href="{{ route('login') }}" class="text-decoration-none">← Kembali</a>
                    <button type="submit" class="btn btn-success text-white">
                        Kirim Link
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
