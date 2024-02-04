# Beeindruckende Plugins

Flight ist unglaublich erweiterbar. Es gibt mehrere Plugins, die verwendet werden können, um Funktionalitäten zu Ihrem Flight-Anwendung hinzuzufügen. Einige werden offiziell vom FlightPHP-Team unterstützt, während andere Mikro-/Lite-Bibliotheken sind, die Ihnen helfen, loszulegen.

## Caching

Caching ist ein großartiger Weg, um Ihre Anwendung zu beschleunigen. Es gibt mehrere Caching-Bibliotheken, die mit Flight verwendet werden können.

- [Wruczek/PHP-File-Cache](/awesome-plugins/php-file-cache) - Leichte, einfache und eigenständige PHP-In-File-Caching-Klasse

## Debugging

Debugging ist entscheidend, wenn Sie in Ihrer lokalen Umgebung entwickeln. Es gibt einige Plugins, die Ihre Debugging-Erfahrung verbessern können.

- [tracy/tracy](/awesome-plugins/tracy) - Dies ist ein voll ausgestatteter Fehlerbehandler, der mit Flight verwendet werden kann. Es hat mehrere Panels, die Ihnen helfen können, Ihre Anwendung zu debuggen. Es ist auch sehr einfach zu erweitern und eigene Panels hinzuzufügen.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Wird mit dem [Tracy](/awesome-plugins/tracy) Fehlerbehandler verwendet, fügt dieses Plugin einige zusätzliche Panels hinzu, um speziell für Flight-Projekte beim Debuggen zu helfen.

## Datenbanken

Datenbanken sind der Kern vieler Anwendungen. So speichern und abrufen Sie Daten. Einige Datenbank-Bibliotheken sind einfach Wrapper zum Schreiben von Abfragen und einige sind voll ausgestattete ORMs.

- [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Offizieller Flight PDO Wrapper, der Teil des Kerns ist. Dies ist ein einfacher Wrapper, der den Prozess des Schreibens von Abfragen und deren Ausführung vereinfachen soll. Es ist kein ORM.
- [flightphp/active-record](/awesome-plugins/active-record) - Offizielles Flight ActiveRecord ORM/Mapper. Tolle kleine Bibliothek zum einfachen Abrufen und Speichern von Daten in Ihrer Datenbank.

## Sitzung

Sitzungen sind für APIs nicht wirklich nützlich, aber zum Aufbau einer Webanwendung können Sitzungen entscheidend sein, um den Zustand und die Anmeldeinformationen aufrechtzuerhalten.

- [Ghostff/Session](/awesome-plugins/session) - PHP Session Manager (nicht blockierend, Flash, Segment, Session Verschlüsselung). Verwendet PHP open_ssl für die optionale Verschlüsselung/Entschlüsselung von Sitzungsdaten.

## Template

Templates sind für jede Webanwendung mit einer Benutzeroberfläche von zentraler Bedeutung. Es gibt eine Reihe von Template-Engines, die mit Flight verwendet werden können.

- [flightphp/core View](/learn#views) - Dies ist eine sehr grundlegende Template-Engine, die Teil des Kerns ist. Es wird nicht empfohlen, sie zu verwenden, wenn Sie mehr als ein paar Seiten in Ihrem Projekt haben.
- [latte/latte](/awesome-plugins/latte) - Latte ist eine umfassende Template-Engine, die sehr einfach zu bedienen ist und sich näher an einer PHP-Syntax als Twig oder Smarty anfühlt. Es ist auch sehr einfach zu erweitern und eigene Filter und Funktionen hinzuzufügen.

## Mitwirken

Haben Sie ein Plugin, das Sie teilen möchten? Senden Sie einen Pull-Request, um es der Liste hinzuzufügen!