<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Flash;
use App\Helpers\Response;
use App\Services\CategoryService;
use App\Services\DictionaryService;
use App\Services\MemoTypeService;

final class DictionaryController extends BaseController
{
    public function __construct(
        private readonly DictionaryService $service = new DictionaryService(),
        private readonly CategoryService $categories = new CategoryService(),
        private readonly MemoTypeService $memoTypes = new MemoTypeService()
    ) {
    }

    public function index(): void
    {
        $this->requireAuth();
        $filters = [
            'category_id' => $_GET['category_id'] ?? '',
            'memo_type_id' => $_GET['memo_type_id'] ?? '',
            'status' => $_GET['status'] ?? '',
            'title' => $_GET['title'] ?? '',
            'keyword' => $_GET['keyword'] ?? '',
        ];
        $this->view('dictionary.index', [
            'entries' => $this->service->list((int) Auth::id(), $filters),
            'filters' => $filters,
            'categories' => $this->categories->list(),
            'memoTypes' => $this->memoTypes->listActive(),
        ]);
    }

    public function show(): void
    {
        $this->requireAuth();
        try {
            $entryId = (int) ($_GET['id'] ?? 0);
            $this->view('dictionary.show', [
                'entry' => $this->service->detail($entryId, (int) Auth::id()),
            ]);
        } catch (\Throwable $e) {
            Flash::error($e->getMessage());
            Response::redirect('/dictionary');
        }
    }

    public function create(): void
    {
        $this->requireAuth();
        $memoTypes = $this->memoTypes->listActive();
        $selectedMemoTypeId = $this->resolveSelectedMemoTypeId($memoTypes, (int) ($_GET['memo_type_id'] ?? 0));
        $this->renderForm('create', [
            'status' => 'draft',
            'priority_level' => 3,
            'memo_type_id' => $selectedMemoTypeId,
        ], '', [], [], $selectedMemoTypeId, $memoTypes);
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        try {
            $entryId = $this->service->create((int) Auth::id(), $_POST);
            Flash::success('辞書を登録しました。');
            Response::redirect('/dictionary/show?id=' . $entryId);
        } catch (\Throwable $e) {
            Flash::error($e->getMessage());
            $entry = $_POST;
            $selectedMemoTypeId = (int) ($entry['memo_type_id'] ?? 0);
            $this->renderForm('create', $entry, (string) ($_POST['keywords'] ?? ''), (array) ($_POST['field_values'] ?? []), (array) ($_POST['field_rows'] ?? []), $selectedMemoTypeId);
        }
    }

    public function edit(): void
    {
        $this->requireAuth();
        try {
            $entryId = (int) ($_GET['id'] ?? 0);
            $entry = $this->service->detail($entryId, (int) Auth::id());
            $keywordsText = implode(', ', array_map(static fn(array $row): string => $row['keyword'], $entry['keywords']));
            $selectedMemoTypeId = (int) ($_GET['memo_type_id'] ?? $entry['memo_type_id']);
            $fieldValues = [];
            foreach ($entry['field_values'] as $field) {
                $fieldValues[(int) $field['field_id']] = $field['value'];
            }
            $entry['memo_type_id'] = $selectedMemoTypeId;
            $fieldRows = [];
            foreach ($entry['field_rows'] as $row) {
                foreach ($row['columns'] as $column) {
                    $fieldRows[$row['row_no']][$column['field_id']] = $column['value'];
                }
            }
            $this->renderForm('edit', $entry, $keywordsText, $fieldValues, $fieldRows, $selectedMemoTypeId);
        } catch (\Throwable $e) {
            Flash::error($e->getMessage());
            Response::redirect('/dictionary');
        }
    }

    public function update(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $entryId = (int) ($_POST['entry_id'] ?? 0);
        try {
            $this->service->update($entryId, (int) Auth::id(), $_POST);
            Flash::success('辞書を更新しました。');
            Response::redirect('/dictionary/show?id=' . $entryId);
        } catch (\Throwable $e) {
            Flash::error($e->getMessage());
            $entry = $_POST;
            $entry['entry_id'] = $entryId;
            $selectedMemoTypeId = (int) ($entry['memo_type_id'] ?? 0);
            $this->renderForm('edit', $entry, (string) ($_POST['keywords'] ?? ''), (array) ($_POST['field_values'] ?? []), (array) ($_POST['field_rows'] ?? []), $selectedMemoTypeId);
        }
    }

    public function delete(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        try {
            $this->service->delete((int) ($_POST['entry_id'] ?? 0), (int) Auth::id());
            Flash::success('辞書を削除しました。');
        } catch (\Throwable $e) {
            Flash::error($e->getMessage());
        }
        Response::redirect('/dictionary');
    }

    public function histories(): void
    {
        $this->requireAuth();
        try {
            $entryId = (int) ($_GET['id'] ?? 0);
            $this->view('dictionary.history_index', [
                'entryId' => $entryId,
                'histories' => $this->service->histories($entryId, (int) Auth::id()),
            ]);
        } catch (\Throwable $e) {
            Flash::error($e->getMessage());
            Response::redirect('/dictionary');
        }
    }

    public function historyShow(): void
    {
        $this->requireAuth();
        try {
            $historyId = (int) ($_GET['history_id'] ?? 0);
            $this->view('dictionary.history_show', [
                'history' => $this->service->historyDetail($historyId, (int) Auth::id()),
            ]);
        } catch (\Throwable $e) {
            Flash::error($e->getMessage());
            Response::redirect('/dictionary');
        }
    }

    private function renderForm(string $mode, array $entry, string $keywordsText, array $fieldValues, array $fieldRows, ?int $selectedMemoTypeId = null, ?array $memoTypes = null): void
    {
        $memoTypes ??= $this->memoTypes->listActive();
        $selectedMemoTypeId = $this->resolveSelectedMemoTypeId($memoTypes, $selectedMemoTypeId ?? 0);
        $selectedType = $selectedMemoTypeId > 0 ? $this->memoTypes->find($selectedMemoTypeId) : null;

        $this->view('dictionary.form', [
            'mode' => $mode,
            'entry' => $entry,
            'categories' => $this->categories->list(),
            'keywordsText' => $keywordsText,
            'memoTypes' => $memoTypes,
            'selectedMemoTypeId' => $selectedMemoTypeId,
            'selectedType' => $selectedType,
            'selectedFields' => $selectedType['fields'] ?? [],
            'fieldValues' => $fieldValues,
            'fieldRows' => $fieldRows,
        ]);
    }

    private function resolveSelectedMemoTypeId(array $memoTypes, int $requestedMemoTypeId): int
    {
        if ($requestedMemoTypeId > 0) {
            return $requestedMemoTypeId;
        }

        return isset($memoTypes[0]['memo_type_id']) ? (int) $memoTypes[0]['memo_type_id'] : 0;
    }
}
