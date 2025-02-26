# Atemberaubende Plugins

Flight ist unglaublich erweiterbar. Es gibt eine Reihe von Plugins, die verwendet werden können, um Funktionalität zu Ihrer Flight-Anwendung hinzuzufügen. Einige werden offiziell vom Flight-Team unterstützt, während andere Mikro-/Lite-Bibliotheken sind, um Ihnen den Einstieg zu erleichtern.

## API-Dokumentation

API-Dokumentation ist entscheidend für jede API. Sie hilft Entwicklern zu verstehen, wie sie mit Ihrer API interagieren können und was sie im Gegenzug erwarten können. Es gibt ein paar Tools, die Ihnen helfen können, API-Dokumentation für Ihre Flight-Projekte zu erstellen.

- [FlightPHP OpenAPI Generator](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - Blogbeitrag von Daniel Schreiber, wie man den OpenAPI-Generator mit FlightPHP verwendet, um API-Dokumentation zu generieren.
- [Swagger UI](https://github.com/zircote/swagger-php) - Swagger UI ist ein großartiges Tool, um Ihnen bei der Erstellung von API-Dokumentation für Ihre Flight-Projekte zu helfen. Es ist sehr einfach zu benutzen und kann an Ihre Bedürfnisse angepasst werden. Dies ist die PHP-Bibliothek, die Ihnen hilft, die Swagger-Dokumentation zu generieren.

## Authentifizierung/Autorisierung

Authentifizierung und Autorisierung sind entscheidend für jede Anwendung, die Kontrollen erfordert, um festzulegen, wer auf was zugreifen kann.

- [flightphp/permissions](/awesome-plugins/permissions) - Offizielle Flight-Berechtigungsbibliothek. Diese Bibliothek ist eine einfache Möglichkeit, Benutzer- und Anwendungsebene-Berechtigungen zu Ihrer Anwendung hinzuzufügen.

## Caching

Caching ist eine großartige Möglichkeit, Ihre Anwendung zu beschleunigen. Es gibt eine Reihe von Caching-Bibliotheken, die mit Flight verwendet werden können.

- [flightphp/cache](/awesome-plugins/php-file-cache) - Leichte, einfache und eigenständige PHP-In-Datei-Caching-Klasse

## CLI

CLI-Anwendungen sind eine großartige Möglichkeit, mit Ihrer Anwendung zu interagieren. Sie können sie verwenden, um Controller zu generieren, alle Routen anzuzeigen und mehr.

- [flightphp/runway](/awesome-plugins/runway) - Runway ist eine CLI-Anwendung, die Ihnen hilft, Ihre Flight-Anwendungen zu verwalten.

## Cookies

Cookies sind eine großartige Möglichkeit, kleine Datenmengen auf der Client-Seite zu speichern. Sie können verwendet werden, um Benutzerpräferenzen, Anwendungseinstellungen und mehr zu speichern.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie ist eine PHP-Bibliothek, die eine einfache und effektive Möglichkeit bietet, Cookies zu verwalten.

## Debugging

Debugging ist entscheidend, wenn Sie in Ihrer lokalen Umgebung entwickeln. Es gibt einige Plugins, die Ihr Debugging-Erlebnis verbessern können.

- [tracy/tracy](/awesome-plugins/tracy) - Dies ist ein voll ausgestatteter Fehlerbehandler, der mit Flight verwendet werden kann. Er hat eine Reihe von Panels, die Ihnen beim Debuggen Ihrer Anwendung helfen können. Es ist auch sehr einfach, ihn zu erweitern und eigene Panels hinzuzufügen.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Wird mit dem [Tracy](/awesome-plugins/tracy) Fehlerbehandler verwendet, fügt dieses Plugin einige zusätzliche Panels hinzu, um speziell beim Debuggen von Flight-Projekten zu helfen.

## Datenbanken

Datenbanken sind das Rückgrat der meisten Anwendungen. So speichern und abrufen Sie Daten. Einige Datenbankbibliotheken sind einfach Wrapper, um Abfragen zu schreiben, und einige sind vollwertige ORMs.

- [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Offizieller Flight PDO Wrapper, der Teil des Kerns ist. Dies ist ein einfacher Wrapper, um den Prozess des Schreibens von Abfragen zu vereinfachen und sie auszuführen. Es ist kein ORM.
- [flightphp/active-record](/awesome-plugins/active-record) - Offizielles Flight ActiveRecord ORM/Mapper. Großartige kleine Bibliothek, um Daten in Ihrer Datenbank einfach abzurufen und zu speichern.
- [byjg/php-migration](/awesome-plugins/migrations) - Plugin, um alle Datenbankänderungen für Ihr Projekt nachzuverfolgen.

## Verschlüsselung

Verschlüsselung ist entscheidend für jede Anwendung, die sensible Daten speichert. Das Verschlüsseln und Entschlüsseln der Daten ist nicht besonders schwierig, aber die richtige Speicherung des Verschlüsselungsschlüssels [kann](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [schwierig](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [sein](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key). Das Wichtigste ist, Ihren Verschlüsselungsschlüssel niemals in einem öffentlichen Verzeichnis zu speichern oder ihn in Ihr Code-Repository einzuchecken.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - Dies ist eine Bibliothek, die verwendet werden kann, um Daten zu verschlüsseln und zu entschlüsseln. Es ist ziemlich einfach, um zu beginnen, Daten zu verschlüsseln und zu entschlüsseln.

## Sitzung

Sitzungen sind für APIs nicht wirklich nützlich, aber für den Aufbau einer Webanwendung können Sitzungen entscheidend sein, um den Zustand und die Anmeldeinformationen aufrechtzuerhalten.

- [Ghostff/Session](/awesome-plugins/session) - PHP Sitzungsmanager (nicht blockierend, Flash, Segment, Sitzungverschlüsselung). Verwendet PHP open_ssl für optionale Verschlüsselung/Entschlüsselung von Sitzungsdaten.

## Vorlagen

Vorlagen sind entscheidend für jede Webanwendung mit einer UI. Es gibt eine Reihe von Vorlagengeneratoren, die mit Flight verwendet werden können.

- [flightphp/core View](/learn#views) - Dies ist ein sehr einfacher Vorlagengenerator, der Teil des Kerns ist. Es wird nicht empfohlen, ihn zu verwenden, wenn Sie mehr als ein paar Seiten in Ihrem Projekt haben.
- [latte/latte](/awesome-plugins/latte) - Latte ist ein voll ausgestatteter Vorlagengenerator, der sehr einfach zu benutzen ist und sich näher an einer PHP-Syntax als Twig oder Smarty anfühlt. Es ist auch sehr einfach, ihn zu erweitern und eigene Filter und Funktionen hinzuzufügen.

## Mitwirken

Haben Sie ein Plugin, das Sie teilen möchten? Reichen Sie eine Pull-Request ein, um es zur Liste hinzuzufügen!