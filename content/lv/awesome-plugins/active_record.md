# Flight Active Record

Aktīvais ieraksts ir datu bāzes entitāte, kas tiek attēlota PHP objektā. Runājot vienkārši, ja jums ir lietotāju tabula jūsu datu bāzē, jūs varat "pārvērst" šajā tabulā esošo rindu par `User` klasi un `$user` objektu jūsu kodā. Skatiet [pamata piemēru](#pamata-piem%C4%93rs).

## Pamata Piemērs

Iepriekšējam tabulai pieņemsim sekojošu struktūru:

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
 * ActiveRecord klase parasti ir vienaskaitlīga
 * 
 * Iesakām pievienot tabulas īpašības kā komentārus šeit
 * 
 * @property int    $id
 * @property string $name
 * @property string $password
 */ 
class User extends flight\ActiveRecord {
	public function __construct($datu_bāzes_savienojums)
	{
		// jūs to varat iestatīt šādi
		parent::__construct($datu_bāzes_savienojums, 'users');
		// vai šādā veidā
		parent::__construct($datu_bāzes_savienojums, null, [ 'table' => 'users']);
	}
}
```

Tagad vērojiet, kā notiek burvība!

```php
// sqlite gadījumā
$datu_bāzes_savienojums = new PDO('sqlite:test.db'); // tas ir tikai piemēram, iespējams, jūs izmantotu reālu datu bāzes savienojumu

// mysql gadījumā
$datu_bāzes_savienojums = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'lietotājvārds', 'parole');

// vai arī mysqli gadījumā
$datu_bāzes_savienojums = new mysqli('localhost', 'lietotājvārds', 'parole', 'test_db');
// vai mysqli bez objekta pamata izveidi
$datu_bāzes_savienojums = mysqli_connect('localhost', 'lietotājvārds', 'parole', 'test_db');

$user = new User($datu_bāzes_savienojums);
$user->name = 'Bobby Tables';
$user->password = password_hash('dažs jauks parole');
$user->insert();
// vai $user->save();

echo $user->id; // 1

$user->name = 'Joseph Mamma';
$user->password = password_hash('dažs jauks parole vēlreiz!!!');
$user->insert();
// šeit nevarat izmantot $user->save(), jo tas domās, ka ir atjauninājums!

echo $user->id; // 2
```