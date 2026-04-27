<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Flash;
use App\Helpers\Response;
use App\Services\MemoFieldService;

final class MemoFieldController extends BaseController
{
    public function __construct(private readonly MemoFieldService $service = new MemoFieldService())
    {
    }

    public function index(): void
    {
        $this->requireAuth();
        $editFieldId = (int) ($_GET['edit'] ?? 0);
        $this->view('memo_fields.index', [
            'fields' => $this->service->list(),
            'editingField' => $editFieldId > 0 ? $this->service->find($editFieldId) : null,
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        try {
            $this->service->create($_POST);
            Flash::success('項目を追加しました。');
        } catch (\Throwable $e) {
            Flash::error($e->getMessage());
        }

        Response::redirect('/memo-fields');
    }

    public function update(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $fieldId = (int) ($_POST['field_id'] ?? 0);
        try {
            $this->service->update($fieldId, $_POST);
            Flash::success('項目を更新しました。');
            Response::redirect('/memo-fields');
        } catch (\Throwable $e) {
            Flash::error($e->getMessage());
            Response::redirect('/memo-fields?edit=' . $fieldId);
        }
    }

    public function delete(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        try {
            $this->service->delete((int) ($_POST['field_id'] ?? 0));
            Flash::success('項目を削除しました。');
        } catch (\Throwable $e) {
            Flash::error($e->getMessage());
        }

        Response::redirect('/memo-fields');
    }
}
