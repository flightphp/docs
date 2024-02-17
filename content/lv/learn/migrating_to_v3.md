# Migrācija uz v3

Atkāpes saderība lielākoties ir bijusi saglabāta, bet ir dažas izmaiņas, par kurām jums jābūt informētiem, pārvietojoties no v2 uz v3.

## Izvades buferēšana

[Izvades buferēšana](https://stackoverflow.com/questions/2832010/what-is-output-buffering-in-php) ir process, kurā PHP skripta ģenerētā izvade tiek saglabāta buferī (iekšēji PHP) pirms tā tiek nosūtīta klientam. Tas ļauj jums modificēt izvadi pirms tā tiek nosūtīta klientam.

MVC lietotnē, Kontrolieris ir "vadītājs" un tas vadībā, kas notiek skatam. Izvades ģenerēšana ārpus kontroliera (vai, piemēram, Flights gadījumā dažreiz anonīmā funkcijā) pārkāpj MVC modeli. Šī izmaiņa ir domāta, lai labāk iekļautos MVC modelī un padarītu pamatlietojumprogrammu kodolu paredzamāku un vieglāk lietojamu.

V2 versijā izvades buferēšana tika apstrādāta tādā veidā, ka tas nepārtraukti neaizvēra savu paša izvades buferi, kas sarežģīja [vienītēs pārbaudi](https://github.com/flightphp/core/pull/545/files#diff-eb93da0a3473574fba94c3c4160ce68e20028e30b267875ab0792ade0b0539a0R42) un [izplūdēšanu](https://github.com/flightphp/core/issues/413). Lielākajai lietotāju daļai šī izmaiņa varētu nemainīt neko faktiski. Taču, ja jūs izvadāt saturu ārpus izsaukumprogrammām un kontrolieriem (piemēram, ķēdē), jums iespējams būs problēmas. Satura izvadīšana ķēdēs un pirms kodols patiesībā tiek izpildīts varbūt strādāja pagātnē, bet tā nedarbosies turpmāk.

### Kur jums varētu rasties problēmas
```php
// index.php
require 'vendor/autoload.php';

// tikai piemērs
define('START_TIME', microtime(true));

function hello() {
	echo 'Sveika pasaule';
}

Flight::map('hello', 'hello');
Flight::after('hello', function(){
	// tas patiešām būs labi
	echo '<p>Šis Sveika pasaule teksts tika piedāvāts jums ar burtu "S"</p>';
});

Flight::before('start', function(){
	// tādas darbības izraisīs kļūdu
	echo '<html><head><title>Mana Lapa</title></head><body>';
});

Flight::route('/', function(){
	// tas patiešām būs labi
	echo 'Sveika pasaule';

	// Tas arī vajadzētu būt labi
	Flight::hello();
});

Flight::after('start', function(){
	// tas izraisīs kļūdu
	echo '<div>Jūsu lapa ielādējās '.(microtime(true) - START_TIME).' sekundēs</div></body></html>';
});
```

### Ieslēdzot v2 renderēšanas rīcību

Vai jūs vēl varat paturēt savu veco kodu tādā formā, kā tas ir, nepārveidojot to, lai tas darbotos ar v3? Jā, jūs varat! Jūs varat ieslēgt v2 renderēšanas rīcību, iestatot `flight.v2.output_buffering` konfigurācijas opciju uz `true`. Tas ļaus jums turpināt lietot veco renderēšanas rīcību, bet ieteicams to labot turpmāk. v4 versijā no kodola, tas tiks noņemts.

```php
// index.php
require 'vendor/autoload.php';

Flight::set('flight.v2.output_buffering', true);

Flight::before('start', function(){
	// Tagad tas būs tikai labi
	echo '<html><head><title>Mana Lapa</title></head><body>';
});

// vairāk koda 
```