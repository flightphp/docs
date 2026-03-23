# Serveur MCP FlightPHP

Le serveur MCP FlightPHP donne à tout assistant de codage IA compatible MCP un accès instantané et structuré à l'ensemble de la documentation FlightPHP — routage, middleware, plugins, guides, et plus encore. Au lieu que votre IA hallucine des détails d'API ou devine des signatures de méthodes, il récupère les vraies docs à la demande. Pas de clés API, pas d'installation requise pour la version hébergée.

Visitez le [dépôt GitHub](https://github.com/flightphp/mcp) pour le code source complet et les détails.

## Démarrage Rapide

Le serveur est hébergé publiquement et prêt à l'emploi :

```
https://mcp.flightphp.com/mcp
```

Ajoutez simplement cette URL à votre extension de codage IA. Pas d'inscription, pas de credentials. Consultez la section [Configuration IDE](#ide--ai-extension-configuration) ci-dessous pour les configs prêtes à copier-coller pour les outils les plus populaires.

## Ce Qu'il Fait

Une fois connecté, votre assistant IA peut :

- **Parcourir toutes les docs disponibles** — lister tous les sujets principaux, guides et pages de plugins
- **Récupérer n'importe quelle page de documentation** — obtenir le contenu complet pour le routage, middleware, requêtes, sécurité, et plus encore
- **Rechercher les docs de plugins** — obtenir la documentation complète pour ActiveRecord, Session, Tracy, Runway, et tous les autres plugins officiels
- **Suivre des guides étape par étape** — accéder à des tutoriels complets pour construire des blogs, des API REST, et des applications testées
- **Rechercher dans tout** — trouver des pages pertinentes à travers les docs principales, guides et plugins en une seule fois

### Points Clés
- **Zéro configuration** — le serveur hébergé à `https://mcp.flightphp.com/mcp` ne nécessite pas d'installation ni de clés API.
- **Toujours à jour** — le serveur récupère les docs en direct de [docs.flightphp.com](https://docs.flightphp.com), donc il est toujours à jour.
- **Fonctionne partout** — tout outil qui supporte le transport HTTP Streamable MCP peut se connecter.
- **Auto-hébergeable** — exécutez votre propre instance avec PHP >= 8.1 et Composer si vous préférez.

## Configuration IDE / Extension IA

Le serveur utilise le transport HTTP Streamable. Choisissez votre extension ci-dessous et collez la config.

### Claude Code (CLI)

Exécutez la commande suivante pour l'ajouter à votre projet :

```bash
claude mcp add --transport http flightphp-docs https://mcp.flightphp.com/mcp
```

Ou ajoutez-le manuellement au fichier `.mcp.json` de votre projet :

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

Ajoutez à `.vscode/mcp.json` dans votre espace de travail :

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

Ajoutez à votre `settings.json` de VS Code :

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

Ajoutez à `~/.continue/config.json` :

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

## Outils Disponibles

Le serveur MCP expose les outils suivants à votre assistant IA :

| Outil | Description |
|-------|-------------|
| `list_docs_pages` | Liste tous les sujets de documentation principaux disponibles avec leurs slugs et descriptions |
| `get_docs_page` | Récupère une page de docs principale par slug de sujet (ex. `routing`, `middleware`, `security`) |
| `list_guide_pages` | Liste tous les guides étape par étape disponibles |
| `get_guide_page` | Récupère un guide complet par slug (ex. `blog`, `unit-testing`) |
| `list_plugin_pages` | Liste toutes les pages de plugins et extensions disponibles |
| `get_plugin_docs` | Récupère la documentation complète d'un plugin par slug (ex. `active-record`, `session`, `jwt`) |
| `search_docs` | Recherches à travers toutes les docs, guides et plugins pour un mot-clé ou un sujet |
| `fetch_url` | Récupère n'importe quelle page directement par son URL complète `docs.flightphp.com` |

## Auto-Hébergement

Préférez exécuter votre propre instance ? Vous aurez besoin de PHP >= 8.1 et Composer.

```bash
git clone https://github.com/flightphp/mcp.git
cd mcp
composer install
php server.php
```

Le serveur démarre par défaut sur `http://0.0.0.0:8890/mcp`. Mettez à jour la config de votre IDE pour pointer vers votre adresse locale :

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