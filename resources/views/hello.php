<?php ob_start(); ?>
<p>Merhaba, <b><?= htmlspecialchars($name ?? 'Anonim') ?></b>!</p>
<?php $content = ob_get_clean(); echo $content; ?>
