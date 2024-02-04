# FlightPHP Aktīvais ieraksts
Aktīvais ieraksts ir datu bāzes vienības atspoguļošana PHP objektā. Vienkārši runājot, ja jums ir lietotāji tabula datu bāzē, jūs varat "tulko" tabulā esošo rindiņu uz `User` klasi un `$user` objektu jūsu kodola bāzē. Skatiet [pamata piemēru](#pamata-piemērs).

## Pamata piemērs

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
 * ActiveRecord klase parasti ir vienskaitlis
 * 
 * Ļoti ieteicams pievienot tabulas īpašības kā komentārus šeit
 * 
 * @property int    $id
 * @property string $name
 * @property string $password
 */ 
class User extends flight\ActiveRecord {
	public function __construct($datu_bāzes_savienojums)
	{
		// to varat iestatīt šādi
		parent::__construct($datu_bāzes_savienojums, 'users');
		// vai arī šādi
		parent::__construct($datu_bāzes_savienojums, null, [ 'table' => 'users']);
	}
}
```

Tagad skatieties, kā notiek maģija!

```php
// priekš sqlite
$datu_bāzes_savienojums = new PDO('sqlite:test.db'); // tas ir tikai piemēram, jūs droši vien izmantotu reālu datu bāzes savienojumu

// priekš mysql
$datu_bāzes_savienojums = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'lietotājvārds', 'parole');

// vai mysqli
$datu_bāzes_savienojums = new mysqli('localhost', 'lietotājvārds', 'parole', 'test_db');
// vai arī mysqli ar objekta pamatotu izveidošanu
$datu_bāzes_savienojums = mysqli_connect('localhost', 'lietotājvārds', 'parole', 'test_db');

$user = new User($datu_bāzes_savienojums);
$user->name = 'John Doe';
$user->password = password_hash('kāda lieliska parole');
$user->insert();
// vai $user->save();

echo $user->id; // 1

$user->name = 'Jane Doe';
$user->password = password_hash('atkal kāda lieliska parole!!!');
$user->insert();
// šeit nevar izmantot $user->save(), jo tas domās, ka tas ir atjaunojums!

echo $user->id; // 2
```

Un tā bija tik viegli pievienot jaunu lietotāju! Tagad, kad datu bāzē ir lietotāja rinda, kā jūs to izraujat?

```php
$user->find(1); // atrod id = 1 datu bāzē un atgriež to.
echo $user->name; // 'John Doe'
```

Kas notiek, ja jūs vēlaties atrast visus lietotājus?

```php
$lietotāji = $user->findAll();
```

Kā būtu ar noteiktu nosacījumu?

```php
$lietotāji = $user->like('name', '%mamma%')->findAll();
```

Redzat, cik jautri tas ir? Uzstādiet to un sāciet lietot!

## Instalācija

Vienkārši instalējiet ar Composer

```php
composer require flightphp/active-record 
```

## Lietošana

To var izmantot kā patstāvīgu bibliotēku vai arī ar Flight PHP ietvaru. Pilnībā jūsu rokās.

### Patstāvīgi
Vienkārši pārliecinieties, ka jūs nododat PDO savienojumu konstruktoram.

```php
$pdo_savienojums = new PDO('sqlite:test.db'); // tas ir tikai piemēram, jūs droši vien izmantojat reālu datu bāzes savienojumu

$User = new User($pdo_savienojums);
```

### Flight PHP ietvars
Ja izmantojat Flight PHP ietvaru, jūs varat reģistrēt ActiveRecord klasi kā servisu (bet jums patiešām nav to jādara).

```php
Flight::register('lietotājs', 'User', [ $pdo_savienojums ]);

// tad varat to izmantot tādējādi kontrolētājā, funkcijā, utt.

Flight::user()->find(1);
```

## API atsauce
### CRUD funkcijas

#### `find($id = null) : boolean|ActiveRecord`

Atrod vienu ierakstu un piešķir to pašreizējam objektam. Ja jūs padodat kādu `$id`, tas veiks meklēšanu pēc pamatatslēgas, uz kuru norāda vērtība. Ja nav nekā padots, tas vienkārši atradīs pirmo ierakstu tabulā.

Turklāt jūs varat padot citus palīgmetodus, lai vaicātu jūsu tabulu.

```php
// atrast ierakstu ar noteiktām nosacījumiem iepriekš
$user->notNull('password')->orderBy('id DESC')->find();

// atrast ierakstu pēc noteiktā id
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

Atrast visus ierakstus tabulā, ko norādījāt.

```php
$user->findAll();
```

#### `insert(): boolean|ActiveRecord`

Ievieto pašreizējo ierakstu datu bāzē.

```php
$user = new User($pdo_savienojums);
$user->name = 'demo';
$user->password = md5('demo');
$user->insert();
```

#### `update(): boolean|ActiveRecord`

Atjaunina pašreizējo ierakstu datu bāzē.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'tests@example.com';
$user->update();
```

#### `delete(): boolean`

Dzēš pašreizējo ierakstu no datu bāzes.

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

Netīra dati attiecas uz datiem, kas ir mainīti ierakstā.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// nekas nav "netīrs" līdz šim brīdim.

$user->email = 'tests@example.com'; // tagad e-pasts tiek uzskatīts par "netīru", jo tas ir mainījies.
$user->update();
// tagad nav netīru datu, jo tas ir atjaunināts un saglabāts datu bāzē.

$user->password = password_hash()'jaunaparole'); // tagad šis ir netīrs
$user->dirty(); // neko padot nekas nenotiks, jo nekas netika uzskatīts par netīru.

$user->dirty([ 'name' => 'kas', 'password' => password_hash('cita parole') ]);
$user->update(); // gan nosaukums, gan parole tiks atjaunoti.
```

### SQL vaicājuma metodes
#### `select(string $field1 [, string $field2 ... ])`

Jūs varat atlasīt vairākas kolonnas tabulā, ja vēlaties (tas ir efektīvāk ar tiešām plašām tabulām ar daudzām kolonnām).

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

Jūs tehniski varat izvēlēties citu tabulu arī! Kāpēc gan ne?!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

Jūs pat varat pievienoties citai tabulai datu bāzē.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

Jūs varat iestatīt dažādus pasūtījuma argumentus (šajā where teikumā nevarat iestatīt parametrus)

```php
$user->where('id=1 AND name="demo"')->find();
```

**Drošības piezīme** - Jums var būt kārdinājums izdarīt kaut ko tādu kā `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. LŪDZU NEIEKŅĒPIET ŠO!!! Tas ir jutīgs pret tā sauktajiem SQL injekcijas uzbrukumiem. Ir daudz rakstu tiešsaistē, lūdzu, meklējiet Google "sql injection attacks php" un jūs atradīsiet daudz rakstu par šo tēmu. Pareizais veids, kā to apstrādāt ar šo bibliotēku, ir tāds, ka nevis šo `where()` metodi, jūs darītu kaut ko tādu kā `$user->eq('id', $id)->eq('name', $name)->find();`

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

Gatavojiet rezultātus pēc kāda īpaša nosacījuma grupām.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

Kārtot atgriezto vaicājumu noteiktā veidā.

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

Ierobežot atgriezamo ierakstu daudzumu. Ja tiek norādīts otra veselais skaitlis, tas būs nobīde, limits tikai kā SQL.

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

### WHERE nosacījumi
#### `equal(string $field, mixed $value) / eq(string $field, mixed $value)`

Kur `field = $value`

```php
$user->eq('id', 1)->find();
```

#### `notEqual(string $field, mixed $value) / ne(string $field, mixed $value)`

Kur `field <> $value`

```php
$user->ne('id', 1)->find();
```

#### `isNull(string $field)`

Kur `field IS NULL`

```php
$user->isNull('id')->find();
```
#### `isNotNull(string $field) / notNull(string $field)`

Kur `field IS NOT NULL`

```php
$user->isNotNull('id')->find();
```

#### `greaterThan(string $field, mixed $value) / gt(string $field, mixed $value)`

Kur `field > $value`

```php
$user->gt('id', 1)->find();
```

#### `lessThan(string $field, mixed $value) / lt(string $field, mixed $value)`

Kur `field < $value`

```php
$user->lt('id', 1)->find();
```
#### `greaterThanOrEqual(string $field, mixed $value) / ge(string $field, mixed $value) / gte(string $field, mixed $value)`

Kur `field >= $value`

```php
$user->ge('id', 1)->find();
```
#### `lessThanOrEqual(string $field, mixed $value) / le(string $field, mixed $value) / lte(string $field, mixed $value)`

Kur `field <= $value`

```php
$user->le('id', 1)->find();
```

#### `like(string $field, mixed $value) / notLike(string $field, mixed $value)`

Kur `field LIKE $value` vai `field NOT LIKE $value`

```php
$user->like('name', 'de')->find();
```

#### `in(string $field, array $values) / notIn(string $field, array $values)`

Kur `field IN($value)` vai `field NOT IN($values)`

```php
$user->in('id', [1, 2])->find();
```

#### `between(string $field, array $values)`

Kur `field BETWEEN $value AND $value1`

```php
$user->between('id', [1, 2])->find();
```

### Attiecības
Jūs varat iestatīt dažādas attiecības, izmantojot šo bibliotēku. Varat iestatīt viens->daudz un viens->viens attiecības starp tabulām. Tas prasa nedaudz papildu iestatījumu klases sākumā.

`$relations` masīva iestatīšana nav grūta, bet pareizo sintaksi var būt apgrūtinoši minēt.

```php
protected array $relations = [
	// varat nosaukt atslēgu kā vēlaties. `ActiveRecord` nosaukums iespējams ir labs. Piemēram: `user`, `kontakts`, `klients`
	'jebkura_active_record' => [
		// obligāti
		self::HAS_ONE, // tas ir attiecības tips

		// obligāti
		'Kāda_Klase', // tas ir "cits" ActiveRecord klase, uz ko šis norādīs

		// obligāti
		'lokālā_atslēga', // tas ir lokālā atslēga, kas norāda savienojumu.
		// tikai lai jums būtu skaidrs, tas arī pievienojas pie "cita" modeļa primārās atslēgas.

		// fakultatīvi
		[ 'eq' => 1, 'select' => 'COUNT(*) as count', 'limit' 5 ], // pielāgoti metodes, ko vēlaties izpildīt. [] ja nevēlaties neko.

		// fakultatīvi
		'atpakaļ_referenču_nosaukums' // tas ir, ja vēlaties atpakaļ atsauces šo attiecību atpakaļ uz sevi. Piemēram: $user->kontakts->lietotājs;
	];
]
```

```php
class User extends ActiveRecord{
	protected array $relations = [
		'kontakti' => [ self::HAS_MANY, Kontakt::class, 'lietotāja_id' ],
		'kontakts' => [ self::HAS_ONE, Kontakt::class, 'lietotāja_id' ],
	];

	public function __construct($datu_bāzes_savienojums)
	{
		parent::__construct($datu_bāzes_savienojums, 'lietotāji');
	}
}

class Kontakti extends ActiveRecord{
	protected array $relations = [
		'lietotājs' => [ self::BELONGS_TO, Lietotājs::class, 'lietotāja_id' ],
		'lietotājs_ar_atpakaļatradni' => [ self::BELONGS_TO, Lietotājs::class, 'lietotāja_id', [], 'kontakts' ],
	];
	public function __construct($datu_bāzes_savienojums)
	{
		parent::__construct($datu_bāzes_savienojums, 'kontakti');
	}
}
```

Tagad mums ir iestatīti ieraksti, tāpēc tos varat izmantot ļoti viegli!

```php
$user = new User($pdo_savienojums);

// atrodiet pēdējo lietotāju.
$user->notNull('id')->orderBy('id desc')->find();

// iegūstiet kontaktus, izmantojot attiecību:
foreach($user->kontakti as $kontakts) {
	echo $kontakts->id;
}

// vai arī varam iet citā virzienā.
$kontakts = new Kontakti();

// atrast```lv
# FlightPHP Aktīvais ieraksts
Aktīvais ieraksts ir datu bāzes vienības atspoguļošana PHP objektā. Citiem vārdiem sakot, ja jums ir lietotāji tabula jūsu datu bāzē, jūs varat "tulko" tabulā esošo rindu uz `Lietotājs` klasi un `$lietotājs` objektu jūsu kodola bāzē. Skatiet [pamata piemēru](#basic-example).

## Pamata Piemērs

Pieņemsim, ka jums ir šādas tabulas:
...

```