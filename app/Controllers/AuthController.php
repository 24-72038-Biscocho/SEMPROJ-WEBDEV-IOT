<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Mailer;
use App\Models\PasswordReset;
use App\Models\User;
use RuntimeException;

final class AuthController extends Controller
{
    public function showLogin(): void
    {
        if (isset($_SESSION['user_id'])) {
            redirect('dashboard');
        }

        $this->render('auth/login', [
            'error' => '',
            'email' => '',
            'notice' => $_SESSION['flash'] ?? ''
        ]);
        unset($_SESSION['flash']);
    }

    public function login(): void
    {
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $user = (new User())->findByEmail($email);

        if ($user !== null && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['id'];
            redirect('dashboard');
        }

        $this->render('auth/login', [
            'error' => 'Invalid email or password.',
            'email' => $email,
            'notice' => ''
        ]);
    }

    public function showForgotPassword(): void
    {
        if (isset($_SESSION['user_id'])) {
            redirect('dashboard');
        }

        $this->render('auth/forgot-password', [
            'error' => '',
            'email' => '',
            'notice' => $_SESSION['flash'] ?? '',
            'otpStep' => false,
            'csrfToken' => $this->csrfToken()
        ]);
        unset($_SESSION['flash']);
    }

    public function requestPasswordReset(): void
    {
        $email = trim((string) ($_POST['email'] ?? ''));
        if (!$this->hasValidCsrfToken()) {
            $this->render('auth/forgot-password', [
                'error' => 'Your session expired. Please try again.',
                'email' => $email,
                'notice' => '',
                'otpStep' => false,
                'csrfToken' => $this->csrfToken()
            ]);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->render('auth/forgot-password', [
                'error' => 'Enter a valid email address.',
                'email' => $email,
                'notice' => '',
                'otpStep' => false,
                'csrfToken' => $this->csrfToken()
            ]);
            return;
        }

        unset($_SESSION['password_reset_user_id']);
        $user = (new User())->findByEmail($email);
        if ($user !== null) {
            $code = (string) random_int(100000, 999999);
            $codeHash = hash('sha256', $code);
            $passwordReset = new PasswordReset();
            $userId = (int) $user['id'];
            $passwordReset->create($userId, $codeHash);

            try {
                (new Mailer())->sendPasswordResetCode($email, $code);
            } catch (RuntimeException $exception) {
                $passwordReset->deleteForUser($userId);
                error_log('GreenPulse password reset email delivery failed: ' . $exception->getMessage());
                $this->render('auth/forgot-password', [
                    'error' => 'The email service is not configured or could not send the code. Check SMTP settings and try again.',
                    'email' => $email,
                    'notice' => '',
                    'otpStep' => false,
                    'csrfToken' => $this->csrfToken()
                ]);
                return;
            }
        }

        $this->render('auth/forgot-password', [
            'error' => '',
            'email' => $email,
            'notice' => 'If an account exists for that email, a 6-digit reset code will be sent. The code expires in 10 minutes.',
            'otpStep' => true,
            'csrfToken' => $this->csrfToken()
        ]);
    }

    public function verifyPasswordResetOtp(): void
    {
        $email = trim((string) ($_POST['email'] ?? ''));
        $code = trim((string) ($_POST['code'] ?? ''));

        if (!$this->hasValidCsrfToken()) {
            $this->render('auth/forgot-password', [
                'error' => 'Your session expired. Please request a new code.',
                'email' => $email,
                'notice' => '',
                'otpStep' => true,
                'csrfToken' => $this->csrfToken()
            ]);
            return;
        }

        $user = filter_var($email, FILTER_VALIDATE_EMAIL)
            ? (new User())->findByEmail($email)
            : null;
        $verified = $user !== null
            && preg_match('/^\d{6}$/', $code) === 1
            && (new PasswordReset())->verifyCode((int) $user['id'], hash('sha256', $code));

        if (!$verified) {
            $this->render('auth/forgot-password', [
                'error' => 'That code is invalid, expired, or has reached the attempt limit. Request a new code.',
                'email' => $email,
                'notice' => '',
                'otpStep' => true,
                'csrfToken' => $this->csrfToken()
            ]);
            return;
        }

        session_regenerate_id(true);
        $_SESSION['password_reset_user_id'] = (int) $user['id'];
        redirect('reset-password');
    }

    public function showPasswordReset(): void
    {
        $userId = (int) ($_SESSION['password_reset_user_id'] ?? 0);
        if ($userId < 1) {
            redirect('forgot-password');
        }

        $this->render('auth/reset-password', [
            'valid' => true,
            'error' => '',
            'csrfToken' => $this->csrfToken()
        ]);
    }

    public function resetPassword(): void
    {
        $userId = (int) ($_SESSION['password_reset_user_id'] ?? 0);
        $password = (string) ($_POST['password'] ?? '');
        $passwordConfirmation = (string) ($_POST['password_confirmation'] ?? '');
        $error = '';

        if (!$this->hasValidCsrfToken()) {
            $error = 'Your session expired. Please request a new code.';
        } elseif ($userId < 1) {
            redirect('forgot-password');
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters.';
        } elseif ($password !== $passwordConfirmation) {
            $error = 'The password confirmation does not match.';
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            if ((new PasswordReset())->consumeVerified($userId, $passwordHash)) {
                unset($_SESSION['password_reset_user_id']);
                $_SESSION['flash'] = 'Password updated. You can sign in with your new password.';
                redirect('login');
            }
            unset($_SESSION['password_reset_user_id']);
            $error = 'The reset code has expired. Request a new code.';
        }

        $this->render('auth/reset-password', [
            'valid' => $userId > 0 && isset($_SESSION['password_reset_user_id']),
            'error' => $error,
            'csrfToken' => $this->csrfToken()
        ]);
    }

    public function showRegistration(): void
    {
        if (isset($_SESSION['user_id'])) {
            redirect('dashboard');
        }

        $this->render('auth/register', [
            'error' => '',
            'name' => '',
            'email' => ''
        ]);
    }

    public function register(): void
    {
        $name = trim((string) ($_POST['full_name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $error = '';

        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter your name and a valid email.';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters.';
        } else {
            $userModel = new User();
            if ($userModel->findByEmail($email) !== null) {
                $error = 'That email is already registered.';
            } else {
                $userModel->create($name, $email, password_hash($password, PASSWORD_DEFAULT));
                $_SESSION['flash'] = 'Account created. You can sign in now.';
                redirect('');
            }
        }

        $this->render('auth/register', [
            'error' => $error,
            'name' => $name,
            'email' => $email
        ]);
    }

    public function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $cookie = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httponly']);
        }

        session_destroy();
        redirect('');
    }

    private function csrfToken(): string
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    private function hasValidCsrfToken(): bool
    {
        $submittedToken = (string) ($_POST['csrf_token'] ?? '');
        return isset($_SESSION['csrf_token'])
            && hash_equals($_SESSION['csrf_token'], $submittedToken);
    }
}
