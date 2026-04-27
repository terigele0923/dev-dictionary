<?php
$isEditing = !empty($editingType);
$title = $isEditing ? 'メモタイプ編集' : 'メモタイプ登録';
$selectedFieldMap = [];
$requiredFieldMap = [];
$sortOrderMap = [];
$labelOverrideMap = [];
if ($isEditing) {
    foreach (($editingType['fields'] ?? []) as $field) {
        $fieldId = (int) $field['field_id'];
        $selectedFieldMap[$fieldId] = true;
        $requiredFieldMap[$fieldId] = !empty($field['is_required']);
        $sortOrderMap[$fieldId] = (int) $field['sort_order'];
        $labelOverrideMap[$fieldId] = (string) ($field['label_override'] ?? '');
    }
}
?>
<div class="card">
    <div class="inline" style="justify-content:space-between;">
        <h1><?= e($title) ?></h1>
        <?php if ($isEditing): ?>
            <a class="btn btn-light" href="/memo-types">新規登録に戻る</a>
        <?php endif; ?>
    </div>
    <form method="post" action="<?= $isEditing ? '/memo-types/update' : '/memo-types/store' ?>" class="grid">
        <?= csrf_field() ?>
        <?php if ($isEditing): ?>
            <input type="hidden" name="memo_type_id" value="<?= e((string) $editingType['memo_type_id']) ?>">
        <?php endif; ?>
        <div class="grid grid-2">
            <div>
                <label>タイプ名</label>
                <input type="text" name="type_name" value="<?= e($editingType['type_name'] ?? '') ?>">
            </div>
            <div>
                <label>タイプキー</label>
                <input type="text" name="type_key" placeholder="investigation" value="<?= e($editingType['type_key'] ?? '') ?>" <?= $isEditing ? 'readonly' : '' ?>>
            </div>
            <div style="grid-column:1 / -1;">
                <label>説明</label>
                <textarea name="description"><?= e($editingType['description'] ?? '') ?></textarea>
            </div>
            <div>
                <label>詳細表示形式</label>
                <select name="display_mode">
                    <option value="section" <?= ($editingType['display_mode'] ?? 'section') === 'section' ? 'selected' : '' ?>>通常表示</option>
                    <option value="table" <?= ($editingType['display_mode'] ?? 'section') === 'table' ? 'selected' : '' ?>>テーブル表示</option>
                </select>
            </div>
            <div>
                <label>入力形式</label>
                <select name="input_mode">
                    <option value="section" <?= ($editingType['input_mode'] ?? 'section') === 'section' ? 'selected' : '' ?>>通常入力</option>
                    <option value="table_rows" <?= ($editingType['input_mode'] ?? 'section') === 'table_rows' ? 'selected' : '' ?>>テーブル行入力</option>
                </select>
            </div>
        </div>

        <div class="checkbox-list">
            <?php foreach ($fields as $index => $field): ?>
                <?php $fieldId = (int) $field['field_id']; ?>
                <div class="checkbox-item">
                    <div class="inline" style="justify-content:space-between;">
                        <label style="margin:0;">
                            <input type="checkbox" name="field_ids[]" value="<?= e((string) $fieldId) ?>" <?= !empty($selectedFieldMap[$fieldId]) ? 'checked' : '' ?>>
                            <?= e($field['field_name']) ?>
                        </label>
                        <span class="muted"><code><?= e($field['field_key']) ?></code> / <?= e($field['input_type']) ?></span>
                    </div>
                    <div class="grid grid-2" style="margin-top:12px;">
                        <div>
                            <label>表示名上書き</label>
                            <input type="text" name="label_override[<?= e((string) $fieldId) ?>]" placeholder="<?= e($field['field_name']) ?>" value="<?= e($labelOverrideMap[$fieldId] ?? '') ?>">
                        </div>
                        <div>
                            <label>表示順</label>
                            <input type="number" name="sort_order[<?= e((string) $fieldId) ?>]" min="1" value="<?= e((string) ($sortOrderMap[$fieldId] ?? ($index + 1))) ?>">
                        </div>
                        <div class="inline">
                            <label style="margin:0;">
                                <input type="checkbox" name="is_required[<?= e((string) $fieldId) ?>]" value="1" <?= !empty($requiredFieldMap[$fieldId]) ? 'checked' : '' ?>>
                                このタイプでは必須
                            </label>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="inline">
            <button class="btn" type="submit"><?= $isEditing ? '更新する' : '追加する' ?></button>
        </div>
    </form>
</div>

<div class="card">
    <h2>登録済みメモタイプ</h2>
    <?php foreach ($types as $type): ?>
        <div style="padding:14px 0; border-bottom:1px solid #e5e7eb;">
            <div class="inline" style="justify-content:space-between;">
                <div>
                    <strong><?= e($type['type_name']) ?></strong>
                    <span class="muted"><code><?= e($type['type_key']) ?></code></span>
                </div>
                <span class="badge"><?= e((string) count($type['fields'])) ?> 項目</span>
            </div>
            <p class="muted">表示形式: <?= ($type['display_mode'] ?? 'section') === 'table' ? 'テーブル表示' : '通常表示' ?> / 入力形式: <?= ($type['input_mode'] ?? 'section') === 'table_rows' ? 'テーブル行入力' : '通常入力' ?></p>
            <?php if (!empty($type['description'])): ?>
                <p class="muted"><?= e($type['description']) ?></p>
            <?php endif; ?>
            <div class="inline" style="margin-bottom:10px;">
                <?php foreach ($type['fields'] as $field): ?>
                    <span class="badge"><?= e($field['label_override'] ?: $field['field_name']) ?></span>
                <?php endforeach; ?>
            </div>
            <div class="inline">
                <?php if (($type['type_key'] ?? '') !== 'standard'): ?>
                    <a class="btn btn-secondary" href="/memo-types?edit=<?= e((string) $type['memo_type_id']) ?>">編集</a>
                    <form method="post" action="/memo-types/delete" onsubmit="return confirm('このメモタイプを削除しますか？');" style="margin:0;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="memo_type_id" value="<?= e((string) $type['memo_type_id']) ?>">
                        <button class="btn btn-danger" type="submit">削除</button>
                    </form>
                <?php else: ?>
                    <span class="muted">標準メモタイプは固定です。</span>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (empty($types)): ?>
        <p class="muted">メモタイプはまだありません。</p>
    <?php endif; ?>
</div>
