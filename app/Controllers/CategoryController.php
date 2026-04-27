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
        $editCategoryId = (int) ($_GET['edit'] ?? 0);
        $this->view('categories.index', [
            'categories' => $this->service->all(),
            'editingCategory' => $editCategoryId > 0 ? $this->service->find($editCategoryId) : null,
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

    public function update(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $categoryId = (int) ($_POST['category_id'] ?? 0);
        try {
            $this->service->update($categoryId, (string) ($_POST['category_name'] ?? ''), (string) ($_POST['description'] ?? ''));
            Flash::success('カテゴリを更新しました。');
            Response::redirect('/categories');
        } catch (\Throwable $e) {
            Flash::error($e->getMessage());
            Response::redirect('/categories?edit=' . $categoryId);
        }
    }

    public function delete(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        try {
            $this->service->delete((int) ($_POST['category_id'] ?? 0));
            Flash::success('カテゴリを削除しました。');
        } catch (\Throwable $e) {
            Flash::error($e->getMessage());
        }
        Response::redirect('/categories');
    }
}
