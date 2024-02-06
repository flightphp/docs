# Parando

Você pode parar o framework a qualquer momento chamando o método `halt`:

```php
Flight::halt();
```

Você também pode especificar um código de status `HTTP` opcional e uma mensagem:

```php
Flight::halt(200, 'Volto em breve...');
```

Chamar `halt` irá descartar qualquer conteúdo de resposta até esse ponto. Se você quiser parar
o framework e exibir a resposta atual, use o método `stop`:

```php
Flight::stop();
```