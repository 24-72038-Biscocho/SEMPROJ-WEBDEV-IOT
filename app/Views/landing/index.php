<header class="landing-nav">
    <a class="landing-brand" href="<?= e(url()) ?>" aria-label="GreenPulse home">
        <svg width="30" height="30" viewBox="0 0 30 30" aria-hidden="true">
            <path d="M15 27C6 24 4 14 6 5c9 0 18 4 18 12 0 5-4 8-9 10z" fill="#A8D672"/>
            <path d="M15 27c0-8 2-12 6-15" stroke="#14261C" stroke-width="2" fill="none"/>
        </svg>
        GreenPulse
    </a>
    <nav aria-label="Main navigation">
        <a href="#features">Features</a>
        <a href="#about">About</a>
        <a href="#developers">Developers</a>
        <button class="landing-theme-toggle" type="button" data-theme-toggle aria-label="Switch to dark mode">Dark mode</button>
        <a class="landing-nav-cta" href="<?= e(url('login')) ?>">Sign in</a>
    </nav>
</header>

<main>
    <section class="landing-hero">
        <div class="landing-hero-content">
            <span class="landing-eyebrow"><span></span> WEB-INTEGRATION IOT SYSTEM</span>
            <h1>GreenPulse: <span>Web-Integration IoT System for Lettuce Microclimate Monitoring.</span></h1>
            <p>Monitor the conditions that matter to your lettuce crop and manage greenhouse equipment from one clear, connected dashboard.</p>
            <div class="landing-actions">
                <a class="landing-button" href="<?= e(url('register')) ?>">Get started <span aria-hidden="true">→</span></a>
                <a class="landing-text-link" href="#features">Explore the platform</a>
            </div>
            <div class="landing-metrics" aria-label="Monitored environmental conditions">
                <span><b>01</b> Temperature</span>
                <span><b>02</b> Humidity</span>
                <span><b>03</b> pH &amp; TDS</span>
            </div>
        </div>
        <div class="landing-hero-note">
            <span class="landing-note-dot"></span>
            <span><strong>One view of your greenhouse</strong><small>Monitoring · Alerts · Actuator control</small></span>
        </div>
    </section>

    <section class="landing-section landing-overview" id="about">
        <div class="landing-section-heading">
            <span class="landing-eyebrow">ABOUT GREENPULSE</span>
            <h2>Designed for a more informed growing workflow.</h2>
        </div>
        <p>GreenPulse is a web-based greenhouse monitoring interface created to help urban farming operators keep track of lettuce-growing conditions. It brings sensor readings, historical trends, alerts, and equipment controls together in one place.</p>
        <p class="landing-prototype-note"><strong>Project prototype:</strong> sensor readings, alerts, and actuator controls currently use sample data. ESP32, MQTT, and relay hardware are not connected yet.</p>
    </section>

    <section class="landing-section landing-features-section" id="features">
        <div class="landing-section-heading">
            <span class="landing-eyebrow">PLATFORM FEATURES</span>
            <h2>The essentials, working together.</h2>
        </div>
        <div class="landing-feature-grid">
            <article class="landing-feature-card">
                <span class="landing-feature-number">01</span>
                <h3>Environmental monitoring</h3>
                <p>Review temperature, humidity, pH, and TDS readings alongside their target ranges.</p>
            </article>
            <article class="landing-feature-card">
                <span class="landing-feature-number">02</span>
                <h3>Telemetry trends</h3>
                <p>View historical sensor trends to better understand changes in greenhouse conditions.</p>
            </article>
            <article class="landing-feature-card">
                <span class="landing-feature-number">03</span>
                <h3>Alerts &amp; equipment</h3>
                <p>See sample environmental alerts and explore fan and pump controls from the dashboard.</p>
            </article>
        </div>
    </section>

    <section class="landing-developers" id="developers">
        <div class="landing-developers-copy">
            <span class="landing-eyebrow">THE PEOPLE BEHIND THE PROJECT</span>
            <h2>Meet the GreenPulse team.</h2>
            <p>GreenPulse was developed as a collaborative project focused on practical, technology-enabled greenhouse monitoring for lettuce production.</p>
            <a class="landing-text-link" href="<?= e(url('register')) ?>">Explore the platform <span aria-hidden="true">→</span></a>
        </div>
        <figure class="landing-team-photo">
            <img src="<?= e(url('public/images/DEVELOPERS.jpg')) ?>" alt="The GreenPulse project team" loading="lazy">
            <figcaption>GreenPulse project team</figcaption>
        </figure>
    </section>
</main>

<footer class="landing-footer">
    <a class="landing-brand" href="<?= e(url()) ?>">
        <svg width="24" height="24" viewBox="0 0 30 30" aria-hidden="true">
            <path d="M15 27C6 24 4 14 6 5c9 0 18 4 18 12 0 5-4 8-9 10z" fill="#A8D672"/>
        </svg>
        GreenPulse
    </a>
    <span>Web-integrated lettuce microclimate monitoring.</span>
    <a class="landing-contact" href="mailto:greenpulseeee@gmail.com">greenpulseeee@gmail.com</a>
    <a href="<?= e(url('login')) ?>">Sign in</a>
</footer>
