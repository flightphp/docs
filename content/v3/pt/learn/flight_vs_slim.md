# Comparação entre Flight e Slim

## O que é o Slim?
[Slim](https://slimframework.com) é um microframework PHP que ajuda você a escrever rapidamente aplicações web e APIs simples, porém poderosas.

Muita da inspiração para algumas das funcionalidades da versão 3 do Flight na verdade veio do Slim. Agrupar rotas e executar middleware em uma ordem específica são duas funcionalidades que foram inspiradas pelo Slim. O Slim v3 foi lançado voltado para a simplicidade, mas houve [críticas mistas](https://github.com/slimphp/Slim/issues/2770) em relação ao v4.

## Prós em comparação com o Flight

- O Slim tem uma comunidade maior de desenvolvedores, que por sua vez criam módulos úteis para ajudar você a não reinventar a roda.
- O Slim segue muitas interfaces e padrões comuns na comunidade PHP, o que aumenta a interoperabilidade.
- O Slim possui documentação decente e tutoriais que podem ser usados para aprender o framework (nada comparado ao Laravel ou Symfony, no entanto).
- O Slim possui diversos recursos como tutoriais no YouTube e artigos online que podem ser usados para aprender o framework.
- O Slim permite que você use os componentes que desejar para lidar com as funcionalidades de roteamento principais, pois é compatível com PSR-7.

## Contras em comparação com o Flight

- Surpreendentemente, o Slim não é tão rápido quanto você imagina que seria para um microframework. Consulte os 
  [testes do TechEmpower](https://www.techempower.com/benchmarks/#hw=ph&test=fortune&section=data-r22&l=zik073-cn3) 
  para obter mais informações.
- O Flight é voltado para um desenvolvedor que deseja construir uma aplicação web leve, rápida e fácil de usar.
- O Flight não possui dependências, enquanto que o [Slim possui algumas dependências](https://github.com/slimphp/Slim/blob/4.x/composer.json) que você precisa instalar.
- O Flight é direcionado para a simplicidade e facilidade de uso.
- Uma das funcionalidades principais do Flight é fazer o melhor possível para manter a compatibilidade com versões anteriores. A transição do Slim da v3 para a v4 foi uma mudança drástica.
- O Flight é destinado a desenvolvedores que estão se aventurando no mundo dos frameworks pela primeira vez.
- O Flight também pode lidar com aplicações de nível empresarial, mas não possui tantos exemplos e tutoriais quanto o Slim.
  Também exigirá mais disciplina por parte do desenvolvedor para manter as coisas organizadas e bem estruturadas.
- O Flight dá mais controle ao desenvolvedor sobre a aplicação, enquanto que o Slim pode introduzir alguma mágica nos bastidores.
- O Flight possui um simples [PdoWrapper](/awesome-plugins/pdo-wrapper) que pode ser usado para interagir com seu banco de dados. O Slim exige que você utilize 
  uma biblioteca de terceiros.
- O Flight possui um plugin de [permissões](/awesome-plugins/permissions) que pode ser usado para proteger sua aplicação. O Slim exige que você utilize 
  uma biblioteca de terceiros.
- O Flight possui um ORM chamado [active-record](/awesome-plugins/active-record) que pode ser usado para interagir com seu banco de dados. O Slim exige que você utilize 
  uma biblioteca de terceiros.
- O Flight possui uma aplicação CLI chamada [runway](/awesome-plugins/runway) que pode ser usada para executar sua aplicação a partir da linha de comando. O Slim não possui.