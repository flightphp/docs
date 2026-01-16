# Runway

Runway ir CLI lietojumprogramma, kas palīdz pārvaldīt jūsu Flight lietojumprogrammas. Tā var ģenerēt kontrolierus, parādīt visas maršrutus un vairāk. Tā ir balstīta uz izcilo [adhocore/php-cli](https://github.com/adhocore/php-cli) bibliotēku.

Noklikšķiniet [šeit](https://github.com/flightphp/runway), lai skatītu kodu.

## Instalēšana

Instalējiet ar composer.

```bash
composer require flightphp/runway
```

## Pamata konfigurācija

Pirmo reizi palaižot Runway, tā mēģinās atrast `runway` konfigurāciju `app/config/config.php` caur `'runway'` atslēgu.

```php
<?php
// app/config/config.php
return [
    'runway' => [
        'app_root' => 'app/',
		'public_root' => 'public/',
    ],
];
```

> **PIEZĪME** - No **v1.2.0**, `.runway-config.json` ir novecojis. Lūdzu, migrējiet savu konfigurāciju uz `app/config/config.php`. Jūs varat to izdarīt viegli ar `php runway config:migrate` komandu.



### Projekta saknes noteikšana

Runway ir pietiekami gudra, lai noteiktu jūsu projekta sakni, pat ja jūs to palaižat no apakšdirektorijas. Tā meklē indikatorus, piemēram, `composer.json`, `.git` vai `app/config/config.php`, lai noteiktu, kur ir projekta sakne. Tas nozīmē, ka jūs varat palaiž Runway komandas no jebkuras vietas savā projektā! 

## Lietošana

Runway ir vairākas komandas, kuras jūs varat izmantot, lai pārvaldītu savu Flight lietojumprogrammu. Ir divi viegli veidi, kā izmantot Runway.

1. Ja jūs izmantojat skeletu projektu, jūs varat palaiž `php runway [command]` no jūsu projekta saknes.
1. Ja jūs izmantojat Runway kā paketi, kas instalēta caur composer, jūs varat palaiž `vendor/bin/runway [command]` no jūsu projekta saknes.

### Komandu saraksts

Jūs varat skatīt sarakstu ar visām pieejamajām komandām, palaižot `php runway` komandu.

```bash
php runway
```

### Komandu palīdzība

Jebkurai komandai jūs varat pievienot `--help` karodziņu, lai iegūtu vairāk informācijas par to, kā izmantot komandu.

```bash
php runway routes --help
```

Šeit ir daži piemēri:

### Ģenerēt kontrolieri

Pamatojoties uz konfigurāciju `runway.app_root`, atrašanās vieta ģenerēs kontrolieri jums `app/controllers/` direktorijā.

```bash
php runway make:controller MyController
```

### Ģenerēt Active Record modeli

Vispirms pārliecinieties, ka esat instalējis [Active Record](/awesome-plugins/active-record) spraudni. Pamatojoties uz konfigurāciju `runway.app_root`, atrašanās vieta ģenerēs ierakstu jums `app/records/` direktorijā.

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
 * // jūs varētu arī pievienot attiecības šeit, kad definēsiet tās $relations masīvā
 * @property CompanyRecord $company Attiecību piemērs
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

### Parādīt visas maršrutus

Tas parādīs visus maršrutus, kas pašlaik ir reģistrēti ar Flight.

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

## Pievienot pielāgotas komandas Runway

Ja jūs vai nu izveidojat paketi Flight, vai vēlaties pievienot savas pielāgotas komandas savam projektam, jūs varat to izdarīt, izveidojot `src/commands/`, `flight/commands/`, `app/commands/` vai `commands/` direktoriju savam projektam/paketei. Ja nepieciešama tālāk customization, skatiet sadaļu zemāk par Konfigurāciju.

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
     * @param array<string,mixed> $config Konfigurācija no app/config/config.php
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

## Konfigurācijas pārvaldība

Tā kā konfigurācija ir pārvietota uz `app/config/config.php` no `v1.2.0`, ir dažas palīpkomandas konfigurācijas pārvaldībai.

### Migrēt veco konfigurāciju

Ja jums ir vecs `.runway-config.json` fails, jūs varat viegli to migrēt uz `app/config/config.php` ar šādu komandu:

```bash
php runway config:migrate
```

### Iestatīt konfigurācijas vērtību

Jūs varat iestatīt konfigurācijas vērtību, izmantojot `config:set` komandu. Tas ir noderīgi, ja vēlaties atjaunināt konfigurācijas vērtību, neatsākot failu.

```bash
php runway config:set app_root "app/"
```

### Iegūt konfigurācijas vērtību

Jūs varat iegūt konfigurācijas vērtību, izmantojot `config:get` komandu.

```bash
php runway config:get app_root
```

## Visas Runway konfigurācijas

Ja nepieciešams pielāgot konfigurāciju Runway, jūs varat iestatīt šīs vērtības `app/config/config.php`. Zemāk ir dažas papildu konfigurācijas, kuras jūs varat iestatīt:

```php
<?php
// app/config/config.php
return [
    // ... citas konfigurācijas vērtības ...

    'runway' => [
        // Šī ir vieta, kur atrodas jūsu lietojumprogrammas direktorija
        'app_root' => 'app/',

        // Šī ir direktorija, kur atrodas jūsu saknes indeksa fails
        'index_root' => 'public/',

        // Šīs ir ceļi uz citu projektu saknēm
        'root_paths' => [
            '/home/user/different-project',
            '/var/www/another-project'
        ],

        // Bāzes ceļi visticamāk nav jākonfigurē, bet tas ir šeit, ja vēlaties
        'base_paths' => [
            '/includes/libs/vendor', // ja jums ir patiesi unikāls ceļš uz jūsu vendor direktoriju vai kaut ko
        ],

        // Galīgie ceļi ir atrašanās vietas projektā, kur meklēt komandu failus
        'final_paths' => [
            'src/diff-path/commands',
            'app/module/admin/commands',
        ],

        // Ja vēlaties tikai pievienot pilnu ceļu, dariet to (absolūts vai relatīvs pret projektu sakni)
        'paths' => [
            '/home/user/different-project/src/diff-path/commands',
            '/var/www/another-project/app/module/admin/commands',
            'app/my-unique-commands'
        ]
    ]
];
```

### Piekļuve konfigurācijai

Ja nepieciešams efektīvi piekļūt konfigurācijas vērtībām, jūs varat piekļūt tām caur `__construct` metodi vai `app()` metodi. Ir arī svarīgi atzīmēt, ka, ja jums ir `app/config/services.php` fails, šie servisi būs pieejami jūsu komandai.

```php
public function execute()
{
    $io = $this->app()->io();
    
    // Piekļuve konfigurācijai
    $app_root = $this->config['runway']['app_root'];
    
    // Piekļuve servisiem, piemēram, datubāzes savienojumam
    $database = $this->config['database']
    
    // ...
}
```

## AI palīglīdzekļu apvalki

Runway ir daži palīglīdzekļu apvalki, kas padara vieglāku AI ģenerēt komandas. Jūs varat izmantot `addOption` un `addArgument` veidā, kas šķiet līdzīgs Symfony Console. Tas ir noderīgi, ja izmantojat AI rīkus, lai ģenerētu savas komandas.

```php
public function __construct(array $config)
{
    parent::__construct('make:example', 'Izveidot piemēru dokumentācijai', $config);
    
    // Name arguments ir nullable un pēc noklusējuma pilnībā opcionāls
    $this->addOption('name', 'Piemēra nosaukums', null);
}
```