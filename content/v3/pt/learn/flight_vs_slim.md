# Flight vs Slim

## O que é Slim?
[Slim](https://slimframework.com) é um micro framework PHP que ajuda você a escrever rapidamente aplicativos web simples, mas poderosos, e APIs.

Muita da inspiração para alguns dos recursos da v3 do Flight na verdade veio do Slim. Agrupar rotas e executar middleware em uma ordem específica são dois recursos inspirados no Slim. O Slim v3 foi lançado com foco em simplicidade, mas houve [críticas mistas](https://github.com/slimphp/Slim/issues/2770) em relação à v4.

## Vantagens em comparação ao Flight

- O Slim tem uma comunidade maior de desenvolvedores, que por sua vez criam módulos úteis para ajudá-lo a não reinventar a roda.
- O Slim segue muitas interfaces e padrões comuns na comunidade PHP, o que aumenta a interoperabilidade.
- O Slim tem documentação decente e tutoriais que podem ser usados para aprender o framework (nada comparado ao Laravel ou Symfony, no entanto).
- O Slim tem vários recursos, como tutoriais no YouTube e artigos online, que podem ser usados para aprender o framework.
- O Slim permite que você use quaisquer componentes que desejar para lidar com os recursos principais de roteamento, pois é compatível com PSR-7.

## Desvantagens em comparação ao Flight

- Surpreendentemente, o Slim não é tão rápido quanto você pensaria para um micro-framework. Veja os 
  [benchmarks do TechEmpower](https://www.techempower.com/benchmarks/#hw=ph&test=fortune&section=data-r22&l=zik073-cn3) 
  para mais informações.
- O Flight é voltado para um desenvolvedor que busca construir um aplicativo web leve, rápido e fácil de usar.
- O Flight não tem dependências, enquanto o [Slim tem algumas dependências](https://github.com/slimphp/Slim/blob/4.x/composer.json) que você deve instalar.
- O Flight é voltado para simplicidade e facilidade de uso.
- Um dos recursos principais do Flight é que ele faz o possível para manter a compatibilidade com versões anteriores. A mudança do Slim v3 para v4 foi uma quebra de compatibilidade.
- O Flight é destinado a desenvolvedores que estão se aventurando no mundo dos frameworks pela primeira vez.
- O Flight também pode lidar com aplicativos de nível empresarial, mas não tem tantos exemplos e tutoriais quanto o Slim.
  Ele também exigirá mais disciplina por parte do desenvolvedor para manter as coisas organizadas e bem estruturadas.
- O Flight dá ao desenvolvedor mais controle sobre o aplicativo, enquanto o Slim pode introduzir alguma magia nos bastidores.
- O Flight tem um [PdoWrapper](/learn/pdo-wrapper) simples que pode ser usado para interagir com seu banco de dados. O Slim exige que você use uma biblioteca de terceiros.
- O Flight tem um plugin de [permissões](/awesome-plugins/permissions) que pode ser usado para proteger seu aplicativo. O Slim exige que você use uma biblioteca de terceiros.
- O Flight tem um ORM chamado [active-record](/awesome-plugins/active-record) que pode ser usado para interagir com seu banco de dados. O Slim exige que você use uma biblioteca de terceiros.
- O Flight tem um aplicativo CLI chamado [runway](/awesome-plugins/runway) que pode ser usado para executar seu aplicativo a partir da linha de comando. O Slim não tem.