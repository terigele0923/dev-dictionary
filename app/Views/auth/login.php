<?php $title = 'ログイン'; ?>
<div class="card" style="max-width:480px; margin:40px auto;">
    <h1>ログイン</h1>
    <form method="post" action="/login" class="grid">
        <?= csrf_field() ?>
        <div>
            <label>ログインID</label>
            <input type="text" name="login_id" value="<?= e($old['login_id'] ?? '') ?>">
        </div>
        <div>
            <label>パスワード</label>
            <input type="password" name="password">
        </div>
        <button class="btn" type="submit">ログイン</button>
    </form>
    <p class="muted">アカウントがない場合は <a href="/register">新規登録</a></p>
</div>
