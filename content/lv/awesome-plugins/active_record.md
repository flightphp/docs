# Flight Active Record 

Aktīvais ieraksts ir datubāzes vienības atspoguļojums PHP objektā. Vienkārši izsakoties, ja jums ir lietotāju tabula datubāzē, jūs varat "pārvērst" rindu šajā tabulā par `User` klasi un `$user` objektu jūsu koda bāzē. Skatiet [pamata piemēru](#basic-example).

Spied [šeit](https://github.com/flightphp/active-record), lai iegūtu repozitoriju GitHub vietnē.

## Pamata Piemērs

Pieņemsim, ka jums ir šāda tabula:

```sql
CREATE TABLE users (
	id INTEGER PRIMARY KEY, 
	name TEXT, 
	password TEXT 
);
```

Tagad jūs varat izveidot jaunu klasi, lai pārstāvētu šo tabulu:

```php
/**
 * ActiveRecord klase parasti ir vienatnē
 * 
 * Iespējams, ir ieteicams pievienot tabulas īpašības kā komentārus šeit
 * 
 * @property int    $id
 * @property string $name
 * @property string $password
 */ 
class User extends flight\ActiveRecord {
	public function __construct($datubāzes_savienojums)
	{
		// jūs varat iestatīt to šādi
		parent::__construct($datubāzes_savienojums, 'lietotāji');
		// vai šādi
		parent::__construct($datubāzes_savienojums, null, [ 'table' => 'lietotāji']);
	}
}
```

Tagad skatieties, kas notiek!

```php
// SQLite gadījumā
$datubāzes_savienojums = new PDO('sqlite:test.db'); // tas ir tikai piemērs; jūs, visticamāk, izmantotu reālu datubāzes savienojumu

// MySQL gadījumā
$datubāzes_savienojums = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'lietotājvārds', 'parole');

// vai mysqli
$datubāzes_savienojums = new mysqli('localhost', 'lietotājvārds', 'parole', 'test_db');
// vai mysqli ar neobjekta pamatotu izveidi
$datubāzes_savienojums = mysqli_connect('localhost', 'lietotājvārds', 'parole', 'test_db');

$user = new User($datubāzes_savienojums);
$user->name = 'Bobby Tabulas';
$user->password = password_hash('kāda forša parole');
$user->insert();
// vai $user->save();

echo $user->id; // 1

$user->name = 'Joseph Tava Mamma';
$user->password = password_hash('kāda ļoti forša parole vēlreiz!!!');
$user->insert();
// šeit nevar izmantot $user->save(), jo tas uzskatīs, ka tas ir atjauninājums!

echo $user->id; // 2
```

Un tik ātri tika pievienots jauns lietotājs! Tagad, kad datu bāzē ir lietotāja rinda, kā to izvilkt?

```php
$user->find(1); // atrod id = 1 datu bāzē un atgriež to.
echo $user->name; // 'Bobby Tabulas'
```

Un ja vēlaties atrast visus lietotājus?

```php
$lietotāji = $user->findAll();
```

Kas notiks ar noteiktu nosacījumu?

```php
$lietotāji = $user->like('name', '%mamma%')->findAll();
```

Redziet, cik jautri tas ir? Uzstādiet to un sāciet!

## Instalēšana

Vienkārši instalējiet ar Composer

```php
composer require flightphp/active-record 
```

## Lietošana

To var izmantot kā neatkarīgu bibliotēku vai ar Flight PHP ietvaru. Pilnīgi atkarīgs no jums.

### Neatkarīgi
Vienkārši pārliecinieties, ka nododat PDO savienojumu konstruktoram.

```php
$pdo_savienojums = new PDO('sqlite:test.db'); // tas ir tikai piemērs; jūs, visticamāk, izmantotu reālu datubāzes savienojumu

$Lietotājs = new User($pdo_savienojums);
```

> **Piezīme:** Ja negribat vienmēr iestatīt savienojumu ar datubāzi konstruktorā, skatiet [Datubāzes Savienojuma Pārvaldība](#database-connection-management) citus ieteikumus!

### Reģistrēt kā metodi Flight
Ja izmantojat Flight PHP ietvaru, varat reģistrēt ActiveRecord klasi kā pakalpojumu, bet, lai godīgi būtu, jums to nevajag.

```php
Flight::register('lietotājs', 'Lietotājs', [ $pdo_savienojums ]);

// tad to varat izmantot tādā veidā kontrolierī, funkcijā, utt.

Flight::user()->find(1);
```

## `runway` Metodes

[runway](https://docs.flightphp.com/awesome-plugins/runway) ir Flight CLI rīks, kam ir pielāgots komandu šim bibliotēkai. 

```bash
# Lietošana
php runway make:record datubāzes_tabulas_vārds [klases_vārds]

# Piemērs
php runway make:record lietotāji
```

Tas izveidos jaunu klasi `UserRecord.php` direktorijā `app/records/` ar šādu saturu:

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * ActiveRecord klase lietotāju tabulai.
 * @link https://docs.flightphp.com/awesome-plugins/active-record
 *
 * @property int $id
 * @property string $lietotājvārds
 * @property string $e-pasts
 * @property string $paroles_hash
 * @property string $izveides_dt
 */
class UserRecord extends \flight\ActiveRecord
{
    /**
     * @var array $attiecības Uzstādiet attiecības modeļiem
     *   https://docs.flightphp.com/awesome-plugins/active-record#relationships
     */
    protected array $attiecības = [
		// 'attiecības_vārds' => [ self::HAS_MANY, 'SaistītāKlase', 'ārējais_atslēgas_lauks' ],
	];

    /**
     * Konstruktors
     * @param mixed $datubāzesSavienojums Savienojums ar datubāzi
     */
    public function __construct($datubāzesSavienojums)
    {
        parent::__construct($datubāzesSavienojums, 'lietotāji');
    }
}
```

## CRUD funkcijas

#### `find($id = null) : boolean|ActiveRecord`

Atrast vienu ierakstu un piešķirt to pašreizējam objektam. Ja padodat kādu `$id`, tas veiks meklēšanu pēc primārās atslēgas ar šo vērtību. Ja netiek padots nekas, tā vienkārši atradīs pirmo ierakstu tabulā.

Papildus varat padot citus palīglīdzekļus, lai vaicātu jūsu tabulu.

```php
// atrast ierakstu ar priekšnosacījumiem
$user->notNull('password')->orderBy('id DESC')->find();

// atrast ierakstu ar konkrētu id
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

Atrast visus ierakstus tabulā, ko norādījāt.

```php
$user->findAll();
```

#### `isHydrated(): boolean` (v0.4.0)

Atgriež `true`, ja pašreizējais ieraksts ir uzpildīts (paņemts no datubāzes).

```php
$user->find(1);
// ja ieraksts tika atrasts ar datiem...
$user->isHydrated(); // true
```

#### `insert(): boolean|ActiveRecord`

Ievieto pašreizējo ierakstu datubāzē.

```php
$user = new User($pdo_savienojums);
$user->name = 'demo';
$user->password = md5('demo');
$user->insert();
```

##### Teksta Bāzēta Primārās Atslēgas

Ja jums ir teksta bāzēta primārā atslēga (piemēram, UUID), jūs varat iestatīt primāro atslēgu vērtību pirms ievietošanas kādā no diviem veidiem.

```php
$user = new User($pdo_savienojums, [ 'primaryKey' => 'uuid' ]);
$user->uuid = 'kāds-uuid';
$user->name = 'demo';
$user->password = md5('demo');
$user->insert(); // vai $user->save();
```

vai varat ļaut primāro atslēgu automātiski ģenerēties, izmantojot notikumus.

```php
class User extends flight\ActiveRecord {
	public function __construct($datubāzes_savienojums)
	{
		parent::__construct($datubāzes_savienojums, 'lietotāji', [ 'primaryKey' => 'uuid' ]);
		// varat arī iestatīt primāro atslēgu šādi, nevis iepriekš definētajā masīvā.
		$this->primaryKey = 'uuid';
	}

	protected function beforeInsert(self $self) {
		$self->uuid = uniqid(); // vai kā jums vajag ģenerēt savus unikālos identifikatorus
	}
}
```

Ja nepiesakāt primāro atslēgu pirms ievietošanas, tā tiks iestatīta uz `rowid`, un datubāze to ģenerēs jums, bet tas nesanāks, jo šis lauks varētu nepastāvēt jūsu tabulā. Tāpēc ir ieteicams izmantot notikumu, lai automātiski šo apstrādātu.

#### `update(): boolean|ActiveRecord`

Atjaunina pašreizējo ierakstu datubāzē.

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->email = 'tests@piemērs.com';
$user->update();
```

#### `save(): boolean|ActiveRecord`

Ievieto vai atjaunina pašreizējo ierakstu datubāzē. Ja ierakstam ir id, tas tiks atjaunināts, pretējā gadījumā tas tiks ievietots.

```php
$user = new User($pdo_savienojums);
$user->name = 'demo';
$user->password = md5('demo');
$user->save();
```

**Piezīme:** Ja klasē ir definētas attiecības, tās tiks šķērsotas, lai atjaunotu arī tās attiecības, ja tās ir definētas, instancētas un ir netīras datu atjaunošanai. (v0.4.0 un jaunāk)

#### `delete(): boolean`

Nosaka pašreizējo ierakstu no datubāzes.

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

Varat arī dzēst vairākus ierakstus, pirms izpildāt meklēšanu.

```php
$user->like('name', 'Boba%')->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

Netīros dati attiecas uz datiem, kas ir mainīti ierakstā.

```php
$user->gt('id', 0)->orderBy('id desc')->find();

// nekas nav "netīrs" līdz šim.

$user->email = 'tests@piemērs.com'; // tagad e-pasts tiek uzskatīts par "netīru", jo tas ir mainījies.
$user->update();
// tagad nav neviena netīrā datu, jo tā ir atjaunināta un saglabāta datubāzē

$user->password = password_hash()'jaunāparole'); // tagad tas ir netīrs
$user->dirty(); // nosūtot neko tīši notīra visus netīros ierakstus.
$user->update(); // netiks veikts neviens atjaunošana, jo nav sagrābts, ka kaut kas ir netīrs.

$user->dirty([ 'name' => 'kautkas', 'password' => password_hash('atkārtota atšifrēšanas parole') ]);
$user->update(); // abi vārdi un parole tiks atjaunoti.
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

Tas ir sinonīms `dirty()` metodei. Tas ir mazliet skaidrāk, ko jūs darāt.

```php
$user->copyFrom([ 'name' => 'kautkas', 'password' => password_hash('atkārtota atšifrēšanas parole') ]);
$user->update(); // abi vārdi un parole tiks atjaunoti.
```

#### `isDirty(): boolean` (v0.4.0)

Atgriež `true`, ja pašreizējais ieraksts ir mainīts.

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->email = 'tests@piemērs.com';
$user->isDirty(); // true
```

#### `reset(bool $include_query_data = true): ActiveRecord`

Atiestata pašreizējo ierakstu uz tā sākotnējo stāvokli. Tas ir ļoti noderīgi izmantojot cikliskā tipa uzvedību. Ja padodat `true`, tas arī atiestatīs vaicājumu datus, kas tika izmantoti, lai atrastu pašreizējo objektu (noklusējuma uzvedība).

```php
$lietotāji = $user->gt('id', 0)->orderBy('id desc')->find();
$user_uzņēmums = new UserCompany($pdo_savienojums);

foreach($lietotāji as $lietotājs) {
	$user_uzņēmums->reset(); // sākt ar tīru plaukta izskatu
	$user_uzņēmums->user_id = $lietotājs->id;
	$user_uzņēmums->company_id = $daudzums_uzņēmuma_id;
	$user_uzņēmums->insert();
}
```

#### `getBuiltSql(): string` (v0.4.1)

Pēc tam, kad esat izpildījis `find()`, `findAll()`, `insert()`, `update()` vai `save()` metodi, jūs varat iegūt izveidotā SQL un izmantot to, lai atkļūdotu.

## SQL Vaicājuma Metodes
#### `select(string $field1 [, string $field2 ... ])`

Jūs varat atlasīt tikai dažus tabulas laukus, ja vēlaties (tas ir efektīvāks ļoti plašu tabulu ar daudziem laukiem gadījumā)

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

Jūs tehniski varat izvēlēties citu tabulu arī! Kāpēc arī ne?

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

Jūs varat pat pievienot citai tabulai datubāzē.

```php
$user->join('kontakti', 'kontakti.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

Jūs varat iestatīt dažus pielāgotus where argumentus (jūs nevarat iestatīt parametrus šдика вЂєкуфос ки.а. џањ.у ниурулка и ПНР ђагди.уии реди.хиртми.д та "кумирлк" дни.дируглик" ки њоьуцфук а руйд.джеигабн.у ПНР и $Пкп$ди ща' ПНРПккпеигш. Соади [екцидаг екиграли](#небни-екцидаиг).

Сеыйт [хере](https://github.com/flightphp/active-record) фор тии репоситоры ин ГитХуб.

## Небни Екиграли

Летс аснукуе једи ле поано:

```икк
ЦРЕАТЕ ТАБЛЕ Усерс (
	ид ІНТЕГЕР ПРИМАРЫ КЕУ,
	наме ТЕХТ,
	пассворд ТЕХТ 
);
```

Нос иаир тсеуп а нев чласс то репресент тхис табле:

```хпх
/**
 * Ан АцтивеРецорд цласс ис усуаллы сингулар
 * 
 * Ит'с хигхлы рецоммендед то адд тхе пропертис оф тхе табле ас комментс хере
 * 
 * @проперты инт    $ид
 * @проперты стринг $наме
 * @проперты стринг $пассворд
 */ 
цласс Усер екстендс флигхт\АцтивеРецорд {
	публиц фунцтион __цонструкт($датабасе_цоннецтион)
	{
		// јоу цан сет ит тхис ваы
		парент::__цонструкт($датабасе_цоннецтион, 'усерс');
		// ор тхис ваы
		парент::__цонструкт($датабасе_цоннецтион, нулл, [ 'табле' => 'усерс']);
	}
}
```

Нов ватцх тхе магив ћаппен!

```хпх
// фор ѕқлите
$датабасе_цоннецтион = неw РДО('сқлите:тест.дб'); // тхис ис јуст фор екампле, уоуд пробаЬлы усе а реал датабасе цоннецтион

// фор мүсқл
$датабасе_цоннецтион = неw РДО('мүсқл:host=localhost;дбнаме=тест_дб&цхарсет=утф8бм4', 'усернаме', 'пассворд');

// ор мйсли
$датабасе_цоннецтион = неw мүсқл('лоцалхост', 'усернаме', 'пассворд', 'тест_дб');
// ор мйсли витх нон-објецт басед цреатион
$датабасе_цоннецтион = мйсли_цоннецт('лоцалхост', 'усернаме', 'пассворд', 'тест_дб');

$усер = неw Усер($датабасе_цоннецтион);
$усер->наме = 'Боббы Таблес';
$усер->пассворд = пассворд_хаш('соме цоол пассворд');
$усер->инсерт();
// ор $усер->саве();

есхо $усер->ид; // 1

$усер->наме = 'Јосепх Мамма';
$усер->пассворд = пассворд_хаш('соме цоол пассворд агаин!!!');
$усер->инсерт();
// цан't усе $усер->саве() хере ор ит вилл тхинк ит's ан упдате!

есхо $усер->ид; // 2
```

Анд ит вас јуст тхат еасы то адд а нев усер! Нов тхат тхере ис а усер ров ин тхе датабасе, хоу до уоу пулл ит оут?

```хпх
$усер->финд(1); // финд ид = 1 ин тхе датабасе анд ретурн ит.
есхо $усер->наме; // 'Боббы Таблес'
```

Анд шат иф уоу вант то финд алл тхе усерс?

```хпх
$усерс = $усер->финдАлл();
```

Вхат абоут витх а цертаин цондитион?

```хпх
$усерс = $усер->лике('наме', '%мамма%')->финдАлл();
```

Сее хов муцх фун тхис ис? Лет'с инсталл ит анд гет стартед!

## Инсталлатион

Симплы инсталл витх Цомпосер

```хпх
цомпосер рецуире флигхтпхп/ацтиве-рецорд 
```

## Усаге

Тхис цан бе усед ас а стандалоне либрары ор витх тхе Флигхт ПХП Фрамеворк. Цомплетелы уп то уоу.

### Стандалоне
Јуст макес шуре уоу пасс а ПДО цоннецтион то тхе констрцтор.

```хпх
$пдо_цоннецтион = неw ПДО('сқлите:тест.дб'); // тхис ис јуст фор екампле, уоуд пробаЬлы усе а реал датабасе цоннецтион

$Усер = неw Усер($пдо_цоннецтион);
```

> **Дон'т вант то алва