# Flight Active Record 

Aktīvais ieraksts ir datubāzes entītijas kartēšana uz PHP objektu. Vienkārši sakot, ja jūsu datubāzē ir lietotāju tabula, varat "pārtulkot" šīs tabulas rindu uz `User` klasi un `$user` objektu savā koda bāzē. Skatiet [vienkāršo piemēru](#vienkāršais-piemērs).

Klikšķiniet [šeit](https://github.com/flightphp/active-record), lai skatītu GitHub repozitoriju.

## Vienkāršais piemērs

Pieņemsim, ka jums ir šāda tabula:

```sql
CREATE TABLE users (
	id INTEGER PRIMARY KEY, 
	name TEXT, 
	password TEXT 
);
```

Tagad varat izveidot jaunu klasi, lai attēlotu šo tabulu:

```php
/**
 * ActiveRecord klase parasti ir vienskaitlī
 * 
 * Ir ļoti ieteicams šeit kā komentārus pievienot tabulas īpašības
 * 
 * @property int    $id
 * @property string $name
 * @property string $password
 */ 
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		// varat iestatīt šādi
		parent::__construct($database_connection, 'users');
		// vai šādi
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

Tagad skatieties, kā notiek maģija!

```php
// sqlite
$database_connection = new PDO('sqlite:test.db'); // tas ir tikai piemērs, jūs droši vien izmantotu īstu datubāzes savienojumu

// mysql
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// vai mysqli
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// vai mysqli, ja izveide nav balstīta uz objektu
$database_connection = mysqli_connect('localhost', 'username', 'password', 'test_db');

$user = new User($database_connection);
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

Un tik vienkārši bija pievienot jaunu lietotāju! Tagad, kad datubāzē ir lietotāja rinda, kā to izvilkt?

```php
$user->find(1); // atrod id = 1 datubāzē un atgriež to.
echo $user->name; // 'Bobby Tables'
```

Un ja vēlaties atrast visus lietotājus?

```php
$users = $user->findAll();
```

Bet ja ar noteiktu nosacījumu?

```php
$users = $user->like('name', '%mamma%')->findAll();
```

Redzat, cik tas ir jautri? Instalēsim to un sāksim!

## Instalēšana

Vienkārši instalējiet ar Composer

```php
composer require flightphp/active-record 
```

## Lietošana

To var izmantot kā atsevišķu bibliotēku vai kopā ar Flight PHP ietvaru. Pilnībā jūsu ziņā.

### Atsevišķi (Standalone)

Vienkārši pārliecinieties, ka konstruktoram nododat PDO savienojumu.

```php
$pdo_connection = new PDO('sqlite:test.db'); // tas ir tikai piemērs, jūs droši vien izmantotu īstu datubāzes savienojumu

$User = new User($pdo_connection);
```

> Nevēlaties vienmēr iestatīt datubāzes savienojumu konstruktorā? Skatiet [Datubāzes savienojuma pārvaldība](#datubāzes-savienojuma-pārvaldība)!

### Reģistrēšana kā metode Flight

Ja izmantojat Flight PHP ietvaru, varat reģistrēt ActiveRecord klasi kā pakalpojumu, bet patiesībā jums tas nav jādara.

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// tad to varat izmantot šādi kontrollerī, funkcijā utt.

Flight::user()->find(1);
```

## `runway` metodes

[runway](/awesome-plugins/runway) ir Flight CLI rīks, kam ir pielāgota komanda šai bibliotēkai.

```bash
# Lietošana
php runway make:record database_table_name [class_name]

# Piemērs
php runway make:record users
```

Tas izveidos jaunu klasi direktorijā `app/records/` kā `UserRecord.php` ar šādu saturu:

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * ActiveRecord klase lietotāju tabulai.
 * @link https://docs.flightphp.com/en/v3/awesome-plugins/active-record
 *
 * @property int $id
 * @property string $username
 * @property string $email
 * @property string $password_hash
 * @property string $created_dt
 */
class UserRecord extends \flight\ActiveRecord
{
    /**
     * @var array $relations Iestata modeļa attiecības
     *   https://docs.flightphp.com/en/v3/awesome-plugins/active-record#relationships
     */
    protected array $relations = [
		// 'relation_name' => [ self::HAS_MANY, 'RelatedClass', 'foreign_key' ],
	];

    /**
     * Konstruktors
     * @param mixed $databaseConnection Savienojums ar datubāzi
     */
    public function __construct($databaseConnection)
    {
        parent::__construct($databaseConnection, 'users');
    }
}
```

## CRUD funkcijas

#### `find($id = null) : boolean|ActiveRecord`

Atrod vienu ierakstu un piešķir to pašreizējam objektam. Ja nododat kaut kādu `$id`, tas veiks meklēšanu pēc primārās atslēgas ar šo vērtību. Ja nekas netiek nodots, tas vienkārši atradīs pirmo ierakstu tabulā.

Turklāt varat nodot citas palīgmetodes, lai vaicātu tabulu.

```php
// atrast ierakstu ar iepriekšējiem nosacījumiem
$user->notNull('password')->orderBy('id DESC')->find();

// atrast ierakstu pēc konkrēta id
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

Atrod visus ierakstus tabulā, kuru norādāt.

```php
$user->findAll();
```

#### `first(): ActiveRecord` (v0.8.0)

Atrod pirmo ierakstu, kas atbilst jūsu nosacījumiem. Ja neesat iestatījis kārtošanu, tas kārto pēc primārās atslēgas augošā secībā. Ja nekas neatbilst, jūs saņemat ierakstu atpakaļ bez datu ielādes (unhydrated), tāpēc pārbaudiet `isHydrated()`, ja neesat pārliecināts, ka kaut kas atgriezies.

```php
$user->eq('status', 'active')->first();
```

#### `last(): ActiveRecord` (v0.8.0)

Tāpat kā `first()`, bet kārto pēc primārās atslēgas dilstošā secībā. Noderīgi vaicājumiem "dod man jaunāko".

```php
$user->eq('status', 'active')->last();
```

#### `count(): int` (v0.8.0)

Saskaita rindas, kas atbilst jūsu pašreizējiem nosacījumiem. Ja vaicājumā ir `groupBy()`, `count()` to apzināti ignorē. Viena skalāra vērtība nevar attēlot vienu rindu katrā grupā.

```php
$user->count();
$user->eq('status', 'active')->count();
```

#### `exists(): bool` (v0.8.0)

Atgriež `true`, ja kāds ieraksts atbilst jūsu nosacījumiem. Tas iekšēji izpilda lētu `SELECT 1 ... LIMIT 1`.

```php
$user->eq('name', 'Bobby')->exists(); // true
```

#### `pluck(string $column): array` (v0.8.0)

Atgriež plakanu masīvu ar vērtībām no vienas kolonnas, nevis ielādē veselu objektu kopu. Apvienojiet ar `distinct()`, lai iegūtu unikālas vērtības.

```php
$user->pluck('name'); // [ 'Bobby', 'Joseph' ]
$user->distinct()->pluck('status'); // [ 'active', 'inactive' ]
```

#### `ids(): array` (v0.8.0)

Īsceļš `pluck()` izsaukumam uz primāro atslēgu.

```php
$user->gt('id', 0)->ids(); // [ 1, 2, 3 ]
```

#### `isHydrated(): boolean` (v0.4.0)

Atgriež `true`, ja pašreizējais ieraksts ir ielādēts (saņemts no datubāzes).

```php
$user->find(1);
// ja ieraksts tika atrasts ar datiem...
$user->isHydrated(); // true
```

#### `insert(): boolean|ActiveRecord`

Ievieto pašreizējo ierakstu datubāzē.

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->insert();
```

##### Teksta primārās atslēgas

Ja jums ir teksta primārā atslēga (piemēram, UUID), varat iestatīt primārās atslēgas vērtību pirms ievietošanas vienā no diviem veidiem.

```php
$user = new User($pdo_connection, [ 'primaryKey' => 'uuid' ]);
$user->uuid = 'some-uuid';
$user->name = 'demo';
$user->password = md5('demo');
$user->insert(); // vai $user->save();
```

vai arī varat ļaut primārajai atslēgai tikt automātiski ģenerētai caur notikumiem.

```php
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users', [ 'primaryKey' => 'uuid' ]);
		// primāro atslēgu varat iestatīt arī šādi, nevis izmantojot iepriekšējo masīvu.
		$this->primaryKey = 'uuid';
	}

	protected function beforeInsert(self $self) {
		$self->uuid = uniqid(); // vai kā citādi jums nepieciešams ģenerēt unikālos id
	}
}
```

Ja pirms ievietošanas neiestatāt primāro atslēgu, tā tiks iestatīta uz `rowid`, un datubāze to ģenerēs jums, bet tā netiks saglabāta, jo šis lauks, iespējams, neeksistē jūsu tabulā. Tāpēc ieteicams izmantot notikumu, lai tas tiktu automātiski apstrādāts jūsu vietā.

#### `update(): boolean|ActiveRecord`

Atjaunina pašreizējo ierakstu datubāzē.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `updateAttribute(string $name, mixed $value): ActiveRecord` (v0.8.0)

Atjaunina vienu kolonnu ielādētajā ierakstā un saglabā to. Tas ir īsceļš priekš `$user->dirty([ 'name' => $value ])->update()`. Šim nolūkam jums ir nepieciešams ielādēts ieraksts.

```php
$user->find(1);
$user->updateAttribute('name', 'New Name');
```

#### `save(): boolean|ActiveRecord`

Ievieto vai atjaunina pašreizējo ierakstu datubāzē. Ja ierakstam ir id, tas tiks atjaunināts, pretējā gadījumā tas tiks ievietots.

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->save();
```

**Piezīme:** Ja klasē ir definētas attiecības, tiks rekursīvi saglabātas arī šīs attiecības, ja tās ir definētas, instantētas un tām ir netīri (dirty) dati, ko atjaunināt. (v0.4.0 un jaunāk)

#### `delete(): boolean`

Izdzēš pašreizējo ierakstu no datubāzes.

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

Varat arī izdzēst vairākus ierakstus, vispirms izpildot meklēšanu.

```php
$user->like('name', 'Bob%')->delete();
```

#### `updateAll(array $attributes, bool $allowEmptyConditions = false): int` (v0.8.0)

Atjaunina katru ierakstu, kas atbilst jūsu nosacījumiem, vienā paziņojumā. Nekādi ieraksti netiek ielādēti un nekādi notikumi netiek izpildīti, tieši tāpēc tas ir ātri. Atgriež skarto rindu skaitu.

Tas atsakās darboties bez WHERE nosacījumiem, ja vien otrajam argumentam nenododat `true`. Jūsu nākotnes "es" pateicas.

```php
$user->eq('status', 'inactive')->updateAll([ 'status' => 'active' ]);

// jā, jūs patiešām vēlaties atjaunināt katru tabulas rindu
$user->updateAll([ 'status' => 'active' ], true);
```

#### `deleteAll(bool $allowEmptyConditions = false): int` (v0.8.0)

Izdzēš katru ierakstu, kas atbilst jūsu nosacījumiem, vienā paziņojumā. Tas pats, kas `updateAll()`: bez ielādes, bez notikumiem, un tam ir nepieciešami WHERE nosacījumi, ja vien nenododat `true`. Atgriež izdzēsto rindu skaitu. Lietojiet piesardzīgi!

```php
$user->eq('status', 'deleted')->deleteAll();
```

#### `dirty(array $dirty = []): ActiveRecord`

Netīrie dati attiecas uz datiem, kas ir mainīti ierakstā.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// nekas nav "netīrs" līdz šim brīdim.

$user->email = 'test@example.com'; // tagad e-pasts tiek uzskatīts par "netīru", jo tas ir mainīts.
$user->update();
// tagad nav nekādu netīro datu, jo tie ir atjaunināti un saglabāti datubāzē

$user->password = password_hash()'newpassword'); // tagad tas ir netīrs
$user->dirty(); // neko nenododot, tiks notīrīti visi netīrie ieraksti.
$user->update(); // nekas netiks atjaunināts, jo nekas netika ierakstīts kā netīrs.

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // gan vārds, gan parole tiek atjaunināti.
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

Šis ir `dirty()` metodes aizstājējs. Tas ir nedaudz skaidrāk redzams, ko jūs darāt.

```php
$user->copyFrom([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // gan vārds, gan parole tiek atjaunināti.
```

#### `isDirty(): boolean` (v0.4.0)

Atgriež `true`, ja pašreizējais ieraksts ir mainīts.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@email.com';
$user->isDirty(); // true
```

#### `reset(bool $include_query_data = true): ActiveRecord`

Atiestata pašreizējo ierakstu uz tā sākotnējo stāvokli. Tas ir ļoti noderīgi cilpu veida darbībās. Ja nododat `true`, tas arī atiestatīs vaicājuma datus, kas tika izmantoti pašreizējā objekta atrašanai (noklusējuma uzvedība).

```php
$users = $user->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($pdo_connection);

foreach($users as $user) {
	$user_company->reset(); // sāciet ar tukšu lapu
	$user_company->user_id = $user->id;
	$user_company->company_id = $some_company_id;
	$user_company->insert();
}
```

#### `getBuiltSql(): string` (v0.4.1)

Pēc tam, kad esat izpildījis `find()`, `findAll()`, `insert()`, `update()` vai `save()` metodi, varat iegūt izveidoto SQL un izmantot to atkļūdošanas nolūkos.

## Transakcijas

Nepieciešams veikt vairākas rakstīšanas darbības, kurām visām jāizdodas kopā? Ietiniet tās `transaction()` (v0.8.0). Nododiet izsaucamo (callable), un ieraksts tiek padots kā arguments. Ja izsaucamais met izņēmumu, viss tiek atcelts (rollback), un izņēmums tiek izmests jums atpakaļ. Pretējā gadījumā tas veic commit un atgriež to, ko izsaucamais atgrieza.

```php
$user->transaction(function ($user) {
	$user->name = 'Bobby Tables';
	$user->password = password_hash('correct horse battery staple');
	$user->insert();

	$user->email = 'bobby@example.com';
	$user->update();
	// commit notiek šeit, ja nekas netika izmests
});
```

Ligzdotas transakcijas netiek atbalstītas (nav savepoint), tāpēc turiet tās plakanas.

## SQL vaicājuma metodes
#### `select(string $field1 [, string $field2 ... ])`

Varat atlasīt tikai dažas kolonnas no tabulas, ja vēlaties (tas ir veiktspējīgāk ļoti platās tabulās ar daudzām kolonnām).

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

Tehniski varat izvēlēties arī citu tabulu! Kāpēc gan ne?!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

Varat pat pievienoties citai tabulai datubāzē.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

Varat iestatīt pielāgotus where argumentus (šajā where paziņojumā nevar iestatīt parametrus).

```php
$user->where('id=1 AND name="demo"')->find();
```

**Drošības piezīme** — Jums var rasties kārdinājums darīt kaut ko līdzīgu `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. LŪDZU, NEDARIET TO!!! Tas ir uzņēmīgs pret to, ko sauc par SQL injekcijas uzbrukumiem. Tiešsaistē ir daudz rakstu, lūdzu, meklējiet "sql injection attacks php", un jūs atradīsiet daudz rakstu par šo tēmu. Pareizais veids, kā to apstrādāt ar šo bibliotēku, ir tā vietā, lai izmantotu šo `where()` metodi, darīt kaut ko līdzīgu `$user->eq('id', $id)->eq('name', $name)->find();`. Ja jums tas noteikti ir jādara, `PDO` bibliotēkā ir `$pdo->quote($var)`, lai to apstrādātu (escape) jūsu vietā. Tikai pēc tam, kad izmantojat `quote()`, varat to izmantot `where()` paziņojumā.

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

Grupējiet rezultātus pēc noteikta nosacījuma.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

Kārtojiet atgriezto vaicājumu noteiktā veidā.

```php
$user->orderBy('name DESC')->find();
```

#### `orderByColumn(string $column, string $direction = 'ASC')` (v0.7.2)

`order()` un `orderBy()` pieņem neapstrādātus SQL fragmentus, kas ir labi, ja iepriekš iestatāt `'name DESC'`. Ja kolonnas nosaukums nāk no lietotāja ievades (piemēram, kārtojamas tabulas galvene), tā vietā izmantojiet `orderByColumn()`. Ir atļauti tikai vienkārši kolonnu nosaukumi un `table.column` ceļi, un virzienam jābūt `ASC` vai `DESC`, tāpēc nav ko injicēt.

```php
// $sortColumn nāk no pieprasījuma
$user->orderByColumn($sortColumn, 'DESC')->findAll();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

Ierobežo atgriezto ierakstu skaitu. Ja ir dots otrs veselais skaitlis, tas būs nobīde (offset) un limits, tāpat kā SQL.

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

#### `distinct()` (v0.8.0)

Pievieno `DISTINCT` jūsu nākamajam vaicājumam. Tas darbojas ar parasto select un ar `pluck()`. `count()` to ignorē, jo `DISTINCT` vienā agregāta rindā neko nedod.

```php
$user->distinct()->pluck('status'); // [ 'active', 'inactive' ]
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

### OR nosacījumi

Ir iespējams ietīt savus nosacījumus OR paziņojumā. To var izdarīt ar `startWrap()` un `endWrap()` metodēm vai aizpildot 3. parametru nosacījumam pēc lauka un vērtības.

```php
// 1. metode
$user->eq('id', 1)->startWrap()->eq('name', 'demo')->or()->eq('name', 'test')->endWrap('OR')->find();
// Tas tiks novērtēts kā `id = 1 AND (name = 'demo' OR name = 'test')`

// 2. metode
$user->eq('id', 1)->eq('name', 'demo', 'OR')->find();
// Tas tiks novērtēts kā `id = 1 OR name = 'demo'`
```

## Tvērumi (Scopes)

Tvērumi (v0.8.0) ir atkārtoti lietojamas vaicājumu ķēdes, kas definētas kā parastas instances metodes jūsu klasē un atgriež `$this`. Kad esat to uzrakstījis, tas tiek ķēdēts tāpat kā jebkura cita vaicājuma metode.

```php
class User extends flight\ActiveRecord {

	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	public function active(): self
	{
		return $this->eq('status', 'active');
	}

	public function recent(int $days = 7): self
	{
		return $this->ge('created_at', date('Y-m-d', strtotime("-{$days} days")));
	}
}

// un tagad jūsu vaicājumi izskatās kā teikumi
(new User($pdo_connection))->active()->recent(30)->findAll();
```

Varat arī izsaukt tvērumu pēc nosaukuma ar `scope()`, kas ir ērti, ja tvēruma nosaukums nāk no citas jūsu koda vietas. Tas met `BadMethodCallException`, ja metode neeksistē.

```php
$user->scope('active')->findAll();
$user->scope('recent', 30)->findAll();
```

## Attiecības (Relationships)
Izmantojot šo bibliotēku, varat iestatīt vairāku veidu attiecības. Varat iestatīt viens->daudzi un viens->viens attiecības starp tabulām. Tas prasa nedaudz papildu iestatīšanas klasē iepriekš.

`$relations` masīva iestatīšana nav grūta, bet pareizās sintakses uzminēšana var būt mulsinoša.

```php
protected array $relations = [
	// atslēgai varat dot jebkādu nosaukumu. Droši vien labi ir ActiveRecord nosaukums. Piem., user, contact, client
	'user' => [
		// obligāti
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // šis ir attiecību tips

		// obligāti
		'Some_Class', // šī ir "cita" ActiveRecord klase, uz kuru šī norāda

		// obligāti
		// atkarībā no attiecību veida
		// self::HAS_ONE = ārējā atslēga, kas norāda uz savienojumu
		// self::HAS_MANY = ārējā atslēga, kas norāda uz savienojumu
		// self::BELONGS_TO = lokālā atslēga, kas norāda uz savienojumu
		'local_or_foreign_key',
		// tikai informācijai, tas savienojas ar "citas" modeļa primāro atslēgu

		// neobligāti
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // papildu nosacījumi, ko vēlaties, savienojot attiecību
		// $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

		// neobligāti
		'back_reference_name' // tas ir, ja vēlaties atpakaļnorādi uz šo attiecību pret sevi. Piem., $user->contact->user;
	]
];
```

```php
class User extends ActiveRecord{
	protected array $relations = [
		'contacts' => [ self::HAS_MANY, Contact::class, 'user_id' ],
		'contact' => [ self::HAS_ONE, Contact::class, 'user_id' ],
	];

	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}
}

class Contact extends ActiveRecord{
	protected array $relations = [
		'user' => [ self::BELONGS_TO, User::class, 'user_id' ],
		'user_with_backref' => [ self::BELONGS_TO, User::class, 'user_id', [], 'contact' ],
	];
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'contacts');
	}
}
```

Tagad mums ir iestatītas atsauces, tāpēc mēs tās varam izmantot ļoti viegli!

```php
$user = new User($pdo_connection);

// atrast jaunāko lietotāju.
$user->notNull('id')->orderBy('id desc')->find();

// iegūt kontaktus, izmantojot attiecību:
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// vai mēs varam iet pretējā virzienā.
$contact = new Contact();

// atrast vienu kontaktu
$contact->find();

// iegūt lietotāju, izmantojot attiecību:
echo $contact->user->name; // šis ir lietotājvārds
```

Diezgan forši, vai ne?

### Agrīna ielāde (Eager Loading)

#### Pārskats
Agrīna ielāde atrisina N+1 vaicājumu problēmu, ielādējot attiecības iepriekš. Tā vietā, lai izpildītu atsevišķu vaicājumu katram ieraksta attiecību kopumam, agrīna ielāde iegūst visus saistītos datus tikai vienā papildu vaicājumā par katru attiecību.

> **Piezīme:** Agrīna ielāde ir pieejama tikai no v0.7.0 un jaunāk.

#### Pamata lietošana
Izmantojiet `with()` metodi, lai norādītu, kuras attiecības ielādēt iepriekš:
```php
// Ielādē lietotājus ar viņu kontaktiem 2 vaicājumos N+1 vietā
$users = $user->with('contacts')->findAll();
foreach ($users as $u) {
    foreach ($u->contacts as $contact) {
        echo $contact->email; // Nav papildu vaicājuma!
    }
}
```

#### Vairākas attiecības
Ielādējiet vairākas attiecības vienlaikus:
```php
$users = $user->with(['contacts', 'profile', 'settings'])->findAll();
```

#### Attiecību veidi

##### HAS_MANY
```php
// Agrīni ielādē visus kontaktus katram lietotājam
$users = $user->with('contacts')->findAll();
foreach ($users as $u) {
    // $u->contacts jau ir ielādēts kā masīvs
    foreach ($u->contacts as $contact) {
        echo $contact->email;
    }
}
```
##### HAS_ONE
```php
// Agrīni ielādē vienu kontaktu katram lietotājam
$users = $user->with('contact')->findAll();
foreach ($users as $u) {
    // $u->contact jau ir ielādēts kā objekts
    echo $u->contact->email;
}
```

##### BELONGS_TO
```php
// Agrīni ielādē vecāku lietotājus visiem kontaktiem
$contacts = $contact->with('user')->findAll();
foreach ($contacts as $c) {
    // $c->user jau ir ielādēts
    echo $c->user->name;
}
```
##### Ar find()
Agrīna ielāde darbojas gan ar 
findAll()
, gan 
find()
:

```php
$user = $user->with('contacts')->find(1);
// Lietotājs un visi viņa kontakti tiek ielādēti 2 vaicājumos
```
#### Veiktspējas priekšrocības
Bez agrīnas ielādes (N+1 problēma):
```php
$users = $user->findAll(); // 1 vaicājums
foreach ($users as $u) {
    $contacts = $u->contacts; // N vaicājumi (pa vienam katram lietotājam!)
}
// Kopā: 1 + N vaicājumi
```

Ar agrīno ielādi:

```php
$users = $user->with('contacts')->findAll(); // 2 vaicājumi kopā
foreach ($users as $u) {
    $contacts = $u->contacts; // 0 papildu vaicājumu!
}
// Kopā: 2 vaicājumi (1 lietotājiem + 1 visiem kontaktiem)
```
10 lietotājiem tas samazina vaicājumu skaitu no 11 uz 2 — par 82% mazāk!

#### Svarīgas piezīmes
- Agrīna ielāde ir pilnībā neobligāta — slinkā ielāde (lazy loading) joprojām darbojas kā iepriekš
- Jau ielādētas attiecības tiek automātiski izlaistas
- Atpakaļnorādes darbojas ar agrīno ielādi
- Attiecību atzvanīšanas (callbacks) tiek ievērotas agrīnās ielādes laikā

#### Ierobežojumi
- Ligzdota agrīna ielāde (piem., 
with(['contacts.addresses'])
) pašlaik netiek atbalstīta
- Agrīnās ielādes ierobežojumi, izmantojot closures, šajā versijā netiek atbalstīti

## Pielāgotu datu iestatīšana
Dažreiz jums var būt nepieciešams pievienot kaut ko unikālu savam ActiveRecord, piemēram, pielāgotu aprēķinu, ko varētu būt vieglāk vienkārši pievienot objektam, kas pēc tam tiktu nodots, teiksim, veidnei.

#### `setCustomData(string $field, mixed $value)`
Jūs pievienojat pielāgotos datus ar `setCustomData()` metodi.
```php
$user->setCustomData('page_view_count', $page_view_count);
```

Un pēc tam jūs vienkārši atsaucaties uz to kā parastu objekta īpašību.

```php
echo $user->page_view_count;
```

## Laikspiedogi (Timestamps)

Ja jūsu tabulā ir kolonnas `created_at` un `updated_at`, varat ļaut bibliotēkai tās aizpildīt jūsu vietā (v0.8.0). Iestatiet `protected bool $timestamps = true;` savā klasē, un tā iestatīs `created_at` un `updated_at`, kad veiksiet ievietošanu, un `updated_at`, kad veiksiet atjaunināšanu. Formāts ir `Y-m-d H:i:s`. Ja kādu no kolonnām iestatāt pats, bibliotēka atstās jūsu vērtību neskartu.

```php
class User extends flight\ActiveRecord {
	protected bool $timestamps = true;

	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}
}
```

Jūsu tabulai patiešām ir nepieciešamas šīs kolonnas, pretējā gadījumā ievietošana un atjaunināšana neizdosies.

## Notikumi (Events)

Vēl viena super forša lieta šajā bibliotēkā ir notikumi. Notikumi tiek aktivizēti noteiktos laikos, pamatojoties uz noteiktām metodēm, kuras izsaucat. Tie ir ļoti, ļoti noderīgi, lai datus iestatītu automātiski jūsu vietā.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

Tas ir ļoti noderīgi, ja nepieciešams iestatīt noklusējuma savienojumu vai kaut ko līdzīgu.

```php
// index.php vai bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

	protected function onConstruct(self $self, array &$config) { // neaizmirstiet & atsauci
		// jūs varat to izdarīt, lai automātiski iestatītu savienojumu
		$config['connection'] = Flight::db();
		// vai šādi
		$self->transformAndPersistConnection(Flight::db());
		
		// Tabulas nosaukumu varat iestatīt arī šādi.
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

Tas, iespējams, ir noderīgi tikai tad, ja jums katru reizi nepieciešama vaicājuma manipulācija.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFind(self $self) {
		// vienmēr izpildiet id >= 0, ja tas ir jūsu gaumē
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Šis, iespējams, ir noderīgāks, ja jums vienmēr ir nepieciešams izpildīt kādu loģiku katru reizi, kad šis ieraksts tiek ielādēts. Vai jums ir nepieciešams atšifrēt kaut ko? Vai jums ir nepieciešams katru reizi izpildīt pielāgotu skaitīšanas vaicājumu (nav veiktspējīgi, bet vienalga)?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFind(self $self) {
		// kaut ko atšifrēt
		$self->secret = yourDecryptFunction($self->secret, $some_key);

		// varbūt saglabāt kaut ko pielāgotu, piemēram, vaicājumu???
		$self->setCustomData('view_count', $self->select('COUNT(*) count')->from('user_views')->eq('user_id', $self->id)['count']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

Tas, iespējams, ir noderīgi tikai tad, ja jums katru reizi nepieciešama vaicājuma manipulācija.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFindAll(self $self) {
		// vienmēr izpildiet id >= 0, ja tas ir jūsu gaumē
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

Līdzīgi kā `afterFind()`, bet jūs to varat izdarīt ar visiem ierakstiem!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFindAll(array $results) {

		foreach($results as $self) {
			// dariet kaut ko foršu, piemēram, afterFind()
		}
	} 
}
```

#### `beforeInsert(ActiveRecord $ActiveRecord)`

Ļoti noderīgi, ja katru reizi ir nepieciešams iestatīt dažas noklusējuma vērtības.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// iestatiet dažas saprātīgas noklusējuma vērtības
		if(!$self->created_date) {
			$self->created_date = gmdate('Y-m-d');
		}

		if(!$self->password) {
			$self->password = password_hash((string) microtime(true));
		}
	} 
}
```

#### `afterInsert(ActiveRecord $ActiveRecord)`

Varbūt jums ir gadījums, kad pēc ievietošanas ir jāmaina dati?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// dariet, ko vēlaties
		Flight::cache()->set('most_recent_insert_id', $self->id);
		// vai jebko citu....
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

Ļoti noderīgi, ja katru reizi atjaunināšanas laikā ir nepieciešams iestatīt dažas noklusējuma vērtības.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// iestatiet dažas saprātīgas noklusējuma vērtības
		if(!$self->updated_date) {
			$self->updated_date = gmdate('Y-m-d');
		}
	} 
}
```

#### `afterUpdate(ActiveRecord $ActiveRecord)`

Varbūt jums ir gadījums, kad pēc atjaunināšanas ir jāmaina dati?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// dariet, ko vēlaties
		Flight::cache()->set('most_recently_updated_user_id', $self->id);
		// vai jebko citu....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

Tas ir noderīgi, ja vēlaties, lai notikumi notiktu gan ievietošanas, gan atjaunināšanas laikā. Es jums paglabāšu garo skaidrojumu, bet esmu pārliecināts, ka varat uzminēt, kas tas ir.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeSave(self $self) {
		$self->last_updated = gmdate('Y-m-d H:i:s');
	} 
}
```

#### `beforeDelete(ActiveRecord $ActiveRecord)/afterDelete(ActiveRecord $ActiveRecord)`

Nezinu, ko jūs šeit gribētu darīt, bet nekādu nosodījumu! Dariet to!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeDelete(self $self) {
		echo 'Viņš bija drosmīgs karavīrs... :cry-face:';
	} 
}
```

## Datubāzes savienojuma pārvaldība

Kad izmantojat šo bibliotēku, datubāzes savienojumu varat iestatīt vairākos dažādos veidos. Savienojumu varat iestatīt konstruktorā, varat to iestatīt, izmantojot konfigurācijas mainīgo `$config['connection']`, vai arī varat to iestatīt, izmantojot `setDatabaseConnection()` (v0.4.1).

```php
$pdo_connection = new PDO('sqlite:test.db'); // piemēram
$user = new User($pdo_connection);
// vai
$user = new User(null, [ 'connection' => $pdo_connection ]);
// vai
$user = new User();
$user->setDatabaseConnection($pdo_connection);
```

Ja vēlaties izvairīties no tā, ka katru reizi, kad izsaucat aktīvo ierakstu, vienmēr ir jāiestata `$database_connection`, ir veidi, kā to apiet!

```php
// index.php vai bootstrap.php
// Iestatiet šo kā reģistrētu klasi Flight
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

// User.php
class User extends flight\ActiveRecord {
	
	public function __construct(array $config = [])
	{
		$database_connection = $config['connection'] ?? Flight::db();
		parent::__construct($database_connection, 'users', $config);
	}
}

// Un tagad, nav nepieciešami argumenti!
$user = new User();
```

> **Piezīme:** Ja plānojat veikt vienību testēšanu (unit testing), šāda rīcība var radīt dažas problēmas vienību testēšanā, bet kopumā, tā kā savienojumu varat injicēt ar `setDatabaseConnection()` vai `$config['connection']`, tas nav pārāk slikti.

Ja nepieciešams atsvaidzināt datubāzes savienojumu, piemēram, ja izpildāt ilgstošu CLI skriptu un ik pa laikam ir nepieciešams atsvaidzināt savienojumu, varat no jauna iestatīt savienojumu ar `$your_record->setDatabaseConnection($pdo_connection)`.

## Līdzdalība

Lūdzu, dariet to. :D

### Iestatīšana

Kad līdzdarbojaties, pārliecinieties, ka izpildāt `composer test-coverage`, lai saglabātu 100% testu pārklājumu (tas nav īsts vienību testu pārklājums, drīzāk integrācijas testēšana).

Tāpat pārliecinieties, ka izpildāt `composer beautify` un `composer phpcs`, lai novērstu jebkādas lintēšanas kļūdas.

## Licence

MIT