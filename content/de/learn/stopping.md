# Stoppen

Du kannst das Framework jederzeit anhalten, indem du die `halt`-Methode aufrufst:

```php
Flight::halt();
```

Du kannst auch einen optionalen `HTTP`-Statuscode und eine Nachricht angeben:

```php
Flight::halt(200, 'Bin gleich wieder da...');
```

Das Aufrufen von `halt` verwirft jeglichen Antwortinhalt bis zu diesem Zeitpunkt. Wenn du das Framework anhalten und die aktuelle Antwort ausgeben möchtest, verwende die `stop`-Methode:

```php
Flight::stop();
```