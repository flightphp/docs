# Überschreiben

Flight erlaubt es Ihnen, seine standardmäßige Funktionalität anzupassen, um Ihren eigenen Anforderungen gerecht zu werden, ohne dass Sie Code ändern müssen.

Zum Beispiel, wenn Flight keine URL mit einer Route abgleichen kann, ruft es die `notFound`-Methode auf, die eine generische `HTTP 404`-Antwort sendet. Sie können dieses Verhalten überschreiben, indem Sie die `map`-Methode verwenden:

```php
Flight::map('notFound', function() {
  // Benutzerdefinierte 404-Seite anzeigen
  include 'errors/404.html';
});
```

Flight ermöglicht es Ihnen auch, Kernkomponenten des Frameworks zu ersetzen.
Zum Beispiel können Sie die Standard-Routenklasse durch Ihre eigene benutzerdefinierte Klasse ersetzen:

```php
// Registrieren Sie Ihre benutzerdefinierte Klasse
Flight::register('router', MyRouter::class);

// Wenn Flight die Router-Instanz lädt, wird Ihre Klasse geladen
$myrouter = Flight::router();
```

Framework-Methoden wie `map` und `register` können jedoch nicht überschrieben werden. Sie erhalten einen Fehler, wenn Sie dies versuchen.