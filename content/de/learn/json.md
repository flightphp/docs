# JSON

Flight unterstützt das Senden von JSON- und JSONP-Antworten. Um eine JSON-Antwort zu senden, geben Sie einige Daten an, die codiert werden sollen:

```php
Flight::json(['id' => 123]);
```

Für JSONP-Anfragen können Sie optional den Abfrageparameter angeben, den Sie verwenden, um Ihre Rückruffunktion zu definieren:

```php
Flight::jsonp(['id' => 123], 'q');
```

Daher sollten Sie beim Senden einer GET-Anfrage mit `?q=my_func` die Ausgabe erhalten:

```javascript
my_func({"id":123});
```

Wenn Sie keinen Abfrageparameter angeben, wird standardmäßig `jsonp` verwendet.