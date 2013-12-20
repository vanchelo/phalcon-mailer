<?php namespace Vanchelo\Mailer;

use Phalcon\Mvc\User\Component;
use Swift_Mailer;
use Swift_SmtpTransport as SmtpTransport;
use Swift_MailTransport as MailTransport;
use Swift_SendmailTransport as SendmailTransport;
use Phalcon\DI;
use Phalcon\Mvc\View\Simple as SimpleView;

class MailerService extends Component
{
    /**
     * Create a new service provider instance.
     *
     * @param  \Phalcon\DI $app
     * @return void
     */
    public function __construct()
    {
        $this->registerSwiftMailer();
        $this->registerView();
    }

    public function mailer()
    {
        // Once we have create the mailer instance, we will set a container instance
        // on the mailer. This allows us to resolve mailer classes via containers
        // for maximum testability on said classes instead of passing Closures.
        $mailer = new Mailer($this->di['mailer.view'], $this->di['swift.mailer']);

        $from = $this->di['config']->mail->from->toArray();

        if (is_array($from) and isset($from['address']))
        {
            $mailer->alwaysFrom($from['address'], $from['name']);
        }

        return $mailer;
    }

    /**
     * Register the Swift Mailer instance.
     *
     * @return void
     */
    protected function registerSwiftMailer()
    {
        $config = $this->di['config']->mail->toArray();

        $this->registerSwiftTransport($config);

        // Once we have the transporter registered, we will register the actual Swift
        // mailer instance, passing in the transport instances, which allows us to
        // override this transporter instances during app start-up if necessary.
        $this->di['swift.mailer'] = function()
        {
            return new Swift_Mailer($this->di['swift.transport']);
        };
    }

    /**
     * Register the Swift Transport instance.
     *
     * @param  array  $config
     * @return void
     */
    protected function registerSwiftTransport($config)
    {
        switch ($config['driver'])
        {
            case 'smtp':
                return $this->registerSmtpTransport($config);

            case 'sendmail':
                return $this->registerSendmailTransport($config);

            case 'mail':
                return $this->registerMailTransport($config);

            default:
                throw new \InvalidArgumentException('Invalid mail driver.');
        }
    }

    /**
     * Register the SMTP Swift Transport instance.
     *
     * @param  array  $config
     * @return void
     */
    protected function registerSmtpTransport($config)
    {
        $this->di['swift.transport'] = function() use ($config)
        {
            extract($config);

            // The Swift SMTP transport instance will allow us to use any SMTP backend
            // for delivering mail such as Sendgrid, Amazon SMS, or a custom server
            // a developer has available. We will just pass this configured host.
            $transport = SmtpTransport::newInstance($host, $port);

            if (isset($encryption))
            {
                $transport->setEncryption($encryption);
            }

            // Once we have the transport we will check for the presence of a username
            // and password. If we have it we will set the credentials on the Swift
            // transporter instance so that we'll properly authenticate delivery.
            if (isset($username))
            {
                $transport->setUsername($username);

                $transport->setPassword($password);
            }

            return $transport;
        };
    }

    /**
     * Register the Sendmail Swift Transport instance.
     *
     * @param  array  $config
     * @return void
     */
    protected function registerSendmailTransport($config)
    {
        $this->di['swift.transport'] = function() use ($config)
        {
            return SendmailTransport::newInstance($config['sendmail']);
        };
    }

    /**
     * Register the Mail Swift Transport instance.
     *
     * @param  array  $config
     * @return void
     */
    protected function registerMailTransport($config)
    {
        $this->di['swift.transport'] = function()
        {
            return MailTransport::newInstance();
        };
    }

    /**
     * Register the Simple View instance
     *
     * @param  array  $config
     * @return void
     */
    protected function registerView()
    {
        $this->di['mailer.view'] = function() {
            $view = new SimpleView;

            $view->setViewsDir($this->config->application->viewsDir);

            return $view;
        };
    }

}