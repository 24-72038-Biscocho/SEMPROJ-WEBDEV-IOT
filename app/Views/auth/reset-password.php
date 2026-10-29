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
            <h1>Secure your farm account.</h1>
            <p>Choose a new password to continue monitoring your greenhouse.</p>
        </div>
        <div class="chips">
            <span class="chip">Greenhouse A <b>23.4 °C</b></span>
            <span class="chip">Nutrient tank <b>pH 6.1</b></span>
        </div>
    </div>
    <div class="formwrap">
        <form method="post" action="<?= e(url('reset-password')) ?>">
            <button class="btn btn-light btn-sm theme-toggle" type="button" data-theme-toggle aria-label="Switch to dark mode">Dark mode</button>
            <h2>Reset password</h2>

            <?php if ($error !== ''): ?>
                <div class="err" role="alert"><?= e($error) ?></div>
            <?php endif; ?>

            <?php if ($valid): ?>
                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                <label for="password">New password (minimum 8 characters)
                    <input class="form-control" id="password" name="password" type="password" autocomplete="new-password" minlength="8" required>
                </label>
                <label for="password_confirmation">Confirm new password
                    <input class="form-control" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="8" required>
                </label>
                <button class="btn btn-success w-100" type="submit">Update password</button>
            <?php else: ?>
                <p class="sub">Your reset code has expired. Request a new one to continue.</p>
                <a class="btn btn-success w-100 text-center" href="<?= e(url('forgot-password')) ?>">Request another code</a>
            <?php endif; ?>

            <p class="auth-link"><a href="<?= e(url('login')) ?>">Back to sign in</a></p>
        </form>
    </div>
</section>
