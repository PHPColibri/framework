<?php
namespace Colibri\Mail;

use Colibri\View\PhpTemplate;
use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;

class Mail
{
    /** @var array */
    private static $config = [];

    /** @var \Swift_Mailer */
    private static $mailer = null;

    /**
     * @param array $config
     */
    public static function setConfig(array $config)
    {
        self::$config = array_replace_recursive(self::$config, $config); // array overwrite
    }

    /**
     * @param string   $to
     * @param string   $subject
     * @param string   $view
     * @param array    $viewVars
     * @param callable $builder
     * @param string   $from
     *
     * @throws \Swift_SwiftException
     */
    public static function send(string $to, string $subject, string $view, array $viewVars = [], $builder = null, $from = 'default')
    {
        $message = static::createMessage($to, $subject, $from);
        if (is_callable($builder)) {
            $builder($message);
        }
        $message->setBody(static::view($view, $viewVars));

        self::getMailer()->send($message);
    }

    /**
     * @return \Swift_Mailer
     */
    final protected static function getMailer(): Swift_Mailer
    {
        return self::$mailer === null
            ? self::$mailer = static::createMailer()
            : self::$mailer;
    }

    /**
     * @return \Swift_Mailer
     */
    protected static function createMailer(): Swift_Mailer
    {
        $class  = self::$config['transport']['driver'] ?? Swift_SmtpTransport::class;
        $params = self::$config['transport']['params'] ?? [];

        /** @var \Swift_SmtpTransport $transport */
        $transport = new $class(...array_values($params));
        if (isset(self::$config['transport']['username'])) {
            $transport->setUsername(self::$config['transport']['username']);
        }
        if (isset(self::$config['transport']['password'])) {
            $transport->setPassword(self::$config['transport']['password']);
        }

        return new Swift_Mailer($transport);
    }

    /**
     * @param string $name
     * @param array  $vars
     *
     * @return string
     */
    protected static function view(string $name, array $vars): string
    {
        static $path = null;
        $path        = $path ?? (
                self::$config['views'] ?? realpath(dirname(__DIR__ . '/../../../application/') . 'templates/mail')
            );

        return (new PhpTemplate("$path/$name.php"))
            ->setVars($vars)
            ->compile()
            ;
    }

    /**
     * @param string $to
     * @param string $subject
     * @param string $from
     *
     * @return \Swift_Message
     *
     * @throws \Swift_DependencyException
     * @throws \Swift_SwiftException
     */
    protected static function createMessage(string $to, string $subject, string $from = 'default'): Swift_Message
    {
        $fromName = $from === 'default' ? self::$config['from']['default'] : $from;
        $from     = self::$config['from'][$fromName];

        return (new Swift_Message())
            ->setContentType('text/html')
            ->setSubject($subject)
            ->setFrom([$from['address'] => $from['name']])
            ->setTo($to)
            ;
    }
}
