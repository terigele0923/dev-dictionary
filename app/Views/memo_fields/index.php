<?php
$isEditing = !empty($editingField);
$title = $isEditing ? '項目編集' : '項目登録';
?>
<div class="card">
    <div class="inline" style="justify-content:space-between;">
        <h1><?= e($title) ?></h1>
        <?php if ($isEditing): ?>
            <a class="btn btn-light" href="/memo-fields">新規登録に戻る</a>
        <?php endif; ?>
    </div>
    <form method="post" action="<?= $isEditing ? '/memo-fields/update' : '/memo-fields/store' ?>" class="grid grid-2">
        <?= csrf_field() ?>
        <?php if ($isEditing): ?>
            <input type="hidden" name="field_id" value="<?= e((string) $editingField['field_id']) ?>">
        <?php endif; ?>
        <div>
            <label>項目名</label>
            <input type="text" name="field_name" value="<?= e($editingField['field_name'] ?? '') ?>">
        </div>
        <div>
            <label>項目キー</label>
            <input type="text" name="field_key" placeholder="symptom" value="<?= e($editingField['field_key'] ?? '') ?>">
        </div>
        <div>
            <label>入力種別</label>
            <select name="input_type">
                <?php foreach (['text' => '1行テキスト', 'textarea' => '複数行', 'number' => '数値', 'date' => '日付'] as $value => $label): ?>
                    <option value="<?= e($value) ?>" <?= ($editingField['input_type'] ?? 'text') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="inline" style="align-self:end;">
            <label style="margin:0;"><input type="checkbox" name="default_required" value="1" <?= !empty($editingField['default_required']) ? 'checked' : '' ?>> 初期値で必須</label>
        </div>
        <div class="inline">
            <button class="btn" type="submit"><?= $isEditing ? '更新する' : '追加する' ?></button>
        </div>
    </form>
</div>

<div class="card">
    <h2>登録済み項目</h2>
    <table>
        <thead><tr><th>ID</th><th>項目名</th><th>項目キー</th><th>入力種別</th><th>初期必須</th><th>操作</th></tr></thead>
        <tbody>
        <?php foreach ($fields as $field): ?>
            <tr>
                <td><?= e($field['field_id']) ?></td>
                <td><?= e($field['field_name']) ?></td>
                <td><code><?= e($field['field_key']) ?></code></td>
                <td><?= e($field['input_type']) ?></td>
                <td><?= !empty($field['default_required']) ? 'はい' : 'いいえ' ?></td>
                <td class="inline">
                    <a class="btn btn-secondary" href="/memo-fields?edit=<?= e((string) $field['field_id']) ?>">編集</a>
                    <form method="post" action="/memo-fields/delete" onsubmit="return confirm('この項目を削除しますか？');" style="margin:0;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="field_id" value="<?= e((string) $field['field_id']) ?>">
                        <button class="btn btn-danger" type="submit">削除</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($fields)): ?>
            <tr><td colspan="6">項目はまだありません。</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
