<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Dashboard Monitoring Cabang</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            background: #f3f4f6;
            color: #1f2937;
        }

        .wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr;
            min-height: 100vh;
        }

        .brand {
            background: linear-gradient(135deg, #1d4ed8, #1e3a8a);
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 0 64px;
        }

        .brand h1 {
            font-size: 2.25rem;
            line-height: 1.2;
        }

        .brand p {
            margin-top: 16px;
            color: #dbeafe;
            max-width: 420px;
            line-height: 1.6;
        }

        .brand small {
            margin-top: 48px;
            color: #bfdbfe;
        }

        .panel {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px;
        }

        .card {
            width: 100%;
            max-width: 420px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, .1);
            padding: 32px;
        }

        .card h2 {
            font-size: 1.5rem;
        }

        .card .sub {
            margin: 4px 0 24px;
            font-size: .9rem;
            color: #6b7280;
        }

        .field {
            margin-bottom: 16px;
        }

        .field label {
            display: block;
            font-size: .875rem;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .field input {
            display: block;
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 1rem;
            outline: none;
        }

        .field input:focus {
            border-color: #1d4ed8;
            box-shadow: 0 0 0 3px rgba(29, 78, 216, .15);
        }

        .error {
            color: #dc2626;
            font-size: .85rem;
            margin-top: 6px;
        }

        .status {
            color: #16a34a;
            font-size: .9rem;
            margin-bottom: 16px;
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: .9rem;
            color: #4b5563;
            margin-bottom: 24px;
        }

        .remember input {
            width: 16px;
            height: 16px;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #1d4ed8;
            color: #fff;
            border: 0;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
        }

        button:hover {
            background: #1e40af;
        }

        @media (max-width: 900px) {
            .wrapper {
                grid-template-columns: 1fr;
            }

            .brand {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="wrapper">

        <div class="brand">
            <h1>Dashboard Monitoring<br>Harian Cabang</h1>
            <p>Pantau data operasional seluruh cabang dalam satu tampilan terpusat.</p>
            <small>PT. Indomarco Prismatama</small>
        </div>

        <div class="panel">
            <div class="card">
                <h2>Masuk</h2>
                <p class="sub">Gunakan akun yang diberikan oleh admin.</p>

                @if (session('status'))
                    <div class="status">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="field">
                        <label for="nik">NIK</label>
                        <input id="nik" type="text" name="nik" value="{{ old('nik') }}"
                            inputmode="numeric" required autofocus autocomplete="username">
                        @error('nik')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="password">Password</label>
                        <input id="password" type="password" name="password" required autocomplete="current-password">
                        @error('password')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <label class="remember">
                        <input type="checkbox" name="remember">
                        <span>Ingat saya</span>
                    </label>

                    <button type="submit">Masuk</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>
