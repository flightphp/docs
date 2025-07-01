# IA e Experiência do Desenvolvedor com Flight

Flight é tudo sobre ajudar você a construir mais rápido, mais inteligente e com menos atrito — especialmente ao trabalhar com ferramentas alimentadas por IA e fluxos de trabalho modernos de desenvolvedor. Esta página cobre como Flight facilita superalimentar seus projetos com IA e como começar com os novos assistentes de IA integrados ao framework e ao projeto de esqueleto.

---

## IA-Pronta por Padrão: O Projeto de Esqueleto

O starter oficial [flightphp/skeleton](https://github.com/flightphp/skeleton) agora vem com instruções e configuração para assistentes de codificação de IA populares:

- **GitHub Copilot**
- **Cursor**
- **Windsurf**

Essas ferramentas são pré-configuradas com instruções específicas do projeto, para que você e sua equipe possam obter a ajuda mais relevante e consciente do contexto ao codificar. Isso significa:

- Assistentes de IA entendem os objetivos, estilo e requisitos do seu projeto
- Orientação consistente para todos os colaboradores
- Menos tempo gasto explicando o contexto, mais tempo construindo

> **Por que isso importa?**
>
> Quando suas ferramentas de IA conhecem a intenção e as convenções do seu projeto, elas podem ajudar a estruturar recursos, refatorar código e evitar erros comuns — tornando você (e sua equipe) mais produtivo desde o primeiro dia.

---

## Novos Comandos de IA no Núcleo do Flight

_v3.16.0+_

O núcleo do Flight agora inclui dois comandos CLI poderosos para ajudar a configurar e direcionar seu projeto com IA:

### 1. `ai:init` — Conecte-se ao Seu Provedor de LLM Favorito

Este comando o orienta na configuração de credenciais para um provedor de LLM (Modelo de Linguagem Grande), como OpenAI, Grok ou Anthropic (Claude).

**Exemplo:**
```bash
php runway ai:init
```
Você será solicitado a selecionar seu provedor, inserir sua chave de API e escolher um modelo. Isso facilita conectar seu projeto aos serviços de IA mais recentes — sem configuração manual necessária.

### 2. `ai:generate-instructions` — Instruções de Codificação com IA Consciente do Projeto

Este comando ajuda a criar ou atualizar instruções específicas do projeto para assistentes de codificação de IA. Ele faz algumas perguntas simples sobre seu projeto (como para o que ele serve, qual banco de dados você usa, tamanho da equipe etc.) e, em seguida, usa seu provedor de LLM para gerar instruções personalizadas.

Se você já tiver instruções, ele as atualizará para refletir as respostas que você fornecer. Essas instruções são escritas automaticamente em:
- `.github/copilot-instructions.md` (para Github Copilot)
- `.cursor/rules/project-overview.mdc` (para Cursor)
- `.windsurfrules` (para Windsurf)

**Exemplo:**
```bash
php runway ai:generate-instructions
```

> **Por que isso é útil?**
>
> Com instruções atualizadas e específicas do projeto, suas ferramentas de IA podem:
> - Fornecer sugestões de código melhores
> - Entender as necessidades únicas do seu projeto
> - Ajudar a embarcar novos colaboradores mais rapidamente
> - Reduzir atrito e confusão à medida que seu projeto evolui

---

## Não Apenas para Construir Aplicativos de IA

Embora você possa usar Flight para construir recursos alimentados por IA (como chatbots, APIs inteligentes ou integrações), o verdadeiro poder está em como Flight ajuda você a trabalhar melhor com ferramentas de IA como desenvolvedor. É sobre:

- **Aumentar a produtividade** com codificação assistida por IA
- **Manter sua equipe alinhada** com instruções compartilhadas e em evolução
- **Facilitar o onboarding** para novos colaboradores
- **Deixar você se concentrar em construir**, e não em lutar contra suas ferramentas

---

## Saiba Mais e Comece

- Veja o [Flight Skeleton](https://github.com/flightphp/skeleton) para um starter pronto e amigável à IA
- Confira o resto da [documentação do Flight](/learn) para dicas sobre como construir aplicativos PHP rápidos e modernos