# Variablen

Flight ermöglicht es Ihnen, Variablen zu speichern, damit sie überall in Ihrer Anwendung verwendet werden können.

```php
// Speichern Sie Ihre Variable
Flight::set('id', 123);

// Anderswo in Ihrer Anwendung
$id = Flight::get('id');
```

Um zu überprüfen, ob eine Variable festgelegt wurde, können Sie Folgendes tun:

```php
if (Flight::has('id')) {
  // Mach etwas
}
```

Sie können eine Variable löschen, indem Sie Folgendes tun:

```php
// Löscht die id-Variable
Flight::clear('id');

// Löscht alle Variablen
Flight::clear();
```

Flight verwendet auch Variablen für Konfigurationszwecke.

```php
Flight::set('flight.log_errors', true);
```  