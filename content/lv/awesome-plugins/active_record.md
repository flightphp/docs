# FlightPHP Aktīvais ieraksts

Aktīvais ieraksts ir datu bāzes iedarbināšana PHP objektā. Runājot vienkārši, ja jums ir lietotāju tabula jūsu datu bāzē, jūs varat "pārtulkot" rindu šajā tabulā uz `User` klasi un `$user` objektu jūsu koda pamatnē. Skatiet [pamata piemēru](#pamata-piemērs).

## Pamata Piemērs

Pretim ņemsim šo tabulu:

```sql
CREATE TABLE lietotaji (
	id INTEGER PRIMARY KEY, 
	vārds TEKSTS, 
	parole TEKSTS 
);
```

Tagad jūs varat iestatīt jaunu klasi, lai attēlotu šo tabulu:

```php
/**
 * ActiveRecord klase parasti ir vienskaitlis
 * 
 * Ieteicams pievienot tabulas īpašības šeit kā komentārus
 * 
 * @property int    $id
 * @property string $vārds
 * @property string $parole
 */ 
class Lietotājs atspējas\ActiveRecord {
	public function __construct($datubāzes_savienojums)
	{
		// jūs varat iestatīt to šādi
		veids::__construct($datubāzes_savienojums, 'lietotaji');
		// vai arī šādi
		veids::__construct($datubāzes_savienojums, null, [ 'tabula' => 'lietotaji']);
	}
}
```

Tagad vērojiet maģiju notiekam!

```php
// sqlite
$datubāzes_savienojums = new PDO('sqlite:test.db'); // tas ir tikai piemēram, iespējams, izmantojat reālu datubāzes savienojumu

// mysql
$datubāzes_savienojums = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'lietotājvārds', 'parole');

// vai mysqli
$datubāzes_savienojums = new mysqli('localhost', 'lietotājvārds', 'parole', 'test_db');
// vai mysqli ar neobjektu pamata izveidošanu
$datubāzes_savienojums = mysqli_connect('localhost', 'lietotājvārds', 'parole', 'test_db');

$lietotājs = new Lietotājs($datubāzes_savienojums);
$lietotājs->vārds = 'Bobijs Tabules';
$lietotājs->parole = password_hash('daudz jauka parole');
$lietotājs->insert();
// vai $lietotājs->save();

echo $lietotājs->id; // 1

$lietotājs->vārds = 'Jozefs Mamma';
$lietotājs->parole = password_hash('daudz jauka parole vēlreiz!!!');
$lietotājs->insert();
// šeit nevar izmantot $lietotājs->save(), citādi tas domās, ka tā ir atjaunināšana!

echo $lietotājs->id; // 2
```

Un tā bija tik vienkārši pievienot jaunu lietotāju! Tagad, kad datu bāzē ir lietotāja rinda, kā jūs to izvelkat?

```php
$lietotājs->find(1); // atrast id = 1 datu bāzē un to atgriezt.
echo $lietotājs->vārds; // 'Bobijs Tabules'
```

Un ja jūs vēlaties atrast visus lietotājus?

```php
$lietotāji = $lietotājs->findAll();
```

Kas ir ar noteiktu nosacījumu?

```php
$lietotāji = $lietotājs->like('vārds', '%mamma%')->findAll();
```

Redzat cik jautri tas ir? Uzinstalēsim to un sāksim!

## Instalācija

Vienkārši instalējiet ar Composer

```php
composer require flightphp/active-record 
```

## Lietošana

Šo var izmantot gan kā neatkarīgu bibliotēku, gan arī ar Flight PHP pamatni. Pilnībā ir atkarīgs no jums.

### Neatkarīgs
Vienkārši nodrošiniet PDO savienojumu konstruktoram.

```php
$pdo_savienojums = new PDO('sqlite:test.db'); // tas ir tikai piemēram, iespējams, izmantojat reālu datubāzes savienojumu

$Lietotājs = new Lietotājs($pdo_savienojums);
```

### Flight PHP pamatne
Ja izmantojat Flight PHP pamatni, jūs varat reģistrēt ActiveRecord klasi kā servisu (bet jums tiešām nav nepieciešams).

```php
Lidojums::reģistrēt('lietotājs', 'Lietotājs', [ $pdo_savienojums ]);

// tad jūs to varat izmantot šādi kontrolorā, funkcijā, utt.

Lidojums::lietotājs()->find(1);
```

## API Atsauce
### CRUD funkcijas

#### `find($id = null) : boolean|ActiveRecord`

Atrast vienu ierakstu un piešķirt to pašreizējam objektam. Ja jūs padodat kādu `$id`, tas veiks meklēšanu pēc primārās atslēgas ar šo vērtību. Ja neko nepiedāvājat, tas vienkārši atradīs pirmo ierakstu tabulā.

Papildus tam varat padot citus palīgmetodus, lai vaicātu savu tabulu.

```php
// atrast ierakstu ar dažādiem nosacījumiem pirms tam
$lietotājs->notNull('parole')->orderBy('id desc')->find();

// atrast ierakstu pēc konkrēta id
$id = 123;
$lietotājs->find($id);
```

#### `findAll() : array<int,ActiveRecord>`

Atrod visus ierakstus tabulā, ko norādāt.

```php
$lietotājs->findAll();
```

#### `insert() : boolean|ActiveRecord`

Ievietot pašreizējo ierakstu datu bāzē.

```php
$lietotājs = new Lietotājs($pdo_savienojums);
$lietotājs->vārds = 'demo';
$lietotājs->parole = md5('demo');
$lietotājs->insert();
```

#### `update() : boolean|ActiveRecord`

Atjaunināt pašreizējo ierakstu datu bāzē.

```php
$lietotājs->greaterThan('id', 0)->orderBy('id desc')->find();
$lietotājs->e-pasts = 'tests@example.com';
$lietotājs->update();
```

#### `delete() : boolean`

Dzēst pašreizējo ierakstu no datu bāzes.

```php
$lietotājs->gt('id', 0)->orderBy('id desc')->find();
$lietotājs->delete();
```

#### `dirty(array  $dirty = []) : ActiveRecord`

"Sliktie" dati attiecas uz datiem, kas ir mainījušies ierakstā.

```php
$lietotājs->gt('id', 0)->orderBy('id desc')->find();

// šajā brīdī nekas nav "slikti".

$lietotājs->e-pasts = 'tests@example.com'; // tagad e-pasts tiek uzskatīts par "slikto", jo tas ir mainījies.
$lietotājs->update();
// tagad nav datu, kas ir slikti, jo tas ir atjaunināts un saglabāts datu bāzē

$lietotājs->parole = password_hash()'jaunaparole'); // tagad tas ir slikti
$lietotājs->dirty(); // pārsūtot neko notīrīs visus "sliktos" ierakstus.
$lietotājs->update(); // nekas netiks atjaunināts, jo nav sagrābtu kā "slikts".

$lietotājs->dirty([ 'vārds' => 'kas', 'parole' => password_hash('atšķirīga parole') ]);
$lietotājs->update(); // abi vārdi un parole tiek atjaunoti.
```

### SQL vaicājumu metodes
#### `select(string $field1 [, string $field2 ... ])`

Jūs varat atlasīt tikai dažus tabulas stabiņus, ja vēlaties (tas ir efektīvāk uz tiešām plašām tabulām ar daudziem stabiņiem)

```php
$lietotājs->select('id', 'vārds')->find();
```

#### `from(string $table)`

Jūs varat tehniski izvēlēties citu tabulu! Kāpēc ne?!

```php
$lietotājs->select('id', 'vārds')->from('lietotājs')->find();
```

#### `join(string $table_name, string $join_condition)`

Jūs varat pat pievienoties citai tabulai datu bāzē.

```php
$lietotājs->join('kontakti', 'kontakti.lietotāja_id = lietotāji.id')->find();
```

#### `where(string $where_conditions)`

Jūs varat iestatīt dažus pielāgotos WHERE argumentus (šajā where izteiksmē nevar iestatīt parametrus)

```php
$lietotājs->where('id=1 AND vārds="demo"')->find();
```

**Drošības piezīme** – Jūs varētu būt kārdināts darīt kaut ko līdzīgu `$lietotājs->where("id = '{$id}' AND vārds = '{$vārds}'")->find();`. LŪDZAM NEKĀRINĀT TO!!! Šis ir ievainojams pret tā sauktajām SQL injekcijas uzbrukumiem. Ir daudz rakstu tiešsaistē, lūdzu, meklējiet Google "sql injection attacks php" un jūs atradīsiet daudz rakstu par šo tēmu. Pareizais veids, kā apieties ar šo bibliotēku, ir tā vietā, lai izmantotu šo `where()` metodi, jums būtu jādara kaut kas līdzīgs `$lietotājs->eq('id', $id)->eq('name', $vārds)->find();`

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

Grupējiet savus rezultātus pēc konkrēta nosacījuma.

```php
$lietotājs->select('COUNT(*) kā skaits')->groupBy('vārds')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

Kārtot atgriezto vaicājumu noteiktā kārtībā.

```php
$lietotājs->orderBy('vārds DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

Ierobežojiet atgriezto ierakstu skaitu. Ja tiek dots otrs int, tas būs nobīde, kā to varētu sagaidīt SQL.

```php
$lietotājs->orderby('vārds DESC')->limit(0, 10)->findAll();
```

### WHERE nosacījumi
#### `equal(string $field, mixed $value) / eq(string $field, mixed $value)`

Kur `field = $value`

```php
$lietotājs->eq('id', 1)->find();
```

#### `notEqual(string $field, mixed $value) / ne(string $field, mixed $value)`

Kur `field <> $value`

```php
$lietotājs->ne('id', 1)->find();
```

#### `isNull(string $field)`

Kur `field IR NULL`

```php
$lietotājs->isNull('id')->find();
```
#### `isNotNull(string $field) / notNull(string $field)`

Kur `fields NAV NULL`

```php
$lietotājs->isNotNull('id')->find();
```

#### `greaterThan(string $field, mixed $value) / gt(string $field, mixed $value)`

Kur `field > $value`

```php
$lietotājs->gt('id', 1)->find();
```

#### `lessThan(string $field, mixed $value) / lt(string $field, mixed $value)`

Kur `field < $value`

```php
$lietotājs->lt('id', 1)->find();
```
#### `greaterThanOrEqual(string $field, mixed $value) / ge(string $field, mixed $value) / gte(string $field, mixed $value)`

Kur `field >= $value`

```php
$lietotājs->ge('id', 1)->find();
```
#### `lessThanOrEqual(string $field, mixed $value) / le(string $field, mixed $value) / lte(string $field, mixed $value)`

Kur `field <= $value`

```php
$lietotājs->le('id', 1)->find();
```

#### `like(string $field, mixed $value) / notLike(string $field, mixed $value)`

Kur `field LIKE $value` vai `field NOT LIKE $value`

```php
$lietotājs->like('vārds', 'de')->find();
```

#### `in(string $field, array $values) / notIn(string $field, array $values)`

Kur `field IN($value)` vai `field NAV IN($value)`

```php
$lietotājs->in('id', [1, 2])->find();
```

#### `between(string $field, array $values)`

Kur `field STARP $value UN $value1`

```php
$lietotājs->between('id', [1, 2])->find();
```

### Attiecības
Ar šo bibliotēku varat iestatīt vairākas attiecības. Jūs varat iestatīt viens->daudz un viens->viens attiecības starp tabulām. Tas prasa nedaudz papildu iestatīšanu klases priekšā.

`$relations` masīva iestatīšana nav sarežģīta, bet pareiza sintakse var būt pārprasta.

```php
aizsargāts masīvs $relations = [
	// jūs varat nosaukt atslēgu kā vēlaties. ActiveRecord nosaukums droši vien ir labs. Piemēram, lietotājs, kontakts, klients
	'jebkurš_active_record' => [
		// nepieciešams
		self::HAS_ONE, // šī attiecības tips

		// nepieciešams
		'Daža_Klase', // tas ir "cits" ActiveRecord klase, uz ko tas atsaucas

		// nepieciešams
		'lokālā_atslēga', // tas ir lokālā atslēga, kas atsaucas pievienojuma.
		// tikai FYI, tas arī pievienojas pie "citas" modela primārās atslēgas

		// pēc izvēles
		[ 'eq' => 1, 'select' => 'COUNT(*) kā skaits', 'limita' 5 ], // pielāgotas metodes, ko vēlaties izpildīt. [] nav nekas, ja jums tādas nav.

		// pēc izvēles
		'atpakaļ_ atsauce_nosaukums' // tas ir, ja vēlaties atpakaļ uz atsauci pašām attiecībam; piemēram, $lietotājs->kontakts->liet'ts`.
  The content has been translated to Latvian (lv).