<?php
namespace Core\Mail;

class SmtpMailer
{
    public function __construct(
        private string $host,
        private int $port = 587,
        private string $username = '',
        private string $password = '',
        private string $encryption = 'tls', // none|ssl|tls
        private int $timeout = 20,
        private string $fromEmail = 'noreply@example.com',
        private string $fromName  = 'App'
    ) {}

    public function send(
        array|string $to,
        string $subject,
        string $htmlBody,
        ?string $textBody = null,
        array $cc = [],
        array $bcc = [],
        array $attachments = [] // each: ['path'=>..., 'name'=>optional, 'type'=>optional]
    ): bool {
        $boundaryMixed = 'mixed_' . bin2hex(random_bytes(8));
        $boundaryAlt   = 'alt_' . bin2hex(random_bytes(8));

        // Headers
        $headers = [];
        $headers[] = 'From: ' . $this->formatAddress($this->fromEmail, $this->fromName);
        $headers[] = 'Subject: ' . $this->encodeHeader($subject);
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-Type: multipart/mixed; boundary="' . $boundaryMixed . '"';

        $toList  = (array)$to;
        $headers[] = 'To: ' . implode(', ', array_map([$this, 'formatAddress'], $toList));

        if (!empty($cc)) {
            $headers[] = 'Cc: ' . implode(', ', array_map([$this, 'formatAddress'], (array)$cc));
        }
        if (!empty($bcc)) {
            $headers[] = 'Bcc: ' . implode(', ', array_map([$this, 'formatAddress'], (array)$bcc));
        }

        $textBody = $textBody ?: strip_tags($htmlBody);

        // Build body
        $body  = "--$boundaryMixed\r\n";
        $body .= "Content-Type: multipart/alternative; boundary=\"$boundaryAlt\"\r\n\r\n";

        // Text part
        $body .= "--$boundaryAlt\r\n";
        $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
        $body .= $textBody . "\r\n\r\n";

        // HTML part
        $body .= "--$boundaryAlt\r\n";
        $body .= "Content-Type: text/html; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
        $body .= $htmlBody . "\r\n\r\n";
        $body .= "--$boundaryAlt--\r\n";

        // Attachments
        foreach ($attachments as $att) {
            $path = $att['path'] ?? null;
            if (!$path || !is_readable($path)) continue;
            $name = $att['name'] ?? basename($path);
            $type = $att['type'] ?? (mime_content_type($path) ?: 'application/octet-stream');
            $data = chunk_split(base64_encode(file_get_contents($path)));

            $body .= "--$boundaryMixed\r\n";
            $body .= "Content-Type: $type; name=\"".$this->encodeHeader($name)."\"\r\n";
            $body .= "Content-Transfer-Encoding: base64\r\n";
            $body .= "Content-Disposition: attachment; filename=\"".$this->encodeHeader($name)."\"\r\n\r\n";
            $body .= $data . "\r\n";
        }
        $body .= "--$boundaryMixed--\r\n";

        return $this->smtpSend($toList, $headers, $body);
    }

    private function smtpSend(array $recipients, array $headers, string $body): bool
    {
        $remote = $this->encryption === 'ssl' ? "ssl://{$this->host}:{$this->port}" : "{$this->host}:{$this->port}";
        $fp = @stream_socket_client($remote, $errno, $errstr, $this->timeout, STREAM_CLIENT_CONNECT);
        if (!$fp) throw new \RuntimeException("SMTP connect failed: $errstr ($errno)");
        $this->expect($fp, 220);

        $this->write($fp, "EHLO localhost\r\n");
        $this->expectMulti($fp, 250);

        if ($this->encryption === 'tls') {
            $this->write($fp, "STARTTLS\r\n");
            $this->expect($fp, 220);
            if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new \RuntimeException("Failed to enable TLS");
            }
            $this->write($fp, "EHLO localhost\r\n");
            $this->expectMulti($fp, 250);
        }

        if ($this->username !== '') {
            $this->write($fp, "AUTH LOGIN\r\n");
            $this->expect($fp, 334);
            $this->write($fp, base64_encode($this->username) . "\r\n");
            $this->expect($fp, 334);
            $this->write($fp, base64_encode($this->password) . "\r\n");
            $this->expect($fp, 235);
        }

        $this->write($fp, "MAIL FROM:<{$this->fromEmail}>\r\n");
        $this->expect($fp, 250);

        foreach ($recipients as $rcpt) {
            $rcpt = preg_replace('/^.*<(.+?)>.*$/', '$1', $rcpt);
            $this->write($fp, "RCPT TO:<{$rcpt}>\r\n");
            $this->expect($fp, 250);
        }

        $this->write($fp, "DATA\r\n");
        $this->expect($fp, 354);

        $data = implode("\r\n", $headers) . "\r\n\r\n" . $body . "\r\n.";
        $this->write($fp, $data . "\r\n");
        $this->expect($fp, 250);

        $this->write($fp, "QUIT\r\n");
        fclose($fp);
        return true;
    }

    private function write($fp, string $data): void { fwrite($fp, $data); }

    private function expect($fp, int $code): void {
        $line = $this->getline($fp);
        if ((int)substr($line, 0, 3) !== $code) {
            throw new \RuntimeException("SMTP error, expected $code, got: $line");
        }
    }

    private function expectMulti($fp, int $code): void
    {
        do {
            $line = $this->getline($fp);
            $ok   = ((int)substr($line, 0, 3) === $code);
            $cont = (isset($line[3]) && $line[3] === '-');
            if (!$ok) throw new \RuntimeException("SMTP error, expected $code, got: $line");
        } while ($cont);
    }

    private function getline($fp): string
    {
        $line = '';
        while (($str = fgets($fp, 515)) !== false) {
            $line .= $str;
            if (isset($str[3]) && $str[3] === ' ') break;
            if (!isset($str[3])) break;
        }
        return $line;
    }

    private function encodeHeader(string $text): string
    {
        return '=?UTF-8?B?'.base64_encode($text).'?=';
    }

    private function formatAddress(string $email, ?string $name=null): string
    {
        if ($name && $name !== '') {
            return $this->encodeHeader($name) . " <{$email}>";
        }
        return $email;
    }
}
