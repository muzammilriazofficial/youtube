<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Email sending service with template support and PHPMailer-style SMTP.
 */
class MailService
{
    private string $driver;
    private string $host;
    private int $port;
    private string $encryption;
    private string $username;
    private string $password;
    private string $fromAddress;
    private string $fromName;

    public function __construct()
    {
        $this->driver      = $_ENV['MAIL_DRIVER'] ?? 'smtp';
        $this->host        = $_ENV['MAIL_HOST'] ?? 'smtp.mailtrap.io';
        $this->port        = (int) ($_ENV['MAIL_PORT'] ?? 587);
        $this->encryption  = $_ENV['MAIL_ENCRYPTION'] ?? 'tls';
        $this->username    = $_ENV['MAIL_USERNAME'] ?? '';
        $this->password    = $_ENV['MAIL_PASSWORD'] ?? '';
        $this->fromAddress = $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@youtube-clone.local';
        $this->fromName    = $_ENV['MAIL_FROM_NAME'] ?? 'YouTube Clone';
    }

    /**
     * Send an email.
     */
    public function send(
        string $to,
        string $subject,
        string $body,
        array $options = []
    ): bool {
        $from       = $options['from'] ?? $this->fromAddress;
        $fromName   = $options['from_name'] ?? $this->fromName;
        $isHtml     = $options['html'] ?? true;
        $replyTo    = $options['reply_to'] ?? null;
        $cc         = $options['cc'] ?? null;
        $bcc        = $options['bcc'] ?? null;
        $attachments = $options['attachments'] ?? [];

        if ($this->driver === 'log' || $this->driver === 'log') {
            return $this->logEmail($to, $subject, $body);
        }

        if ($this->driver === 'mail') {
            return $this->phpMail($to, $subject, $body, $from, $fromName, $isHtml);
        }

        return $this->smtpSend($to, $subject, $body, $from, $fromName, $isHtml, $replyTo, $cc, $bcc, $attachments);
    }

    /**
     * Send an email using a template slug.
     */
    public function sendTemplate(string $to, string $templateSlug, array $variables = []): bool
    {
        $templateDir = ROOT_PATH . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'email_templates';
        $templateFile = $templateDir . DIRECTORY_SEPARATOR . $templateSlug . '.html';

        if (!file_exists($templateFile)) {
            error_log("[MailService] Template not found: {$templateSlug}");
            return false;
        }

        $template = file_get_contents($templateFile);

        foreach ($variables as $key => $value) {
            $template = str_replace('{{' . $key . '}}', htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'), $template);
        }

        $subject = $variables['subject'] ?? ucfirst(str_replace('_', ' ', $templateSlug));

        return $this->send($to, $subject, $template, ['html' => true]);
    }

    /**
     * Send a welcome email to a new user.
     */
    public function sendWelcomeEmail(array $user): bool
    {
        $name = $user['name'] ?? $user['username'] ?? 'User';

        return $this->send(
            $user['email'],
            'Welcome to ' . ($_ENV['APP_NAME'] ?? 'YouTube Clone'),
            $this->renderEmail('welcome', [
                'name'      => $name,
                'app_name'  => $_ENV['APP_NAME'] ?? 'YouTube Clone',
                'app_url'   => $_ENV['APP_URL'] ?? 'http://localhost/youtube',
                'year'      => date('Y'),
            ])
        );
    }

    /**
     * Send an email verification link.
     */
    public function sendVerificationEmail(array $user, string $token): bool
    {
        $verifyUrl = url("verify-email?token={$token}");

        return $this->send(
            $user['email'],
            'Verify your email address',
            $this->renderEmail('verification', [
                'name'     => $user['name'] ?? $user['username'],
                'url'      => $verifyUrl,
                'app_name' => $_ENV['APP_NAME'] ?? 'YouTube Clone',
                'year'     => date('Y'),
            ])
        );
    }

    /**
     * Send a password reset email.
     */
    public function sendPasswordReset(array $user, string $token): bool
    {
        $resetUrl = url("reset-password?token={$token}");

        return $this->send(
            $user['email'],
            'Reset your password',
            $this->renderEmail('password_reset', [
                'name'     => $user['name'] ?? $user['username'],
                'url'      => $resetUrl,
                'app_name' => $_ENV['APP_NAME'] ?? 'YouTube Clone',
                'year'     => date('Y'),
            ])
        );
    }

    /**
     * Send a notification digest email.
     */
    public function sendNotificationEmail(array $user, array $notification): bool
    {
        return $this->send(
            $user['email'],
            $notification['title'] ?? 'New Notification',
            $this->renderEmail('notification', [
                'name'     => $user['name'] ?? $user['username'],
                'title'    => $notification['title'] ?? '',
                'message'  => $notification['message'] ?? '',
                'app_name' => $_ENV['APP_NAME'] ?? 'YouTube Clone',
                'year'     => date('Y'),
            ])
        );
    }

    /**
     * Send a support ticket reply email.
     */
    public function sendTicketReply(array $ticket, array $reply): bool
    {
        $to = $ticket['user_email'] ?? $ticket['email'] ?? '';

        if (empty($to)) {
            return false;
        }

        return $this->send(
            $to,
            'Re: ' . ($ticket['subject'] ?? 'Support Ticket #' . $ticket['id']),
            $this->renderEmail('ticket_reply', [
                'ticket_id'   => $ticket['id'],
                'subject'     => $ticket['subject'] ?? '',
                'message'     => $reply['message'] ?? '',
                'agent_name'  => $reply['agent_name'] ?? 'Support Team',
                'app_name'    => $_ENV['APP_NAME'] ?? 'YouTube Clone',
                'year'        => date('Y'),
            ])
        );
    }

    /**
     * Render an email template with variables.
     */
    private function renderEmail(string $template, array $variables = []): string
    {
        $templateDir = ROOT_PATH . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'email_templates';
        $file        = $templateDir . DIRECTORY_SEPARATOR . $template . '.html';

        if (!file_exists($file)) {
            $body = '<p>' . ($variables['message'] ?? '') . '</p>';
            foreach ($variables as $key => $value) {
                if ($key !== 'message') {
                    $body .= "<p><strong>{$key}:</strong> " . htmlspecialchars((string) $value) . "</p>";
                }
            }
            return $body;
        }

        $html = file_get_contents($file);
        foreach ($variables as $key => $value) {
            $html = str_replace('{{' . $key . '}}', htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'), $html);
        }

        return $html;
    }

    /**
     * Send via PHP mail() function.
     */
    private function phpMail(string $to, string $subject, string $body, string $from, string $fromName, bool $isHtml): bool
    {
        $headers  = "From: {$fromName} <{$from}>\r\n";
        $headers .= "Reply-To: {$from}\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= $isHtml ? "Content-Type: text/html; charset=UTF-8\r\n" : "Content-Type: text/plain; charset=UTF-8\r\n";

        return mail($to, $subject, $body, $headers);
    }

    /**
     * Send via SMTP using raw sockets.
     */
    private function smtpSend(
        string $to,
        string $subject,
        string $body,
        string $from,
        string $fromName,
        bool $isHtml,
        ?string $replyTo,
        ?string $cc,
        ?string $bcc,
        array $attachments
    ): bool {
        $errno = 0;
        $errstr = '';

        $host = $this->encryption === 'ssl' ? "ssl://{$this->host}" : $this->host;
        $connection = fsockopen($host, $this->port, $errno, $errstr, 30);

        if (!$connection) {
            error_log("[MailService] SMTP connection failed: {$errstr}");
            return false;
        }

        $response = fgets($connection, 512);

        $this->smtpCommand($connection, "EHLO localhost");
        if ($this->encryption === 'tls') {
            $this->smtpCommand($connection, "STARTTLS");
            stream_socket_enable_crypto($connection, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT);
            $this->smtpCommand($connection, "EHLO localhost");
        }

        if (!empty($this->username)) {
            $this->smtpCommand($connection, "AUTH LOGIN");
            $this->smtpCommand($connection, base64_encode($this->username));
            $this->smtpCommand($connection, base64_encode($this->password));
        }

        $this->smtpCommand($connection, "MAIL FROM:<{$from}>");
        $this->smtpCommand($connection, "RCPT TO:<{$to}>");

        if (!empty($cc)) {
            $this->smtpCommand($connection, "RCPT TO:<{$cc}>");
        }
        if (!empty($bcc)) {
            $this->smtpCommand($connection, "RCPT TO:<{$bcc}>");
        }

        $this->smtpCommand($connection, "DATA");

        $eol    = "\r\n";
        $headers  = "From: {$fromName} <{$from}>{$eol}";
        $headers .= "To: {$to}{$eol}";
        $headers .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?={$eol}";
        $headers .= "Date: " . date('r') . "{$eol}";
        $headers .= "MIME-Version: 1.0{$eol}";
        $headers .= $isHtml
            ? "Content-Type: text/html; charset=UTF-8{$eol}"
            : "Content-Type: text/plain; charset=UTF-8{$eol}";

        if (!empty($replyTo)) {
            $headers .= "Reply-To: {$replyTo}{$eol}";
        }

        $this->smtpCommand($connection, $headers . $eol . $body . $eol . '.', true);
        $this->smtpCommand($connection, "QUIT");

        fclose($connection);

        return true;
    }

    /**
     * Execute an SMTP command and check the response.
     */
    private function smtpCommand($connection, string $command, bool $raw = false): string
    {
        fwrite($connection, $command . "\r\n");
        $response = '';

        do {
            $line = fgets($connection, 512);
            $response .= $line;
        } while ($line !== false && $line[3] === ' ');

        return $response;
    }

    /**
     * Log email instead of sending (development mode).
     */
    private function logEmail(string $to, string $subject, string $body): bool
    {
        $logDir  = ROOT_PATH . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $logFile = $logDir . DIRECTORY_SEPARATOR . 'mail.log';
        $entry   = date('Y-m-d H:i:s') . " | To: {$to} | Subject: {$subject}\n";

        return file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX) !== false;
    }
}
