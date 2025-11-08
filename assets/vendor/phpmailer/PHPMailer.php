<?php
namespace PHPMailer\PHPMailer;

class PHPMailer
{
    public $Host;
    public $SMTPAuth = true;
    public $Username;
    public $Password;
    public $SMTPSecure = 'tls';
    public $Port = 587;
    public $From;
    public $FromName;
    public $Subject;
    public $Body;
    public $AltBody;

    private $to = [];
    private $html = false;

    /**
     * Set method to HTML
     */
    public function isHTML($bool = true) {
        $this->html = $bool;
    }

    /**
     * Set sender email & name
     */
    public function setFrom($email, $name = '') {
        $this->From = $email;
        $this->FromName = $name;
    }

    /**
     * Dummy SMTP function
     */
    public function isSMTP() {
        // kosong untuk versi simple (pakai mail())
    }

    /**
     * Add recipient
     */
    public function addAddress($email, $name = '') {
        $this->to[] = [$email, $name];
    }

    /**
     * Send email
     */
    public function send() {
        $headers = "From: {$this->FromName} <{$this->From}>\r\n";
        $headers .= "MIME-Version: 1.0\r\n";

        if ($this->html) {
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $body = $this->Body;
        } else {
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
            $body = strip_tags($this->Body);
        }

        $success = true;
        foreach ($this->to as $recipient) {
            $success = $success && mail($recipient[0], $this->Subject, $body, $headers);
        }

        return $success;
    }
}
?>
