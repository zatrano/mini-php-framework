<?php
namespace App\Http\Controllers;

use Core\View\View;
use Core\Http\Request;
use Core\Http\Response;
use Core\Mail\SmtpMailer;

class MailController extends Controller
{
    public function form(): void
    {
        View::render('contact', [
            'title'    => 'İletişim',
            'subtitle' => 'Aşağıdaki form ile e-posta gönderebilirsiniz.'
        ]);
    }

    public function send(): void
    {
        $name  = trim($_POST['name']  ?? '');
        $email = trim($_POST['email'] ?? '');
        $msg   = trim($_POST['message'] ?? '');

        if ($name === '' || $email === '' || $msg === '') {
            http_response_code(422);
            echo "Lütfen tüm alanları doldurun.";
            return;
        }

        $cfg = include __DIR__ . '/../../../../config/mail.php';
        $mailer = new SmtpMailer(
            host: $cfg['host'],
            port: $cfg['port'],
            username: $cfg['username'],
            password: $cfg['password'],
            encryption: $cfg['encryption'],
            timeout: $cfg['timeout'],
            fromEmail: $cfg['from_email'],
            fromName: $cfg['from_name']
        );

        // Optional CC/BCC and attachments
        $cc  = isset($_POST['cc'])  && $_POST['cc'] !== ''  ? array_map('trim', explode(',', $_POST['cc'])) : [];
        $bcc = isset($_POST['bcc']) && $_POST['bcc'] !== '' ? array_map('trim', explode(',', $_POST['bcc'])) : [];

        $attachments = [];
        if (!empty($_FILES['attachment']['name'])) {
            $attachments[] = [
                'path' => $_FILES['attachment']['tmp_name'],
                'name' => $_FILES['attachment']['name'],
                'type' => $_FILES['attachment']['type'] ?? 'application/octet-stream'
            ];
        }

        $subject = "Yeni İletişim: $name";
        $html    = "<h3>Yeni mesaj</h3>
                    <p><b>İsim:</b> ".htmlspecialchars($name)."</p>
                    <p><b>E-posta:</b> ".htmlspecialchars($email)."</p>
                    <p><b>Mesaj:</b><br>".nl2br(htmlspecialchars($msg))."</p>";

        try {
            $mailer->send(
                to: [$cfg['from_email']],
                subject: $subject,
                htmlBody: $html,
                textBody: null,
                cc: $cc,
                bcc: $bcc,
                attachments: $attachments
            );
            View::render('mail-success', ['title' => 'Teşekkürler', 'subtitle' => 'Mesajınız gönderildi.']);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo "Gönderim hatası: " . htmlspecialchars($e->getMessage());
        }
    }
}
