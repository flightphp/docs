# Plugins Incríveis

Flight é incrivelmente extensível. Existem vários plugins que podem ser usados para adicionar funcionalidade à sua aplicação Flight. Alguns são oficialmente suportados pela equipe Flight e outros são bibliotecas micro/lite para ajudá-lo a começar.

## Documentação da API

A documentação da API é crucial para qualquer API. Ela ajuda os desenvolvedores a entender como interagir com sua API e o que esperar em troca. Existem algumas ferramentas disponíveis para ajudá-lo a gerar documentação da API para seus projetos Flight.

- [FlightPHP OpenAPI Generator](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - Postagem no blog escrita por Daniel Schreiber sobre como usar a Especificação OpenAPI com FlightPHP para construir sua API usando uma abordagem primeiro a API.
- [SwaggerUI](https://github.com/zircote/swagger-php) - Swagger UI é uma ótima ferramenta para ajudá-lo a gerar documentação da API para seus projetos Flight. É muito fácil de usar e pode ser personalizado para atender às suas necessidades. Esta é a biblioteca PHP para ajudá-lo a gerar a documentação Swagger.

## Monitoramento de Desempenho de Aplicação (APM)

O Monitoramento de Desempenho de Aplicação (APM) é crucial para qualquer aplicação. Ele ajuda você a entender como sua aplicação está se saindo e onde estão os gargalos. Existem várias ferramentas APM que podem ser usadas com Flight.
- <span class="badge bg-info">beta</span>[flightphp/flight-apm](/awesome-plugins/apm) - Flight APM é uma biblioteca APM simples que pode ser usada para monitorar suas aplicações Flight. Pode ser usada para monitorar o desempenho da sua aplicação e ajudá-lo a identificar gargalos.

## Autenticação/Autorização

A autenticação e autorização são cruciais para qualquer aplicação que requer controles sobre quem pode acessar o que.

- <span class="badge bg-primary">official</span> [flightphp/permissions](/awesome-plugins/permissions) - Biblioteca oficial de Permissões do Flight. Esta biblioteca é uma maneira simples de adicionar permissões a nível de usuário e aplicação à sua aplicação.

## Caching

O caching é uma ótima maneira de acelerar sua aplicação. Existem várias bibliotecas de caching que podem ser usadas com o Flight.

- <span class="badge bg-primary">official</span> [flightphp/cache](/awesome-plugins/php-file-cache) - Classe de caching in-file PHP leve, simples e autônoma

## CLI

As aplicações CLI são uma ótima maneira de interagir com sua aplicação. Você pode usá-las para gerar controladores, exibir todas as rotas e mais.

- <span class="badge bg-primary">official</span> [flightphp/runway](/awesome-plugins/runway) - Runway é uma aplicação CLI que ajuda você a gerenciar suas aplicações Flight.

## Cookies

Os cookies são uma ótima maneira de armazenar pequenos pedaços de dados no lado do cliente. Eles podem ser usados para armazenar preferências de usuário, configurações de aplicação e mais.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie é uma biblioteca PHP que fornece uma maneira simples e eficaz de gerenciar cookies.

## Depuração

A depuração é crucial ao desenvolver em seu ambiente local. Existem alguns plugins que podem melhorar sua experiência de depuração.

- [tracy/tracy](/awesome-plugins/tracy) - Este é um manipulador de erros completo que pode ser usado com o Flight. Ele possui vários painéis que podem ajudá-lo a depurar sua aplicação. Também é muito fácil de estender e adicionar seus próprios painéis.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Usado com o [Tracy](/awesome-plugins/tracy) manipulador de erros, este plugin adiciona alguns painéis extras para ajudar com a depuração especificamente para projetos Flight.

## Bancos de Dados

Os bancos de dados são o núcleo da maioria das aplicações. É assim que você armazena e recupera dados. Algumas bibliotecas de banco de dados são simplesmente wrappers para escrever consultas e outras são ORMs completos.

- <span class="badge bg-primary">official</span> [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Wrapper PDO oficial do Flight que faz parte do núcleo. Este é um wrapper simples para ajudar a simplificar o processo de escrever consultas e executá-las. Não é um ORM.
- <span class="badge bg-primary">official</span> [flightphp/active-record](/awesome-plugins/active-record) - ORM/Mapeador ActiveRecord oficial do Flight. Ótima biblioteca para recuperar e armazenar dados facilmente em seu banco de dados.
- [byjg/php-migration](/awesome-plugins/migrations) - Plugin para acompanhar todas as alterações no banco de dados do seu projeto.

## Criptografia

A criptografia é crucial para qualquer aplicação que armazene dados sensíveis. Criptografar e descriptografar os dados não é tão difícil, mas armazenar corretamente a chave de criptografia [pode](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [ser](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [difícil](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key). A coisa mais importante é nunca armazenar sua chave de criptografia em um diretório público ou comitá-la em seu repositório de código.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - Esta é uma biblioteca que pode ser usada para criptografar e descriptografar dados. Começar a usar é relativamente simples para começar a criptografar e descriptografar dados.

## Fila de Trabalho

As filas de trabalho são muito úteis para processar tarefas de forma assíncrona. Isso pode ser o envio de e-mails, processamento de imagens ou qualquer coisa que não precise ser feita em tempo real.

- [n0nag0n/simple-job-queue](/awesome-plugins/simple-job-queue) - Fila de Trabalho Simples é uma biblioteca que pode ser usada para processar trabalhos de forma assíncrona. Pode ser usada com beanstalkd, MySQL/MariaDB, SQLite e PostgreSQL.

## Sessão

As sessões não são realmente úteis para APIs, mas para construir uma aplicação web, as sessões podem ser cruciais para manter o estado e as informações de login.

- <span class="badge bg-primary">official</span> [flightphp/session](/awesome-plugins/session) - Biblioteca oficial de Sessão do Flight. Esta é uma biblioteca de sessão simples que pode ser usada para armazenar e recuperar dados de sessão. Utiliza o gerenciamento de sessão embutido do PHP.
- [Ghostff/Session](/awesome-plugins/ghost-session) - Gerenciador de Sessões PHP (não bloqueante, flash, segmentação, criptografia de sessão). Usa o PHP open_ssl para criptografar/descriptografar opcionais os dados da sessão.

## Modelagem

A modelagem é fundamental para qualquer aplicação web com uma interface de usuário. Existem vários mecanismos de modelagem que podem ser usados com Flight.

- <span class="badge bg-warning">deprecated</span> [flightphp/core View](/learn#views) - Este é um mecanismo de modelagem muito básico que faz parte do núcleo. Não é recomendável usá-lo se você tiver mais de algumas páginas em seu projeto.
- [latte/latte](/awesome-plugins/latte) - Latte é um mecanismo de modelagem completo que é muito fácil de usar e se sente mais próximo de uma sintaxe PHP do que Twig ou Smarty. Também é muito fácil de estender e adicionar seus próprios filtros e funções.

## Contribuindo

Tem um plugin que gostaria de compartilhar? Envie uma solicitação de pull para adicioná-lo à lista!