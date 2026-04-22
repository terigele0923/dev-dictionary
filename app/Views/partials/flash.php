<?php
$success = \App\Helpers\Flash::get('success');
$error = \App\Helpers\Flash::get('error');
?>
<?php if ($success): ?><div class="flash flash-success"><?= e($success) ?></div><?php endif; ?>
<?php if ($error): ?><div class="flash flash-error"><?= e($error) ?></div><?php endif; ?>
