<?php

namespace Vanchelo\Mailer;

use Phalcon\Mvc\User\Component;
use Swift_Mailer;
use Swift_SmtpTransport as SmtpTransport;
use Swift_MailTransport as MailTransport;
use Swift_SendmailTransport as SendmailTransport;
use Phalcon\Mvc\View\Simple as SimpleView;

/**
 * Class MailerService
 *
 * @package Vanchelo\Mailer
 */
class MailerService extends Component
{
    /**
     * @var array
     */
    protected $config;

    /**
     * Create a new service provider instance.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        if (!$config && !$this->di->has('config')) {
            throw new \RuntimeException('Correct config for Mailer is not provided!');
        }

        $this->config = $config ?: $this->di['config']->mail->toArray();

        $this->registerSwiftMailer();
        $this->registerView();
    }

    /**
     * Register the Mailer instance
     *
     * @return Mailer $mailer
     */
    public function mailer()
    {
        // Once we have create the mailer instance, we will set a container instance
        // on the mailer. This allows us to resolve mailer classes via containers
        // for maximum testability on said classes instead of passing Closures.
        $mailer = new Mailer($this->di['mailer.view'], $this->di['swift.mailer']);

        $this->setMailerDependencies($mailer);

        $from = $this->config['from'];

        if (is_array($from) && isset($from['address'])) {
            $mailer->alwaysFrom($from['address'], $from['name']);
        }

        return $mailer;
    }

    /**
     * Register the Swift Mailer instance.
     */
    protected function registerSwiftMailer()
    {
        $this->registerSwiftTransport($this->config);

        // Once we have the transporter registered, we will register the actual Swift
        // mailer instance, passing in the transport instances, which allows us to
        // override this transporter instances during app start-up if necessary.
        $this->di['swift.mailer'] = function () {
            return new Swift_Mailer($this->di['swift.transport']);
        };
    }

    /**
     * Register the Swift Transport instance.
     *
     * @param array $config
     */
    protected function registerSwiftTransport(array $config)
    {
        if (!isset($config['driver'])) {
            throw new \InvalidArgumentException('Please set "driver" for Mailer!');
        }

        switch ($config['driver']) {
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
     * @param array $config
     *
     * return null
     */
    protected function registerSmtpTransport(array $config)
    {
        $this->di['swift.transport'] = function () use ($config) {
            extract($config);

            if (!isset($host, $port)) {
                throw new \InvalidArgumentException('Please set "host" and "port" for Mailer!');
            }

            // The Swift SMTP transport instance will allow us to use any SMTP backend
            // for delivering mail such as Sendgrid, Amazon SMS, or a custom server
            // a developer has available. We will just pass this configured host.
            $transport = SmtpTransport::newInstance($host, $port);

            if (isset($encryption)) {
                $transport->setEncryption($encryption);
            }

            // Once we have the transport we will check for the presence of a username
            // and password. If we have it we will set the credentials on the Swift
            // transporter instance so that we'll properly authenticate delivery.
            if (isset($username, $password)) {
                $transport->setUsername($username);

                $transport->setPassword($password);
            }

            return $transport;
        };
    }

    /**
     * Register the Sendmail Swift Transport instance.
     *
     * @param array $config
     */
    protected function registerSendmailTransport(array $config)
    {
        $this->di['swift.transport'] = function () use ($config) {
            return SendmailTransport::newInstance($config['sendmail']);
        };
    }

    /**
     * Register the Mail Swift Transport instance.
     */
    protected function registerMailTransport()
    {
        $this->di['swift.transport'] = function () {
            return MailTransport::newInstance();
        };
    }

    /**
     * Register the Simple View instance
     */
    protected function registerView()
    {
        if ($this->di->has('view')) {
            $this->di['mailer.view'] = function () {
                return $this->di->get('view');
            };
        } else {
            $this->di['mailer.view'] = function () {
                if (!isset($this->config['viewsDir'])) {
                    throw new \InvalidArgumentException('Invalid views dir!');
                }

                $view = new SimpleView;

                $view->setViewsDir($this->config['viewsDir']);

                return $view;
            };
        }
    }

    /**
     * Set a few dependencies on the mailer instance.
     *
     * @param Mailer $mailer
     */
    protected function setMailerDependencies(Mailer $mailer)
    {
        $mailer->setDI($this->di);

        if ($this->di->has('queue')) {
            $mailer->setQueue($this->di['queue']);
        }
    }
}
