# Notikumu pārvaldnieks

_no v3.15.0_

## Pārskats

Notikumi ļauj reģistrēt un izraisīt pielāgotu uzvedību jūsu lietojumprogrammā. Ar `Flight::onEvent()` un `Flight::triggerEvent()` pievienošanu jūs tagad varat savienoties ar galvenajiem jūsu lietojumprogrammas dzīves cikla brīžiem vai definēt savus notikumus (piemēram, paziņojumus un e-pastus), lai padarītu jūsu kodu modulārāku un paplašināmu. Šīs metodes ir daļa no Flight [mappable methods](/learn/extending), kas nozīmē, ka jūs varat pārdefinēt to uzvedību atbilstoši savām vajadzībām.

## Saprašana

Notikumi ļauj atdalīt dažādas jūsu lietojumprogrammas daļas, lai tās pārāk stipri nepaļautos viena uz otru. Šī atdalīšana — bieži saukta par **decoupling** — padara jūsu kodu vieglāku atjaunināt, paplašināt vai atkļūdot. Tā vietā, lai rakstītu visu vienā lielā gabalā, jūs varat sadalīt savu loģiku mazākos, neatkarīgos gabalos, kas reaģē uz specifiskām darbībām (notikumiem).

Iedomājieties, ka jūs veidojat emuāru lietojumprogrammu:
- Kad lietotājs publicē komentāru, jūs varētu vēlēties:
  - Saglabāt komentāru datubāzē.
  - Nosūtīt e-pastu emuāra īpašniekam.
  - Reģistrēt darbību drošības nolūkos.

Bez notikumiem jūs visu sapotu vienā funkcijā. Ar notikumiem jūs varat to sadalīt: viena daļa saglabā komentāru, cita izraisa notikumu, piemēram, `'comment.posted'`, un atsevišķi klausītāji apstrādā e-pastu un reģistrēšanu. Tas uztur jūsu kodu tīrāku un ļauj pievienot vai noņemt funkcijas (piemēram, paziņojumus) bez pieskāriena kodola loģikai.

### Izplatīti izmantošanas gadījumi

Lielākoties notikumi ir piemēroti lietām, kas ir izvēles, bet ne absolūti kodola daļa jūsu sistēmā. Piemēram, sekojošie ir labi, bet ja tie kaut kāda iemesla dēļ neizdodas, jūsu lietojumprogramma joprojām darbojas:

- **Reģistrēšana**: Reģistrēt darbības, piemēram, pieteikšanos vai kļūdas, bez jūsu galvenā koda sajaukšanas.
- **Paziņojumi**: Nosūtīt e-pastus vai brīdinājumus, kad kaut kas notiek.
- **Kešatjauninājumi**: Atsvaidzināt kešus vai informēt citas sistēmas par izmaiņām.

Tomēr pieņemsim, ka jums ir aizmirsta parole funkcija. Tā jābūt jūsu kodola funkcionalitātes daļai un nevis notikumam, jo ja tas e-pasts netiek nosūtīts, jūsu lietotājs nevar atiestatīt paroli un izmantot jūsu lietojumprogrammu.

## Pamata izmantošana

Flight notikumu sistēma ir balstīta uz divām galvenajām metodēm: `Flight::onEvent()` notikumu klausītāju reģistrēšanai un `Flight::triggerEvent()` notikumu izraisīšanai. Lūk, kā jūs varat tās izmantot:

### Notikumu klausītāju reģistrēšana

Lai klausītos notikumu, izmantojiet `Flight::onEvent()`. Šī metode ļauj definēt, kas jānotiek, kad notikums notiek.

```php
Flight::onEvent(string $event, callable $callback): void
```

- `$event`: Nosaukums jūsu notikumam (piemēram, `'user.login'`).
- `$callback`: Funkcija, kas jāizpilda, kad notikums tiek izraisīts.

Jūs "abonējat" notikumu, sakot Flight, ko darīt, kad tas notiek. Atsaukums var pieņemt argumentus, kas nodoti no notikuma izraisītāja.

Flight notikumu sistēma ir sinhrona, kas nozīmē, ka katrs notikuma klausītājs tiek izpildīts secīgi, viens pēc otra. Kad jūs izraisa notikumu, visi reģistrētie klausītāji tam notikumam tiks izpildīti līdz galam, pirms jūsu kods turpinās. Tas ir svarīgi saprast, jo tas atšķiras no asinhronām notikumu sistēmām, kur klausītāji var darboties paralēli vai vēlākā laikā.

#### Vienkāršs piemērs
```php
Flight::onEvent('user.login', function ($username) {
    echo "Welcome back, $username!";

	// you can send an email if the login is from a new location
});
```
Šeit, kad `'user.login'` notikums tiek izraisīts, tas sveiks lietotāju vārdā un varētu ietvert loģiku e-pasta nosūtīšanai, ja nepieciešams.

> **Piezīme:** Atsaukums var būt funkcija, anonīma funkcija vai klases metode.

### Notikumu izraisīšana

Lai notikums notiktu, izmantojiet `Flight::triggerEvent()`. Tas saka Flight izpildīt visus klausītājus, kas reģistrēti tam notikumam, nododot līdzi jebkādu datu, ko jūs nododiet.

```php
Flight::triggerEvent(string $event, ...$args): void
```

- `$event`: Notikuma nosaukums, ko jūs izraisa (jāatbilst reģistrētam notikumam).
- `...$args`: Izvēles argumenti, ko nosūtīt klausītājiem (var būt jebkurš argumentu skaits).

#### Vienkāršs piemērs
```php
$username = 'alice';
Flight::triggerEvent('user.login', $username);
```
Tas izraisa `'user.login'` notikumu un nosūta `'alice'` klausītājam, ko mēs definējām iepriekš, kas izvadīs: `Welcome back, alice!`.

- Ja nav reģistrēti klausītāji, nekas nenotiek — jūsu lietojumprogramma nesabojāsies.
- Izmantojiet izplatīšanas operatoru (`...`), lai elastīgi nodotu vairākus argumentus.

### Notikumu apturēšana

Ja klausītājs atgriež `false`, papildu klausītāji tam notikumam netiks izpildīti. Tas ļauj apturēt notikumu ķēdi, balstoties uz specifiskiem nosacījumiem. Atcerieties, ka klausītāju secība ir svarīga, jo pirmais, kas atgriež `false`, apturēs pārējos.

**Piemērs**:
```php
Flight::onEvent('user.login', function ($username) {
    if (isBanned($username)) {
        logoutUser($username);
        return false; // Stops subsequent listeners
    }
});
Flight::onEvent('user.login', function ($username) {
    sendWelcomeEmail($username); // this is never sent
});
```

### Notikumu metožu pārdefinēšana

`Flight::onEvent()` un `Flight::triggerEvent()` ir pieejamas [paplašināšanai](/learn/extending), kas nozīmē, ka jūs varat pārdefinēt, kā tās darbojas. Tas ir lieliski attīstītiem lietotājiem, kas vēlas pielāgot notikumu sistēmu, piemēram, pievienojot reģistrēšanu vai mainot, kā notikumi tiek izplatīti.

#### Piemērs: `onEvent` pielāgošana
```php
Flight::map('onEvent', function (string $event, callable $callback) {
    // Log every event registration
    error_log("New event listener added for: $event");
    // Call the default behavior (assuming an internal event system)
    Flight::_onEvent($event, $callback);
});
```
Tagad katru reizi, kad jūs reģistrējat notikumu, tas to reģistrē pirms turpināšanas.

#### Kāpēc pārdefinēt?
- Pievienot atkļūdošanu vai uzraudzību.
- Ierobežot notikumus noteiktās vidēs (piemēram, atslēgt testēšanā).
- Integrēt ar citu notikumu bibliotēku.

### Kur novietot savus notikumus

Ja jūs esat jauns notikumu konceptos savā projektā, jūs varētu brīnīties: *kur es reģistrēju visus šos notikumus savā lietojumprogrammā?* Flight vienkāršība nozīmē, ka nav stingra noteikuma — jūs varat tos novietot kur vien tas ir loģiski jūsu projektam. Tomēr to organizēšana palīdz uzturēt jūsu kodu, kad jūsu lietojumprogramma aug. Lūk, daži praktiski varianti un labākās prakses, pielāgotas Flight vieglajai dabai:

#### Opcija 1: Jūsu galvenajā `index.php`
Mazām lietojumprogrammām vai ātriem prototipiem jūs varat reģistrēt notikumus tieši jūsu `index.php` failā blakus jūsu maršrutiem. Tas uztur visu vienā vietā, kas ir labi, kad vienkāršība ir jūsu prioritāte.

```php
require 'vendor/autoload.php';

// Register events
Flight::onEvent('user.login', function ($username) {
    error_log("$username logged in at " . date('Y-m-d H:i:s'));
});

// Define routes
Flight::route('/login', function () {
    $username = 'bob';
    Flight::triggerEvent('user.login', $username);
    echo "Logged in!";
});

Flight::start();
```
- **Priekšrocības**: Vienkārši, nav papildu failu, lieliski maziem projektiem.
- **Trūkumi**: Var kļūt nekārtīgs, kad jūsu lietojumprogramma aug ar vairāk notikumiem un maršrutiem.

#### Opcija 2: Atsevišķs `events.php` fails
Nedaudz lielākai lietojumprogrammai apsveriet notikumu reģistrāciju pārvietošanu uz veltītu failu, piemēram, `app/config/events.php`. Iekļaujiet šo failu jūsu `index.php` pirms jūsu maršrutiem. Tas imitē, kā maršruti bieži ir organizēti `app/config/routes.php` Flight projektos.

```php
// app/config/events.php
Flight::onEvent('user.login', function ($username) {
    error_log("$username logged in at " . date('Y-m-d H:i:s'));
});

Flight::onEvent('user.registered', function ($email, $name) {
    echo "Email sent to $email: Welcome, $name!";
});
```

```php
// index.php
require 'vendor/autoload.php';
require 'app/config/events.php';

Flight::route('/login', function () {
    $username = 'bob';
    Flight::triggerEvent('user.login', $username);
    echo "Logged in!";
});

Flight::start();
```
- **Priekšrocības**: Uztur `index.php` fokusētu uz maršrutēšanu, loģiski organizē notikumus, viegli atrast un rediģēt.
- **Trūkumi**: Pievieno mazu struktūru, kas var šķist pārspīlēta ļoti mazām lietojumprogrammām.

#### Opcija 3: Tuvu tam, kur tie tiek izraisīti
Vēl viena pieeja ir reģistrēt notikumus tuvu vietai, kur tie tiek izraisīti, piemēram, iekšā kontrolierī vai maršruta definīcijā. Tas labi darbojas, ja notikums ir specifisks vienai jūsu lietojumprogrammas daļai.

```php
Flight::route('/signup', function () {
    // Register event here
    Flight::onEvent('user.registered', function ($email) {
        echo "Welcome email sent to $email!";
    });

    $email = 'jane@example.com';
    Flight::triggerEvent('user.registered', $email);
    echo "Signed up!";
});
```
- **Priekšrocības**: Uztur saistītu kodu kopā, labi izolētām funkcijām.
- **Trūkumi**: Izkliedē notikumu reģistrācijas, padarot grūtāku redzēt visus notikumus uzreiz; risks dublikēt reģistrācijas, ja neuzmanīgs.

#### Labākā prakse Flight
- **Sākt vienkārši**: Mazām lietojumprogrammām novietojiet notikumus `index.php`. Tas ir ātri un atbilst Flight minimālismam.
- **Augt gudri**: Kad jūsu lietojumprogramma paplašinās (piemēram, vairāk nekā 5-10 notikumi), izmantojiet `app/config/events.php` failu. Tas ir dabisks solis uz augšu, kā maršrutu organizēšana, un uztur jūsu kodu sakārtotu bez sarežģītu ietvaru pievienošanas.
- **Izvairīties no pārspīlējuma**: Neizveidojiet pilnībā attīstītu “notikumu pārvaldnieka” klasi vai direktoriju, ja vien jūsu lietojumprogramma nekļūst milzīga — Flight uzplaukst vienkāršībā, tāpēc uzturiet to vieglu.

#### Padoms: Grupēt pēc mērķa
`events.php` failā grupējiet saistītus notikumus (piemēram, visus lietotāju saistītos notikumus kopā) ar komentāriem skaidrībai:

```php
// app/config/events.php
// User Events
Flight::onEvent('user.login', function ($username) {
    error_log("$username logged in");
});
Flight::onEvent('user.registered', function ($email) {
    echo "Welcome to $email!";
});

// Page Events
Flight::onEvent('page.updated', function ($pageId) {
    Flight::cache()->delete("page_$pageId");
});
```

Šī struktūra labi paplašinās un paliek iesācējam draudzīga.

### Reālas pasaules piemēri

Apskatīsim dažus reālas pasaules scenārijus, lai parādītu, kā notikumi darbojas un kāpēc tie ir noderīgi.

#### Piemērs 1: Lietotāja pieteikšanās reģistrēšana
```php
// Step 1: Register a listener
Flight::onEvent('user.login', function ($username) {
    $time = date('Y-m-d H:i:s');
    error_log("$username logged in at $time");
});

// Step 2: Trigger it in your app
Flight::route('/login', function () {
    $username = 'bob'; // Pretend this comes from a form
    Flight::triggerEvent('user.login', $username);
    echo "Hi, $username!";
});
```
**Kāpēc tas ir noderīgs**: Pieteikšanās kods nav jāzina par reģistrēšanu — tas tikai izraisa notikumu. Vēlāk jūs varat pievienot vairāk klausītāju (piemēram, nosūtīt sveiciena e-pastu) bez maršruta maiņas.

#### Piemērs 2: Paziņošana par jauniem lietotājiem
```php
// Listener for new registrations
Flight::onEvent('user.registered', function ($email, $name) {
    // Simulate sending an email
    echo "Email sent to $email: Welcome, $name!";
});

// Trigger it when someone signs up
Flight::route('/signup', function () {
    $email = 'jane@example.com';
    $name = 'Jane';
    Flight::triggerEvent('user.registered', $email, $name);
    echo "Thanks for signing up!";
});
```
**Kāpēc tas ir noderīgs**: Reģistrācijas loģika fokusējas uz lietotāja izveidošanu, kamēr notikums apstrādā paziņojumus. Jūs varētu vēlāk pievienot vairāk klausītāju (piemēram, reģistrēt reģistrāciju).

#### Piemērs 3: Keša dzēšana
```php
// Listener to clear a cache
Flight::onEvent('page.updated', function ($pageId) {
	// if using the flightphp/cache plugin
    Flight::cache()->delete("page_$pageId");
    echo "Cache cleared for page $pageId.";
});

// Trigger when a page is edited
Flight::route('/edit-page/(@id)', function ($pageId) {
    // Pretend we updated the page
    Flight::triggerEvent('page.updated', $pageId);
    echo "Page $pageId updated.";
});
```
**Kāpēc tas ir noderīgs**: Rediģēšanas kods neuztraucas par kešošanu — tas tikai signalizē atjauninājumu. Citas lietojumprogrammas daļas var reaģēt pēc vajadzības.

### Labākās prakses

- **Skaidri nosaukt notikumus**: Izmantojiet specifiskus nosaukumus, piemēram, `'user.login'` vai `'page.updated'`, lai būtu acīmredzams, ko tie dara.
- **Uzturēt klausītājus vienkāršus**: Neievietojiet lēnas vai sarežģītas uzdevumus klausītājos — uzturiet savu lietojumprogrammu ātru.
- **Testēt savus notikumus**: Izraisiet tos manuāli, lai nodrošinātu, ka klausītāji darbojas kā paredzēts.
- **Izmantot notikumus gudri**: Tie ir lieliski atdalīšanai, bet pārāk daudzi var padarīt jūsu kodu grūti izsekojamu — izmantojiet tos, kad tas ir loģiski.

Flight PHP notikumu sistēma ar `Flight::onEvent()` un `Flight::triggerEvent()` sniedz jums vienkāršu, bet spēcīgu veidu, kā būvēt elastīgas lietojumprogrammas. Ļaujot dažādām jūsu lietojumprogrammas daļām sazināties caur notikumiem, jūs varat uzturēt savu kodu organizētu, atkārtoti izmantojamu un viegli paplašināmu. Vai nu jūs reģistrējat darbības, sūtat paziņojumus vai pārvaldāt atjauninājumus, notikumi palīdz to darīt bez jūsu loģikas savijuma. Turklāt, ar iespēju pārdefinēt šīs metodes, jums ir brīvība pielāgot sistēmu savām vajadzībām. Sāciet mazu ar vienu notikumu un skatieties, kā tas pārveido jūsu lietojumprogrammas struktūru!

### Iebūvētie notikumi

Flight PHP nāk ar dažiem iebūvētiem notikumiem, ko jūs varat izmantot, lai savienotos ar ietvara dzīves ciklu. Šie notikumi tiek izraisīti specifiskos pieprasījuma/atbildes cikla punktos, ļaujot jums izpildīt pielāgotu loģiku, kad notiek noteiktas darbības.

#### Iebūvēto notikumu saraksts
- **flight.request.received**: `function(Request $request)` Izraisīts, kad pieprasījums tiek saņemts, parsēts un apstrādāts.
- **flight.error**: `function(Throwable $exception)` Izraisīts, kad kļūda rodas pieprasījuma dzīves cikla laikā.
- **flight.redirect**: `function(string $url, int $status_code)` Izraisīts, kad tiek uzsākta pāradresēšana.
- **flight.cache.checked**: `function(string $cache_key, bool $hit, float $executionTime)` Izraisīts, kad kešs tiek pārbaudīts specifiskai atslēgai un vai kešs trāpījis vai ne.
- **flight.middleware.before**: `function(Route $route)`Izraisīts pēc before middleware izpildes.
- **flight.middleware.after**: `function(Route $route)` Izraisīts pēc after middleware izpildes.
- **flight.middleware.executed**: `function(Route $route, $middleware, string $method, float $executionTime)` Izraisīts pēc jebkura middleware izpildes
- **flight.route.matched**: `function(Route $route)` Izraisīts, kad maršruts ir saskaņots, bet vēl nav izpildīts.
- **flight.route.executed**: `function(Route $route, float $executionTime)` Izraisīts pēc maršruta izpildes un apstrādes. `$executionTime` ir laiks, kas vajadzīgs maršruta izpildei (izsaukt kontrolieri utt.).
- **flight.view.rendered**: `function(string $template_file_path, float $executionTime)` Izraisīts pēc skata renderēšanas. `$executionTime` ir laiks, kas vajadzīgs veidnes renderēšanai. **Piezīme: Ja jūs pārdefinējat `render` metodi, jums būs jāizraisa šis notikums atkārtoti.**
- **flight.response.sent**: `function(Response $response, float $executionTime)` Izraisīts pēc atbildes nosūtīšanas klientam. `$executionTime` ir laiks, kas vajadzīgs atbildes izveidošanai.

## Skatīt arī
- [Extending Flight](/learn/extending) - Kā paplašināt un pielāgot Flight kodola funkcionalitāti.
- [Cache](/awesome-plugins/php_file_cache) - Piemērs, kā izmantot notikumus keša dzēšanai, kad lapa tiek atjaunināta.

## Problēmu risināšana
- Ja jūs neredzat savus notikumu klausītājus tiek izsauktus, pārliecinieties, ka jūs tos reģistrējat pirms notikumu izraisīšanas. Reģistrācijas secība ir svarīga.

## Izmaiņu žurnāls
- v3.15.0 - Pievienoti notikumi Flight.