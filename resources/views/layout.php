<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= isset($title) ? htmlspecialchars($title) : 'Mini' ?></title>
<style>
  body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;margin:0;background:#0f172a;color:#e2e8f0}
  header,main,footer{max-width:1000px;margin:auto;padding:24px}
  header h1{margin:0 0 8px;font-size:28px}
  a,button,input,textarea{font:inherit}
  .card{background:#111827;border:1px solid #1f2937;border-radius:12px;padding:20px}
  .muted{color:#94a3b8}
  label{display:block;margin:10px 0 6px}
  input,textarea{width:100%;padding:10px;border-radius:8px;border:1px solid #334155;background:#0b1220;color:#e2e8f0}
  button{padding:10px 14px;border:0;border-radius:8px;background:#6366f1;color:white;cursor:pointer}
  button:hover{opacity:.9}
  .row{display:flex;gap:16px;flex-wrap:wrap}
  .col{flex:1 1 320px}
  nav a{color:#93c5fd;margin-right:14px;text-decoration:none}
  code{background:#0b1220;padding:2px 6px;border-radius:6px}
</style>
</head>
<body>
<header>
  <h1><?= htmlspecialchars($title ?? 'Mini Framework') ?></h1>
  <div class="muted"><?= htmlspecialchars($subtitle ?? '') ?></div>
  <nav style="margin-top:12px">
    <a href="<?= url(''); ?>">Ana Sayfa</a>
    <a href="<?= url('contact'); ?>">İletişim</a>
    <a href="<?= url('hello/World'); ?>">Hello</a>
    <a href="<?= url('post/123'); ?>">Post</a>
  </nav>
</header>
<main>
  <div class="card">
    <?php echo $content ?? ''; ?>
  </div>
</main>
<footer class="muted" style="text-align:center;padding-bottom:40px">
  <?= date('Y') ?> • Mini Framework
</footer>
</body>
</html>
