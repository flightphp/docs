# Großartige Plugins

Flight ist unglaublich erweiterbar. Es gibt eine Reihe von Plugins, mit denen du Funktionalität zu deiner Flight-Anwendung hinzufügen kannst. Einige werden offiziell vom Flight-Team unterstützt und andere sind Micro-/Lite-Bibliotheken, die dir den Einstieg erleichtern.

## KI-Tools

Flight kann mit KI-gestützten Plugins noch cooler gemacht werden.

- [Flight MCP](/awesome-plugins/mcp) – Ein Plugin zur Integration von MCP (Model Control Protocol) in Flight, das nahtlose KI-gestützte Funktionalität ermöglicht. Es konzentriert sich hauptsächlich auf die Dokumentationsseiten und hilft, Token-Kosten zu senken, indem es die aktuellsten Informationen zu deinen Flight-Projekten bereitstellt.
- [stribus/mcp-flightphp-server-skeleton](https://github.com/stribus/mcp-flightphp-server-skeleton) – Ein FlightPHP-MCP-Server-Skelett mit HTTP und stdio sowie automatischer Erkennung von Tools, Prompts und Ressourcen.

## API-Dokumentation

API-Dokumentation ist entscheidend für jede API. Sie hilft Entwicklern zu verstehen, wie sie mit deiner API interagieren und was sie als Antwort erwarten können. Es gibt einige Tools, mit denen du API-Dokumentation für deine Flight-Projekte generieren kannst.

- [FlightPHP OpenAPI Generator](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) – Blogbeitrag von Daniel Schreiber darüber, wie man die OpenAPI-Spezifikation mit FlightPHP verwendet, um deine API mit einem API-First-Ansatz aufzubauen.
- [SwaggerUI](https://github.com/zircote/swagger-php) – Swagger UI ist ein großartiges Tool, mit dem du API-Dokumentation für deine Flight-Projekte generieren kannst. Es ist sehr einfach zu bedienen und kann an deine Bedürfnisse angepasst werden. Dies ist die PHP-Bibliothek, mit der du die Swagger-Dokumentation generieren kannst.

## Anwendungs-Performance-Monitoring (APM)

Anwendungs-Performance-Monitoring (APM) ist entscheidend für jede Anwendung. Es hilft dir zu verstehen, wie deine Anwendung funktioniert und wo die Engpässe liegen. Es gibt eine Reihe von APM-Tools, die mit Flight verwendet werden können.
- <span class="badge bg-primary">offiziell</span> [flightphp/apm](/awesome-plugins/apm) – Flight APM ist eine einfache APM-Bibliothek, mit der du deine Flight-Anwendungen überwachen kannst. Sie kann verwendet werden, um die Leistung deiner Anwendung zu überwachen und dir zu helfen, Engpässe zu identifizieren.

## Asynchron

Flight ist bereits ein schnelles Framework, aber ihm einen Turbomotor zu verpassen, macht alles unterhaltsamer (und herausfordernder)!

- [flightphp/async](/awesome-plugins/async) – Offizielle Flight Async-Bibliothek. Diese Bibliothek ist eine einfache Möglichkeit, asynchrone Verarbeitung zu deiner Anwendung hinzuzufügen. Sie verwendet Swoole/Openswoole im Hintergrund, um eine einfache und effektive Möglichkeit zu bieten, Aufgaben asynchron auszuführen.

## Autorisierung/Berechtigungen

Autorisierung und Berechtigungen sind entscheidend für jede Anwendung, bei der kontrolliert werden muss, wer auf was zugreifen darf.

- <span class="badge bg-primary">offiziell</span> [flightphp/permissions](/awesome-plugins/permissions) – Offizielle Flight Permissions-Bibliothek. Diese Bibliothek ist eine einfache Möglichkeit, Berechtigungen auf Benutzer- und Anwendungsebene zu deiner Anwendung hinzuzufügen.

## Authentifizierung

Authentifizierung ist unerlässlich für Anwendungen, die die Benutzeridentität überprüfen und API-Endpunkte absichern müssen.

- [firebase/php-jwt](/awesome-plugins/jwt) – JSON Web Token (JWT)-Bibliothek für PHP. Eine einfache und sichere Möglichkeit, tokenbasierte Authentifizierung in deinen Flight-Anwendungen zu implementieren. Perfekt für zustandslose API-Authentifizierung, zum Schutz von Routen mit Middleware und zur Implementierung von OAuth-artigen Autorisierungsabläufen.

## Caching

Caching ist eine großartige Möglichkeit, deine Anwendung zu beschleunigen. Es gibt eine Reihe von Caching-Bibliotheken, die mit Flight verwendet werden können.

- <span class="badge bg-primary">offiziell</span> [flightphp/cache](/awesome-plugins/php-file-cache) – Leichte, einfache und eigenständige PHP-Klasse für In-File-Caching

## CLI

CLI-Anwendungen sind eine großartige Möglichkeit, mit deiner Anwendung zu interagieren. Du kannst sie verwenden, um Controller zu generieren, alle Routen anzuzeigen und mehr.

- <span class="badge bg-primary">offiziell</span> [flightphp/runway](/awesome-plugins/runway) – Runway ist eine CLI-Anwendung, die dir hilft, deine Flight-Anwendungen zu verwalten.

## Cookies

Cookies sind eine großartige Möglichkeit, kleine Datenmengen auf der Clientseite zu speichern. Sie können verwendet werden, um Benutzereinstellungen, Anwendungseinstellungen und mehr zu speichern.

- [overclokk/cookie](/awesome-plugins/php-cookie) – PHP Cookie ist eine PHP-Bibliothek, die eine einfache und effektive Möglichkeit zur Verwaltung von Cookies bietet.

## Debugging

Debugging ist entscheidend, wenn du in deiner lokalen Umgebung entwickelst. Es gibt einige Plugins, die dein Debugging-Erlebnis verbessern können.

- [tracy/tracy](/awesome-plugins/tracy) – Dies ist ein funktionsreicher Fehlerhandler, der mit Flight verwendet werden kann. Er verfügt über eine Reihe von Panels, die dir beim Debuggen deiner Anwendung helfen können. Er ist außerdem sehr einfach zu erweitern und um eigene Panels zu ergänzen.
- <span class="badge bg-primary">offiziell</span> [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) – Wird mit dem [Tracy](/awesome-plugins/tracy)-Fehlerhandler verwendet und fügt einige zusätzliche Panels hinzu, die speziell beim Debuggen von Flight-Projekten helfen.

## Datenbanken

Datenbanken sind das Herzstück der meisten Anwendungen. Hier werden Daten gespeichert und abgerufen. Einige Datenbankbibliotheken sind einfach Wrapper zum Schreiben von Abfragen, andere sind vollwertige ORMs.

- <span class="badge bg-primary">offiziell</span> [flightphp/core SimplePdo](/learn/simple-pdo) – Offizieller Flight PDO Helper, der Teil des Cores ist. Dies ist ein moderner Wrapper mit praktischen Hilfsmethoden wie `insert()`, `update()`, `delete()` und `transaction()`, um Datenbankoperationen zu vereinfachen. Alle Ergebnisse werden als Collections zurückgegeben, um flexiblen Array-/Objektzugriff zu ermöglichen. Kein ORM, sondern einfach eine bessere Möglichkeit, mit PDO zu arbeiten.
- <span class="badge bg-warning">veraltet</span> [flightphp/core PdoWrapper](/learn/pdo-wrapper) – Offizieller Flight PDO Wrapper, der Teil des Cores ist (veraltet ab v3.18.0). Verwende stattdessen SimplePdo.
- <span class="badge bg-primary">offiziell</span> [flightphp/active-record](/awesome-plugins/active-record) – Offizielles Flight ActiveRecord ORM/Mapper. Großartige kleine Bibliothek, um Daten einfach aus deiner Datenbank abzurufen und darin zu speichern.
- [byjg/php-migration](/awesome-plugins/migrations) – Plugin, um alle Datenbankänderungen für dein Projekt nachzuverfolgen.
- [knifelemon/easy-query](/awesome-plugins/easy-query) – Leichtgewichtiger, fluent SQL-Abfrage-Builder, der SQL und Parameter für Prepared Statements generiert. Funktioniert hervorragend mit [SimplePdo](/learn/simple-pdo).

## Verschlüsselung

Verschlüsselung ist entscheidend für jede Anwendung, die sensible Daten speichert. Das Verschlüsseln und Entschlüsseln der Daten ist nicht besonders schwer, aber das ordnungsgemäße Speichern des Verschlüsselungsschlüssels [kann](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [schwierig](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [sein](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key). Am wichtigsten ist, dass du deinen Verschlüsselungsschlüssel niemals in einem öffentlichen Verzeichnis speicherst oder ihn in deinem Code-Repository committest.

- [defuse/php-encryption](/awesome-plugins/php-encryption) – Dies ist eine Bibliothek, mit der Daten verschlüsselt und entschlüsselt werden können. Die Einrichtung ist recht einfach, um mit dem Verschlüsseln und Entschlüsseln von Daten zu beginnen.

## E-Mail

Das Senden von E-Mails ist ein grundlegendes Bedürfnis für die meisten Webanwendungen – Willkommensnachrichten, Passwort-Zurücksetzungen, Benachrichtigungen. Diese Bibliotheken machen es schmerzlos und halten die Zustellbarkeit stabil.

- [ryanstubbs/flightmail](/awesome-plugins/flightmail) – FlightMail umhüllt Symfony Mailer mit einer fluent Flight-freundlichen API. Versende über SMTP oder jeden großen Anbieter mittels einfacher DSN-Strings, route verschiedene Anbieter pro Nachricht und rendere Nachrichtentexte mit Twig- oder Latte-Templates. Dies ist ein inoffizielles Plugin für Flight und wird nicht vom Flight-Team gepflegt.

## Job-Warteschlange

Job-Warteschlangen sind wirklich hilfreich, um Aufgaben asynchron zu verarbeiten. Das können das Senden von E-Mails, die Verarbeitung von Bildern oder alles sein, was nicht in Echtzeit erledigt werden muss.

- [n0nag0n/simple-job-queue](/awesome-plugins/simple-job-queue) – Simple Job Queue ist eine Bibliothek, mit der Jobs asynchron verarbeitet werden können. Sie kann mit beanstalkd, MySQL/MariaDB, SQLite und PostgreSQL verwendet werden.

## Session

Sessions sind für APIs nicht wirklich nützlich, aber beim Aufbau einer Webanwendung können Sessions entscheidend sein, um den Zustand und Anmeldeinformationen beizubehalten.

- <span class="badge bg-primary">offiziell</span> [flightphp/session](/awesome-plugins/session) – Offizielle Flight Session-Bibliothek. Dies ist eine einfache Session-Bibliothek, mit der Session-Daten gespeichert und abgerufen werden können. Sie verwendet PHPs integrierte Session-Verwaltung.
- [Ghostff/Session](/awesome-plugins/ghost-session) – PHP-Session-Manager (nicht blockierend, Flash, Segment, Session-Verschlüsselung). Verwendet PHP open_ssl für optionale Ver-/Entschlüsselung von Session-Daten.

## Templating

Templating ist das Herzstück jeder Webanwendung mit einer UI. Es gibt eine Reihe von Templating-Engines, die mit Flight verwendet werden können.

- <span class="badge bg-warning">veraltet</span> [flightphp/core View](/learn#views) – Dies ist eine sehr einfache Templating-Engine, die Teil des Cores ist. Es wird nicht empfohlen, sie zu verwenden, wenn du mehr als ein paar Seiten in deinem Projekt hast.
- [latte/latte](/awesome-plugins/latte) – Latte ist eine funktionsreiche Templating-Engine, die sehr einfach zu verwenden ist und sich näher an einer PHP-Syntax anfühlt als Twig oder Smarty. Sie ist außerdem sehr einfach zu erweitern und um eigene Filter und Funktionen zu ergänzen.
- [twig/twig](/awesome-plugins/twig) – Twig ist eine flexible, schnelle und sichere Template-Engine (dieselbe, die von Symfony verwendet wird). KI-Tools und viele PHP-Entwickler kennen sie gut, sie maskiert Ausgaben standardmäßig automatisch und hat ein riesiges Ökosystem an Erweiterungen.
- [knifelemon/comment-template](/awesome-plugins/comment-template) – CommentTemplate ist eine leistungsstarke PHP-Template-Engine mit Asset-Kompilierung, Template-Vererbung und Variablenverarbeitung. Sie bietet automatische CSS/JS-Minifizierung, Caching, Base64-Kodierung und optionale Integration des Flight PHP Frameworks.

## WordPress-Integration

Möchtest du Flight in deinem WordPress-Projekt verwenden? Dafür gibt es ein praktisches Plugin!

- [n0nag0n/wordpress-integration-for-flight-framework](/awesome-plugins/n0nag0n_wordpress) – Dieses WordPress-Plugin lässt dich Flight direkt neben WordPress ausführen. Es ist perfekt, um benutzerdefinierte APIs, Microservices oder sogar vollständige Apps mit dem Flight-Framework zu deiner WordPress-Seite hinzuzufügen. Super nützlich, wenn du das Beste aus beiden Welten willst!

## Mitwirken

Hast du ein Plugin, das du teilen möchtest? Reiche einen Pull Request ein, um es zur Liste hinzuzufügen!