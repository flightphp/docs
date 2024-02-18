# Drošība

Drošība ir liela problēma, kad runa ir par tīmekļa lietojumprogrammām. Jums ir jānodrošina, ka jūsu lietojumprogramma ir droša un jūsu lietotāju dati ir drošībā. Flight nodrošina vairākas funkcijas, lai palīdzētu nodrošināt jūsu tīmekļa lietojumprogrammas.

## Galvenes

HTTP galvenes ir viens no vieglākajiem veidiem, kā nodrošināt jūsu tīmekļa lietojumprogrammas. Jūs varat izmantot galvenes, lai novērstu klikšķināšanas ietekmi, XSS un citus uzbrukumus. Ir vairākas metodes, kā pievienot šīs galvenes savai lietojumprogrammai.

### Pievienot manuāli

Jūs varat manuāli pievienot šīs galvenes, izmantojot `header` metodi objektam `Flight\Response`.
```php
// Iestatiet X-Frame-Options galveni, lai novērstu klikšķināšanas ietekmi
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Iestatiet Content-Security-Policy galveni, lai novērstu XSS
// Piezīme: šī galvene var kļūt ļoti sarežģīta, tāpēc jums vajadzēs
// konsultēties ar piemēriem internetā savai lietojumprogrammai
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Iestatiet X-XSS-Protection galveni, lai novērstu XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Iestatiet X-Content-Type-Options galveni, lai novērstu MIME sniffing
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Iestatiet Referrer-Policy galveni, lai kontrolētu, cik daudz referrera informācijas tiek nosūtīts
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Iestatiet Strict-Transport-Security galveni, lai piespiestu HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
```

Šīs var pievienot virsotnē jūsu `bootstrap.php` vai `index.php` failos.

### Pievienot kā filtru

Jūs varat tās pievienot arī kā filtru/iekodu līdzīgi šādam: 

```php
// Pievienojiet galvenes filtrā
Flight::before('start', function() {
	Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');
	Flight::response()->header("Content-Security-Policy", "default-src 'self'");
	Flight::response()->header('X-XSS-Protection', '1; mode=block');
	Flight::response()->header('X-Content-Type-Options', 'nosniff');
	Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');
	Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
});
```

### Pievienot kā starpviela

Jūs varat tās pievienot arī kā starpvielas klasi. Tas ir labs veids, kā uzturēt kodu tīru un sakārtotu.

```php
// app/middleware/SecurityHeadersMiddleware.php

namespace app\middleware;

class SecurityHeadersMiddleware
{
	public function before(array $params): void
	{
		Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');
		Flight::response()->header("Content-Security-Policy", "default-src 'self'");
		Flight::response()->header('X-XSS-Protection', '1; mode=block');
		Flight::response()->header('X-Content-Type-Options', 'nosniff');
		Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');
		Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
	}
}

// index.php vai kur jums ir savas maršruta tabulas
// FYI, šis tukšais simbolu grupu darbojas kā globālais starpvielas
// visiem maršrutiem. Protams, jūs varētu darīt to pašu un pievienot
// to tikai konkrētiem maršrutiem.
Flight::group('', function(Router $router) {
	$router->get('/lietotāji', [ 'LietotājuKontrolieris', 'iegūtLietotājus' ]);
	// vairāki maršruti
}, [ new SecurityHeadersMiddleware() ]);
```


## Pārkrāptā vietas prasījuma krāpšana (CSRF)

Pārkrāptā vietas prasījuma krāpšana (CSRF) ir veids uzbrukumam, kur ļaunatkal webvietne var pieprasīt lietotāja pārlūkam nosūtīt prasījumu uz jūsu vietni. Tas var tikt izmantots, lai veiktu darbības jūsu vietnē bez lietotāja zināšanām. Flight nenodrošina iebūvētu CSRF aizsardzības mehānismu, bet jūs varat viegli ieviest savu, izmantojot starprogrammatūras.

### Iestatīšana

Vispirms jums jāģenerē CSRF žetons un jāsaglabā tas lietotāja sesijā. Tad jūs varat izmantot šo žetonu savos veidos un pārbaudīt to, kad veidlapa tiek iesniegta.

```php
// Ģenerējiet CSRF žetonu un saglabājiet to lietotāja sesijā
// (ja jūs esat izveidojis sesijas objektu un to pievienojis Flight)
// Jums ir nepieciešams ģenerēt tikai vienu žetonu uz sesiju (tāpēc tas darbosies
// pārkājienu un prasījumiem daudzās cilnēs vienam lietotājam)
ja(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

```html
<!-- Izmantojiet CSRF žetonu savā veidlapā -->
<form metode="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- citas formu lauksturi -->
</form>
```

#### Izmantojot Latte

Jūs arī varat noteikt pielāgotu funkciju, lai izvadītu CSRF žetonu savos Latte veidlapās.

```php
// Iestatiet pielāgotu funkciju, lai izvadītu CSRF žetonu
// Piezīme: Skatu ir konfigurējis ar Latte kā skatu dzinēju
Flight::view()->addFunction('csrf', function() {
	$csrfToken = Flight::session()->get('csrf_token');
	return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
});
```

Un tagad savās Latte veidlapās jūs varat izmantot `csrf()` funkciju, lai izvadītu CSRF žetonu.

```html
<form metode="post">
	{csrf()}
	<!-- citas formu lauksturi -->
</form>
```

Īss un vienkāršs, vai ne?

### Pārbaudiet CSRF žetonu

Jūs varat pārbaudīt CSRF žetonu, izmantojot notikumu filtrus:

```php
// Šī starprogramma pārbauda, vai pieprasījums ir POST prasījums un, ja tā ir, tā pārbauda, vai CSRF žetons ir derīgs
Flight::before('start', function() {
	ja(Flight::request()->metode == 'POST') {

		// noķeriet CSRF žetonu no formas vērtībām
		$token = Flight::request()->data->csrf_token;
		ja($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Nederīgs CSRF žetons');
		}
	}
});
```

Vai arī varat izmantot starprogrammas klasi:

```php
// app/middleware/CsrfMiddleware.php

namespace app\middleware;

class CsrfMiddleware
{
	public function before(array $params): void
	{
		ja(Flight::request()->metode == 'POST') {
			$token = Flight::request()->data->csrf_token;
			ja($token !== Flight::session()->get('csrf_token')) {
				Flight::halt(403, 'Nedersīgs CSRF žetons');
			}
		}
	}
}

// index.php vai kur jums ir savas maršruta tabulas
Flight::group('', function(Router $router) {
	$router->get('/lietotāji', [ 'LietotājuKontrolieris', 'iegūtLietotājus' ]);
	// vairāki maršruti
}, [ new CsrfMiddleware() ]);
```


## Pārkājienu vietas ievirzīšana (XSS)

Pārkājienu vietas ievirzīšana (XSS) ir veids uzbrukumam, kur ļauna webvietne var ievietot kodu jūsu vietnē. Lielākā daļa šo iespēju rodas no veidlapu vērtībām, ko aizpildīs jūsu galaplietotāji. Jums **nevajadzētu** uzticēties savu lietotāju izvadei! Vienmēr pieņemiet, ka visi no viņiem ir labākie hakkeri pasaulē. Viņi var ievietot ļaunu JavaScript vai HTML jūsu lapā. Šo kodu var izmantot, lai zagtu informāciju no jūsu lietotājiem vai veiktu darbības jūsu vietnē. Izmantojot Flight skatu klasi, jūs varat viegli izvairīties no XSS uzbrukumiem.

```php
// Pieņemsim, ka lietotājs ir gudrs un cenšas to izmantot kā savu vārdu
vārds = '<script>alert("XSS")</script>';

// Tas izvairīs izvadi
Flight::view()->set('name', vārds);
// Tas izvadīs: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Ja izmantojat kaut ko līdzīgu kā Latte reģistrēts kā jūsu skata klase, tas arī automātiski izvairīsies no tā.
Flight::view()->render('veidne', ['name' => vārds]);
```

## SQL injekcija

SQL injekcija ir veids uzbrukumam, kur ļaunprātīgs lietotājs var ievietot SQL kodu jūsu datu bāzē. Tas var tikt izmantots, lai zagtu informāciju no jūsu datu bāzes vai veiktu darbības jūsu datu bāzē. Atkal jums **nebūtu** jāuzticas savu lietotāju ievadei! Vienmēr pieņemiet, ka viņi ir asinīs. Jūs varat izmantot sagatavotās izteiksmes savos `PDO` objektos, lai novērstu SQL injekcijas.

```php
// Tēlot, ka esat Flight::db() reģistrējis kā savu PDO objektu
paziņojums = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
paziņojums->izpildīt([':username' => lietotājvārds]);
lietotāji = paziņojums->fetchAll();

// Ja izmantojat PdoWrapper klasi, to var viegli darīt vienā rindiņā
lietotāji = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => lietotājvārds ]);

// Jūs varat darīt to pašu ar PDO objektu, izmantojot ? vietātājos
paziņojums = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ lietotājvārds ]);

// Tikai soliet, ka jūs nekad NEKAD nedarīsit kaut ko līdzīgu šim...
lietotāji = Flight::db()->fetchAll("SELECT * FROM users WHERE username = '{$username}' LIMIT 5");
// jo kas notiek, ja $lietotājvārds = "' OR 1=1; -- "; 
// Pēc tam, kad vaicājums tiek izveidots, tas izskatās šādi
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5
// Tas izskatās dīvaini, bet tas ir derīgs vaicājums, kas darbosies. Patiesībā,
// tā ir ļoti izplatīta SQL injekcijas uzbrukumu forma, kas atgriezīs visus lietotājus.
```

## CORS

Cross-Origin Resource Sharing (CORS) ir mehānisms, kas ļauj daudziem resursiem (piemēram, fontiem, JavaScript utt.) uz tīmekļa lapas tikt pieprasītiem no citas domēna ārpus resursa izcelsmes domēna. Flight neietver iebūvētu funkcionalitāti, bet to var viegli apstrādāt ar starprogrammas vai notikumu filtru līdzīgi kā CSRF.

```php
// app/middleware/CorsMiddleware.php

namespace app\middleware;

class CorsMiddleware
{
	public function before(array $params): void
	{
		atbilde = Flight::response();
		if (isset($_SERVER['HTTP_ORIGIN'])) {
			this->allowOrigins();
			atbilde->header('Access-Control-Allow-Credentials: true');
			atbilde->header('Access-Control-Max-Age: 86400');
		}

		if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
			if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
				atbilde->header(
					'Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS'
				);
			}
			if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
				atbilde->header(
					"Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}"
				);
			}
			atbilde->send();
			iziešana(0);
		}
	}

	privāts atļautIzcelsmās(): void
	{
		// pielāgojiet savas atļaujamos resursus šeit.
		atļauts = [
			'capacitor://localhost',
			'ionic://localhost',
			'http://localhost',
			'http://localhost:4200',
			'http://localhost:8080',
			'http://localhost:8100',
		];

		ja (in_array($_SERVER['HTTP_ORIGIN'], atļauts)) {
			atbilde = Flight::response();
			atbilde->header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
		}
	}
}

// index.php vai kur jums ir savas maršruta tabulas
Flight::route('/lietotāji', function() {
	lietotāji = Flight::db()->fetchAll('SELECT * FROM users');
	Flight::json(lietotāji);
})->addMiddleware(new CorsMiddleware());
```

## Secinājums

Drošība ir liela problēma un ir svarīgi nodrošināt, ka jūsu tīmekļa lietojumprogrammas ir drošas. Flight nodrošina vairākas funkcijas, lai palīdzētu nodrošināt jū# Drošība

Drošība ir liela problēma, kad runa ir par tīmekļa lietojumprogrammām. Jums ir jāpārliecinās, ka jūsu lietojumprogramma ir droša un ka jūsu lietotāju dati ir drošībā. Flight nodrošina virkni funkciju, lai palīdzētu jums nodrošināt savas tīmekļa lietojumprogrammas.

## Galvenes

HTTP galvenes ir viens no vieglākajiem veidiem, kā nodrošināt jūsu tīmekļa lietojumprogrammas. Jūs varat izmantot galvenes, lai novērstu klikšķināšanas ietekmi, XSS un citus uzbrukumus. Ir vairāki veidi, kā pievienot šīs galvenes savai lietojumprogrammai.

### Pievienot Manuāli

Jūs varat manuāli pievienot šīs galvenes, izmantojot `header` metodi objektam `Flight\Response`.
```php
// Iestatiet X-Frame-Options galveni, lai novērstu klikšķināšanas ietekmi
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Iestatiet Content-Security-Policy galveni, lai novērstu XSS
// Piezīme: šai galvenei var kļūt ļoti sarežģīts, līdz ar to jums vajadzēs
// konsultēties ar piemēriem internetā savai lietojumprogrammai
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Iestatiet X-XSS-Protection galveni, lai novērstu XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Iestatiet X-Content-Type-Options galveni, lai novērstu MIME sniffing
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Iestatiet Referrer-Policy galveni, lai kontrolētu, cik daudz referrera informācijas tiek nosūtīts
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Iestatiet Strict-Transport-Security galveni, lai piespiestu HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
```

Šos var pievienot jūsu `bootstrap.php` vai `index.php` failos.

### Pievienot Kā Filtru

Jūs arī varat pievienot tos kā filtru/apavu līdzīgi šim: 

```php
// Pievienojiet galvenes filtrā
Flight::before('start', function() {
	Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');
	Flight::response()->header("Content-Security-Policy", "default-src 'self'");
	Flight::response()->header('X-XSS-Protection', '1; mode=block');
	Flight::response()->header('X-Content-Type-Options', 'nosniff');
	Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');
	Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
});
```

### Pievienot Kā Vidējo

Jūs varat pievienot tos arī kā vidējo klasi. Tas ir labs veids, kā uzturēt jūsu kodu tīru un sakārtotu.

```php
// app/middleware/SecurityHeadersMiddleware.php

namespace app\middleware;

class SecurityHeadersMiddleware
{
	public function before(array $params): void
	{
		Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');
		Flight::response()->header("Content-Security-Policy", "default-src 'self'");
		Flight::response()->header('X-XSS-Protection', '1; mode=block');
		Flight::response()->header('X-Content-Type-Options', 'nosniff');
		Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');
		Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
	}
}

// index.php vai jebkur, kur ir jūsu maršruta tabulas
// FYI, šī tukšā simbolu grupa darbojas kā globālais vidējais jūsu maršrutiem. Protams, jūs varētu darīt to pašu un pievienot to tikai konkrētiem maršrutiem.
Flight::group('', function(Router $router) {
	$router->get('/lietotāji', [ 'LietotājuKontrolieris', 'iegūtLietotājus' ]);
	// vairāki maršruti
}, [ new SecurityHeadersMiddleware() ]);
```


## Pārkāpuma vietas pieprasījuma krāpšana (CSRF)

Pārkāpuma vietas pieprasījuma krāpšana (CSRF) ir veids uzbrukumam, kur ļauna tīmekļa vietne var likt lietotāja pārlūkam nosūtīt pieprasījumu uz jūsu tīmekļa vietni. To var izmantot, lai veiktu darbības jūsu vietnē bez lietotāja zināšanām. Flight nesniedz iebūvētu CSRF aizsardzības mehānismu, bet jūs varat viegli izveidot savu, izmantojot starprogrammu.

### Iestatīšana

Vispirms jums jāģenerē CSRF žetons un jāsaglabā tas lietotāja sesijā. Pēc tam varat izmantot šo žetonu savos formās un pārbaudīt to, kad forma tiek iesniegta.

```php
// Ģenerējiet CSRF žetonu un saglabājiet to lietotāja sesijā
// (pārliecieties, ka esat izveidojis sesijas objektu un to piesaistījis Flight)
// Jums jāģenerē tikai viens žetons sesijai (lai tas darbotos
// vairākās cilnēs un pieprasījumos vienam lietotājam)
ja(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

```html
<!-- Izmantojiet CSRF žetonu savā formā -->
<form metode="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- citas formas lauki -->
</form>
```

#### Izmantojot Latte

Jūs arī varat iestatīt pielāgotu funkciju, lai izvadītu CSRF žetonu savos Latte veidņos.

```php
// Uzstādiet pielāgotu funkciju, lai izvadītu CSRF žetonu
// Piezīme: Skatu ir konfigurēts ar Latte kā skatu dzinēju
Flight::view()->addFunction('csrf', function() {
	$csrfToken = Flight::session()->get('csrf_token');
	return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
});
```

Un tagad savos Latte veidnēs varat izmantot `csrf()` funkciju, lai izvadītu CSRF žetonu.

```html
<form metode="post">
	{csrf()}
	<!-- citas formas lauki -->
</form>
```

Īss un vienkāršs, vai ne?

### Pārbaudiet CSRF Žetonu

Jūs varat pārbaudīt CSRF žetonu, izmantojot notikumu filtrus:

```php
// Šī starprogramma pārbauda, vai pieprasījums ir POST pieprasījums un, ja ir, pārbauda, vai CSRF žetons ir derīgs
Flight::before('start', function() {
	if(Flight::request()->metode == 'POST') {

		// noķeriet CSRF žetonu no formas vērtībām
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Nederīgs CSRF žetons');
		}
	}
});
```

Vai arī varat izmantot starprogrammas klasi:

```php
// app/middleware/CsrfMiddleware.php

namespace app\middleware;

class CsrfMiddleware
{
	public function before(array $params): void
	{
		if(Flight::request()->metode == 'POST') {
			$token = Flight::request()->data->csrf_token;
			if($token !== Flight::session()->get('csrf_token')) {
				Flight::halt(403, 'Nederīgs CSRF žetons');
			}
		}
	}
}

// index.php vai jebkur, kur ir jūsu maršruta tabulas
Flight::group('', function(Router $router) {
	$router->get('/lietotāji', [ 'LietotājuKontrolieris', 'iegūtLietotājus' ]);
	// vairāki maršruti
}, [ new CsrfMiddleware() ]);
```


## Pārkāpuma vietas skriptu ievešana (XSS)

Pārkāpuma vietas skriptu ievešana (XSS) ir veids uzbrukumam, kur ļauna tīmekļa vietne var ievietot kodu jūsu vietnē. Lielākā daļa šo iespēju rodas no formu vērtībām, ko aizpildīs jūsu gala lietotāji. Jūs **nevienmēr** nedrīkstat uzticēties lietotāju izvadei! Vienum vienmēr pieņemiet, ka visi no viņiem ir labākie hakkeri pasaulē. Viņi var ievietot ļaunu JavaScript vai HTML jūsu lapā. Šo kodu var izmantot, lai zagtu informāciju no jūsu lietotājiem vai veiktu darbības jūsu vietnē. Izmantojot Flight skata klasi, jūs viegli varat bēgt no XSS uzbrukumiem.

```php
// Cilvēki ir gudri un mēģina to izmantot kā sava vārda ievadi
vārds = '<script>alert("XSS")</script>';

// Tas izvairīsies no izvades
Flight::view()->set('name', vārds);
// Tas izvadīs: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Ja izmantojat kaut ko līdzīgu kā Latte reģistrēts kā savu skata klasi, tas arī automātiski izvairīsies no tā.
Flight::view()->render('veidne', ['name' => vārds]);
```

## SQL injekcija

SQL injekcija ir veids uzbrukumam, kur ļaunprātīgs lietotājs var injicēt SQL kodu jūsu datu bāzē. To var izmantot, lai zagtu informāciju no jūsu datu bāzes vai veiktu darbības jūsu datu bāzē. Atkal jūs nedrīkstat uzticēties lietotāju ievadei! Vienum vienmēr pieņemiet, ka visi no viņiem ir asinis. Jūs varat izmantot sagatavotas izteiksmes savos `PDO` objektos, lai novērstu SQL injekcijas.

```php
// Pieņemsim, ka jums ir Flight::db() reģistrēts kā jūsu PDO objekts
paziņojums = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
paziņojums->execute([':username' => lietotājvārds]);
lietotāji = paziņojums->fetchAll();

// Ja izmantojat PdoWrapper klasi, to var viegli izdarīt vienā rindiņā
lietotāji = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => lietotājvārds ]);

// Jūs varat veikt to pašu ar PDO objektu ar ? atrašanās vietām
paziņojums = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ lietotājvārds ]);

// Soliet, ka nekad NENOMAĪŠIET darīt kaut ko tādu kā šo...
lietotāji = Flight::db()->fetchAll("SELECT * FROM users WHERE username = '{$username}' LIMIT 5");
// jo kas notiek, ja $username = "' OR 1=1; -- "; 
// Pēc vaicājuma izstrādes tas izskatās šādi
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5
// Tas izskatās dīvaini, bet tas ir derīgs vaicājums, kas darbosies. Patiesībā,
// tas ir ļoti izplatīts SQL injekcijas uzbrukums, kas atgriezīs visus lietotājus.
```

## CORS

Cross-Origin Resource Sharing (CORS) ir mehānisms, kas ļauj pieprasīt daudzus resursus (piemēram, fontus, JavaScript utt.) uz tīmekļa lapas no cita domēna, kas atšķiras no resursa izcelsmes domēna. Flight nav iebūvētas funkcionalitātes, bet to var viegli apstrādāt, izmantojot starprogrammu vai notikumu filtrus, līdzīgi kā CSRF.

```php
// app/middleware/CorsMiddleware.php

namespace app\middleware;

class CorsMiddleware
{
	public function before(array $params): void
	{
		atbilde = Flight::response();
		if (isset($_SERVER['HTTP_ORIGIN'])) {
			this->allowOrigins();
			atbilde->header('Access-Control-Allow-Credentials: true');
			atbilde->header('Access-Control-Max-Age: 86400');
		}

		if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
			if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
				atbilde->header(
					'Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS'
				);
			}
			if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
				atbilde->header(
					"Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}"
				);
			}
			atbilde->send();
			iziešana(0);
		}
	}

	privāts atļautIzcelsmās(): void
	{
		// pielāgojiet savus atļautus resursus šeit.
		atļauts = [
			'capacitor://localhost',
			'ionic://localhost',
			'http://localhost',
			'http://localhost:4200',
			'http://localhost:8080',
			'http://localhost:8100',
		];

		if (in_array($_SERVER['HTTP_ORIGIN'], atļauts)) {
			atbilde = Flight::response();
			atbilde->header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
		}
	}
}

// index.php vai jebkur, kur ir jūsu maršruta tabulas
Flight::route('/lietotāji', function() {
	lietotāji = Flight::db()->fetchAll('SELECT * FROM users');
	Flight::json(lietotāji);
})->addMiddleware(new CorsMiddleware());
```

## Secinājums

Drošība ir liela problēma, un ir svarīgi nodrošināt, ka jūsu tīmekļa lietojumprogrammas ir drošas. Flight nodrošina vairākas funkcijas, lai palīdzētu jums nodrošināt savas tīmekļa lietojumprogrammas, bet svarīgi vienmēr būt gādīgiem un nodrošināt, ka jūs darāt visu iespējamo, lai saglabātu savu lietotāju datus drošībā. Vienmēr pieņemiet sliktāko un neuzticieties savu lietotāju ievadei. Vienmēr izvairieties no izvades un izmantojiet sagatavotās izteiksmes, lai novērstu SQL injekciju. Vienmēr izmantojiet starprogrammu, lai aizsargātu savus maršrutus no CSRF un CORS uzbrukumiem. Ja jūs veicat visas šīs darbības, jūs būsiet labi ceļā uz drošu tīmekļa lietojumprogrammu izveidi.