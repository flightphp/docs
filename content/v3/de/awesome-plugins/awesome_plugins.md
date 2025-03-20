# Fantastische Plugins

Flight ist unglaublich erweiterbar. Es gibt eine Reihe von Plugins, die verwendet werden können, um Funktionalität zu Ihrer Flight-Anwendung hinzuzufügen. Einige werden offiziell vom Flight-Team unterstützt und andere sind Mikro-/Lite-Bibliotheken, um Ihnen den Einstieg zu erleichtern.

## API-Dokumentation

API-Dokumentation ist entscheidend für jede API. Sie hilft Entwicklern zu verstehen, wie sie mit Ihrer API interagieren können und was sie im Gegenzug erwarten können. Es gibt einige Tools, die Ihnen helfen, die API-Dokumentation für Ihre Flight-Projekte zu generieren.

- [FlightPHP OpenAPI Generator](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - Blogbeitrag von Daniel Schreiber darüber, wie man die OpenAPI-Spezifikation mit FlightPHP verwendet, um Ihre API mit einem API-First-Ansatz zu erstellen.
- [SwaggerUI](https://github.com/zircote/swagger-php) - Swagger UI ist ein großartiges Tool, um API-Dokumentationen für Ihre Flight-Projekte zu generieren. Es ist sehr einfach zu bedienen und kann an Ihre Bedürfnisse angepasst werden. Dies ist die PHP-Bibliothek, die Ihnen hilft, die Swagger-Dokumentation zu generieren.

## Anwendungsleistungsüberwachung (APM)

Anwendungsleistungsüberwachung (APM) ist entscheidend für jede Anwendung. Sie hilft Ihnen zu verstehen, wie Ihre Anwendung funktioniert und wo die Engpässe liegen. Es gibt eine Reihe von APM-Tools, die mit Flight verwendet werden können.
- <span class="badge bg-info">beta</span>[flightphp/apm](/awesome-plugins/apm) - Flight APM ist eine einfache APM-Bibliothek, die verwendet werden kann, um Ihre Flight-Anwendungen zu überwachen. Sie kann genutzt werden, um die Leistung Ihrer Anwendung zu überwachen und Engpässe zu identifizieren.

## Authentifizierung/Autorisierung

Authentifizierung und Autorisierung sind entscheidend für jede Anwendung, die Kontrollen benötigt, um festzulegen, wer auf was zugreifen kann. 

- <span class="badge bg-primary">official</span> [flightphp/permissions](/awesome-plugins/permissions) - Offizielle Flight Permissions-Bibliothek. Diese Bibliothek ist eine einfache Möglichkeit, Benutzer- und Anwendungsebene Berechtigungen zu Ihrer Anwendung hinzuzufügen.

## Caching

Caching ist eine großartige Möglichkeit, Ihre Anwendung zu beschleunigen. Es gibt eine Reihe von Caching-Bibliotheken, die mit Flight verwendet werden können.

- <span class="badge bg-primary">official</span> [flightphp/cache](/awesome-plugins/php-file-cache) - Leichte, einfache und eigenständige PHP-In-Datei-Caching-Klasse

## CLI

CLI-Anwendungen sind eine großartige Möglichkeit, mit Ihrer Anwendung zu interagieren. Sie können verwendet werden, um Controller zu generieren, alle Routen anzuzeigen und mehr.

- <span class="badge bg-primary">official</span> [flightphp/runway](/awesome-plugins/runway) - Runway ist eine CLI-Anwendung, die Ihnen hilft, Ihre Flight-Anwendungen zu verwalten.

## Cookies

Cookies sind eine großartige Möglichkeit, kleine Datenmengen auf der Client-Seite zu speichern. Sie können verwendet werden, um Benutzervoreinstellungen, Anwendungseinstellungen und mehr zu speichern.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie ist eine PHP-Bibliothek, die eine einfache und effektive Möglichkeit bietet, Cookies zu verwalten.

## Debugging

Debugging ist entscheidend, wenn Sie in Ihrer lokalen Umgebung entwickeln. Es gibt einige Plugins, die Ihr Debugging-Erlebnis verbessern können.

- [tracy/tracy](/awesome-plugins/tracy) - Dies ist ein voll funktionsfähiger Fehlerhandler, der mit Flight verwendet werden kann. Er hat eine Reihe von Panels, die Ihnen helfen können, Ihre Anwendung zu debuggen. Es ist außerdem sehr einfach zu erweitern und eigene Panels hinzuzufügen.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Wird mit dem [Tracy](/awesome-plugins/tracy) Fehlerhandler verwendet, dieses Plugin fügt einige zusätzliche Panels hinzu, um das Debugging speziell für Flight-Projekte zu erleichtern.

## Datenbanken

Datenbanken sind das Herz der meisten Anwendungen. So speichern und rufen Sie Daten ab. Einige Datenbankbibliotheken sind einfach Wrapper zum Schreiben von Abfragen, während andere vollwertige ORMs sind.

- <span class="badge bg-primary">official</span> [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Offizieller Flight PDO Wrapper, der Teil des Kern ist. Dies ist ein einfacher Wrapper, um den Prozess des Schreibens von Abfragen und deren Ausführung zu erleichtern. Es ist kein ORM.
- <span class="badge bg-primary">official</span> [flightphp/active-record](/awesome-plugins/active-record) - Offizieller Flight ActiveRecord ORM/Mapper. Großartige kleine Bibliothek für das einfache Abrufen und Speichern von Daten in Ihrer Datenbank.
- [byjg/php-migration](/awesome-plugins/migrations) - Plugin zur Verfolgung aller Datenbankänderungen für Ihr Projekt.

## Verschlüsselung

Verschlüsselung ist entscheidend für jede Anwendung, die sensible Daten speichert. Die Verschlüsselung und Entschlüsselung der Daten ist nicht allzu schwierig, aber den Schlüssel zur Verschlüsselung richtig zu speichern [kann](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [schwierig](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [sein](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key). Das Wichtigste ist, Ihren Verschlüsselungsschlüssel niemals in einem öffentlichen Verzeichnis zu speichern oder ihn in Ihr Code-Repository einzuchecken.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - Dies ist eine Bibliothek, die verwendet werden kann, um Daten zu verschlüsseln und zu entschlüsseln. Der Einstieg ist recht einfach, um mit der Verschlüsselung und Entschlüsselung von Daten zu beginnen.

## Job-Queue

Job-Queues sind sehr hilfreich, um Aufgaben asynchron zu verarbeiten. Dies kann das Versenden von E-Mails, die Verarbeitung von Bildern oder alles, was nicht in Echtzeit erledigt werden muss, sein.

- [n0nag0n/simple-job-queue](/awesome-plugins/simple-job-queue) - Simple Job Queue ist eine Bibliothek, die verwendet werden kann, um Jobs asynchron zu verarbeiten. Sie kann mit beanstalkd, MySQL/MariaDB, SQLite und PostgreSQL verwendet werden.

## Sitzung

Sitzungen sind für APIs nicht wirklich nützlich, aber für den Aufbau einer Webanwendung können Sitzungen entscheidend sein, um den Zustand und die Anmeldeinformationen zu erhalten.

- <span class="badge bg-primary">official</span> [flightphp/session](/awesome-plugins/session) - Offizielle Flight Session-Bibliothek. Dies ist eine einfache Sitzungsbibliothek, die verwendet werden kann, um Sitzungsdaten zu speichern und abzurufen. Sie verwendet die integrierte Sitzungsverwaltung von PHP.
- [Ghostff/Session](/awesome-plugins/ghost-session) - PHP Session Manager (nicht-blockierend, Flash, Segment, Sitzungsverschlüsselung). Verwendet PHP open_ssl für die optionale Verschlüsselung/Entschlüsselung von Sitzungsdaten.

## Vorlagenverarbeitung

Vorlagenverarbeitung ist grundlegend für jede Webanwendung mit einer Benutzeroberfläche. Es gibt eine Reihe von Vorlagenmotoren, die mit Flight verwendet werden können.

- <span class="badge bg-warning">deprecated</span> [flightphp/core View](/learn#views) - Dies ist ein sehr einfacher Vorlagenmotor, der Teil des Kern ist. Es wird nicht empfohlen, ihn zu verwenden, wenn Sie mehr als ein paar Seiten in Ihrem Projekt haben.
- [latte/latte](/awesome-plugins/latte) - Latte ist ein voll funktionsfähiger Vorlagenmotor, der sehr einfach zu verwenden ist und sich näher an einer PHP-Syntax anfühlt als Twig oder Smarty. Es ist auch sehr einfach, eigene Filter und Funktionen hinzuzufügen.

## Mitwirken

Haben Sie ein Plugin, das Sie teilen möchten? Reichen Sie einen Pull-Request ein, um es zur Liste hinzuzufügen!