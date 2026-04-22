<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Flash;
use App\Helpers\Response;
use App\Services\CategoryService;
use App\Services\DictionaryService;

final class DictionaryController extends BaseController
{
    public function __construct(
        private readonly DictionaryService $service = new DictionaryService(),
        private readonly CategoryService $categories = new CategoryService()
    ) {
    }

    public function index(): void
    {
        $this->requireAuth();
        $filters = [
            'category_id' => $_GET['category_id'] ?? '',
            'status' => $_GET['status'] ?? '',
            'title' => $_GET['title'] ?? '',
            'keyword' => $_GET['keyword'] ?? '',
        ];
        $this->view('dictionary.index', [
            'entries' => $this->service->list((int) Auth::id(), $filters),
            'filters' => $filters,
            'categories' => $this->categories->list(),
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
        $this->view('dictionary.form', [
            'mode' => 'create',
            'entry' => ['status' => 'draft', 'priority_level' => 3],
            'categories' => $this->categories->list(),
            'keywordsText' => '',
        ]);
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
            $this->view('dictionary.form', [
                'mode' => 'create',
                'entry' => $_POST,
                'categories' => $this->categories->list(),
                'keywordsText' => (string) ($_POST['keywords'] ?? ''),
            ]);
        }
    }

    public function edit(): void
    {
        $this->requireAuth();
        try {
            $entryId = (int) ($_GET['id'] ?? 0);
            $entry = $this->service->detail($entryId, (int) Auth::id());
            $keywordsText = implode(', ', array_map(static fn(array $row): string => $row['keyword'], $entry['keywords']));
            $this->view('dictionary.form', [
                'mode' => 'edit',
                'entry' => $entry,
                'categories' => $this->categories->list(),
                'keywordsText' => $keywordsText,
            ]);
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
            $this->view('dictionary.form', [
                'mode' => 'edit',
                'entry' => $entry,
                'categories' => $this->categories->list(),
                'keywordsText' => (string) ($_POST['keywords'] ?? ''),
            ]);
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
}
