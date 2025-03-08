# Plugins Incríveis

Flight é incrivelmente extensível. Existem vários plugins que podem ser usados para adicionar funcionalidade à sua aplicação Flight. Alguns são oficialmente suportados pela equipe Flight e outros são bibliotecas micro/lite para ajudá-lo a começar.

## Documentação da API

A documentação da API é crucial para qualquer API. Ela ajuda os desenvolvedores a entender como interagir com sua API e o que esperar em troca. Existem algumas ferramentas disponíveis para ajudá-lo a gerar documentação da API para seus projetos Flight.

- [FlightPHP OpenAPI Generator](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - Post do blog escrito por Daniel Schreiber sobre como usar a Especificação OpenAPI com FlightPHP para construir sua API usando uma abordagem primeiro a API.
- [SwaggerUI](https://github.com/zircote/swagger-php) - Swagger UI é uma ótima ferramenta para ajudá-lo a gerar documentação da API para seus projetos Flight. É muito fácil de usar e pode ser personalizado para atender às suas necessidades. Esta é a biblioteca PHP que o ajudará a gerar a documentação Swagger.

## Autenticação/Autorização

Autenticação e Autorização são cruciais para qualquer aplicação que exija controles para determinar quem pode acessar o quê.

- <span class="badge bg-primary">oficial</span> [flightphp/permissions](/awesome-plugins/permissions) - Biblioteca oficial de Permissões Flight. Esta biblioteca é uma maneira simples de adicionar permissões de nível de usuário e aplicação à sua aplicação.

## Cache

Cache é uma ótima maneira de acelerar sua aplicação. Existem várias bibliotecas de cache que podem ser usadas com Flight.

- <span class="badge bg-primary">oficial</span> [flightphp/cache](/awesome-plugins/php-file-cache) - Classe de cache em arquivo PHP leve, simples e autônoma.

## CLI

Aplicações CLI são uma ótima maneira de interagir com sua aplicação. Você pode usá-las para gerar controladores, exibir todas as rotas e mais.

- <span class="badge bg-primary">oficial</span> [flightphp/runway](/awesome-plugins/runway) - Runway é uma aplicação CLI que o ajuda a gerenciar suas aplicações Flight.

## Cookies

Cookies são uma ótima maneira de armazenar pequenos pedaços de dados no lado do cliente. Eles podem ser usados para armazenar preferências do usuário, configurações da aplicação e mais.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie é uma biblioteca PHP que fornece uma maneira simples e eficaz de gerenciar cookies.

## Depuração

A depuração é crucial ao desenvolver em seu ambiente local. Existem alguns plugins que podem elevar sua experiência de depuração.

- [tracy/tracy](/awesome-plugins/tracy) - Este é um manipulador de erros completo que pode ser usado com Flight. Ele possui vários painéis que podem ajudá-lo a depurar sua aplicação. Também é muito fácil de estender e adicionar seus próprios painéis.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Usado com o manipulador de erros [Tracy](/awesome-plugins/tracy), este plugin adiciona alguns painéis extras para ajudar especificamente na depuração de projetos Flight.

## Bancos de Dados

Bancos de dados são a base da maioria das aplicações. Esta é a forma como você armazena e recupera dados. Algumas bibliotecas de banco de dados são apenas wrappers para escrever consultas e algumas são ORMs completos.

- <span class="badge bg-primary">oficial</span> [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Wrapper PDO oficial do Flight que faz parte do núcleo. Este é um wrapper simples para ajudar a simplificar o processo de escrever consultas e executá-las. Não é um ORM.
- <span class="badge bg-primary">oficial</span> [flightphp/active-record](/awesome-plugins/active-record) - ORM/Mapper ActiveRecord do Flight oficial. Ótima biblioteca para recuperar e armazenar dados em seu banco de dados de forma fácil.
- [byjg/php-migration](/awesome-plugins/migrations) - Plugin para manter o controle de todas as alterações no banco de dados para o seu projeto.

## Criptografia

A criptografia é crucial para qualquer aplicação que armazena dados sensíveis. Criptografar e descriptografar os dados não é muito difícil, mas armazenar adequadamente a chave de criptografia [pode](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [ser](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [difícil](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key). A coisa mais importante é nunca armazenar sua chave de criptografia em um diretório público ou comprometer em seu repositório de código.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - Esta é uma biblioteca que pode ser usada para criptografar e descriptografar dados. Começar a usá-la é bastante simples para iniciar a criptografia e descriptografia de dados.

## Fila de Trabalho

Filas de trabalho são muito úteis para processar tarefas de forma assíncrona. Isso pode incluir o envio de e-mails, processamento de imagens ou qualquer coisa que não precisa ser feita em tempo real.

- [n0nag0n/simple-job-queue](/awesome-plugins/simple-job-queue) - Fila de Trabalho Simples é uma biblioteca que pode ser usada para processar trabalhos de forma assíncrona. Pode ser usada com beanstalkd, MySQL/MariaDB, SQLite e PostgreSQL.

## Sessão

Sessões não são realmente úteis para APIs, mas para construir uma aplicação web, as sessões podem ser cruciais para manter o estado e as informações de login.

- <span class="badge bg-primary">oficial</span> [flightphp/session](/awesome-plugins/session) - Biblioteca oficial de Sessões Flight. Esta é uma biblioteca simples de sessão que pode ser usada para armazenar e recuperar dados de sessão. Ela utiliza o gerenciamento de sessões embutido do PHP.
- [Ghostff/Session](/awesome-plugins/ghost-session) - Gerenciador de Sessões PHP (não bloqueante, flash, segmento, criptografia de sessão). Utiliza PHP open_ssl para criptografia/descriptografia opcional de dados de sessão.

## Modelagem

Modelagem é essencial para qualquer aplicação web com uma interface de usuário. Existem vários mecanismos de modelagem que podem ser usados com Flight.

- <span class="badge bg-warning">obsoleto</span> [flightphp/core View](/learn#views) - Este é um mecanismo de modelagem muito básico que faz parte do núcleo. Não é recomendado o uso se você tiver mais de algumas páginas em seu projeto.
- [latte/latte](/awesome-plugins/latte) - Latte é um mecanismo de modelagem completo que é muito fácil de usar e se sente mais próximo de uma sintaxe PHP do que Twig ou Smarty. Também é muito fácil de estender e adicionar seus próprios filtros e funções.

## Contribuindo

Tem um plugin que gostaria de compartilhar? Envie um pedido de pull para adicioná-lo à lista!