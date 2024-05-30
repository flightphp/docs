# Flight Active Record

Aktīvā ieraksta ir datu bāzes vienības atveidojums PHP objektā. Vienkārši sakot, ja jums ir lietotāju tabula jūsu datu bāzē, jūs varat "pārvērst" rindu šajā tabulā par `User` klasi un `$user` objektu jūsu koda bāzē. Skatiet [pamata piemēru](#pamata-piem%C4%93rs).

## Pamata Piemērs

Pieņemsim, ka jums ir šāda tabula:

```sql
CREATE TABLE users (
	id INTEGER PRIMARY KEY, 
	name TEXT, 
	password TEXT 
);
```

Tagad jūs varat iestatīt jaunu klasi, lai pārstāvētu šo tabulu:

```php
/**
 * Aktīvā ieraksta klase parasti ir vienskaitlis
 * 
 * Ļoti ieteicams pievienot tabulas īpašības kā komentārus šeit
 * 
 * @property int    $id
 * @property string $name
 * @property string $password
 */ 
class User extends flight\ActiveRecord {
	public function __construct($datubāzes_savienojums)
	{
		// varat iestatīt šādi
		parent::__construct($datubāzes_savienojums, 'users');
		// vai arī šādi
		parent::__construct($datubāzes_savienojums, null, [ 'table' => 'users']);
	}
}
```

Tagad vērojiet maģiju notieko!

```php
// priekš sqlite
$datubāzes_savienojums = new PDO('sqlite:test.db'); // šis ir tikai piemēram, visticamāk izmantotu reālu datu bāzes savienojumu

// priekš mysql
$datubāzes_savienojums = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'lietotājvārds', 'parole');

// vai mysqli
$datubāzes_savienojums = new mysqli('localhost', 'lietotājvārds', 'parole', 'test_db');
// vai mysqli, izmantojot ne objektu pamatni
$datubāzes_savienojums = mysqli_connect('localhost', 'lietotājvārds', 'parole', 'test_db');

$user = new User($datubāzes_savienojums);
$user->name = 'Bobby Tables';
$user->password = password_hash('daža liela parole');
$user->insert();
// vai $user->save();

echo $user->id; // 1

$user->name = 'Joseph Mamma';
$user->password = password_hash('vēl viena forša parole!!!');
$user->insert();
// šeit nevarat izmantot $user->save(), jo tas domās, ka tas ir atjaunināšana!

echo $user->id; // 2
```

Un tik viegli bija pievienot jaunu lietotāju! Tagad, kad datu bāzē ir lietotāja rinda, kā jūs to izvelkat?
