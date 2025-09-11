<?php
use Core\View\View;
?>
<?php ob_start(); ?>
<form method="post" action="<?= url('contact'); ?>" enctype="multipart/form-data">
  <?= \Core\View\View::csrf_field(); ?>
  <label>İsim</label>
  <input name="name" required>
  <label>E-posta</label>
  <input type="email" name="email" required>
  <label>Mesaj</label>
  <textarea name="message" rows="5" required></textarea>
  <label>CC (virgülle ayırın)</label>
  <input name="cc" placeholder="cc1@ex.com, cc2@ex.com">
  <label>BCC (virgülle ayırın)</label>
  <input name="bcc" placeholder="bcc1@ex.com">
  <label>Ek dosya</label>
  <input type="file" name="attachment">
  <div style="margin-top:10px"><button type="submit">Gönder</button></div>
</form>
<?php $content = ob_get_clean(); echo $content; ?>
