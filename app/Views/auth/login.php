<section id="login">
    <div class="hero">
        <div class="brand-row">
            <a class="brand" href="<?= e(url()) ?>" aria-label="GreenPulse home">
                <svg width="30" height="30" viewBox="0 0 30 30" aria-hidden="true">
                    <path d="M15 27C6 24 4 14 6 5c9 0 18 4 18 12 0 5-4 8-9 10z" fill="#A8D672"/>
                    <path d="M15 27c0-8 2-12 6-15" stroke="#14261C" stroke-width="2" fill="none"/>
                </svg>
                GreenPulse
            </a>
        </div>
        <div>
            <h1>Know your lettuce is fine before you check the farm.</h1>
            <p>Live temperature, humidity, pH and TDS from your greenhouse, with fan and pump control from anywhere.</p>
        </div>
        <div class="chips">
            <span class="chip">Greenhouse A <b>23.4 °C</b></span>
            <span class="chip">Nutrient tank <b>pH 6.1</b></span>
        </div>
    </div>
    <div class="formwrap">
        <form method="post" action="<?= e(url('login')) ?>">
            <button class="btn btn-light btn-sm theme-toggle" type="button" data-theme-toggle aria-label="Switch to dark mode">Dark mode</button>
            <h2>Sign in</h2>
            <p class="sub">Use your farm account to open the dashboard.</p>

            <?php if ($error !== ''): ?>
                <div class="err" role="alert"><?= e($error) ?></div>
            <?php elseif ($notice !== ''): ?>
                <div class="notice" role="status"><?= e($notice) ?></div>
            <?php endif; ?>

            <label for="email">Email
                <input class="form-control" id="email" name="email" type="email" autocomplete="username" required value="<?= e($email) ?>">
            </label>
            <label for="password">Password
                <input class="form-control" id="password" name="password" type="password" autocomplete="current-password" required>
            </label>
            <p class="auth-link"><a href="<?= e(url('forgot-password')) ?>">Forgot password?</a></p>
            <button class="btn btn-success w-100" type="submit">Sign in</button>
            <p class="auth-link">New to GreenPulse? <a href="<?= e(url('register')) ?>">Create an account</a></p>
        </form>
    </div>
</section>
