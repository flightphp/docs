# IA & Experiência do Desenvolvedor com Flight

## Visão Geral

Flight facilita o superpoder de seus projetos PHP com ferramentas alimentadas por IA e fluxos de trabalho modernos para desenvolvedores. Com comandos integrados para conectar a provedores de LLM (Large Language Model) e gerar instruções de codificação específicas do projeto baseadas em IA, Flight ajuda você e sua equipe a tirar o máximo proveito de assistentes de IA como GitHub Copilot, Cursor, Windsurf e Antigravity (Gemini).

## Entendendo

Assistentes de codificação de IA são mais úteis quando entendem o contexto, as convenções e os objetivos do seu projeto. Os ajudantes de IA do Flight permitem que você:
- Conecte seu projeto a provedores populares de LLM (OpenAI, Grok, Claude, etc.)
- Gere e atualize instruções específicas do projeto para ferramentas de IA, para que todos recebam ajuda consistente e relevante
- Mantenha sua equipe alinhada e produtiva, com menos tempo gasto explicando o contexto

Esses recursos estão integrados ao CLI principal do Flight e ao projeto inicial oficial [flightphp/skeleton](https://github.com/flightphp/skeleton).

## Uso Básico

### Configurando Credenciais de LLM

O comando `ai:init` o guia através da conexão do seu projeto a um provedor de LLM.

```bash
php runway ai:init
```

Você será solicitado a:
- Escolher seu provedor (OpenAI, Grok, Claude, etc.)
- Inserir sua chave de API
- Definir a URL base e o nome do modelo

Isso cria as credenciais necessárias para que você faça solicitações futuras de LLM.

**Exemplo:**
```
Bem-vindo ao AI Init!
Qual API de LLM você deseja usar? [1] openai, [2] grok, [3] claude: 1
Digite a URL base para a API de LLM [https://api.openai.com]:
Digite sua chave de API para openai: sk-...
Digite o nome do modelo que você deseja usar (ex. gpt-4, claude-3-opus, etc) [gpt-4o]:
Credenciais salvas em .runway-creds.json
```

### Gerando Instruções de IA Específicas do Projeto

O comando `ai:generate-instructions` ajuda você a criar ou atualizar instruções para assistentes de codificação de IA, adaptadas ao seu projeto.

```bash
php runway ai:generate-instructions
```

Você responderá a algumas perguntas sobre seu projeto (descrição, banco de dados, templating, segurança, tamanho da equipe, etc.). Flight usa seu provedor de LLM para gerar instruções e, em seguida, as escreve em:
- `.github/copilot-instructions.md` (para GitHub Copilot)
- `.cursor/rules/project-overview.mdc` (para Cursor)
- `.windsurfrules` (para Windsurf)
- `.gemini/GEMINI.md` (para Antigravity)

**Exemplo:**
```
Por favor, descreva para que serve seu projeto? Minha API incrível
Qual banco de dados você planeja usar? MySQL
Qual engine de templating HTML você planeja usar (se houver)? latte
A segurança é um elemento importante deste projeto? (s/n) s
...
Instruções de IA atualizadas com sucesso.
```

Agora, suas ferramentas de IA darão sugestões mais inteligentes e relevantes com base nas necessidades reais do seu projeto.

## Uso Avançado

- Você pode personalizar a localização dos seus arquivos de credenciais ou instruções usando opções de comando (veja `--help` para cada comando).
- Os ajudantes de IA são projetados para funcionar com qualquer provedor de LLM que suporte APIs compatíveis com OpenAI.
- Se você quiser atualizar suas instruções à medida que seu projeto evolui, basta executar novamente `ai:generate-instructions` e responder aos prompts novamente.

## Veja Também

- [Flight Skeleton](https://github.com/flightphp/skeleton) – O inicial oficial com integração de IA
- [Runway CLI](/awesome-plugins/runway) – Mais sobre a ferramenta CLI que impulsiona esses comandos

## Solução de Problemas

- Se você vir "Missing .runway-creds.json", execute `php runway ai:init` primeiro.
- Certifique-se de que sua chave de API é válida e tem acesso ao modelo selecionado.
- Se as instruções não estiverem atualizando, verifique as permissões de arquivo no diretório do seu projeto.

## Changelog

- v3.16.0 – Adicionado comandos CLI `ai:init` e `ai:generate-instructions` para integração de IA.