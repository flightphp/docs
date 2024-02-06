# Tolle Plugins

Flight ist unglaublich erweiterbar. Es gibt eine Reihe von Plugins, die verwendet werden können, um Funktionalität zu Ihrer Flight-Anwendung hinzuzufügen. Einige werden offiziell vom FlightPHP-Team unterstützt und andere sind Mikro-/Lite-Bibliotheken, um Ihnen beim Einstieg zu helfen.

## Zwischenspeichern

Zwischenspeichern ist ein großartiger Weg, um Ihre Anwendung zu beschleunigen. Es gibt eine Reihe von Zwischenspeicher-Bibliotheken, die mit Flight verwendet werden können.

- [Wruczek/PHP-File-Cache](/awesome-plugins/php-file-cache) - Leichte, einfache und eigenständige PHP-In-Datei-Zwischenspeicherklasse

## Debuggen

Debuggen ist entscheidend beim Entwickeln in Ihrer lokalen Umgebung. Es gibt einige Plugins, die Ihre Debugging-Erfahrung verbessern können.

- [tracy/tracy](/awesome-plugins/tracy) - Dies ist ein voll ausgestatteter Fehlerbehandlungsmechanismus, der mit Flight verwendet werden kann. Er verfügt über eine Reihe von Panels, die Ihnen bei der Fehlersuche in Ihrer Anwendung helfen können. Es ist auch sehr einfach zu erweitern und eigene Panels hinzuzufügen.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - In Verbindung mit dem [Tracy](/awesome-plugins/tracy) Fehlerbehandlungsmechanismus fügt dieses Plugin einige zusätzliche Panels hinzu, um speziell für Flight-Projekte das Debuggen zu erleichtern.

## Datenbanken

Datenbanken sind das Herzstück vieler Anwendungen. So speichern und abrufen Sie Daten. Einige Datenbank-Bibliotheken sind einfach Wrapper zum Schreiben von Abfragen, andere sind vollwertige ORMs.

- [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Offizieller Flight PDO Wrapper, der Teil des Kerns ist. Dies ist ein einfacher Wrapper, der den Prozess des Schreibens von Abfragen und deren Ausführung vereinfachen soll. Es ist kein ORM.
- [flightphp/active-record](/awesome-plugins/active-record) - Offizielles Flight ActiveRecord ORM/Mapper. Tolle kleine Bibliothek zum einfachen Abrufen und Speichern von Daten in Ihrer Datenbank.

## Sitzung

Sitzungen sind nicht wirklich nützlich für APIs, aber für den Aufbau einer Webanwendung können Sitzungen entscheidend sein, um den Zustand und die Anmeldeinformationen zu speichern.

- [Ghostff/Session](/awesome-plugins/session) - PHP-Sitzungsmanager (nicht blockierend, Flash, Segment, Sitzungsverschlüsselung). Verwendet PHP open_ssl zur optionalen Verschlüsselung/Entschlüsselung von Sitzungsdaten.

## Templating

Das Templating ist Kern jeder Webanwendung mit einer Benutzeroberfläche. Es gibt mehrere Templating-Engines, die mit Flight verwendet werden können.

- [flightphp/core View](/learn#views) - Dies ist eine sehr einfache Templating-Engine, die Teil des Kerns ist. Es wird nicht empfohlen, sie zu verwenden, wenn Sie mehr als ein paar Seiten in Ihrem Projekt haben.
- [latte/latte](/awesome-plugins/latte) - Latte ist eine voll ausgestattete Templating-Engine, die sehr einfach zu bedienen ist und sich näher an einer PHP-Syntax als Twig oder Smarty anfühlt. Es ist auch sehr einfach zu erweitern und eigene Filter und Funktionen hinzuzufügen.

## Mitwirken

Hast du ein Plugin, das du teilen möchtest? Reichen Sie einen Pull-Request ein, um es zur Liste hinzuzufügen! 