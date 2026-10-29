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
            <h1>Grow with a clearer view of your greenhouse.</h1>
            <p>Create a farm account to access your GreenPulse dashboard.</p>
        </div>
        <div class="chips">
            <span class="chip">Greenhouse A <b>23.4 °C</b></span>
            <span class="chip">Nutrient tank <b>pH 6.1</b></span>
        </div>
    </div>
    <div class="formwrap">
        <form method="post" action="<?= e(url('register')) ?>">
            <button class="btn btn-light btn-sm theme-toggle" type="button" data-theme-toggle aria-label="Switch to dark mode">Dark mode</button>
            <h2>Create account</h2>
            <p class="sub">Set up your farm account to get started.</p>

            <?php if ($error !== ''): ?>
                <div class="err" role="alert"><?= e($error) ?></div>
            <?php endif; ?>

            <label for="full_name">Full name
                <input class="form-control" id="full_name" name="full_name" type="text" autocomplete="name" required value="<?= e($name) ?>">
            </label>
            <label for="email">Email
                <input class="form-control" id="email" name="email" type="email" autocomplete="email" required value="<?= e($email) ?>">
            </label>
            <label for="password">Password (minimum 8 characters)
                <input class="form-control" id="password" name="password" type="password" autocomplete="new-password" minlength="8" required>
            </label>
            <button class="btn btn-success w-100" type="submit">Create account</button>
            <p class="auth-link">Already registered? <a href="<?= e(url('login')) ?>">Sign in</a></p>
        </form>
    </div>
</section>
