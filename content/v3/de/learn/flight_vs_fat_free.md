# Flight vs Fat-Free

## Was ist Fat-Free?
[Fat-Free](https://fatfreeframework.com) (liebenswürdig bekannt als **F3**) ist ein leistungsstarkes, aber einfach zu bedienendes PHP-Micro-Framework, das Ihnen hilft, dynamische und robuste 
Web-Anwendungen – schnell – zu erstellen!

Flight vergleicht sich mit Fat-Free in vielerlei Hinsicht und ist wahrscheinlich der nächste Verwandte in Bezug auf Funktionen und Einfachheit. Fat-Free hat
eine Menge Funktionen, die Flight nicht hat, aber es hat auch viele Funktionen, die Flight hat. Fat-Free zeigt langsam sein Alter
und ist nicht mehr so beliebt wie früher.

Updates werden seltener, und die Community ist nicht mehr so aktiv wie früher. Der Code ist einfach genug, aber manchmal kann der Mangel an
Syntax-Disziplin es schwierig machen, ihn zu lesen und zu verstehen. Es funktioniert für PHP 8.3, aber der Code selbst sieht immer noch so aus, als würde er in
PHP 5.3 leben.

## Vorteile im Vergleich zu Flight

- Fat-Free hat ein paar mehr Sterne auf GitHub als Flight.
- Fat-Free hat eine ordentliche Dokumentation, aber es fehlt in einigen Bereichen an Klarheit.
- Fat-Free hat einige knappe Ressourcen wie YouTube-Tutorials und Online-Artikel, die verwendet werden können, um das Framework zu lernen.
- Fat-Free hat [einige hilfreiche Plugins](https://fatfreeframework.com/3.8/api-reference) integriert, die manchmal nützlich sind.
- Fat-Free hat ein integriertes ORM namens Mapper, das verwendet werden kann, um mit Ihrer Datenbank zu interagieren. Flight hat [active-record](/awesome-plugins/active-record).
- Fat-Free hat Sessions, Caching und Lokalisierung integriert. Flight erfordert die Verwendung von Drittanbieter-Bibliotheken, aber es wird in der [Dokumentation](/awesome-plugins) abgedeckt.
- Fat-Free hat eine kleine Gruppe von [von der Community erstellten Plugins](https://fatfreeframework.com/3.8/development#Community), die verwendet werden können, um das Framework zu erweitern. Flight hat einige in der [Dokumentation](/awesome-plugins) und [Beispiele](/examples) Seiten abgedeckt.
- Fat-Free hat wie Flight keine Abhängigkeiten.
- Fat-Free ist wie Flight darauf ausgerichtet, dem Entwickler Kontrolle über seine Anwendung und ein einfaches Entwicklererlebnis zu geben.
- Fat-Free erhält wie Flight die Abwärtskompatibilität (teilweise, weil Updates seltener werden [seltener](https://github.com/bcosca/fatfree/releases)).
- Fat-Free ist wie Flight für Entwickler gedacht, die zum ersten Mal in die Welt der Frameworks eintauchen.
- Fat-Free hat einen integrierten Template-Engine, der robuster ist als Flight's Template-Engine. Flight empfiehlt [Latte](/awesome-plugins/latte), um dies zu erreichen.
- Fat-Free hat einen einzigartigen CLI-Befehl vom Typ "route", mit dem Sie CLI-Apps innerhalb von Fat-Free selbst erstellen und ihn wie eine `GET`-Anfrage behandeln können. Flight erreicht dies mit [runway](/awesome-plugins/runway).

## Nachteile im Vergleich zu Flight

- Fat-Free hat einige Implementierungstests und sogar eine eigene [Test](https://fatfreeframework.com/3.8/test)-Klasse, die sehr basisch ist. Allerdings
  ist es nicht zu 100 % Unit-getestet wie Flight. 
- Sie müssen eine Suchmaschine wie Google verwenden, um die Dokumentationsseite tatsächlich zu durchsuchen.
- Flight hat Dark Mode auf ihrer Dokumentationsseite. (Mic Drop)
- Fat-Free hat einige Module, die erbärmlich unmaintained sind.
- Flight hat einen einfachen [PdoWrapper](/learn/pdo-wrapper), der etwas einfacher ist als Fat-Free's integrierte `DB\SQL`-Klasse.
- Flight hat ein [Permissions-Plugin](/awesome-plugins/permissions), das verwendet werden kann, um Ihre Anwendung zu sichern. Fat-Free erfordert die Verwendung 
  einer Drittanbieter-Bibliothek.
- Flight hat ein ORM namens [active-record](/awesome-plugins/active-record), das sich mehr wie ein ORM anfühlt als Fat-Free's Mapper.
  Der zusätzliche Vorteil von `active-record` ist, dass Sie Beziehungen zwischen Datensätzen definieren können für automatische Joins, wo Fat-Free's Mapper
  erfordert, dass Sie [SQL-Views](https://fatfreeframework.com/3.8/databases#ProsandCons) erstellen.
- Erstaunlicherweise hat Fat-Free keinen Root-Namespace. Flight ist namespaced bis zum Ende, um nicht mit Ihrem eigenen Code zu kollidieren.
  Die `Cache`-Klasse ist hier der größte Übeltäter.
- Fat-Free hat kein Middleware. Stattdessen gibt es `beforeroute`- und `afterroute`-Hooks, die verwendet werden können, um Anfragen und Antworten in Controllern zu filtern.
- Fat-Free kann Routen nicht gruppieren.
- Fat-Free hat einen Dependency-Injection-Container-Handler, aber die Dokumentation ist unglaublich spärlich darüber, wie man ihn verwendet.
- Debugging kann etwas knifflig werden, da im Wesentlichen alles in dem gespeichert wird, was als [`HIVE`](https://fatfreeframework.com/3.8/quick-reference) bezeichnet wird.