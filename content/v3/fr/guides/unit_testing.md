# Tests unitaires dans Flight PHP avec PHPUnit

Ce guide présente les tests unitaires dans Flight PHP en utilisant [PHPUnit](https://phpunit.de/), destiné aux débutants qui souhaitent comprendre *pourquoi* les tests unitaires sont importants et comment les appliquer de manière pratique. Nous nous concentrerons sur les tests de *comportement* — s'assurer que votre application fait ce que vous attendez, comme envoyer un e-mail ou sauvegarder un enregistrement — plutôt que sur des calculs triviaux. Nous commencerons par un simple [gestionnaire de routes](/learn/routing) et progresserons vers un [contrôleur](/learn/routing) plus complexe, en intégrant l'injection de dépendances [/learn/dependency-injection-container) (DI) et la simulation de services tiers.

## Pourquoi effectuer des tests unitaires ?

Les tests unitaires garantissent que votre code se comporte comme prévu, en détectant les bogues avant qu'ils n'atteignent la production. Ils sont particulièrement précieux dans Flight, où le routage léger et la flexibilité peuvent entraîner des interactions complexes. Pour les développeurs solos ou les équipes, les tests unitaires servent de filet de sécurité, documentent le comportement attendu et préviennent les régressions lorsque vous revenez sur le code plus tard. Ils améliorent également la conception : un code difficile à tester signale souvent des classes trop complexes ou trop fortement couplées.

Contrairement à des exemples simplistes (par exemple, tester `x * y = z`), nous nous concentrerons sur des comportements du monde réel, tels que la validation d'entrée, la sauvegarde de données ou le déclenchement d'actions comme les e-mails. Notre objectif est de rendre les tests accessibles et significatifs.

## Principes directeurs généraux

1. **Tester le comportement, pas l'implémentation** : Concentrez-vous sur les résultats (par exemple, « e-mail envoyé » ou « enregistrement sauvegardé ») plutôt que sur les détails internes. Cela rend les tests robustes face aux refactorisations.
2. **Arrêtez d'utiliser `Flight::`** : Les méthodes statiques de Flight sont terriblement pratiques, mais rendent les tests difficiles. Vous devriez vous habituer à utiliser la variable `$app` de `$app = Flight::app();`. `$app` possède toutes les mêmes méthodes que `Flight::`. Vous pourrez toujours utiliser `$app->route()` ou `$this->app->json()` dans votre contrôleur, etc. Vous devriez également utiliser le vrai routeur Flight avec `$router = $app->router()` et ensuite vous pouvez utiliser `$router->get()`, `$router->post()`, `$router->group()`, etc. Voir [Routage](/learn/routing).
3. **Gardez les tests rapides** : Des tests rapides encouragent une exécution fréquente. Évitez les opérations lentes comme les appels à la base de données dans les tests unitaires. Si vous avez un test lent, c'est un signe que vous écrivez un test d'intégration, pas un test unitaire. Les tests d'intégration sont ceux où vous impliquerez réellement des bases de données réelles, des appels HTTP réels, l'envoi d'e-mails réels, etc. Ils ont leur place, mais ils sont lents et peuvent être instables, ce qui signifie qu'ils échouent parfois pour une raison inconnue.
4. **Utilisez des noms descriptifs** : Les noms des tests doivent décrire clairement le comportement testé. Cela améliore la lisibilité et la maintenabilité.
5. **Évitez les globales comme la peste** : Minimisez l'utilisation de `$app->set()` et `$app->get()`, car elles agissent comme un état global, nécessitant des simulations dans chaque test. Privilégiez l'injection de dépendances ou un conteneur DI (voir [Conteneur d'injection de dépendances](/learn/dependency-injection-container)). Même l'utilisation de la méthode `$app->map()` est techniquement une « globale » et devrait être évitée au profit de l'injection de dépendances. Utilisez une bibliothèque de session comme [flightphp/session](https://github.com/flightphp/session) afin de pouvoir simuler l'objet session dans vos tests. **Ne** appelez pas [`$_SESSION`](https://www.php.net/manual/en/reserved.variables.session.php) directement dans votre code car cela injecte une variable globale dans votre code, rendant le test difficile.
6. **Utilisez l'injection de dépendances** : Injectez les dépendances (par exemple, [`PDO`](https://www.php.net/manual/en/class.pdo.php), expéditeurs d'e-mails) dans les contrôleurs pour isoler la logique et simplifier la simulation. Si vous avez une classe avec trop de dépendances, envisagez de la refactoriser en classes plus petites, chacune ayant une seule responsabilité suivant les [principes SOLID](https://en.wikipedia.org/wiki/SOLID).
7. **Simulez les services tiers** : Simulez les bases de données, les clients HTTP (cURL) ou les services d'e-mail pour éviter les appels externes. Testez une ou deux couches en profondeur, mais laissez votre logique principale s'exécuter. Par exemple, si votre application envoie un message texte, vous **NE VOULEZ PAS** vraiment envoyer un message texte à chaque exécution de vos tests car ces frais s'accumuleront (et ce sera plus lent). Au lieu de cela, simulez le service de message texte et vérifiez simplement que votre code a appelé le service de message texte avec les bons paramètres.
8. **Visez une couverture élevée, pas la perfection** : Une couverture de ligne à 100 % est bonne, mais cela ne signifie pas réellement que tout dans votre code est testé comme il le devrait (allez-y et recherchez [la couverture de branche/chemin dans PHPUnit](https://localheinz.com/articles/2023/03/22/collecting-line-branch-and-path-coverage-with-phpunit/)). Priorisez les comportements critiques (par exemple, l'inscription d'utilisateur, les réponses API et la capture des réponses échouées).
9. **Utilisez des contrôleurs pour les routes** : Dans vos définitions de routes, utilisez des contrôleurs et non des fermetures. L'instance `flight\Engine $app` est injectée dans chaque contrôleur via le constructeur par défaut. Dans les tests, utilisez `$app = new Flight\Engine()` pour instancier Flight dans un test, injectez-la dans votre contrôleur et appelez directement les méthodes (par exemple, `$controller->register()`). Voir [Extension de Flight](/learn/extending) et [Routage](/learn/routing).
10. **Choisissez un style de simulation et tenez-vous-y** : PHPUnit prend en charge plusieurs styles de simulation (par exemple, prophecy, simulations intégrées), ou vous pouvez utiliser des classes anonymes qui ont leurs propres avantages comme l'autocomplétion de code, la rupture si vous changez la définition de la méthode, etc. Soyez simplement cohérent dans vos tests. Voir [Objets simulés de PHPUnit](https://docs.phpunit.de/en/12.3/test-doubles.html#test-doubles).
11. **Utilisez la visibilité `protected` pour les méthodes/propriétés que vous voulez tester dans les sous-classes** : Cela vous permet de les surcharger dans les sous-classes de test sans les rendre publiques, ce qui est particulièrement utile pour les simulations de classes anonymes.

## Configuration de PHPUnit

Tout d'abord, configurez [PHPUnit](https://phpunit.de/) dans votre projet Flight PHP en utilisant Composer pour des tests faciles. Voir le [guide de démarrage de PHPUnit](https://phpunit.readthedocs.io/en/12.3/installation.html) pour plus de détails.

1. Dans le répertoire de votre projet, exécutez :
   ```bash
   composer require --dev phpunit/phpunit
   ```
   Cela installe la dernière version de PHPUnit en tant que dépendance de développement.

2. Créez un répertoire `tests` à la racine de votre projet pour les fichiers de test.

3. Ajoutez un script de test à `composer.json` pour plus de commodité :
   ```json
   // autre contenu de composer.json
   "scripts": {
       "test": "phpunit --configuration phpunit.xml"
   }
   ```

4. Créez un fichier `phpunit.xml` à la racine :
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

Maintenant, lorsque vos tests sont construits, vous pouvez exécuter `composer test` pour exécuter les tests.

## Test d'un gestionnaire de route simple

Commençons par une route de base [/learn/routing] qui valide l'entrée e-mail d'un utilisateur. Nous testerons son comportement : renvoyer un message de succès pour les e-mails valides et une erreur pour les invalides. Pour la validation d'e-mail, nous utilisons [`filter_var`](https://www.php.net/manual/en/function.filter-var.php).

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
			$responseArray = ['status' => 'error', 'message' => 'Invalid email'];
		} else {
			$responseArray = ['status' => 'success', 'message' => 'Valid email'];
		}

		$this->app->json($responseArray);
	}
}
```

Pour tester cela, créez un fichier de test. Voir [Tests unitaires et principes SOLID](/learn/unit-testing-and-solid-principles) pour plus d'informations sur la structuration des tests :

```php
// tests/UserControllerTest.php
use PHPUnit\Framework\TestCase;
use Flight;
use flight\Engine;

class UserControllerTest extends TestCase {

    public function testValidEmailReturnsSuccess() {
		$app = new Engine();
		$request = $app->request();
		$request->data->email = 'test@example.com'; // Simulate POST data
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
		$request->data->email = 'invalid-email'; // Simulate POST data
		$UserController = new UserController($app);
		$UserController->register($request->data->email);
		$response = $app->response()->getBody();
		$output = json_decode($response, true);
		$this->assertEquals('error', $output['status']);
		$this->assertEquals('Invalid email', $output['message']);
	}
}
```

**Points clés** :
- Nous simulons les données POST en utilisant la classe request. N'utilisez pas les globales comme `$_POST`, `$_GET`, etc. car cela complique les tests (vous devez toujours réinitialiser ces valeurs ou d'autres tests pourraient échouer).
- Tous les contrôleurs auront par défaut l'instance `flight\Engine` injectée en eux même sans conteneur DIC configuré. Cela rend beaucoup plus facile de tester directement les contrôleurs.
- Il n'y a aucune utilisation de `Flight::`, rendant le code plus facile à tester.
- Les tests vérifient le comportement : statut et message corrects pour les e-mails valides/invalides.

Exécutez `composer test` pour vérifier que la route se comporte comme prévu. Pour plus d'informations sur les [requêtes](/learn/requests) et les [réponses](/learn/responses) dans Flight, voir les documents pertinents.

## Utilisation de l'injection de dépendances pour des contrôleurs testables

Pour des scénarios plus complexes, utilisez l'injection de dépendances [/learn/dependency-injection-container) (DI) pour rendre les contrôleurs testables. Évitez les globales de Flight (par exemple, `Flight::set()`, `Flight::map()`, `Flight::register()`) car elles agissent comme un état global, nécessitant des simulations pour chaque test. Au lieu de cela, utilisez le conteneur DI de Flight, [DICE](https://github.com/Level-2/Dice), [PHP-DI](https://php-di.org/) ou une injection manuelle DI.

Utilisons [`flight\database\PdoWrapper`](/learn/pdo-wrapper) au lieu de PDO brut. Ce wrapper est beaucoup plus facile à simuler et à tester unitairement !

Voici un contrôleur qui sauvegarde un utilisateur dans une base de données et envoie un e-mail de bienvenue :

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
			// adding the return here helps unit testing to stop execution
			return $this->app->jsonHalt(['status' => 'error', 'message' => 'Invalid email']);
		}

		$this->db->runQuery('INSERT INTO users (email) VALUES (?)', [$email]);
		$this->mailer->sendWelcome($email);

		return $this->app->json(['status' => 'success', 'message' => 'User registered']);
    }
}
```

**Points clés** :
- Le contrôleur dépend d'une instance [`PdoWrapper`](/learn/pdo-wrapper) et d'un `MailerInterface` (un service d'e-mail tiers fictif).
- Les dépendances sont injectées via le constructeur, évitant les globales.

### Test du contrôleur avec des simulations

Maintenant, testons le comportement de `UserController` : validation des e-mails, sauvegarde en base de données et envoi d'e-mails. Nous simulerons la base de données et l'expéditeur pour isoler le contrôleur.

```php
// tests/UserControllerDICTest.php
use PHPUnit\Framework\TestCase;

class UserControllerDICTest extends TestCase {
    public function testValidEmailSavesAndSendsEmail() {

		// Sometimes mixing mocking styles is necessary
		// Here we use PHPUnit's built-in mock for PDOStatement
		$statementMock = $this->createMock(PDOStatement::class);
		$statementMock->method('execute')->willReturn(true);
		// Using an anonymous class to mock PdoWrapper
        $mockDb = new class($statementMock) extends PdoWrapper {
			protected $statementMock;
			public function __construct($statementMock) {
				$this->statementMock = $statementMock;
			}

			// When we mock it this way, we are not really making a database call.
			// We can further setup this to alter the PDOStatement mock to simulate failures, etc.
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
			// An empty constructor bypasses the parent constructor
			public function __construct() {}
            public function runQuery(string $sql, array $params = []): PDOStatement {
                throw new Exception('Should not be called');
            }
        };
        $mockMailer = new class implements MailerInterface {
            public $sentEmail = null;
            public function sendWelcome($email): bool {
                throw new Exception('Should not be called');
            }
        };
		$app = new Engine();
		$app->request()->data->email = 'invalid-email';

		// Need to map jsonHalt to avoid exiting
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

**Points clés** :
- Nous simulons `PdoWrapper` et `MailerInterface` pour éviter les appels réels à la base de données ou aux e-mails.
- Les tests vérifient le comportement : les e-mails valides déclenchent des insertions en base de données et des envois d'e-mails ; les e-mails invalides sautent les deux.
- Simulez les dépendances tierces (par exemple, `PdoWrapper`, `MailerInterface`), en laissant la logique du contrôleur s'exécuter.

### Simulation excessive

Faites attention à ne pas simuler trop de votre code. Laissez-moi vous donner un exemple ci-dessous sur pourquoi cela pourrait être une mauvaise chose en utilisant notre `UserController`. Nous changerons cette vérification en une méthode appelée `isEmailValid` (en utilisant `filter_var`) et les autres ajouts en une méthode séparée appelée `registerUser`.

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
			// adding the return here helps unit testing to stop execution
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

Et maintenant le test unitaire sursimulé qui ne teste en réalité rien :

```php
use PHPUnit\Framework\TestCase;

class UserControllerTest extends TestCase {
    public function testValidEmailSavesAndSendsEmail() {
		$app = new Engine();
		$app->request()->data->email = 'test@example.com';
		// we are skipping the extra dependency injection here cause it's "easy"
        $controller = new class($app) extends UserControllerDICV2 {
			protected $app;
			// Bypass the deps in the construct
			public function __construct($app) {
				$this->app = $app;
			}

			// We'll just force this to be valid.
			protected function isEmailValid($email) {
				return true; // Always return true, bypassing real validation
			}

			// Bypass the actual DB and mailer calls
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

Hourra, nous avons des tests unitaires et ils passent ! Mais attendez, que se passe-t-il si je change réellement le fonctionnement interne de `isEmailValid` ou `registerUser` ? Mes tests passeront toujours parce que j'ai simulé toute la fonctionnalité. Laissez-moi vous montrer ce que je veux dire.

```php
// UserControllerDICV2.php
class UserControllerDICV2 {

	// ... other methods ...

	protected function isEmailValid($email) {
		// Changed logic
		$validEmail = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
		// Now it should only have a specific domain
		$validDomain = strpos($email, '@example.com') !== false; 
		return $validEmail && $validDomain;
	}
}
```

Si j'exécute mes tests unitaires ci-dessus, ils passent toujours ! Mais parce que je n'ai pas testé pour le comportement (en laissant une partie du code s'exécuter réellement), j'ai potentiellement codé un bogue en attente de se produire en production. Le test devrait être modifié pour prendre en compte le nouveau comportement, et aussi l'opposé quand le comportement n'est pas ce que nous attendons.

## Exemple complet

Vous pouvez trouver un exemple complet d'un projet Flight PHP avec des tests unitaires sur GitHub : [n0nag0n/flight-unit-tests-guide](https://github.com/n0nag0n/flight-unit-tests-guide).
Pour une compréhension plus approfondie, voir [Tests unitaires et principes SOLID](/learn/unit-testing-and-solid-principles).

## Pièges courants

- **Sur-simulation** : Ne simulez pas chaque dépendance ; laissez une partie de la logique (par exemple, la validation du contrôleur) s'exécuter pour tester un comportement réel. Voir [Tests unitaires et principes SOLID](/learn/unit-testing-and-solid-principles).
- **État global** : L'utilisation intensive de variables PHP globales (par exemple, [`$_SESSION`](https://www.php.net/manual/en/reserved.variables.session.php), [`$_COOKIE`](https://www.php.net/manual/en/reserved.variables.cookie.php)) rend les tests fragiles. Idem pour `Flight::`. Refactorez pour passer explicitement les dépendances.
- **Configuration complexe** : Si la configuration des tests est fastidieuse, votre classe peut avoir trop de dépendances ou de responsabilités violant les [principes SOLID](/learn/unit-testing-and-solid-principles).

## Évolutivité avec les tests unitaires

Les tests unitaires brillent dans les projets plus grands ou lors de la reprise de code après des mois. Ils documentent le comportement et détectent les régressions, vous évitant de réapprendre votre application. Pour les devs solos, testez les chemins critiques (par exemple, inscription utilisateur, traitement des paiements). Pour les équipes, les tests assurent un comportement cohérent à travers les contributions. Voir [Pourquoi les frameworks ?](/learn/why-frameworks) pour plus d'informations sur les avantages d'utiliser des frameworks et des tests.

Contribuez vos propres conseils de test au dépôt de documentation Flight PHP !

_Écrit par [n0nag0n](https://github.com/n0nag0n) 2025_