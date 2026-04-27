<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\CategoryController;
use App\Controllers\DictionaryController;
use App\Controllers\HomeController;
use App\Controllers\MemoFieldController;
use App\Controllers\MemoTypeController;
$router->get('/', [HomeController::class, 'index']);
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'register']);
$router->post('/logout', [AuthController::class, 'logout']);

$router->get('/dictionary', [DictionaryController::class, 'index']);
$router->get('/dictionary/show', [DictionaryController::class, 'show']);
$router->get('/dictionary/create', [DictionaryController::class, 'create']);
$router->post('/dictionary/store', [DictionaryController::class, 'store']);
$router->get('/dictionary/edit', [DictionaryController::class, 'edit']);
$router->post('/dictionary/update', [DictionaryController::class, 'update']);
$router->post('/dictionary/delete', [DictionaryController::class, 'delete']);
$router->get('/dictionary/history', [DictionaryController::class, 'histories']);
$router->get('/dictionary/history/show', [DictionaryController::class, 'historyShow']);

$router->get('/categories', [CategoryController::class, 'index']);
$router->post('/categories/store', [CategoryController::class, 'store']);
$router->post('/categories/update', [CategoryController::class, 'update']);
$router->post('/categories/delete', [CategoryController::class, 'delete']);
$router->get('/memo-fields', [MemoFieldController::class, 'index']);
$router->post('/memo-fields/store', [MemoFieldController::class, 'store']);
$router->post('/memo-fields/update', [MemoFieldController::class, 'update']);
$router->post('/memo-fields/delete', [MemoFieldController::class, 'delete']);
$router->get('/memo-types', [MemoTypeController::class, 'index']);
$router->post('/memo-types/store', [MemoTypeController::class, 'store']);
$router->post('/memo-types/update', [MemoTypeController::class, 'update']);
$router->post('/memo-types/delete', [MemoTypeController::class, 'delete']);
