phalcon-mailer
==============

Сервис для отправки почты испльзуя Swift Mailer.
Код заимствован из Laravel 4 и адаптирован под Phalcon
Удалены только методы работы с очередями, для отложенной отправки почты

Инициализация сервиса:
```php
/**
 * Register Mailer Service
 */
$this->di['mailer'] = function() {
    $service = new MailerService();

    return $service->mailer();
};
```

Отправка письма из контроллера:
```php
$this->mailer->send('emails/xxx', [
    'test' => 'test' // Переменные для передачи в шаблон
], function($message) {
    $message->to('some_email@email.com');
    $message->subject('Test Email');
});
```

Настройки.
Настройки по умолчанию необходимо прописать в конфигурационном файле вашего приложения config/config.php
```php
<?php
return new \Phalcon\Config(array(
    'application' => array(
        'viewsDir'  => __DIR__ . '/../app/views/',
        /* ... */
    ),

    'mail' => array(
        'driver' => 'smtp', // mail, sendmail, smtp
        'host'   => 'smtp.email.com',
        'port'   => 587,
        'from'   => array(
            'address' => 'no-reply@my-domain.com',
            'name'    => 'My Cool Company'
        ),
        'encryption' => 'tls',
        'username'   => 'no-reply@my-domain.com',
        'password'   => 'some-strong-password',
        'sendmail'   => '/usr/sbin/sendmail -bs',
    ),
));

```