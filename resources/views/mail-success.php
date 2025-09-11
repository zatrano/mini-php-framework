<?php ob_start(); ?>
<p>Teşekkürler! Mesajınız başarıyla gönderildi.</p>
<p><a href="/">← Ana sayfaya dön</a></p>
<?php $content = ob_get_clean(); echo $content; ?>
