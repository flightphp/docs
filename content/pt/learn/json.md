```pt
# JSON

Flight fornece suporte para o envio de respostas JSON e JSONP. Para enviar uma resposta JSON, você
passa alguns dados a serem codificados em JSON:

```php
Flight::json(['id' => 123]);
```

Para solicitações JSONP, você pode passar opcionalmente o nome do parâmetro de consulta que está
usando para definir sua função de retorno de chamada:

```php
Flight::jsonp(['id' => 123], 'q');
```

Portanto, ao fazer uma solicitação GET usando `?q=my_func`, você deve receber a saída:

```javascript
my_func({"id":123});
```

Se você não passar um nome de parâmetro de consulta, ele será padrão para `jsonp`.
```