# Code actif de FlightPHP

Un enregistrement actif représente une entité de base de données sous la forme d'un objet PHP. En termes simples, si vous avez une table `utilisateurs` dans votre base de données, vous pouvez "trader" une ligne de cette table avec une classe `Utilisateur` et un objet `$utilisateur` dans votre code source. Voir [exemple de base](#exemple-de-base).

## Exemple de base

Supposons que vous avez la table suivante:

```sql
CREATE TABLE users (
	id INTEGER PRIMARY KEY, 
	name TEXT, 
	password TEXT 
);
```

Maintenant, vous pouvez configurer une nouvelle classe pour représenter cette table:

```php
/**
 * Une classe ActiveRecord est généralement au singulier
 * 
 * Il est fortement recommandé d'ajouter les propriétés de la table en commentaire ici
 * 
 * @property int    $id
 * @property string $name
 * @property string $password
 */ 
classe Utilisateur étend flight\ActiveRecord {
	public function __construct($connexion_base_de_données)
	{
		// vous pouvez le définir de cette manière
		parent::__construct($connexion_base_de_données, 'utilisateurs');
		// ou de cette manière
		parent::__construct($connexion_base_de_données, null, [ 'table' => 'users']);
	}
}
```

Maintenant regardez la magie opérer!

```php
// pour sqlite
$connexion_base_de_données = new PDO('sqlite:test.db'); // c'est juste un exemple, vous utiliseriez probablement une véritable connexion de base de données

// pour mysql
$connexion_base_de_données = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'nom_utilisateur', 'mot_de_passe');

// ou mysqli
$connexion_base_de_données = new mysqli('localhost', 'nom_utilisateur', 'mot_de_passe', 'test_db');
// ou mysqli avec une création non basée sur un objet
$connexion_base_de_données = mysqli_connect('localhost', 'nom_utilisateur', 'mot_de_passe', 'test_db');

$utilisateur = new Utilisateur($connexion_base_de_données);
$utilisateur->name = 'Bobby Tables';
$utilisateur->password = password_hash('un mot de passe sympa');
$utilisateur->insert();
// ou $utilisateur->save();

echo $utilisateur->id; // 1

$utilisateur->name = 'Joseph Mamma';
$utilisateur->password = password_hash('un autre mot de passe sympa!!!');
$utilisateur->insert();
// impossible d'utiliser $utilisateur->save() ici sinon il pensera que c'est une mise à jour!

echo $utilisateur->id; // 2
```