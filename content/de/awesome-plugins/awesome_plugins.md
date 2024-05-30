# Erstaunliche Plugins

Flight ist unglaublich erweiterbar. Es gibt eine Reihe von Plugins, die verwendet werden können, um Funktionalitäten zu Ihrer Flight-Anwendung hinzuzufügen. Einige werden offiziell vom Flight-Team unterstützt, andere sind Mikro-/Lite-Bibliotheken, die Ihnen beim Einstieg helfen.

## Caching

Caching ist eine großartige Möglichkeit, um Ihre Anwendung zu beschleunigen. Es gibt eine Reihe von Caching-Bibliotheken, die mit Flight verwendet werden können.

- [Wruczek/PHP-Datei-Cache](/beeindruckende-plugins/php-datei-cache) - Leichte, einfache und eigenständige PHP-In-File-Caching-Klasse

## CLI

CLI-Anwendungen sind eine großartige Möglichkeit, mit Ihrer Anwendung zu interagieren. Sie können sie verwenden, um Controller zu generieren, alle Routen anzuzeigen und mehr.

- [flightphp/startbahn](/beeindruckende-plugins/startbahn) - Startbahn ist eine CLI-Anwendung, die Ihnen hilft, Ihre Flight-Anwendungen zu verwalten.

## Cookies

Cookies sind eine großartige Möglichkeit, kleine Datenstücke auf der Client-Seite zu speichern. Sie können verwendet werden, um Benutzereinstellungen, Anwendungseinstellungen und mehr zu speichern.

- [overclokk/cookie](/beeindruckende-plugins/php-cookie) - PHP Cookie ist eine PHP-Bibliothek, die einen einfachen und effektiven Weg zum Verwalten von Cookies bietet.

## Debugging

Debugging ist entscheidend, wenn Sie in Ihrer lokalen Umgebung entwickeln. Es gibt einige Plugins, die Ihr Debugging-Erlebnis verbessern können.

- [tracy/tracy](/beeindruckende-plugins/tracy) - Dies ist ein voll ausgestatteter Fehlerbehandlungsmechanismus, der mit Flight verwendet werden kann. Es verfügt über mehrere Panels, die Ihnen beim Debuggen Ihrer Anwendung helfen können. Es ist auch sehr einfach zu erweitern und eigene Panels hinzuzufügen.
- [flightphp/tracy-erweiterungen](/beeindruckende-plugins/tracy-erweiterungen) - Wird mit dem [Tracy](/beeindruckende-plugins/tracy) Fehlerbehandlungsmechanismus verwendet, fügt dieses Plugin einige zusätzliche Panels hinzu, um speziell für Flight-Projekte beim Debuggen zu helfen.

## Datenbanken

Datenbanken sind der Kern vieler Anwendungen. So speichern und abrufen Sie Daten. Einige Datenbank-Bibliotheken sind einfach Wrapper, um Abfragen zu schreiben, andere sind vollwertige ORMs.

- [flightphp/core PdoWrapper](/beeindruckende-plugins/pdo-wrapper) - Offizieller Flight PDO Wrapper, der Teil des Kerns ist. Dies ist ein einfacher Wrapper, der den Prozess des Schreibens von Abfragen und deren Ausführung vereinfachen soll. Es ist kein ORM.
- [flightphp/active-record](/beeindruckende-plugins/active-record) - Offizielles Flight ActiveRecord ORM/Mapper. Tolle kleine Bibliothek zum einfachen Abrufen und Speichern von Daten in Ihrer Datenbank.

## Verschlüsselung

Verschlüsselung ist entscheidend für jede Anwendung, die sensible Daten speichert. Das Verschlüsseln und Entschlüsseln der Daten ist nicht besonders schwierig, aber das ordnungsgemäße Speichern des Verschlüsselungsschlüssels kann schwierig sein. Das Wichtigste ist, Ihren Verschlüsselungsschlüssel niemals in einem öffentlichen Verzeichnis zu speichern oder ihn Ihrem Code-Repository zu übermitteln.

- [defuse/php-verschlüsselung](/beeindruckende-plugins/php-verschlüsselung) - Dies ist eine Bibliothek, die zum Verschlüsseln und Entschlüsseln von Daten verwendet werden kann. Es ist ziemlich einfach, mit der Verschlüsselung und Entschlüsselung von Daten zu beginnen.

## Sitzung

Sitzungen sind für APIs nicht wirklich nützlich, aber für den Aufbau einer Webanwendung können Sitzungen entscheidend sein, um den Zustand und die Anmeldeinformationen zu speichern.

- [Ghostff/Sitzung](/beeindruckende-plugins/sitzung) - PHP Session Manager (nicht blockierend, Flash, Segment, Sitzungsverschlüsselung). Verwendet PHP open_ssl für die optionale Verschlüsselung/Entschlüsselung der Sitzungsdaten.

## Templating

Templating ist für jede Webanwendung mit einer Benutzeroberfläche unerlässlich. Es gibt eine Reihe von Templating-Engines, die mit Flight verwendet werden können.

- [flightphp/core Ansehen](/lernen#ansichten) - Dies ist eine sehr einfache Templating-Engine, die Teil des Kerns ist. Es wird nicht empfohlen, sie zu verwenden, wenn Sie mehr als ein paar Seiten in Ihrem Projekt haben.
- [latte/latte](/beeindruckende-plugins/latte) - Latte ist eine voll ausgestattete Templating-Engine, die sehr einfach zu bedienen ist und sich näher an einer PHP-Syntax als Twig oder Smarty anfühlt. Es ist auch sehr einfach zu erweitern und eigene Filter und Funktionen hinzuzufügen.

## Mitarbeit

Hast du ein Plugin, das du teilen möchtest? Reichen Sie einen Pull-Request ein, um es zur Liste hinzuzufügen!