# Migrēšana uz v3

Atpakaļsaderība lielākoties ir saglabāta, bet ir daži izmaiņas, par kurām jums jāzina, migrējot no v2 uz v3. Ir daži izmaiņas, kas pārāk daudz nonāca pretrunā ar dizaina modeļiem, tāpēc bija jāveic dažas korekcijas.

## Izvades buferizācijas uzvedība

_v3.5.0_

[Izvades buferizācija](https://stackoverflow.com/questions/2832010/what-is-output-buffering-in-php) ir process, kurā PHP skripta ģenerētais izvade tiek saglabāts buferī (iekšēji PHP), pirms tas tiek nosūtīts klientam. Tas ļauj modificēt izvadi pirms tās nosūtīšanas klientam.

MVC lietojumprogrammā Kontrolētājs ir "vadītājs" un tas pārvalda, ko dara skats. Ja izvade tiek ģenerēta ārpus kontrolētāja (vai Flight gadījumā dažreiz anonīma funkcija), tas pārkāpj MVC modeli. Šī izmaiņa ir lai būtu vairāk saskaņota ar MVC modeli un lai padarītu ietvaru paredzamāku un vieglāk lietojamu.

v2 versijā izvades buferizācija tika apstrādāta tā, ka tā konsekventi neaizvēra savu izvades buferi, kas apgrūtināja [vienības testēšanu](https://github.com/flightphp/core/pull/545/files#diff-eb93da0a3473574fba94c3c4160ce68e20028e30b267875ab0792ade0b0539a0R42) un [straumēšanu](https://github.com/flightphp/core/issues/413). Lielākajai daļai lietotāju šī izmaiņa var pat nebūt ietekme uz jums. Tomēr, ja jūs izvadat saturu ārpus izsaucamajām funkcijām un kontrolētājiem (piemēram, āķī), visticamāk, saskarsieties ar problēmām. Izvades satura āķos un pirms ietvara faktiskās izpildes varēja darboties iepriekš, bet tas nedarbosies turpmāk.

### Kur var rasties problēmas
```php
// index.php
require 'vendor/autoload.php';

// tikai piemērs
define('START_TIME', microtime(true));

function hello() {
	echo 'Hello World';
}

Flight::map('hello', 'hello');
Flight::after('hello', function(){
	// tas faktiski būs kārtībā
	echo '<p>This Hello World phrase was brought to you by the letter "H"</p>';
});

Flight::before('start', function(){
	// tādas lietas izraisīs kļūdu
	echo '<html><head><title>My Page</title></head><body>';
});

Flight::route('/', function(){
	// tas faktiski ir kārtībā
	echo 'Hello World';

	// Arī šis vajadzētu būt kārtībā
	Flight::hello();
});

Flight::after('start', function(){
	// tas izraisīs kļūdu
	echo '<div>Your page loaded in '.(microtime(true) - START_TIME).' seconds</div></body></html>';
});
```

### Ieslēgšana v2 renderēšanas uzvedībai

Vai jūs joprojām varat saglabāt savu veco kodu tāpat, kā tas ir, bez pārstrādes, lai tas darbotos ar v3? Jā, varat! Jūs varat ieslēgt v2 renderēšanas uzvedību, iestatot konfigurācijas opciju `flight.v2.output_buffering` uz `true`. Tas ļaus jums turpināt izmantot veco renderēšanas uzvedību, bet ieteicams to labot turpmāk. v4 versijā ietvarā tas tiks noņemts.

```php
// index.php
require 'vendor/autoload.php';

Flight::set('flight.v2.output_buffering', true);

Flight::before('start', function(){
	// Tagad tas būs kārtībā
	echo '<html><head><title>My Page</title></head><body>';
});

// vairāk koda 
```

## Dispatcher izmaiņas

_v3.7.0_

Ja jūs tieši esat izsaukuši statiskas metodes `Dispatcher` klasei, piemēram, `Dispatcher::invokeMethod()`, `Dispatcher::execute()` utt., jums būs jāatjaunina jūsu kods, lai tieši neizsauktu šīs metodes. `Dispatcher` ir pārveidots, lai būtu vairāk objektorientēts, tāpēc atkarību injekcijas konteineri var tikt izmantoti vieglāk. Ja jums jāizsauc metode līdzīgi kā to darīja Dispatcher, jūs varat manuāli izmantot kaut ko līdzīgu `$result = $class->$method(...$params);` vai `call_user_func_array()`.

## `halt()` `stop()` `redirect()` un `error()` izmaiņas

_v3.10.0_

Noklusētā uzvedība pirms 3.10.0 bija notīrīt gan galvenes, gan atbildes ķermeni. Tas tika mainīts, lai notīrītu tikai atbildes ķermeni. Ja jums jānotīra arī galvenes, jūs varat izmantot `Flight::response()->clear()`.