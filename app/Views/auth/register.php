<?php $title = '新規登録'; ?>
<div class="card" style="max-width:560px; margin:40px auto;">
    <h1>新規登録</h1>
    <form method="post" action="/register" class="grid">
        <?= csrf_field() ?>
        <div>
            <label>ログインID</label>
            <input type="text" name="login_id" value="<?= e($old['login_id'] ?? '') ?>">
        </div>
        <div>
            <label>表示名</label>
            <input type="text" name="user_name" value="<?= e($old['user_name'] ?? '') ?>">
        </div>
        <div>
            <label>メールアドレス</label>
            <input type="email" name="email" value="<?= e($old['email'] ?? '') ?>">
        </div>
        <div>
            <label>パスワード</label>
            <input type="password" name="password">
        </div>
        <div>
            <label>パスワード確認</label>
            <input type="password" name="password_confirmation">
        </div>
        <button class="btn" type="submit">登録する</button>
    </form>
    <p class="muted"><a href="/login">ログインへ戻る</a></p>
</div>
