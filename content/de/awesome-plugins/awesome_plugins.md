# Beeindruckende Plugins

Flight ist unglaublich erweiterbar. Es gibt eine Reihe von Plugins, die verwendet werden können, um Funktionalität zu Ihrer Flight-Anwendung hinzuzufügen. Einige werden offiziell vom Flight-Team unterstützt, während andere Mikro-/Lite-Bibliotheken sind, die Ihnen helfen, loszulegen.

## Zwischenspeicherung

Das Zwischenspeichern ist ein großartiger Weg, um Ihre Anwendung zu beschleunigen. Es gibt eine Reihe von Zwischenspeicherungsbibliotheken, die mit Flight verwendet werden können.

- [Wruczek/PHP-Datei-Zwischenspeicherung](/beeindruckende-plugins/php-datei-zwischenspeicherung) - Leichte, einfache und eigenständige PHP-Datei-Zwischenspeicherungsklasse

## Cookies

Cookies sind eine großartige Möglichkeit, um kleine Datenstücke auf der Client-Seite zu speichern. Sie können verwendet werden, um Benutzereinstellungen, Anwendungseinstellungen und mehr zu speichern.

- [overclokk/cookie](/beeindruckende-plugins/php-cookie) - PHP-Cookie ist eine PHP-Bibliothek, die eine einfache und effektive Möglichkeit bietet, Cookies zu verwalten.

## Debuggen

Debuggen ist entscheidend, wenn Sie in Ihrer lokalen Umgebung entwickeln. Es gibt einige Plugins, die Ihr Debug-Erlebnis verbessern können.

- [tracy/tracy](/beeindruckende-plugins/tracy) - Dies ist ein voll ausgestatteter Fehlerbehandlungsprogramm, das mit Flight verwendet werden kann. Es hat eine Reihe von Panels, die Ihnen helfen können, Ihre Anwendung zu debuggen. Es ist auch sehr einfach zu erweitern und eigene Panels hinzuzufügen.
- [flightphp/tracy-Erweiterungen](/beeindruckende-plugins/tracy-erweiterungen) - Verwendet mit dem [Tracy](/beeindruckende-plugins/tracy) Fehlerbehandlungsprogramm, fügt dieses Plugin einige zusätzliche Panels hinzu, um das Debuggen speziell für Flight-Projekte zu unterstützen.

## Datenbanken

Datenbanken sind das Herzstück vieler Anwendungen. So speichern und abrufen Sie Daten. Einige Datenbankbibliotheken sind einfach Wrapper, um Abfragen zu schreiben, während andere umfassende ORMs sind.

- [flightphp/core PdoWrapper](/beeindruckende-plugins/pdo-wrapper) - Offizieller Flight PDO Wrapper, der Teil des Kerns ist. Dies ist ein einfacher Wrapper, der dabei hilft, den Prozess des Schreibens von Abfragen und deren Ausführung zu vereinfachen. Es ist kein ORM.
- [flightphp/active-record](/beeindruckende-plugins/active-record) - Offizielles Flight ActiveRecord ORM/Mapper. Tolle kleine Bibliothek zum einfachen Abrufen und Speichern von Daten in Ihrer Datenbank.

## Verschlüsselung

Verschlüsselung ist für jede Anwendung, die sensible Daten speichert, entscheidend. Das Verschlüsseln und Entschlüsseln der Daten ist nicht besonders schwierig, aber das ordnungsgemäße Speichern des Verschlüsselungsschlüssels kann schwierig sein. Das Wichtigste ist, Ihren Verschlüsselungsschlüssel niemals in einem öffentlichen Verzeichnis zu speichern oder ihn in Ihr Code-Repository zu übernehmen.

- [defuse/php-Verschlüsselung](/beeindruckende-plugins/php-verschlüsselung) - Dies ist eine Bibliothek, die verwendet werden kann, um Daten zu verschlüsseln und zu entschlüsseln. Es ist recht einfach, mit der Verschlüsselung und Entschlüsselung von Daten zu beginnen.

## Sitzung

Sitzungen sind für APIs nicht wirklich nützlich, aber für den Aufbau einer Webanwendung können Sitzungen entscheidend sein, um den Zustand und die Anmeldeinformationen aufrechtzuerhalten.

- [Ghostff/Sitzung](/beeindruckende-plugins/sitzung) - PHP-Sitzungsverwalter (nicht blockierend, Flash, Segment, Sitzungsverschlüsselung). Verwendet PHP open_ssl für die optionale Verschlüsselung/Entschlüsselung von Sitzungsdaten.

## Vorlagen

Vorlagen sind wesentlich für jede Webanwendung mit einer Benutzeroberfläche. Es gibt eine Reihe von Vorlagen-Engines, die mit Flight verwendet werden können.

- [flightphp/core View](/lernen#ansichten) - Dies ist eine sehr grundlegende Vorlagen-Engine, die Teil des Kerns ist. Es wird nicht empfohlen, sie zu verwenden, wenn Sie mehr als ein paar Seiten in Ihrem Projekt haben.
- [latte/latte](/beeindruckende-plugins/latte) - Latte ist eine voll ausgestattete Vorlagen-Engine, die sehr einfach zu bedienen ist und sich näher an einer PHP-Syntax als Twig oder Smarty anfühlt. Es ist auch sehr einfach zu erweitern und eigene Filter und Funktionen hinzuzufügen.

## Mitwirken

Haben Sie ein Plugin, das Sie teilen möchten? Reichen Sie einen Pull-Request ein, um es der Liste hinzuzufügen!