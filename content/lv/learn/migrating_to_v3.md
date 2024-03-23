# Migrēšana uz v3

Atgriezeniskā saderība lielākoties ir saglabāta, bet ir dažas izmaiņas, par kurām jums jāņem vērā, migrējot no v2 uz v3.

## Izvades buferēšanas uzvedība (3.5.0)

[Izvades buferēšana](https://stackoverflow.com/questions/2832010/what-is-output-buffering-in-php) ir process, kurā PHP skripta ģenerētā izvade tiek saglabāta buferī (iekšējā PHP) pirms tā tiek nosūtīta klientam. Tas ļauj jums modificēt izvadi pirms tā tiek nosūtīta klientam.

MVC lietojumprogrammā Kļūdu izvadītājs ir "vadītājs" un tas vadīt to, ko skats dara. Izvade, kas tiek ģenerēta ārpus vadītāja (vai dažreiz Fly ietvars gadījumā anonīmā funkcija) pārkāpj MVC modeli. Šī izmaiņa ir veikta, lai iegūtu lielāku saderību ar MVC modeli un padarītu ietvaru paredzamāku un vieglāk lietojamu.

v2, izvades buferēšana tika apstrādāta tā, ka tā konsistenti neatvēra savu izvades buferi, kas padarīja [mērvienību testēšanu](https://github.com/flightphp/core/pull/545/files#diff-eb93da0a3473574fba94c3c4160ce68e20028e30b267875ab0792ade0b0539a0R42) un [straumēšanu](https://github.com/flightphp/core/issues/413) sarežģītāku. Lielākajai vairumu lietotāju šī izmaiņa varētu neietekmēt praktiski. Tomēr, ja jūs atgriežat saturu ārpusizsaucamajiem un vadītājiem (piemēram, āķī), iespējams, ka uz jums gaida problēmas. Uzskaitot saturu āķos un pirms ietvara faktiskā izpildes agrāk varēja darboties, bet nedarbosies turpmāk.

### Vietas, kur var būt problēmas
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
	// tas faktiski būs kārtībā
	echo '<p>Šo Sveika pasaule frazi jums sagādā burti "S"</p>';
});

Flight::before('sākums', function(){
	// lietas, kas izskatās šādi, radīs kļūdu
	echo '<html><head><title>Mana lapa</title></head><body>';
});

Flight::route('/', function(){
	// tas faktiski ir tikai labi
	echo 'Sveika pasaule';

	// Tas arī būtu tikai labi
	Flight::hello();
});

Flight::after('sākums', function(){
	// tas radīs kļūdu
	echo '<div>Jūsu lapa ielādējās '.(microtime(true) - START_TIME).' sekundes</div></body></html>';
});
```

### Ieslēgt v2 izvades buferēšanas uzvedību

Vai joprojām varat paturēt savu veco kodu tāpat kā tas ir, neveicot pārrakstīšanu, lai tas strādātu ar v3? Jā, jūs varat! Jūs varat ieslēgt v2 izvades buferēšanas uzvedību, iestatot konfigurācijas opciju `flight.v2.output_buffering` uz `true`. Tas ļaus jums turpināt izmantot veco renderēšanas uzvedību, bet ieteicams to labot turpmāk. v4 ietvarā tas tiks noņemts.

```php
// index.php
require 'vendor/autoload.php';

Flight::set('flight.v2.output_buffering', true);

Flight::before('sākums', function(){
	// Tagad tas būs tikai kārtībā
	echo '<html><head><title>Mana lapa</title></head><body>';
});

// vairāk koda 
```

## Dispečera izmaiņas (3.7.0)

Ja jūs tieši esat izsaukdami statiskās metodes `Dispatcher`, piemēram, `Dispatcher::invokeMetode()`, `Dispečera::execute()`, utt., jums būs jāatjaunina savs kods, lai neizsauktu šīs metodes tieši. `Dispatcher` ir pārveidots, lai būtu vairāk objektu orientēts, tādējādi Atkarību ieviešanas konteineri var tikt izmantoti vieglāk. Ja jums ir nepieciešams izsaukt metodi, līdzīgu tam, kā to darīja Dispečers, jūs varat manuāli izmantot kaut ko līdzīgu `$rezultāts = $klase->$metode(...$parametri);` vai `call_user_func_array()` vietā.