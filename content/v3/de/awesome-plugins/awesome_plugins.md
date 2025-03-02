# Fantastische Plugins

Flight ist unglaublich erweiterbar. Es gibt eine Reihe von Plugins, die verwendet werden können, um Funktionalitäten zu Ihrer Flight-Anwendung hinzuzufügen. Einige werden offiziell vom Flight-Team unterstützt, während andere Mikro-/Lite-Bibliotheken sind, die Ihnen den Einstieg erleichtern.

## API-Dokumentation

API-Dokumentation ist entscheidend für jede API. Sie hilft Entwicklern zu verstehen, wie sie mit Ihrer API interagieren können und was sie im Gegenzug erwarten können. Es stehen ein paar Tools zur Verfügung, um Ihnen zu helfen, API-Dokumentation für Ihre Flight-Projekte zu erstellen.

- [FlightPHP OpenAPI Generator](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - Blogbeitrag von Daniel Schreiber darüber, wie man die OpenAPI-Spezifikation mit FlightPHP verwendet, um Ihre API mit einem API-First-Ansatz zu erstellen.
- [SwaggerUI](https://github.com/zircote/swagger-php) - Swagger UI ist ein großartiges Tool, das Ihnen hilft, API-Dokumentation für Ihre Flight-Projekte zu erstellen. Es ist sehr einfach zu bedienen und kann an Ihre Bedürfnisse angepasst werden. Dies ist die PHP-Bibliothek, die Ihnen hilft, die Swagger-Dokumentation zu erstellen.

## Authentifizierung/Autorisierung

Authentifizierung und Autorisierung sind entscheidend für jede Anwendung, die Kontrollen darüber benötigt, wer auf was zugreifen kann.

- [flightphp/permissions](/awesome-plugins/permissions) - Offizielle Flight-Berechtigungsbibliothek. Diese Bibliothek ist eine einfache Möglichkeit, Benutzer- und Anwendungsberechtigungen zu Ihrer Anwendung hinzuzufügen.

## Caching

Caching ist eine großartige Möglichkeit, Ihre Anwendung zu beschleunigen. Es gibt eine Reihe von Caching-Bibliotheken, die mit Flight verwendet werden können.

- [flightphp/cache](/awesome-plugins/php-file-cache) - Leichte, einfache und eigenständige PHP-In-Datei-Caching-Klasse.

## CLI

CLI-Anwendungen sind eine großartige Möglichkeit, mit Ihrer Anwendung zu interagieren. Sie können damit Controller generieren, alle Routen anzeigen und mehr.

- [flightphp/runway](/awesome-plugins/runway) - Runway ist eine CLI-Anwendung, die Ihnen hilft, Ihre Flight-Anwendungen zu verwalten.

## Cookies

Cookies sind eine großartige Möglichkeit, kleine Datenmengen auf der Client-Seite zu speichern. Sie können verwendet werden, um Benutzereinstellungen, Anwendungseinstellungen und mehr zu speichern.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie ist eine PHP-Bibliothek, die eine einfache und effektive Möglichkeit bietet, Cookies zu verwalten.

## Debugging

Debugging ist entscheidend, wenn Sie in Ihrer lokalen Umgebung entwickeln. Es gibt einige Plugins, die Ihr Debugging-Erlebnis verbessern können.

- [tracy/tracy](/awesome-plugins/tracy) - Dies ist ein vollständig ausgestatteter Fehlerhandler, der mit Flight verwendet werden kann. Er hat eine Reihe von Panels, die Ihnen helfen können, Ihre Anwendung zu debuggen. Es ist auch sehr einfach zu erweitern und eigene Panels hinzuzufügen.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Wird mit dem [Tracy](/awesome-plugins/tracy) Fehlerhandler verwendet, fügt dieses Plugin einige extra Panels hinzu, um speziell für Flight-Projekte beim Debugging zu helfen.

## Datenbanken

Datenbanken sind das Herzstück der meisten Anwendungen. So speichern und abrufen Sie Daten. Einige Datenbankbibliotheken sind einfach Wrapper, um Abfragen zu schreiben, und einige sind vollwertige ORMs.

- [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Offizieller Flight PDO Wrapper, der Teil des Kerns ist. Dies ist ein einfacher Wrapper, um den Prozess des Schreibens von Abfragen und deren Ausführung zu vereinfachen. Es ist kein ORM.
- [flightphp/active-record](/awesome-plugins/active-record) - Offizielles Flight ActiveRecord ORM/Mapper. Tolle kleine Bibliothek, um Daten in Ihrer Datenbank einfach abzurufen und zu speichern.
- [byjg/php-migration](/awesome-plugins/migrations) - Plugin, um alle Datenbankänderungen für Ihr Projekt zu verfolgen.

## Verschlüsselung

Verschlüsselung ist entscheidend für jede Anwendung, die sensible Daten speichert. Daten zu verschlüsseln und zu entschlüsseln ist nicht besonders schwer, aber den Verschlüsselungsschlüssel richtig zu speichern [kann](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [schwierig](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [sein](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key). Das Wichtigste ist, den Verschlüsselungsschlüssel niemals in einem öffentlichen Verzeichnis zu speichern oder ihn in Ihr Code-Repository einzufügen.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - Dies ist eine Bibliothek, die verwendet werden kann, um Daten zu verschlüsseln und zu entschlüsseln. Der Einstieg ist recht einfach, um mit der Verschlüsselung und Entschlüsselung von Daten zu beginnen.

## Job-Warteschlange

Job-Warteschlangen sind sehr hilfreich, um Aufgaben asynchron zu verarbeiten. Dies kann das Versenden von E-Mails, das Verarbeiten von Bildern oder alles sein, was nicht in Echtzeit erledigt werden muss.

- [n0nag0n/simple-job-queue](/awesome-plugins/simple-job-queue) - Simple Job Queue ist eine Bibliothek, die verwendet werden kann, um Jobs asynchron zu verarbeiten. Sie kann mit beanstalkd, MySQL/MariaDB, SQLite und PostgreSQL verwendet werden.

## Sitzung

Sitzungen sind für APIs nicht wirklich nützlich, aber beim Aufbau einer Webanwendung können Sitzungen entscheidend sein, um den Zustand und die Anmeldeinformationen zu erhalten.

- [Ghostff/Session](/awesome-plugins/session) - PHP-Sitzungsmanager (nicht blockierend, Flash, Segment, Sitzungsverschlüsselung). Verwendet PHP open_ssl für die optionale Verschlüsselung/Entschlüsselung von Sitzungsdaten.

## Template

Templates sind das Herzstück jeder Webanwendung mit einer Benutzeroberfläche. Es gibt eine Reihe von Template-Engines, die mit Flight verwendet werden können.

- [flightphp/core View](/learn#views) - Dies ist eine sehr grundlegende Template-Engine, die Teil des Kerns ist. Es wird nicht empfohlen, sie zu verwenden, wenn Sie mehr als ein paar Seiten in Ihrem Projekt haben.
- [latte/latte](/awesome-plugins/latte) - Latte ist eine voll ausgestattete Template-Engine, die sehr einfach zu bedienen ist und sich näher an einer PHP-Syntax als Twig oder Smarty anfühlt. Es ist auch sehr einfach zu erweitern und eigene Filter und Funktionen hinzuzufügen.

## Mitwirken

Haben Sie ein Plugin, das Sie teilen möchten? Reichen Sie eine Pull-Anfrage ein, um es zur Liste hinzuzufügen!