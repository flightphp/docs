# WordPress Integration: n0nag0n/wordpress-integration-for-flight-framework

Möchten Sie Flight PHP in Ihrer WordPress-Site verwenden? Dieses Plugin macht es zum Kinderspiel! Mit `n0nag0n/wordpress-integration-for-flight-framework` können Sie eine vollständige Flight-App direkt neben Ihrer WordPress-Installation ausführen – ideal zum Erstellen benutzerdefinierter APIs, Microservices oder sogar vollwertiger Apps, ohne WordPress zu verlassen.

---

## Was tut es?

- **Integriert Flight PHP nahtlos mit WordPress**
- Leitet Anfragen an Flight oder WordPress basierend auf URL-Mustern um
- Organisieren Sie Ihren Code mit Controllern, Modellen und Views (MVC)
- Richten Sie die empfohlene Flight-Ordnerstruktur einfach ein
- Verwenden Sie die WordPress-Datenbankverbindung oder Ihre eigene
- Feinabstimmung, wie Flight und WordPress interagieren
- Einfache Admin-Oberfläche für die Konfiguration

## Installation

1. Laden Sie den `flight-integration`-Ordner in Ihr `/wp-content/plugins/`-Verzeichnis hoch.
2. Aktivieren Sie das Plugin im WordPress-Admin-Bereich (Plugins-Menü).
3. Gehen Sie zu **Einstellungen > Flight Framework**, um das Plugin zu konfigurieren.
4. Legen Sie den Pfad zum Vendor-Ordner für Ihre Flight-Installation fest (oder verwenden Sie Composer, um Flight zu installieren).
5. Konfigurieren Sie den Pfad für Ihren App-Ordner und erstellen Sie die Ordnerstruktur (das Plugin kann dabei helfen!).
6. Starten Sie mit der Erstellung Ihrer Flight-Anwendung!

## Nutzungsbeispiele

### Einfaches Route-Beispiel
In Ihrer `app/config/routes.php`-Datei:

```php
Flight::route('GET /api/hello', function() {
    Flight::json(['message' => 'Hello World!']);
});
```

### Controller-Beispiel

Erstellen Sie einen Controller in `app/controllers/ApiController.php`:

```php
namespace app\controllers;

use Flight;

class ApiController {
    public function getUsers() {
        // Sie können WordPress-Funktionen in Flight verwenden!
        $users = get_users();
        $result = [];
        foreach($users as $user) {
            $result[] = [
                'id' => $user->ID,
                'name' => $user->display_name,
                'email' => $user->user_email
            ];
        }
        Flight::json($result);
    }
}
```

Dann in Ihrer `routes.php`:

```php
Flight::route('GET /api/users', [app\controllers\ApiController::class, 'getUsers']);
```

## FAQ

**F: Muss ich Flight kennen, um dieses Plugin zu verwenden?**  
A: Ja, dies ist für Entwickler, die Flight innerhalb von WordPress nutzen möchten. Grundkenntnisse von Flights Routing und Anfrageverarbeitung werden empfohlen.

**F: Wird dies meine WordPress-Site verlangsamen?**  
A: Nein! Das Plugin verarbeitet nur Anfragen, die Ihren Flight-Routen entsprechen. Alle anderen Anfragen gehen wie üblich an WordPress.

**F: Kann ich WordPress-Funktionen in meiner Flight-App verwenden?**  
A: Absolut! Sie haben vollen Zugriff auf alle WordPress-Funktionen, Hooks und Globals innerhalb Ihrer Flight-Routen und Controller.

**F: Wie erstelle ich benutzerdefinierte Routen?**  
A: Definieren Sie Ihre Routen in der `config/routes.php`-Datei in Ihrem App-Ordner. Sehen Sie sich die Beispielfdatei an, die vom Ordnerstruktur-Generator erstellt wurde, für Beispiele.

## Änderungsprotokoll

**1.0.0**  
Erstveröffentlichung.

---

Für mehr Informationen schauen Sie sich das [GitHub repo](https://github.com/n0nag0n/wordpress-integration-for-flight-framework) an.