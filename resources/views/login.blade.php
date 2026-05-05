<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>NAZEM — Log in</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{ asset('static/css/style.css') }}">

    <style>
        .auth-card{
            animation: fadeUp .45s ease;
        }

        @keyframes fadeUp{
            from{
                opacity:0;
                transform: translateY(14px);
            }
            to{
                opacity:1;
                transform: translateY(0);
            }
        }

        .alert-box{
            border-radius: 14px;
            padding: 12px 14px;
            margin-bottom: 14px;
            font-size: 14px;
            line-height: 1.5;
        }

        .alert-box--success{
            background: #ecfdf3;
            border: 1px solid #bbf7d0;
            color: #166534;
        }

        .alert-box--error{
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
        }

        .login-form{
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .role-switch{
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            background: #f7f8fc;
            border: 1px solid #dbe1ee;
            border-radius: 999px;
            padding: 6px;
            margin-bottom: 6px;
        }

        .role-option{
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            border-radius: 999px;
            padding: 14px 16px;
            cursor: pointer;
            transition: all .22s ease;
            color: #4b5b76;
            user-select: none;
            font-weight: 600;
        }

        .role-option:hover{
            background: rgba(31, 67, 109, 0.06);
        }

        .role-option.active{
            background: #1f436d;
            color: #fff;
            box-shadow: 0 8px 20px rgba(31, 67, 109, 0.18);
        }

        .role-option input{
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .role-option .dot{
            width: 16px;
            height: 16px;
            border-radius: 50%;
            border: 2px solid currentColor;
            display: inline-block;
            position: relative;
            flex-shrink: 0;
        }

        .role-option.active .dot::after{
            content: "";
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #fff;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .role-hint{
            font-size: 13px;
            color: #7b879b;
            margin-top: -4px;
            margin-bottom: 4px;
            text-align: center;
            min-height: 20px;
        }

        .field label{
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #24324a;
        }

        .input{
            transition: border-color .2s ease, box-shadow .2s ease, background-color .2s ease;
        }

        .input:focus{
            border-color: #1f436d !important;
            box-shadow: 0 0 0 4px rgba(31, 67, 109, 0.12);
            outline: none;
        }

        .input.is-invalid{
            border-color: #dc2626 !important;
            background: #fffafa;
            box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.08);
        }

        .field-error{
            color: #dc2626;
            font-size: 13px;
            margin-top: 7px;
            font-weight: 500;
        }

        .password-wrap{
            position: relative;
        }

        .input--password{
            padding-right: 130px;
        }

        .toggle-password{
            position: absolute;
            right: 118px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            color: #5f6f89;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            padding: 4px 6px;
        }

        .toggle-password:hover{
            color: #1f436d;
        }

        .forgot-inside{
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 13px;
            font-weight: 700;
            color: #1f436d;
            text-decoration: none;
        }

        .forgot-inside:hover{
            text-decoration: underline;
        }

        .login-btn{
            position: relative;
            transition: transform .15s ease, opacity .2s ease;
        }

        .login-btn:hover{
            transform: translateY(-1px);
        }

        .login-btn:disabled{
            opacity: .75;
            cursor: not-allowed;
            transform: none;
        }

        .btn-loader{
            width: 17px;
            height: 17px;
            border: 2px solid rgba(255,255,255,.35);
            border-top-color: #fff;
            border-radius: 50%;
            display: inline-block;
            animation: spin .7s linear infinite;
            vertical-align: middle;
            margin-right: 8px;
        }

        @keyframes spin{
            to { transform: rotate(360deg); }
        }

        .signup-helper{
            text-align: center;
            margin-top: 2px;
        }

        .signup-helper a{
            font-weight: 800;
        }

        .shake{
            animation: shake .28s ease-in-out 1;
        }

        @keyframes shake{
            0%,100%{ transform: translateX(0); }
            20%{ transform: translateX(-6px); }
            40%{ transform: translateX(6px); }
            60%{ transform: translateX(-4px); }
            80%{ transform: translateX(4px); }
        }

        @media (max-width: 640px){
            .input--password{
                padding-right: 110px;
            }

            .toggle-password{
                right: 100px;
                font-size: 12px;
            }

            .forgot-inside{
                right: 12px;
                font-size: 12px;
            }
        }
    </style>
</head>

<body class="auth">

<nav class="topnav">
    <div class="container">
        <a class="brand" href="{{ route('home') }}">NAZEM</a>

        <div class="nav-auth">
            <a class="chip chip--primary" href="{{ route('login') }}">Log in</a>
            <a class="chip" href="{{ route('signup') }}">Sign up</a>
        </div>
    </div>
</nav>

<main class="auth-wrap">
    <div class="auth-card {{ session('error') || $errors->any() ? 'shake' : '' }}">

        <div class="wordmark">
            <div class="wordmark__brand">NAZEM</div>
            <div class="wordmark__sub">INVENTORY MANAGEMENT SYSTEM</div>
        </div>

        @if (session('success'))
            <div class="alert-box alert-box--success" role="alert">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert-box alert-box--error" role="alert">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert-box alert-box--error" role="alert">
                Please review the highlighted fields and try again.
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="login-form" id="loginForm" novalidate>
            @csrf

            @php
                $selectedType = old('login_type', 'user');
            @endphp

            <div class="role-switch" id="roleSwitch">
                <label class="role-option {{ $selectedType === 'user' ? 'active' : '' }}">
                    <input type="radio" name="login_type" value="user" {{ $selectedType === 'user' ? 'checked' : '' }}>
                    <span class="dot"></span>
                    <span>User</span>
                </label>

                <label class="role-option {{ $selectedType === 'admin' ? 'active' : '' }}">
                    <input type="radio" name="login_type" value="admin" {{ $selectedType === 'admin' ? 'checked' : '' }}>
                    <span class="dot"></span>
                    <span>Admin</span>
                </label>
            </div>

            <p class="role-hint" id="roleHint">
                {{ $selectedType === 'admin' ? 'Admin access for inventory management and control.' : 'User access for dashboard, alerts, and profile.' }}
            </p>

            <div class="field">
                <label for="email">Email</label>
                <input
                    id="email"
                    class="input @error('email') is-invalid @enderror"
                    type="email"
                    name="email"
                    placeholder="Enter your email"
                    value="{{ old('email') }}"
                    autocomplete="email"
                    required
                    autofocus
                >

                @error('email')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="field">
                <label for="password">Password</label>

                <div class="password-wrap">
                    <input
                        id="password"
                        class="input input--password @error('password') is-invalid @enderror"
                        type="password"
                        name="password"
                        placeholder="Enter password"
                        autocomplete="current-password"
                        required
                    >

                    <button type="button" class="toggle-password" id="togglePassword" aria-label="Show password">
                        Show
                    </button>

                    <a class="forgot-inside" href="{{ route('password.request') }}">Forgot password?</a>
                </div>

                @error('password')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>

            <button class="btn btn--primary login-btn" type="submit" id="loginBtn">
                <span id="loginBtnText">LOG IN</span>
            </button>

            <p class="helper signup-helper">
                Don't have an account?
                <a href="{{ route('signup') }}">Sign up.</a>
            </p>
        </form>

    </div>
</main>

<script>
    const options = document.querySelectorAll('.role-option');
    const roleHint = document.getElementById('roleHint');
    const passwordInput = document.getElementById('password');
    const togglePassword = document.getElementById('togglePassword');
    const loginForm = document.getElementById('loginForm');
    const loginBtn = document.getElementById('loginBtn');
    const loginBtnText = document.getElementById('loginBtnText');
    const emailInput = document.getElementById('email');

    const roleMessages = {
        user: 'User access for dashboard, alerts, and profile.',
        admin: 'Admin access for inventory management and control.'
    };

    options.forEach(option => {
        option.addEventListener('click', () => {
            options.forEach(o => o.classList.remove('active'));
            option.classList.add('active');

            const input = option.querySelector('input');
            input.checked = true;

            roleHint.textContent = roleMessages[input.value] || '';
        });
    });

    togglePassword.addEventListener('click', () => {
        const isPassword = passwordInput.type === 'password';
        passwordInput.type = isPassword ? 'text' : 'password';
        togglePassword.textContent = isPassword ? 'Hide' : 'Show';
        togglePassword.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
    });

    loginForm.addEventListener('submit', () => {
        loginBtn.disabled = true;
        loginBtnText.innerHTML = '<span class="btn-loader"></span>Logging in...';
    });

    emailInput.addEventListener('blur', () => {
        const value = emailInput.value.trim();
        if (!value) return;

        const isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);

        if (isValid) {
            emailInput.classList.remove('is-invalid');
        }
    });
</script>

</body>
</html>