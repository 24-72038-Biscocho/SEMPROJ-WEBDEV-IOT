<?php
declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\LandingController;
use App\Core\Router;

$router = new Router();
$router->get('/', [LandingController::class, 'index']);
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/forgot-password', [AuthController::class, 'showForgotPassword']);
$router->post('/forgot-password', [AuthController::class, 'requestPasswordReset']);
$router->post('/verify-reset-otp', [AuthController::class, 'verifyPasswordResetOtp']);
$router->get('/reset-password', [AuthController::class, 'showPasswordReset']);
$router->post('/reset-password', [AuthController::class, 'resetPassword']);
$router->get('/register', [AuthController::class, 'showRegistration']);
$router->post('/register', [AuthController::class, 'register']);
$router->get('/dashboard', [DashboardController::class, 'index']);
$router->post('/dashboard/thresholds', [DashboardController::class, 'updateThresholds']);
$router->get('/logout', [AuthController::class, 'logout']);
