<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Response;

final class HomeController extends BaseController
{
    public function index(): void
    {
        if (Auth::check()) {
            Response::redirect('/dictionary');
        }
        Response::redirect('/login');
    }
}
