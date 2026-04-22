<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\CategoryController;
use App\Controllers\DictionaryController;
use App\Controllers\HomeController;
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
