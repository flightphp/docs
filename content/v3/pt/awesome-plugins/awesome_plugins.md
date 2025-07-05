# Plugins Incríveis

Flight é incrivelmente extensível. Há uma série de plugins que podem ser usados para adicionar funcionalidade à sua aplicação Flight. Alguns são oficialmente suportados pela Equipe Flight e outros são bibliotecas micro/lite para ajudar você a começar.

## Documentação da API

A documentação da API é crucial para qualquer API. Ela ajuda os desenvolvedores a entenderem como interagir com sua API e o que esperar em troca. Há algumas ferramentas disponíveis para ajudar você a gerar documentação da API para seus projetos Flight.

- [FlightPHP Gerador OpenAPI](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - Postagem de blog escrita por Daniel Schreiber sobre como usar a especificação OpenAPI com FlightPHP para construir sua API usando uma abordagem API first.
- [SwaggerUI](https://github.com/zircote/swagger-php) - Swagger UI é uma ótima ferramenta para ajudar você a gerar documentação da API para seus projetos Flight. É muito fácil de usar e pode ser personalizado para atender às suas necessidades. Esta é a biblioteca PHP para ajudar você a gerar a documentação Swagger.

## Monitoramento de Desempenho de Aplicação (APM)

O Monitoramento de Desempenho de Aplicação (APM) é crucial para qualquer aplicação. Ele ajuda você a entender como sua aplicação está performando e onde estão os gargalos. Há uma série de ferramentas de APM que podem ser usadas com Flight.
- <span class="badge bg-info">beta</span>[flightphp/apm](/awesome-plugins/apm) - Flight APM é uma biblioteca de APM simples que pode ser usada para monitorar suas aplicações Flight. Ela pode ser usada para monitorar o desempenho da sua aplicação e ajudar você a identificar gargalos.

## Autenticação/Autorização

A autenticação e autorização são cruciais para qualquer aplicação que exija controles para quem pode acessar o quê.

- <span class="badge bg-primary">oficial</span> [flightphp/permissions](/awesome-plugins/permissions) - Biblioteca oficial de permissões do Flight. Esta biblioteca é uma forma simples de adicionar permissões de usuário e de aplicação ao seu aplicativo.

## Caching

O caching é uma ótima forma de acelerar sua aplicação. Há uma série de bibliotecas de caching que podem ser usadas com Flight.

- <span class="badge bg-primary">oficial</span> [flightphp/cache](/awesome-plugins/php-file-cache) - Classe leve, simples e autônoma de caching em arquivo PHP

## CLI

Aplicações CLI são uma ótima forma de interagir com sua aplicação. Você pode usá-las para gerar controladores, exibir todas as rotas e muito mais.

- <span class="badge bg-primary">oficial</span> [flightphp/runway](/awesome-plugins/runway) - Runway é uma aplicação CLI que ajuda você a gerenciar suas aplicações Flight.

## Cookies

Cookies são uma ótima forma de armazenar pequenos pedaços de dados no lado do cliente. Eles podem ser usados para armazenar preferências do usuário, configurações de aplicação e muito mais.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie é uma biblioteca PHP que fornece uma forma simples e eficaz de gerenciar cookies.

## Depuração

A depuração é crucial quando você está desenvolvendo em seu ambiente local. Há alguns plugins que podem elevar sua experiência de depuração.

- [tracy/tracy](/awesome-plugins/tracy) - Trata-se de um manipulador de erros com recursos completos que pode ser usado com Flight. Ele possui uma série de painéis que podem ajudar você a depurar sua aplicação. Além disso, é muito fácil de estender e adicionar seus próprios painéis.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Usado com o [Tracy](/awesome-plugins/tracy) manipulador de erros, este plugin adiciona alguns painéis extras para ajudar com a depuração especificamente para projetos Flight.

## Bancos de Dados

Bancos de dados são o núcleo da maioria das aplicações. É assim que você armazena e recupera dados. Algumas bibliotecas de banco de dados são simplesmente wrappers para escrever consultas e outras são ORMs de pleno direito.

- <span class="badge bg-primary">oficial</span> [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Wrapper oficial do PDO do Flight que faz parte do core. Trata-se de um wrapper simples para ajudar a simplificar o processo de escrita de consultas e execução delas. Não é um ORM.
- <span class="badge bg-primary">oficial</span> [flightphp/active-record](/awesome-plugins/active-record) - ORM/Mapper ActiveRecord oficial do Flight. Ótima pequena biblioteca para recuperar e armazenar dados em seu banco de dados com facilidade.
- [byjg/php-migration](/awesome-plugins/migrations) - Plugin para rastrear todas as alterações de banco de dados para seu projeto.

## Criptografia

A criptografia é crucial para qualquer aplicação que armazene dados sensíveis. Criptografar e descriptografar os dados não é terrivelmente difícil, mas armazenar corretamente a chave de criptografia [can](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [be](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [difficult](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key). O mais importante é nunca armazenar sua chave de criptografia em um diretório público ou commitê-la ao seu repositório de código.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - Esta é uma biblioteca que pode ser usada para criptografar e descriptografar dados. Começar a usar é bastante simples para começar a criptografar e descriptografar dados.

## Fila de Tarefas

Filas de tarefas são realmente úteis para processar tarefas de forma assíncrona. Isso pode ser enviado e-mails, processar imagens ou qualquer coisa que não precise ser feita em tempo real.

- [n0nag0n/simple-job-queue](/awesome-plugins/simple-job-queue) - Simple Job Queue é uma biblioteca que pode ser usada para processar tarefas de forma assíncrona. Ela pode ser usada com beanstalkd, MySQL/MariaDB, SQLite e PostgreSQL.

## Sessão

Sessões não são realmente úteis para APIs, mas para construir uma aplicação web, sessões podem ser cruciais para manter o estado e informações de login.

- <span class="badge bg-primary">oficial</span> [flightphp/session](/awesome-plugins/session) - Biblioteca oficial de sessão do Flight. Trata-se de uma biblioteca de sessão simples que pode ser usada para armazenar e recuperar dados de sessão. Ela usa o manuseio de sessão incorporado do PHP.
- [Ghostff/Session](/awesome-plugins/ghost-session) - Gerenciador de Sessão PHP (não bloqueante, flash, segmento, criptografia de sessão). Usa o PHP open_ssl para criptografia/decrypt opcional de dados de sessão.

## Templating

O templating é o núcleo de qualquer aplicação web com uma interface de usuário. Há uma série de engines de templating que podem ser usados com Flight.

- <span class="badge bg-warning">descontinuado</span> [flightphp/core View](/learn#views) - Trata-se de um engine de templating muito básico que faz parte do core. Não é recomendado usá-lo se você tiver mais de algumas páginas em seu projeto.
- [latte/latte](/awesome-plugins/latte) - Latte é um engine de templating com recursos completos que é muito fácil de usar e se sente mais próximo de uma sintaxe PHP do que Twig ou Smarty. Além disso, é muito fácil de estender e adicionar seus próprios filtros e funções.

## Integração com WordPress

Quer usar Flight no seu projeto WordPress? Há um plugin prático para isso!

- [n0nag0n/wordpress-integration-for-flight-framework](/awesome-plugins/n0nag0n_wordpress) - Este plugin do WordPress permite que você execute Flight ao lado do WordPress. É perfeito para adicionar APIs personalizadas, microservices ou até aplicativos completos ao seu site WordPress usando o framework Flight. Super útil se você quiser o melhor dos dois mundos!

## Contribuindo

Tem um plugin que você gostaria de compartilhar? Envie um pull request para adicioná-lo à lista!