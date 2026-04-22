<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Flash;
use App\Helpers\Response;
use App\Services\CategoryService;

final class CategoryController extends BaseController
{
    public function __construct(private readonly CategoryService $service = new CategoryService())
    {
    }

    public function index(): void
    {
        $this->requireAuth();
        $this->view('categories.index', [
            'categories' => $this->service->list(),
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        try {
            $this->service->create((string) ($_POST['category_name'] ?? ''), (string) ($_POST['description'] ?? ''));
            Flash::success('カテゴリを追加しました。');
        } catch (\Throwable $e) {
            Flash::error($e->getMessage());
        }
        Response::redirect('/categories');
    }
}
