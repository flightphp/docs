# FlightPHP Aktīvais ieraksts

Aktīvais ieraksts ir datu bāzes vienības attēlošana PHP objektā. Runājot vienkārši, ja jums ir lietotāju tabula jūsu datu bāzē, jūs varat "tulkot" rindu no šīs tabulas uz `User` klasi un `$user` objektu jūsu koda bāzē. Skatieties [pamata piemēru](#pamata-piemērs).

## Pamata piemērs

Ņemsim vērā, ka jums ir šāda tabula:

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
 * Aktīvā ieraksta klase parasti ir vienaskaitļa
 * 
 * Ļoti ieteicams šeit pievienot tabulas rekvizītus kā komentārus
 * 
 * @property int    $id
 * @property string $name
 * @property string $password
 */ 
class User extends flight\ActiveRecord {
	public function __construct($datu_bāzes_savienojums)
	{
		// jūs varat to iestatīt šādi
		parent::__construct($datu_bāzes_savienojums, 'users');
		// vai šādi
		parent::__construct($datu_bāzes_savienojums, null, [ 'tabula' => 'users']);
	}
}
```

Tagad vērojiet, kas notiek!

```php
// sqlite gadījumā
$datu_bāzes_savienojums = new PDO('sqlite:test.db'); // tas ir tikai piemēram, visticamāk, ka jūs izmantosiet reālu datu bāzes savienojumu

// mysql gadījumā
$datu_bāzes_savienojums = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'lietotājvārds', 'parole');

// vai mysqli gadījumā
$datu_bāzes_savienojums = new mysqli('localhost', 'lietotājvārds', 'parole', 'test_db');
// vai mysqli ar ne-objekta pamatotu izveidi
$datu_bāzes_savienojums = mysqli_connect('localhost', 'lietotājvārds', 'parole', 'test_db');

$user = new User($datu_bāzes_savienojums);
$user->name = 'Bobby Tables';
$user->password = password_hash('daža forša parole');
$user->insert();
// vai $user->save();

echo $user->id; // 1

$user->name = 'Joseph Mamma';
$user->password = password_hash('daža forša parole atkārtoti!!!');
$user->insert();
// šeit nevar izmantot $user->save(), jo tas domās, ka tas ir atjauninājums!

echo $user->id; // 2
```

Un tā bija tik viegli pievienot jaunu lietotāju! Tagad, kad datu bāzē ir lietotāja rinda, kā jūs to izvilksiet?

```php
$user->find(1); // atrast id = 1 datu bāzē un atgriezt to.
echo $user->name; // 'Bobby Tables'
```

Un ko darīt, ja jūs vēlaties atrast visus lietotājus?

```php
$lietotāji = $user->findAll();
```

Kas attiecas uz noteiktu nosacījumu?

```php
$lietotāji = $user->like('name', '%mamma%')->findAll();
```

Redziet, cik jautri tas ir? Uzstādiet to un sāciet darbu!

## Instalācija

Vienkārši instalējiet ar Composer

```php
composer require flightphp/active-record 
```

## Lietošana

To var lietot kā atsevišķu bibliotēku vai arī ar Flight PHP ietvaru. Pilnīgi jūsu izvēlei.

### Atsevišķs izmantošanas veids
Vienkārši pārliecinieties, ka jūs nododat PDO savienojumu konstruktoram.

```php
$pdo_savienojums = new PDO('sqlite:test.db'); // tas ir tikai piemēram, visticamāk, ka jūs izmantosiet reālu datu bāzes savienojumu

$User = new User($pdo_savienojums);
```

### Flight PHP ietvars
Ja lietojat Flight PHP ietvaru, varat reģistrēt ActiveRecord klasi kā servisu (bet jums patiesībā nav jādara).

```php
Flight::register('lietotājs', 'User', [ $pdo_savienojums ]);

// tad to varat izmantot šādi kontrolētājā, funkcijā, utt.

Flight::user()->find(1);
```

## API atsauce
### CRUD funkcijas

#### `find($id = null) : boolean|ActiveRecord`

Atrast vienu ierakstu un piešķirt to pašreizējam objektam. Ja jūs padodat kādu `$id`, tas veiks meklēšanu pēc primārās atslēgas ar to vērtību. Ja nekas netiek padots, tas vienkārši atradīs pirmo ierakstu tabulā.

Papildus tam jūs varat padot tam citas palīglīdzekļus, lai pieprasītu savu tabulu.

```php
// atrast ierakstu ar dažiem nosacījumiem iepriekš
$user->notNull('password')->orderBy('id DESC')->find();

// atrast ierakstu ar konkrētu id
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

Atrast visus ierakstus tabulā, ko norādāt.

```php
$user->findAll();
```

#### `insert(): boolean|ActiveRecord`

Ievieto pašreizējo ierakstu datu baze.

```php
$user = new User($pdo_savienojums);
$user->name = 'demo';
$user->password = md5('demo');
$user->insert();
```

#### `update(): boolean|ActiveRecord`

Atjauniniet pašreizējo ierakstu datu bāzē.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `delete(): boolean`

Dzēst pašreizējo ierakstu no datu bāzes.

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

Netīra dati attiecas uz datiem, kas ir mainījušies ierakstā.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// nekas nav "netīrs" līdz šim brīdim.

$user->email = 'test@example.com'; // tagad e-pasts tiek uzskatīts par "netīru", jo tas ir mainījies.
$user->update();
// tagad nav nekādu datu, kas ir netīri, jo tie ir atjaunināti un saglabāti datu bāzē

$user->password = password_hash()'newpassword'); // tagad tas ir netīrs
$user->dirty(); // neko padot nekas, tiks notīrīti visi netīrie ieraksti.
$user->update(); // nekas netiks atjaunots, jo nekas netika saglabāts kā netīrs.

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // abi nosaukumi un parole ir atjaunoti.
```

### SQL vaicājuma metodes
#### `select(string $field1 [, string $field2 ... ])`

Jūs varat atlasīt tikai dažas kolonnas tabulā, ja vēlaties (tas ir efektīvāk uz ļoti plašām tabulām ar daudzām kolonnām).

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

Jūs tehniski varat izvēlēties arī citu tabulu! Kāpēc gan nē?!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

Jūs pat varat pievienoties citai tabulai datu bāzē.

```php
$user->join('saziņa', 'saziņa.lietotājs_id = lietotāji.id')->find();
```

#### `where(string $where_conditions)`

Jūs varat iestatīt dažus pielāgotos where argumentus (jūs nevarat iestatīt parametrus šajā where paziņojumā).

```php
$user->where('id=1 AND name="demo"')->find();
```

**Siksnības piezīme** - Jūs varētu būt kārdinājis darīt kaut ko tādu kā `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. LŪDZAM, NEDARIET TO!!! Šis ir uzņēmīgs pret datu ievietošanas uzbrukumiem. Ir daudz rakstu tiešsaistē, lūdzu, meklējiet internetā par "sql ievietošanas uzbrukumi php" un jūs atradīsiet daudz rakstu par šo tēmu. Pareizais veids, kā rīkoties ar šo bibliotēku, ir tā vietā, lai to dariet ar `where()` metodi, jūs darītu kaut ko līdzīgu `$user->eq('id', $id)->eq('name', $name)->find();`

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

Cērtiet savi rezultāti pēc konkrētas sausmas.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

Kārtot atgriezto vaicājumu konkrētu veidu.

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

Ierobežojiet atgriezto ierakstu skaitu. Ja tiek dots otrs int, tas būs nobīde, limitēt tāpat kā SQL.

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

### WHERE nosacījumi
#### `equal(string $field, mixed $value) / eq(string $field, mixed $value)`

Kur `laukums = $vērtība`

```php
$user->eq('id', 1)->find();
```

#### `notEqual(string $field, mixed $value) / ne(string $field, mixed $value)`

Kur `laukums <> $vērtība`

```php
$user->ne('id', 1)->find();
```

#### `isNull(string $field)`

Kur `laukums IR NULL`

```php
$user->isNull('id')->find();
```
#### `isNotNull(string $field) / notNull(string $field)`

Kur `laukums NAV NULL`

```php
$user->isNotNull('id')->find();
```

#### `greaterThan(string $field, mixed $value) / gt(string $field, mixed $value)`

Kur `laukums > $vērtība`

```php
$user->gt('id', 1)->find();
```

#### `lessThan(string $field, mixed $value) / lt(string $field, mixed $value)`

Kur `laukums < $vērtība`

```php
$user->lt('id', 1)->find();
```
#### `greaterThanOrEqual(string $field, mixed $value) / ge(string $field, mixed $value) / gte(string $field, mixed $value)`

Kur `laukums >= $vērtība`

```php
$user->ge('id', 1)->find();
```
#### `lessThanOrEqual(string $field, mixed $value) / le(string $field, mixed $value) / lte(string $field, mixed $value)`

Kur `laukums <= $vērtība`

```php
$user->le('id', 1)->find();
```

#### `like(string $field, mixed $value) / notLike(string $field, mixed $value)`

Kur `laukums LĪDZINĀS $vērtība` vai `laukums NAV LĪDZINĀTS $vērtība`

```php
$user->like('name', 'de')->find();
```

#### `in(string $field, array $values) / notIn(string $field, array $values)`

Kur `laukums IN($vērtība)` vai `laukums NAV IN($vērtība)`

```php
$user->in('id', [1, 2])->find();
```

#### `between(string $field, array $values)`

Kur `laukums STARP $vērtība UN $vērtība1`

```php
$user->between('id', [1, 2])->find();
```

### Attiecības
Šajā bibliotēkā varat uzstādīt vairāku veidu attiecības. Jūs varat iestatīt viens->daudz un viens->viens attiecības starp tabulām. Tas prasa nedaudz papildu iestatījumu šai klasei iepriekš.

$relations iestatīšana nav grūta, bet pareizās sintakses minēšana var būt mulsinoša.

```php
protected array $relations = [
	// jūs varat nosaukt atslēgu tā, kā vēlaties. Aktīvais ieraksts ir ļoti labs nosaukums. Piemēram, lietotājs, kontakts, klients
	'jebkādas_active_record' => [
		// nepieciešams
		self::HAS_ONE, // tas ir attiecību veids

		// nepieciešams
		'Kāda_Klase', // šis ir "cits" ActiveRecord klase, uz kuru tas attiecas

		// nepieciešams
		'vietējais_atslēgas', // tas ir lokālā atslēga, kas saistās pievienošanu.
		// tikai FYI, tas arī pievienojas tikai pie "citas" modeļa primārās atslēgas

		// neobligāti
		[ 'eq' => 1, 'select' => 'COUNT(*) as count', 'limit' 5 ], // pielāgotas metodes, ko jūs vēlaties izpildīt. [] ja jums nevajag neko.

		// neobligāti
		'atpakaļ_atsauces_nosaukums' // tas ir, ja vēlaties atpakaļ atsauces attiecību atpakaļ sev, piemēram, $user->kontakts->lietotājs;
	];
]
```

```php
class User extends ActiveRecord{
	protected array $relations = [
		'kontakti' => [ self::HAS_MANY, Contact::class, 'lietotājs_id' ],
		'kontakts' => [ self::HAS_ONE, Contact::class, 'lietotājs_id' ],
	];

	public function __construct($datu_bāzes_savienojums)
	{
		parent::__construct($datu_bāzes_savienojums, 'lietotāji');
	}
}

class Contact extends ActiveRecord{
	protected array $relations = [
		'lietotājs' => [ self::BELONGS_TO, User::class, 'lietotājs_id' ],
		'lietotājs_ar_atpakaļ_atsauces' => [ self::BELONGS_TO, User::class, 'lietotājs_id', [], 'kontakts' ],
	];
	public function __construct($datu_bāzes_savienojums)
	{
parent::__construct($datu_bāzes_savienojums, 'kontakti');
	}
}
```

Tagad mums ir iestatītas atsauces, tāpēc varam tos ļoti viegli izmantot!

```php
$user = new User($pdo_savienojums);

// atrast jaunāko lietotāju.
$user->notNull('id')->orderBy('id desc')->find();

// iegūstiet kontaktus, izmantojot attiecību:
foreach($user->kontakti as $kontakts) {
	echo $kontakts->id;
}

// vai varam doties citā virzienā.
$kontakts = new Kontakt();

// atrast vienu kontaktu
$kontakts->find();

// iegūstiet lietotāju, izmantojot attiecību:
echo $kontakts->lietotājs->name; // tas ir lietotāja vārds
```

Ļoti forši, vai ne?

### Iestatīšana pielāgotiem datiem
Dažreiz var būt nepieciešams pievienot kaut ko īpašu savam ActiveRecord, piemēram, pielāgotu izrēķinu, kas varētu būt vieglāk pievienot objektam, ko pēc tam nododiet, teiksim, veidlapai.

#### `setCustomData(string $field, mixed $value)`
Pielāgoto datus pievienojat ar `setCustomData()` metodi.
```php
$user->setCustomData('page_view_count', $page_view_count);
```

Un tad vienkārši norādiet to kā normālu objekta rekvizītu.

```php
echo $user->page_view_count;
```

### Notikumi

Vēl viens super foršs šīs bibliotēkas elements ir par notikumiem. Notikumi tiek izsaukti noteiktos laikos, pamatojoties uz konkrētiem metodēm, ko jūs izsaucat. Tie ir ļoti noderīgi dati automātiski uzstādīšanai jums.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

Tas ir ļoti noderīgi, ja jums ir jāiestata noklusējuma savienojums vai kaut kas tamlīdzīgs.

```php
// index.php vai bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

	protected function onConstruct(self $self, array &$config) { // nedrīkst aizmirst & atsauce
		// jūs varētu to darīt, lai automātiski iestatītu savienojumu
		$config['connection'] = Flight::db();
		// vai tā
		$self->transformAndPersistConnection(Flight::db());
		
		// Jūs varat arī šādi iestatīt tabulas nosaukumu.
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

Tas visticamāk būs noderīgi, ja jums ir jāveic vaicājuma manipulācija katru reizi.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($datu_bāzes_savienojums)
	{
		parent::__construct($datu_bāzes_savienojums, 'lietotāji');
	}

	protected function beforeFind(self $self) {
		// vienmēr palaist id >= 0, ja tas ir jūsu gaume
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Šis visticamāk būs noderīgi, ja jums vienmēr ir nepieciešams palaist kādu loģiku katru reizi, kad šis ieraksts tiek iegūts. Vai jums ir nepieciešams šifrēt kaut ko? Vai jums ir nepieciešams palaist pielāgotu skaitījumu vaicājumu katru reizi (nav efektīvs, bet nē).

```php
class User extends flight\ActiveRecord {
	
	public function __construct($datu_bāzes_savienojums)
	{
		parent::__construct($datu_bāzes_savienojums, 'lietotāji');
	}

	protected function afterFind(self $self) {
		// atšifrēšana
		$self->noslēpums = tavaAtšifrēšanasFunkcija($self->noslēpums, kāds_atslēga);

		// varbūt saglabāt kaut ko pielāgotu, piemēram, vaicājumu???
		$self->setCustomData('skatījumu_skaits', $self->select('COUNT(*) count')->from('lietotāja_skatījumi')->eq('lietotāja_id', $self->id)['count']; 
	} 
}
```