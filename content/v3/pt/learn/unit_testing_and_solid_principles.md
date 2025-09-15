> _Este artigo foi originalmente publicado no [Airpair](https://web.archive.org/web/20220204014708/https://www.airpair.com/php/posts/best-practices-for-modern-php-development#5-unit-testing) em 2015. Todo o crédito é dado ao Airpair e a Brian Fenton, que originalmente escreveu este artigo, embora o site não esteja mais disponível e o artigo só exista dentro da [Wayback Machine](https://web.archive.org/web/20220204014708/https://www.airpair.com/php/posts/best-practices-for-modern-php-development#5-unit-testing). Este artigo foi adicionado ao site para fins de aprendizado e educacionais para a comunidade PHP em geral._

1 Configuração e instalação
-------------------------

### 1.1 Mantenha atualizado

Vamos destacar isso desde o início - um número deprimentemente pequeno de instalações de PHP no mundo real está atualizado ou mantido atualizado. Seja devido a restrições de hospedagem compartilhada, padrões que ninguém pensa em alterar, ou falta de tempo/orçamento para testes de atualização, os humildes binários do PHP tendem a ficar para trás. Então, uma prática recomendada clara que precisa de mais ênfase é sempre usar uma versão atual do PHP (5.6.x conforme este artigo). Além disso, é importante agendar atualizações regulares tanto do PHP quanto de quaisquer extensões ou bibliotecas de fornecedores que você possa estar usando. As atualizações trazem novos recursos de linguagem, velocidade aprimorada, menor uso de memória e atualizações de segurança. Quanto mais frequentemente você atualizar, menos doloroso o processo se torna.

### 1.2 Defina padrões sensíveis

O PHP faz um trabalho decente ao definir bons padrões fora da caixa com seus arquivos _php.ini.development_ e _php.ini.production_, mas podemos fazer melhor. Por exemplo, eles não definem uma data/horário para nós. Isso faz sentido do ponto de vista de distribuição, mas sem um, o PHP lançará um erro E_WARNING toda vez que chamarmos uma função relacionada a data/hora. Aqui estão algumas configurações recomendadas:

*   date.timezone - escolha da [lista de fusos horários suportados](http://php.net/manual/en/timezones.php)
*   session.save_path - se estivermos usando arquivos para sessões e não algum outro manipulador de salvamento, defina isso para algo fora de _/tmp_. Deixar isso como _/tmp_ pode ser arriscado em um ambiente de hospedagem compartilhada, pois _/tmp_ geralmente tem permissões amplamente abertas. Mesmo com o bit sticky definido, qualquer um com acesso para listar o conteúdo desse diretório pode aprender todos os seus IDs de sessão ativos.
*   session.cookie_secure - algo óbvio, ative isso se você estiver servindo seu código PHP por HTTPS.
*   session.cookie_httponly - defina isso para impedir que cookies de sessão do PHP sejam acessíveis via JavaScript
*   Mais... use uma ferramenta como [iniscan](https://github.com/psecio/iniscan) para testar sua configuração quanto a vulnerabilidades comuns

### 1.3 Extensões

Também é uma boa ideia desabilitar (ou pelo menos não habilitar) extensões que você não vai usar, como drivers de banco de dados. Para ver o que está habilitado, execute o comando `phpinfo()` ou vá para uma linha de comando e execute isso.

```bash
$ php -i
``` 

As informações são as mesmas, mas phpinfo() adiciona formatação HTML. A versão CLI é mais fácil de redirecionar para grep para encontrar informações específicas. Ex.

```bash
$ php -i | grep error_log
```

Uma ressalva desse método: é possível ter configurações diferentes do PHP aplicadas à versão voltada para a web e à versão CLI.

2 Use Composer
--------------

Isso pode surpreender, mas uma das melhores práticas para escrever PHP moderno é escrever menos dele. Embora seja verdade que uma das melhores maneiras de se tornar bom em programação é fazer isso, há um grande número de problemas que já foram resolvidos no espaço PHP, como roteamento, bibliotecas básicas de validação de entrada, conversão de unidades, camadas de abstração de banco de dados, etc... Basta ir para [Packagist](https://www.packagist.org/) e navegar. Você provavelmente descobrirá que partes significativas do problema que você está tentando resolver já foram escritas e testadas.

Embora seja tentador escrever todo o código você mesmo (e não há nada de errado em escrever seu próprio framework ou biblioteca como uma experiência de aprendizado), você deve lutar contra esses sentimentos de "Não Inventado Aqui" e se poupar de muito tempo e dor de cabeça. Siga a doutrina do PIE em vez disso - Orgulhosamente Inventado Em Outro Lugar. Além disso, se você escolher escrever seu próprio algo, não o libere a menos que ele faça algo significativamente diferente ou melhor do que as ofertas existentes.

[Composer](https://www.getcomposer.org/) é um gerenciador de pacotes para PHP, semelhante ao pip no Python, gem no Ruby e npm no Node. Ele permite que você defina um arquivo JSON que lista as dependências do seu código e tentará resolver esses requisitos para você, baixando e instalando os pacotes de código necessários.

### 2.1 Instalando Composer

Estamos assumindo que isso é um projeto local, então vamos instalar uma instância do Composer apenas para o projeto atual. Navegue para o diretório do seu projeto e execute isso:
```bash
$ curl -sS https://getcomposer.org/installer | php
```

Lembre-se de que direcionar qualquer download diretamente para um interpretador de script (sh, ruby, php, etc...) é um risco de segurança, então leia o código de instalação e garanta que você esteja confortável com ele antes de executar qualquer comando como esse.

Por conveniência (se você preferir digitar `composer install` em vez de `php composer.phar install`), você pode usar este comando para instalar uma cópia única do composer globalmente:

```bash
$ mv composer.phar /usr/local/bin/composer
$ chmod +x composer
```

Você pode precisar executar esses com `sudo`, dependendo das suas permissões de arquivo.

### 2.2 Usando Composer

O Composer tem duas categorias principais de dependências que ele pode gerenciar: "require" e "require-dev". Dependências listadas como "require" são instaladas em todos os lugares, mas dependências "require-dev" são instaladas apenas quando solicitadas especificamente. Geralmente, essas são ferramentas para quando o código está em desenvolvimento ativo, como [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer). A linha abaixo mostra um exemplo de como instalar [Guzzle](http://docs.guzzlephp.org/en/latest/), uma biblioteca HTTP popular.

```bash
$ php composer.phar require guzzle/guzzle
```

Para instalar uma ferramenta apenas para fins de desenvolvimento, adicione a flag `--dev`:

```bash
$ php composer.phar require --dev 'sebastian/phpcpd'
```

Isso instala [PHP Copy-Paste Detector](https://github.com/sebastianbergmann/phpcpd), outra ferramenta de qualidade de código como uma dependência apenas para desenvolvimento.

### 2.3 Install vs update

Quando executamos `composer install` pela primeira vez, ele instalará quaisquer bibliotecas e suas dependências de que precisamos, com base no arquivo _composer.json_. Quando isso é feito, o composer cria um arquivo de bloqueio, previsivelmente chamado _composer.lock_. Esse arquivo contém uma lista das dependências que o composer encontrou para nós e suas versões exatas, com hashes. Então, qualquer vez futura que executarmos `composer install`, ele olhará no arquivo de bloqueio e instalará aquelas versões exatas.

`composer update` é um pouco diferente. Ele ignorará o arquivo _composer.lock_ (se presente) e tentará encontrar as versões mais atualizadas de cada uma das dependências que ainda satisfazem as restrições em _composer.json_. Ele então escreve um novo _composer.lock_ quando terminar.

### 2.4 Autoload

Tanto o composer install quanto o composer update gerarão um [autoloader](https://getcomposer.org/doc/04-schema.md#autoload) para nós que diz ao PHP onde encontrar todos os arquivos necessários para usar as bibliotecas que acabamos de instalar. Para usá-lo, basta adicionar esta linha (geralmente a um arquivo de bootstrap que é executado em cada solicitação):
```php
require 'vendor/autoload.php';
```

3 Siga bons princípios de design
-------------------------------

### 3.1 SOLID

SOLID é um mnemônico para nos lembrar de cinco princípios-chave no bom design de software orientado a objetos.

#### 3.1.1 S - Princípio da Responsabilidade Única

Isso afirma que as classes devem ter apenas uma responsabilidade, ou dito de outra forma, elas devem ter apenas um motivo para mudar. Isso se encaixa bem com a filosofia Unix de muitas ferramentas pequenas, fazendo uma coisa bem. Classes que fazem apenas uma coisa são muito mais fáceis de testar e depurar, e menos propensas a surpreendê-lo. Você não quer que uma chamada de método para uma classe Validator atualize registros no banco de dados. Aqui está um exemplo de violação de SRP, do tipo que você veria comumente em um aplicativo baseado no [padrão ActiveRecord](http://en.wikipedia.org/wiki/Active_record_pattern).

```php
class Person extends Model
{
    public $name;
    public $birthDate;
    protected $preferences;
    public function getPreferences() {}
    public function save() {}
}
```
    

Então isso é um modelo de [entidade](http://lostechies.com/jimmybogard/2008/05/21/entities-value-objects-aggregates-and-roots/) bastante básico. No entanto, uma dessas coisas não pertence aqui. A única responsabilidade de um modelo de entidade deve ser o comportamento relacionado à entidade que ele representa; ele não deve ser responsável por persistir a si mesmo.

```php
class Person extends Model
{
    public $name;
    public $birthDate;
    protected $preferences;
    public function getPreferences() {}
}
class DataStore
{
    public function save(Model $model) {}
}
```

Isso é melhor. O modelo Person está de volta a fazer apenas uma coisa, e o comportamento de salvamento foi movido para um objeto de persistência. Observe também que eu só adicionei um type hint em Model, não em Person. Voltaremos a isso quando chegarmos às partes L e D do SOLID.

#### 3.1.2 O - Princípio Aberto/Fechado

Há um teste incrível para isso que resume bem o que esse princípio é: pense em um recurso para implementar, provavelmente o mais recente que você trabalhou ou está trabalhando. Você pode implementar esse recurso no seu código existente APENAS adicionando novas classes e não alterando nenhuma classe existente no seu sistema? Sua configuração e código de fiação recebem um pouco de perdão, mas na maioria dos sistemas isso é surpreendentemente difícil. Você tem que depender muito de despacho polimórfico e a maioria dos códigos não está configurada para isso. Se você estiver interessado nisso, há uma boa palestra do Google no YouTube sobre [polimorfismo e escrever código sem Ifs](https://www.youtube.com/watch?v=4F72VULWFvc) que aprofunda isso. Como bônus, a palestra é dada por [Miško Hevery](http://misko.hevery.com/), que muitos podem conhecer como o criador do [AngularJs](https://angularjs.org/).

#### 3.1.3 L - Princípio da Substituição de Liskov

Esse princípio é nomeado para [Barbara Liskov](http://en.wikipedia.org/wiki/Barbara_Liskov) e é impresso abaixo:

> "Objetos em um programa devem ser substituíveis por instâncias de seus subtipos sem alterar a correção desse programa."

Isso parece bom, mas é mais claramente ilustrado com um exemplo.

```php
abstract class Shape
{
    public function getHeight();
    public function setHeight($height);
    public function getLength();
    public function setLength($length);
}
```   

Isso vai representar nossa forma básica de quatro lados. Nada chique aqui.

```php
class Square extends Shape
{
    protected $size;
    public function getHeight() {
        return $this->size;
    }
    public function setHeight($height) {
        $this->size = $height;
    }
    public function getLength() {
        return $this->size;
    }
    public function setLength($length) {
        $this->size = $length;
    }
}
```

Aqui está nossa primeira forma, o Quadrado. Uma forma bem direta, certo? Você pode assumir que há um construtor onde definimos as dimensões, mas você vê aqui desta implementação que o comprimento e a altura sempre serão os mesmos. Quadrados são assim.

```php
class Rectangle extends Shape
{
    protected $height;
    protected $length;
    public function getHeight() {
        return $this->height;
    }
    public function setHeight($height) {
        $this->height = $height;
    }
    public function getLength() {
        return $this->length;
    }
    public function setLength($length) {
        $this->length = $length;
    }
}
```

Então aqui temos uma forma diferente. Ainda tem as mesmas assinaturas de método, ainda é uma forma de quatro lados, mas e se começarmos a tentar usá-las no lugar uma da outra? Agora, de repente, se mudarmos a altura da nossa Shape, não podemos mais assumir que o comprimento da nossa forma corresponderá. Violamos o contrato que tínhamos com o usuário quando demos a eles nossa forma Quadrado.

Isso é um exemplo clássico de violação do LSP e precisamos desse tipo de princípio para fazer o melhor uso de um sistema de tipos. Até mesmo o [duck typing](http://en.wikipedia.org/wiki/Duck_typing) não nos dirá se o comportamento subjacente é diferente, e como não podemos saber isso sem vê-lo quebrar, é melhor garantir que não seja diferente em primeiro lugar.

#### 3.1.3 I - Princípio da Segregação de Interface

Esse princípio diz para favorecer muitas interfaces pequenas e refinadas em vez de uma grande. Interfaces devem ser baseadas em comportamento em vez de "é uma dessas classes". Pense nas interfaces que vêm com o PHP. Traversable, Countable, Serializable, coisas assim. Elas anunciam capacidades que o objeto possui, não o que ele herda. Então, mantenha suas interfaces pequenas. Você não quer uma interface com 30 métodos, 3 é uma meta muito melhor.

#### 3.1.4 D - Princípio da Inversão de Dependência

Você provavelmente ouviu falar disso em outros lugares que falaram sobre [Injeção de Dependência](http://en.wikipedia.org/wiki/Dependency_injection), mas Inversão de Dependência e Injeção de Dependência não são exatamente a mesma coisa. Inversão de dependência é realmente apenas uma forma de dizer que você deve depender de abstrações no seu sistema e não dos seus detalhes. O que isso significa para você no dia a dia?

> Não use diretamente mysqli_query() em todo o seu código, use algo como DataStore->query() em vez disso.

O cerne desse princípio é sobre abstrações. É mais sobre dizer "use um adaptador de banco de dados" em vez de depender de chamadas diretas a coisas como mysqli_query. Se você está usando mysqli_query diretamente em metade das suas classes, você está amarrando tudo diretamente ao seu banco de dados. Nada contra o MySQL aqui, mas se você está usando mysqli_query, esse tipo de detalhe de baixo nível deve ser escondido em apenas um lugar e então essa funcionalidade deve ser exposta via um wrapper genérico.

Agora eu sei que isso é um exemplo um pouco batido se você pensar sobre isso, porque o número de vezes que você vai realmente mudar completamente o motor do seu banco de dados após o produto estar em produção é muito, muito baixo. Eu escolhi isso porque imaginei que as pessoas estariam familiarizadas com a ideia do seu próprio código. Além disso, mesmo se você tiver um banco de dados que sabe que vai manter, esse objeto wrapper abstrato permite que você corrija bugs, mude o comportamento ou implemente recursos que você deseja que o seu banco de dados escolhido tivesse. Ele também torna o teste unitário possível onde chamadas de baixo nível não o fariam.

4 Calistenia de objetos
---------------------

Isso não é um mergulho completo nesses princípios, mas os dois primeiros são fáceis de lembrar, fornecem bom valor e podem ser aplicados imediatamente a praticamente qualquer base de código.

### 4.1 Não mais que um nível de indentação por método

Isso é uma forma útil de pensar sobre decompor métodos em pedaços menores, deixando você com código que é mais claro e autodocumentado. Quanto mais níveis de indentação você tiver, mais o método está fazendo e mais estado você tem que rastrear na sua cabeça enquanto trabalha com ele.

Logo de cara eu sei que as pessoas vão objetar a isso, mas isso é apenas uma diretriz/heurística, não uma regra rígida e rápida. Eu não espero que ninguém imponha regras do PHP_CodeSniffer para isso (embora [pessoas tenham](https://github.com/object-calisthenics/phpcs-calisthenics-rules)).

Vamos passar por uma amostra rápida do que isso pode parecer:

```php
public function transformToCsv($data)
{
    $csvLines = array();
    $csvLines[] = implode(',', array_keys($data[0]));
    foreach ($data as $row) {
        if (!$row) {
            continue;
        }
        $csvLines[] = implode(',', $row);
    }
    return $csvLines;
}
```

Embora isso não seja um código terrível (é tecnicamente correto, testável, etc...), podemos fazer muito mais para torná-lo claro. Como reduzir os níveis de aninhamento aqui?

Sabemos que precisamos simplificar muito o conteúdo do loop foreach (ou removê-lo completamente), então vamos começar aí.

```php
if (!$row) {
    continue;
}
```   

Essa primeira parte é fácil. Tudo o que isso está fazendo é ignorar linhas vazias. Podemos encurtar esse processo inteiro usando uma função integrada do PHP antes de chegarmos ao loop.

```php
$data = array_filter($data);
foreach ($data as $row) {
    $csvLines[] = implode(',', $row);
}
```

Agora temos nosso único nível de aninhamento. Mas olhando para isso, tudo o que estamos fazendo é aplicar uma função a cada item em um array. Nós nem precisamos do loop foreach para fazer isso.

```php
$data = array_filter($data);
$csvLines = array_map(function($row) {
    return implode(',', $row);
}, $data);
```

Agora não temos aninhamento algum, e o código provavelmente será mais rápido, pois estamos fazendo todo o looping com funções nativas em C em vez de PHP. Temos que nos envolver um pouco para passar a vírgula para `implode`, então você poderia argumentar que parar no passo anterior é muito mais compreensível.

### 4.2 Tente não usar `else`

Isso realmente lida com duas ideias principais. A primeira é múltiplos statements de return de um método. Se você tiver informações suficientes para tomar uma decisão sobre o resultado do método, vá em frente e tome essa decisão e retorne. A segunda é uma ideia conhecida como [Guard Clauses](http://c2.com/cgi/wiki?GuardClause). Essas são basicamente verificações de validação combinadas com retornos iniciais, geralmente perto do topo de um método. Deixe-me mostrar o que quero dizer.

```php
public function addThreeInts($first, $second, $third) {
    if (is_int($first)) {
        if (is_int($second)) {
            if (is_int($third)) {
                $sum = $first + $second + $third;
            } else {
                return null;
            }
        } else {
            return null;
        }
    } else {
        return null;
    }
    return $sum;
}
```

Então isso é bem direto novamente, ele adiciona 3 ints juntos e retorna o resultado, ou `null` se qualquer dos parâmetros não for um inteiro. Ignorando o fato de que podemos combinar todas essas verificações em uma única linha com operadores AND, acho que você pode ver como a estrutura if/else aninhada torna o código mais difícil de seguir. Agora olhe para este exemplo em vez disso.

```php
public function addThreeInts($first, $second, $third) {
    if (!is_int($first)) {
        return null;
    }
    if (!is_int($second)) {
        return null;
    }
    if (!is_int($third)) {
        return null;
    }
    return $first + $second + $third;
}
```   

Para mim, esse exemplo é muito mais fácil de seguir. Aqui estamos usando cláusulas de guarda para verificar nossas asserções iniciais sobre os parâmetros que estamos passando e saindo imediatamente do método se eles não passarem. Também não temos mais a variável intermediária para rastrear a soma o caminho todo pelo método. Nesse caso, verificamos que já estamos no caminho feliz e podemos apenas fazer o que viemos aqui para fazer. Novamente, podemos fazer todas essas verificações em um único `if`, mas o princípio deve estar claro.

5 Testes unitários
--------------

Testes unitários é a prática de escrever testes pequenos que verificam o comportamento no seu código. Eles são quase sempre escritos na mesma linguagem que o código (nesse caso PHP) e são destinados a ser rápidos o suficiente para rodar a qualquer momento. Eles são extremamente valiosos como uma ferramenta para melhorar o seu código. Além dos benefícios óbvios de garantir que o seu código esteja fazendo o que você acha que está, testes unitários podem fornecer feedback de design muito útil também. Se um pedaço de código é difícil de testar, isso frequentemente destaca problemas de design. Eles também dão a você uma rede de segurança contra regressões e isso permite que você refatore muito mais frequentemente e evolua o seu código para um design mais limpo.

### 5.1 Ferramentas

Há várias ferramentas de testes unitários por aí no PHP, mas de longe a mais comum é [PHPUnit](https://phpunit.de/). Você pode instalá-lo baixando um [PHAR](http://php.net/manual/en/intro.phar.php) [diretamente](https://phar.phpunit.de/phpunit.phar), ou instalá-lo com composer. Como estamos usando composer para tudo mais, vamos mostrar esse método. Além disso, como o PHPUnit provavelmente não vai ser implantado para produção, podemos instalá-lo como uma dependência de desenvolvimento com o seguinte comando:

```bash
composer require --dev phpunit/phpunit
```

### 5.2 Testes são uma especificação

O papel mais importante dos testes unitários no seu código é fornecer uma especificação executável do que o código é suposto fazer. Mesmo se o código de teste estiver errado ou o código tiver bugs, o conhecimento do que o sistema é _suposto_ fazer é inestimável.

### 5.3 Escreva seus testes primeiro

Se você teve a chance de ver um conjunto de testes escrito antes do código e um escrito após o código ter sido finalizado, eles são notavelmente diferentes. Os testes "após" são muito mais preocupados com os detalhes de implementação da classe e garantindo que tenham boa cobertura de linhas, enquanto os testes "antes" são mais sobre verificar o comportamento externo desejado. Isso é realmente o que nos importa com testes unitários de qualquer maneira, é garantir que a classe exiba o comportamento certo. Testes focados na implementação na verdade tornam o refatoramento mais difícil porque eles quebram se os internos das classes mudarem e você acabou de se custar os benefícios de ocultação de informação da POO.

### 5.4 O que faz um bom teste unitário

Bons testes unitários compartilham muitas das seguintes características:

*   Rápidos - devem rodar em milissegundos.
*   Sem acesso à rede - devem ser capazes de desligar o wireless/desconectar e todos os testes ainda passarem.
*   Acesso limitado ao sistema de arquivos - isso adiciona à velocidade e flexibilidade se implantando código para outros ambientes.
*   Sem acesso ao banco de dados - evita atividades custosas de configuração e desmontagem.
*   Teste apenas uma coisa de cada vez - um teste unitário deve ter apenas um motivo para falhar.
*   Bem nomeados - veja 5.2 acima.
*   Principalmente objetos falsos - os únicos "reais" objetos em testes unitários devem ser o objeto que estamos testando e objetos de valor simples. O resto deve ser alguma forma de [test double](https://phpunit.de/manual/current/en/test-doubles.html)

Há razões para ir contra algumas dessas, mas como diretrizes gerais elas vão servir bem a você.

### 5.5 Quando testar é doloroso

> Testes unitários forçam você a sentir a dor do mau design no início - Michael Feathers

Quando você está escrevendo testes unitários, você está forçando a si mesmo a realmente usar a classe para realizar coisas. Se você escrever testes no final, ou pior, apenas jogar o código por cima do muro para QA ou quem quer que escreva testes, você não obtém nenhum feedback sobre como a classe realmente se comporta. Se estamos escrevendo testes e a classe é um saco de usar, vamos descobrir enquanto estamos escrevendo, o que é quase o momento mais barato para consertar.

Se uma classe é difícil de testar, é um defeito de design. Diferentes defeitos se manifestam de maneiras diferentes, no entanto. Se você tiver que fazer um monte de mocking, sua classe provavelmente tem muitas dependências ou seus métodos estão fazendo muito. Quanto mais configuração você tiver que fazer para cada teste, mais provável é que seus métodos estejam fazendo muito. Se você tiver que escrever cenários de teste realmente complicados para exercer o comportamento, os métodos da classe provavelmente estão fazendo muito. Se você tiver que cavar dentro de um monte de métodos privados e estado para testar coisas, talvez haja outra classe tentando sair. Testes unitários são muito bons em expor "classes iceberg" onde 80% do que a classe faz está escondido em código protegido ou privado. Eu costumava ser um grande fã de tornar o máximo possível protegido, mas agora percebi que eu estava apenas tornando minhas classes individuais responsáveis por muito, e a solução real era quebrar a classe em pedaços menores.

> **Escrito por Brian Fenton** - Brian Fenton tem sido um desenvolvedor PHP por 8 anos no Meio-Oeste e na Baía, atualmente na Thismoment. Ele se concentra em artesanato de código e princípios de design. Blog em www.brianfenton.us, Twitter em @brianfenton. Quando ele não está ocupado sendo pai, ele gosta de comida, cerveja, jogos e aprendizado.