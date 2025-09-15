# Testes Unitários no Flight PHP com PHPUnit

Este guia introduz testes unitários no Flight PHP usando [PHPUnit](https://phpunit.de/), direcionado a iniciantes que querem entender *por que* os testes unitários importam e como aplicá-los na prática. Nós nos concentraremos em testar *comportamentos* — garantindo que sua aplicação faça o que você espera, como enviar um email ou salvar um registro — em vez de cálculos triviais. Começaremos com um manipulador de rota simples [/learn/routing] e progrediremos para um controlador mais complexo [/learn/routing], incorporando [injeção de dependência](/learn/dependency-injection-container) (DI) e simulando serviços de terceiros.

## Por que Testar Unitariamente?

Testes unitários garantem que seu código se comporte conforme o esperado, capturando bugs antes que eles cheguem à produção. É especialmente valioso no Flight, onde roteamento leve e flexibilidade podem levar a interações complexas. Para desenvolvedores solo ou equipes, testes unitários atuam como uma rede de segurança, documentando o comportamento esperado e prevenindo regressões ao revisitar o código mais tarde. Eles também melhoram o design: código difícil de testar frequentemente sinaliza classes excessivamente complexas ou fortemente acopladas.

Diferente de exemplos simplistas (ex.: testando `x * y = z`), nós nos concentraremos em comportamentos do mundo real, como validar entrada, salvar dados ou disparar ações como emails. Nosso objetivo é tornar os testes acessíveis e significativos.

## Princípios Gerais de Orientação

1. **Teste Comportamento, Não Implementação**: Foque em resultados (ex.: “email enviado” ou “registro salvo”) em vez de detalhes internos. Isso torna os testes robustos contra refatorações.
2. **Pare de usar `Flight::`**: Os métodos estáticos do Flight são extremamente convenientes, mas tornam os testes difíceis. Você deve se acostumar a usar a variável `$app` de `$app = Flight::app();`. `$app` tem todos os mesmos métodos que `Flight::`. Você ainda poderá usar `$app->route()` ou `$this->app->json()` no seu controlador etc. Você também deve usar o roteador real do Flight com `$router = $app->router()` e então usar `$router->get()`, `$router->post()`, `$router->group()` etc. Veja [Routing](/learn/routing).
3. **Mantenha Testes Rápidos**: Testes rápidos incentivam execuções frequentes. Evite operações lentas como chamadas de banco de dados em testes unitários. Se você tiver um teste lento, é um sinal de que está escrevendo um teste de integração, não unitário. Testes de integração envolvem bancos de dados reais, chamadas HTTP reais, envio de emails reais etc. Eles têm seu lugar, mas são lentos e podem ser instáveis, significando que às vezes falham por razões desconhecidas.
4. **Use Nomes Descritivos**: Nomes de testes devem descrever claramente o comportamento sendo testado. Isso melhora a legibilidade e a manutenção.
5. **Evite Globals Como a Peste**: Minimize o uso de `$app->set()` e `$app->get()`, pois eles atuam como estado global, exigindo simulações em cada teste. Prefira DI ou um contêiner DI (veja [Dependency Injection Container](/learn/dependency-injection-container)). Até mesmo usar o método `$app->map()` é tecnicamente um "global" e deve ser evitado em favor da DI. Use uma biblioteca de sessão como [flightphp/session](https://github.com/flightphp/session) para que você possa simular o objeto de sessão nos seus testes. **Não** chame [`$_SESSION`](https://www.php.net/manual/en/reserved.variables.session.php) diretamente no seu código, pois isso injeta uma variável global no seu código, tornando-o difícil de testar.
6. **Use Injeção de Dependência**: Injete dependências (ex.: [`PDO`](https://www.php.net/manual/en/class.pdo.php), mailers) em controladores para isolar a lógica e simplificar simulações. Se você tiver uma classe com muitas dependências, considere refatorá-la em classes menores que tenham uma única responsabilidade seguindo os [princípios SOLID](https://en.wikipedia.org/wiki/SOLID).
7. **Simule Serviços de Terceiros**: Simule bancos de dados, clientes HTTP (cURL) ou serviços de email para evitar chamadas externas. Teste uma ou duas camadas de profundidade, mas permita que sua lógica principal execute. Por exemplo, se sua aplicação envia uma mensagem de texto, você **NÃO** quer realmente enviar uma mensagem de texto toda vez que executar seus testes, pois isso acumulará custos (e será mais lento). Em vez disso, simule o serviço de mensagem de texto e apenas verifique se seu código chamou o serviço de mensagem de texto com os parâmetros corretos.
8. **Almeje Alta Cobertura, Não Perfeição**: 100% de cobertura de linhas é bom, mas não significa que tudo no seu código esteja testado da maneira correta (vá em frente e pesquise [cobertura de ramo/caminho no PHPUnit](https://localheinz.com/articles/2023/03/22/collecting-line-branch-and-path-coverage-with-phpunit/)). Priorize comportamentos críticos (ex.: registro de usuário, respostas de API e captura de respostas falhas).
9. **Use Controladores para Rotas**: Nas definições de rotas, use controladores e não closures. O `flight\Engine $app` é injetado em todo controlador via o construtor por padrão. Nos testes, use `$app = new Flight\Engine()` para instanciar o Flight dentro de um teste, injetá-lo no seu controlador e chamar métodos diretamente (ex.: `$controller->register()`). Veja [Extending Flight](/learn/extending) e [Routing](/learn/routing).
10. **Escolha um estilo de simulação e mantenha-se nele**: O PHPUnit suporta vários estilos de simulação (ex.: prophecy, mocks internos), ou você pode usar classes anônimas que têm seus próprios benefícios como autocompletar, quebrar se você alterar a definição do método etc. Apenas seja consistente em seus testes. Veja [PHPUnit Mock Objects](https://docs.phpunit.de/en/12.3/test-doubles.html#test-doubles).
11. **Use visibilidade `protected` para métodos/propriedades que você quer testar em subclasses**: Isso permite que você os substitua em subclasses de teste sem torná-los públicos, isso é especialmente útil para mocks de classes anônimas.

## Configurando o PHPUnit

Primeiro, configure [PHPUnit](https://phpunit.de/) no seu projeto Flight PHP usando o Composer para testes fáceis. Veja o [guia de introdução ao PHPUnit](https://phpunit.readthedocs.io/en/12.3/installation.html) para mais detalhes.

1. No diretório do seu projeto, execute:
   ```bash
   composer require --dev phpunit/phpunit
   ```
   Isso instala a versão mais recente do PHPUnit como uma dependência de desenvolvimento.

2. Crie um diretório `tests` na raiz do seu projeto para arquivos de teste.

3. Adicione um script de teste ao `composer.json` para conveniência:
   ```json
   // outro conteúdo do composer.json
   "scripts": {
       "test": "phpunit --configuration phpunit.xml"
   }
   ```

4. Crie um arquivo `phpunit.xml` na raiz:
   ```xml
   <?xml version="1.0" encoding="UTF-8"?>
   <phpunit bootstrap="vendor/autoload.php">
       <testsuites>
           <testsuite name="Flight Tests">
               <directory>tests</directory>
           </testsuite>
       </testsuites>
   </phpunit>
   ```

Agora, quando seus testes estiverem prontos, você pode executar `composer test` para executar os testes.

## Testando um Manipulador de Rota Simples

Vamos começar com uma rota básica [/learn/routing] que valida o email de entrada de um usuário. Nós testaremos seu comportamento: retornando uma mensagem de sucesso para emails válidos e um erro para inválidos. Para validação de email, usamos [`filter_var`](https://www.php.net/manual/en/function.filter_var.php).

```php
// index.php
$app->route('POST /register', [ UserController::class, 'register' ]);

// UserController.php
class UserController {
	protected $app;

	public function __construct(flight\Engine $app) {
		$this->app = $app;
	}

	public function register() {
		$email = $this->app->request()->data->email;
		$responseArray = [];
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$responseArray = ['status' => 'error', 'message' => 'Invalid email'];  // Email inválido
		} else {
			$responseArray = ['status' => 'success', 'message' => 'Valid email'];  // Email válido
		}

		$this->app->json($responseArray);
	}
}
```

Para testar isso, crie um arquivo de teste. Veja [Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles) para mais sobre estruturar testes:

```php
// tests/UserControllerTest.php
use PHPUnit\Framework\TestCase;
use Flight;
use flight\Engine;

class UserControllerTest extends TestCase {

    public function testValidEmailReturnsSuccess() {
		$app = new Engine();  // Simula o objeto Engine
		$request = $app->request();
		$request->data->email = 'test@example.com';  // Simula dados POST
		$UserController = new UserController($app);
		$UserController->register($request->data->email);
        $response = $app->response()->getBody();
		$output = json_decode($response, true);
        $this->assertEquals('success', $output['status']);
        $this->assertEquals('Valid email', $output['message']);
    }

    public function testInvalidEmailReturnsError() {
		$app = new Engine();
		$request = $app->request();
		$request->data->email = 'invalid-email';  // Simula dados POST
		$UserController = new UserController($app);
		$UserController->register($request->data->email);
		$response = $app->response()->getBody();
		$output = json_decode($response, true);
		$this->assertEquals('error', $output['status']);
		$this->assertEquals('Invalid email', $output['message']);
	}
}
```

**Pontos Chave**:
- Nós simulamos dados POST usando a classe de requisição. Não use globals como `$_POST`, `$_GET` etc, pois isso complica os testes (você tem que sempre resetar esses valores ou outros testes podem falhar).
- Todos os controladores por padrão terão a instância `flight\Engine` injetada neles mesmo sem um contêiner DIC sendo configurado. Isso torna muito mais fácil testar controladores diretamente.
- Não há uso de `Flight::` de forma alguma, tornando o código mais fácil de testar.
- Testes verificam comportamento: status e mensagem corretos para emails válidos/inválidos.

Execute `composer test` para verificar se a rota se comporta conforme o esperado. Para mais sobre [requests](/learn/requests) e [responses](/learn/responses) no Flight, veja os docs relevantes.

## Usando Injeção de Dependência para Controladores Testáveis

Para cenários mais complexos, use [injeção de dependência](/learn/dependency-injection-container) (DI) para tornar controladores testáveis. Evite globals do Flight (ex.: `Flight::set()`, `Flight::map()`, `Flight::register()`) pois eles atuam como estado global, exigindo simulações para cada teste. Em vez disso, use o contêiner DI do Flight, [DICE](https://github.com/Level-2/Dice), [PHP-DI](https://php-di.org/) ou DI manual.

Vamos usar [`flight\database\PdoWrapper`](/awesome-plugins/pdo-wrapper) em vez de PDO cru. Essa wrapper é muito mais fácil de simular e testar unitariamente!

Aqui está um controlador que salva um usuário em um banco de dados e envia um email de boas-vindas:

```php
use flight\database\PdoWrapper;

class UserController {
    protected $app;
    protected $db;
    protected $mailer;

    public function __construct(Engine $app, PdoWrapper $db, MailerInterface $mailer) {
        $this->app = $app;
        $this->db = $db;
        $this->mailer = $mailer;
    }

    public function register() {
		$email = $this->app->request()->data->email;
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			// adicionando o return aqui ajuda nos testes unitários para parar a execução
			return $this->app->jsonHalt(['status' => 'error', 'message' => 'Invalid email']);
		}

		$this->db->runQuery('INSERT INTO users (email) VALUES (?)', [$email]);
		$this->mailer->sendWelcome($email);

		return $this->app->json(['status' => 'success', 'message' => 'User registered']);
    }
}
```

**Pontos Chave**:
- O controlador depende de uma instância [`PdoWrapper`](/awesome-plugins/pdo-wrapper) e de um `MailerInterface` (um serviço de email de terceiros fictício).
- Dependências são injetadas via o construtor, evitando globals.

### Testando o Controlador com Simulações

Agora, vamos testar o comportamento do `UserController`: validando emails, salvando no banco de dados e enviando emails. Nós simularemos o banco de dados e o mailer para isolar o controlador.

```php
// tests/UserControllerDICTest.php
use PHPUnit\Framework\TestCase;

class UserControllerDICTest extends TestCase {
    public function testValidEmailSavesAndSendsEmail() {

		// Às vezes misturar estilos de simulação é necessário
		// Aqui usamos o mock interno do PHPUnit para PDOStatement
		$statementMock = $this->createMock(PDOStatement::class);
		$statementMock->method('execute')->willReturn(true);
		// Usando uma classe anônima para simular PdoWrapper
        $mockDb = new class($statementMock) extends PdoWrapper {
			protected $statementMock;
			public function __construct($statementMock) {
				$this->statementMock = $statementMock;
			}

			// Quando simulamos dessa forma, não estamos realmente fazendo uma chamada de banco de dados.
			// Podemos configurar isso para alterar o mock PDOStatement para simular falhas, etc.
            public function runQuery(string $sql, array $params = []): PDOStatement {
                return $this->statementMock;
            }
        };
        $mockMailer = new class implements MailerInterface {
            public $sentEmail = null;
            public function sendWelcome($email): bool {
                $this->sentEmail = $email;
                return true;	
            }
        };
		$app = new Engine();
		$app->request()->data->email = 'test@example.com';
        $controller = new UserControllerDIC($app, $mockDb, $mockMailer);
        $controller->register();
		$response = $app->response()->getBody();
		$result = json_decode($response, true);
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('User registered', $result['message']);
        $this->assertEquals('test@example.com', $mockMailer->sentEmail);
    }

    public function testInvalidEmailSkipsSaveAndEmail() {
		 $mockDb = new class() extends PdoWrapper {
			// Um construtor vazio ignora o construtor pai
			public function __construct() {}
            public function runQuery(string $sql, array $params = []): PDOStatement {
                throw new Exception('Should not be called');  // Não deve ser chamado
            }
        };
        $mockMailer = new class implements MailerInterface {
            public $sentEmail = null;
            public function sendWelcome($email): bool {
                throw new Exception('Should not be called');  // Não deve ser chamado
            }
        };
		$app = new Engine();
		$app->request()->data->email = 'invalid-email';

		// Precisa mapear jsonHalt para evitar saída
		$app->map('jsonHalt', function($data) use ($app) {
			$app->json($data, 400);
		});
        $controller = new UserControllerDIC($app, $mockDb, $mockMailer);
        $controller->register();
        $response = $app->response()->getBody();
        $result = json_decode($response, true);
        $this->assertEquals('error', $result['status']);
        $this->assertEquals('Invalid email', $result['message']);
    }
}
```

**Pontos Chave**:
- Nós simulamos `PdoWrapper` e `MailerInterface` para evitar chamadas reais de banco de dados ou email.
- Testes verificam comportamento: emails válidos disparam inserções no banco de dados e envios de email; emails inválidos pulam ambos.
- Simule dependências de terceiros (ex.: `PdoWrapper`, `MailerInterface`), permitindo que a lógica do controlador execute.

### Simulando Demais

Cuidado para não simular demais do seu código. Deixe-me dar um exemplo abaixo sobre por que isso pode ser ruim usando nosso `UserController`. Nós vamos mudar essa verificação para um método chamado `isEmailValid` (usando `filter_var`) e as outras novas adições para um método separado chamado `registerUser`.

```php
use flight\database\PdoWrapper;
use flight\Engine;

// UserControllerDICV2.php
class UserControllerDICV2 {
	protected $app;
    protected $db;
    protected $mailer;

    public function __construct(Engine $app, PdoWrapper $db, MailerInterface $mailer) {
        $this->app = $app;
        $this->db = $db;
        $this->mailer = $mailer;
    }

    public function register() {
		$email = $this->app->request()->data->email;
		if (!$this->isEmailValid($email)) {
			// adicionando o return aqui ajuda nos testes unitários para parar a execução
			return $this->app->jsonHalt(['status' => 'error', 'message' => 'Invalid email']);
		}

		$this->registerUser($email);

		$this->app->json(['status' => 'success', 'message' => 'User registered']);
    }

	protected function isEmailValid($email) {
		return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
	}

	protected function registerUser($email) {
		$this->db->runQuery('INSERT INTO users (email) VALUES (?)', [$email]);
		$this->mailer->sendWelcome($email);
	}
}
```

E agora o teste unitário com simulação excessiva que não testa nada de fato:

```php
use PHPUnit\Framework\TestCase;

class UserControllerTest extends TestCase {
    public function testValidEmailSavesAndSendsEmail() {
		$app = new Engine();
		$app->request()->data->email = 'test@example.com';
		// estamos pulando a injeção de dependência extra porque é "fácil"
        $controller = new class($app) extends UserControllerDICV2 {
			protected $app;
			// Bypass as dependências no construtor
			public function __construct($app) {
				$this->app = $app;
			}

			// Vamos forçar isso a ser válido.
			protected function isEmailValid($email) {
				return true;  // Sempre retorna true, contornando a validação real
			}

			// Bypass as chamadas reais de DB e mailer
			protected function registerUser($email) {
				return false;
			}
		};
        $controller->register();
		$response = $app->response()->getBody();
		$result = json_decode($response, true);
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('User registered', $result['message']);
    }
}
```

Uhu! Temos testes unitários e eles estão passando! Mas espere, o que acontece se eu realmente alterar o funcionamento interno de `isEmailValid` ou `registerUser`? Meus testes ainda passarão porque eu simulei toda a funcionalidade. Deixe-me mostrar o que quero dizer.

```php
// UserControllerDICV2.php
class UserControllerDICV2 {

	// ... outros métodos ...

	protected function isEmailValid($email) {
		// Lógica alterada
		$validEmail = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
		// Agora deve ter um domínio específico
		$validDomain = strpos($email, '@example.com') !== false; 
		return $validEmail && $validDomain;
	}
}
```

Se eu executar meus testes acima, eles ainda passam! Mas porque eu não estava testando o comportamento (deixando parte do código executar), eu tenho potencialmente um bug esperando para acontecer na produção. O teste deve ser modificado para contabilizar o novo comportamento, e também o oposto quando o comportamento não é o que esperamos.

## Exemplo Completo

Você pode encontrar um exemplo completo de um projeto Flight PHP com testes unitários no GitHub: [n0nag0n/flight-unit-tests-guide](https://github.com/n0nag0n/flight-unit-tests-guide).
Para mais guias, veja [Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles) e [Troubleshooting](/learn/troubleshooting).

## Armadilhas Comuns

- **Simulação Excessiva**: Não simule todas as dependências; deixe alguma lógica (ex.: validação de controlador) executar para testar o comportamento real. Veja [Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles).
- **Estado Global**: Usar variáveis PHP globais (ex.: [`$_SESSION`](https://www.php.net/manual/en/reserved.variables.session.php), [`$_COOKIE`](https://www.php.net/manual/en/reserved.variables.cookie.php)) intensivamente torna os testes frágeis. O mesmo vale para `Flight::`. Refatore para passar dependências explicitamente.
- **Configuração Complexa**: Se a configuração de teste for complicada, sua classe pode ter muitas dependências ou responsabilidades violando os [princípios SOLID](https://en.wikipedia.org/wiki/SOLID).

## Escalando com Testes Unitários

Testes unitários brilham em projetos maiores ou ao revisitar código após meses. Eles documentam o comportamento e capturam regressões, salvando-o de re-aprender sua aplicação. Para devs solo, teste caminhos críticos (ex.: cadastro de usuário, processamento de pagamento). Para equipes, testes garantem comportamento consistente através de contribuições. Veja [Why Frameworks?](/learn/why-frameworks) para mais sobre os benefícios de usar frameworks e testes.

Contribua com suas próprias dicas de teste para o repositório de documentação do Flight PHP!

_Escrito por [n0nag0n](https://github.com/n0nag0n) 2025_