## Maršrutēšana

> **Piezīme:** Vai vēlaties uzzināt vairāk par maršrutēšanu? Aplūkojiet ["kāpēc izmantot ietvaru?"](learn/why-frameworks) lapu, lai saņemtu detalizētāku skaidrojumu.

Pamata maršrutēšanu "Flight" veic, salīdzinot URL šablonu ar atsauces funkciju vai klases un metodes masīvu.

```php
Flight::route('/', function(){
    echo 'sveika pasaule!';
});
```

> Maršruti tiek sakrāti saskaņā ar to definēšanas secību. Pirmajam sakrāto maršrutēšanai tiks izsaukts.

### Atsauces/Funkcijas
Atsauces var būt jebkāds objekts, kurš ir izsaukams. Tāpēc varat izmantot parasto funkciju:

```php
function hello(){
    echo 'sveika pasaule!';
}

Flight::route('/', 'hello');
```

### Klases
Jūs varat izmantot arī klases statisko metodi:

```php
class Greeting {
    public static function hello() {
        echo 'sveika pasaule!';
    }
}

Flight::route('/', [ 'Greeting','hello' ]);
```

Vai arī, izveidojot vispirms objektu un tad izsaukot metodi:

```php

// Greeting.php
class Greeting
{
    public function __construct() {
        $this->name = 'Jānis Bērziņš';
    }

    public function hello() {
        echo "Sveiki, {$this->name}!";
    }
}

// index.php
$greeting = new Greeting();

Flight::route('/', [ $greeting, 'hello' ]);
// To varat izdarīt arī bez objekta izveides
// Piezīme: konstruktorā netiks ievadīti argumenti
Flight::route('/', [ 'Greeting', 'hello' ]);
```

#### Atkarības iegūšana izmantojot DIC (Dependencies Injection Container)
Ja vēlaties izmantot atkarības iegūšanu izmantojot konteineri (PSR-11, PHP-DI, Dice, utt.), 
tikai vienīgais veids, kā tas ir iespējams izmantojot maršrutus, ir vai nu tieši izveidojot objektu pats
un izmantojot konteineri, lai izveidotu savu objektu, vai arī izmantojot virknes, lai definētu klasi un
metodi, kuru izsaukt. Varat doties uz [Atkarības Iegūšana](learn/extending) lapu, lai iegūtu 
vairāk informācijas. 

Šeit ir īss piemērs:

```php

lietot flight\databāze\PdoWrapper;

// Greeting.php
class Greeting
{
	protected PdoWrapper $pdoWrapper;
	public function __construct(PdoWrapper $pdoWrapper) {
		$this->pdoWrapper = $pdoWrapper;
	}

	public function hello(int $id) {
		// darīt kaut ko ar $this->pdoWrapper
		$vārds = $this->pdoWrapper->fetchField("SELECT vārds FROM lietotāji WHERE id = ?", [ $id ]);
		echo "Sveiki, pasaule! Mani sauc {$vārds}!";
	}
}

// index.php

// Izveidojiet konteineru ar nepieciešamajiem parametriem
// Skatiet Atkarības Iegūšana lapu, lai iegūtu vairāk informācijas par PSR-11
$dice = new \Dice\Dice();

// Neaizmirstiet atkārtoti piešķirt mainīgo ar '$dice = '!!!!!
$dice = $dice->addRule('flight\databāze\PdoWrapper', [
	'koplietojams' => true,
	'constructParams' => [ 
		'mysql:host=localhost;dbname=test', 
		'root',
		'parole'
	]
]);

// Reģistrējiet konteineru apstrādes elementu
Flight::registerContainerHandler(function($klase, $parametri) use ($dice) {
	return $dice->create($klase, $parametri);
});

// Maršruti kā parasti
Flight::route('/sveiki/@id', [ 'Greeting', 'hello' ]);
// vai
Flight::route('/sveiki/@id', 'Greeting->hello');
// vai
Flight::route('/sveiki/@id', 'Greeting::hello');

Flight::start();
```