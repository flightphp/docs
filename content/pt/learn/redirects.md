# Redirecionamentos

Você pode redirecionar a solicitação atual usando o método `redirect` e passando
uma nova URL:

```php
Flight::redirect('/novo/local');
```

Por padrão, o Flight envia um código de status HTTP 303. Você pode opcionalmente definir um
código personalizado:

```php
Flight::redirect('/novo/local', 401);
```