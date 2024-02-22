# Migrēšana uz v3

Atkārtotā saderība lielākoties ir saglabāta, bet ir dažas izmaiņas, par kurām jums jābūt informētiem, migrējot no v2 uz v3.

## Izvades buferēšana

[Izvades buferēšana](https://stackoverflow.com/questions/2832010/what-is-output-buffering-in-php) ir process, kurā PHP skriptā ģenerētā izvade tiek saglabāta buferī (ietvertā PHP) pirms nosūtīšanas klientam. Tas ļauj jums modificēt izvadi pirms tā tiek nosūtīta klientam.

MVC lietotnē, Kontrolieris ir "pārvaldnieks" un tas pārvalda to, ko apskata. Izvades ģenerēšana ārpus kontroliera (vai reizēm Flights gadījumā anonīmā funkcijā) pārkāpj MVC modeli. Šī izmaiņa ir domāta, lai būtu vairāk saskanīga ar MVC modeli un padarītu šablonu paredzamāku un vieglāk lietojamu.

v2 versijā izvades buferēšana tika apstrādāta tā, ka tā neatkārtoti aizvēra savu pašas izvades buferi, kas padarīja [vienības testēšanu](https://github.com/flightphp/core/pull/545/files#diff-eb93da0a3473574fba94c3c4160ce68e20028e30b267875ab0792ade0b0539a0R42) un [strādāšanu ar straumēm](https://github.com/flightphp/core/issues/413) sarežģītāku. Lielākajai lietotāju daļai šī izmaiņa var nebūt faktiski ietekmējusi. Tomēr, ja jūs izvadāt saturu ārpus izsaukjamām funkcijām un kontroleriem (piemēram, kādā "hook" funkcijā), iespējams, ka jums radīsies problēmas. Izvadot saturu "hook" funkcijās un pirms šablons faktiski tiek izpildīts, tas varēja darboties pagātnē, bet tas nedarbosies turpmāk.

### Kur jums varētu rasties problēmas
```php
// index.php
require 'vendor/autoload.php';

// tikai piemērs
define('SĀKUMA_LAİKS', microtime(true));

function sveiki() {
	echo 'Sveika pasaule';
}

Flight::map('sveiki', 'sveiki');
Flight::after('sveiki', function(){
	// tas būs kārtībā
	echo '<p>Šis Sveika pasaule teksts tika radīts ar burtu "S"</p>';
});

Flight::before('sākums', function(){
	// tādas lietas izraisīs kļūdu
	echo '<html><head><title>Mana lapa</title></head><body>';
});

Flight::route('/', function(){
	// tas praktiski ir kārtībā
	echo 'Sveika pasaule';

	// Tas arī būs kārtībā
	Flight::sveiki();
});

Flight::after('sākums', function(){
	// tas izraisīs kļūdu
	echo '<div>Jūsu lapa ielādējās '.(microtime(true) - SĀKUMA_LAİKS).' sekundēs</div></body></html>';
});
```

### Ieslēgšana v2 Atveides Uzvedībai

Vai jūs vēl varat paturēt savu veco kodu tādu, kāds tas ir, neveicot pārrakstīšanu, lai tas darbotos ar v3? Jā, jūs varat! Jūs varat ieslēgt v2 atveides uzvedību, iestatot `flight.v2.output_buffering` konfigurācijas opciju uz `true`. Tas ļaus jums turpināt izmantot veco atveides uzvedību, bet ir ieteicams to labot nākotnē. v4 versijā šis tiks noņemts.

```php
// index.php
require 'vendor/autoload.php';

Flight::set('flight.v2.output_buffering', true);

Flight::before('sākums', function(){
	// Tagad tas būs kārtībā
	echo '<html><head><title>Mana lapa</title></head><body>';
});

// vairāk koda
```