<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 05/06/2017
 * Time: 20:18
 */

namespace Framework\Mail;

require_once __DIR__ . "/../Framework/vendor/autoload.php";

use Framework\Config;
use Framework\Exceptions\{MailMustBeEncryptedError, SectionNotDefined};


final class Mailer {
    private $mailer;

    public function __construct() {
        if(Config::instance()->main == null)
            throw new SectionNotDefined("Section 'Mail' must be defined in 'config.ini'");

        if(Config::instance()->mail->encryption == null)
            throw new MailMustBeEncryptedError("Mail must be encrypted with either 'ssl' or 'tls'. \n This can be set in config.ini");

        switch (Config::instance()->mail->encryption) {
            case "ssl":
                $port = 465;
                $security = "ssl";
                break;
            case "tls":
                $port = 587;
                $security = "tls";
                break;
            default:
                throw new MailMustBeEncryptedError("'" . Config::instance()->mail->encryption . "' is not a encryption method but be either 'ssl' or 'tls'");
        }

        $transport = (
            new \Swift_SmtpTransport(
                Config::instance()->mail->host,
                $port,
                $security
            )
        );

        $transport
            ->setUsername(Config::instance()->mail->username)
            ->setPassword(Config::instance()->mail->password);

        $this->mailer = new \Swift_Mailer($transport);
    }

    /**
     * @param string $subject - Subject of the Mail
     * @return \Swift_Message - Returns a Swift_Message object
     */
    public function getSwiftMessage($subject) {
        return new \Swift_Message($subject);
    }

    /**
     * @param \Swift_Message $message - Message object to be sent
     * @return int - Returns the result of sending the Mail
     */
    public function send($message) {
        return $this->mailer->send($message);
    }
}