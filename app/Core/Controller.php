<?php
declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected function render(string $view, array $data = [], string $layout = 'auth'): void
    {
        extract($data, EXTR_SKIP);

        ob_start();
        require APP_PATH . '/Views/' . $view . '.php';
        $content = ob_get_clean();

        require APP_PATH . '/Views/layouts/' . $layout . '.php';
    }
}
