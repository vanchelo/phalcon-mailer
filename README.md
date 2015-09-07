Phalcon Mailer
==============
Удобная библиотека для отправки Вашей почты в [Phalcon 2.0](http://phalconphp.com/).

Код заимствован из Laravel 4 и адаптирован под Phalcon.

##Установка
C помощью `composer`:

Добавить в файл `composer.json` в секцию `require` следующую строку:
```
"vanchelo/phalcon-mailer": "~2.0"
```
или выполнить в командной строке:
```
composer require vanchelo/phalcon-mailer
```
Должно получится примерно так:
```json
{
  "require": {
    "vanchelo/phalcon-console": "dev-master"
  }
}
```
После этого выполните в терминале команду:
```bash
composer update
```

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
или с передачей параметров на этапе инициализации сервиса

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
        // Путь используемый для поиска шаблонов писем
        'viewsDir'   => __DIR__ . '/../app/views/', // optional
    ]);

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
где, emails/xxx - шаблон письма расположенный в каталоге views, (app/views/emails/xxx.[phtml|volt])

По умолчанию, если в контейнере зарегистрирован сервис `view` библиотека будет использовать его, соответственно можно использовать любой удобный доступный шаблонизатор (phtml, volt и т.д.)

Настройки
---------
Настройки по умолчанию необходимо прописать в конфигурационном файле вашего приложения config/config.php
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
        // Путь используемый для поиска шаблонов писем
        'viewsDir'   => __DIR__ . '/../app/views/', // optional
    ],
]);
```

Если будет необходимость, настройки почты можно вынести в отдельный конфигурационный файл

**UPD**. Реализована возможность использования очередей для отложенной отправки почты через реализованный в Phalcon сервис очередей Beanstalk

Очереди (отложенная отправка почты)
---------

Для отложенной отправки почты у вас должен быть зарегистрирован сервис `queue` в контейнере, например:

```php
use Phalcon\Queue\Beanstalk;

$this->di['queue'] = function () {
    $queue = new Beanstalk();
    $queue->connect(); // ?

    return $queue;
};
```

Пример отложенной отправки почты

```php
$this->mailer->queue('emails/xxx', [
    'test' => 'test' // Переменные для передачи в шаблон
], function($message) {
    $message->to('some_email@email.com');
    $message->subject('Test Email');
});
```

Вся отложенная почта помещается в очередь `mailer` (не знаю насколько это правильно)

Пример обработчика очереди https://github.com/vanchelo/phalcon-mailer/blob/master/example/mailer.php
