# Tolle Plugins

Flight ist unglaublich erweiterbar. Es gibt eine Reihe von Plugins, die verwendet werden können, um Funktionen zu Ihrer Flight-Anwendung hinzuzufügen. Einige werden offiziell vom Flight-Team unterstützt und andere sind Mikro-/Lite-Bibliotheken, um Ihnen den Einstieg zu erleichtern.

## API-Dokumentation

API-Dokumentation ist entscheidend für jede API. Sie hilft Entwicklern zu verstehen, wie sie mit Ihrer API interagieren können und was sie im Gegenzug erwarten können. Es stehen einige Tools zur Verfügung, um Ihnen zu helfen, API-Dokumentation für Ihre Flight-Projekte zu erstellen.

- [FlightPHP OpenAPI Generator](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - Blogbeitrag von Daniel Schreiber darüber, wie man die OpenAPI-Spezifikation mit FlightPHP verwendet, um Ihre API mit einem API-First-Ansatz zu erstellen.
- [SwaggerUI](https://github.com/zircote/swagger-php) - Swagger UI ist ein großartiges Tool, um Ihnen zu helfen, API-Dokumentation für Ihre Flight-Projekte zu erstellen. Es ist sehr einfach zu bedienen und kann an Ihre Bedürfnisse angepasst werden. Dies ist die PHP-Bibliothek, die Ihnen hilft, die Swagger-Dokumentation zu erstellen.

## Authentifizierung/Autorisierung

Authentifizierung und Autorisierung sind entscheidend für jede Anwendung, die Kontrollen darüber verlangt, wer auf was zugreifen kann.

- <span class="badge bg-primary">offiziell</span> [flightphp/permissions](/awesome-plugins/permissions) - Offizielle Flight-Berechtigungsbibliothek. Diese Bibliothek ist eine einfache Möglichkeit, Benutzer- und Anwendungsberechtigungen zu Ihrer Anwendung hinzuzufügen.

## Caching

Caching ist eine großartige Möglichkeit, Ihre Anwendung zu beschleunigen. Es gibt eine Reihe von Caching-Bibliotheken, die mit Flight verwendet werden können.

- <span class="badge bg-primary">offiziell</span> [flightphp/cache](/awesome-plugins/php-file-cache) - Leichte, einfache und eigenständige PHP-In-Datei-Caching-Klasse

## CLI

CLI-Anwendungen sind eine großartige Möglichkeit, mit Ihrer Anwendung zu interagieren. Sie können sie verwenden, um Controller zu generieren, alle Routen anzuzeigen und mehr.

- <span class="badge bg-primary">offiziell</span> [flightphp/runway](/awesome-plugins/runway) - Runway ist eine CLI-Anwendung, die Ihnen hilft, Ihre Flight-Anwendungen zu verwalten.

## Cookies

Cookies sind eine großartige Möglichkeit, kleine Datenelemente auf der Client-Seite zu speichern. Sie können verwendet werden, um Benutzereinstellungen, Anwendungsoptionen und mehr zu speichern.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie ist eine PHP-Bibliothek, die eine einfache und effektive Möglichkeit bietet, Cookies zu verwalten.

## Debugging

Debugging ist entscheidend, wenn Sie in Ihrer lokalen Umgebung entwickeln. Es gibt einige Plugins, die Ihr Debugging-Erlebnis verbessern können.

- [tracy/tracy](/awesome-plugins/tracy) - Dies ist ein funktionsreicher Fehlerhandler, der mit Flight verwendet werden kann. Es gibt mehrere Panels, die Ihnen helfen können, Ihre Anwendung zu debuggen. Es ist auch sehr einfach, es zu erweitern und Ihre eigenen Panels hinzuzufügen.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Wird mit dem [Tracy](/awesome-plugins/tracy) Fehlerhandler verwendet, fügt dieses Plugin einige zusätzliche Panels hinzu, um insbesondere für Flight-Projekte beim Debuggen zu helfen.

## Datenbanken

Datenbanken sind der Kern der meisten Anwendungen. So speichern und holen Sie Daten. Einige Datenbankbibliotheken sind einfach Wrapper, um Abfragen zu schreiben, und einige sind vollwertige ORMs.

- <span class="badge bg-primary">offiziell</span> [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Offizieller Flight PDO Wrapper, der Teil des Kerns ist. Dies ist ein einfacher Wrapper, um den Prozess des Schreibens von Abfragen und deren Ausführung zu vereinfachen. Es ist kein ORM.
- <span class="badge bg-primary">offiziell</span> [flightphp/active-record](/awesome-plugins/active-record) - Offizielles Flight ActiveRecord ORM/Mapper. Großartige kleine Bibliothek zum einfachen Abrufen und Speichern von Daten in Ihrer Datenbank.
- [byjg/php-migration](/awesome-plugins/migrations) - Plugin zur Verfolgung aller Datenbankänderungen für Ihr Projekt.

## Verschlüsselung

Verschlüsselung ist entscheidend für jede Anwendung, die sensible Daten speichert. Die Daten zu verschlüsseln und zu entschlüsseln ist nicht allzu schwer, aber das korrekte Speichern des Verschlüsselungsschlüssels [kann](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [schwierig](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [sein](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key). Das Wichtigste ist, Ihren Verschlüsselungsschlüssel niemals in einem öffentlichen Verzeichnis zu speichern oder ihn in Ihrem Code-Repository zu committen.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - Dies ist eine Bibliothek, die verwendet werden kann, um Daten zu verschlüsseln und zu entschlüsseln. Es ist ziemlich einfach, loszulegen und mit der Verschlüsselung und Entschlüsselung von Daten zu beginnen.

## Job-Warteschlange

Job-Warteschlangen sind sehr hilfreich, um Aufgaben asynchron zu verarbeiten. Dies kann das Versenden von E-Mails, das Verarbeiten von Bildern oder alles sein, was nicht in Echtzeit erledigt werden muss.

- [n0nag0n/simple-job-queue](/awesome-plugins/simple-job-queue) - Simple Job Queue ist eine Bibliothek, die verwendet werden kann, um Jobs asynchron zu verarbeiten. Sie kann mit beanstalkd, MySQL/MariaDB, SQLite und PostgreSQL verwendet werden.

## Sitzung

Sitzungen sind für APIs nicht wirklich nützlich, aber für den Aufbau einer Webanwendung können Sitzungen entscheidend sein, um den Status und die Anmeldeinformationen aufrechtzuerhalten.

- <span class="badge bg-primary">offiziell</span> [flightphp/session](/awesome-plugins/session) - Offizielle Flight-Sitzungsbibliothek. Dies ist eine einfache Sitzungsbibliothek, die verwendet werden kann, um Sitzungsdaten zu speichern und abzurufen. Sie verwendet die integrierte Sitzungsverwaltung von PHP.
- [Ghostff/Session](/awesome-plugins/ghost-session) - PHP Session Manager (nicht blockierend, Flash, Segment, Sitzungsverschlüsselung). Verwendet PHP open_ssl für optionale Verschlüsselung/Entschlüsselung von Sitzungsdaten.

## Vorlagen

Vorlagen sind der Kern jeder Webanwendung mit einer UI. Es gibt eine Reihe von Template-Engines, die mit Flight verwendet werden können.

- <span class="badge bg-warning">veraltet</span> [flightphp/core View](/learn#views) - Dies ist eine sehr einfache Template-Engine, die Teil des Kerns ist. Es wird nicht empfohlen, sie zu verwenden, wenn Sie mehr als ein paar Seiten in Ihrem Projekt haben.
- [latte/latte](/awesome-plugins/latte) - Latte ist eine voll ausgestattete Template-Engine, die sehr einfach zu bedienen ist und sich näher an einer PHP-Syntax als an Twig oder Smarty anfühlt. Es ist auch sehr einfach, es zu erweitern und eigene Filter und Funktionen hinzuzufügen.

## Mitwirken

Haben Sie ein Plugin, das Sie teilen möchten? Reichen Sie eine Pull-Anfrage ein, um es zur Liste hinzuzufügen!