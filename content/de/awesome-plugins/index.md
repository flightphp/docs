# Tolle Plugins

Flight ist unglaublich erweiterbar. Es gibt eine Reihe von Plugins, die verwendet werden können, um Funktionalität zu Ihrer Flight-Anwendung hinzuzufügen. Einige werden offiziell vom FlightPHP-Team unterstützt, andere sind Mikro-/Lite-Bibliotheken, die Ihnen helfen, schnell einzusteigen.

## Caching

Caching ist ein großartiger Weg, um Ihre Anwendung zu beschleunigen. Es gibt eine Reihe von Caching-Bibliotheken, die mit Flight verwendet werden können.

- [Wruczek/PHP-File-Cache](/de/awesome-plugins/php-file-cache) - Leichte, einfache und eigenständige PHP-In-File-Caching-Klasse

## Debugging

Debugging ist entscheidend, wenn Sie in Ihrer lokalen Umgebung entwickeln. Es gibt einige Plugins, die Ihr Debugging-Erlebnis verbessern können.

- [tracy/tracy](/de/awesome-plugins/tracy) - Dies ist ein voll ausgestatteter Fehlerbehandlungsprozessor, der mit Flight verwendet werden kann. Es verfügt über eine Reihe von Panels, die Ihnen helfen können, Ihre Anwendung zu debuggen. Es ist auch sehr einfach zu erweitern und eigene Panels hinzuzufügen.
- [flightphp/tracy-extensions](/de/awesome-plugins/tracy-extensions) - Verwendet mit dem [Tracy](/de/awesome-plugins/tracy) Fehlerbehandlungsprozessor, fügt dieses Plugin einige zusätzliche Panels hinzu, um speziell für Flight-Projekte das Debugging zu erleichtern.

## Datenbanken

Datenbanken sind der Kern vieler Anwendungen. So speichern und abrufen Sie Daten. Einige Datenbankbibliotheken sind einfach Wrapper, um Abfragen zu schreiben, andere sind vollwertige ORMs.

- [flightphp/core PdoWrapper](/de/awesome-plugins/pdo-wrapper) - Offizieller Flight PDO Wrapper, der Teil des Kerns ist. Dies ist ein einfacher Wrapper, der den Prozess des Schreibens von Abfragen und deren Ausführung vereinfachen soll. Es ist kein ORM.
- [flightphp/active-record](/de/awesome-plugins/active-record) - Offizielles Flight ActiveRecord ORM/Mapper. Tolle kleine Bibliothek zum einfachen Abrufen und Speichern von Daten in Ihrer Datenbank.

## Sitzung

Sitzungen sind für APIs nicht wirklich nützlich, aber für den Aufbau einer Webanwendung können Sitzungen entscheidend sein, um den Zustand und die Login-Informationen beizubehalten.

- [Ghostff/Session](/de/awesome-plugins/session) - PHP-Sitzungsmanager (nicht blockierend, Flash, Segment, Sitzungsverschlüsselung). Verwendet PHP open_ssl zur optionalen Verschlüsselung/Entschlüsselung von Sitzungsdaten.

## Templating

Das Templating ist grundlegend für jede Webanwendung mit einer Benutzeroberfläche. Es gibt eine Reihe von Templating-Engines, die mit Flight verwendet werden können.

- [flightphp/core View](/de/learn#views) - Dies ist eine sehr grundlegende Templating-Engine, die Teil des Kerns ist. Es wird nicht empfohlen, wenn Sie mehr als ein paar Seiten in Ihrem Projekt haben.
- [latte/latte](/de/awesome-plugins/latte) - Latte ist eine voll ausgestattete Templating-Engine, die sehr einfach zu verwenden ist und näher an einer PHP-Syntax liegt als Twig oder Smarty. Es ist auch sehr einfach zu erweitern und eigene Filter und Funktionen hinzuzufügen.

## Mitarbeit

Haben Sie ein Plugin, das Sie teilen möchten? Senden Sie einen Pull-Request, um es der Liste hinzuzufügen!