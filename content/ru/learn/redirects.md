# Редиректы

Можно перенаправить текущий запрос, используя метод `redirect` и передав новый URL:

```php
Flight::redirect('/новое/местоположение');
```

По умолчанию Flight отправляет статусный код HTTP 303. Можно дополнительно установить
пользовательский код:

```php
Flight::redirect('/новое/местоположение', 401);
```