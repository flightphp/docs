# Tolle Plugins

Flight ist unglaublich erweiterbar. Es gibt eine Reihe von Plugins, die dazu verwendet werden können, Funktionalitäten Ihrer Flight-Anwendung hinzuzufügen. Einige werden offiziell vom Flight-Team unterstützt, während andere Mikro-/Lite-Bibliotheken sind, um Ihnen den Einstieg zu erleichtern.

## Caching

Caching ist ein großartiger Weg, um Ihre Anwendung zu beschleunigen. Es gibt einige Caching-Bibliotheken, die mit Flight verwendet werden können.

- [Wruczek/PHP-File-Cache](/awesome-plugins/php-file-cache) - Leichte, einfache und eigenständige PHP-In-File-Caching-Klasse

## Debugging

Debugging ist entscheidend, wenn Sie in Ihrer lokalen Umgebung entwickeln. Es gibt einige Plugins, die Ihr Debugging-Erlebnis verbessern können.

- [tracy/tracy](/awesome-plugins/tracy) - Dies ist ein voll ausgestatteter Fehlerbehandlungsmechanismus, der mit Flight verwendet werden kann. Es verfügt über mehrere Panels, die Ihnen beim Debuggen Ihrer Anwendung helfen können. Es ist auch sehr einfach zu erweitern und eigene Panels hinzuzufügen.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Wird mit dem [Tracy](/awesome-plugins/tracy) Fehlerbehandler verwendet, fügt dieses Plugin einige zusätzliche Panels hinzu, die speziell für das Debuggen von Flight-Projekten hilfreich sind.

## Datenbanken

Datenbanken sind das Herzstück vieler Anwendungen. So speichern und abrufen Sie Daten. Einige Datenbank-Bibliotheken sind einfach Wrapper zum Schreiben von Abfragen, während andere vollwertige ORMs sind.

- [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Offizieller Flight PDO Wrapper, der zum Kern gehört. Dies ist ein einfacher Wrapper, der den Prozess des Schreibens von Abfragen und deren Ausführung vereinfacht. Es ist kein ORM.
- [flightphp/active-record](/awesome-plugins/active-record) - Offizielles Flight ActiveRecord ORM/Mapper. Tolle kleine Bibliothek zum einfachen Abrufen und Speichern von Daten in Ihrer Datenbank.

## Sitzung

Sitzungen sind nicht wirklich nützlich für APIs, aber beim Erstellen einer Webanwendung können Sitzungen wichtig sein, um Zustands- und Anmeldeinformationen zu speichern.

- [Ghostff/Session](/awesome-plugins/session) - PHP-Session-Manager (nicht blockierend, Flash, Segment, Sitzungsverschlüsselung). Verwendet PHP open_ssl für die optionale Verschlüsselung/Entschlüsselung von Sitzungsdaten.

## Templating

Templates sind für jede Webanwendung mit einer Benutzeroberfläche unerlässlich. Es gibt eine Reihe von Templating-Engines, die mit Flight verwendet werden können.

- [flightphp/core View](/learn#views) - Dies ist eine sehr einfache Templating-Engine, die zum Kern gehört. Es wird nicht empfohlen, sie zu verwenden, wenn Sie mehr als ein paar Seiten in Ihrem Projekt haben.
- [latte/latte](/awesome-plugins/latte) - Latte ist eine voll ausgestattete Templating-Engine, die sehr einfach zu verwenden ist und sich näher an der PHP-Syntax als Twig oder Smarty anfühlt. Es ist auch sehr einfach zu erweitern und eigene Filter und Funktionen hinzuzufügen.

## Mitwirken

Haben Sie ein Plugin, das Sie teilen möchten? Senden Sie einen Pull-Request, um es der Liste hinzuzufügen!