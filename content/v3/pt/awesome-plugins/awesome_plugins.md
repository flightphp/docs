# Plugins Incríveis

Flight é extremamente extensível. Existem vários plugins que podem ser usados para adicionar funcionalidade à sua aplicação Flight. Alguns são oficialmente suportados pela equipe Flight e outros são bibliotecas micro/lite para ajudar você a começar.

## Documentação da API

A documentação da API é crucial para qualquer API. Ela ajuda os desenvolvedores a entenderem como interagir com sua API e o que esperar em troca. Existem algumas ferramentas disponíveis para ajudar a gerar documentação da API para seus projetos Flight.

- [Gerador OpenAPI do FlightPHP](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - Postagem no blog escrita por Daniel Schreiber sobre como usar a especificação OpenAPI com FlightPHP para construir sua API usando uma abordagem API first.
- [SwaggerUI](https://github.com/zircote/swagger-php) - Swagger UI é uma ótima ferramenta para ajudar a gerar documentação da API para seus projetos Flight. É muito fácil de usar e pode ser personalizada para atender às suas necessidades. Esta é a biblioteca PHP para ajudar a gerar a documentação Swagger.

## Monitoramento de Desempenho de Aplicações (APM)

Monitoramento de Desempenho de Aplicações (APM) é crucial para qualquer aplicação. Ele ajuda você a entender como sua aplicação está performando e onde estão os gargalos. Existem vários ferramentas de APM que podem ser usadas com Flight.
- <span class="badge bg-primary">oficial</span> [flightphp/apm](/awesome-plugins/apm) - Flight APM é uma biblioteca simples de APM que pode ser usada para monitorar suas aplicações Flight. Ela pode ser usada para monitorar o desempenho da sua aplicação e ajudar a identificar gargalos.

## Autorização/Permissões

Autorização e Permissões são cruciais para qualquer aplicação que exija controles para definir quem pode acessar o quê.

- <span class="badge bg-primary">oficial</span> [flightphp/permissions](/awesome-plugins/permissions) - Biblioteca oficial de Permissões do Flight. Esta biblioteca é uma forma simples de adicionar permissões em nível de usuário e aplicação à sua aplicação. 

## Caching

Caching é uma ótima forma de acelerar sua aplicação. Existem vários bibliotecas de caching que podem ser usadas com Flight.

- <span class="badge bg-primary">oficial</span> [flightphp/cache](/awesome-plugins/php-file-cache) - Classe leve, simples e autônoma de caching em arquivo PHP

## CLI

Aplicações CLI são uma ótima forma de interagir com sua aplicação. Você pode usá-las para gerar controladores, exibir todas as rotas e muito mais.

- <span class="badge bg-primary">oficial</span> [flightphp/runway](/awesome-plugins/runway) - Runway é uma aplicação CLI que ajuda a gerenciar suas aplicações Flight.

## Cookies

Cookies são uma ótima forma de armazenar pequenos pedaços de dados no lado do cliente. Eles podem ser usados para armazenar preferências do usuário, configurações da aplicação e muito mais.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie é uma biblioteca PHP que fornece uma forma simples e eficaz de gerenciar cookies.

## Debugging

Debugging é crucial quando você está desenvolvendo em seu ambiente local. Existem alguns plugins que podem elevar sua experiência de debugging.

- [tracy/tracy](/awesome-plugins/tracy) - Esta é uma ferramenta de gerenciamento de erros completa que pode ser usada com Flight. Ela tem vários painéis que podem ajudar a depurar sua aplicação. Também é muito fácil de estender e adicionar seus próprios painéis.
- <span class="badge bg-primary">oficial</span> [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Usado com o [Tracy](/awesome-plugins/tracy) gerenciador de erros, este plugin adiciona alguns painéis extras para ajudar com debugging específico para projetos Flight.

## Bancos de Dados

Bancos de dados são o núcleo da maioria das aplicações. É assim que você armazena e recupera dados. Algumas bibliotecas de banco de dados são simplesmente wrappers para escrever consultas e outras são ORMs completos.

- <span class="badge bg-primary">oficial</span> [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Wrapper oficial PDO do Flight que faz parte do core. Este é um wrapper simples para ajudar a simplificar o processo de escrever consultas e executá-las. Não é um ORM.
- <span class="badge bg-primary">oficial</span> [flightphp/active-record](/awesome-plugins/active-record) - ORM/Mapper ActiveRecord oficial do Flight. Ótima pequena biblioteca para recuperar e armazenar dados em seu banco de dados facilmente.
- [byjg/php-migration](/awesome-plugins/migrations) - Plugin para rastrear todas as alterações no banco de dados para seu projeto.

## Criptografia

Criptografia é crucial para qualquer aplicação que armazena dados sensíveis. Criptografar e descriptografar os dados não é terrivelmente difícil, mas armazenar corretamente a chave de criptografia [can](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [be](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [difficult](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key). O mais importante é nunca armazenar sua chave de criptografia em um diretório público ou commitá-la em seu repositório de código.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - Esta é uma biblioteca que pode ser usada para criptografar e descriptografar dados. Começar a usá-la é bastante simples para começar a criptografar e descriptografar dados.

## Fila de Tarefas

Filas de tarefas são realmente úteis para processar tarefas de forma assíncrona. Isso pode ser enviar emails, processar imagens ou qualquer coisa que não precise ser feita em tempo real.

- [n0nag0n/simple-job-queue](/awesome-plugins/simple-job-queue) - Simple Job Queue é uma biblioteca que pode ser usada para processar tarefas de forma assíncrona. Ela pode ser usada com beanstalkd, MySQL/MariaDB, SQLite e PostgreSQL.

## Sessão

Sessões não são realmente úteis para APIs, mas para construir uma aplicação web, sessões podem ser cruciais para manter o estado e informações de login.

- <span class="badge bg-primary">oficial</span> [flightphp/session](/awesome-plugins/session) - Biblioteca oficial de Sessão do Flight. Esta é uma biblioteca simples de sessão que pode ser usada para armazenar e recuperar dados de sessão. Ela usa o gerenciamento de sessão integrado do PHP.
- [Ghostff/Session](/awesome-plugins/ghost-session) - Gerenciador de Sessão PHP (não bloqueante, flash, segmento, criptografia de sessão). Usa PHP open_ssl para criptografia opcional/descriptografia de dados de sessão.

## Templating

Templating é o núcleo de qualquer aplicação web com uma interface de usuário. Existem vários engines de templating que podem ser usados com Flight.

- <span class="badge bg-warning">descontinuado</span> [flightphp/core View](/learn#views) - Esta é uma engine de templating muito básica que faz parte do core. Não é recomendável usá-la se você tiver mais do que algumas páginas em seu projeto.
- [latte/latte](/awesome-plugins/latte) - Latte é uma engine de templating completa que é muito fácil de usar e se sente mais próxima da sintaxe PHP do que Twig ou Smarty. Também é muito fácil de estender e adicionar seus próprios filtros e funções.

## Integração com WordPress

Quer usar Flight no seu projeto WordPress? Há um plugin prático para isso!

- [n0nag0n/wordpress-integration-for-flight-framework](/awesome-plugins/n0nag0n_wordpress) - Este plugin do WordPress permite que você execute Flight junto com o WordPress. É perfeito para adicionar APIs personalizadas, microservices ou até aplicativos completos ao seu site WordPress usando o framework Flight. Super útil se você quiser o melhor dos dois mundos!

## Contribuindo

Tem um plugin que você gostaria de compartilhar? Envie um pull request para adicioná-lo à lista!