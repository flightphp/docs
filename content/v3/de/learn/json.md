```de
# JSON

Flight bietet Unterstützung für das Senden von JSON- und JSONP-Antworten. Um eine JSON-Antwort zu senden, übergeben Sie einige Daten, die in JSON kodiert werden sollen:

```php
Flight::json(['id' => 123]);
```

Für JSONP-Anfragen können Sie optional den Abfrage-Parameter Namen angeben, den Sie verwenden, um Ihre Rückruffunktion zu definieren:

```php
Flight::jsonp(['id' => 123], 'q');
```

Wenn Sie also eine GET-Anfrage mit `?q=my_func` stellen, sollten Sie die Ausgabe erhalten:

```javascript
my_func({"id":123});
```

Wenn Sie keinen Abfrageparameter Namen angeben, wird standardmäßig `jsonp` verwendet.
```