# Flug vs. Schlank

## Was ist Schlank?
[Slim](https://slimframework.com) ist ein PHP-Mikrorahmenwerk, das Ihnen hilft, schnell einfache, aber leistungsstarke Webanwendungen und APIs zu erstellen.

Eine Menge der Inspiration für einige der v3-Funktionen von Flug kam tatsächlich von Schlank. Das Gruppieren von Routen und das Ausführen von Middleware in einer bestimmten Reihenfolge sind zwei Funktionen, die von Schlank inspiriert wurden. Schlank v3 wurde mit Blick auf Einfachheit entwickelt, aber es gab [gemischte Bewertungen](https://github.com/slimphp/Slim/issues/2770) bezüglich v4.

## Vorteile im Vergleich zu Flug

- Schlank hat eine größere Entwicklergemeinschaft, die wiederum nützliche Module erstellt, um Ihnen zu helfen, das Rad nicht neu zu erfinden.
- Schlank folgt vielen Schnittstellen und Standards, die in der PHP-Community üblich sind, was die Interoperabilität erhöht.
- Schlank hat eine anständige Dokumentation und Tutorials, die verwendet werden können, um das Framework zu erlernen (im Vergleich zu Laravel oder Symfony jedoch nichts).
- Schlank verfügt über verschiedene Ressourcen wie YouTube-Tutorials und Online-Artikel, die verwendet werden können, um das Framework zu erlernen.
- Schlank lässt Sie die Komponenten verwenden, die Sie möchten, um die Kernroutingfunktionen zu behandeln, da es PSR-7-konform ist.

## Nachteile im Vergleich zu Flug

- Überraschenderweise ist Schlank nicht so schnell, wie man denken würde, für ein Mikro-Rahmenwerk. Sehen Sie sich die 
  [TechEmpower-Benchmarks](https://www.techempower.com/benchmarks/#hw=ph&test=fortune&section=data-r22&l=zik073-cn3) 
  für weitere Informationen an.
- Flug zielt auf einen Entwickler ab, der eine leichtgewichtige, schnelle und benutzerfreundliche Webanwendung erstellen möchte.
- Flug hat keine Abhängigkeiten, während [Schlank einige Abhängigkeiten hat](https://github.com/slimphp/Slim/blob/4.x/composer.json), die Sie installieren müssen.
- Flug zielt auf Einfachheit und Benutzerfreundlichkeit ab.
- Eine der Kernfunktionen von Flug ist, dass es sein Bestes tut, um Abwärtskompatibilität zu gewährleisten. Der Wechsel von Slim v3 zu v4 war ein einschneidender Wechsel.
- Flug ist für Entwickler gedacht, die sich zum ersten Mal in die Welt der Frameworks wagen.
- Flug kann auch Unternehmensanwendungen bewältigen, aber es hat nicht so viele Beispiele und Tutorials wie Schlank. Es erfordert auch mehr Disziplin seitens des Entwicklers, um Dinge organisiert und gut strukturiert zu halten.
- Flug gibt dem Entwickler mehr Kontrolle über die Anwendung, während sich Schlank hinter den Kulissen etwas Magie einschleichen kann.
- Flug hat ein einfaches [PdoWrapper](/awesome-plugins/pdo-wrapper), das verwendet werden kann, um mit Ihrer Datenbank zu interagieren. Schlank erfordert die Verwendung einer Bibliothek von Drittanbietern.
- Flug hat ein [Berechtigungs-Plugin](/awesome-plugins/permissions), das verwendet werden kann, um Ihre Anwendung abzusichern. Schlank erfordert die Verwendung einer Bibliothek von Drittanbietern.
- Flug hat ein ORM namens [active-record](/awesome-plugins/active-record), das verwendet werden kann, um mit Ihrer Datenbank zu interagieren. Schlank erfordert die Verwendung einer Bibliothek von Drittanbietern.
- Flug hat eine CLI-Anwendung namens [runway](/awesome-plugins/runway), die verwendet werden kann, um Ihre Anwendung von der Befehlszeile aus auszuführen. Schlank nicht.