# Plugins Incríveis

Flight é incrivelmente extensível. Existem vários plugins que podem ser usados para adicionar funcionalidade à sua aplicação Flight. Alguns são oficialmente suportados pela Equipe Flight e outros são bibliotecas micro/lite para ajudá-lo a começar.

## Documentação de API

A documentação de API é crucial para qualquer API. Ela ajuda os desenvolvedores a entenderem como interagir com sua API e o que esperar em retorno. Existem algumas ferramentas disponíveis para ajudá-lo a gerar documentação de API para seus Projetos Flight.

- [FlightPHP OpenAPI Generator](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - Post de blog escrito por Daniel Schreiber sobre como usar a Especificação OpenAPI com FlightPHP para construir sua API usando uma abordagem API first.
- [SwaggerUI](https://github.com/zircote/swagger-php) - Swagger UI é uma ótima ferramenta para ajudá-lo a gerar documentação de API para seus projetos Flight. É muito fácil de usar e pode ser personalizada para atender às suas necessidades. Esta é a biblioteca PHP para ajudá-lo a gerar a documentação Swagger.

## Monitoramento de Desempenho de Aplicação (APM)

O Monitoramento de Desempenho de Aplicação (APM) é crucial para qualquer aplicação. Ele ajuda você a entender como sua aplicação está se saindo e onde estão os gargalos. Existem vários ferramentas APM que podem ser usadas com Flight.
- <span class="badge bg-primary">oficial</span> [flightphp/apm](/awesome-plugins/apm) - Flight APM é uma biblioteca APM simples que pode ser usada para monitorar suas aplicações Flight. Pode ser usada para monitorar o desempenho da sua aplicação e ajudá-lo a identificar gargalos.

## Assíncrono

Flight já é um framework rápido, mas adicionar um motor turbo a ele torna tudo mais divertido (e desafiador)!

- [flightphp/async](/awesome-plugins/async) - Biblioteca Async oficial do Flight. Esta biblioteca é uma forma simples de adicionar processamento assíncrono à sua aplicação. Ela usa Swoole/Openswoole sob o capô para fornecer uma maneira simples e eficaz de executar tarefas de forma assíncrona.

## Autorização/Permissões

Autorização e Permissões são cruciais para qualquer aplicação que exija controles para quem pode acessar o quê.

- <span class="badge bg-primary">oficial</span> [flightphp/permissions](/awesome-plugins/permissions) - Biblioteca de Permissões oficial do Flight. Esta biblioteca é uma forma simples de adicionar permissões em nível de usuário e aplicação à sua aplicação. 

## Cache

Cache é uma ótima maneira de acelerar sua aplicação. Existem várias bibliotecas de cache que podem ser usadas com Flight.

- <span class="badge bg-primary">oficial</span> [flightphp/cache](/awesome-plugins/php-file-cache) - Classe de cache em arquivo PHP leve, simples e independente

## CLI

Aplicações CLI são uma ótima maneira de interagir com sua aplicação. Você pode usá-las para gerar controladores, exibir todas as rotas e mais.

- <span class="badge bg-primary">oficial</span> [flightphp/runway](/awesome-plugins/runway) - Runway é uma aplicação CLI que ajuda você a gerenciar suas aplicações Flight.

## Cookies

Cookies são uma ótima maneira de armazenar pequenos pedaços de dados no lado do cliente. Eles podem ser usados para armazenar preferências do usuário, configurações da aplicação e mais.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie é uma biblioteca PHP que fornece uma maneira simples e eficaz de gerenciar cookies.

## Depuração

Depuração é crucial quando você está desenvolvendo em seu ambiente local. Existem alguns plugins que podem elevar sua experiência de depuração.

- [tracy/tracy](/awesome-plugins/tracy) - Este é um manipulador de erros completo que pode ser usado com Flight. Ele tem vários painéis que podem ajudá-lo a depurar sua aplicação. Também é muito fácil de estender e adicionar seus próprios painéis.
- <span class="badge bg-primary">oficial</span> [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Usado com o manipulador de erros [Tracy](/awesome-plugins/tracy), este plugin adiciona alguns painéis extras para ajudar na depuração especificamente para projetos Flight.

## Bancos de Dados

Bancos de dados são o núcleo da maioria das aplicações. É assim que você armazena e recupera dados. Algumas bibliotecas de banco de dados são simplesmente wrappers para escrever consultas e algumas são ORMs completos.

- <span class="badge bg-primary">oficial</span> [flightphp/core PdoWrapper](/learn/pdo-wrapper) - Wrapper PDO oficial do Flight que faz parte do núcleo. Este é um wrapper simples para ajudar a simplificar o processo de escrever consultas e executá-las. Não é um ORM.
- <span class="badge bg-primary">oficial</span> [flightphp/active-record](/awesome-plugins/active-record) - ORM/Mapper ActiveRecord oficial do Flight. Ótima biblioteca pequena para recuperar e armazenar dados facilmente em seu banco de dados.
- [byjg/php-migration](/awesome-plugins/migrations) - Plugin para manter o controle de todas as alterações de banco de dados para seu projeto.

## Criptografia

Criptografia é crucial para qualquer aplicação que armazena dados sensíveis. Criptografar e descriptografar os dados não é terrivelmente difícil, mas armazenar adequadamente a chave de criptografia [pode](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [ser](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [difícil](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key). O mais importante é nunca armazenar sua chave de criptografia em um diretório público ou commitá-la em seu repositório de código.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - Esta é uma biblioteca que pode ser usada para criptografar e descriptografar dados. Começar a usar é bastante simples para começar a criptografar e descriptografar dados.

## Fila de Tarefas

Filas de tarefas são realmente úteis para processar tarefas de forma assíncrona. Isso pode ser enviar e-mails, processar imagens ou qualquer coisa que não precise ser feita em tempo real.

- [n0nag0n/simple-job-queue](/awesome-plugins/simple-job-queue) - Simple Job Queue é uma biblioteca que pode ser usada para processar tarefas de forma assíncrona. Pode ser usada com beanstalkd, MySQL/MariaDB, SQLite e PostgreSQL.

## Sessão

Sessões não são realmente úteis para APIs, mas para construir uma aplicação web, sessões podem ser cruciais para manter o estado e informações de login.

- <span class="badge bg-primary">oficial</span> [flightphp/session](/awesome-plugins/session) - Biblioteca de Sessão oficial do Flight. Esta é uma biblioteca de sessão simples que pode ser usada para armazenar e recuperar dados de sessão. Ela usa o manuseio de sessão integrado do PHP.
- [Ghostff/Session](/awesome-plugins/ghost-session) - Gerenciador de Sessão PHP (não bloqueante, flash, segmento, criptografia de sessão). Usa PHP open_ssl para criptografia/descriptografia opcional de dados de sessão.

## Modelagem

Modelagem é o núcleo de qualquer aplicação web com uma UI. Existem vários motores de modelagem que podem ser usados com Flight.

- <span class="badge bg-warning">deprecado</span> [flightphp/core View](/learn#views) - Este é um motor de modelagem muito básico que faz parte do núcleo. Não é recomendado usá-lo se você tiver mais do que algumas páginas em seu projeto.
- [latte/latte](/awesome-plugins/latte) - Latte é um motor de modelagem completo que é muito fácil de usar e se sente mais próximo da sintaxe PHP do que Twig ou Smarty. Também é muito fácil de estender e adicionar seus próprios filtros e funções.
- [knifelemon/comment-template](/awesome-plugins/comment-template) - CommentTemplate é um poderoso motor de template PHP com compilação de assets, herança de templates e processamento de variáveis. Recursos incluem minificação automática de CSS/JS, cache, codificação Base64 e integração opcional com o framework PHP Flight.

## Integração com WordPress

Quer usar Flight em seu projeto WordPress? Há um plugin prático para isso!

- [n0nag0n/wordpress-integration-for-flight-framework](/awesome-plugins/n0nag0n_wordpress) - Este plugin do WordPress permite que você execute Flight ao lado do WordPress. É perfeito para adicionar APIs personalizadas, microsserviços ou até aplicativos completos ao seu site WordPress usando o framework Flight. Super útil se você quiser o melhor dos dois mundos!

## Contribuição

Tem um plugin que gostaria de compartilhar? Envie um pull request para adicioná-lo à lista!