
# Mini PHP Framework (Composer’sız) – Template Giydirme Rehberi

Bu doküman, gönderdiğim **mini PHP framework** (composer’sız, DB’siz, SMTP’li) üzerine **hazır bir HTML template’i giydirerek** hızlıca web sitesi yapmanız için adım adım bir rehberdir.  
Framework; **alt klasörde** (örn. `/test`) ya da **kök dizinde** çalışacak şekilde tasarlandı.

---

## 0) Sistem Gereksinimleri
- PHP 7.2+ (8.x önerilir)
- Apache (mod_rewrite açık). Nginx için örnek yapılandırma aşağıda.
- Paylaşımlı hosting (InfinityFree gibi) için ek ayarlar **gerekmez**; `.htaccess` projenin içindedir.

---

## 1) Dizine Kopyalama
- Projeyi sunucuda köke (`/`) ya da bir alt klasöre (örn. `/test`) **aynı hiyerarşi** ile yükleyin.
- Mevcut `.htaccess` dosyası şunları yapar:
  - Var olan statik dosyaları **doğrudan** sunar (CSS/JS/IMG vs).
  - Diğer tüm istekleri `index.php`ye yönlendirir **ve** yolu `?__url=` parametresiyle iletir (host bağımsız).

> **Not:** `RewriteBase` satırı **gerekli değildir**. Framework kendi base path’ini otomatik bulur.

---

## 2) Klasör Yapısı (önerilen)
```
/ (veya /test)
├─ .htaccess
├─ index.php
├─ app/
├─ core/
├─ config/
├─ resources/
│  └─ views/
│     ├─ layout.php
│     ├─ home.php
│     ├─ contact.php
│     ├─ hello.php
│     ├─ post.php
│     └─ partials/           ← (header.php, footer.php gibi kendi parçalarınız)
└─ public/                    ← (yeni) statik varlıklarınız
   ├─ css/
   ├─ js/
   └─ img/
```

- **public/** klasörünü siz oluşturabilirsiniz. `.htaccess` zaten var olan dosyaları doğrudan sunar.
- Tüm statik dosyalarınızı (template’in CSS/JS/IMG) buraya koymanız tavsiye edilir.

---

## 3) URL Yardımcıları (alt klasöre uyumlu)
- `url('contact')` → bulunduğunuz klasöre göre `/contact` **ya da** `/test/contact` üretir.
- `asset('public/css/app.css')` → `/public/css/app.css` **ya da** `/test/public/css/app.css` üretir.

Bu sayede projenizi kökten alt klasöre taşısanız bile linkler **bozulmaz**.

Örnek (HTML’de):
```php
<link rel="stylesheet" href="<?= asset('public/css/style.css'); ?>">
<a href="<?= url('hakkimizda'); ?>">Hakkımızda</a>
<script src="<?= asset('public/js/app.js'); ?>"></script>
<img src="<?= asset('public/img/logo.png'); ?>" alt="logo">
```

---

## 4) Template Giydirme – Adım Adım

### Adım 4.1 – Template dosyalarını yerleştir
- Hazır HTML template’inizin **CSS/JS/IMG** dosyalarını `/public` altına kopyalayın:
  - `/public/css/...`
  - `/public/js/...`
  - `/public/img/...`

### Adım 4.2 – `layout.php` içine iskeleti taşıyın
- Template’in `index.html` dosyasındaki **`<html> ... </html>`** iskeletini `resources/views/layout.php` içine alın.
- `<link>` / `<script>` / `<img>` yollarını `asset()` ile değiştirin.
- Navigasyon bağlantılarını `url()` ile değiştirin.
- İçerik alanına **`<?= $content ?? '' ?>`** bırakın (view’ların gövdesi burada görünecek).

Minimal örnek (`layout.php`):
```php
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($title ?? 'Site') ?></title>
  <link rel="stylesheet" href="<?= asset('public/css/style.css'); ?>">
</head>
<body>
  <?php // header/nav alanınız ?>
  <?php // ... ?>
  <main>
    <?= $content ?? '' ?>
  </main>
  <?php // footer alanınız ?>
  <script src="<?= asset('public/js/app.js'); ?>"></script>
</body>
</html>
```

> İsterseniz `resources/views/partials/header.php` ve `partials/footer.php` oluşturup `layout.php` içinde `include` edebilirsiniz.

### Adım 4.3 – Sayfa view’larını oluşturun
- Her sayfa için `resources/views/` altında bir **PHP view** dosyası oluşturun (örn. `hakkimizda.php`).
- Bu view dosyaları **sadece içerik bölümünü** taşımalı; `layout.php` iskeleti zaten var.

Örnek (`resources/views/hakkimizda.php`):
```php
<?php ob_start(); ?>
<section class="hero">
  <div class="container">
    <h1>Hakkımızda</h1>
    <p>Şirketiniz hakkında açıklama metni.</p>
  </div>
</section>
<?php $content = ob_get_clean(); echo $content; ?>
```

### Adım 4.4 – Rotaları tanımlayın
`routes/web.php` içerisine sayfanızın rotasını ekleyin:
```php
$router->get('/hakkimizda', function() {
    // sayfa başlığı & alt başlık gibi değişkenleri layout’a geçmek isterseniz:
    \Core\View\View::render('hakkimizda', [
        'title'    => 'Hakkımızda',
        'subtitle' => 'Biz kimiz?'
    ]);
});
```

> Parametreli rotalar: `$router->get('/urun/{slug}', [ProductController::class, 'show']);`  
> Regex’li örnek: `$router->get('/post/{id:\d+}', [HomeController::class, 'showPost']);`

### Adım 4.5 – Controller ile sayfa
İsterseniz controller kullanın:
```php
use App\Http\Controllers\PageController;
$router->get('/iletisim', [PageController::class, 'contact']);
```

`app/Http/Controllers/PageController.php`:
```php
<?php
namespace App\Http\Controllers;

use Core\View\View;

class PageController extends Controller
{
    public function contact(): void
    {
        View::render('contact', [
            'title' => 'İletişim'
        ]);
    }
}
```

---

## 5) Formlar ve CSRF
- Formlarınıza **CSRF token** otomatik ekleyin: `<?= \Core\View\View::csrf_field(); ?>`
- POST rotasında `VerifyCsrfToken` middleware’i kullanın.

Örnek rota (`routes/web.php`):
```php
use App\Http\Middleware\VerifyCsrfToken;

$router->post('/contact', [MailController::class, 'send'])
       ->middleware([VerifyCsrfToken::class]);
```

Form örneği (`contact.php`):
```php
<form method="post" action="<?= url('contact'); ?>" enctype="multipart/form-data">
  <?= \Core\View\View::csrf_field(); ?>
  <!-- alanlar -->
</form>
```

---

## 6) SMTP ile E-posta Gönderimi
`config/mail.php` içini doldurun:
```php
return [
  'host'       => 'smtp.example.com',
  'port'       => 587,
  'username'   => 'kullanici',
  'password'   => 'sifre',
  'encryption' => 'tls', // none|ssl|tls
  'from_email' => 'noreply@alanadiniz.com',
  'from_name'  => 'Site Adı',
  'timeout'    => 20,
];
```

Kullanım (MailController örneği hazırdır): CC/BCC/Ek destekli.
```php
$mailer->send(
  to: ['destek@alanadiniz.com'],
  subject: 'İletişim Formu',
  htmlBody: $html,
  cc: ['cc@alanadiniz.com'],
  bcc: ['gizli@alanadiniz.com'],
  attachments: [
    ['path' => $_FILES['attachment']['tmp_name'], 'name' => $_FILES['attachment']['name']]
  ]
);
```

---

## 7) Middleware
- Rota bazlı middleware eklemek için route tanımında `->middleware([Class::class])` kullanın.
- Örnek CSRF middleware’i `App\Http\Middleware\VerifyCsrfToken`:

```php
class VerifyCsrfToken {
  public function handle($req, $res, $next) {
    $token = $_POST['_csrf'] ?? '';
    $ok = isset($_SESSION['_csrf']) && hash_equals($_SESSION['_csrf'], $token);
    if (!$ok) { http_response_code(419); echo 'CSRF token mismatch.'; return null; }
    return $next();
  }
}
```

Global middleware’leri `App\Http\Middleware\Kernel::register()` içinde tanımlayabilirsiniz.

---

## 8) Nginx Konfig (Apache yerine)
Kök için:
```nginx
location / {
  try_files $uri $uri/ /index.php?$query_string;
}
```
Alt klasör (örn. `/test`) için server blok içinde:
```nginx
location /test/ {
  try_files $uri $uri/ /test/index.php?$query_string;
}
```

---

## 9) Sorun Giderme
- **404**: `.htaccess` okunuyor mu? (Tanılama index’i daha önce OK verdi.)  
  `routes/web.php`’de rota var mı? `Request::path()` alt klasörünüz için doğru çalışır (v3+).
- **Sınıf bulunamadı**: Birebir yol ve isim alanı (`namespace`) eşleşmeli. Linux’ta `Http`/`HTTP` gibi **case-sensitive**.
- **Parse error / BOM**: PHP dosyalarını **UTF-8 (BOM’suz)** kaydedin. Kısa etiketler `<?= ... ?>` desteklenir.
- **SMTP hatası**: Sunucu bağlantısı veya kimlik doğrulama. `config/mail.php` değerlerini ve port/SSL ayarını kontrol edin.
- **Statik varlıklar yüklenmiyor**: Dosyaları `/public` altına koyun ve `asset()` ile çağırın. `.htaccess` statik dosyaları doğrudan verir.

---

## 10) Faydalı İpuçları
- Yeni sayfa eklemek: **view dosyasını** oluştur → **route** ekle → **nav’a link** koy.  
- Template’in her sayfada ortak kısımlarını **layout** veya **partials** olarak kullanın.  
- İsterseniz `\Core\View\View::blade($templateString, $data)` ile minimal Blade-benzeri sonuç üretebilirsiniz (gelişmiş şablonlama ihtiyaçları için).

---

## 11) Örnek Akış (Hızlı Kurulum)
1. Template varlıklarını `/public` altına kopyalayın.  
2. `layout.php` içine HTML iskeletinizi taşıyın; linkleri `asset()` ve `url()` ile değiştirin.  
3. Her sayfa için bir view (ör. `hizmetler.php`) oluşturun.  
4. `routes/web.php`‘ye rota ekleyin.  
5. Gerekirse bir controller yazın.  
6. Form kullanıyorsanız CSRF alanını ekleyin ve POST rotasına CSRF middleware’i bağlayın.  
7. SMTP yapılandırmasını yapıp iletişim formunu test edin.

---

Hazır! Artık template’iniz framework üzerinde alt klasörde de problemsiz çalışacaktır. 
