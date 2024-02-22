# FlightPHP Darbības ieraksts

Darbības ieraksts nozīmē datu bāzes vienības atainošanu kā PHP objektu. Vienkārši izsakoties, ja jums ir lietotāju tabula jūsu datu bāzē, jūs varat "pārvērst" tabulas rindu `User` klases un `$user` objektā jūsu koda bāzē. Skatīt [pamata piemēru](#pamata-piemērs).

## Pamata Piemērs

Tātad, pieņemsim, ka jums ir šāda tabula:

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
 * Darbības ieraksta klase parasti ir vienā skaitnī.
 * 
 * Ieteicams šeit pievienot tabulas īpašības kā komentārus
 * 
 * @property int    $id
 * @property string $name
 * @property string $password
 */ 
class User extends flight\ActiveRecord {
	public function __construct($datu_bāzes_savienojums)
	{
		// varat iestatīt to šādi
		parent::__construct($datu_bāzes_savienojums, 'lietotāji');
		// vai arī šādi
		parent::__construct($datu_bāzes_savienojums, null, [ 'tabula' => 'lietotāji']);
	}
}
```

Tagad vērojiet, kā notiek maģija!

```php
// izmantojot sqlite
$datu_bāzes_savienojums = new PDO('sqlite:test.db'); // tas ir tikai piemēram, jūs visdrīzāk izmantotu reālu datu bāzes savienojumu

// izmantojot mysql
$datu_bāzes_savienojums = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'lietotājvārds', 'parole');

// vai arī mysqli
$datu_bāzes_savienojums = new mysqli('localhost', 'lietotājvārds', 'parole', 'test_db');
// vai arī mysqli ar neobjekta pamatā izveidi
$datu_bāzes_savienojums = mysqli_connect('localhost', 'lietotājvārds', 'parole', 'test_db');

$user = new User($datu_bāzes_savienojums);
$user->name = 'Bobby Tables';
$user->password = password_hash('some cool password');
$user->insert();
// vai $user->save();

echo $user->id; // 1

$user->name = 'Joseph Mamma';
$user->password = password_hash('some cool password again!!!');
$user->insert();
// šeit nevar izmantot $user->save(), jo tas domās, ka tas ir atjauninājums!

echo $user->id; // 2
```

Un tā arī vienkārši tika pievienots jauns lietotājs! Tagad, kad datu bāzē ir lietotāja rinda, kā jūs to izvilksiet?

```php
$user->find(1); // atrast id = 1 datu bāzē un to atgriezt.
echo $user->name; // 'Bobby Tables'
```

Un ja jūs vēlaties atrast visus lietotājus?

```php
$lietotāji = $user->findAll();
```

Kā ir ar kādu nosacījumu?

```php
$users = $user->like('name', '%mamma%')->findAll();
```

Redziet, cik jautri tas ir? Instalēsim to un sāksim!

## Instalācija

Vienkārši instalējiet ar Composer

```php
composer require flightphp/active-record 
```

## Lietošana

To var izmantot kā neatkarīgu bibliotēku vai arī ar Flight PHP Framework. Pilnīgi jūsu ziņā.

### Neatkarīgs
Vienkārši nodrošiniet PDO savienojumu konstruktoram.

```php
$pdo_savienojums = new PDO('sqlite:test.db'); // tas ir tikai piemēram, jūs visdrīzāk izmantosiet reālu datu bāzes savienojumu

$User = new User($pdo_savienojums);
```

### Flight PHP Framework
Ja izmantojat Flight PHP Framework, jūs varat reģistrēt ActiveRecord klasi kā servisu (bet jums godīgi nav tas jādara).

```php
Flight::register('user', 'User', [ $pdo_savienojums ]);

// tad to varat izmantot šādi kontrolētājā, funkcijā, utt.

Flight::user()->find(1);
```

## API Atsauce
### CRUD funkcijas

#### `find($id = null) : boolean|ActiveRecord`

Atrast vienu ierakstu un piešķirt to pašreizējam objektam. Ja jūs norādāt kādu `$id`, tas veiks meklēšanu pēc primārās atslēgas ar šo vērtību. Ja nekas netiek norādīts, tas vienkārši atrod pirmo ierakstu tabulā.

Papildus jūs varat norādīt citus palīgmetodus, lai vaicātu jūsu tabulā.

```php
// atrast ierakstu ar dažiem nosacījumiem iepriekš
$user->notNull('password')->orderBy('id DESC')->find();

// atrast ierakstu pēc konkrēta id
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

Atrod visus ierakstus tabulā, ko norādāt.

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

Atjauno pašreizējo ierakstu datu bāzē.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `delete(): boolean`

Dzēš pašreizējo ierakstu no datu bāzes.

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

Jūs varat arī dzēst vairākus ierakstus, izpildot meklēšanu iepriekš.

```php
$user->like('name', 'Bob%')->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

"Sliktie" dati attiecas uz datiem, kas ir mainīti ierakstā.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// šajā punktā nav nekas "slikti".

$user->email = 'test@example.com'; // tagad e-pasts tiek uzskatīts par "sliktu", jo tas ir mainīts.
$user->update();
// tagad nav "sliktu" datu, jo tie ir atjaunināti un saglabāti datu bāzē.

$user->password = password_hash()'newpassword'); // tagad tas ir slikts
$user->dirty(); // nedzēlīšu neko tīru, jo nav noteikts nekāds "slikts".
$user->update(); // nekas netiks atjaunināts, jo nekas nav uzskatīts par "sliktu".

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // gan vārds, gan parole tiks atjauninātas.
```

#### `reset(bool $include_query_data = true): ActiveRecord`

Atiestata pašreizējo ierakstu uz sākotnējo stāvokli. Tas ir ļoti noderīgi, ja izmantojat cikla tipa darbības.
Ja jūs nododat `true`, tas atiestata arī vaicājuma datus, kas tika izmantoti pašreizējā objekta atrašanai (noklusējuma darbība).

```php
$users = $user->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($pdo_savienojums);

foreach($users as $user) {
	$user_company->reset(); // sāciet ar tīru līniju
	$user_company->user_id = $user->id;
	$user_company->company_id = $some_company_id;
	$user_company->insert();
}
```

### SQL vaicājuma metodes
#### `select(string $field1 [, string $field2 ... ])`

Jūs varat izvēlēties tikai dažus tabulas stabiņus, ja vēlaties (tas ir efektīvāks, ja ir ļoti plašas tabulas ar daudziem stabiņiem)

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

Jūs tehniski varat izvēlēties citu tabulu arī! Kāpēc gan nē?!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

Jūs pat varat pievienoties citai tabulai datu bāzē.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

Jūs varat iestatīt dažus pielāgotus where argumentus (jūs nevarat iestatīt parametrus šajā where norādījumā)

```php
$user->where('id=1 AND name="demo"')->find();
```

**Drošības piezīme** - Jūs varētu būt kārdināts izdarīt kaut ko tādu kā `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. LŪDZAM, TO NEDARIET!!! Tas ir ievainojams pret tā saukto SQL injekcijas uzbrukumiem. Ir daudz rakstu tiešsaistē, lūdzu, meklējiet tīmeklī "sql injection attacks php" un jūs atradīsiet daudz rakstu par šo tēmu. Pareizais veids, kā rīkoties ar šo bibliotēku, ir nevis šis `where()` metods, bet gan kaut kas līdzīgs `$user->eq('id', $id)->eq('name', $name)->find();`

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

Grupējiet savus rezultātus pēc konkrēta nosacījuma.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

Kārtot atgriezto vaicājumu noteiktā secībā.

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

Ierobežojiet atgriezto ierakstu daudzumu. Ja tiek norādīts otrais integers, tas būs nobīde, limits tikpat kā SQL.

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

Kur `field IN($value)` vai `field NOT IN($value)`

```php
$user->in('id', [1, 2])->find();
```

#### `between(string $field, array $values)`

Kur `field BETWEEN $value AND $value1`

```php
$user->between('id', [1, 2])->find();
```

### Attiecības
Šajā bibliotēkā varat iestatīt vairāku veidu attiecības. Jūs varat noteikt viens → daudzi un viens → viens attiecības starp tabulām. Lai to paveiktu, ir nepieciešama nedaudz papildu iestatīšana klātienē.

`$relations` masīva iestatīšana nav grūta, bet pareizā sintakse var būt samulsinoša.

```php
protected array $relations = [
	// varat nosaukt atslēgu jebko, ko vēlētos. ActiveRecord nosaukums ir iespējams labais. Piemēram: lietotājs, kontakts, klients
	'whatever_active_record' => [
		// obligāts
		self::HAS_ONE, // šis ir attiecību veids

		// obligāti
		'Some_Class', // šī ir "cita" ActiveRecord klase, uz kuru tas norādīs

		// obligāti
		'lokalizācijas_atslēga', // tas ir vietējais atslēgas, kurš norāda uz pievienošanos.
		// tikai FYI, tas arī pievienojas pie "cita" modeļa primārās atslēgas

		// neobligāti
		[ 'eq' => 1, 'select' => 'COUNT(*) as count', 'limit' 5 ], // pielāgoti metodes, kuras jūs vēlaties izpildīt. [] ja jums nav vajadzīgs nekas.

		// neobligāti
		'atpakaļ_atsauces_vārds' // tas ir, ja jūs vēlaties atzīmēt šo attiecību atpakaļ uz sevi, piemēram, $user->kontakts->lietotājs;
	];
]
```

```php
class User extends ActiveRecord{
	protected array $relations = [
		'contacts' => [ self::HAS_MANY, Contact::class, 'user_id' ],
		'contact' => [ self::HAS_ONE, Contact::class, 'user_id' ],
	];

	public function __construct($datu_bāzes_savienojums)
	{
		parent::__construct($datu_bāzes_savienojums, 'lietotāji');
	}
}

class Contact extends ActiveRecord{
	protected array $relations = [
	'lietotāji' => [ self::HAS_MANY, Kontakts::class, 'lietotājs_id' ],
	'kontakts' => [ self::HAS_ONE, Kontakts::class, 'lietotājs_id' ],
];

public function __construct($datu_bāzes_savienojums)
{
	parent::__construct($datu_bāzes_savienojums, 'kontakti');
}
```

Tagad atsauces ir iestatītas, lai tās būtu ļoti viegli izmantot!

```php
$user = new User($pdo_savienojums);

// atrast visjaunāko lietotāju.
$user->notNull('id')->orderBy('id desc')->find();

// iegūt kontaktus, izmantojot attiecību:
foreach($user->kontakti as $kontakts) {
	echo $kontakts->id;
}

// vai arī mēs varam iet otrajā virzienā.
$kontakts = new Contact();

// atrast vienu kontaktu
$kontakts->find();

// iegūt lietotāju, izmantojot attiecību:
echo $kontakts->lietotājs->name; // tas ir lietotāja vārds
```

Diezgan forši, vai ne?

### Iestatīt pielāgotus datus
Dažreiz jums var būt nepieciešams pievienot kaut ko unikālu jūsu ActiveRecord, piemēram, pielāgotu aprēķinu, kas varētu būt vieglāk piesaistīts objektam, ko pēc tam nodotu, teiksim, veidlapai.

#### `setCustomData(string $field, mixed $value)`
Jūs pievienojat pielāgoto datus ar `setCustomData()` metodi.
```php
$user->setCustomData('lapas_apmeklējumu_skaits', $lapas_apmeklējumu_skaits);
```

Un tad vienkārši to norādiet, it kā tā būtu parasta objekta īpašība.

```php
echo $user->lapas_apmeklējumu_skaits;
```

### Notikumi

Vēl viena lieliska funkcija par šo bibliotēku ir notikumi. Notikumi tiek izraisīti noteiktos laikos atkarībā no konkrētajiem sauktajiem metodēm. Tie ļoti ļoti palīdz iestatīt datus jums automātiski.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

Tas ir tiešām noderīgi, ja jums ir nepieciešams iestatīt noklusēto savienojumu vai kaut ko līdzīgu.

```php
// index.php vai bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

	protected function onConstruct(self $self, array &$config) { // neaizmirstiet & atsauci
		// jūs varētu to izdarīt, lai automātiski iestatītu savienojumu
		$config['savienojums'] = Flight::db();
		// vai arī šo
		$self->transformAndPersistConnection(Flight::db());
		
		// Jūs varat arī iestatīt tabulas nosaukumu šajā veidā.
		$config['tabula'] = 'lietotāji';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

Iespējams, ka tas noder tikai tad, ja jums ir jāveic jautājumu manipulācija katru reizi.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($datu_bāzes_savienojums)
	{
		parent::__construct($datu_bāzes_savienojums, 'lietotāji');
	}

	protected function beforeFind(self $self) {
		// vienmēr palaist id >= 0, ja tā ir jūsu gaume
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Šis, visticamāk, ir noderīgāks, ja vienmēr jums ir nepieciešams izpildīt kādu loģiku katru reizi, kad šis ieraksts tiek iegūts. Vai jums ir nepieciešams dešifrēt kaut ko? Vai jums ir nepieciešams izpildīt kādu pielāgotu skaitīšanas vaicājumu katru reizi (neefektīvs, bet nu jau).

```php
class User extends flight\ActiveRecord {
	
	public function __construct($datu_bāzes_savienojums)
	{
		parent::__construct($datu_bāzes_savienojums, 'lietotāji');
	}

	protected function afterFind(self $self) {
		// atšifrējot kaut ko
		$self->noslēpums = jūsuAtšifrēšanasFunkcija($self->noslēpums, $kāds_atslēga);

		// iespējams saglabāšana kautkas pielāgots, piemēram, vaicājums???
		$self->setCustomData('skatīšanās_skaits', $self->select('COUNT(*) count')->from('lietotāja_skatījumi')->eq('lietotājs_id', $self->id)['skaita']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

Tas ir derīgs, ja katru reizi jums ir jāveic vaicājuma manipulācija.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($datu_bāzes_savienojums)
	{
		parent::__construct($datu_bāzes_savienojums, 'lietotāji');
	}

	protected function beforeFindAll(self $self) {
		// vienmēr palaist id >= 0, ja tā ir jūsu gaume
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

Līdzīgi kā `afterFind()`, bet jūsu var to darīt visiem ierakstiem!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($datu_bāzes_savienojums)
	{
		parent::__construct($datu_bāzes_savienojums, 'lietotāji');
	}

	protected function afterFindAll(array $results) {

		foreach($results as $self) {
			// dariet kaut ko foršu kā afterFind()
		}
	} 
}
```

#### `beforeInsert(ActiveRecord $ActiveRecord)`

Tiešām noderīgi, ja jums ir nepieciešamas noklusējuma vērtības katru reizi.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($datu_bāzes_savienojums)
	{
		parent::__construct($datu_bāzes_savienojums, 'lietotāji');
	}

	protected function beforeInsert(self $self) {
		// iestatiet dažas standarta vērtības
		if(!$self->izveides_datums) {
			$self->izveides_datums = gmdate('Y-m-d');
		}

		if(!$self->parole) {
			$self->parole = password_hash((string) microtime(true));
		}
	} 
}
```

#### `afterInsert(ActiveRecord $ActiveRecord)`

Piedāvājat gadījumu, kad dati tiek mainīti pēc ievietošanas?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($datu_bāzes_savienojums)
	{
		parent::__construct($datu_bāzes_savienojums, 'lietotāji');
	}

	protected function afterInsert(self $self) {
		// dariet ko vēlaties
		Flight::kešatmiņa()->set('jaunākais_ievietošanas_id', $self->id);
		// vai cits....
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

Tiešām noderīgi, ja jums ir nepieciešamas noklusējuma vērtības katru reizi atjaunināšanai.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($datu_bāzes_savienojums)
	{
		parent::__construct($datu_bāzes_savienojums, 'lietotāji');
	}

	protected function beforeInsert(self $self) {
		// iestatiet dažas standarta vērtības
		if(!$self->atjaunināšanas_datums) {
			$self->atjaunināšanas_datums = gmdate('Y-m-d');
		}
	} 
}
```

#### `afterUpdate(ActiveRecord $ActiveRecord)`

Iespējams, ka ir lietas, ko vēlaties mainīt pēc atjaunināšanas?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($datu_bāzes_savienojums)
	{
		parent::__construct($datu_bāzes_savienojums, 'lietotāji');
	}

	protected function afterInsert(self $self) {
		// dariet ko vēlaties
		Flight::kešatmiņa()->set('pēdējais_atjauninātais_lietotāja_id', $self->id);
		// vai cits....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

Tas ir noderīgi, ja vēlaties, lai notikumi notiktu gan, kad notiek ievietošana, gan atjaunināšana. Iespējams, jūs varat minēt, kas tas ir.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($datu_bāzes_savienojums)
	{
		parent::__construct($datu_bāzes_savienojums, 'lietotāji');
	}

	protected function beforeSave(self $self) {
		$self->pēdējais_atjaunināšanās = gmdate('Y-m-d H:i:s');
	} 
}
```

#### `beforeDelete(ActiveRecord $ActiveRecord)/afterDelete(ActiveRecord $ActiveRecord)`

Nav skaidrs, ko jūs šeit vēlaties darīt, bet nav nekādu sūdzību! Varat to izdarīt!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($datu_bāzes_savienojums)
	{
		parent::__construct($datu_bāzes_savienojums, 'lietotāji');
	}

	protected function beforeDelete(self $self) {
		echo 'Viņš bija drosmīgs karavīrs... :cry-face:';
	} 
}
```

## Contributing

Lūdzu, dariet to.

### Setup

Kad jūs dodat savu ieguldījumu, pārliecinieties, ka izpildāt `composer test-coverage`, lai uzturētu 100% testu pārklājumu (tas nav īsts testu pārklājums, drīzāk integrācijas testi).

Lūdzu, pārliecinieties, ka pēc iespējas izpildīt `composer beautify` un `composer phpcs`, lai labotu jebkādus linting kļūdas.

## License

MIT