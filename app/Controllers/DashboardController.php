<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\SensorThreshold;

final class DashboardController extends Controller
{
    public function index(): void
    {
        if (!isset($_SESSION['user_id'])) {
            redirect('');
        }

        $user = (new User())->findById((int) $_SESSION['user_id']);
        if ($user === null) {
            $_SESSION = [];
            session_destroy();
            redirect('');
        }

        $thresholds = (new SensorThreshold())->all();
        $this->render('dashboard/index', [
            'user' => $user,
            'thresholds' => $thresholds,
            'csrfToken' => $this->csrfToken(),
            'isAdmin' => ($user['role'] ?? '') === 'Administrator',
            'notice' => $_SESSION['dashboard_notice'] ?? ''
        ], 'dashboard');
        unset($_SESSION['dashboard_notice']);
    }

    public function updateThresholds(): void
    {
        $user = isset($_SESSION['user_id']) ? (new User())->findById((int) $_SESSION['user_id']) : null;
        if ($user === null) redirect('');
        if (($user['role'] ?? '') !== 'Administrator') {
            http_response_code(403);
            $_SESSION['dashboard_notice'] = 'Only administrators can change sensor thresholds.';
            redirect('dashboard');
        }
        if (!$this->hasValidCsrfToken()) {
            $_SESSION['dashboard_notice'] = 'Your session expired. Please try again.';
            redirect('dashboard');
        }
        $values = [];
        foreach ((new SensorThreshold())->all() as $sensor) {
            $key = $sensor['key'];
            $low = filter_var($_POST[$key . '_min'] ?? null, FILTER_VALIDATE_FLOAT);
            $high = filter_var($_POST[$key . '_max'] ?? null, FILTER_VALIDATE_FLOAT);
            if ($low === false || $high === false || $low >= $high || $low < $sensor['min'] || $high > $sensor['max']) {
                $_SESSION['dashboard_notice'] = 'Check each threshold: minimum must be lower than maximum and within the sensor range.';
                redirect('dashboard');
            }
            $values[$key] = [$low, $high];
        }
        (new SensorThreshold())->updateAll($values);
        $_SESSION['dashboard_notice'] = 'Sensor thresholds saved.';
        redirect('dashboard');
    }

    private function csrfToken(): string
    {
        if (!isset($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return $_SESSION['csrf_token'];
    }

    private function hasValidCsrfToken(): bool
    {
        return isset($_SESSION['csrf_token'], $_POST['csrf_token'])
            && hash_equals($_SESSION['csrf_token'], (string) $_POST['csrf_token']);
    }
}
