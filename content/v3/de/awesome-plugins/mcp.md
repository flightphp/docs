# FlightPHP MCP Server

Der FlightPHP MCP Server gibt jedem MCP-kompatiblen KI-Coding-Assistenten sofortigen, strukturierten Zugriff auf die gesamte FlightPHP-Dokumentation — Routing, Middleware, Plugins, Anleitungen und mehr. Statt dass Ihre KI API-Details halluziniert oder Methodensignaturen errät, holt sie die echten Dokumente bei Bedarf ab. Keine API-Schlüssel, keine Installation für die gehostete Version erforderlich.

Besuchen Sie das [Github-Repository](https://github.com/flightphp/mcp) für den vollständigen Quellcode und Details.

## Schnellstart

Der Server ist öffentlich gehostet und einsatzbereit:

```
https://mcp.flightphp.com/mcp
```

Fügen Sie einfach diese URL zu Ihrer KI-Coding-Erweiterung hinzu. Keine Anmeldung, keine Zugangsdaten. Siehe den Abschnitt [IDE-Konfiguration](#ide--ai-extension-configuration) unten für Kopier-Einfügen-Konfigurationen für die beliebtesten Tools.

## Was es tut

Sobald verbunden, kann Ihr KI-Assistent:

- **Alle verfügbaren Dokumente durchsuchen** — jede Kern-Thema, Anleitung und Plugin-Seite auflisten
- **Jede Dokumentationsseite abrufen** — vollständigen Inhalt für Routing, Middleware, Anfragen, Sicherheit und mehr abrufen
- **Plugin-Dokumente nachschlagen** — vollständige Dokumentation für ActiveRecord, Session, Tracy, Runway und alle anderen offiziellen Plugins abrufen
- **Schritt-für-Schritt-Anleitungen folgen** — vollständige Durchgänge für den Aufbau von Blogs, REST-APIs und getesteten Anwendungen abrufen
- **Über alles suchen** — relevante Seiten in Kern-Dokumenten, Anleitungen und Plugins gleichzeitig finden

### Wichtige Punkte
- **Null Einrichtung** — der gehostete Server unter `https://mcp.flightphp.com/mcp` erfordert keine Installation oder API-Schlüssel.
- **Immer aktuell** — der Server holt Dokumente live von [docs.flightphp.com](https://docs.flightphp.com), sodass er immer auf dem neuesten Stand ist.
- **Funktioniert überall** — jedes Tool, das den MCP Streamable HTTP-Transport unterstützt, kann sich verbinden.
- **Selbst hostbar** — Führen Sie Ihre eigene Instanz mit PHP >= 8.1 und Composer aus, wenn Sie möchten.

## IDE / AI-Erweiterungskonfiguration

Der Server verwendet den Streamable HTTP-Transport. Wählen Sie Ihre Erweiterung unten aus und fügen Sie die Konfiguration ein.

### Claude Code (CLI)

Führen Sie den folgenden Befehl aus, um es zu Ihrem Projekt hinzuzufügen:

```bash
claude mcp add --transport http flightphp-docs https://mcp.flightphp.com/mcp
```

Oder fügen Sie es manuell zu Ihrer Projektdatei `.mcp.json` hinzu:

```json
{
  "mcpServers": {
    "flightphp-docs": {
      "type": "http",
      "url": "https://mcp.flightphp.com/mcp"
    }
  }
}
```

### GitHub Copilot (VS Code)

Fügen Sie es zu `.vscode/mcp.json` in Ihrem Arbeitsbereich hinzu:

```json
{
  "servers": {
    "flightphp-docs": {
      "type": "http",
      "url": "https://mcp.flightphp.com/mcp"
    }
  }
}
```

### Kilo Code (VS Code)

Fügen Sie es zu Ihrer VS Code `settings.json` hinzu:

```json
{
  "kilocode.mcpServers": {
    "flightphp-docs": {
      "url": "https://mcp.flightphp.com/mcp",
      "transport": "streamable-http"
    }
  }
}
```

### Continue.dev (VS Code / JetBrains)

Fügen Sie es zu `~/.continue/config.json` hinzu:

```json
{
  "mcpServers": [
    {
      "name": "flightphp-docs",
      "transport": {
        "type": "http",
        "url": "https://mcp.flightphp.com/mcp"
      }
    }
  ]
}
```

## Verfügbare Tools

Der MCP-Server stellt Ihrem KI-Assistenten die folgenden Tools zur Verfügung:

| Tool | Beschreibung |
|------|-------------|
| `list_docs_pages` | Listet alle verfügbaren Kern-Dokumentationsthemen mit Slugs und Beschreibungen auf |
| `get_docs_page` | Ruft eine Kern-Dokumentationsseite anhand des Themen-Slugs ab (z. B. `routing`, `middleware`, `security`) |
| `list_guide_pages` | Listet alle verfügbaren Schritt-für-Schritt-Anleitungen auf |
| `get_guide_page` | Ruft eine vollständige Anleitung anhand des Slugs ab (z. B. `blog`, `unit-testing`) |
| `list_plugin_pages` | Listet alle verfügbaren Plugin- und Erweiterungsseiten auf |
| `get_plugin_docs` | Ruft die vollständige Plugin-Dokumentation anhand des Slugs ab (z. B. `active-record`, `session`, `jwt`) |
| `search_docs` | Sucht über alle Dokumente, Anleitungen und Plugins nach einem Schlüsselwort oder Thema |
| `fetch_url` | Ruft jede Seite direkt anhand ihrer vollständigen `docs.flightphp.com`-URL ab |

## Selbst-Hosting

Bevorzugen Sie es, Ihre eigene Instanz auszuführen? Sie benötigen PHP >= 8.1 und Composer.

```bash
git clone https://github.com/flightphp/mcp.git
cd mcp
composer install
php server.php
```

Der Server startet standardmäßig unter `http://0.0.0.0:8890/mcp`. Aktualisieren Sie Ihre IDE-Konfiguration, um auf Ihre lokale Adresse zu verweisen:

```json
{
  "mcpServers": {
    "flightphp-docs": {
      "type": "http",
      "url": "http://localhost:8890/mcp"
    }
  }
}
```