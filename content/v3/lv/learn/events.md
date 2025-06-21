# Notikumu sistēma Flight PHP (v3.15.0+)

Flight PHP ievieš vieglu un intuitīvu notikumu sistēmu, kas ļauj reģistrēt un izraisīt pielāgotus notikumus jūsu aplikācijā. Ar `Flight::onEvent()` un `Flight::triggerEvent()` pievienošanu, jūs tagad varat pieslēgties galvenajiem jūsu app dzīves cikla mirkļiem vai definēt savus notikumus, lai padarītu kodu modulārāku un paplašināmu. Šīs metodes ir daļa no Flight **mappable methods**, kas nozīmē, ka jūs varat pārdefinēt to uzvedību atbilstoši savām vajadzībām.

Šis ceļvedis aptver visu, ko jums vajadzētu zināt, lai sāktu ar notikumiem, ieskaitot iemeslus, kāpēc tie ir vērtīgi, kā tos izmantot, un praktiskus piemērus, lai palīdzētu iesācējiem saprast to spēku.

## Kāpēc izmantot notikumus?

Notikumi ļauj atdalīt dažādas aplikācijas daļas, lai tās nepārāk stipri nebalstītos viena uz otru. Šī atdalīšana — bieži saukta par **decoupling** — padara kodu vieglāku atjaunināšanai, paplašināšanai vai atkļūdošanai. Tā vietā, lai rakstītu visu vienā lielā blokā, jūs varat sadalīt loģiku mazākos, neatkarīgos gabalos, kas reaģē uz specifiskām darbībām (notikumiem).

Iedomājieties, ka jūs veidojat bloga app:
- Kad lietotājs pievieno komentāru, jūs varētu vēlēties:
  - Saglabāt komentāru datu bāzē.
  - Nosūtīt e-pastu bloga īpašniekam.
  - Reģistrēt darbību drošības nolūkos.

Bez notikumiem jūs to visu iespiestu vienā funkcijā. Ar notikumiem jūs to varat sadalīt: viena daļa saglabā komentāru, cita izraisa notikumu, piemēram, `'comment.posted'`, un atsevišķi klausītāji apstrādā e-pastu un reģistrāciju. Tas padara kodu tīrāku un ļauj pievienot vai noņemt funkcijas (piemēram, paziņojumus) bez ietekmes uz kodola loģiku.

### Biežākās izmantošanas vietas
- **Reģistrācija**: Reģistrēt darbības, piemēram, pieteikumus vai kļūdas, neaizkraujot galveno kodu.
- **Paziņojumi**: Nosūtīt e-pastus vai brīdinājumus, kad kaut kas notiek.
- **Atjauninājumi**: Atsvaidzināt kešus vai paziņot citiem sistēmām par izmaiņām.

## Reģistrējot notikumu klausītājus

Lai klausītos notikumu, izmantojiet `Flight::onEvent()`. Šī metode ļauj definēt, kas notiek, kad notikums notiek.

### Sintakse
```php
Flight::onEvent(string $event, callable $callback): void  // $event: Notikuma nosaukums (piem., 'user.login').
```
- `$event`: Notikuma nosaukums (piem., `'user.login'`).
- `$callback`: Funkcija, kas jāizpilda, kad notikums tiek izsaukts.

### Kā tas darbojas
Jūs "abonējat" notikumu, norādot Flight, ko darīt, kad tas notiek. Callback var pieņemt argumentus, kas nodoti no notikuma izsaukuma.

Flight notikumu sistēma ir sinhroniska, kas nozīmē, ka katrs notikuma klausītājs tiek izpildīts secīgi, viens pēc otra. Kad jūs izraisa notikumu, visi reģistrētie klausītāji tam notikumam tiks izpildīti līdz galam, pirms jūsu kods turpina. Tas ir svarīgi saprast, jo tas atšķiras no asinhroniskām notikumu sistēmām, kur klausītāji varētu darboties paralēli vai vēlāk.

### Vienkāršs piemērs
```php
Flight::onEvent('user.login', function ($username) {
    echo "Laipni lūgts atpakaļ, $username!";  // Šeit sveic lietotāju vārdā.
});
```
Šeit, kad `'user.login'` notikums tiek izsaukts, tas sveic lietotāju vārdā.

### Galvenie punkti
- Jūs varat pievienot vairākus klausītājus vienam notikumam — tie tiks izpildīti reģistrēšanas secībā.
- Callback var būt funkcija, anonīma funkcija vai klases metode.

## Izraisot notikumus

Lai izraisītu notikumu, izmantojiet `Flight::triggerEvent()`. Tas liek Flight izpildīt visus reģistrētos klausītājus tam notikumam, nododot jebkādus datus, ko jūs norādāt.

### Sintakse
```php
Flight::triggerEvent(string $event, ...$args): void
```
- `$event`: Notikuma nosaukums, ko jūs izraisa (jāatbilst reģistrētajam notikumam).
- `...$args`: Izvēles argumenti, ko nosūtīt klausītājiem (var būt jebkurš argumentu skaits).

### Vienkāršs piemērs
```php
$username = 'alice';
Flight::triggerEvent('user.login', $username);
```
Tas izraisa `'user.login'` notikumu un nosūta `'alice'` klausītājam, ko mēs agrāk definējām, kas izvadīs: `Laipni lūgts atpakaļ, alice!`.

### Galvenie punkti
- Ja nav reģistrētu klausītāju, nekas nenotiek — jūsu app netiks sabojāts.
- Izmantojiet izklājuma operatoru (`...`) , lai elastīgi nodotu vairākus argumentus.

### Reģistrējot notikumu klausītājus

...

**Apturēt turpmākos klausītājus**:
Ja klausītājs atgriež `false`, neviens papildu klausītājs tam notikumam netiks izpildīts. Tas ļauj apturēt notikumu ķēdi, balstoties uz specifiskiem nosacījumiem. Atcerieties, ka klausītāju secība ir svarīga, jo pirmais, kas atgriež `false`, apturēs pārējos.

**Piemērs**:
```php
Flight::onEvent('user.login', function ($username) {
    if (isBanned($username)) {
        logoutUser($username);
        return false;  // Aptur turpmākos klausītājus
    }
});
Flight::onEvent('user.login', function ($username) {
    sendWelcomeEmail($username);  // šis netiek nosūtīts
});
```

## Pārdefinējot notikumu metodes

`Flight::onEvent()` un `Flight::triggerEvent()` ir pieejamas [extended](/learn/extending), kas nozīmē, ka jūs varat pārdefinēt, kā tās darbojas. Tas ir lieliski piemērots pieredzējušiem lietotājiem, kuri vēlas pielāgot notikumu sistēmu, piemēram, pievienojot reģistrāciju vai mainot, kā notikumi tiek izsūtīti.

### Piemērs: Pielāgojot `onEvent`
```php
Flight::map('onEvent', function (string $event, callable $callback) {
    // Reģistrē katru notikuma reģistrāciju
    error_log("Jauns notikuma klausītājs pievienots: $event");
    // Izsauc noklusējuma uzvedību (pieņemot iekšēju notikumu sistēmu)
    Flight::_onEvent($event, $callback);
});
```
Tagad katru reizi, kad jūs reģistrējat notikumu, tas to reģistrē pirms turpināšanas.

### Kāpēc pārdefinēt?
- Pievienot atkļūdošanu vai uzraudzību.
- Ierobežot notikumus noteiktās vidēs (piemēram, atspējot testēšanā).
- Integrēt ar citu notikumu bibliotēku.

## Kur ievietot savus notikumus

Kā iesācējs, jūs varētu brīnīties: *kur es reģistrēju visus šos notikumus savā app?* Flight vienkāršība nozīmē, ka nav stingru noteikumu — jūs varat ievietot tos tur, kur tas šķiet loģiski jūsu projektam. Tomēr to organizēšana palīdz uzturēt kodu, jo app aug. Šeit ir dažas praktiskas opcijas un labākās prakses, pielāgotas Flight vieglajai dabai:

### Opcija 1: Galvenajā `index.php`
Maziem app vai ātriem prototipiem jūs varat reģistrēt notikumus tieši `index.php` failā kopā ar maršrutiem. Tas visu tur vienuviet, kas ir piemēroti, kad galvenā prioritāte ir vienkāršība.

```php
require 'vendor/autoload.php';

// Reģistrēt notikumus
Flight::onEvent('user.login', function ($username) {
    error_log("$username pi logged in at " . date('Y-m-d H:i:s'));  // Reģistrē pieteikšanos ar laiku.
});

// Definēt maršrutus
Flight::route('/login', function () {
    $username = 'bob';
    Flight::triggerEvent('user.login', $username);
    echo "Pieslēdzies!";
});

Flight::start();
```
- **Priekšrocības**: Vienkārši, nav papildu failu, lieliski maziem projektiem.
- **Trūkumi**: Var kļūt nekārtīgs, kad app aug ar vairāk notikumiem un maršrutiem.

### Opcija 2: Atsevišķs `events.php` fails
Nedaudz lielākam app, apsveriet iespēju pārvietot notikumu reģistrācijas uz speciālu failu, piemēram, `app/config/events.php`. Iekļaujiet šo failu `index.php` pirms maršrutiem. Tas līdzinās tam, kā maršruti bieži tiek organizēti `app/config/routes.php` Flight projektos.

```php
// app/config/events.php
Flight::onEvent('user.login', function ($username) {
    error_log("$username logged in at " . date('Y-m-d H:i:s'));  // Reģistrē pieteikšanos ar laiku.
});

Flight::onEvent('user.registered', function ($email, $name) {
    echo "E-pasts nosūtīts uz $email: Laipni lūgts, $name!";
});
```

```php
// index.php
require 'vendor/autoload.php';
require 'app/config/events.php';

Flight::route('/login', function () {
    $username = 'bob';
    Flight::triggerEvent('user.login', $username);
    echo "Pieslēdzies!";
});

Flight::start();
```
- **Priekšrocības**: Tur `index.php` fokusēts uz maršrutiem, loģiski organizē notikumus, viegli atrast un rediģēt.
- **Trūkumi**: Pievieno nedaudz struktūras, kas var šķist pārāk daudz ļoti maziem app.

### Opcija 3: Tuvu vietai, kur tie tiek izraisīti
Cita pieeja ir reģistrēt notikumus tuvu vietai, kur tie tiek izraisīti, piemēram, iekšā kontrolierī vai maršruta definīcijā. Tas darbojas labi, ja notikums ir specifisks vienai app daļai.

```php
Flight::route('/signup', function () {
    // Reģistrēt notikumu šeit
    Flight::onEvent('user.registered', function ($email) {
        echo "Laipni lūgts e-pasts nosūtīts uz $email!";  // Simulē e-pasta nosūtīšanu.
    });

    $email = 'jane@example.com';
    Flight::triggerEvent('user.registered', $email);
    echo "Reģistrēts!";
});
```
- **Priekšrocības**: Tur saistīto kodu kopā, labs izolētām funkcijām.
- **Trūkumi**: Izklāj notikumu reģistrācijas, padarot grūtāk redzēt visus notikumus vienā vietā; risks atkārtot reģistrācijas, ja neuzmanīgs.

### Labākā prakse Flight
- **Sāciet vienkārši**: Maziem app ievietojiet notikumus `index.php`. Tas ir ātri un atbilst Flight minimālismam.
- **Augiet gudri**: Kad app paplašinās (piemēram, vairāk nekā 5-10 notikumiem), izmantojiet `app/config/events.php` failu. Tas ir dabisks solis uz priekšu, līdzīgi kā organizēt maršrutus, un uztur kodu kārtīgu bez sarežģītām ietvēm.
- **Izvairieties no pārmērīgas sarežģītības**: Neveidojiet pilnīgu “notikumu pārvaldnieka” klasi vai direktoriju, ja vien app nav milzīgs — Flight uzplaukst vienkāršībā, tāpēc turiet to vieglu.

### Padoms: Grupējiet pēc mērķa
`events.php` failā grupējiet saistītos notikumus (piemēram, visi lietotājiem saistītie notikumi kopā) ar komentāriem skaidrībai:

```php
// app/config/events.php
// Lietotāju notikumi
Flight::onEvent('user.login', function ($username) {
    error_log("$username pi logged in");  // Reģistrē pieteikšanos.
});
Flight::onEvent('user.registered', function ($email) {
    echo "Laipni lūgts uz $email!";
});

// Lappušu notikumi
Flight::onEvent('page.updated', function ($pageId) {
    unset($_SESSION['pages'][$pageId]);  // Notīra sesijas kešu, ja piemērojams.
});
```

Šī struktūra labi mērojas un paliek iesācējiem draudzīga.

## Piemēri iesācējiem

Ejiet cauri dažiem reālas pasaules scenārijiem, lai parādītu, kā notikumi darbojas un kāpēc tie ir noderīgi.

### Piemērs 1: Reģistrēt lietotāja pieteikšanos
```php
// 1. solis: Reģistrēt klausītāju
Flight::onEvent('user.login', function ($username) {
    $time = date('Y-m-d H:i:s');
    error_log("$username pi logged in at $time");  // Reģistrē pieteikšanos ar laiku.
});

// 2. solis: Izraisa to app
Flight::route('/login', function () {
    $username = 'bob';  // Izlikts, ka tas nāk no formas
    Flight::triggerEvent('user.login', $username);
    echo "Sveiks, $username!";
});
```
**Kāpēc tas ir noderīgi**: Pieteikšanās kods nav jāsazinās ar reģistrēšanu — tas tikai izraisa notikumu. Vēlāk jūs varat pievienot vairāk klausītāju (piemēram, nosūtīt laipni lūgts e-pastu), nemainot maršrutu.

### Piemērs 2: Paziņot par jauniem lietotājiem
```php
// Klausītājs jaunām reģistrācijām
Flight::onEvent('user.registered', function ($email, $name) {
    // Simulē e-pasta nosūtīšanu
    echo "E-pasts nosūtīts uz $email: Laipni lūgts, $name!";
});

// Izraisa to, kad kāds reģistrējas
Flight::route('/signup', function () {
    $email = 'jane@example.com';
    $name = 'Jane';
    Flight::triggerEvent('user.registered', $email, $name);
    echo "Paldies par reģistrēšanos!";
});
```
**Kāpēc tas ir noderīgi**: Reģistrācijas loģika koncentrējas uz lietotāja izveidošanu, kamēr notikums apstrādā paziņojumus. Jūs varētu vēlāk pievienot vairāk klausītāju (piemēram, reģistrēt reģistrēšanos).

### Piemērs 3: Notīrīt kešu
```php
// Klausītājs, lai notīrītu kešu
Flight::onEvent('page.updated', function ($pageId) {
    unset($_SESSION['pages'][$pageId]);  // Notīra sesijas kešu, ja piemērojams
    echo "Kešs notīrīts lappusei $pageId.";
});

// Izraisa, kad lappuse tiek rediģēta
Flight::route('/edit-page/(@id)', function ($pageId) {
    // Izlikts, ka mēs atjauninājām lappusi
    Flight::triggerEvent('page.updated', $pageId);
    echo "Lappuse $pageId atjaunināta.";
});
```
**Kāpēc tas ir noderīgi**: Rediģēšanas kods neinteresējas par kešu — tas tikai signalizē atjauninājumu. Citas app daļas var reaģēt, kā nepieciešams.

## Labākās prakses

- **Nosaukiet notikumus skaidri**: Izmantojiet specifiskus nosaukumus, piemēram, `'user.login'` vai `'page.updated'`, lai būtu skaidrs, ko tie dara.
- **Turiet klausītājus vienkāršus**: Nelieciet lēnus vai sarežģītus uzdevumus klausītājos — turiet app ātru.
- **Testējiet savus notikumus**: Izraisa tos manuāli, lai nodrošinātu, ka klausītāji darbojas, kā gaidīts.
- **Izmantojiet notikumus gudri**: Tie ir lieliski decouplēšanai, bet pārāk daudzi var padarīt kodu grūti izsekojamu — izmantojiet tos, kad tas ir pamatoti.

Flight PHP notikumu sistēma ar `Flight::onEvent()` un `Flight::triggerEvent()`, piedāvā vienkāršu, bet spēcīgu veidu, kā veidot elastiskas aplikācijas. Ielaižot dažādas app daļas sazināties caur notikumiem, jūs varat turēt kodu organizētu, atkārtoti izmantojamu un viegli paplašināmu. Vai jūs reģistrējat darbības, nosūtāt paziņojumus vai pārvaldāt atjauninājumus, notikumi palīdz to darīt bez loģikas sajaukšanas. Turklāt, ar iespēju pārdefinēt šīs metodes, jums ir brīvība pielāgot sistēmu savām vajadzībām. Sāciet ar vienu notikumu un vērojiet, kā tas pārveido app struktūru!

## Iebūvētie notikumi

Flight PHP nāk ar dažiem iebūvētiem notikumiem, ko jūs varat izmantot, lai pieslēgtos framework dzīves ciklam. Šie notikumi tiek izsaukti specifiskos pieprasījuma/atbildes cikla punktos, ļaujot izpildīt pielāgotu loģiku, kad noteiktas darbības notiek.

### Iebūvētie notikumi saraksts
- **flight.request.received**: `function(Request $request)` Izsaukts, kad pieprasījums tiek saņemts, parsēts un apstrādāts.
- **flight.error**: `function(Throwable $exception)` Izsaukts, kad kļūda notiek pieprasījuma dzīves ciklā.
- **flight.redirect**: `function(string $url, int $status_code)` Izsaukts, kad tiek uzsākts pāradresējums.
- **flight.cache.checked**: `function(string $cache_key, bool $hit, float $executionTime)` Izsaukts, kad kešs tiek pārbaudīts specifiskai atslēgai un vai kešs trāpīts vai netrāpīts.
- **flight.middleware.before**: `function(Route $route)` Izsaukts pēc before middleware izpildes.
- **flight.middleware.after**: `function(Route $route)` Izsaukts pēc after middleware izpildes.
- **flight.middleware.executed**: `function(Route $route, $middleware, string $method, float $executionTime)` Izsaukts pēc jebkuras middleware izpildes
- **flight.route.matched**: `function(Route $route)` Izsaukts, kad maršruts ir saskaņots, bet vēl nav izpildīts.
- **flight.route.executed**: `function(Route $route, float $executionTime)` Izsaukts pēc maršruta izpildes un apstrādes. `$executionTime` ir laiks, kas vajadzīgs maršruta izpildei (zvana kontrolierim utt).
- **flight.view.rendered**: `function(string $template_file_path, float $executionTime)` Izsaukts pēc skata renderēšanas. `$executionTime` ir laiks, kas vajadzīgs šablona renderēšanai. **Piezīme: Ja jūs pārdefinējat `render` metodi, jums būs jāizsauc šis notikums atkārtoti.**
- **flight.response.sent**: `function(Response $response, float $executionTime)` Izsaukts pēc atbildes nosūtīšanas klientam. `$executionTime` ir laiks, kas vajadzīgs atbildes izveidošanai.