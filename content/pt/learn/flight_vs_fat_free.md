# Voo vs Sem Gordura

## O que é Fat-Free?
[Sem Gordura](https://fatfreeframework.com) (afetuosamente conhecido como **SG**) é um microframework PHP poderoso, mas fácil de usar, projetado para ajudá-lo a construir aplicativos web dinâmicos e robustos - rapidamente!

Voo se compara com Sem Gordura de muitas maneiras e provavelmente é o parente mais próximo em termos de recursos e simplicidade. Sem Gordura tem muitos recursos que Voo não tem, mas também tem muitos recursos que Voo tem. Sem Gordura está começando a mostrar sua idade e não é tão popular quanto já foi.

As atualizações estão se tornando menos frequentes e a comunidade não é mais tão ativa quanto já foi. O código é simples o suficiente, mas às vezes a falta de disciplina sintática pode tornar difícil de ler e entender. Ele funciona para o PHP 8.3, mas o código em si ainda parece que pertence ao PHP 5.3.

## Prós em comparação com Voo

- Sem Gordura tem um pouco mais de estrelas no GitHub do que Voo.
- Sem Gordura tem uma documentação decente, mas falta clareza em algumas áreas.
- Sem Gordura tem alguns recursos escassos como tutoriais do YouTube e artigos online que podem ser usados para aprender o framework.
- Sem Gordura tem [alguns plugins úteis](https://fatfreeframework.com/3.8/api-reference) integrados que às vezes são úteis.
- Sem Gordura tem um ORM integrado chamado Mapper que pode ser usado para interagir com seu banco de dados. Voo tem [active-record](/awesome-plugins/active-record).
- Sem Gordura tem Sessões, Cache e localização embutidos. Voo requer que você use bibliotecas de terceiros, mas está coberto na [documentação](/awesome-plugins).
- Sem Gordura tem um pequeno grupo de [plugins criados pela comunidade](https://fatfreeframework.com/3.8/development#Community) que podem ser usados para estender o framework. Voo tem alguns cobertos na [documentação](/awesome-plugins) e páginas de [exemplos](/examples).
- Sem Gordura assim como Voo não possui dependências.
- Sem Gordura assim como Voo é voltado para dar ao desenvolvedor controle sobre seu aplicativo e uma experiência de desenvolvedor simples.
- Sem Gordura mantém compatibilidade com versões anteriores como Voo faz (parcialmente porque as atualizações estão se tornando menos frequentes [menos frequentes](https://github.com/bcosca/fatfree/releases)).
- Sem Gordura assim como Voo é destinado a desenvolvedores que estão se aventurando no mundo dos frameworks pela primeira vez.
- Sem Gordura tem um mecanismo de modelo integrado que é mais robusto do que o mecanismo de modelo do Voo. Voo recomenda [Latte](/awesome-plugins/latte) para realizar isso.
- Sem Gordura possui um comando de tipo CLI único "route" onde você pode construir aplicativos CLI dentro do próprio Sem Gordura e tratá-lo como uma solicitação `GET`. Voo realiza isso com [runway](/awesome-plugins/runway).

## Contras em comparação com Voo

- Sem Gordura tem alguns testes de implementação e até possui sua própria classe de [teste](https://fatfreeframework.com/3.8/test) que é muito básica. No entanto,
  não é 100% testado unitariamente como Voo é.
- Você precisa usar um mecanismo de busca como o Google para realmente pesquisar o site de documentação.
- Voo tem modo escuro em seu site de documentação. (microfone derrubado)
- Sem Gordura tem alguns módulos que são lamentavelmente não mantidos.
- Voo tem um [PdoWrapper](/awesome-plugins/pdo-wrapper) simples que é um pouco mais simples do que a classe `DB\SQL` integrada do Sem Gordura.
- Voo tem um plugin de [permissões](/awesome-plugins/permissions) que pode ser usado para proteger seu aplicativo. Slim requer que você use
  uma biblioteca de terceiros.
- Voo tem um ORM chamado [active-record](/awesome-plugins/active-record) que parece mais com um ORM do que o Mapper do Sem Gordura.
  O benefício adicional do `active-record` é que você pode definir relacionamentos entre registros para junções automáticas onde o Mapper do Sem Gordura
  requer que você crie [visualizações SQL](https://fatfreeframework.com/3.8/databases#ProsandCons).
- Surpreendentemente, Sem Gordura não tem um espaço de nomes raiz. Voo tem espaço de nomes o tempo todo para não colidir com seu próprio código.
  a classe `Cache` é o maior infrator aqui.
- Sem Gordura não possui middleware. Em vez disso, existem ganchos `beforeroute` e `afterroute` que podem ser usados para filtrar solicitações e respostas em controladores.
- Sem Gordura não pode agrupar rotas.
- Sem Gordura possui um manipulador de contêiner de injeção de dependência, mas a documentação é incrivelmente escassa sobre como usá-lo.
- Depurar pode ficar um pouco complicado, uma vez que basicamente tudo é armazenado no que é chamado de [`HIVE`](https://fatfreeframework.com/3.8/quick-reference)