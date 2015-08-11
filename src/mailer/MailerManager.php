<?php

class MailerManager {

    private $template = 'email_template';
    private $to;
    private $subject;
    private $message;
    private $dumpEmailOnScreen;

    /**
     * @var CupRenderer
     */
    private $renderer;
    private $config;

    public function __construct(array $config, CupRenderer $renderer, $dumpMailOnScreen = false) {
        $this->renderer = $renderer;
        $this->config = $config;
        $this->dumpEmailOnScreen = $dumpMailOnScreen;
    }

    public function enviaEmail($dados, $to, $subject = 'Contato através do site', $viewEmail = 'email/contato') {
        $this->to = $to;
        $this->subject = $subject;
        $this->message = $this->getRenderedEmail($viewEmail, array('dados' => $dados, 'subject' => $this->subject));
        return $this->send();
    }

    private function getRenderedEmail($view, $dados) {
        $this->getRenderer()->setTemplate($this->template);
        return $this->getRenderer()->renderizar($view, $dados, true);
    }

    private function send() {
        $mail = $this->getMailer();
        $mail->addAddress($this->to);
        $mail->addReplyTo($this->to);
        $mail->CharSet = 'UTF-8';
        $mail->isHTML(true);

        $mail->Subject = $this->subject;
        $mail->Body = $this->message;

        if (true == $this->dumpEmailOnScreen) {
            header('Content-Type: text / html;charset = utf-8');
            die($mail->Body);
        }
        return $mail->send();
    }

    public function getMailer() {
        $mail = new PHPMailer(true);
        if ($this->config['isSMTP']) {
            $mail->SMTPDebug = $this->config['SMTPDebug'];                               // Enable verbose debug output
            $mail->isSMTP();                                      // Set mailer to use SMTP
            $mail->Host = $this->config['Host'];  // Specify main and backup SMTP servers
            $mail->SMTPAuth = $this->config['SMTPAuth'];                               // Enable SMTP authentication
            $mail->Username = $this->config['Username'];                 // SMTP username
            $mail->Password = $this->config['Password'];                           // SMTP password
            if (isset($this->config['SMTPSecure'])) {
                $mail->SMTPSecure = $this->config['SMTPSecure'];                            // Enable TLS encryption, `ssl` also accepted
            }
            $mail->Port = $this->config['Port'];                                    // TCP port to connect to
            $mail->From = $this->config['From'];
            $mail->FromName = $this->config['FromName'];
        } else {
            $mail->From = $this->config['From'];
            $mail->FromName = $this->config['FromName'];
            $mail->isMail();
        }
        return $mail;
    }

    /**
     * @return CupRenderer
     */
    private function getRenderer() {
        return $this->renderer;
    }

}
