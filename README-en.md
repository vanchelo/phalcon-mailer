**english** | [русский](./README.md)
- - -

Phalcon Mailer
==============
Convenient library for sending your mail to [Phalcon 2.0](http://phalconphp.com/).

The code is borrowed from Laravel 4 and adapted for Phalcon.

Installation
---------
With the help of `composer`:

Add the following line to the  `composer.json`  file in the `require` section:

```
"vanchelo/phalcon-mailer": "~2.0"
```
or run on the command line:

```
composer require vanchelo/phalcon-mailer
```
It should look something like this:

```json
{
  "require": {
    "vanchelo/phalcon-mailer": "~2.0"
  }
}
```
After that, run the command in the terminal:

```bash
composer update
```
Service initialization
---------
```php
/**
 * Register Mailer Service
 */
$this->di['mailer'] = function() {
    $service = new MailerService();
    return $service->mailer();
};
```
or with passing parameters at the stage of service initialization

```php
/**
 * Register Mailer Service
 */
$this->di['mailer'] = function() {
    $service = new MailerService([
        'driver' => 'smtp', // mail, sendmail, smtp
        'host'   => 'smtp.email.com',
        'port'   => 587,
        'from'   => [
            'address' => 'no-reply@my-domain.com',
            'name'    => 'My Cool Company',
        ],
        'encryption' => 'tls',
        'username'   => 'no-reply@my-domain.com',
        'password'   => 'some-strong-password',
        'sendmail'   => '/usr/sbin/sendmail -bs',
        // The path used to find email templates
        'viewsDir'   => __DIR__ . '/../app/views/', // optional
    ]);
    return $service->mailer();
};
```

Sending letter
---------
Example for a controller, but will work not only in controllers

```php
$this->mailer->send('emails/xxx', [
    'test' => 'test' // Template variables
], function($message) {
    $message->to('some_email@email.com');
    $message->subject('Test Email');
});
```
where, emails / xxx is a letter template located in the views directory, (app/views/emails/xxx.[phtml|volt])

By default, if the `view` service is registered in the container, the library will use it, so you can use any convenient available template engine (phtml, volt, etc.)

Settings
---------
The default settings must be written in the configuration file of your application config / config.php
```php
<?php
return new \Phalcon\Config([
    'mail' => [
        'driver' => 'smtp', // mail, sendmail, smtp
        'host'   => 'smtp.email.com',
        'port'   => 587,
        'from'   => [
            'address' => 'no-reply@my-domain.com',
            'name'    => 'My Cool Company'
        ],
        'encryption' => 'tls',
        'username'   => 'no-reply@my-domain.com',
        'password'   => 'some-strong-password',
        'sendmail'   => '/usr/sbin/sendmail -bs',
        // The path used to find email templates
        'viewsDir'   => __DIR__ . '/../app/views/', // optional
    ],
]);
```

If necessary, mail settings can be moved to a separate configuration file

** UPD **. Implemented the ability to use queues for delayed mail sending via the Beanstalk queue service implemented in Phalcon

Queues (delayed mail sending)
---------

For delayed sending of mail, you must have registered the `queue` service in the container, for example:

```php
use Phalcon\Queue\Beanstalk;
$this->di['queue'] = function () {
    $queue = new Beanstalk();
    $queue->connect(); // ?
    return $queue;
};
```

Example of delayed mail sending

```php
$this->mailer->queue('emails/xxx', [
    'test' => 'test' // Template variables
], function($message) {
    $message->to('some_email@email.com');
    $message->subject('Test Email');
});
```

All postponed mail is placed in the `mailer` queue (I don't know if this is correct)

Queue handler example https://github.com/vanchelo/phalcon-mailer/blob/master/example/mailer.php
