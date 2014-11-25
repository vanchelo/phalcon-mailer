#!/usr/bin/env php
<?php
/**
 * Workers that handles queues related to the videos.
 */
use Phalcon\Queue\Beanstalk;
use Phalcon\Queue\Beanstalk\Job;
use Phalcon\DI;
use Phalcon\Mvc\View\Simple as SimpleView;
use Vanchelo\Mailer\MailerService;

$config = include __DIR__ . '/../config/config.php';

$di = new DI();
$di->set('config', $config);

/**
 * Register Simple View Service
 */
$di['viewSimple'] = function ()
{
    $view = new SimpleView;
    $view->setViewsDir($this->config->application->viewsDir);

    return $view;
};

/**
 * Register Mailer Service
 */
$di['mailer'] = function ()
{
    $service = new MailerService();

    return $service->mailer();
};

$queue = new Beanstalk();
$queue->choose('mailer');
$di['queue'] = $queue;

/** @var Job $job */
while ($queue->peekReady() !== false)
{
    $message = $job->getBody();

    list($jobHandler, $data) = $message;

    $segments = explode(':', $jobHandler);

    if (count($segments) !== 2) continue;

    call_user_func_array([$this->di[$segments[0]], $segments[1]], [$job, $data]);
}
