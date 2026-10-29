<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

final class LandingController extends Controller
{
    public function index(): void
    {
        $this->render('landing/index', [], 'public');
    }
}
