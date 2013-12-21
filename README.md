phalcon-mailer
==============

Сервис для отправки почты для Phalcon используя Swift Mailer.

Рекомендую устанавливать Swift Mailer через composer, тогда вам не придется думать о require

Код заимствован из Laravel 4 и адаптирован под Phalcon.
Удалены только методы работы с очередями, для отложенной отправки почты

Инициализация сервиса
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

Отправка письма
---------
Пример для контроллера, но работать будет не только в контроллерах
```php
$this->mailer->send('emails/xxx', [
    'test' => 'test' // Переменные для передачи в шаблон
], function($message) {
    $message->to('some_email@email.com');
    $message->subject('Test Email');
});
```
где, emails/xxx - шаблон письма расположенный в каталоге views, (app/views/emails/xxx.phtml)

Все шаблоны писем должны иметь расширение .phtml

Настройки
---------
Настройки по умолчанию необходимо прописать в конфигурационном файле вашего приложения config/config.php
```php
<?php
return new \Phalcon\Config(array(
    'application' => array(
        // Путь используемый для поиска шаблонов писем
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

Если будет необходимость, настройки почты можно вынести в отдельный конфигурационный файл

Чуть позже будет реализована возможность использования очередей для отложенной отправки почты через доступные сервисы очередей в Phalcon