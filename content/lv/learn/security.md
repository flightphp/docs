# Drošība

Drošība ir liela lieta, ja runa ir par taustītām vietnēm. Jūs vēlaties nodrošināt, ka jūsu lietotne ir droša un jūsu lietotāju dati ir pasargāti. Flight nodrošina vēlamo funkciju kļūdu drošināšanai.

## Galvenes

HTTP galvenes ir viens no vieglākajiem veidiem, kā nodrošināt jūsu taustītas vietnes. Jūs varat izmantot galvenes, lai novērstu klikškināšanu, XSS un citas uzbrūķa veidus. Ir vairīgi veidi, kā pievienot šādas galvenes savai lietotnei.

Lieliskas šīsu galvenu drošības pārbaudei ir saita [securityheaders.com](https://securityheaders.com/) un [observatory.mozilla.org](https://observatory.mozilla.org/).

### Pievienot Manuāli

Jūs varat manuāli pievienot šas galvenes, izmantojot `header` metodi objektā `Flight\Response`.
```php
// Iestatiet X-Frame-Options galveni, lai novērstu klikškināšanu
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Iestatiet Content-Security-Policy galveni, lai novērstu XSS
// Piezīme: šāda galvene var būt ļoti sarežģīta, tāpēc jūs vājat
//  konsultēties par piemēriem internetā savai lietotnei
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Iestatiet X-XSS-Protection galveni, lai novērstu XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Iestatiet X-Content-Type-Options galveni, lai novērstu MIME pacelšanu
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Iestatiet atsaucēja politikas galveni, lai kontrolētu, cik daudz atsaucēja informācijas tiek nosūta
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Iestatiet Strict-Transport-Security galveni, lai piespiestu HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// Iestatiet Atļaujas-politikas galveni, lai kontrolētu, kuras funkcijas un API var lietot
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

Šīs var pievienot lietotnes `bootstrap.php` vai `index.php` failu augšdaivā.

### Pievienot kā Filtru

Jūs arī varat pievienot tos kā filtru/pīķi šādi: 

```php
// Pievienojiet galvenes kā filtru
Flight::before('start', function() {
	Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');
	Flight::response()->header("Content-Security-Policy", "default-src 'self'");
	Flight::response()->header('X-XSS-Protection', '1; mode=block');
	Flight::response()->header('X-Content-Type-Options', 'nosniff');
	Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');
	Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
	Flight::response()->header('Permissions-Policy', 'geolocation=()');
});
```

### Pievienot kā Starpstibi

Jūs arī varetu pievienot tos kopā ar starpstibi klasi. Tas ir labs veids, kā saglabāt jūsu kodu tāru un pārskatāmu.

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
		Flight::response()->header('Permissions-Policy', 'geolocation=()');
	}
}

// index.php vai kurā vietā jūs tiit savaus maršrutus
// FYI, ši tukša grupa kalpo kā globālais starpstibi visiem maršrutiem. Protams, jūs varētu darīt to pašu un pielāgot tikai
// to tikai konkrētiem maršrutiem.
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// vairāki maršruti
}, [ new SecurityHeadersMiddleware() ]);
```


## Plpie mājas lapas ievilkšana (CSRF)

Plpie mājas lapas ievilkšana (CSRF) ir uzbrukumu veids, kuru ļaunumsligīga mājas lapa var piespiest lietotāja pārlūkprogrammu nosūtīt pieprasījumu jūsu mājas lapai. Tas var tikt izmantots, lai veiktu darbības jūsu mājas lapā bez lietotāja zināšanām. Flight nenodrošina iebūvētu CSRF aizsardzi, bet to var viegli ieviest, izmantojot starpstibojas.

### Iestatīšana

Vispirms jums jāģenerē CSRF žetons un jāsaglabā lietotāja sesijā. Tad jūs varat izmantot šo žetonu savos veidlapos un pārbaudīt to, kad veidlapa tiek iesniegta.

```php
// Ģenerēt CSRF žetonu un saglabāt to lietotāja sesijā
// (pieņemsim, ka esat izveidojis sesijas objektu un pievienojis to Flight)
// skatiet sesijas dokumentāciju uzzināt vaiera
Flight::register('sesija', \Ghostff\Sesija\Sesija::class);

// Jums ir jāģenerē viens žetons sesiju (tāpēc tas darbojas
// pār vairākām cilnēm un pieprasījumiem attiecībā uz to pašu lietotāju)
jā gad.('sesī'->sanemt('csrf_zētons') === nu1uls) {
	'sesija'->iestati('csrf_zētons', bin2hex(nejatrine_atstarpes(32)) );
}
```

```html
<!­­ Izmantojiet CSRF žetonu jūsu veidlapā ­­>
<form method="post">
	<input type="hidden" name="csrf_zētons" value="<?= 'sesija'->sanemt('csrf_zētons') ?>">
	<!­- citi veidlapas lauki ­->
</form>
```

#### Izmantojot Latte

Jūs arī varat uzstādīt pielāgotu funkciju, lai izvadītu CSRF žetonu jūsu Latte veidlapās.

```php
// Uzstādiet pielāgotu funkciju, lai izvadītu CSRF žetonu
// Piezīme: Skats konfigurēts ar Latte kā skatu dzinēju
Flight::skats()->izveidoFunkciju('csrf', funkcija() {
	$csrfZētons = 'sesija'->sanemt('csrf_zētons');
	atgriezt jaunu \Latte\PalaišiHtml('<input type="hidden" name="csrf_zētons" value="' . $csrfZētons . '">');
});
```

Un tagad jūsu Latte veidlapās jūs varat izmantot `csrf()` funkciju, lai izvadītu CSRF žetonu.

```html
<form method="post">
	{csrf()}
	<!­- citi veidlapas lauki ­>
</form>
```

Īss un vienkāršs, vai ne?

### Pārbaudīt CSRF žetonu

Jūs varat pārbaudīt CSRF žetonu, izmantojot notikumu filtrus:

```php
// Šis starpstibi pārbauda, vai pieprasījums ir POST pieprasījums un, ja tā ir, tas pārbauda, vai CSRF žetons ir derīgs
Flight::before('start', function() {
	ja('iesniegums'->metode == 'POST') {

		// noformēt CSRF žetonu no veidlapas vērtībām
		$zētons = 'prasījums'->dati->csrf_zētons;
		ja($zētons !== 'sesija'->sanemt('csrf_zētons')) {
			Flight::halt(403, 'Nederīgs CSRF žetons');
			// vai JSON atbildes nolaižana
			Flight::jsonHalt(['klauda' => 'Nederīgs CSRF žetons'], 403);
		}
	}
});
```

Vai arī varat izmantot starpstibi klasi:

```php
// app/starpsībija/CsrfMiddleware.php

atseviškums app\starpsībija;

klase CsrfMiddleware
{
	publiska funkcija pirms(array $parametri): nulles
	{
		ja('prasījums'->metode == 'POST') {
			$zētons = 'prasījums'->dati->csrf_zētons;
			ja($zētons !== 'sesija'->sanemt('csrf_zētons')) {
				Flight::halt(403, 'Nederīgs CSRF žetons');
			}
		}
	}
}

// index.php vai kur jūs turat savas maršrutus
Flight::group('', funkcija(Ruteris $mautējs) {
	$mautējs->iegūt('/lietotāji', [ 'LietotājsKontrolieris', 'iegūtLietotājus' ]);
	// vairāki maršruti
}, [ jauns CsrfMiddleware() ]);
```

## Kross-mājas lapas skriptēšana (XSS)

Kross-mājas lapu skriptēšana (XSS) ir uzbrukumu veids, kad ļaunumsligīga mājas lapa var ieviest kodu jūsu mājas lapā. Lielākā daļa šo iespēju nāk no veidlapu vērtībām, ko aizpilda jūsu lietotāji. Jūs nekad **nekam** nedraūzējieties no savu lietotāju ievades! Vietā vienmēr uzskatāt, ka tie visi ir labākie hakkeri pasaulē. Viņi var ieviest ļaunprātīgu JavaScript vai HTML jūsu lapā. Šo kodu var izmantot, lai nozagt informāciju no jūsu lietotājiem vai veikt darbības jūsu mājas lapā. Izmantojot Flight skata klasi, jūs varat viegli izbēgt izeju, lai novērstu XSS uzbrūkus.

```php
// Paredzam, ka lietotājs ir izdomājīgs, mēģinot to izmantot kā savu vārdu
vards = '<script>alert("XSS")</script>';

// Tas izbēgs izeju
Flight::skats()->iestati('vards', vards);
// Tas izvadīs: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Ja izmantojat kaut ko tādu kā Latte, reģistrēts kā jūsu skata klase, tas arī automātiski izvairīsies no šāda veida koda.
Flight::skats()->renderē('veidne', ['vards' => vards]);
```

## SQL injekcija

SQL injekcija ir uzbrukumu veids, kuru ļaunumsligīgs lietotājs var injicēt SQL kodu jūsu datu bāzē. Tas var tikt izmantots, lai nozagt informāciju no jūsu datu bāzes vai veikt darbības jūsu datu bāzē. Atkal jums **nekad** nevajadzētu paļauties uz ievadi no jūsu lietotājiem! Vietā vienmēr uzskatiet, ka viņi ir uz nemieru. Jūs varat izmantot sagatavotas pieslēguma vietas savos `PDO` objektos, lai novērstu SQL injekciju.

```php
// Paredzot, ka Flight: db() ir reģistrēts kā jūsu PDO objekts
izaicinājums = Flight::db()->sagatavot('Izvēlēties * no lietotājiem, kur lietotājvārds = :lietotājvārds');
izaicinājums->izpilda([ ':lietotājvārds' => lietotājvārds ]);
lietotāji = izaicinājums->izlasītVisus();

// Ja izmantojat PdoApaķešu klasi, to var viegli izdarīt vienā rindā
lietotāji = Flight::db()->izlasītVisus('Izvēlēties * no lietotājiem, kur lietotājvārds = :lietotājvārds', [ 'lietotājvārds' => lietotājvārds ]);

// Jūs varat darīt to pašu ar PDO objektu ar ? vietturēm
izaicinājums = Flight::db()->izlasītVisus('Izvēlēties * no lietotājiem, kur lietotājvārds = ?', [ lietotājvārds ]);

// Vieta nekad NEKAD nedariet kaut ko tādu kā...
lietotāji = Flight::db()->izlasītVisus("Izvēlēties * no lietotājiem, kur lietotājvārds = '{$lietotājvārds}' LIMIT 5");
// jo ko ja $lietotājvārds = "' VAI 1 = 1; -- "; 
// Kad uzgriezieni tiek izveidoti šādi
// IZVELĒT * no lietotājiem, kur lietotājvārds = '' VAI 1 = 1; -- LIMITS 5
{/* ir dīvains, bet tas ir derīgs pieprašana, kas strādās. Pate ir tas ir ļoti izplatīts SQL injekcijas uzbrukums, kas atgriezīs# Drošība

Drošība ir liela lieta, ja runa ir par tīmekļa lietotnēm. Jūs vēlaties nodrošināties, ka jūsu lietotne ir droša un ka lietotāju dati ir pasargāti. Flight nodrošina vairākas funkcijas, lai palīdzētu jums nodrošināt savas tīmekļa lietotnes.

## Galvenes

HTTP galvenes ir viens no vieglākajiem veidiem, kā nodrošināt jūsu tīmekļa lietotnes drošību. Jūs varat izmantot galvenes, lai novērstu klikšķināšanas aizbiedēšanu, XSS un citas uzbrukuma formas. Ir vairāki veidi, kā pievienot šīs galvenes savai lietotnei.

Labi vietnes, kurās varat pārbaudīt savu galvu drošību, ir [securityheaders.com](https://securityheaders.com/) un [observatory.mozilla.org](https://observatory.mozilla.org/).

### Pievienot Manuāli

Jūs varat manuāli pievienot šīs galvenes, izmantojot `header` metodi objektam `Flight\Response`.
```php
// Iestatiet X-Frame-Options galvu, lai novērstu klikšķināšanu
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Iestatiet Content-Security-Policy galvu, lai novērstu XSS
// Piezīme: šai galvei var kļūt diezgan sarežģīta, tāpēc jums vajadzētu
// apmeklēt piemērus internetā jūsu lietotnei
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Iestatiet X-XSS-Protection galvu, lai novērstu XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Iestatiet X-Content-Type-Options galvu, lai novērstu MIME sviestināšanu
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Iestatiet Referrer-Policy galvu, lai kontrolētu, cik daudz referrera informācijas tiek nosūtīts
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Iestatiet Strict-Transport-Security galvu, lai piespiestu HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// Iestatiet Permissions-Policy galvu, lai kontrolētu, kādas funkcijas un API var izmantot
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

Šīs var pievienot jūsu `bootstrap.php` vai `index.php` failiem.

### Pievienot kā Filtru

Jūs arī varat pievienot tos kā filtru/kaitinātāju, piemēram: 

```php
// Pievienojiet galvenes kā filtru
Flight::before('start', function() {
	Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');
	Flight::response()->header("Content-Security-Policy", "default-src 'self'");
	Flight::response()->header('X-XSS-Protection', '1; mode=block');
	Flight::response()->header('X-Content-Type-Options', 'nosniff');
	Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');
	Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
	Flight::response()->header('Permissions-Policy', 'geolocation=()');
});
```

### Pievienot kā Starpmaiziņu

Jūs arī varat pievienot tos kā starpmaiziņas klasi. Tas ir labs veids, kā saglabāt jūsu kodu tīru un organizētu.

```php
// app/middleware/SecurityHeadersMiddleware.php

telpa app\middleware;

klase SecurityHeadersMiddleware
{
	publiska funkcija pirms( masīvs $parametri): tukšs
	{
		Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');
		Flight::response()->header("Content-Security-Policy", "default-src 'self'");
		Flight::response()->header('X-XSS-Protection', '1; mode=block');
		Flight::response()->header('X-Content-Type-Options', 'nosniff');
		Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');
		Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
		Flight::response()->header('Permissions-Policy', 'geolocation=()');
	}
}

// index.php vai kur jūs turat savus maršrutus
// FYI, šī tukšā grupa darbojas kā globālā starpmaiziņa visiem maršrutiem. Protams, jūs varētu darīt to pašu un vienkārši pievienot
// to tikai konkrētiem maršrutiem.
Flight::group('', funkcija(Router $maisītājs) {
	$maisītājs->iegūt('/lietotāji', [ 'LietotājuKontrolieris', 'iegūtLietotājus' ]);
	// vairāki maršruti
}, [ jauns SecurityHeadersMiddleware() ]);
```


## Krustu vietu pieprasījumu viltots (CSRF)

Krustu vietu pieprasījumu viltots (CSRF) ir uzbrukuma veids, kur ļaunumsliegta tīmekļa lapa var padarīt lietotāja pārlūkprogrammu sūtīt pieprasījumu uz jūsu tīmekļa lapu. Tas var tikt izmantots, lai veiktu darbības jūsu tīmekļa lapā bez lietotāja zināšanām. Flight nepiedāvā iebūvētu CSRF aizsardzības mehānismu, bet to var viegli ieviest, izmantojot starpmaiziņu.

### Iestatīšana

Vispirms jums jāģenerē CSRF žetons un jāsaglabā lietotāja sesijā. Jūs varat izmantot šo žetonu savos veidlapos un pārbaudīt to, kad veidlapa tiek iesniegta.

```php
// Ģenerēt CSRF žetonu un saglabāt to lietotāja sesijā
// (ja esat izveidojis sesijas objektu un piesaistījis to Flight)
// skatiet sesijas dokumentāciju saņemt vairāk informācijas
Flight::register('sesija', \Ghostff\Sesija\Sesija::class);

// Jums ir jāģenerē viens žetons sesijai (tāpēc tas strādā
// pār vairākām cilnēm un pieprasījumiem attiecībā uz to pašu lietotāju)
ja(Flight::sesija()->get('csrf_zetons') === null) {
	Flight::sesija()->set('csrf_zetons', bin2hex(random_bytes(32)));
}
```

```html
<!-- Izmantojiet CSRF žetonu savā veidlapā -->
<form method="post">
	<input type="hidden" name="csrf_zetons" value="<?= Flight::sesija()->get('csrf_zetons') ?>">
	<!-- citas veidlapas laukas -->
</form>
```

#### Izmantojot Latte

Jūs arī varat iestatīt pielāgotu funkciju, lai izvadītu CSRF žetonu jūsu Latte veidlapās.

```php
// Uzstādiet pielāgotu funkciju, lai izvadītu CSRF žetonu
// Piezīme: Skats konfigurēts ar Latte kā skata dzinēju
Flight::skats()->addFunction('csrf', function() {
	$csrfZetons = Flight::sesija()->get('csrf_zetons');
	return new \Latte\Runtime\Html('<input type="hidden" name="csrf_zetons" value="' . $csrfZetons . '">');
});
```

Un tagad jūsu Latte veidlapās jūs varat izmantot `csrf()` funkciju, lai izvadītu CSRF žetonu.

```html
<form method="post">
	{csrf()}
	<!-- citas veidlapas laukas -->
</form>
```

Īss un vienkāršs, vai ne?

### Pārbaudīt CSRF žetonu

Jūs varat pārbaudīt CSRF žetonu, izmantojot notikumu filtrus:

```php
// Šis starpmaiziņa pārbauda, vai pieprasījums ir POST pieprasījums un, ja tā ir, tas pārbauda, vai CSRF žetons ir derīgs
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// saņemt CSRF žetonu no veidlapas vērtībām
		$zetons = Flight::request()->data->csrf_zetons;
		if($zetons !== Flight::sesija()->get('csrf_zetons')) {
			Flight::halt(403, 'Nederīgs CSRF žetons');
			// vai JSON atbildes apstāšanās
			Flight::jsonHalt(['kļūda' => 'Nederīgs CSRF žetons'], 403);
		}
	}
});
```

Vai arī varat izmantot starpmaiziņas klasi:

```php
// app/middleware/CsrfMiddleware.php

namespace app\middleware;

klase CsrfMiddleware
{
	publiska funkcija pirms( masīvs $parametri): tukšs
	{
		if(Flight::request()->method == 'POST') {
			$zetons = Flight::request()->data->csrf_zetons;
			if($zetons !== Flight::sesija()->get('csrf_zetons')) {
				Flight::halt(403, 'Nederīgs CSRF žetons');
			}
		}
	}
}

// index.php vai kur jūs turat savus maršrutus
Flight::group('', funkcija(Router $maisītājs) {
	$maisītājs->iegūt('/lietotāji', [ 'LietotājuKontrolieris', 'iegūtLietotājus' ]);
	// vairāki maršruti
}, [ jauns CsrfMiddleware() ]);
```

## Krustu vietu skriptēšana (XSS)

Krustu vietu skriptēšana (XSS) ir uzbrukuma veids, kad ļaunumsliegta tīmekļa lapa var injicēt kodu jūsu tīmekļa lapā. Lielākā daļa šo iespēju nāk no veidlapu vērtībām, ko aizpilda jūsu lietotāji. Jums **nekad** nevajadzētu uzticēties jūsu lietotāju ievadei! Vienmēr pieņemiet, ka visi no viņiem ir labākie hakkeri pasaulē. Viņi var injicēt kaitīgu JavaScript vai HTML jūsu lapā. Šo kodu var izmantot, lai nozagt informāciju no jūsu lietotājiem vai veikt darbības jūsu tīmekļa lapā. Izmantojot Flight skates klasi, jūs varat viegli izslēgt izvadi, lai novērstu XSS uzbrukumus.

```php
// Pēc noklusējuma pieņemsim, ka lietotājs ir gudrs un mēģina to izmantot kā savu vārdu
vārds = '<script>alert("XSS")</script>';

// Tas izslēgs izvadi
Flight::skats()->set('vārds', $vārds);
// Tas izvadīs: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Ja izmantojat kaut ko līdzīgu kā Latte, reģistrēts kā jūsu skates klase, tas arī automātiski izslēgs izvadi.
Flight::skats()->renderēt('veidne', ['vārds' => $vārds]);
```

## SQL injekcija

SQL injekcija ir uzbrukuma veids, kad ļaunumsliegts lietotājs var injicēt SQL kodu jūsu datu bāzē. Tas var tikt izmantots, lai nozagt informāciju no jūsu datu bāzes vai veikt darbības jūsu datu bāzē. Atkal **nekad** neuzticieties ievadei no jūsu lietotājiem! Viemēr pieņemiet, ka viņi ir asiņu centienācī. Jūs varat izmantot sagatavotās apgalvojumu vietas savos `PDO` objektos, lai novērstu SQL injekciju.

```php
// Paredzot, ka Flight::db() ir reģistrēts kā jūsu PDO objekts
apgalvojums = Flight::db()->sagatavot('Izvēlēties * no lietotājiem, kur lietotājvārds = :lietotājvārds');
apgalvojums->izpildīt([ ':lietotājvārds' => $lietotājvārds ]);
lietotāji = apgalvojums->izgūtVisus();

// Ja izmantojat PdoApglūtas klasi, to var viegli izdarīt vienā rindā
lietotāji = Flight::db()->izgūtVisus('Izvēlēties * no lietotājiem, kur lietotājvārds = :lietotājvārds', [ 'lietotājvārds' => $lietotājvārds ]);

// Jūs varat darīt to pašu ar PDO objektu ar ? vietasnorādēm
apgalvojums = Flight::db()->izgūtVisus('Izvēlēties * no lietotājiem, kur lietotājvārds = ?', [ $lietotājvārds ]);

// Vieta apsolieties nekad NEDARĪT ko tādu kā šo...
lietotāji = Flight::db()->izgūtVisus("Izvēlēties * no lietotājiem, kur lietotājvārds = '{$lietotājvārds}' LIMIT 5");
// jo ja $lietotājvārds = "' VAI 1=1; -- "; 
// Pēc kārtas ir izveidots tas šādi
// Izvēlēties * no lietotājiem, kur lietotājvārds = '' VAI 1=1; -- LIMITS 5
{/* var izskaties dīvaini, bet tas ir derīgs vaiera, kas darbosies. Patiesībā
// tas ir ļoti izplatīts SQL injicēšanas uzbrukums, kas atgriezīs visus lietotājus.
```

## CORS

Krustu resursu koplietošanas (CORS) mehānisms ir mehānisms, kas ļauj daudz resursu (piemēram, fontus, JavaScript utt.) tīmekļa lapā pieprasīt no cita domēna ārpus domēna, no kura resurss nāca. Flight neuzlādē iebūvētas funkcionalitātes, bet to var viegli apstrādāt, pievienojot āķi, kurš izpildās pirms tiek izsaukts `Flight::start()` metode.

## Nobeigums

Drošība ir būtiska, un ir svarīgi nodrošināt, ka jūsu tīmekļa lietotnes ir drošas. Flight nodrošina vairākas funkcijas, lai palīdzētu jums nodrošināt savas tīmekļa lietotnes, bet ir svarīgi vienmēr būt uzmanīgiem un nodrošināt, ka jūs darāt visu iespējamo, lai pasargātu savu lietotāju datus. Vieta vienmēr pieņem sliktāko un nekad neuzticieties ievadei no savu lietotāju. Vieta vienmēr izvairieties no izejas un izmantojiet sagatavotus apgalvojumus, lai novērstu SQL injekcijas. Vieta vienmēr izmantojiet starpmaiziņas, lai aizsargātu savus maršrutus no CSRF un CORS uzbrukumiem. Ja jūs veicat visus šos pasākumus, jūs būsiet labā ceļā uz drošu tīmekļa lietotņu izstrādi.