# JSON

Voo fornece suporte para o envio de respostas JSON e JSONP. Para enviar uma resposta JSON você passa alguns dados para serem codificados em JSON:

```php
Flight::json(['id' => 123]);
```

Para requisições JSONP, você pode opcionalmente passar o nome do parâmetro de consulta que está utilizando para definir sua função de retorno de chamada:

```php
Flight::jsonp(['id' => 123], 'q');
```

Assim, ao fazer uma solicitação GET usando `?q=my_func`, você deve receber a saída:

```javascript
my_func({"id":123});
```

Se você não passar um nome de parâmetro de consulta, ele será padrão para `jsonp`.