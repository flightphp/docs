# Migrācija uz v3

Atkārtotā saderība lielākoties ir saglabāta, bet ir veiktas dažas izmaiņas, par kurām ir jābūt informētam, migrējot no v2 uz v3.

## Izvades buferēšanas uzvedība (3.5.0)

[Izvades buferēšana](https://stackoverflow.com/questions/2832010/what-is-output-buffering-in-php) ir process, kur izveidotā PHP skripta izvade tiek saglabāta buferī (iekšējā PHP) pirms tā tiek nosūtīta klientam. Tas ļauj jums modificēt izvadi pirms tā tiek nosūtīta klientam.

MVC lietotnē Kontrolieris ir "vadītājs" un tas vadīs skatu darbības. Izvades ģenerēšana ārpus kontroliera (vai Flight gadījumā dažreiz anonīmā funkcija) pārkāpj MVC modeli. Šī izmaiņa ir paredzēta, lai labāk iekļautos MVC modelī un padarītu struktūru paredzamāku un vieglāk lietojamu.

v2 versijā izvades buferēšana tika apstrādāta veidā, kurā tā neatbilda vienmērīgi savam paša izvades buferim, kas padarīja [vienības testēšanu](https://github.com/flightphp/core/pull/545/files#diff-eb93da0a3473574fba94c3c4160ce68e20028e30b267875ab0792ade0b0539a0R42) un [strāvošanu](https://github.com/flightphp/core/issues/413) sarežģītāku. Lielai daļai lietotāju šī izmaiņa varētu nemainīt jūsu darbības. Tomēr, ja jūs izmantojat "echo" saturu ārpus izsaukamiem un kontrolieriem (piemēram, āķī), iespējams, ka jums radīsies problēmas. Satura izvadīšana āķos un pirms struktūra patiešām izpilda šo darbību, varēja darboties iepriekš, bet tas nedarbosies turpmāk.

### Vietas, kur jums varētu rasties problēmas
```php
// index.php
require 'vendor/autoload.php';

// tikai piemērs
define('START_TIME', microtime(true));

function hello() {
	echo 'Sveika, pasaule';
}

Flight::map('hello', 'hello');
Flight::after('hello', function(){
	// tas faktiski ir kārtībā
	echo '<p>Šis Sveika, pasaule teksts jums sniegts ar burtni "S"</p>';
});

Flight::before('start', function(){
	// šīs lietas izraisīs kļūdu
	echo '<html><head><title>Mana lapas nosaukums</title></head><body>';
});

Flight::route('/', function(){
	// tas faktiski ir kārtībā
	echo 'Sveika, pasaule';

	// Tas arī vajadzētu būt kārtībā
	Flight::hello();
});

Flight::after('start', function(){
	// tas izraisīs kļūdu
	echo '<div>Jūsu lapa ielādējās '.(microtime(true) - START_TIME).' sekundēs</div></body></html>';
});
```

### Ieslēgt v2 izstrādes uzvedību

Vai jūs vēl varat saglabāt savu veco kodu tādā formā, neveicot pārrakstīšanu, lai tas darbotos ar v3? Jā, var! Jūs varat ieslēgt v2 izstrādes uzvedību, iestatot `flight.v2.output_buffering` konfigurācijas opciju uz `true`. Tas ļaus jums turpināt izmantot veco izstrādes uzvedību, bet ieteicams to labot turpmāk. V4 versijā struktūrā tas tiks noņemts.

```php
// index.php
require 'vendor/autoload.php';

Flight::set('flight.v2.output_buffering', true);

Flight::before('start', function(){
	// Tagad tas būs kārtībā
	echo '<html><head><title>Mana lapas nosaukums</title></head><body>';
});

// vairāk koda 
```

## Dispečera izmaiņas (3.7.0)

Ja jūs tieši esat izsaukuši statiskās metodes "Dispatcher", piemēram, `Dispatcher::invokeMethod()`, `Dispatcher::execute()`, uc., jums būs jāatjauno jūsu kodu, lai šīs metodes netiktu tieši izsauktas. `Dispatcher` ir pārveidots, lai būtu vairāk objektu orientēts, lai atkarību ievietošanas konteineri varētu tikt izmantoti vieglāk. Ja jums ir nepieciešams izsaukt metodi līdzīgu tam, kā to darīja Dispečers, jūs varat manuāli izmantot kaut ko tādu kā `$result = $class->$method(...$params);` vai `call_user_func_array()` vietā.