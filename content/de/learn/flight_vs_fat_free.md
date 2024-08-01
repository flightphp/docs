# Flight vs Fat-Free

## Was ist Fat-Free?

[Fat-Free](https://fatfreeframework.com) (zärtlich bekannt als **F3**) ist ein leistungsstarkes und dennoch einfach zu bedienendes PHP-Mikro-Framework, das entwickelt wurde, um Ihnen zu helfen, dynamische und robuste Webanwendungen schnell zu erstellen!

Flight vergleicht sich in vielerlei Hinsicht mit Fat-Free und ist wahrscheinlich der engste Verwandte in Bezug auf Funktionen und Einfachheit. Fat-Free hat viele Funktionen, die Flight nicht hat, aber auch viele Funktionen, die Flight besitzt. Fat-Free beginnt, sein Alter zu zeigen und ist nicht mehr so beliebt wie früher.

Aktualisierungen werden seltener und die Community ist nicht mehr so aktiv wie zuvor. Der Code ist einfach genug, aber manchmal kann der Mangel an Syntaxdisziplin das Lesen und Verstehen erschweren. Es funktioniert für PHP 8.3, aber der Code selbst sieht immer noch aus, als würde er in PHP 5.3 existieren.

## Vorzüge im Vergleich zu Flight

- Fat-Free hat auf GitHub ein paar mehr Sterne als Flight.
- Fat-Free hat eine anständige Dokumentation, allerdings fehlt es manchmal an Klarheit.
- Fat-Free verfügt über einige knappe Ressourcen wie YouTube-Tutorials und Online-Artikel, die zum Erlernen des Frameworks genutzt werden können.
- Fat-Free verfügt über einige hilfreiche integrierte Plugins, die manchmal nützlich sind.
- Fat-Free hat ein eingebautes ORM namens Mapper, mit dem Sie mit Ihrer Datenbank interagieren können. Flight verwendet aktiv-record.
- Fat-Free bietet Sessions, Caching und Lokalisierung integriert. Flight erfordert die Verwendung von Bibliotheken von Drittanbietern, was jedoch in der Dokumentation behandelt wird.
- Fat-Free hat eine kleine Gruppe von von der Community erstellten Plugins, um das Framework zu erweitern. Flight enthält einige davon in der Dokumentation und den Beispielseiten.
- Fat-Free wie Flight hat keine Abhängigkeiten.
- Fat-Free und Flight zielen darauf ab, dem Entwickler die Kontrolle über ihre Anwendung und eine einfache Entwicklererfahrung zu geben.
- Fat-Free pflegt wie Flight die Abwärtskompatibilität (teilweise, da die Aktualisierungen weniger häufig werden).
- Fat-Free und Flight sind für Entwickler gedacht, die sich zum ersten Mal in die Welt der Frameworks begeben.
- Fat-Free hat einen integrierten Template-Engine, die robuster ist als Flight Template-Engine. Flight empfiehlt zur Realisierung Latte.
- Fat-Free hat eine einzigartige CLI-Typ "route"-Befehl, mit dem Sie CLI-Anwendungen innerhalb von Fat-Free erstellen und diese ähnlich wie eine `GET`-Anforderung behandeln können. Flight realisiert dies mit Runway.

## Nachteile im Vergleich zu Flight

- Fat-Free hat einige Implementierungstests und verfügt sogar über eine eigene Testklasse, die sehr grundlegend ist. Allerdings ist es nicht zu 100 % unit-getestet wie Flight.
- Sie müssen eine Suchmaschine wie Google verwenden, um die Dokumentationsseite tatsächlich durchsuchen zu können.
- Flight hat auf ihrer Dokumentationswebsite den Dark Mode.
- Fat-Free hat einige Module, die leider nicht gewartet werden.
- Flight hat einen einfachen PdoWrapper, der etwas einfacher ist als Fat-Free's eingebaute `DB\SQL`-Klasse.
- Flight hat ein Berechtigungs-Plugin, mit dem Sie Ihre Anwendung sichern können. Bei Slim müssen Sie eine Bibliothek von Drittanbietern verwenden.
- Flight hat ein ORM namens active-record, das sich mehr wie ein ORM anfühlt als Fat-Free's Mapper. Der zusätzliche Vorteil von `active-record` besteht darin, dass Sie Beziehungen zwischen Datensätzen definieren können, um automatische Joins zu erstellen, während Fat-Free's Mapper das Erstellen von SQL-Views erfordert.
- Erstaunlicherweise hat Fat-Free keinen Stammnamensraum. Flight ist vollständig unterteilten, um nicht mit Ihrem eigenen Code zu kollidieren. Die `Cache`-Klasse ist hier das größte Problem.
- Fat-Free verfügt nicht über Middleware. Stattdessen gibt es `beforeroute`- und `afterroute`-Hooks, die verwendet werden können, um Anfragen und Antworten in Controllern zu filtern.
- Fat-Free kann Routen nicht gruppieren.
- Fat-Free verfügt über einen Dependency Injection Container Handler, jedoch ist die Dokumentation äußerst knapp, wie man es verwendet.
- Das Debuggen kann etwas knifflig werden, da im Wesentlichen alles in dem sogenannten [`HIVE`](https://fatfreeframework.com/3.8/quick-reference) gespeichert ist.