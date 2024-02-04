# Parar

É possível parar o framework a qualquer momento ao chamar o método `halt`:

```php
Flight::halt();
```

Também é possível especificar um código de status `HTTP` e uma mensagem opcional:

```php
Flight::halt(200, 'Volto já...');
```

Chamar `halt` descartará qualquer conteúdo de resposta até esse ponto. Se desejar parar
o framework e exibir a resposta atual, utilize o método `stop`:

```php
Flight::stop();
```