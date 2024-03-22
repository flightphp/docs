# Flight Active Record

Aktīvā ieraksta mērķis ir attēlot datu bāzes vienību kā PHP objektu. Vienkārši runājot, ja jums ir lietotāji tabula jūsu datu bāzē, jūs varat "tulko" rindu no šīs tabulas uz `User` klasi un `$user` objektu jūsu koda bāzē. Skatiet [pamata piemēru](#pamata-piemērs).

## Pamata Piemērs

Iedomāsimies, ka jums ir šāda tabula:

```sql
CREATE TABLE lietotāji (
	id INTEGER PRIMARY KEY, 
	vārds TEKSTS, 
	parole TEKSTS 
);
```

Tagad jūs varat izveidot jaunu klasi, lai attēlotu šo tabulu:

```php
/**
 * ActiveRecord klase parasti ir vienaskaitļa formā
 * 
 * Lielākā mērā ieteicams pievienot tabulas īpašības kā komentārus šeit
 * 
 * @property int    $id
 * @property string $name
 * @property string $password
 */ 
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		// varat iestatīt šādi
		parent::__construct($database_connection, 'lietotāji');
		// vai arī šādi
		parent::__construct($database_connection, null, [ 'table' => 'lietotāji']);
	}
}
```

Tagad vērojiet maģiju notiekam!

```php
// sqlite
$database_connection = new PDO('sqlite:test.db'); // ​​tikai piemēram, visticamāk, jūs izmantotu reālu datu bāzes savienojumu

// mysql
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'lietotājvārds', 'parole');

// vai arī mysqli
$database_connection = new mysqli('localhost', 'lietotājvārds', 'parole', 'test_db');
// vai arī mysqli ar neobjekta pamatotu izveidošanu
$database_connection = mysqli_connect('localhost', 'lietotājvārds', 'parole', 'test_db');

$user = new User($database_connection);
$user->name = 'Bobby Tables';
$user->password = password_hash('daža jauka parole');
$user->insert();
// vai arī $user->save();

echo $user->id; // 1

$user->name = 'Joseph Mamma';
$user->password = password_hash('atkal daža jauka parole!!!');
$user->insert();
// šeit nevar izmantot $user->save(), jo tas domās, ka tas ir atjaunināšana!

echo $user->id; // 2
```

Un tā bija tikai tik viegli pievienot jaunu lietotāju! Tagad, kad datu bāzē ir lietotāja rinda, kā to iegūt?

```php
$user->find(1); // atrast id = 1 datu bāzē un atgriezt to.
echo $user->name; // 'Bobby Tables'
```

Un kas, ja jūs vēlaties atrast visus lietotājus?

```php
$users = $user->findAll();
```

Kā būtu ar noteiktu nosacījumu?

```php
$users = $user->like('name', '%mamma%')->findAll();
```

Vai redzat, cik liela šī ir izklaide? Uzstādiet to un sāciet!

## Instalācija

Vienkārši instalējiet ar Composer

```php
composer require flightphp/active-record 
```

## Lietošana

To var izmantot kā neatkarīgu bibliotēku vai ar Flight PHP ietvaru. Pilnīgi jūsu izvēle.

### Neatkarīgi
Vienkārši pārliecinieties, ka jūs nododat PDO savienojumu konstruktoram.

```php
$pdo_savienojums = new PDO('sqlite:test.db'); // ​​tikai piemēram, visticamāk, jūs izmantotu reālu datu bāzes savienojumu

$Lietotājs = new User($pdo_savienojums);
```

### Flight PHP ietvars
Ja izmantojat Flight PHP ietvaru, jūs varat reģistrēt ActiveRecord klasi kā pakalpojumu (bet, godīgi sakot, jums to nav nepieciešams).

```php
Flight::register('lietotājs', 'Lietotājs', [ $pdo_savienojums ]);

// tad varat to izmantot iekš kontrolera, funkcijas, utt.

Flight::user()->find(1);
```

## CRUD funkcijas

#### `find($id = null) : boolean|ActiveRecord`

Atrast vienu ierakstu un piešķirt to pašreizējam objektam. Ja jūs padodat `$id` kādu vērtību, tā veiks meklēšanu pēc primārā atslēgas ar šo vērtību. Ja nav padots nekas, tas vienkārši atradīs pirmo ierakstu tabulā.

Papildus tam jūs varat tam pievienot citus palīgmetodus, lai vaicātu tabulu.

```php
// atrast ierakstu ar dažiem iepriekšējiem nosacījumiem
$user->notNull('password')->orderBy('id DESC')->find();

// atrast ierakstu pēc konkrēta id
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

Atrast visus ierakstus tabulā, kuru norādījāt.

```php
$user->findAll();
```

#### `isHydrated(): boolean` (v0.4.0)

Atgriež `True`, ja pašreizējais ieraksts ir iestatīts (atpauzdināts no datu bāzes).

```php
$user->find(1);
// ja tiek atrasts ieraksts ar datiem...
$user->isHydrated(); // True
```

#### `insert(): boolean|ActiveRecord`

Ievieto pašreizējo ierakstu datu bāzē.

```php
$user = new User($pdo_savienojums);
$user->name = 'piemērs';
$user->password = md5('piemērs');
$user->insert();
```

#### `update(): boolean|ActiveRecord`

Atjauno pašreizējo ierakstu datu bāzē.

```php
$user->lielāksKā('id', 0)->orderBy('id desc')->find();
$user->email = 'tests@example.com';
$user->update();
```

#### `save(): boolean|ActiveRecord`

Ievieto vai atjauno pašreizējo ierakstu datu bāzē. Ja ierakstam ir id, tas tiks atjaunināts, pretējā gadījumā tas tiks ievietots.

```php
$user = new User($pdo_savienojums);
$user->name = 'piemērs';
$user->password = md5('piemērs');
$user->save();
```

**Piezīme:** Ja ir attiecības, kas ir definētas klasē, tas rekurzīvi saglabās arī šīs attiecības, ja tās ir definētas, instancētas un ir netīras datu atjaunošanai. (v0.4.0 un vēlāk)

#### `delete(): boolean`

Dzēst pašreizējo ierakstu no datu bāzes.

```php
$user->lielāksKā('id', 0)->orderBy('id desc')->find();
$user->delete();
```

Jūs varat dzēst arī vairākus ierakstus, izpildot meklēšanu iepriekš.

```php
$user->like('name', 'B%')->delete();
```

#### `dirty(array  $dirty = [])`: `ActiveRecord`

Netīrs dati attiecas uz datiem, kas ir mainīti ierakstā.

```php
$user->lielāksKā('id', 0)->orderBy('id desc')->find();

// šajā brīdī nekas nav "netīrs".

$user->email = 'tests@example.com'; // tagad email tiek uzskatīts par "netīro", jo tas ir mainīts.
$user->update();
// tagad nav nevienam netīrama dati, jo tie tika atjaunoti un saglabāti datu bāzē

$user->password = md5('jauna parole'); // tagad šī ir netīra
$user->dirty(); // nepārtraucot neko aktualizēt, jo nav nekas uzkrāts kā netīrs.

$user->dirty([ 'name' => 'kas', 'password' => md5('atkārtota parole') ]);
$user->update(); // tiek atjaunots gan vārds, gan parole.
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

Tas ir aizstājējdarbība `dirty()` metodei. Šī ir skaidrāka par to, ko jūs darāt.

```php
$user->copyFrom([ 'name' => 'kas', 'password' => md5('atkārtota parole') ]);
$user->update(); // gan vārds, gan parole tiek atjaunoti
```

#### `isDirty(): boolean` (v0.4.0)

Atgriež `True`, ja pašreizējais ieraksts ir mainījies.

```php
$user->lielāksKā('id', 0)->orderBy('id desc')->find();
$user->email = 'tests@e-pasts.com';
$user->isDirty(); // True
```

#### `reset(bool $include_query_data = true): ActiveRecord`

Atiestata pašreizējo ierakstu uz sākotnējo stāvokli. Tas ir ļoti noderīgi, lietojot saraksta veida darbības.
Ja jūs nododat `true`, tas arī atiestatīs vaicājuma datus, kuri tika izmantoti pašreizējā objekta atrašanai (noklusējuma uzvedība).

```php
$lietotāji = $user->lielāksKā('id', 0)->orderBy('id desc')->find();
$lietotājs_uzņēmums = new LietotājsUzņēmums($pdo_savienojums);

foreach($lietotāji as $lietotājs) {
	$lietotājs_uzņēmums->reset(); // sākt ar tīru iztērēšu
	$lietotājs_uzņēmums->lietotājs_id = $lietotājs->id;
	$lietotājs_uzņēmums->uzņēmuma_id = $dažs_uzņēmuma_id;
	$lietotājs_uzņēmums->insert();
}
```

#### `getBuiltSql(): string` (v0.4.1)

Kad jūs izpildāt `find()`, `findAll()`, `insert()`, `update()` vai `save()` metodi, jūs varat iegūt SQL, kas tika izveidots, un to izmantot, lai veiktu kļūdu novēršanas nolūkos.

## SQL vaicājuma metodes
#### `select(string $field1 [, string $field2 ... ])`

Varat izvēlēties tikai dažus tabulas stabiņus, ja vēlaties (tas ir efektīvāk ar ļoti plānām tabulām ar daudz stabiņiem)

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

Jūs tehniski varat arī izvēlēties citu tabulu! Kāpēc gan nē?!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

Jūs pat varat pievienoties pie citas tabulas datu bāzē.

```php
$user->join('kontakti', 'kontakti.lietotāja_id = lietotāji.id')->find();
```

#### `where(string $where_conditions)`

Jūs varat iestatīt dažas pielāgotas where argumentus (šajā where izteiksmē nevarat iestatīt parametrus)

```php
$user->where('id=1 AND name="piemērs"')->find();
```

**Drošības piezīme** - Jūs varat būt piespiests darīt kaut ko līdzīgu `$user->where("id = '{$id}' AND name = '{$vārds}'")->find();`. LŪDZAM NEDARIET TO!!! Tas ir uzņēmīgs pret SQL injekcijas uzbrukumiem. Ir daudz rakstu tiešsaistē, lūdzu, meklējiet Google "sql inekcijas uzbrukumi php" un jūs atradīsiet daudz rakstu par šo tēmu. Pareizais veids, kā to apstrādāt ar šo bibliotēku, ir tā vietā, lai izmantotu šo `where()` metodi, jūs varētu darīt kaut ko līdzīgu `$user->eq('id', $id)->eq('name', $vārds)->find();`. Ja jums absulūti tas jādara, tad `PDO` bibliotēkā ir `$pdo->quote($mainīgo)` lai izvairītos no šāda koda pirms tā izmantošanas `where()` izteiksmē.

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

Savienojiet rezultātus pēc konkrēta nosacījuma.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

Sakārto atgriezto vaicājumu noteiktā veidā.

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

Ierobežojiet atgriezto ierakstu daudzumu. Ja otrā vienība ir dotā, tā būs nobīde, limits tikpat kā SQL.

```php
$user->orderBy('name DESC')->limit(0, 10)->findAll();
```

## WHERE nosacījumi
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

#### `$user->in(string $field, array $values) / notIn(string $field, array $values)`

Kur `field IN($value)` vai `field NOT IN($value)`

```php
$user->in('id', [1, 2])->find();
```

#### `$user->between(string $field, array $values)`

Kur `field BETWEEN $value AND $value1`

```php
$user->between('id', [1, 2])->find();
```

## Attiecības
Šajā bibliotēkā varat iestatīt vairākus attiecību veidus. Jūs varat iestatīt viens -> daudz un viens -> viens attiecības starp tabulām. Tam ir nepieciešama neliela papildu iestatīšana klasē iepriekš.

`$relations` masīva iestatīšana nav sarežģīta, bet pareiza sintakse var būt neskaidra.

```php
aizsargāts masīvs $attiecības = [
	// jūs varat nosaukt atslēgu pēc vēlmes. Galvenā ActiveRecord nosaukums, iespējams, ir labs. Piemēram, lietotājs, kontakts, klients
	'user' => [
		// obligāts
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // šis ir attiecību tips

		// obligāts
		'Daža_Klase', // tas ir "citais" ActiveRecord klase, kas būs atsauce

		// obligāts
		// atkarībā no attiecību veida
		// self::HAS_ONE = ārējais atslēga, kas norāda pievienošanu
		// self::HAS_MANY = ārējais atslēga, kas norāda pievienošanu
		// self::BELONGS_TO = lokālais atslēga, kas norāda pievienošanu
		'lokāls_vai_ārējais_atslēga',
		// tikai no žetona, tas pievienojas tikai primārās atslēgas atslēgai "cita" modelī

		// nav obligāti
		[ 'eq' => [ 'clients_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' => 5 ], // papildu nosacījumi, ko vēlaties, lai pievienotu attiecības
		// $record->eq('clients_id', 5)->select('COUNT(*) as count')->limit(5))

		// nav obligāti
		'atpakaļ_komandas_vārds' // tas ir, ja jūs vēlaties atpakaļpagriezt šo attiecību sev. Piemēram: $lietotājs->kontakts->lietotājs;
	];
]
```

```php
class User extends ActiveRecord{
	aizsargāts masīvs $attiecības = [
		'kontakti' => [ self::HAS_MANY, Kontakt::class, 'lietotāja_id' ],
		'kontakts' => [ self::HAS_ONE, Kontakt::class, 'lietotāja_id' ],
	];

	publiskā funkcija __construct($datu_bāzes_savienojums)
	{
		parent::__construct($datu_bāzes_savienojums, 'lietotāji');
	}
}

klase Kontakts atvasinās no ActiveRecord{
	aizsargāts masīvs $attiecības = [
		'lietotājs' => [ self::BELONGS_TO, Lietotājs::class, 'lietotāja_id' ],
		'lietotājs_ar_atpakaļatnosi' => [ self::BELONGS_TO, Lietotājs::class, 'lietotāja_id', [], 'kontakts' ],
	];
	publiskā funkcija __construct($datu_bāzes_savienojums)
	{
		parent::__construct($datu_bāzes_savienojums, 'kontakti');
	}
}
```

Tagad mums ir iestatītas atsauces, tās ir ļoti viegli izmantot!

```php
$lietotājs = new Lietotājs($pdo_savienojums);

// atrodiet jaunāko lietotāju.
$user->notNull('id')->orderBy('id desc')->find();

// iegūstiet kontaktus, izmantojot attiecību:
katrs($user->kontakti kā $kontakts) {
	echo $kontakts->id;
}

// vai arī varam iet otras puses.
$kontakts = new Kontakts();

// atrast vienu kontaktu
$kontakts->find();

// iegūstiet lietotāju, izmantojot attiecību:
echo $kontakts->lietotājs->vārds; // tas ir lietotāja vārds
```

Diezgan forši vai ne?

## Norādīt pielāgotus datus
Dažreiz var būt nepieciešams pievienot kaut ko unikālu jūsu ActiveRecord, piemēram, pielāgotu aprēķinu, kas varētu būt vieglāk vienkārši pievienot objektam, kas tiks nodots, teiksim, veidlapai.

#### `setCustomData(string $field, mixed $value)`
Jūs pievienojat pielāgoto datus ar `setCustomData()` metodi.
```php
$user->setCustomData('lapas_skatījumu_skaitītājs', $lapas_skatījumu_skaitītājs);
```

Un tad jūs vienkārši to atsaukaties kā parastu objekta īpašību.

```php
echo $user->lapas_skatījumu_skaitītājs;
```

## Pasākumi

Vēl viena lieliska funkcija šajā bibliotēkā ir par notikumiem. Pasākumi tiek izsaukti noteiktos laikos, pamatojoties uz noteiktiem izsaukumiem. Tie ir ļoti, ļoti noderīgi, lai iestatītu datus jums automātiski.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

Šis ir ļoti noderīgi, ja jums nepieciešams iestatīt noklusējuma savienojumu vai kaut ko tādu.

```php
// index.php vai bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// Lietotājs.php
class Lietotājs extends flight\ActiveRecord {

	protected function onConstruct(self $self, array &$config) { // neaizmirstiet & atsauci
		// jūs varētu to izdarīt, lai automātiski iestatītu savienojumu
		$config['savienojums'] = Flight::db();
		// vai arī šo
		$self->pārveidotUnSaglabātSavienojumu(Flight::db());
		
		// Jūs varat arī iestatīt tabulas nosaukumu šādi.
		$config['tabula'] = 'lietotāji';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

Šis ir vēlējies, tikai ja jums nepieciešama vaicājuma manipulācija katru reizi.

```php
class Lietotājs atvasinās no flight\ActiveRecord {
	
	publiskā funkcija __construct($datu_bāzes_savienojums)
	{
		parent::__construct($datu_bāzes_savienojums, 'lietotāji');
	}

	protected function beforeFind(self $self) {
		// vienmēr palaist id > = 0, ja tas ir jūsu dziesma
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Tas visticamāk ir noderīgāk, ja jums vienmēr ir jāizpilda kāda loģika, kad šis ieraksts tiek atsaitēts. Vai jums ir nepieciešams dekodēt kaut ko? Vai jums ir nepieciešams vienmēr palaidīt kādu pielāgotu skaitītāju vaicājumu (neefektīvi, bet tāpat)?

```php
class Lietotājs atvasinās no flight\ActiveRecord {
	
	publiskā funkcija __construct($datu_bāzes_savienojums)
	{
		parent::__construct($datu_bāzes_savienojums, 'lietotāji');
	}

	protected function afterFind(self $self) {
		// dekodēšana
		$self->noslēpums = jūsuAtšifrēšanasFunkcija($self->noslēpums, $dažs_atslēga);

		// iespējams, saglabāt kaut ko pielāgotu kā vaicājumu???
		$self->iestatītPielāgotoDatu('apmeklējumu_skaitītājs', $self->select('COUNT(*) skaits')->no('lietotāju_apmeklējumi')->eq('lietotāja_id', $self->id)['skaits']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

Tas visticamāk ir noderīgi tikai, ja jums ir jāmanipulē vaicājumi katru reizi.

```php
class Lietotājs atvasinās no flight\ActiveRecord {
	
	publiskā funkcija __construct($datu_bāzes_savienojums)
	{
		parent::__construct($datu_bāzes_savienojums, 'lietotāji');
	}

	protected function beforeFindAll(self $self) {
		// vienmēr palaist id >= 0, ja tas ir jūsu dziesma
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

Līdzīgi kā `afterFind()`, bet jums tas jādara visiem ierakstiem!

```php
class Lietotājs atvasinās no flight\ActiveRecord {
	
	publiskā funkcija __construct($datu_bāzes_savienojums)
	{
		parent::__construct($datu_bāzes_savienojums, 'lietotāji');
	}

	protected function afterFindAll(array $results) {

		katra($results kā $self) {
			// darīt kaut ko foršu kā pēcFind()
		}
	} 
}
```

#### `beforeInsert(ActiveRecord $ActiveRecord)`

Tiešām noderīgi, ja jums ir jāiestata noklusējuma vērtības katru reizi.

```php
class Lietotājs atvasinās no flight\ActiveRecord {
	
	publiskā funkcija __construct($datu_bāzes_savienojums)
	{
		parent::__construct($datu_bāzes_savienojums, 'lietotāji');
	}

	protected function beforeInsert(self $self) {
		// uzstādiet dažas uzskaitīšanas noklusējuma vērtības
		if(!$self->izveidots_datums) {
			$self->izveidots_datums = gmdate('Y-m-d');
		}

		if(!$self->parole) {
			$self->parole = md5((string) microtime(true));
		}
	} 
}
```

#### `afterInsert(ActiveRecord $ActiveRecord)`

Iespējams, jums ir lietotāja gadījums datu mainīšanai pēc ievietošanas?

```php
class Lietotājs atvasinās no flight\ActiveRecord {
	
	publiskā funkcija __construct($datu_bāzes_savienojums)
	{
		parent::__construct($datu_bāzes_savienojums, 'lietotāji');
	}

	protected function afterInsert(self $self) {
		// jūs dariet jūs
		Flight::kešatmiņa()->ievietot('pēdējo_ievietoto_id', $self->id);
		// vai kaut kas cits....
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

Tiešām noderīgi, ja jums ir jāiestata noklusējuma vērtības katru reizi, kad tiek atjaunināts.

```php
class Lietotājs atvasinās no flight\ActiveRecord {
	
	publiskā funkcija __construct($datu_bāzes_savienojums)
	{
		parent::__construct($datu_bāzes_savienojums, 'lietotāji');
	}

	protected function beforeInsert(self $self) {
		// uzstādiet dažas uzskaitīšanas noklusējuma vērtības
		if(!$self->atjaunināt_datumu) {
			$self->atjaunināt_datumu = gmdate('Y-m-d');
		}
	} 
}
```

#### `afterUpdate(ActiveRecord $ActiveRecord)`

Iespējams, jums ir lietotāja gadījums datu mainīšanai pēc atjaunināšanas?

```php
class Lietotājs atvasinās no flight\ActiveRecord {
	
	publiskā funkcija __construct($datu_bāzes_savienojums)
	{
		parent::__construct($datu_bāzes_savienojums, 'lietotāji');
	}

	protected function afterInsert(self $self) {
		// jūs dariet jūs
		Flight::kešatmiņa()->ievietot('pēdējo_atjaunināto_lietotāja_id', $self->id);
		// vai kaut kas cits....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

Tas ir noderīgi, ja vēlaties, lai notikumi notiktu gan tad, kad notiktu ievietošana, gan atjaunināšana. Es jums ietaupīšu garu izskaidrojumu, bet esmu pārliecināts, ka spējat minēt, kas tas ir.

```php
class Lietotājs atvasinās no flight\ActiveRecord {
	
	publiskā funkcija __construct($datu_bāzes_savienojums)
	{
		parent::__construct($datu_bāzes_savienojums, 'lietotāji');
	}

	protected function beforeSave(self $self) {
		$self->pēdējais_atjaunināts = gmdate('Y-m-d H:i:s');
	} 
}
```

#### `beforeDelete(ActiveRecord $ActiveRecord)/afterDelete(ActiveRecord $ActiveRecord)`

Neesmu pārliecināts, ko jūs šeit vēlaties darīt, bet šeit nav sprieduma! Dodieties uz to!

```php
class Lietotājs atvasinās no flight\ActiveRecord {
	
	publiskā funkcija __construct($datu_savienojums)
	{
		parent::__construct($datu_savienojums, 'lietotāji');
	}

	protected function beforeDelete(self $self) {
		echo 'Viņš bija drosmīgs kareivis... :aizvēršanās:'';
	} 
}
```

## Datu bāzes savienojuma pārvaldība

Kad lietojat šo bibliotēku, datu bāzes savienojumu varat iestatīt dažādos veidos. Jūs varat iestatīt savienojumu konstruktorā, varat iestatīt to, izmantojot konfigurācijas mainīgo `$config['savienojums']` vai arī to varat iestatīt, izmantojot `setDatabaseConnection()` (v0.4.1).

```php
$pdo_savienojums = new PDO('sqlite:test.db'); // piemēram
$user = new User($pdo_savienojums);
// vai
$user = new User(null, [ 'savienojums' => $pdo_savienojums ]);
// vai$user = new User();
$user->setDatabaseConnection($pdo_savienojums);
```

Ja ir nepieciešams atsvaidzināt datu bāzes savienojumu, piemēram, ja izpildāt ilglaicīgu CLI skriptu un ir nepieciešams savienojumu atkārtoti atsvaidzināt, jūs varat atkārtoti iestatīt savienojumu ar `$jūsu_ieraksts->setDatabaseConnection($pdo_savienojums)`.

## Piedalīšanās

Lūdzu dariet to. :D

## Iestatīšana

Kad jūs piedalāties, pārliecinieties, ka palaiziet `composer test-pārklājums`, lai saglabātu 100% testu pārklājumu (tas nav īsta testu pārklājuma vienība, vairāk kā integrācijas testēšana).

Pārliecinieties arī, ka izpildāt `composer skaistums` un `composer phpcs`, lai labotu jebkādas formatēšanas kļūdas.

## Licences

MIT