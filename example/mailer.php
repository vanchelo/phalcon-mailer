#!/usr/bin/env php
<?php

use Phalcon\DI;
use Phalcon\Queue\Beanstalk;
use Phalcon\Queue\Beanstalk\Job;
use Vanchelo\Mailer\MailerService;

// Подключаем конфиг приложения (исправить на свой)
$config = require __DIR__ . '/app/config/config.php';

$di = new DI();
$di->set('config', $config);

$queue = new Beanstalk();
$queue->choose('mailer');
$di['queue'] = $queue;

/**
 * Register Mailer Service
 */
$di['mailer'] = function () {
    $service = new MailerService();

    return $service->mailer();
};

/** @var Job $job */
while (($job = $queue->peekReady()) !== false) {
    $data = json_decode($job->getBody(), true);

    $segments = explode(':', $data['job']);

    if (count($segments) !== 2) {
        continue;
    }

    call_user_func_array([$di[$segments[0]], $segments[1]], [$job, $data['data']]);
}
