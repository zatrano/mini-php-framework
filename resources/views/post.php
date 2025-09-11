<?php ob_start(); ?>
<p>Post ID: <b><?= htmlspecialchars((string)($id ?? '')) ?></b></p>
<?php $content = ob_get_clean(); echo $content; ?>
