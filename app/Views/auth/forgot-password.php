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
            <h1>Get back to growing.</h1>
            <p>Request a secure, one-time password reset code for your farm account.</p>
        </div>
        <div class="chips">
            <span class="chip">Greenhouse A <b>23.4 °C</b></span>
            <span class="chip">Nutrient tank <b>pH 6.1</b></span>
        </div>
    </div>
    <div class="formwrap">
        <form method="post" action="<?= e(url($otpStep ? 'verify-reset-otp' : 'forgot-password')) ?>">
            <button class="btn btn-light btn-sm theme-toggle" type="button" data-theme-toggle aria-label="Switch to dark mode">Dark mode</button>
            <h2>Forgot password?</h2>
            <p class="sub"><?= $otpStep ? 'Enter the 6-digit code sent to your email address.' : 'Enter the email address associated with your account.' ?></p>

            <?php if ($error !== ''): ?>
                <div class="err" role="alert"><?= e($error) ?></div>
            <?php elseif ($notice !== ''): ?>
                <div class="notice" role="status"><?= e($notice) ?></div>
            <?php endif; ?>

            <?php if ($otpStep): ?>
                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                <input type="hidden" name="email" value="<?= e($email) ?>">
                <label for="code">6-digit verification code
                    <input class="form-control" id="code" name="code" type="text" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" required>
                </label>
                <button class="btn btn-success w-100" type="submit">Verify code</button>
                <p class="auth-link"><a href="<?= e(url('forgot-password')) ?>">Use a different email</a></p>
            <?php else: ?>
                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                <label for="email">Email
                    <input class="form-control" id="email" name="email" type="email" autocomplete="email" required value="<?= e($email) ?>">
                </label>
                <button class="btn btn-success w-100" type="submit">Send reset code</button>
            <?php endif; ?>
            <p class="auth-link"><a href="<?= e(url('login')) ?>">Back to sign in</a></p>
        </form>
    </div>
</section>
