# Flight vs Fat-Free

## O que é Fat-Free?
[Fat-Free](https://fatfreeframework.com) (carinhosamente conhecido como **F3**) é um micro-framework PHP poderoso e fácil de usar, projetado para ajudá-lo a construir aplicativos web dinâmicos e robustos - rapidamente!

Flight se compara ao Fat-Free de muitas maneiras e é provavelmente o primo mais próximo em termos de recursos e simplicidade. Fat-Free tem
muitos recursos que Flight não tem, mas também tem muitos recursos que Flight tem. Fat-Free está começando a mostrar sua idade
e não é tão popular quanto costumava ser.

As atualizações estão se tornando menos frequentes e a comunidade não é tão ativa quanto costumava ser. O código é simples o suficiente, mas às vezes a falta de
disciplina de sintaxe pode torná-lo difícil de ler e entender. Ele funciona para PHP 8.3, mas o código em si ainda parece que vive no
PHP 5.3.

## Prós em comparação com Flight

- Fat-Free tem algumas estrelas a mais no GitHub do que Flight.
- Fat-Free tem uma documentação decente, mas falta clareza em algumas áreas.
- Fat-Free tem alguns recursos esparsos, como tutoriais no YouTube e artigos online que podem ser usados para aprender o framework.
- Fat-Free tem [alguns plugins úteis](https://fatfreeframework.com/3.8/api-reference) integrados que são às vezes úteis.
- Fat-Free tem um ORM integrado chamado Mapper que pode ser usado para interagir com seu banco de dados. Flight tem [active-record](/awesome-plugins/active-record).
- Fat-Free tem Sessions, Cache e localização integrados. Flight requer que você use bibliotecas de terceiros, mas está coberto na [documentação](/awesome-plugins).
- Fat-Free tem um pequeno grupo de [plugins criados pela comunidade](https://fatfreeframework.com/3.8/development#Community) que podem ser usados para estender o framework. Flight tem alguns cobertos na [documentação](/awesome-plugins) e [exemplos](/examples).
- Fat-Free, como Flight, não tem dependências.
- Fat-Free, como Flight, é voltado para dar ao desenvolvedor controle sobre sua aplicação e uma experiência de desenvolvimento simples.
- Fat-Free mantém compatibilidade com versões anteriores, como Flight (parcialmente porque as atualizações estão ficando [menos frequentes](https://github.com/bcosca/fatfree/releases)).
- Fat-Free, como Flight, é destinado a desenvolvedores que estão se aventurando no mundo dos frameworks pela primeira vez.
- Fat-Free tem um motor de templates integrado que é mais robusto do que o motor de templates do Flight. Flight recomenda [Latte](/awesome-plugins/latte) para isso.
- Fat-Free tem um comando de tipo CLI único "route" onde você pode construir aplicativos CLI dentro do próprio Fat-Free e tratá-lo como um pedido `GET`. Flight realiza isso com [runway](/awesome-plugins/runway).

## Contras em comparação com Flight

- Fat-Free tem alguns testes de implementação e até tem sua própria [classe de teste](https://fatfreeframework.com/3.8/test) que é muito básica. No entanto,
  não é 100% testado com unit tests como Flight.
- Você tem que usar um mecanismo de busca como o Google para realmente pesquisar o site de documentação.
- Flight tem modo escuro em seu site de documentação. (mic drop)
- Fat-Free tem alguns módulos que são lamentavelmente não mantidos.
- Flight tem um [PdoWrapper](/learn/pdo-wrapper) simples que é um pouco mais simples do que a classe `DB\SQL` integrada do Fat-Free.
- Flight tem um [plugin de permissões](/awesome-plugins/permissions) que pode ser usado para proteger sua aplicação. Fat-Free requer que você use
  uma biblioteca de terceiros.
- Flight tem um ORM chamado [active-record](/awesome-plugins/active-record) que parece mais um ORM do que o Mapper do Fat-Free.
  O benefício adicional do `active-record` é que você pode definir relacionamentos entre registros para joins automáticos, onde o Mapper do Fat-Free
  requer que você crie [views SQL](https://fatfreeframework.com/3.8/databases#ProsandCons).
- Incrivelmente, Fat-Free não tem um namespace raiz. Flight é namespaced completamente para não colidir com seu próprio código.
  A classe `Cache` é a maior infratora aqui.
- Fat-Free não tem middleware. Em vez disso, há ganchos `beforeroute` e `afterroute` que podem ser usados para filtrar requisições e respostas em controladores.
- Fat-Free não pode agrupar rotas.
- Fat-Free tem um manipulador de contêiner de injeção de dependência, mas a documentação é incrivelmente esparsa sobre como usá-lo.
- A depuração pode ficar um pouco complicada, já que basicamente tudo é armazenado no que é chamado de [`HIVE`](https://fatfreeframework.com/3.8/quick-reference)