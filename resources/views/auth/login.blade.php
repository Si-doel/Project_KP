<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Dashboard Monitoring Cabang</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --blue-900: #0b2a6b;
            --blue-700: #0b4aa8;
            --blue-600: #0a5bc4;
            --red: #e4002b;
            --yellow: #ffc20e;
            --ink: #14213d;
            --muted: #64748b;
            --line: #d8e0ee;
            --surface: #ffffff;
            --page: #eef3fb;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            background: var(--page);
            color: var(--ink);
        }

        .wrapper {
            display: grid;
            grid-template-columns: 1.1fr 1fr;
            min-height: 100vh;
        }

        /* ---------- Sisi brand ---------- */
        .brand {
            position: relative;
            overflow: hidden;
            background:
                radial-gradient(circle at 85% 8%, rgba(255, 255, 255, .12) 0, transparent 40%),
                linear-gradient(160deg, var(--blue-600), var(--blue-900));
            color: #fff;
            display: flex;
            flex-direction: column;
            padding: 48px 64px 0;
        }

        .brand::before {
            /* strip tiga warna khas minimarket */
            content: "";
            position: absolute;
            inset: 0 auto 0 0;
            width: 10px;
            background: linear-gradient(to bottom, var(--yellow) 0 33.3%, var(--red) 33.3% 66.6%, #fff 66.6% 100%);
        }

        .logo {
            display: flex;
            align-items: center;
            min-height: 96px;
        }

        .logo img {
            height: 96px;
            width: auto;
            max-width: 100%;
            object-fit: contain;
            filter: drop-shadow(0 2px 6px rgba(0, 0, 0, .25));
        }

        .logo .wordmark {
            display: none;
            font-weight: 800;
            font-size: 2.6rem;
            letter-spacing: -.03em;
            color: #fff;
        }

        .logo .wordmark span {
            color: var(--yellow);
        }

        .logo.no-img .wordmark {
            display: block;
        }

        .logo.no-img img {
            display: none;
        }

        .brand-copy {
            margin-top: 56px;
        }

        .brand h1 {
            font-size: clamp(2rem, 3.2vw, 2.9rem);
            line-height: 1.12;
            font-weight: 800;
            letter-spacing: -.025em;
        }

        .brand p {
            margin-top: 16px;
            color: #cfe0ff;
            max-width: 430px;
            line-height: 1.65;
        }

        .illustration {
            margin-top: auto;
            width: 100%;
            max-width: 560px;
            align-self: center;
            display: block;
        }

        .brand small {
            position: absolute;
            left: 64px;
            bottom: 20px;
            color: #9fbcf0;
            font-size: .8rem;
        }

        /* ---------- Sisi form ---------- */
        .panel {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px;
        }

        .card {
            width: 100%;
            max-width: 430px;
            background: var(--surface);
            border-radius: 16px;
            box-shadow: 0 20px 50px -20px rgba(11, 42, 107, .35);
            padding: 40px 36px 36px;
            border-top: 6px solid var(--blue-600);
        }

        .card-logo {
            display: none;
            margin-bottom: 20px;
            font-weight: 800;
            font-size: 1.3rem;
            color: var(--blue-700);
        }

        .card-logo span {
            color: var(--red);
        }

        .card h2 {
            font-size: 1.65rem;
            font-weight: 800;
            letter-spacing: -.02em;
        }

        .card .sub {
            margin: 6px 0 28px;
            font-size: .92rem;
            color: var(--muted);
            line-height: 1.5;
        }

        .field {
            margin-bottom: 18px;
        }

        .field label {
            display: block;
            font-size: .85rem;
            font-weight: 600;
            margin-bottom: 7px;
        }

        .input-wrap {
            position: relative;
        }

        .input-wrap svg.icon {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            stroke: #94a3b8;
            fill: none;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
            pointer-events: none;
        }

        .field input {
            display: block;
            width: 100%;
            padding: 12px 14px 12px 42px;
            border: 1.5px solid var(--line);
            border-radius: 10px;
            font: inherit;
            font-size: 1rem;
            background: #fff;
            color: var(--ink);
            outline: none;
            transition: border-color .15s, box-shadow .15s;
        }

        .field input:focus {
            border-color: var(--blue-600);
            box-shadow: 0 0 0 4px rgba(10, 91, 196, .15);
        }

        .field input.invalid {
            border-color: var(--red);
        }

        .toggle {
            position: absolute;
            right: 6px;
            top: 50%;
            transform: translateY(-50%);
            width: 38px;
            height: 38px;
            display: grid;
            place-items: center;
            background: transparent;
            border: 0;
            border-radius: 8px;
            cursor: pointer;
            color: #64748b;
        }

        .toggle:hover {
            background: #eef3fb;
        }

        .toggle:focus-visible {
            outline: 3px solid rgba(10, 91, 196, .35);
        }

        .toggle svg {
            width: 20px;
            height: 20px;
            stroke: currentColor;
            fill: none;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .field.has-toggle input {
            padding-right: 48px;
        }

        .error {
            color: var(--red);
            font-size: .85rem;
            margin-top: 6px;
        }

        .status {
            background: #ecfdf3;
            border: 1px solid #b7ebcb;
            color: #166534;
            font-size: .9rem;
            padding: 10px 12px;
            border-radius: 10px;
            margin-bottom: 18px;
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 9px;
            font-size: .9rem;
            color: #475569;
            margin: 4px 0 26px;
            cursor: pointer;
        }

        .remember input {
            width: 17px;
            height: 17px;
            accent-color: var(--blue-600);
        }

        .submit {
            width: 100%;
            padding: 13px;
            background: var(--blue-600);
            color: #fff;
            border: 0;
            border-radius: 10px;
            font: inherit;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            box-shadow: inset 0 -3px 0 var(--red);
            transition: background .15s;
        }

        .submit:hover {
            background: var(--blue-700);
        }

        .submit:focus-visible {
            outline: 3px solid rgba(10, 91, 196, .4);
            outline-offset: 2px;
        }

        .submit[disabled] {
            opacity: .75;
            cursor: progress;
        }

        .card-foot {
            margin-top: 22px;
            text-align: center;
            font-size: .8rem;
            color: var(--muted);
        }

        @media (max-width: 900px) {
            .wrapper {
                grid-template-columns: 1fr;
            }

            .brand {
                display: none;
            }

            .panel {
                background: linear-gradient(180deg, var(--blue-600) 0 220px, var(--page) 220px);
                align-items: flex-start;
                padding-top: 64px;
            }

            .card-logo {
                display: block;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            * {
                transition: none !important;
            }
        }
    </style>
</head>

<body>
    <div class="wrapper">

        <div class="brand">
            <!--
                LOGO: letakkan file logo resmi di public/images/logo-indomaret.png
                Jika file belum ada, otomatis tampil teks "indomaret" sebagai pengganti.
            -->
            <div class="logo" id="brandLogo">
                <img src="{{ asset('adminLTE/assets/img/Logo_Indomaret.png') }}" alt="Indomaret"
                    onerror="document.getElementById('brandLogo').classList.add('no-img')">
                <div class="wordmark">indo<span>maret</span></div>
            </div>

            <div class="brand-copy">
                <h1>Dashboard Monitoring<br>Harian Cabang</h1>
                <p>Pantau data operasional seluruh cabang dalam satu tampilan terpusat.</p>
            </div>

            <!-- Ilustrasi toko (SVG inline, tanpa file tambahan) -->
            <svg class="illustration" viewBox="0 0 560 330" xmlns="http://www.w3.org/2000/svg" role="img"
                aria-label="Ilustrasi toko minimarket">
                <ellipse cx="280" cy="312" rx="250" ry="10" fill="#06214f" opacity=".35" />
                <!-- bangunan -->
                <rect x="70" y="110" width="420" height="200" rx="6" fill="#f5f8ff" />
                <!-- papan nama -->
                <rect x="60" y="62" width="440" height="58" rx="6" fill="#0b4aa8" />
                <rect x="60" y="62" width="440" height="8" fill="#ffc20e" />
                <rect x="60" y="112" width="440" height="8" fill="#e4002b" />
                <text x="280" y="106" text-anchor="middle" font-family="Plus Jakarta Sans, system-ui, sans-serif"
                    font-weight="800" font-size="34" fill="#ffffff">indo<tspan fill="#ffc20e">maret</tspan></text>
                <!-- kanopi -->
                <path d="M70 124h420l-14 34H84z" fill="#e4002b" />
                <path d="M70 124h70l-8 34H84zM210 124h70l-4 34h-70zM350 124h70l4 34h-70z" fill="#ffffff" />
                <!-- jendela kiri -->
                <rect x="96" y="176" width="150" height="110" rx="4" fill="#cfe0ff" />
                <rect x="96" y="176" width="150" height="110" rx="4" fill="none" stroke="#0b4aa8"
                    stroke-width="4" />
                <rect x="108" y="226" width="126" height="6" fill="#0b4aa8" opacity=".35" />
                <rect x="108" y="262" width="126" height="6" fill="#0b4aa8" opacity=".35" />
                <rect x="112" y="204" width="14" height="22" rx="2" fill="#e4002b" />
                <rect x="130" y="210" width="14" height="16" rx="2" fill="#ffc20e" />
                <rect x="148" y="200" width="14" height="26" rx="2" fill="#0a5bc4" />
                <rect x="166" y="208" width="14" height="18" rx="2" fill="#e4002b" />
                <rect x="184" y="204" width="14" height="22" rx="2" fill="#ffc20e" />
                <rect x="202" y="210" width="14" height="16" rx="2" fill="#0a5bc4" />
                <rect x="114" y="240" width="18" height="22" rx="2" fill="#ffc20e" />
                <rect x="136" y="244" width="18" height="18" rx="2" fill="#e4002b" />
                <rect x="158" y="238" width="18" height="24" rx="2" fill="#0a5bc4" />
                <rect x="180" y="244" width="18" height="18" rx="2" fill="#ffc20e" />
                <rect x="202" y="240" width="18" height="22" rx="2" fill="#e4002b" />
                <!-- pintu -->
                <rect x="268" y="176" width="100" height="134" rx="4" fill="#cfe0ff" />
                <rect x="268" y="176" width="100" height="134" rx="4" fill="none" stroke="#0b4aa8"
                    stroke-width="4" />
                <line x1="318" y1="176" x2="318" y2="310" stroke="#0b4aa8"
                    stroke-width="4" />
                <rect x="306" y="236" width="6" height="30" rx="3" fill="#0b4aa8" />
                <rect x="324" y="236" width="6" height="30" rx="3" fill="#0b4aa8" />
                <!-- jendela kanan -->
                <rect x="390" y="176" width="80" height="110" rx="4" fill="#cfe0ff" />
                <rect x="390" y="176" width="80" height="110" rx="4" fill="none" stroke="#0b4aa8"
                    stroke-width="4" />
                <circle cx="430" cy="226" r="22" fill="#ffc20e" />
                <path d="M420 226l8 8 14-16" fill="none" stroke="#0b4aa8" stroke-width="5" stroke-linecap="round"
                    stroke-linejoin="round" />
                <!-- grafik kecil sebagai simbol monitoring -->
                <g transform="translate(440 6)">
                    <rect width="108" height="64" rx="10" fill="#ffffff" />
                    <rect x="14" y="34" width="12" height="18" rx="2" fill="#0a5bc4" />
                    <rect x="32" y="24" width="12" height="28" rx="2" fill="#0a5bc4" />
                    <rect x="50" y="30" width="12" height="22" rx="2" fill="#ffc20e" />
                    <rect x="68" y="14" width="12" height="38" rx="2" fill="#e4002b" />
                    <rect x="86" y="22" width="12" height="30" rx="2" fill="#0a5bc4" />
                </g>
                <!-- keranjang belanja -->
                <g transform="translate(14 232)">
                    <path d="M10 22h62l-8 44H22z" fill="#e4002b" />
                    <path d="M18 22c0-16 10-22 22-22s22 6 22 22" fill="none" stroke="#e4002b" stroke-width="5"
                        stroke-linecap="round" />
                    <rect x="22" y="34" width="38" height="5" rx="2" fill="#fff"
                        opacity=".7" />
                </g>
            </svg>

            <small>PT. Indomarco Prismatama</small>
        </div>

        <div class="panel">
            <div class="card">
                <div class="card-logo">indo<span>maret</span></div>

                <h2>Masuk</h2>
                <p class="sub">Gunakan akun yang diberikan oleh admin.</p>

                @if (session('status'))
                    <div class="status">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ route('login') }}" id="loginForm">
                    @csrf

                    <div class="field">
                        <label for="nik">NIK</label>
                        <div class="input-wrap">
                            <svg class="icon" viewBox="0 0 24 24" aria-hidden="true">
                                <circle cx="12" cy="8" r="4" />
                                <path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7" />
                            </svg>
                            <input id="nik" type="text" name="nik" value="{{ old('nik') }}"
                                class="@error('nik') invalid @enderror" inputmode="numeric" required autofocus
                                autocomplete="username" placeholder="Masukkan NIK">
                        </div>
                        @error('nik')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field has-toggle">
                        <label for="password">Password</label>
                        <div class="input-wrap">
                            <svg class="icon" viewBox="0 0 24 24" aria-hidden="true">
                                <rect x="4" y="11" width="16" height="10" rx="2" />
                                <path d="M8 11V8a4 4 0 0 1 8 0v3" />
                            </svg>
                            <input id="password" type="password" name="password"
                                class="@error('password') invalid @enderror" required autocomplete="current-password"
                                placeholder="Masukkan password">
                            <button type="button" class="toggle" id="togglePassword"
                                aria-label="Tampilkan password">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <label class="remember">
                        <input type="checkbox" name="remember">
                        <span>Ingat saya</span>
                    </label>

                    <button type="submit" class="submit" id="submitBtn">Masuk</button>
                </form>

                <p class="card-foot">Lupa password? Hubungi admin cabang Anda.</p>
            </div>
        </div>
    </div>

    <script>
        (function() {
            var pwd = document.getElementById('password');
            var toggle = document.getElementById('togglePassword');
            toggle.addEventListener('click', function() {
                var show = pwd.type === 'password';
                pwd.type = show ? 'text' : 'password';
                toggle.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
            });

            var form = document.getElementById('loginForm');
            var btn = document.getElementById('submitBtn');
            form.addEventListener('submit', function() {
                btn.disabled = true;
                btn.textContent = 'Memproses...';
            });
        })();
    </script>
</body>

</html>
