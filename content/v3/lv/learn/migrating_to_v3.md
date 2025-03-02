# Migrācija uz v3

Atgriezeniskā saderība lielākoties ir saglabāta, bet ir dažas izmaiņas, par kurām jums vajadzētu zināt, migrējot no v2 uz v3.

## Rezultātu buferēšanas uzvedība (3.5.0)

[Rezultātu buferēšana](https://stackoverflow.com/questions/2832010/what-is-output-buffering-in-php) ir process, kurā PHP skriptā ģenerētais izvads tiek saglabāts buferī 
(iekšējs PHP) pirms tiek nosūtīts klientam. Tas ļauj jums modificēt izvadu pirms tā nosūtīšanas klientam.

MVC lietojumprogrammā, vadītājs ir "pārvaldnieks" un tas pārvalda to, ko darbojas skats. Izvades ģenerēšana ārpus vadītāja 
(vai Flight gadījumā dažreiz anonīmā funkcijā) pārkāpj MVC modeli. Šī izmaiņa ir domāta, lai būtu labāka saskaņa ar MVC modeli 
un padarītu struktūru paredzamāku un vieglāk lietojamu.

v2 versijā rezultātu buferēšana tika apstrādāta tādā veidā, ka tā konsistenti neaizvēra savu paša izvades buferi, un tas padarīja 
[vienības pārbaudi](https://github.com/flightphp/core/pull/545/files#diff-eb93da0a3473574fba94c3c4160ce68e20028e30b267875ab0792ade0b0539a0R42) 
un [strāmošanu](https://github.com/flightphp/core/issues/413) sarežģītāku. Lielai daļai lietotāju šī izmaiņa varbūt patiesībā 
ietekmēt jūs. Tomēr, ja jūs izvadāt saturu ārpus izsaukamajiem un vadītājiem (piemēram, āķī), jums, visticamāk, 
radīsies problēmas. Satura izvadīšana āķos un pirms struktūra faktiski izpilda to varēja darboties pagātnē, bet tas nedarbosies 
turpmāk.

### Kur var rasties problēmas
```php
// index.php
require 'vendor/autoload.php';

// tikai piemērs
define('START_TIME', microtime(true));

function hello() {
	echo 'Sveika, pasaule!';
}

// papildus kods
```

### Ieslēgt v2 renderēšanas uzvedību

Vai jūs joprojām varat paturēt savu veco kodu tādu, kāds tas ir, neveicot pārrakstīšanu, lai tas darbotos ar v3? Jā, varat! Jūs varat ieslēgt v2 
renderēšanas uzvedību, iestatot konfigurācijas opciju `flight.v2.output_buffering` uz `true`. Tas ļaus jums turpināt izmantot veco renderēšanas uzvedību, 
bet ieteicams to labot turpmāk. v4 versijā no struktūras tas tiks noņemts.

```php
// index.php
require 'vendor/autoload.php';

Flight::set('flight.v2.output_buffering', true);

// papildus kods
```

## Dispecera izmaiņas (3.7.0)

Ja jūs tieši esat izsaucis statiskās metodes `Dispatcher`, tādas kā `Dispatcher::invokeMethod()`, `Dispatcher::execute()`, utt., 
jums būs jāatjaunina jūsu kods, lai tieši nesaucu šīs metodes. `Dispatcher` ir pārveidots, lai būtu vairāk objektu orientēts, tāpēc 
atkarību ievades konteinerus var izmantot vieglāk. Ja jums ir nepieciešams izsaukt metodi līdzīgi tam, kā to darīja Dispecers, jūs 
varat manuāli izmantot kaut ko līdzīgu `$rezultāts = $klase->$metode(...$parametri);` vai `call_user_func_array()` vietā.

## `halt()` `stop()` `redirect()` un `error()` izmaiņas (3.10.0)

Noklusējuma uzvedība pirms 3.10.0 bija notīrīt gan galvenes, gan atbildes korpusu. Tas tika mainīts, lai notīrītu tikai atbildes korpusu. 
Ja jums ir nepieciešams notīrīt arī galvenes, jūs varat izmantot `Flight::response()->clear()`.