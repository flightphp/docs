# Anhalten

Sie können das Framework jederzeit durch Aufrufen der `halt` Methode stoppen:

```php
Flight::halt();
```

Sie können auch einen optionalen `HTTP`-Statuscode und eine Nachricht angeben:

```php
Flight::halt(200, 'Bin gleich wieder da...');
```

Das Aufrufen von `halt` verwirft jeglichen Antwortinhalt bis zu diesem Zeitpunkt. Wenn Sie das Framework anhalten und die aktuelle Antwort ausgeben möchten, verwenden Sie die `stop` Methode:

```php
Flight::stop();
```