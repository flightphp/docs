# Drošība

Drošība ir liela problēma, runājot par tīmekļa lietojumprogrammām. Jums ir jānodrošina, ka jūsu lietojumprogramma ir droša un ka jūsu lietotāju dati ir drošībā. Flight nodrošina vairākas funkcijas, lai palīdzētu jums nodrošināt savas tīmekļa lietojumprogrammas.

## Krustsenēju pieprasījumu viltus (CSRF)

Krustsenēju pieprasījumu viltus (CSRF) ir veids uzbrukumam, kur ļaunprātīga vietne var likt lietotāja pārlūkam nosūtīt pieprasījumu jūsu vietnei. Tas var tikt izmantots, lai veiktu darbības jūsu vietnē bez lietotāja zināšanām. Flight nenodrošina iebūvētu CSRF aizsardzības mehānismu, bet to var viegli ieviest, izmantojot starpprogrammatūru.

Vispirms ir jāģenerē CSRF marķieris un jāsaglabā tas lietotāja sesijā. Tad jūs varat izmantot šo marķieri savos veidlapās un pārbaudīt to, kad veidlapa tiek iesniegta.

```php
// Ģenerēt CSRF marķieri un saglabāt to lietotāja sesijā
// (pieņemsim, ka jūs esat izveidojis sesijas objektu un pievienojis to Flight)
Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
```

```html
<!-- Izmantojiet CSRF marķieri savā veidlapā -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- citi veidlapas lauki -->
</form>
```

Un pēc tam varat pārbaudīt CSRF marķieri, izmantojot notikumu filtrus:

```php
// Šī starpprogramma pārbauda, vai pieprasījums ir POST pieprasījums, un, ja tā ir, pārbauda, vai CSRF marķieris ir derīgs
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// fiksēt csrf marķieri no formas vērtībām
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Nederīgs CSRF marķieris');
		}
	}
});
```

## Krustsenēju skriptēšana (XSS)

Krustsenēju skriptēšana (XSS) ir uzbrukuma veids, kur ļaunprātīga vietne var ievietot kodu jūsu vietnē. Lielākā daļa šo iespēju nāk no formas vērtībām, ko aizpildīs jūsu gala lietotāji. Jums **nekad** nevajadzētu uzticēties saviem lietotāju izvades datiem! Viņiem vienmēr jāuzskata par vislabākajiem hakerniekiem pasaulē. Viņi var ieviest ļaunprātīgu JavaScript vai HTML jūsu lapā. Šo kodu var izmantot, lai zagtu informāciju no jūsu lietotājiem vai veiktu darbības jūsu vietnē. Izmantojot Flight skats klasi, jūs varat viegli izvairīties no izvades, lai novērstu XSS uzbrukumus.

```php

// Pieņemam, ka lietotājs ir viltīgs un cenšas to izmantot kā savu vārdu
$name = '<script>alert("XSS")</script>';

// Tas izvairīsies no izvades
Flight::view()->set('name', $name);
// Tas izvadīs: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Ja izmantojat kaut ko līdzīgu kā savu skata klasi reģistrētu kā savu skata klasi, tas arī tiks automātiski izvairīts.
Flight::view()->render('template', ['name' => $name]);
```

## SQL injekcija

SQL injekcija ir uzbrukuma veids, kur ļaunprātīgs lietotājs var ievietot SQL kodu jūsu datu bāzē. Tas var tikt izmantots, lai izvilktu informāciju no jūsu datu bāzes vai veiktu darbības jūsu datu bāzē. Atkal jums **nekad** nevajadzētu uzticēties savu lietotāju ievadei! Viņiem vienmēr jāuzskata par asinsdzeniem. Jūs varat izmantot sagatavotas ​​izteiksmes savos `PDO` objektos, lai novērstu SQL ielaušanos.

```php

// Pieņemot, ka jums ir Flight::db() reģistrēts kā jūsu PDO objekts
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// Ja izmantojat PdoWrapper klasi, to var viegli izdarīt vienā rindiņā
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// To pašu var izdarīt ar PDO objektu ar vietām jautājumzīmēm
$statement = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);

// Vienkārši apsoliet, ka nekad JŪS nekad neko NEIZDARĪS...Kaut ko tādu kā tas...
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE username = '{$username}' LIMIT 5");
// jo ja $username = "' OR 1=1; -- "; Pēc tam, kad pieprasījums tiek veidots, tas izskatās
// tā
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5
// Tas izskatās dīvains, bet tas ir derīgs vaicājums, kas darbosies. Patiesībā
// tas ir ļoti izplatīts SQL ielaušanās uzbrukums, kas atgriezīs visus lietotājus.
```

## CORS

Krustpunktus resursu kopīgošana (CORS) ir mehānisms, kas ļauj pieprasīt daudzas resursus (piemēram, fontus, JavaScript utt.) uz tīmekļa lapas no citas domēna, kas atšķiras no resursa izcelsmes domēna. Flight neuztura iebūvētu funkcionalitāti, bet ar to var viegli rīkoties, izmantojot starpprogrammatūru vai notikumu filtrus līdzīgi kā CSRF.

```php

// app/middleware/CorsMiddleware.php

namespace app\middleware;

class CorsMiddleware
{
	public function before(array $params): void
	{
		$response = Flight::response();
		if (isset($_SERVER['HTTP_ORIGIN'])) {
			$this->allowOrigins();
			$response->header('Access-Control-Allow-Credentials: true');
			$response->header('Access-Control-Max-Age: 86400');
		}

		if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
			if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
				$response->header(
					'Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS'
				);
			}
			if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
				$response->header(
					"Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}"
				);
			}
			$response->send();
			exit(0);
		}
	}

	private function allowOrigins(): void
	{
		$allowed = [
			'capacitor://localhost',
			'ionic://localhost',
			'http://localhost',
			'http://localhost:4200',
			'http://localhost:8080',
			'http://localhost:8100',
		];

		if (in_array($_SERVER['HTTP_ORIGIN'], $allowed)) {
			$response = Flight::response();
			$response->header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
		}
	}
}

// index.php vai kur jums ir jūsu maršruti
Flight::route('/lietotāji', function() {
	$users = Flight::db()->fetchAll('SELECT * FROM users');
	Flight::json($users);
})->addMiddleware(new CorsMiddleware());
```

## Secinājums

Drošība ir liela problēma, un svarīgi ir nodrošināt, ka jūsu tīmekļa lietojumprogrammas ir drošas. Flight nodrošina vairākas funkcijas, lai palīdzētu jums nodrošināt savas tīmekļa lietojumprogrammas, bet ir svarīgi vienmēr būt uzmanīgiem un nodrošināt, ka jūs darāt visu, lai saglabātu savu lietotāju datus drošībā. Vienu vienmēr jāuzticas sliktākajam un nekad nedrīkst uzticēties ievadei no saviem lietotājiem. Vienu vienmēr jāizvairās no izvades un jāizmanto sagatavotas ​​izteiksmes, lai novērstu SQL ielaušanos. Vienu vienmēr jāizmanto starpprogrammatūru, lai aizsargātu savus maršrutus no CSRF un CORS uzbrukumiem. Ja jūs veicat visus šos pasākumus, jūs būsiet labi uz ceļa, lai veidotu drošas tīmekļa lietojumprogrammas.