# Servidor MCP do FlightPHP

O Servidor MCP do FlightPHP fornece a qualquer assistente de codificação de IA compatível com MCP acesso instantâneo e estruturado a toda a documentação do FlightPHP — roteamento, middleware, plugins, guias e mais. Em vez de sua IA alucinar detalhes de API ou adivinhar assinaturas de métodos, ele busca os documentos reais sob demanda. Sem chaves de API, sem instalação necessária para a versão hospedada.

Visite o [repositório no Github](https://github.com/flightphp/mcp) para o código-fonte completo e detalhes.

## Início Rápido

O servidor está hospedado publicamente e pronto para uso:

```
https://mcp.flightphp.com/mcp
```

Basta adicionar essa URL à sua extensão de codificação de IA. Sem cadastro, sem credenciais. Veja a seção [Configuração de IDE](#configuração-de-ide--extensão-de-ia) abaixo para configurações de copiar e colar para as ferramentas mais populares.

## O Que Ele Faz

Uma vez conectado, seu assistente de IA pode:

- **Navegar por todos os documentos disponíveis** — listar todos os tópicos principais, guias e páginas de plugins
- **Buscar qualquer página de documentação** — recuperar o conteúdo completo para roteamento, middleware, requisições, segurança e mais
- **Consultar documentação de plugins** — obter a documentação completa para ActiveRecord, Session, Tracy, Runway e todos os outros plugins oficiais
- **Seguir guias passo a passo** — acessar walkthroughs completos para construir blogs, APIs REST e aplicações testadas
- **Pesquisar em tudo** — encontrar páginas relevantes em documentos principais, guias e plugins de uma só vez

### Pontos Principais
- **Configuração zero** — o servidor hospedado em `https://mcp.flightphp.com/mcp` não requer instalação ou chaves de API.
- **Sempre atualizado** — o servidor busca documentos ao vivo de [docs.flightphp.com](https://docs.flightphp.com), então está sempre em dia.
- **Funciona em qualquer lugar** — qualquer ferramenta que suporte o transporte HTTP Streamable do MCP pode se conectar.
- **Auto-hospedável** — execute sua própria instância com PHP >= 8.1 e Composer se preferir.

## Configuração de IDE / Extensão de IA

O servidor usa o transporte HTTP Streamable. Escolha sua extensão abaixo e cole a configuração.

### Claude Code (CLI)

Execute o seguinte comando para adicioná-lo ao seu projeto:

```bash
claude mcp add --transport http flightphp-docs https://mcp.flightphp.com/mcp
```

Ou adicione manualmente ao `.mcp.json` do seu projeto:

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

Adicione ao `.vscode/mcp.json` no seu workspace:

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

Adicione ao `settings.json` do seu VS Code:

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

Adicione ao `~/.continue/config.json`:

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

## Ferramentas Disponíveis

O servidor MCP expõe as seguintes ferramentas para o seu assistente de IA:

| Ferramenta | Descrição |
|------------|-----------|
| `list_docs_pages` | Lista todos os tópicos de documentação principal disponíveis com slugs e descrições |
| `get_docs_page` | Busca uma página de documentação principal por slug de tópico (ex: `routing`, `middleware`, `security`) |
| `list_guide_pages` | Lista todos os guias passo a passo disponíveis |
| `get_guide_page` | Busca um guia completo por slug (ex: `blog`, `unit-testing`) |
| `list_plugin_pages` | Lista todas as páginas de plugins e extensões disponíveis |
| `get_plugin_docs` | Busca a documentação completa de um plugin por slug (ex: `active-record`, `session`, `jwt`) |
| `search_docs` | Pesquisa em todos os documentos, guias e plugins por uma palavra-chave ou tópico |
| `fetch_url` | Busca qualquer página diretamente por sua URL completa `docs.flightphp.com` |

## Auto-Hospedagem

Prefere executar sua própria instância? Você precisará de PHP >= 8.1 e Composer.

```bash
git clone https://github.com/flightphp/mcp.git
cd mcp
composer install
php server.php
```

O servidor inicia em `http://0.0.0.0:8890/mcp` por padrão. Atualize a configuração da sua IDE para apontar para o endereço local:

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