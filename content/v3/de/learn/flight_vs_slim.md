# Flight vs Slim

## Was ist Slim?
[Slim](https://slimframework.com) ist ein PHP-Micro-Framework, das Ihnen hilft, schnell einfache, aber leistungsstarke Webanwendungen und APIs zu schreiben.

Viel Inspiration für einige der v3-Funktionen von Flight stammt tatsächlich von Slim. Das Gruppieren von Routen und das Ausführen von Middleware in einer spezifischen Reihenfolge sind zwei Funktionen, die von Slim inspiriert wurden. Slim v3 wurde mit dem Fokus auf Einfachheit veröffentlicht, aber es gibt [gemischte Bewertungen](https://github.com/slimphp/Slim/issues/2770) bezüglich v4.

## Vorteile im Vergleich zu Flight

- Slim hat eine größere Community von Entwicklern, die im Gegenzug nützliche Module erstellen, um zu vermeiden, das Rad neu zu erfinden.
- Slim folgt vielen Interfaces und Standards, die in der PHP-Community üblich sind, was die Interoperabilität erhöht.
- Slim hat anständige Dokumentation und Tutorials, die verwendet werden können, um das Framework zu lernen (nichts im Vergleich zu Laravel oder Symfony).
- Slim hat verschiedene Ressourcen wie YouTube-Tutorials und Online-Artikel, die verwendet werden können, um das Framework zu lernen.
- Slim lässt Sie beliebige Komponenten verwenden, um die Kern-Routing-Funktionen zu handhaben, da es PSR-7-konform ist.

## Nachteile im Vergleich zu Flight

- Überraschenderweise ist Slim nicht so schnell, wie man für ein Micro-Framework denken würde. Sehen Sie sich die 
  [TechEmpower-Benchmarks](https://www.techempower.com/benchmarks/#hw=ph&test=fortune&section=data-r22&l=zik073-cn3) 
  für weitere Informationen an.
- Flight ist auf Entwickler ausgerichtet, die eine leichte, schnelle und einfach zu bedienende Webanwendung erstellen möchten.
- Flight hat keine Abhängigkeiten, während [Slim einige Abhängigkeiten hat](https://github.com/slimphp/Slim/blob/4.x/composer.json), die Sie installieren müssen.
- Flight ist auf Einfachheit und Benutzerfreundlichkeit ausgerichtet.
- Eine der Kernfunktionen von Flight ist, dass es sein Bestes tut, um Abwärtskompatibilität zu wahren. Slim v3 zu v4 war eine Breaking Change.
- Flight ist für Entwickler gedacht, die zum ersten Mal in die Welt der Frameworks eintauchen.
- Flight kann auch Enterprise-Level-Anwendungen umsetzen, hat aber nicht so viele Beispiele und Tutorials wie Slim.
  Es erfordert auch mehr Disziplin vom Entwickler, um Dinge organisiert und gut strukturiert zu halten.
- Flight gibt dem Entwickler mehr Kontrolle über die Anwendung, während Slim hinter den Kulissen etwas Magie einbauen kann.
- Flight hat einen einfachen [PdoWrapper](/learn/pdo-wrapper), der verwendet werden kann, um mit Ihrer Datenbank zu interagieren. Slim erfordert die Verwendung einer Drittanbieter-Bibliothek.
- Flight hat ein [Permissions-Plugin](/awesome-plugins/permissions), das verwendet werden kann, um Ihre Anwendung zu sichern. Slim erfordert die Verwendung einer Drittanbieter-Bibliothek.
- Flight hat ein ORM namens [active-record](/awesome-plugins/active-record), das verwendet werden kann, um mit Ihrer Datenbank zu interagieren. Slim erfordert die Verwendung einer Drittanbieter-Bibliothek.
- Flight hat eine CLI-Anwendung namens [runway](/awesome-plugins/runway), die verwendet werden kann, um Ihre Anwendung von der Kommandozeile aus auszuführen. Slim hat das nicht.