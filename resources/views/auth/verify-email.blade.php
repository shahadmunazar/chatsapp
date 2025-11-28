<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verify Email - Real-Time Chat</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            max-width: 550px;
            width: 100%;
        }
        .verify-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            text-align: center;
        }
        .icon {
            font-size: 64px;
            margin-bottom: 20px;
        }
        h1 {
            font-size: 28px;
            color: #333;
            margin-bottom: 15px;
        }
        p {
            color: #666;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 25px;
        }
        .email-highlight {
            font-weight: 600;
            color: #667eea;
        }
        .btn {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 15px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }
        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        .btn-secondary {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
        }
        .btn-secondary:hover {
            background: #f8f9fa;
        }
        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .divider {
            text-align: center;
            margin: 25px 0;
            color: #999;
            font-size: 14px;
        }
        .logout-link {
            margin-top: 20px;
        }
        .logout-link form {
            display: inline;
        }
        .logout-link button {
            background: none;
            border: none;
            color: #667eea;
            text-decoration: underline;
            cursor: pointer;
            font-size: 14px;
        }
        .info-box {
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
        }
        .info-box h3 {
            color: #004085;
            font-size: 18px;
            margin-bottom: 10px;
        }
        .info-box ul {
            list-style: none;
            padding: 0;
            color: #004085;
            font-size: 14px;
            text-align: left;
        }
        .info-box li {
            padding: 5px 0;
            padding-left: 25px;
            position: relative;
        }
        .info-box li:before {
            content: "✓";
            position: absolute;
            left: 0;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="verify-card">
            <div class="icon">📧</div>
            <h1>Verify Your Email Address</h1>
            
            @if (session('message'))
                <div class="alert alert-success">
                    {{ session('message') }}
                </div>
            @endif

            <p>
                Thanks for signing up! Before getting started, could you verify your email address 
                by clicking on the link we just emailed to 
                <span class="email-highlight">{{ auth()->user()->email }}</span>?
            </p>

            <div class="info-box">
                <h3>📋 What Happens Next?</h3>
                <ul>
                    <li>Check your inbox for our verification email</li>
                    <li>Click the verification link in the email</li>
                    <li>You'll be redirected back here automatically</li>
                    <li>Full access to all features unlocked!</li>
                </ul>
            </div>

            <p style="font-size: 14px; color: #999;">
                Didn't receive the email? Check your spam folder or request a new one below.
            </p>

            <form method="POST" action="{{ route('verification.resend') }}">
                @csrf
                <button type="submit" class="btn btn-primary" id="resendBtn">
                    🔄 Resend Verification Email
                </button>
            </form>

            <div class="divider">or</div>

            <a href="/" class="btn btn-secondary">
                📱 Browse Wall (Public)
            </a>

            <div class="logout-link">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">Logout</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Disable resend button temporarily after click
        document.querySelector('form').addEventListener('submit', function() {
            const btn = document.getElementById('resendBtn');
            btn.disabled = true;
            btn.textContent = 'Sending...';
            
            setTimeout(() => {
                btn.disabled = false;
                btn.textContent = '🔄 Resend Verification Email';
            }, 5000);
        });
    </script>
</body>
</html>

