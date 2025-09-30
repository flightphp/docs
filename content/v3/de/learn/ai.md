# KI & Entwicklererfahrung mit Flight

## Überblick

Flight erleichtert es, Ihre PHP-Projekte mit KI-gestützten Tools und modernen Entwickler-Workflows zu superchargen. Mit integrierten Befehlen zum Verbinden mit LLM-Anbietern (Large Language Model) und zur Generierung projektspezifischer KI-Coding-Anweisungen hilft Flight Ihnen und Ihrem Team, das Maximum aus KI-Assistenten wie GitHub Copilot, Cursor und Windsurf herauszuholen.

## Verständnis

KI-Coding-Assistenten sind am hilfreichsten, wenn sie den Kontext, die Konventionen und die Ziele Ihres Projekts verstehen. Die KI-Hilfsprogramme von Flight ermöglichen es Ihnen:
- Ihr Projekt mit beliebten LLM-Anbietern zu verbinden (OpenAI, Grok, Claude usw.)
- Projektspezifische Anweisungen für KI-Tools zu generieren und zu aktualisieren, damit jeder konsistente, relevante Hilfe erhält
- Ihr Team ausgerichtet und produktiv zu halten, mit weniger Zeit für die Erklärung des Kontexts

Diese Funktionen sind in die Flight-Core-CLI und das offizielle [flightphp/skeleton](https://github.com/flightphp/skeleton)-Starter-Projekt integriert.

## Grundlegende Nutzung

### 1. Einrichten von LLM-Anmeldeinformationen

Der Befehl `ai:init` führt Sie durch die Verbindung Ihres Projekts mit einem LLM-Anbieter.

```bash
php runway ai:init
```

Sie werden aufgefordert:
- Ihren Anbieter auszuwählen (OpenAI, Grok, Claude usw.)
- Ihren API-Schlüssel einzugeben
- Die Basis-URL und den Modellnamen festzulegen

Dies erstellt eine `.runway-creds.json`-Datei im Stammverzeichnis Ihres Projekts (und stellt sicher, dass sie in Ihrer `.gitignore` ist).

**Beispiel:**
```
Willkommen bei AI Init!
Welchen LLM-API möchten Sie verwenden? [1] openai, [2] grok, [3] claude: 1
Geben Sie die Basis-URL für die LLM-API ein [https://api.openai.com]:
Geben Sie Ihren API-Schlüssel für openai ein: sk-...
Geben Sie den Modellnamen ein, den Sie verwenden möchten (z. B. gpt-4, claude-3-opus usw.) [gpt-4o]:
Anmeldeinformationen in .runway-creds.json gespeichert
```

### 2. Generieren projektspezifischer KI-Anweisungen

Der Befehl `ai:generate-instructions` hilft Ihnen, Anweisungen für KI-Coding-Assistenten zu erstellen oder zu aktualisieren, die auf Ihr Projekt zugeschnitten sind.

```bash
php runway ai:generate-instructions
```

Sie beantworten ein paar Fragen zu Ihrem Projekt (Beschreibung, Datenbank, Vorlagen, Sicherheit, Teamgröße usw.). Flight verwendet Ihren LLM-Anbieter, um Anweisungen zu generieren, und schreibt sie dann in:
- `.github/copilot-instructions.md` (für GitHub Copilot)
- `.cursor/rules/project-overview.mdc` (für Cursor)
- `.windsurfrules` (für Windsurf)

**Beispiel:**
```
Beschreiben Sie bitte, wofür Ihr Projekt gedacht ist? My awesome API
Welche Datenbank planen Sie zu verwenden? MySQL
Welchen HTML-Vorlagen-Engine planen Sie zu verwenden (falls zutreffend)? latte
Ist Sicherheit ein wichtiger Aspekt dieses Projekts? (y/n) y
...
KI-Anweisungen erfolgreich aktualisiert.
```

Nun geben Ihre KI-Tools intelligentere, relevantere Vorschläge basierend auf den tatsächlichen Bedürfnissen Ihres Projekts.

## Erweiterte Nutzung

- Sie können den Speicherort Ihrer Anmeldeinformations- oder Anweisungsdateien mit Befehlsoptionen anpassen (siehe `--help` für jeden Befehl).
- Die KI-Hilfsprogramme sind so konzipiert, dass sie mit jedem LLM-Anbieter funktionieren, der OpenAI-kompatible APIs unterstützt.
- Wenn Sie Ihre Anweisungen aktualisieren möchten, während sich Ihr Projekt weiterentwickelt, führen Sie einfach `ai:generate-instructions` erneut aus und beantworten Sie die Aufforderungen erneut.

## Siehe auch

- [Flight Skeleton](https://github.com/flightphp/skeleton) – Das offizielle Starter-Projekt mit KI-Integration
- [Runway CLI](/awesome-plugins/runway) – Mehr über das CLI-Tool, das diese Befehle antreibt

## Fehlerbehebung

- Wenn Sie "Missing .runway-creds.json" sehen, führen Sie zuerst `php runway ai:init` aus.
- Stellen Sie sicher, dass Ihr API-Schlüssel gültig ist und Zugriff auf das ausgewählte Modell hat.
- Wenn Anweisungen nicht aktualisiert werden, überprüfen Sie die Dateiberechtigungen in Ihrem Projektverzeichnis.

## Änderungsprotokoll

- v3.16.0 – Hinzugefügt: `ai:init` und `ai:generate-instructions` CLI-Befehle für KI-Integration.