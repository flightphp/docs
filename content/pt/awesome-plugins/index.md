# Plugins Incríveis

Flight é incrivelmente extensível. Existem vários plugins que podem ser usados para adicionar funcionalidades à sua aplicação Flight. Alguns são oficialmente suportados pela Equipe FlightPHP e outros são bibliotecas micro/lite para ajudá-lo a começar.

## Cache

Cache é uma ótima maneira de acelerar sua aplicação. Existem várias bibliotecas de cache que podem ser usadas com o Flight.

- [Wruczek/PHP-File-Cache](/awesome-plugins/php-file-cache) - Classe de cache leve, simples e independente de arquivos PHP

## Depuração

Depuração é crucial ao desenvolver no ambiente local. Existem alguns plugins que podem elevar sua experiência de depuração.

- [tracy/tracy](/awesome-plugins/tracy) - Este é um manipulador de erros completo que pode ser usado com o Flight. Possui várias áreas que podem ajudá-lo a depurar sua aplicação. Também é muito fácil de estender e adicionar suas próprias áreas.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Usado com o manipulador de erros [Tracy](/awesome-plugins/tracy), este plugin adiciona algumas áreas extras para ajudar na depuração, especificamente para projetos Flight.

## Bancos de Dados

Bancos de dados são o núcleo da maioria das aplicações. É assim que você armazena e recupera dados. Algumas bibliotecas de bancos de dados são simplesmente wrappers para escrever consultas e outras são ORMs completos.

- [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Wrapper oficial PDO Flight que faz parte do núcleo. Este é um wrapper simples para ajudar a simplificar o processo de escrever e executar consultas. Não é um ORM.
- [flightphp/active-record](/awesome-plugins/active-record) - ORM/Mapper Active Record oficial do Flight. Ótima pequena biblioteca para recuperar e armazenar dados facilmente no seu banco de dados.

## Sessão

Sessões não são realmente úteis para APIs, mas para criar uma aplicação web, as sessões podem ser cruciais para manter o estado e informações de login.

- [Ghostff/Session](/awesome-plugins/session) - Gerenciador de Sessão PHP (não bloqueante, flash, segmento, criptografia de sessão). Usa open_ssl do PHP para criptografar/descriptografar dados de sessão opcionalmente.

## Modelagem

A modelagem é fundamental para qualquer aplicação web com uma UI. Existem várias engines de modelagem que podem ser usadas com o Flight.

- [flightphp/core View](/learn#views) - Esta é uma engine de modelagem muito básica que faz parte do núcleo. Não é recomendado para uso se você tem mais do que algumas páginas em seu projeto.
- [latte/latte](/awesome-plugins/latte) - Latte é uma engine de modelagem completa e muito fácil de usar, com uma sintaxe mais próxima do PHP do que Twig ou Smarty. Também é muito fácil de estender e adicionar seus próprios filtros e funções.

## Contribuição

Tem um plugin que gostaria de compartilhar? Envie uma solicitação pull para adicioná-lo à lista!