# GreenPulse

GreenPulse is a native PHP MVC greenhouse monitoring prototype for XAMPP and MySQL. It uses Bootstrap 5.3.3 and PHPMailer; no PHP framework is required.

## Setup

1. Place the project in XAMPP `htdocs` and start Apache and MySQL.
2. Import `login_system.sql` using phpMyAdmin. It creates the database tables and default sensor thresholds.
3. Set MySQL credentials in `config/database.php` if they differ from the XAMPP defaults.
4. Configure SMTP in `.env` before using email OTP. For Gmail, use `smtp.gmail.com`, port `587`, TLS, and a Google App Password. Keep `.env` private.
5. Run `composer install` to install PHPMailer.
6. Open `http://localhost/WEBDEV_IOT_SEM_PROJ/`, then sign in or create an account.

On a fresh database, the first registered account is Administrator and later registrations are Farm Staff. Existing accounts become Administrators when the role column is added to an older database. Re-importing `login_system.sql` adds missing schema; default threshold rows do not overwrite customized values. The dashboard can also create the threshold table on first load if it is missing.

Apache `mod_rewrite` and `AllowOverride All` are required for clean routes. Bootstrap and Google Fonts load from CDNs, so the interface needs internet access for those assets.

## Temporary remote demo

For a temporary demo, keep Apache and MySQL running and start this development server from the project directory:

```powershell
php -S 127.0.0.1:8081 -t public app/tunnel-router.php
```

In another terminal, run `cloudflared tunnel --url http://127.0.0.1:8081`. The generated URL is public to anyone who has it and changes when the tunnel restarts. This setup is for demos, not production.

## MVC structure

- `app/Controllers/` - landing, authentication, and dashboard request handling
- `app/Models/` - users, password resets, and sensor thresholds
- `app/Core/` - router, base controller, database connection, and mailer
- `app/Views/` - landing, authentication, and dashboard templates/layouts
- `config/` - MySQL and SMTP configuration
- `routes/web.php` - application routes
- `public/` - CSS, JavaScript, and image assets
- `index.php` - application front controller
- `login_system.sql` - database schema and default thresholds

## Routes

- `/` - landing page
- `/login`, `/register` - account access
- `/forgot-password`, `/reset-password` - email OTP password reset
- `/dashboard` - protected dashboard
- `POST /dashboard/thresholds` - administrator-only threshold updates
- `/logout` - end the current session

Dashboard readings, greenhouse choices, alerts, reports, devices, and actuator commands are demo data. ESP32/MQTT hardware, live telemetry storage, and relay control are not connected. Admin account rows in the management panel are demo-only. Farm Staff cannot access Admin management or edit thresholds.

Password reset OTPs are stored as hashes, expire after 10 minutes, allow up to five verification attempts, and are invalidated after use. Email is sent over authenticated SMTP using PHPMailer.
