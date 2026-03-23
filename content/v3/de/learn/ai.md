# KI & Entwicklererfahrung mit Flight

## Überblick

Flight erleichtert es, Ihre PHP-Projekte mit KI-gestützten Tools und modernen Entwickler-Workflows zu superchargen. Mit integrierten Befehlen zum Verbinden mit LLM-Anbietern (Large Language Model) und zum Generieren projektspezifischer KI-Coding-Anweisungen hilft Flight Ihnen und Ihrem Team, das Maximum aus KI-Assistenten wie GitHub Copilot, Cursor, Windsurf und Antigravity (Gemini) herauszuholen.

## Verständnis

KI-Coding-Assistenten sind am hilfreichsten, wenn sie den Kontext, die Konventionen und die Ziele Ihres Projekts verstehen. Die KI-Helfer von Flight ermöglichen es Ihnen:
- Ihr Projekt mit beliebten LLM-Anbietern zu verbinden (OpenAI, Grok, Claude usw.)
- Projektspezifische Anweisungen für KI-Tools zu generieren und zu aktualisieren, damit alle konsistente, relevante Hilfe erhalten
- Ihr Team ausgerichtet und produktiv zu halten, mit weniger Zeit für das Erklären des Kontexts

Diese Funktionen sind in den Flight-Core-CLI und das offizielle [flightphp/skeleton](https://github.com/flightphp/skeleton) Starter-Projekt integriert.

## Grundlegende Nutzung

### Einrichten von LLM-Zugangsdaten

Der Befehl `ai:init` führt Sie durch den Prozess, Ihr Projekt mit einem LLM-Anbieter zu verbinden.

```bash
php runway ai:init
```

Sie werden aufgefordert:
- Ihren Anbieter auszuwählen (OpenAI, Grok, Claude usw.)
- Ihren API-Schlüssel einzugeben
- Die Basis-URL und den Modellnamen festzulegen

Dies erstellt die notwendigen Zugangsdaten, damit Sie zukünftige LLM-Anfragen stellen können.

**Beispiel:**
```
Welcome to AI Init!
Which LLM API do you want to use? [1] openai, [2] grok, [3] claude: 1
Enter the base URL for the LLM API [https://api.openai.com]:
Enter your API key for openai: sk-...
Enter the model name you want to use (e.g. gpt-4, claude-3-opus, etc) [gpt-4o]:
Credentials saved to .runway-creds.json
```

### Generieren projektspezifischer KI-Anweisungen

Der Befehl `ai:generate-instructions` hilft Ihnen, Anweisungen für KI-Coding-Assistenten zu erstellen oder zu aktualisieren, die auf Ihr Projekt zugeschnitten sind.

```bash
php runway ai:generate-instructions
```

Sie beantworten ein paar Fragen zu Ihrem Projekt (Beschreibung, Datenbank, Templating, Sicherheit, Teamgröße usw.). Flight verwendet Ihren LLM-Anbieter, um Anweisungen zu generieren, und schreibt sie dann in:
- `.github/copilot-instructions.md` (für GitHub Copilot)
- `.cursor/rules/project-overview.mdc` (für Cursor)
- `.windsurfrules` (für Windsurf)
- `.gemini/GEMINI.md` (für Antigravity)

**Beispiel:**
```
Please describe what your project is for? My awesome API
What database are you planning on using? MySQL
What HTML templating engine will you plan on using (if any)? latte
Is security an important element of this project? (y/n) y
...
AI instructions updated successfully.
```

Nun geben Ihre KI-Tools intelligentere, relevantere Vorschläge basierend auf den tatsächlichen Bedürfnissen Ihres Projekts.

## Erweiterte Nutzung

- Sie können den Speicherort Ihrer Zugangsdaten- oder Anweisungsdateien mit Befehlsoptionen anpassen (siehe `--help` für jeden Befehl).
- Die KI-Helfer sind so konzipiert, dass sie mit jedem LLM-Anbieter funktionieren, der OpenAI-kompatible APIs unterstützt.
- Wenn Sie Ihre Anweisungen aktualisieren möchten, während sich Ihr Projekt weiterentwickelt, führen Sie einfach `ai:generate-instructions` erneut aus und beantworten die Prompts erneut.

## Siehe auch

- [Flight Skeleton](https://github.com/flightphp/skeleton) – Das offizielle Starter-Projekt mit KI-Integration
- [Runway CLI](/awesome-plugins/runway) – Mehr über das CLI-Tool, das diese Befehle antreibt

## Fehlerbehebung

- Wenn Sie "Missing .runway-creds.json" sehen, führen Sie zuerst `php runway ai:init` aus.
- Stellen Sie sicher, dass Ihr API-Schlüssel gültig ist und Zugriff auf das ausgewählte Modell hat.
- Wenn Anweisungen nicht aktualisiert werden, überprüfen Sie die Dateiberechtigungen in Ihrem Projektverzeichnis.

## Änderungsprotokoll

- v3.16.0 – Hinzugefügt: `ai:init` und `ai:generate-instructions` CLI-Befehle für KI-Integration.