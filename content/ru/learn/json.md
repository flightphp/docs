# JSON

Flight предоставляет поддержку для отправки JSON и JSONP ответов. Чтобы отправить ответ в формате JSON, вы передаете данные, которые будут закодированы в JSON:

```php
Flight::json(['id' => 123]);
```

Для запросов JSONP вы можете, опционально, передать имя параметра запроса, которое вы используете для определения функции обратного вызова:

```php
Flight::jsonp(['id' => 123], 'q');
```

Таким образом, при выполнении GET-запроса с использованием `?q=my_func`, вы должны получить следующий вывод:

```javascript
my_func({"id":123});
```

Если вы не передаете имя параметра запроса, оно будет по умолчанию установлено на `jsonp`.