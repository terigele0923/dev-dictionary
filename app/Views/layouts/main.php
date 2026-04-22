<?php ob_start(); require $viewFile; $content = ob_get_clean(); ?>
<!doctype html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'Dev Dictionary') ?></title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, sans-serif; margin: 0; background:#f5f7fb; color:#1f2937; }
        header { background:#111827; color:#fff; padding:14px 20px; display:flex; justify-content:space-between; align-items:center; }
        header a { color:#fff; text-decoration:none; margin-right:14px; }
        .container { max-width: 1080px; margin: 24px auto; padding: 0 16px; }
        .card { background:#fff; border-radius:12px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,.08); margin-bottom:18px; }
        .grid { display:grid; gap:14px; }
        .grid-2 { grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); }
        label { display:block; font-size:14px; font-weight:600; margin-bottom:6px; }
        input[type=text], input[type=password], input[type=email], input[type=number], select, textarea { width:100%; box-sizing:border-box; border:1px solid #d1d5db; border-radius:8px; padding:10px 12px; }
        textarea { min-height:120px; resize:vertical; }
        .btn { display:inline-block; border:none; border-radius:8px; padding:10px 14px; background:#2563eb; color:#fff; text-decoration:none; cursor:pointer; }
        .btn-secondary { background:#4b5563; }
        .btn-danger { background:#dc2626; }
        .btn-light { background:#e5e7eb; color:#111827; }
        table { width:100%; border-collapse:collapse; }
        th, td { padding:10px; border-bottom:1px solid #e5e7eb; text-align:left; vertical-align:top; }
        .flash { padding:12px 14px; border-radius:10px; margin-bottom:16px; }
        .flash-success { background:#dcfce7; color:#166534; }
        .flash-error { background:#fee2e2; color:#991b1b; }
        .muted { color:#6b7280; font-size:14px; }
        .inline { display:flex; gap:10px; align-items:center; flex-wrap:wrap; }
        .badge { display:inline-block; padding:4px 10px; border-radius:999px; background:#eef2ff; color:#3730a3; font-size:12px; }
        pre { white-space:pre-wrap; background:#111827; color:#f9fafb; padding:16px; border-radius:10px; }
    </style>
</head>
<body>
<header>
    <div><a href="/dictionary"><strong>Dev Dictionary</strong></a></div>
    <?php if (!empty($authUser)): ?>
        <nav class="inline">
            <a href="/dictionary">辞書一覧</a>
            <a href="/dictionary/create">新規登録</a>
            <a href="/categories">カテゴリ</a>
            <span><?= e($authUser['user_name']) ?></span>
            <form method="post" action="/logout" onsubmit="return confirm('ログアウトしますか？');" style="margin:0;">
                <?= csrf_field() ?>
                <button class="btn btn-light" type="submit">ログアウト</button>
            </form>
        </nav>
    <?php endif; ?>
</header>
<div class="container">
    <?php require dirname(__DIR__) . '/partials/flash.php'; ?>
    <?= $content ?>
</div>
</body>
</html>
