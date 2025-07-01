# KI & Entwicklererfahrung mit Flight

Flight ist darauf ausgerichtet, Ihnen zu helfen, schneller, schlauer und mit weniger Reibung zu bauen – insbesondere bei der Arbeit mit KI-gestützten Tools und modernen Entwicklerworkflows. Diese Seite behandelt, wie Flight es einfach macht, Ihre Projekte mit KI aufzuladen, und wie Sie mit den neuen KI-Hilfsmitteln beginnen, die direkt in das Framework und das Skeleton-Projekt integriert sind.

---

## KI-Bereit von Haus aus: Das Skeleton-Projekt

Das offizielle [flightphp/skeleton](https://github.com/flightphp/skeleton) Starterpaket enthält jetzt Anweisungen und Konfigurationen für beliebte KI-Coding-Assistenten:

- **GitHub Copilot**
- **Cursor**
- **Windsurf**

Diese Tools sind mit projektbezogenen Anweisungen vorkonfiguriert, sodass Sie und Ihr Team die relevanteste, kontextbezogene Hilfe beim Codieren erhalten. Das bedeutet:

- KI-Assistenten verstehen die Ziele, den Stil und die Anforderungen Ihres Projekts
- Konsistente Anleitung für alle Beiträger
- Weniger Zeit für die Erklärung des Kontexts, mehr Zeit für das Bauen

> **Warum ist das wichtig?**
>
> Wenn Ihre KI-Tools die Absichten und Konventionen Ihres Projekts kennen, können sie Ihnen helfen, Features zu strukturieren, Code umzuschreiben und häufige Fehler zu vermeiden – sodass Sie (und Ihr Team) ab dem ersten Tag produktiver sind.

---

## Neue KI-Befehle im Flight Core

_v3.16.0+_

Flight Core enthält jetzt zwei leistungsstarke CLI-Befehle, um Ihnen bei der Einrichtung und Steuerung Ihres Projekts mit KI zu helfen:

### 1. `ai:init` — Verbindung zu Ihrem bevorzugten LLM-Anbieter herstellen

Dieser Befehl führt Sie durch die Einrichtung von Anmeldedaten für einen LLM-Anbieter (Large Language Model), wie z. B. OpenAI, Grok oder Anthropic (Claude).

**Beispiel:**
```bash
php runway ai:init
```
Sie werden aufgefordert, Ihren Anbieter auszuwählen, Ihren API-Schlüssel einzugeben und ein Modell zu wählen. Dadurch können Sie Ihr Projekt einfach mit den neuesten KI-Diensten verbinden – ohne manuelle Konfiguration.

### 2. `ai:generate-instructions` — Projektbezogene KI-Coding-Anweisungen

Dieser Befehl hilft Ihnen, projektbezogene Anweisungen für KI-Coding-Assistenten zu erstellen oder zu aktualisieren. Er stellt Ihnen ein paar einfache Fragen zu Ihrem Projekt (z. B. wofür es dient, welche Datenbank Sie verwenden, Teamgröße usw.) und verwendet dann Ihren LLM-Anbieter, um maßgeschneiderte Anweisungen zu generieren.

Falls Sie bereits Anweisungen haben, werden diese aktualisiert, um Ihre Antworten widerzuspiegeln. Diese Anweisungen werden automatisch geschrieben in:
- `.github/copilot-instructions.md` (für Github Copilot)
- `.cursor/rules/project-overview.mdc` (für Cursor)
- `.windsurfrules` (für Windsurf)

**Beispiel:**
```bash
php runway ai:generate-instructions
```

> **Warum ist das hilfreich?**
>
> Mit aktuellen, projektbezogenen Anweisungen können Ihre KI-Tools:
> - Bessere Codevorschläge geben
> - Die einzigartigen Bedürfnisse Ihres Projekts verstehen
> - Neue Beiträger schneller einweisen
> - Reibung und Verwirrung reduzieren, während sich Ihr Projekt weiterentwickelt

---

## Nicht nur für die Erstellung von KI-Apps

Obwohl Sie Flight absolut für die Erstellung von KI-gestützten Features verwenden können (wie Chatbots, smarte APIs oder Integrationen), liegt die wahre Stärke darin, wie Flight Ihnen hilft, besser mit KI-Tools als Entwickler zu arbeiten. Es geht um:

- **Produktivität steigern** mit KI-unterstütztem Codieren
- **Ihr Team ausrichten** mit geteilten, sich entwickelnden Anweisungen
- **Onboarding erleichtern** für neue Beiträger
- **Sich auf das Bauen konzentrieren**, nicht auf den Kampf mit Ihren Tools

---

## Mehr erfahren & Loslegen

- Sehen Sie sich das [Flight Skeleton](https://github.com/flightphp/skeleton) für ein fertiges, KI-freundliches Starterpaket an
- Schauen Sie sich den Rest der [Flight-Dokumentation](/learn) für Tipps zum Erstellen schneller, moderner PHP-Apps an