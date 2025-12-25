# Awesome Plugins

Flight ist unglaublich erweiterbar. Es gibt eine Reihe von Plugins, die verwendet werden können, um Funktionalität zu Ihrer Flight-Anwendung hinzuzufügen. Einige werden offiziell vom Flight-Team unterstützt, und andere sind Micro/Lite-Bibliotheken, um Ihnen den Einstieg zu erleichtern.

## API-Dokumentation

API-Dokumentation ist entscheidend für jede API. Sie hilft Entwicklern zu verstehen, wie sie mit Ihrer API interagieren können und was sie als Rückgabe erwarten können. Es gibt ein paar Tools, die Ihnen helfen, API-Dokumentation für Ihre Flight-Projekte zu generieren.

- [FlightPHP OpenAPI Generator](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - Blogbeitrag von Daniel Schreiber darüber, wie man die OpenAPI-Spezifikation mit FlightPHP verwendet, um Ihre API mit einem API-First-Ansatz aufzubauen.
- [SwaggerUI](https://github.com/zircote/swagger-php) - Swagger UI ist ein großartiges Tool, um API-Dokumentation für Ihre Flight-Projekte zu generieren. Es ist sehr einfach zu bedienen und kann an Ihre Bedürfnisse angepasst werden. Dies ist die PHP-Bibliothek, die Ihnen hilft, die Swagger-Dokumentation zu generieren.

## Application Performance Monitoring (APM)

Application Performance Monitoring (APM) ist entscheidend für jede Anwendung. Es hilft Ihnen zu verstehen, wie Ihre Anwendung performt und wo die Engpässe liegen. Es gibt eine Reihe von APM-Tools, die mit Flight verwendet werden können.
- <span class="badge bg-primary">offiziell</span> [flightphp/apm](/awesome-plugins/apm) - Flight APM ist eine einfache APM-Bibliothek, die verwendet werden kann, um Ihre Flight-Anwendungen zu überwachen. Sie kann verwendet werden, um die Leistung Ihrer Anwendung zu überwachen und Ihnen zu helfen, Engpässe zu identifizieren.

## Async

Flight ist bereits ein schnelles Framework, aber es mit einem Turbolader auszustatten macht alles noch spaßiger (und herausfordernder)!

- [flightphp/async](/awesome-plugins/async) - Offizielle Flight Async-Bibliothek. Diese Bibliothek ist eine einfache Möglichkeit, asynchrone Verarbeitung zu Ihrer Anwendung hinzuzufügen. Sie verwendet Swoole/Openswoole im Hintergrund, um eine einfache und effektive Möglichkeit zu bieten, Aufgaben asynchron auszuführen.

## Authorization/Permissions

Autorisierung und Berechtigungen sind entscheidend für jede Anwendung, die Steuerelemente für den Zugriff auf bestimmte Inhalte erfordert.

- <span class="badge bg-primary">offiziell</span> [flightphp/permissions](/awesome-plugins/permissions) - Offizielle Flight Permissions-Bibliothek. Diese Bibliothek ist eine einfache Möglichkeit, Benutzer- und Anwendungsebene-Berechtigungen zu Ihrer Anwendung hinzuzufügen. 

## Caching

Caching ist eine großartige Möglichkeit, Ihre Anwendung zu beschleunigen. Es gibt eine Reihe von Caching-Bibliotheken, die mit Flight verwendet werden können.

- <span class="badge bg-primary">offiziell</span> [flightphp/cache](/awesome-plugins/php-file-cache) - Leichte, einfache und eigenständige PHP-in-File-Caching-Klasse

## CLI

CLI-Anwendungen sind eine großartige Möglichkeit, mit Ihrer Anwendung zu interagieren. Sie können sie verwenden, um Controller zu generieren, alle Routen anzuzeigen und mehr.

- <span class="badge bg-primary">offiziell</span> [flightphp/runway](/awesome-plugins/runway) - Runway ist eine CLI-Anwendung, die Ihnen hilft, Ihre Flight-Anwendungen zu verwalten.

## Cookies

Cookies sind eine großartige Möglichkeit, kleine Datenmengen auf der Client-Seite zu speichern. Sie können verwendet werden, um Benutzerpräferenzen, Anwendungseinstellungen und mehr zu speichern.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie ist eine PHP-Bibliothek, die eine einfache und effektive Möglichkeit bietet, Cookies zu verwalten.

## Debugging

Debugging ist entscheidend, wenn Sie in Ihrer lokalen Umgebung entwickeln. Es gibt ein paar Plugins, die Ihr Debugging-Erlebnis verbessern können.

- [tracy/tracy](/awesome-plugins/tracy) - Dies ist ein vollständiges Fehlerbehandlungstool, das mit Flight verwendet werden kann. Es verfügt über eine Reihe von Panels, die Ihnen helfen können, Ihre Anwendung zu debuggen. Es ist auch sehr einfach zu erweitern und eigene Panels hinzuzufügen.
- <span class="badge bg-primary">offiziell</span> [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - In Verbindung mit dem [Tracy](/awesome-plugins/tracy) Fehlerbehandlungstool fügt dieses Plugin ein paar zusätzliche Panels hinzu, um das Debugging speziell für Flight-Projekte zu erleichtern.

## Databases

Datenbanken sind der Kern der meisten Anwendungen. So speichern und rufen Sie Daten ab. Einige Datenbank-Bibliotheken sind einfach Wrapper, um Abfragen zu schreiben, und einige sind vollwertige ORMs.

- <span class="badge bg-primary">offiziell</span> [flightphp/core PdoWrapper](/learn/pdo-wrapper) - Offizieller Flight PDO-Wrapper, der zum Kern gehört. Dies ist ein einfacher Wrapper, der den Prozess des Schreibens und Ausführens von Abfragen vereinfacht. Es handelt sich nicht um ein ORM.
- <span class="badge bg-primary">offiziell</span> [flightphp/active-record](/awesome-plugins/active-record) - Offizielles Flight ActiveRecord ORM/Mapper. Tolle kleine Bibliothek, um Daten einfach in Ihrer Datenbank abzurufen und zu speichern.
- [byjg/php-migration](/awesome-plugins/migrations) - Plugin, um alle Datenbankänderungen für Ihr Projekt zu verfolgen.

## Encryption

Verschlüsselung ist entscheidend für jede Anwendung, die sensible Daten speichert. Das Verschlüsseln und Entschlüsseln der Daten ist nicht allzu schwer, aber das ordnungsgemäße Speichern des Verschlüsselungsschlüssels [kann](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [sein](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [schwierig](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key). Das Wichtigste ist, Ihren Verschlüsselungsschlüssel niemals in einem öffentlichen Verzeichnis zu speichern oder ihn in Ihr Code-Repository zu committen.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - Dies ist eine Bibliothek, die verwendet werden kann, um Daten zu verschlüsseln und zu entschlüsseln. Der Einstieg ist ziemlich einfach, um mit dem Verschlüsseln und Entschlüsseln von Daten zu beginnen.

## Job Queue

Job-Warteschlangen sind wirklich hilfreich, um Aufgaben asynchron zu verarbeiten. Dies kann das Senden von E-Mails, das Verarbeiten von Bildern oder alles sein, was nicht in Echtzeit erledigt werden muss.

- [n0nag0n/simple-job-queue](/awesome-plugins/simple-job-queue) - Simple Job Queue ist eine Bibliothek, die verwendet werden kann, um Jobs asynchron zu verarbeiten. Sie kann mit beanstalkd, MySQL/MariaDB, SQLite und PostgreSQL verwendet werden.

## Session

Sitzungen sind für APIs nicht wirklich nützlich, aber beim Aufbau einer Web-Anwendung können Sitzungen entscheidend für die Aufrechterhaltung des Zustands und Login-Informationen sein.

- <span class="badge bg-primary">offiziell</span> [flightphp/session](/awesome-plugins/session) - Offizielle Flight Session-Bibliothek. Dies ist eine einfache Session-Bibliothek, die verwendet werden kann, um Session-Daten zu speichern und abzurufen. Sie verwendet die integrierte Session-Behandlung von PHP.
- [Ghostff/Session](/awesome-plugins/ghost-session) - PHP Session-Manager (nicht-blockierend, Flash, Segment, Session-Verschlüsselung). Verwendet PHP open_ssl für optionale Verschlüsselung/Entschlüsselung von Session-Daten.

## Templating

Templating ist der Kern jeder Web-Anwendung mit einer Benutzeroberfläche. Es gibt eine Reihe von Templating-Engines, die mit Flight verwendet werden können.

- <span class="badge bg-warning">deprecated</span> [flightphp/core View](/learn#views) - Dies ist eine sehr grundlegende Templating-Engine, die zum Kern gehört. Es wird nicht empfohlen, sie zu verwenden, wenn Ihr Projekt mehr als ein paar Seiten hat.
- [latte/latte](/awesome-plugins/latte) - Latte ist eine vollständige Templating-Engine, die sehr einfach zu bedienen ist und sich näher an der PHP-Syntax anfühlt als Twig oder Smarty. Sie ist auch sehr einfach zu erweitern und eigene Filter und Funktionen hinzuzufügen.
- [knifelemon/comment-template](/awesome-plugins/comment-template) - CommentTemplate ist eine leistungsstarke PHP-Template-Engine mit Asset-Kompilierung, Template-Vererbung und Variablenverarbeitung. Bietet automatische CSS/JS-Minifizierung, Caching, Base64-Codierung und optionale Integration mit dem Flight PHP-Framework.

## WordPress Integration

Möchten Sie Flight in Ihrem WordPress-Projekt verwenden? Es gibt ein praktisches Plugin dafür!

- [n0nag0n/wordpress-integration-for-flight-framework](/awesome-plugins/n0nag0n_wordpress) - Dieses WordPress-Plugin ermöglicht es Ihnen, Flight direkt neben WordPress auszuführen. Es ist perfekt, um benutzerdefinierte APIs, Microservices oder sogar vollständige Apps zu Ihrer WordPress-Site mit dem Flight-Framework hinzuzufügen. Super nützlich, wenn Sie das Beste aus beiden Welten wollen!

## Contributing

Haben Sie ein Plugin, das Sie teilen möchten? Reichen Sie einen Pull Request ein, um es zur Liste hinzuzufügen!