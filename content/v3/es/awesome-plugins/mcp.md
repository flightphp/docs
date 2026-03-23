# Servidor MCP de FlightPHP

El Servidor MCP de FlightPHP proporciona a cualquier asistente de codificación de IA compatible con MCP acceso instantáneo y estructurado a toda la documentación de FlightPHP: enrutamiento, middleware, plugins, guías y más. En lugar de que tu IA alucine detalles de la API o adivine firmas de métodos, obtiene los documentos reales a demanda. Sin claves de API, sin instalación requerida para la versión alojada.

Visita el [repositorio de GitHub](https://github.com/flightphp/mcp) para el código fuente completo y detalles.

## Inicio Rápido

El servidor está alojado públicamente y listo para usar:

```
https://mcp.flightphp.com/mcp
```

Solo agrega esa URL a tu extensión de codificación de IA. Sin registro, sin credenciales. Consulta la sección [Configuración de IDE](#configuración-de-ide--extensión-de-ia) a continuación para configuraciones de copiar y pegar para las herramientas más populares.

## Qué Hace

Una vez conectado, tu asistente de IA puede:

- **Explorar todos los documentos disponibles** — listar cada tema principal, guía y página de plugin
- **Obtener cualquier página de documentación** — recuperar el contenido completo para enrutamiento, middleware, solicitudes, seguridad y más
- **Buscar documentación de plugins** — obtener la documentación completa para ActiveRecord, Session, Tracy, Runway y todos los otros plugins oficiales
- **Seguir guías paso a paso** — acceder a walkthroughs completos para construir blogs, APIs REST y aplicaciones probadas
- **Buscar en todo** — encontrar páginas relevantes en documentos principales, guías y plugins al mismo tiempo

### Puntos Clave
- **Cero configuración** — el servidor alojado en `https://mcp.flightphp.com/mcp` no requiere instalación ni claves de API.
- **Siempre actualizado** — el servidor obtiene documentos en vivo de [docs.flightphp.com](https://docs.flightphp.com), por lo que siempre está actualizado.
- **Funciona en todas partes** — cualquier herramienta que soporte el transporte HTTP Streamable de MCP puede conectarse.
- **Autoalojable** — ejecuta tu propia instancia con PHP >= 8.1 y Composer si lo prefieres.

## Configuración de IDE / Extensión de IA

El servidor usa transporte HTTP Streamable. Elige tu extensión a continuación y pega la configuración.

### Claude Code (CLI)

Ejecuta el siguiente comando para agregarlo a tu proyecto:

```bash
claude mcp add --transport http flightphp-docs https://mcp.flightphp.com/mcp
```

O agrégalo manualmente al archivo `.mcp.json` de tu proyecto:

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

Agrega a `.vscode/mcp.json` en tu espacio de trabajo:

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

Agrega a tu `settings.json` de VS Code:

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

Agrega a `~/.continue/config.json`:

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

## Herramientas Disponibles

El servidor MCP expone las siguientes herramientas a tu asistente de IA:

| Herramienta | Descripción |
|-------------|-------------|
| `list_docs_pages` | Lista todos los temas de documentación principal disponibles con slugs y descripciones |
| `get_docs_page` | Obtiene una página de documentos principal por slug de tema (p. ej. `routing`, `middleware`, `security`) |
| `list_guide_pages` | Lista todas las guías paso a paso disponibles |
| `get_guide_page` | Obtiene una guía completa por slug (p. ej. `blog`, `unit-testing`) |
| `list_plugin_pages` | Lista todas las páginas de plugins y extensiones disponibles |
| `get_plugin_docs` | Obtiene la documentación completa del plugin por slug (p. ej. `active-record`, `session`, `jwt`) |
| `search_docs` | Busca en todos los documentos, guías y plugins por una palabra clave o tema |
| `fetch_url` | Obtiene cualquier página directamente por su URL completa de `docs.flightphp.com` |

## Autoalojamiento

¿Prefieres ejecutar tu propia instancia? Necesitarás PHP >= 8.1 y Composer.

```bash
git clone https://github.com/flightphp/mcp.git
cd mcp
composer install
php server.php
```

El servidor se inicia en `http://0.0.0.0:8890/mcp` por defecto. Actualiza la configuración de tu IDE para apuntar a tu dirección local:

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