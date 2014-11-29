phalcon-mailer
==============
[![Bitdeli Badge](https://d2weczhvl823v0.cloudfront.net/vanchelo/phalcon-mailer/trend.png)](https://bitdeli.com/free "Bitdeli Badge")

Сервис для отправки почты для Phalcon используя Swift Mailer.

Код заимствован из Laravel 4 и адаптирован под Phalcon.

##Установка с помощью `composer`:
Добавить в файл `composer.json` в секцию `require`:
```
"vanchelo/phalcon-mailer": "dev-master"
```
Должно получится примерно так:
```json
{
  "require": {
    "vanchelo/phalcon-console": "dev-master"
  }
}
```
В терминале выполнить команду `composer update`

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
