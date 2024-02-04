# Maršrutēšana

Maršrutēšana Flight tiek veikta, salīdzinot URL šablonu ar atzvana funkciju.

```php
Flight::route('/', function(){
    echo 'sveika pasaule!';
});
```

Atzvana funkcija var būt jebkura objekta, kas ir izsaukama. Tāpēc varat izmantot parasto funkciju:

```php
function sveiki(){
    echo 'sveika pasaule!';
}

Flight::route('/', 'sveiki');
```

Vai arī klasē esošu metodi:

```php
class Sveiciens {
    public static function sveiki() {
        echo 'sveika pasaule!';
    }
}

Flight::route('/', array('Sveiciens','sveiki'));
```

Vai objekta metodi:

```php
class Sveiciens
{
    public function __construct() {
        $this->vards = 'Jānis Bērziņš';
    }

    public function sveiki() {
        echo "Sveiki, {$this->vards}!";
    }
}

$sveiciens = new Sveiciens();

Flight::route('/', array($sveiciens, 'sveiki'));
```

Maršruti tiek salīdzināti ar to secību, kādā tie ir definēti. Pirmajam maršrutam, kas atbilst pieprasījumam, tiks izsaukts.

## Metodes Maršrutēšana

Pēc noklusējuma maršruta šabloni salīdzina pret visām pieprasījuma metodēm. Jūs varat atbildēt uz konkrētām metodēm, ieliekot identifikatoru pirms URL.

```php
Flight::route('GET /', function () {
  echo 'Es saņēmu GET pieprasījumu.';
});

Flight::route('POST /', function () {
  echo 'Es saņēmu POST pieprasījumu.';
});
```

Jūs varat arī piešķirt vairākas metodes vienai atzvana funkcijai, izmantojot `|` atdalītāju:

```php
Flight::route('GET|POST /', function () {
  echo 'Es saņēmu vai nu GET vai POST pieprasījumu.';
});
```

## Parastās Izteiksmes

Jūs varat izmantot regulārās izteiksmes savos maršrutēs:

```php
Flight::route('/lietotājs/[0-9]+', function () {
  // Tas atbilstēs /lietotājs/1234
});
```

## Nosauktie Parametri

Jūs varat norādīt nosauktus parametrus savos maršrutos, kas tiks padoti jūsu atzvana funkcijai.

```php
Flight::route('/@vards/@id', function (string $vards, string $id) {
  echo "sveiki, $vards ($id)!";
});
```

Varat iekļaut arī regulārās izteiksmes ar nosauktajiem parametriem, izmantojot `:` atdalītāju:

```php
Flight::route('/@vards/@id:[0-9]{3}', function (string $vards, string $id) {
  // Tas atbilstēs /bobs/123
  // Bet neatbilstēs /bobs/12345
});
```

Sakritības regex grupas `()` ar nosauktajiem parametriem netiek atbalstītas.

## Iezīmētie Parametri

Jūs varat norādīt nosauktos parametrus savos maršrutos, kas ir neobligāti, lai atbilstu, iesaišu ceļus iekavās.

```php
Flight::route(
  '/blogs(/@gads(/@menesis(/@diena)))',
  function(?string $gads, ?string $menesis, ?string $diena) {
    // Tas atbilstēs šādiem URLS:
    // /blogs/2012/12/10
    // /blogs/2012/12
    // /blogs/2012
    // /blogs
  }
);
```

Jebkuri ne obligātie parametri, kas nav saskaņoti, tiks padoti kā NULL vērtības.

## Vietējie simboli

Sakrišana tiek veikta tikai ar atsevišķiem URL posmiem. Ja vēlaties sakrist vairākiem posmiem, jūs varat izmantot `*` aizstājējzīmi.

```php
Flight::route('/blogs/*', function () {
  // Tas atbilstēs /blogs/2000/02/01
});
```

Lai sasniegtu visus pieprasījumus ar vienu atzvana funkciju, jūs varat:

```php
Flight::route('*', function () {
  // Darīt kaut ko
});
```

## Pāreja

Jūs varat pārnest izpildi uz nākamo atbilstošo maršrutu, atgriežot `true` no savas atzvana funkcijas.

```php
Flight::route('/lietotājs/@vards', function (string $vards) {
  // Pārbaudiet kādu nosacījumu
  ja ($vards !== "Bobs") {
    // Turpiniet uz nākamo maršrutu
    atgriezt true;
  }
});

Flight::route('/lietotājs/*', function () {
  // Tas tiks izsaukts
});
```

## Maršruta Informācija

Ja vēlaties izpētīt atbilstošo maršrutu informāciju, jūs varat pieprasīt maršruta objektu, lai tas tiktu padots jūsu atzvana funkcijai, padodot `true` kā trešo parametru maršruta metodē. Maršruta objekts vienmēr būs pēdējais parametrs, kas padots jūsu atzvana funkcijai.

```php
Flight::route('/', function(\flight\net\Route $route) {
  // Masīvs ar atbilstošajiem HTTP metodiem
  $route->metodes;

  // Nosauktie parametri
  $route->parametri;

  // Sakrītošais regulārais izteiksmes
  $route->regex;

  // Saturs jebkuram izmantotajam '*' URL šablonā
  $route->splat;
}, true);
```

## Maršruta Grupēšana

Var gadīties, ka vēlaties grupēt saistītos maršrutus kopā (piemēram, `/api/v1`). To var izdarīt, izmantojot `group` metodi:

```php
Flight::group('/api/v1', function () {
  Flight::route('/lietotāji', function () {
	// Atbilst /api/v1/lietotāji
  });

  Flight::route('/ieņemšanas', function () {
	// Atbilst /api/v1/ieņemšanas
  });
});
```

Varat iegult grupas grupās:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() iegūst mainīgos, tas neiestata maršrutu! Skatiet objekta kontekstu zemāk
	Flight::route('GET /lietotāji', function () {
	  // Atbilst GET /api/v1/lietotāji
	});

	Flight::post('/ieņemšanas', function () {
	  // Atbilst POST /api/v1/ieņemšanas
	});

	Flight::put('/ieņemšanas/1', function () {
	  // Atbilst PUT /api/v1/ieņemšanas
	});
  });
  Flight::group('/v2', function () {

	// Flight::get() iegūst mainīgos, tas neiestata maršrutu! Skatiet objekta kontekstu zemāk
	Flight::route('GET /lietotāji', function () {
	  // Atbilst GET /api/v2/lietotāji
	});
  });
});
```

### Grupēšana ar Objekta Kontekstu

Joprojām varat izmantot maršruta grupēšanu, izmantojot `Engine` objektu šādā veidā:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {
  $router->get('/lietotāji', function () {
	// Atbilst GET /api/v1/lietotāji
  });

  $router->post('/ieņemšanas', function () {
	// Atbilst POST /api/v1/ieņemšanas
  });
});
```

## Maršruta Pasākšana

Jūs varat piešķirt maršrutam aliasu, lai URL varētu dynamiški tikt ģenerēta vēlāk jūsu kodā (piemēram, šablona gadījumā).

```php
Flight::route('/lietotāji/@id', function($id) { echo 'lietotājs:'.$id; }, false, 'lietotāja_skatījums');

// vēlāk kādā vietā kodā
Flight::getUrl('lietotāja_skatījums', [ 'id' => 5 ]); // atgriezīs '/lietotāji/5'
```

Šis ir it īpaši noderīgs, ja jūsu URL gadījumā ir nepieciešama izmaiņa. Iepriekš minētajā piemērā, pieņemsim, ka lietotāji tika pārvietoti uz `/admin/lietotāji/@id` vietā.
Ar aliasu iegāšanu, Jums nav jāmaina visur, kur atsaucaties uz aliase, jo aliass tagad atgriezīs `/admin/lietotāji/5`, kā minēts iepriekšējo piemēru.

Maršruta aliasēšana joprojām darbojas arī grupās:

```php
Flight::group('/lietotāji', function() {
    Flight::route('/@id', function($id) { echo 'lietotājs:'.$id; }, false, 'lietotāja_skatījums');
});


// vēlāk kādā vietā kodā
Flight::getUrl('lietotāja_skatījums', [ 'id' => 5 ]); // atgriezīs '/lietotāji/5'
```

## Maršruta Starpnieki
Flight atbalsta maršruta un grupas maršruta starpniekus. Starpnieks ir funkcija, kas tiek izpildīta pirms (vai pēc) maršruta atzvana. Tas ir lielisks veids, kā pievienot API autentifikācijas pārbaudes savā kodā vai validēt, vai lietotājam ir atļauja piekļūt maršrutam.

Šeit ir pamata piemērs:

```php
// Ja nodrošināt tikai anonīmu funkciju, tā tiks izpildīta pirms maršruta atzvana. 
// nav "pēc" maršruta starpnieku funkciju, izņemot klases (skatīt zemāk)
Flight::route('/ceļš', function() { echo ' Šeit esmu!'; })->addMiddleware(function() {
	echo 'Starpposms pirmais!';
});

Flight::start();

// Tas izvadīs "Starpposms pirmais! Šeit esmu!"
```

Par maršruta starpniekiem ir dažas ļoti svarīgas piezīmes, ko jums vajadzētu zināt pirms to lietošanas:
- Maršruta starpnieku funkcijas tiek izpildītas tajā secībā, kurā tās tiek pievienotas maršrutam. Izpildīšana ir līdzīga tam, kā to apstrādā [Slim Framework](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work).
   - Pirms tiek izpildīti secībā, un Pēc tam tiek izpildīti pretējā secībā.
- Ja jūsu maršruta starpnieka funkcija atgriež `false`, visa izpilde tiek apturēta un tiek izvadīta 403 Aizliegts kļūda. Jums varētu vēlēties šo apstrādāt elegantiāk ar `Flight::redirect()` vai kaut ko līdzīgu.
- Ja jums ir nepieciešami parametri no jūsu maršruta, tie tiks padoti kā viena masīva parametrs jūsu starpnieka funkcijai. (`function($params) { ... }` vai `public function before($params) {}`). Iemesls tam ir tas, ka varat strukturēt savus parametrus grupās, un dažādās šajās grupās jūsu parametri faktiski var parādīties citā secībā, kas break starpnieka funkciju, norādot nepareizo parametru. Šādā veidā jūs varat piekļūt tiem pēc nosaukuma nevis pēc pozīcijas.

### Starpnieku Klases

Starpniekus ir iespējams reģistrēt arī kā klasi. Ja jums ir nepieciešama "pēc" funkcionalitāte, jums jāizmanto klase.

```php
class ManaStarpposmaKlase {
	public function before($params) {
		echo 'Starpposms pirmais!';
	}

	public function after($params) {
		echo 'Starpposms beidzams!';
	}
}

$ManaStarpposmaKlase = new ManaStarpposmaKlase();
Flight::route('/ceļš', function() { echo ' Šeit esmu! '; })->addMiddleware($ManaStarpposmaKlase); // arī ->addMiddleware([ $ManaStarpposmaKlase, $ManaStarpposmaKlase2 ]);

Flight::start();

// Tas parādīs "Starpposms pirmais! Šeit esmu! Starpposms beidzams!"
```

### Starpnieku Grupas

Jūs varat pievienot maršruta grupu, un tad katram šīs grupas maršrutam būs vienāda starpnieku funkcionalitāte arī. Tas ir noderīgi, ja ir nepieciešams grupēt kopā daudzus maršrutus, piemēram, pēc Autentifikācijas starpnieku pārbaudes API atslēgas galvu.

```php

// pievienots grupas metodei
Flight::group('/api', function() {
    Flight::route('/lietotāji', function() { echo 'lietotāji'; }, false, 'lietotāji');
	Flight::route('/lietotāji/@id', function($id) { echo 'lietotājs:'.$id; }, false, 'lietotāja_skatījums');
}, [ jauna ApiAuthMiddleware() ]);