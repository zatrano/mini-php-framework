<?php
use Core\View\View;
?>
<?php ob_start(); ?>
<div class="row">
  <div class="col">
    <p>Bu bir demo sayfasıdır. Router, Middleware, CSRF ve SMTP örnekleri içerir.</p>
    <ul>
      <li>Parametreli rota: <code><?= htmlspecialchars(url('hello/{name}')) ?></code>, <code><?= htmlspecialchars(url('post/{id}')) ?></code></li>
      <li>CSRF Token: formlarda otomatik gizli input</li>
      <li>SMTP Mail: CC/BCC/Ek desteği</li>
      <li>Mini Blade: <code>{{ var }}</code> ve <code>@include('partial')</code></li>
    </ul>
  </div>
  <div class="col">
    <form method="post" action="<?= url('contact'); ?>" enctype="multipart/form-data">
      <?= View::csrf_field(); ?>
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
  </div>
</div>
<?php $content = ob_get_clean(); echo $content; ?>
