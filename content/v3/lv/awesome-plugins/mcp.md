# FlightPHP MCP Server

FlightPHP MCP Server nodrošina jebkuram MCP savietojamam AI kodēšanas asistentam tūlītēju, strukturētu piekļuvi visai FlightPHP dokumentācijai — maršrutizācijai, starpprogrammatūrai, spraudņiem, ceļvežiem un citam. Tā vietā, lai jūsu AI izdomātu API detaļas vai minētos metožu parakstus, tas iegūst īsto dokumentāciju pēc pieprasījuma. Nav nepieciešamas API atslēgas, nav nepieciešama instalēšana viesotajai versijai.

Apmeklējiet [Github repository](https://github.com/flightphp/mcp), lai iegūtu pilnu avota kodu un detaļas.

## Ātrais starts

Serveris ir publiski viesots un gatavs lietošanai:

```
https://mcp.flightphp.com/mcp
```

Vienkārši pievienojiet šo URL jūsu AI kodēšanas paplašinājumam. Nav reģistrācijas, nav akreditācijas datu. Skatiet sadaļu [IDE Configuration](#ide--ai-extension-configuration) zemāk, lai iegūtu kopēšanas-līmēšanas konfigurācijas populārākajām rīkiem.

## Ko tas dara

Pēc savienojuma jūsu AI asistents var:

- **Pārlūkot visas pieejamās dokumentācijas** — uzskaitīt katru galveno tēmu, ceļvedi un spraudņu lapu
- **Iegūt jebkuru dokumentācijas lapu** — iegūt pilnu saturu maršrutizācijai, starpprogrammatūrai, pieprasījumiem, drošībai un citam
- **Meklēt spraudņu dokumentāciju** — iegūt pilnu dokumentāciju ActiveRecord, Session, Tracy, Runway un visiem citiem oficiālajiem spraudņiem
- **Sekot soli pa solim ceļvežiem** — piekļūt pilniem soļu pa soļiem ceļvežiem blogu, REST API un testēto lietojumprogrammu izveidei
- **Meklēt visā** — atrast saistītās lapas visā galvenajā dokumentācijā, ceļvežos un spraudņos vienlaikus

### Galvenie punkti
- **Nulles uzstādīšana** — viesotais serveris `https://mcp.flightphp.com/mcp` neprasa instalēšanu vai API atslēgas.
- **Vienmēr aktuāls** — serveris iegūst dokumentāciju tiešraidē no [docs.flightphp.com](https://docs.flightphp.com), tāpēc tas vienmēr ir atjaunināts.
- **Darbojas visur** — jebkurš rīks, kas atbalsta MCP Streamable HTTP transportu, var savienoties.
- **Pašviesojams** — palaidiet savu instances ar PHP >= 8.1 un Composer, ja vēlaties.

## IDE / AI Paplašinājuma konfigurācija

Serveris izmanto Streamable HTTP transportu. Izvēlieties savu paplašinājumu zemāk un ielīmējiet konfigurāciju.

### Claude Code (CLI)

Palaidiet šādu komandu, lai pievienotu to savam projektam:

```bash
claude mcp add --transport http flightphp-docs https://mcp.flightphp.com/mcp
```

Vai pievienojiet manuāli sava projekta `.mcp.json`:

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

Pievienojiet `.vscode/mcp.json` savā darba telpā:

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

Pievienojiet savam VS Code `settings.json`:

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

Pievienojiet `~/.continue/config.json`:

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

## Pieejamie rīki

MCP serveris piedāvā šādus rīkus jūsu AI asistentam:

| Rīks | Apraksts |
|------|-------------|
| `list_docs_pages` | Uzskaita visas pieejamās galvenās dokumentācijas tēmas ar slugiem un aprakstiem |
| `get_docs_page` | Iegūst galvenās dokumentācijas lapu pēc tēmas slug (piem., `routing`, `middleware`, `security`) |
| `list_guide_pages` | Uzskaita visus pieejamos soli pa solim ceļvežus |
| `get_guide_page` | Iegūst pilnu ceļvedi pēc slug (piem., `blog`, `unit-testing`) |
| `list_plugin_pages` | Uzskaita visas pieejamās spraudņu un paplašinājumu lapas |
| `get_plugin_docs` | Iegūst pilnu spraudņu dokumentāciju pēc slug (piem., `active-record`, `session`, `jwt`) |
| `search_docs` | Meklē visā dokumentācijā, ceļvežos un spraudņos pēc atslēgvārda vai tēmas |
| `fetch_url` | Iegūst jebkuru lapu tieši pēc pilna `docs.flightphp.com` URL |

## Pašviesojums

Vēlaties palaidiet savu instances? Jums būs nepieciešams PHP >= 8.1 un Composer.

```bash
git clone https://github.com/flightphp/mcp.git
cd mcp
composer install
php server.php
```

Serveris palaižas uz `http://0.0.0.0:8890/mcp` pēc noklusējuma. Atjauniniet savu IDE konfigurāciju, lai norādītu uz jūsu lokālo adresi:

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