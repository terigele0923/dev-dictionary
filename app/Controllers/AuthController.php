<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Flash;
use App\Helpers\Response;
use App\Helpers\Session;
use App\Services\AuthService;

final class AuthController extends BaseController
{
    public function __construct(private readonly AuthService $service = new AuthService())
    {
    }

    public function showLogin(): void
    {
        $this->requireGuest();
        $this->view('auth.login', ['old' => []]);
    }

    public function login(): void
    {
        $this->requireGuest();
        $this->verifyCsrf();

        $loginId = trim((string) ($_POST['login_id'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($loginId === '' || $password === '') {
            Flash::error('ログインIDとパスワードを入力してください。');
            $this->view('auth.login', ['old' => ['login_id' => $loginId]]);
            return;
        }

        if (!$this->service->login($loginId, $password)) {
            Flash::error('ログインに失敗しました。');
            $this->view('auth.login', ['old' => ['login_id' => $loginId]]);
            return;
        }

        Flash::success('ログインしました。');
        Response::redirect('/dictionary');
    }

    public function showRegister(): void
    {
        $this->requireGuest();
        $this->view('auth.register', ['old' => []]);
    }

    public function register(): void
    {
        $this->requireGuest();
        $this->verifyCsrf();

        $input = [
            'login_id' => trim((string) ($_POST['login_id'] ?? '')),
            'user_name' => trim((string) ($_POST['user_name'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')) ?: null,
            'password' => (string) ($_POST['password'] ?? ''),
            'password_confirmation' => (string) ($_POST['password_confirmation'] ?? ''),
        ];

        try {
            if ($input['login_id'] === '' || $input['user_name'] === '' || $input['password'] === '') {
                throw new \InvalidArgumentException('必須項目を入力してください。');
            }
            if ($input['password'] !== $input['password_confirmation']) {
                throw new \InvalidArgumentException('パスワード確認が一致しません。');
            }
            $this->service->register($input);
            Flash::success('ユーザー登録が完了しました。ログインしてください。');
            Response::redirect('/login');
        } catch (\Throwable $e) {
            Flash::error($e->getMessage());
            $this->view('auth.register', ['old' => $input]);
        }
    }

    public function logout(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        Auth::logout();
        Session::start();
        Flash::success('ログアウトしました。');
        Response::redirect('/login');
    }
}
