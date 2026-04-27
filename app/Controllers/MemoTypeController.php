<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Flash;
use App\Helpers\Response;
use App\Services\MemoFieldService;
use App\Services\MemoTypeService;

final class MemoTypeController extends BaseController
{
    public function __construct(
        private readonly MemoTypeService $types = new MemoTypeService(),
        private readonly MemoFieldService $fields = new MemoFieldService()
    ) {
    }

    public function index(): void
    {
        $this->requireAuth();
        $editTypeId = (int) ($_GET['edit'] ?? 0);
        $editingType = $editTypeId > 0 ? $this->types->find($editTypeId) : null;
        $this->view('memo_types.index', [
            'types' => $this->types->list(),
            'fields' => $this->fields->listActive(),
            'editingType' => $editingType,
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        try {
            $this->types->create($_POST);
            Flash::success('メモタイプを追加しました。');
        } catch (\Throwable $e) {
            Flash::error($e->getMessage());
        }

        Response::redirect('/memo-types');
    }

    public function update(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $memoTypeId = (int) ($_POST['memo_type_id'] ?? 0);
        try {
            $this->types->update($memoTypeId, $_POST);
            Flash::success('メモタイプを更新しました。');
            Response::redirect('/memo-types');
        } catch (\Throwable $e) {
            Flash::error($e->getMessage());
            Response::redirect('/memo-types?edit=' . $memoTypeId);
        }
    }

    public function delete(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $memoTypeId = (int) ($_POST['memo_type_id'] ?? 0);
        try {
            $this->types->delete($memoTypeId);
            Flash::success('メモタイプを削除しました。');
        } catch (\Throwable $e) {
            Flash::error($e->getMessage());
        }

        Response::redirect('/memo-types');
    }
}
