<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ConfigVault — Sign In</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=JetBrains+Mono:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #0f4c75; --primary-light: #1b6ca8;
            --accent: #00b4d8; --accent-glow: rgba(0,180,216,0.18);
            --bg: #070d1a; --bg-2: #0d1626; --surface: #152035;
            --border: #1e3050; --text: #cdd9ec; --text-muted: #6b82a4;
        }
        body {
            font-family: 'Syne', sans-serif;
            background: var(--bg); color: var(--text);
            min-height: 100vh; display: flex;
            background-image:
                radial-gradient(ellipse 60% 60% at 20% 50%, rgba(15,76,117,0.25) 0%, transparent 60%),
                radial-gradient(ellipse 40% 40% at 80% 50%, rgba(0,180,216,0.08) 0%, transparent 60%);
        }
        .login-split { display: flex; width: 100%; min-height: 100vh; }

        /* Left panel */
        .login-left {
            flex: 1; display: flex; flex-direction: column;
            justify-content: center; padding: 60px;
            background: linear-gradient(135deg, #0a1628 0%, #0f2040 50%, #091525 100%);
            border-right: 1px solid var(--border); position: relative; overflow: hidden;
        }
        .login-left::before {
            content: ''; position: absolute; inset: 0;
            background-image: repeating-linear-gradient(0deg, transparent, transparent 49px, rgba(0,180,216,0.04) 50px),
                              repeating-linear-gradient(90deg, transparent, transparent 49px, rgba(0,180,216,0.04) 50px);
        }
        .login-left-content { position: relative; z-index: 1; }
        .brand-mark {
            display: flex; align-items: center; gap: 14px; margin-bottom: 48px;
        }
        .brand-icon {
            width: 52px; height: 52px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            border-radius: 14px; display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem; color: #fff;
            box-shadow: 0 0 30px var(--accent-glow);
        }
        .brand-name { font-size: 1.6rem; font-weight: 800; color: #fff; letter-spacing: -0.03em; }
        .brand-tagline { font-size: 0.65rem; color: var(--accent); letter-spacing: 0.15em; text-transform: uppercase; font-family: 'JetBrains Mono', monospace; }

        .login-left h2 { font-size: 2.4rem; font-weight: 800; color: #fff; line-height: 1.15; letter-spacing: -0.03em; margin-bottom: 16px; }
        .login-left h2 span { color: var(--accent); }
        .login-left p { font-size: 0.9rem; color: var(--text-muted); line-height: 1.7; max-width: 380px; margin-bottom: 36px; }

        .feature-list { list-style: none; padding: 0; }
        .feature-list li {
            display: flex; align-items: flex-start; gap: 12px;
            padding: 10px 0; border-top: 1px solid var(--border); font-size: 0.82rem; color: var(--text-muted);
        }
        .feature-list li:first-child { border-top: none; }
        .feature-list li i { color: var(--accent); margin-top: 2px; flex-shrink: 0; }
        .feature-list li strong { color: var(--text); }

        /* Right panel */
        .login-right {
            width: 440px; display: flex; align-items: center;
            justify-content: center; padding: 40px;
        }
        .login-form-wrap { width: 100%; max-width: 360px; }
        .login-form-wrap h3 { font-size: 1.5rem; font-weight: 700; color: #fff; margin-bottom: 6px; }
        .login-form-wrap p { font-size: 0.82rem; color: var(--text-muted); margin-bottom: 28px; }

        .form-control {
            background: var(--surface); border-color: var(--border);
            color: var(--text); border-radius: 10px; padding: 11px 14px;
            font-family: 'Syne', sans-serif; font-size: 0.88rem;
        }
        .form-control:focus {
            background: var(--surface); border-color: var(--accent);
            color: var(--text); box-shadow: 0 0 0 3px var(--accent-glow);
        }
        .form-control::placeholder { color: var(--text-muted); }
        .form-label { font-size: 0.75rem; font-weight: 600; color: var(--text-muted); letter-spacing: 0.05em; margin-bottom: 7px; }
        .input-group-text {
            background: var(--surface); border-color: var(--border); color: var(--text-muted);
            border-radius: 10px 0 0 10px;
        }

        .btn-login {
            background: linear-gradient(135deg, var(--primary-light), var(--accent));
            border: none; border-radius: 10px; padding: 12px;
            font-family: 'Syne', sans-serif; font-weight: 700; font-size: 0.9rem;
            color: #fff; width: 100%; margin-top: 8px;
            transition: opacity 0.2s, transform 0.2s;
        }
        .btn-login:hover { opacity: 0.9; transform: translateY(-1px); color: #fff; }

        .demo-credentials {
            background: rgba(0,180,216,0.06); border: 1px solid rgba(0,180,216,0.2);
            border-radius: 10px; padding: 14px; margin-top: 20px;
        }
        .demo-credentials h6 { font-size: 0.7rem; letter-spacing: 0.12em; text-transform: uppercase; color: var(--accent); margin-bottom: 10px; font-family: 'JetBrains Mono', monospace; }
        .demo-row { display: flex; justify-content: space-between; font-size: 0.72rem; font-family: 'JetBrains Mono', monospace; color: var(--text-muted); padding: 2px 0; }
        .demo-row strong { color: var(--text); }

        .alert { border-radius: 10px; border: none; font-size: 0.82rem; background: rgba(230,57,70,0.15); color: #ff6b7a; border: 1px solid rgba(230,57,70,0.3); }
        ::-webkit-scrollbar { width: 4px; } ::-webkit-scrollbar-thumb { background: var(--border); }
    </style>
</head>
<body>
<div class="login-split">
    <!-- LEFT -->
    <div class="login-left">
        <div class="login-left-content">
            <div class="brand-mark">
                <div class="brand-icon"><i class="bi bi-shield-lock-fill"></i></div>
                <div>
                    <div class="brand-name">ConfigVault</div>
                    <div class="brand-tagline">Configuration Management System</div>
                </div>
            </div>
            <h2>Secure.<br>Controlled.<br><span>Audited.</span></h2>
            <p>A complete configuration management platform with version control, role-based access, multi-stage approval workflows, and full audit trail for secure software deployments.</p>
            <ul class="feature-list">
                <li><i class="bi bi-git"></i><div><strong>Version-Controlled Repository</strong><br>Git-based storage with full rollback and history</div></li>
                <li><i class="bi bi-people-fill"></i><div><strong>Role-Based Access Control</strong><br>Admin, Developer, Reviewer, Approver, Auditor</div></li>
                <li><i class="bi bi-check2-all"></i><div><strong>Multi-Stage Approval Workflow</strong><br>Two-level review before any config is deployed</div></li>
                <li><i class="bi bi-journal-check"></i><div><strong>Immutable Audit Logs</strong><br>Every action logged with timestamp & user ID</div></li>
                <li><i class="bi bi-shield-exclamation"></i><div><strong>STRIDE Threat Modelling</strong><br>Live threat register with CIA + Non-Repudiation</div></li>
            </ul>
        </div>
    </div>

    <!-- RIGHT -->
    <div class="login-right">
        <div class="login-form-wrap">
            <h3>Sign In</h3>
            <p>Enter your credentials to access the system</p>

            @if($errors->any())
                <div class="alert mb-4"><i class="bi bi-exclamation-triangle me-2"></i>{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" name="email" class="form-control" placeholder="you@domain.com" value="{{ old('email') }}" required>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label" for="remember" style="font-size:0.8rem;color:var(--text-muted);">Remember me</label>
                </div>
                <button type="submit" class="btn btn-login"><i class="bi bi-shield-lock me-2"></i>Authenticate</button>
            </form>

            <div class="demo-credentials">
                <h6><i class="bi bi-info-circle me-1"></i>Demo Accounts</h6>
                <div class="demo-row"><strong>admin@configvault.com</strong><span>Admin — password</span></div>
                <div class="demo-row"><strong>dev@configvault.com</strong><span>Developer — password</span></div>
                <div class="demo-row"><strong>reviewer@configvault.com</strong><span>Reviewer — password</span></div>
                <div class="demo-row"><strong>approver@configvault.com</strong><span>Approver — password</span></div>
                <div class="demo-row"><strong>auditor@configvault.com</strong><span>Auditor — password</span></div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
