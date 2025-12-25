# Runway

Runway ir CLI lietojumprogramma, kas palīdz pārvaldīt jūsu Flight lietojumprogrammas. Tā var ģenerēt kontrolierus, parādīt visas maršrutus un vairāk. Tā balstās uz izcilo [adhocore/php-cli](https://github.com/adhocore/php-cli) bibliotēku.

Noklikšķiniet [šeit](https://github.com/flightphp/runway), lai skatītu kodu.

## Uzstādīšana

Uzstādiet ar composer.

```bash
composer require flightphp/runway
```

## Pamata konfigurācija

Pirmo reizi palaižot Runway, tā vadīs jūs cauri uzstādīšanas procesam un izveidos `.runway.json` konfigurācijas failu jūsu projekta saknes direktorijā. Šis fails saturēs dažas nepieciešamas konfigurācijas, lai Runway darbotos pareizi.

## Lietošana

Runway ir vairākas komandas, kuras jūs varat izmantot, lai pārvaldītu jūsu Flight lietojumprogrammu. Ir divi viegli veidi, kā izmantot Runway.

1. Ja jūs izmantojat skeletu projektu, jūs varat palaidīt `php runway [command]` no jūsu projekta saknes.
1. Ja jūs izmantojat Runway kā paketi, kas uzstādīta caur composer, jūs varat palaidīt `vendor/bin/runway [command]` no jūsu projekta saknes.

Jebkurai komandai jūs varat pievienot `--help` karodziņu, lai iegūtu vairāk informācijas par to, kā izmantot komandu.

```bash
php runway routes --help
```

Šeit ir daži piemēri:

### Ģenerēt kontrolieri

Pamatojoties uz konfigurāciju jūsu `.runway.json` failā, noklusējuma atrašanās vieta ģenerēs kontrolieri jums `app/controllers/` direktorijā.

```bash
php runway make:controller MyController
```

### Ģenerēt Active Record modeli

Pamatojoties uz konfigurāciju jūsu `.runway.json` failā, noklusējuma atrašanās vieta ģenerēs kontrolieri jums `app/records/` direktorijā.

```bash
php runway make:record users
```

Ja, piemēram, jums ir `users` tabula ar šādu shēmu: `id`, `name`, `email`, `created_at`, `updated_at`, fails, līdzīgs šim, tiks izveidots `app/records/UserRecord.php` failā:

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * ActiveRecord klase lietotājiem tabulai.
 * @link https://docs.flightphp.com/awesome-plugins/active-record
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $created_at
 * @property string $updated_at
 * // jūs varat arī pievienot attiecības šeit, kad definēsiet tās $relations masīvā
 * @property CompanyRecord $company Attiecības piemērs
 */
class UserRecord extends \flight\ActiveRecord
{
    /**
     * @var array $relations Iestatīt attiecības modelim
     *   https://docs.flightphp.com/awesome-plugins/active-record#relationships
     */
    protected array $relations = [];

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

### Parādīt visus maršrutus

Tas parādīs visus maršrutus, kas pašlaik reģistrēti ar Flight.

```bash
php runway routes
```

Ja vēlaties skatīt tikai specifiskus maršrutus, jūs varat pievienot karodziņu, lai filtrētu maršrutus.

```bash
# Parādīt tikai GET maršrutus
php runway routes --get

# Parādīt tikai POST maršrutus
php runway routes --post

# utt.
```

## Pielāgošana Runway

Ja jūs vai nu izveidojat paketi Flight, vai vēlaties pievienot savas pielāgotas komandas savam projektam, jūs varat to izdarīt, izveidojot `src/commands/`, `flight/commands/`, `app/commands/` vai `commands/` direktoriju savam projektam/paketei. Ja nepieciešama tālākā pielāgošana, skatiet sadaļu zemāk par Konfigurāciju.

Lai izveidotu komandu, jūs vienkārši pagarināt `AbstractBaseCommand` klasi un implementēt vismaz `__construct` metodi un `execute` metodi.

```php
<?php

declare(strict_types=1);

namespace flight\commands;

class ExampleCommand extends AbstractBaseCommand
{
	/**
     * Konstruktors
     *
     * @param array<string,mixed> $config JSON konfigurācija no .runway-config.json
     */
    public function __construct(array $config)
    {
        parent::__construct('make:example', 'Izveidot piemēru dokumentācijai', $config);
        $this->argument('<funny-gif>', 'Smieklīgā gif nosaukums');
    }

	/**
     * Izpilda funkciju
     *
     * @return void
     */
    public function execute()
    {
        $io = $this->app()->io();

		$io->info('Izveido piemēru...');

		// Dariet kaut ko šeit

		$io->ok('Piemērs izveidots!');
	}
}
```

Skatiet [adhocore/php-cli Dokumentāciju](https://github.com/adhocore/php-cli), lai iegūtu vairāk informācijas par to, kā izveidot savas pielāgotas komandas savai Flight lietojumprogrammai!

### Konfigurācija

Ja nepieciešams pielāgot konfigurāciju Runway, jūs varat izveidot `.runway-config.json` failu jūsu projekta saknes direktorijā. Zemāk ir dažas papildu konfigurācijas, kuras jūs varat iestatīt:

```js
{

	// Šī ir vieta, kur atrodas jūsu lietojumprogrammas direktorija
	"app_root": "app/",

	// Šī ir direktorija, kur atrodas jūsu saknes indeksa fails
	"index_root": "public/",

	// Šie ir ceļi uz citu projektu saknēm
	"root_paths": [
		"/home/user/different-project",
		"/var/www/another-project"
	],

	// Bāzes ceļi visticamāk nav jākonfigurē, bet tas ir šeit, ja vēlaties
	"base_paths": {
		"/includes/libs/vendor", // ja jums ir patiesi unikāls ceļš uz jūsu vendor direktoriju vai kaut ko
	},

	// Galīgie ceļi ir vietas projektā, kur meklēt komandu failus
	"final_paths": {
		"src/diff-path/commands",
		"app/module/admin/commands",
	},

	// Ja vēlaties pievienot pilnu ceļu, dariet to (absolūts vai relatīvs pret projektu sakni)
	"paths": [
		"/home/user/different-project/src/diff-path/commands",
		"/var/www/another-project/app/module/admin/commands",
		"app/my-unique-commands"
	]
}
```