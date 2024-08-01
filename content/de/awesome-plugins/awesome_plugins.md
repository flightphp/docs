# Fantastische Plugins

Flight ist unglaublich erweiterbar. Es gibt eine Anzahl von Plugins, die verwendet werden können, um Funktionalitäten zu Ihrer Flight-Anwendung hinzuzufügen. Einige werden offiziell vom Flight-Team unterstützt, andere sind Micro/Lite-Bibliotheken, um Ihnen den Einstieg zu erleichtern.

## Authentifizierung/Berechtigung

Authentifizierung und Berechtigung sind entscheidend für jede Anwendung, die Steuerungen erfordert, um festzulegen, wer auf was zugreifen kann. 

- [flightphp/permissions](/awesome-plugins/permissions) - Offizielle Flight-Berechtigungs-Bibliothek. Diese Bibliothek ist eine einfache Möglichkeit, Benutzer- und Anwendungsebene-Berechtigungen zu Ihrer Anwendung hinzuzufügen.

## Zwischenspeicher

Der Zwischenspeicher ist eine großartige Möglichkeit, um Ihre Anwendung zu beschleunigen. Es gibt eine Anzahl von Zwischenspeicher-Bibliotheken, die mit Flight verwendet werden können.

- [Wruczek/PHP-File-Cache](/awesome-plugins/php-file-cache) - Leichte, einfache und eigenständige PHP-In-Datei-Zwischenspeicher-Klasse

## CLI

CLI-Anwendungen sind eine großartige Möglichkeit, mit Ihrer Anwendung zu interagieren. Sie können sie verwenden, um Controller zu generieren, alle Routen anzuzeigen und mehr.

- [flightphp/runway](/awesome-plugins/runway) - Runway ist eine CLI-Anwendung, die Ihnen bei der Verwaltung Ihrer Flight-Anwendungen hilft.

## Cookies

Cookies sind eine großartige Möglichkeit, kleine Datenstücke auf der Clientseite zu speichern. Sie können verwendet werden, um Benutzereinstellungen, Anwendungseinstellungen und mehr zu speichern.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie ist eine PHP-Bibliothek, die eine einfache und effektive Möglichkeit zur Verwaltung von Cookies bietet.

## Debuggen

Debuggen ist entscheidend, wenn Sie in Ihrer lokalen Umgebung entwickeln. Es gibt ein paar Plugins, die Ihr Debugging-Erlebnis verbessern können.

- [tracy/tracy](/awesome-plugins/tracy) - Dies ist ein voll ausgestatteter Fehlerbehandlungsmechanismus, der mit Flight verwendet werden kann. Er verfügt über eine Reihe von Panels, die Ihnen beim Debuggen Ihrer Anwendung helfen können. Es ist auch sehr einfach zu erweitern und eigene Panels hinzuzufügen.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Wird mit dem [Tracy](/awesome-plugins/tracy) Fehlerbehandlungsmechanismus verwendet, fügt dieses Plugin einige zusätzliche Panels hinzu, die speziell für das Debuggen von Flight-Projekten hilfreich sind.

## Datenbanken

Datenbanken sind das Herzstück vieler Anwendungen. So speichern und abrufen Sie Daten. Einige Datenbank-Bibliotheken sind einfach Wrapper, um Abfragen zu schreiben, während andere vollständige ORMs sind.

- [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Offizieller Flight PDO Wrapper, der Teil des Kerns ist. Dies ist ein einfacher Wrapper, um den Prozess des Schreibens von Abfragen und deren Ausführung zu vereinfachen. Es handelt sich nicht um ein ORM.
- [flightphp/active-record](/awesome-plugins/active-record) - Offizieller Flight ActiveRecord ORM/Mapper. Tolle kleine Bibliothek zum einfachen Abrufen und Speichern von Daten in Ihrer Datenbank.

## Verschlüsselung

Verschlüsselung ist entscheidend für jede Anwendung, die sensible Daten speichert. Das Verschlüsseln und Entschlüsseln der Daten ist nicht besonders schwierig, aber das ordnungsgemäße Speichern des Verschlüsselungsschlüssels kann schwierig sein. Das Wichtigste ist, den Verschlüsselungsschlüssel niemals in einem öffentlichen Verzeichnis zu speichern oder ihn in Ihr Code-Repository zu übernehmen.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - Dies ist eine Bibliothek, die verwendet werden kann, um Daten zu verschlüsseln und zu entschlüsseln. Das Einrichten ist recht einfach, um mit der Verschlüsselung und Entschlüsselung von Daten zu beginnen.

## Sitzung

Sitzungen sind für APIs nicht wirklich nützlich, aber für den Aufbau einer Webanwendung können Sitzungen entscheidend sein, um den Zustand und die Anmeldeinformationen aufrechtzuerhalten.

- [Ghostff/Session](/awesome-plugins/session) - PHP-Sitzungsmanager (nicht blockierend, Flash, Segment, Sitzungsverschlüsselung). Verwendet PHP open_ssl für die optionale Verschlüsselung/Entschlüsselung von Sitzungsdaten.

## Template

Vorlagen sind elementar für jede Webanwendung mit einer Benutzeroberfläche. Es gibt eine Anzahl von Template-Engines, die mit Flight verwendet werden können.

- [flightphp/core View](/learn#views) - Dies ist eine sehr grundlegende Template-Engine, die Teil des Kerns ist. Es wird nicht empfohlen, sie zu verwenden, wenn Sie mehr als ein paar Seiten in Ihrem Projekt haben.
- [latte/latte](/awesome-plugins/latte) - Latte ist eine voll ausgestattete Template-Engine, die sehr einfach zu bedienen ist und näher an einer PHP-Syntax als Twig oder Smarty liegt. Es ist auch sehr einfach zu erweitern und eigene Filter und Funktionen hinzuzufügen.

## Mitarbeit

Haben Sie ein Plugin, das Sie teilen möchten? Reichen Sie einen Pull-Request ein, um es der Liste hinzuzufügen!