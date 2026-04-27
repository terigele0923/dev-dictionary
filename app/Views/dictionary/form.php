<?php $title = $mode === 'create' ? 'メモ登録' : 'メモ編集'; ?>
<?php $inputMode = $selectedType['input_mode'] ?? 'section'; ?>
<div class="card">
    <div class="inline" style="justify-content:space-between;">
        <h1><?= e($title) ?></h1>
        <a class="btn btn-light" href="/dictionary">戻る</a>
    </div>

    <form method="get" action="<?= $mode === 'create' ? '/dictionary/create' : '/dictionary/edit' ?>" class="grid grid-2" style="margin-bottom:20px;">
        <?php if ($mode === 'edit'): ?>
            <input type="hidden" name="id" value="<?= e($entry['entry_id'] ?? '') ?>">
        <?php endif; ?>
        <div>
            <label>メモタイプ</label>
            <select name="memo_type_id">
                <?php foreach ($memoTypes as $memoType): ?>
                    <option value="<?= e($memoType['memo_type_id']) ?>" <?= (string) $selectedMemoTypeId === (string) $memoType['memo_type_id'] ? 'selected' : '' ?>>
                        <?= e($memoType['type_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="inline" style="align-self:end;">
            <button class="btn btn-secondary" type="submit">入力項目を切り替え</button>
        </div>
    </form>

    <form method="post" action="<?= $mode === 'create' ? '/dictionary/store' : '/dictionary/update' ?>" class="grid">
        <?= csrf_field() ?>
        <?php if ($mode === 'edit'): ?>
            <input type="hidden" name="entry_id" value="<?= e($entry['entry_id'] ?? '') ?>">
        <?php endif; ?>
        <input type="hidden" name="memo_type_id" value="<?= e((string) $selectedMemoTypeId) ?>">

        <div class="grid grid-2">
            <div>
                <label>メモタイプ</label>
                <input type="text" value="<?= e($selectedType['type_name'] ?? '') ?>" readonly>
            </div>
            <div>
                <label>カテゴリ</label>
                <select name="category_id">
                    <option value="">選択してください</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= e($category['category_id']) ?>" <?= (string) ($entry['category_id'] ?? '') === (string) $category['category_id'] ? 'selected' : '' ?>><?= e($category['category_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label>タイトル</label>
                <input type="text" name="title" value="<?= e($entry['title'] ?? '') ?>">
            </div>
            <div>
                <label>ステータス</label>
                <select name="status">
                    <?php foreach (['draft' => '下書き', 'published' => '公開', 'archived' => 'アーカイブ'] as $value => $label): ?>
                        <option value="<?= e($value) ?>" <?= ($entry['status'] ?? 'draft') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label>優先度 (1〜5)</label>
                <input type="number" name="priority_level" min="1" max="5" value="<?= e((string) ($entry['priority_level'] ?? 3)) ?>">
            </div>
            <div>
                <label>キーワード（カンマ区切り）</label>
                <input type="text" name="keywords" value="<?= e($keywordsText ?? '') ?>">
            </div>
        </div>

        <?php if (!empty($selectedFields) && $inputMode === 'table_rows'): ?>
            <?php $tableRows = !empty($fieldRows) ? $fieldRows : [1 => []]; ?>
            <div class="inline" style="justify-content:space-between;">
                <label style="margin:0;">入力行</label>
                <button class="btn btn-secondary" type="button" id="add-row-button">行を追加</button>
            </div>
            <div style="overflow:auto;">
                <table id="field-rows-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <?php foreach ($selectedFields as $field): ?>
                                <th><?= e($field['display_label'] ?? $field['field_name']) ?><?= !empty($field['required_flag']) ? ' *' : '' ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody id="field-rows-body">
                        <?php foreach ($tableRows as $rowNo => $rowValues): ?>
                            <tr data-row-no="<?= e((string) $rowNo) ?>">
                                <td><?= e((string) $rowNo) ?></td>
                                <?php foreach ($selectedFields as $field): ?>
                                    <?php $fieldId = (int) $field['field_id']; $fieldValue = (string) ($rowValues[$fieldId] ?? ''); ?>
                                    <td>
                                        <?php if (($field['input_type'] ?? 'text') === 'textarea'): ?>
                                            <textarea name="field_rows[<?= e((string) $rowNo) ?>][<?= e((string) $fieldId) ?>]" style="min-height:100px;"><?= e($fieldValue) ?></textarea>
                                        <?php elseif (($field['input_type'] ?? 'text') === 'number'): ?>
                                            <input type="number" name="field_rows[<?= e((string) $rowNo) ?>][<?= e((string) $fieldId) ?>]" value="<?= e($fieldValue) ?>">
                                        <?php elseif (($field['input_type'] ?? 'text') === 'date'): ?>
                                            <input type="date" name="field_rows[<?= e((string) $rowNo) ?>][<?= e((string) $fieldId) ?>]" value="<?= e($fieldValue) ?>">
                                        <?php else: ?>
                                            <input type="text" name="field_rows[<?= e((string) $rowNo) ?>][<?= e((string) $fieldId) ?>]" value="<?= e($fieldValue) ?>">
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <template id="field-row-template">
                <tr data-row-no="__ROW_NO__">
                    <td>__ROW_NO__</td>
                    <?php foreach ($selectedFields as $field): ?>
                        <?php $fieldId = (int) $field['field_id']; ?>
                        <td>
                            <?php if (($field['input_type'] ?? 'text') === 'textarea'): ?>
                                <textarea name="field_rows[__ROW_NO__][<?= e((string) $fieldId) ?>]" style="min-height:100px;"></textarea>
                            <?php elseif (($field['input_type'] ?? 'text') === 'number'): ?>
                                <input type="number" name="field_rows[__ROW_NO__][<?= e((string) $fieldId) ?>]">
                            <?php elseif (($field['input_type'] ?? 'text') === 'date'): ?>
                                <input type="date" name="field_rows[__ROW_NO__][<?= e((string) $fieldId) ?>]">
                            <?php else: ?>
                                <input type="text" name="field_rows[__ROW_NO__][<?= e((string) $fieldId) ?>]">
                            <?php endif; ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            </template>
        <?php elseif (!empty($selectedFields)): ?>
            <?php foreach ($selectedFields as $field): ?>
                <?php
                $fieldId = (int) $field['field_id'];
                $fieldValue = (string) ($fieldValues[$fieldId] ?? '');
                $label = $field['display_label'] ?? $field['field_name'];
                $required = !empty($field['required_flag']);
                ?>
                <div>
                    <label><?= e($label) ?><?= $required ? ' *' : '' ?></label>
                    <?php if (($field['input_type'] ?? 'text') === 'textarea'): ?>
                        <textarea name="field_values[<?= e((string) $fieldId) ?>]"><?= e($fieldValue) ?></textarea>
                    <?php elseif (($field['input_type'] ?? 'text') === 'number'): ?>
                        <input type="number" name="field_values[<?= e((string) $fieldId) ?>]" value="<?= e($fieldValue) ?>">
                    <?php elseif (($field['input_type'] ?? 'text') === 'date'): ?>
                        <input type="date" name="field_values[<?= e((string) $fieldId) ?>]" value="<?= e($fieldValue) ?>">
                    <?php else: ?>
                        <input type="text" name="field_values[<?= e((string) $fieldId) ?>]" value="<?= e($fieldValue) ?>">
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="muted">このメモタイプには項目が設定されていません。</p>
        <?php endif; ?>

        <div class="inline">
            <button class="btn" type="submit"><?= $mode === 'create' ? '登録する' : '更新する' ?></button>
            <a class="btn btn-light" href="/dictionary">戻る</a>
        </div>
    </form>
</div>
<?php if ($inputMode === 'table_rows'): ?>
<script>
(() => {
    const addButton = document.getElementById('add-row-button');
    const body = document.getElementById('field-rows-body');
    const template = document.getElementById('field-row-template');
    if (!addButton || !body || !template) {
        return;
    }
    addButton.addEventListener('click', () => {
        const nextRowNo = body.querySelectorAll('tr').length + 1;
        const html = template.innerHTML.replaceAll('__ROW_NO__', String(nextRowNo));
        body.insertAdjacentHTML('beforeend', html);
    });
})();
</script>
<?php endif; ?>
