# Lernen Sie Flight kennen

Flight ist ein schnelles, einfaches, erweiterbares Framework für PHP. Es ist sehr vielseitig und kann für die Erstellung aller Arten von Web-Anwendungen verwendet werden. 
Es wurde mit dem Gedanken an Einfachheit entwickelt und so geschrieben, dass es leicht zu verstehen und zu verwenden ist.

> **Hinweis:** Sie werden Beispiele sehen, die `Flight::` als statische Variable verwenden und einige, die das Engine-Objekt `$app->` verwenden. Beide funktionieren austauschbar mit dem anderen. `$app` und `$this->app` in einem Controller/Middleware ist der empfohlene Ansatz vom Flight-Team.

## Kernkomponenten

### [Routing](/learn/routing)

Lernen Sie, wie Sie Routen für Ihre Web-Anwendung verwalten. Dies umfasst auch das Gruppieren von Routen, Routenparameter und Middleware.

### [Middleware](/learn/middleware)

Lernen Sie, wie Sie Middleware verwenden, um Anfragen und Antworten in Ihrer Anwendung zu filtern.

### [Autoloading](/learn/autoloading)

Lernen Sie, wie Sie Ihre eigenen Klassen in Ihrer Anwendung autoloaden.

### [Requests](/learn/requests)

Lernen Sie, wie Sie Anfragen und Antworten in Ihrer Anwendung handhaben.

### [Responses](/learn/responses)

Lernen Sie, wie Sie Antworten an Ihre Benutzer senden.

### [HTML Templates](/learn/templates)

Lernen Sie, wie Sie den integrierten View-Engine verwenden, um Ihre HTML-Templates zu rendern.

### [Security](/learn/security)

Lernen Sie, wie Sie Ihre Anwendung vor gängigen Sicherheitsbedrohungen schützen.

### [Configuration](/learn/configuration)

Lernen Sie, wie Sie das Framework für Ihre Anwendung konfigurieren.

### [Event Manager](/learn/events)

Lernen Sie, wie Sie das Event-System verwenden, um benutzerdefinierte Events zu Ihrer Anwendung hinzuzufügen.

### [Extending Flight](/learn/extending)

Lernen Sie, wie Sie das Framework erweitern, indem Sie Ihre eigenen Methoden und Klassen hinzufügen.

### [Method Hooks and Filtering](/learn/filtering)

Lernen Sie, wie Sie Event-Hooks zu Ihren Methoden und internen Framework-Methoden hinzufügen.

### [Dependency Injection Container (DIC)](/learn/dependency-injection-container)

Lernen Sie, wie Sie Dependency-Injection-Container (DIC) verwenden, um die Abhängigkeiten Ihrer Anwendung zu verwalten.

## Utility Classes

### [Collections](/learn/collections)

Collections werden verwendet, um Daten zu speichern und sie als Array oder als Objekt zugänglich zu machen, um die Nutzung zu erleichtern.

### [JSON Wrapper](/learn/json)

Dies bietet einige einfache Funktionen, um das Encodieren und Decodieren Ihres JSON konsistent zu machen.

### [SimplePdo](/learn/simple-pdo)

PDO kann manchmal mehr Kopfschmerzen verursachen als notwendig. SimplePdo ist eine moderne PDO-Hilfsklasse mit bequemen Methoden wie `insert()`, `update()`, `delete()` und `transaction()`, um Datenbankoperationen viel einfacher zu machen.

### [PdoWrapper](/learn/pdo-wrapper) (Deprecated)

Der ursprüngliche PDO-Wrapper ist ab v3.18.0 veraltet. Bitte verwenden Sie stattdessen [SimplePdo](/learn/simple-pdo).

### [Uploaded File Handler](/learn/uploaded-file)

Eine einfache Klasse, die hilft, hochgeladene Dateien zu verwalten und sie an einen permanenten Speicherort zu verschieben.

## Wichtige Konzepte

### [Why a Framework?](/learn/why-frameworks)

Hier ist ein kurzer Artikel darüber, warum Sie ein Framework verwenden sollten. Es ist eine gute Idee, die Vorteile der Verwendung eines Frameworks zu verstehen, bevor Sie eines einsetzen.

Zusätzlich wurde ein exzellentes Tutorial von [@lubiana](https://git.php.fail/lubiana) erstellt. Obwohl es nicht insbesondere auf Flight eingeht, 
hilft diese Anleitung Ihnen, einige der wichtigsten Konzepte rund um ein Framework zu verstehen und warum sie vorteilhaft sind. 
Sie finden das Tutorial [hier](https://git.php.fail/lubiana/no-framework-tutorial/src/branch/master/README.md).

### [Flight Compared to Other Frameworks](/learn/flight-vs-another-framework)

Wenn Sie von einem anderen Framework wie Laravel, Slim, Fat-Free oder Symfony zu Flight migrieren, hilft Ihnen diese Seite, die Unterschiede zwischen den beiden zu verstehen.

## Andere Themen

### [Unit Testing](/learn/unit-testing)

Folgen Sie dieser Anleitung, um zu lernen, wie Sie Ihren Flight-Code unit-testen, um ihn robust zu machen.

### [AI & Developer Experience](/learn/ai)

Lernen Sie, wie Flight mit AI-Tools und modernen Entwickler-Workflows zusammenarbeitet, um Ihnen zu helfen, schneller und smarter zu coden.

### [Migrating v2 -> v3](/learn/migrating-to-v3)

Rückwärtskompatibilität wurde größtenteils beibehalten, aber es gibt einige Änderungen, die Sie beim Migrieren von v2 zu v3 beachten sollten.