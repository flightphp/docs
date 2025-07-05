# Tolle Plugins

Flight ist unglaublich erweiterbar. Es gibt eine Reihe von Plugins, die verwendet werden können, um Funktionen zu Ihrer Flight-Anwendung hinzuzufügen. Einige werden offiziell vom Flight-Team unterstützt und andere sind Micro-/Lite-Bibliotheken, um Ihnen den Einstieg zu erleichtern.

## API-Dokumentation

API-Dokumentation ist entscheidend für jede API. Sie hilft Entwicklern zu verstehen, wie sie mit Ihrer API interagieren und was sie erwarten können. Es gibt ein paar Tools, die Ihnen helfen, API-Dokumentation für Ihre Flight-Projekte zu generieren.

- [FlightPHP OpenAPI Generator](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - Blog-Beitrag von Daniel Schreiber, der erklärt, wie man die OpenAPI-Spezifikation mit FlightPHP verwendet, um Ihre API mit einem API-first-Ansatz aufzubauen.
- [SwaggerUI](https://github.com/zircote/swagger-php) - Swagger UI ist ein großartiges Tool, um API-Dokumentation für Ihre Flight-Projekte zu generieren. Es ist sehr einfach zu verwenden und kann an Ihre Bedürfnisse angepasst werden. Dies ist die PHP-Bibliothek, die Ihnen bei der Generierung der Swagger-Dokumentation hilft.

## Anwendungsleistungsüberwachung (APM)

Anwendungsleistungsüberwachung (APM) ist entscheidend für jede Anwendung. Sie hilft Ihnen zu verstehen, wie Ihre Anwendung performt und wo die Engpässe liegen. Es gibt eine Reihe von APM-Tools, die mit Flight verwendet werden können.
- <span class="badge bg-info">beta</span>[flightphp/apm](/awesome-plugins/apm) - Flight APM ist eine einfache APM-Bibliothek, die verwendet werden kann, um Ihre Flight-Anwendungen zu überwachen. Sie kann verwendet werden, um die Leistung Ihrer Anwendung zu messen und Engpässe zu identifizieren.

## Authentifizierung/Autorisierung

Authentifizierung und Autorisierung sind entscheidend für jede Anwendung, die Kontrollen für den Zugriff benötigt.

- <span class="badge bg-primary">official</span> [flightphp/permissions](/awesome-plugins/permissions) - Offizielle Flight Permissions-Bibliothek. Diese Bibliothek ist ein einfacher Weg, um Benutzer- und Anwendungsebene-Berechtigungen zu Ihrer Anwendung hinzuzufügen. 

## Caching

Caching ist eine großartige Möglichkeit, Ihre Anwendung zu beschleunigen. Es gibt eine Reihe von Caching-Bibliotheken, die mit Flight verwendet werden können.

- <span class="badge bg-primary">official</span> [flightphp/cache](/awesome-plugins/php-file-cache) - Leichtes, einfaches und eigenständiges PHP-In-Datei-Caching-Klasse

## CLI

CLI-Anwendungen sind eine großartige Möglichkeit, mit Ihrer Anwendung zu interagieren. Sie können verwendet werden, um Controller zu generieren, alle Routen anzuzeigen und mehr.

- <span class="badge bg-primary">official</span> [flightphp/runway](/awesome-plugins/runway) - Runway ist eine CLI-Anwendung, die Ihnen bei der Verwaltung Ihrer Flight-Anwendungen hilft.

## Cookies

Cookies sind eine großartige Möglichkeit, kleine Datenstücke auf der Client-Seite zu speichern. Sie können verwendet werden, um Benutzerpräferenzen, Anwendungseinstellungen und mehr zu speichern.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie ist eine PHP-Bibliothek, die einen einfachen und effektiven Weg bietet, Cookies zu verwalten.

## Debugging

Debugging ist entscheidend, wenn Sie in Ihrer lokalen Umgebung entwickeln. Es gibt ein paar Plugins, die Ihr Debugging-Erlebnis verbessern können.

- [tracy/tracy](/awesome-plugins/tracy) - Dies ist ein vollständig ausgestatteter Fehlerhandler, der mit Flight verwendet werden kann. Er hat eine Reihe von Panels, die Ihnen bei der Fehlersuche in Ihrer Anwendung helfen. Er ist auch sehr einfach zu erweitern und eigene Panels hinzuzufügen.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Wird mit dem [Tracy](/awesome-plugins/tracy) Fehlerhandler verwendet, dieses Plugin fügt ein paar extra Panels hinzu, um bei der Fehlersuche speziell für Flight-Projekte zu helfen.

## Datenbanken

Datenbanken sind der Kern der meisten Anwendungen. So speichern und abrufen Sie Daten. Einige Datenbank-Bibliotheken sind einfach Wrapper, um Abfragen zu schreiben, und einige sind vollständige ORMs.

- <span class="badge bg-primary">official</span> [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Offizieller Flight PDO-Wrapper, der zum Kern gehört. Dies ist ein einfacher Wrapper, um den Prozess des Schreibens und Ausführens von Abfragen zu vereinfachen. Es handelt sich nicht um ein ORM.
- <span class="badge bg-primary">official</span> [flightphp/active-record](/awesome-plugins/active-record) - Offizielles Flight ActiveRecord ORM/Mapper. Tolle kleine Bibliothek zum einfachen Abrufen und Speichern von Daten in Ihrer Datenbank.
- [byjg/php-migration](/awesome-plugins/migrations) - Plugin zur Nachverfolgung aller Datenbankänderungen für Ihr Projekt.

## Verschlüsselung

Verschlüsselung ist entscheidend für jede Anwendung, die sensible Daten speichert. Das Verschlüsseln und Entschlüsseln der Daten ist nicht besonders schwierig, aber das ordnungsgemäße Speichern des Verschlüsselungsschlüssels [kann](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [sein](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [schwierig](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key). Das Wichtigste ist, Ihren Verschlüsselungsschlüssel niemals in einem öffentlichen Verzeichnis zu speichern oder ihn in Ihr Code-Repository zu commiten.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - Dies ist eine Bibliothek, die verwendet werden kann, um Daten zu verschlüsseln und zu entschlüsseln. Der Einstieg ist relativ einfach, um mit dem Verschlüsseln und Entschlüsseln von Daten zu beginnen.

## Job-Warteschlange

Job-Warteschlangen sind sehr hilfreich, um Aufgaben asynchron zu verarbeiten. Das kann das Senden von E-Mails, das Verarbeiten von Bildern oder alles bedeuten, was nicht in Echtzeit erfolgen muss.

- [n0nag0n/simple-job-queue](/awesome-plugins/simple-job-queue) - Simple Job Queue ist eine Bibliothek, die verwendet werden kann, um Jobs asynchron zu verarbeiten. Sie kann mit beanstalkd, MySQL/MariaDB, SQLite und PostgreSQL verwendet werden.

## Session

Sitzungen sind für APIs nicht wirklich nützlich, aber beim Aufbau einer Web-Anwendung können Sitzungen entscheidend sein, um den Status und Login-Informationen aufrechtzuerhalten.

- <span class="badge bg-primary">official</span> [flightphp/session](/awesome-plugins/session) - Offizielle Flight Session-Bibliothek. Dies ist eine einfache Session-Bibliothek, die verwendet werden kann, um Sitzungsdaten zu speichern und abzurufen. Sie verwendet die eingebaute Sitzungsverwaltung von PHP.
- [Ghostff/Session](/awesome-plugins/ghost-session) - PHP Session-Manager (nicht blockierend, Flash, Segment, Sitzungsverschlüsselung). Verwaltet PHP open_ssl für optionale Verschlüsselung/Entschlüsselung von Sitzungsdaten.

## Vorlagen

Vorlagen sind der Kern jeder Web-Anwendung mit einer Benutzeroberfläche. Es gibt eine Reihe von Vorlagen-Engines, die mit Flight verwendet werden können.

- <span class="badge bg-warning">deprecated</span> [flightphp/core View](/learn#views) - Dies ist ein sehr grundlegender Vorlagen-Engine, der zum Kern gehört. Es wird nicht empfohlen, ihn zu verwenden, wenn Ihr Projekt mehr als ein paar Seiten hat.
- [latte/latte](/awesome-plugins/latte) - Latte ist ein vollständig ausgestatteter Vorlagen-Engine, der sehr einfach zu verwenden ist und sich näher an der PHP-Syntax als Twig oder Smarty anfühlt. Er ist auch sehr einfach zu erweitern und eigene Filter und Funktionen hinzuzufügen.

## WordPress-Integration

Wollen Sie Flight in Ihrem WordPress-Projekt verwenden? Es gibt ein praktisches Plugin dafür!

- [n0nag0n/wordpress-integration-for-flight-framework](/awesome-plugins/n0nag0n_wordpress) - Dieses WordPress-Plugin ermöglicht es Ihnen, Flight direkt neben WordPress auszuführen. Es ist perfekt, um benutzerdefinierte APIs, Microservices oder sogar vollständige Apps zu Ihrer WordPress-Site hinzuzufügen, unter Verwendung des Flight-Frameworks. Super nützlich, wenn Sie das Beste aus beiden Welten wollen!

## Beitrag

Haben Sie ein Plugin, das Sie teilen möchten? Reichen Sie einen Pull-Request ein, um es zur Liste hinzuzufügen!