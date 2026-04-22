<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Csrf;
use App\Helpers\Flash;
use App\Helpers\Response;
use App\Helpers\View;

abstract class BaseController
{
    protected function view(string $view, array $data = []): void
    {
        View::render($view, $data + [
            'authUser' => Auth::user(),
        ]);
    }

    protected function requireAuth(): void
    {
        if (!Auth::check()) {
            Flash::error('ログインしてください。');
            Response::redirect('/login');
        }
    }

    protected function requireGuest(): void
    {
        if (Auth::check()) {
            Response::redirect('/dictionary');
        }
    }

    protected function verifyCsrf(): void
    {
        if (!Csrf::verify($_POST['_token'] ?? null)) {
            Flash::error('不正なリクエストです。');
            Response::redirect($_SERVER['HTTP_REFERER'] ?? '/');
        }
    }
}
