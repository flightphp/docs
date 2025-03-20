# Pasākumu sistēma Flight PHP (v3.15.0+)

Flight PHP ievieš vieglu un intuitīvu pasākumu sistēmu, kas ļauj reģistrēt un izsistīt pielāgotus pasākumus jūsu lietojumprogrammā. Pievienojot `Flight::onEvent()` un `Flight::triggerEvent()`, jūs tagad varat pieslēgties jūsu lietojumprogrammas dzīves cikla nozīmīgajiem brīžiem vai definēt savus pasākumus, lai padarītu jūsu kodu modulāru un paplašināmu. Šīs metodes ir daļa no Flight **kartējamajām metodēm**, tas nozīmē, ka jūs varat pārdefinēt to uzvedību, lai pielāgotu to savām vajadzībām.

Šis ceļvedis aptver visu, kas jums jāzina, lai uzsāktu darbu ar pasākumiem, ieskaitot, kāpēc tie ir vērtīgi, kā tos izmantot un praktiskus piemērus, lai palīdzētu iesācējiem saprast to potenciālu.

## Kāpēc izmantot pasākumus?

Pasākumi ļauj jums atdalīt dažādas jūsu lietojumprogrammas daļas, lai tās stipri neatkarētu viena no otras. Šī atdalīšana—bieži saukta par **atslēgšanu**—padara jūsu kodu vieglāk atjauninātu, paplašinātu vai labotu. Tā vietā, lai uzrakstītu visu vienā lielā blokā, jūs varat sadalīt savu loģiku mazākos, neatkarīgos gabalos, kas reaģē uz konkrētām darbībām (pasākumiem).

Iedomājieties, ka jūs veidojat bloga lietojumprogrammu:
- Kad lietotājs ievieto komentāru, jūs varat vēlēties:
  - Saglabāt komentāru datu bāzē.
  - Nosūtīt e-pastu bloga īpašniekam.
  - Reģistrēt darbību drošībai.

Bez pasākumiem jūs visu šo iepazīsiet vienā funkcijā. Ar pasākumiem jūs varat to sadalīt: viena daļa saglabā komentāru, otra izsist pasākumu, piemēram, `'comment.posted'`, un atsevišķi klausītāji apstrādā e-pastu un reģistrēšanu. Tas saglabā jūsu kodu tīrāku un ļauj jums pievienot vai noņemt funkcijas (piemēram, paziņojumus), nepieskaroties pamatloģikai.

### Bieži lietojumi
- **Reģistrēšana**: Ierakstīt darbības, piemēram, pieteikšanos vai kļūdas, nepiesārņojot jūsu galveno kodu.
- **Paziņojumi**: Nosūtīt e-pastus vai brīdinājumus, kad notiek kaut kas.
- **Atjauninājumi**: Atsvaidzināt kešatmiņas vai paziņot citām sistēmām par izmaiņām.

## Pasākumu klausītāju reģistrēšana

Lai klausītos uz pasākumu, izmantojiet `Flight::onEvent()`. Šī metode ļauj jums definēt, kas notiks, kad pasākums notiek.

### Sintakse
```php
Flight::onEvent(string $event, callable $callback): void
```
- `$event`: Nosaukums jūsu pasākumam (piemēram, `'user.login'`).
- `$callback`: Funkcija, kas jāizpilda, kad pasākums tiek izsists.

### Kā tas darbojas
Jūs "pierakstāties" uz pasākumu, sakot Flight, ko darīt, kad tas notiek. Atgriezeniskā funkcija var pieņemt argumentus, kas tiek nodoti no pasākuma izsistšanas.

Flight pasākumu sistēma ir sinhrona, kas nozīmē, ka katrs pasākumu klausītājs tiek izpildīts secīgi, viens pēc otra. Kad jūs izsitat pasākumu, visi reģistrētie klausītāji šim pasākumam tiks izpildīti līdz beigām, pirms jūsu kods turpinās. Tas ir svarīgi saprast, jo tas atšķiras no asinkronajiem pasākumu sistēmām, kur klausītāji var darboties paralēli vai vēlāk.

### Vienkāršs piemērs
```php
Flight::onEvent('user.login', function ($username) {
    echo "Laipni lūdzam atpakaļ, $username!";
});
```
Šeit, kad pasākums `'user.login'` tiek izsists, tas sveicinās lietotāju pēc vārda.

### Galvenie punkti
- Jūs varat pievienot vairākus klausītājus vienam un tam pašam pasākumam—tie darbosies tajā secībā, kādā jūs tos reģistrējāt.
- Atgriezeniskā funkcija var būt funkcija, anonīma funkcija vai metode no klases.

## Pasākumu izsistīšana

Lai izsistītu pasākumu, izmantojiet `Flight::triggerEvent()`. Tas norāda Flight, lai izpildītu visus klausītājus, kas reģistrēti šim pasākumam, nododot jebkādus datus, ko sniedzat.

### Sintakse
```php
Flight::triggerEvent(string $event, ...$args): void
```
- `$event`: Pasākuma nosaukums, ko jūs izsitat (jāatbilst reģistrētam pasākumam).
- `...$args`: Papildus argumenti, kurus nosūtīt klausītājiem (var būt jebkura skaita argumenti).

### Vienkāršs piemērs
```php
$username = 'alice';
Flight::triggerEvent('user.login', $username);
```
Tas izsist pasākumu `'user.login'` un nosūta `'alice'` klausītājam, ko mēs definējām iepriekš, kas izvadīs: `Laipni lūdzam atpakaļ, alice!`.

### Galvenie punkti
- Ja nav reģistrētu klausītāju, nekas nenotiek—jūsu lietojumprogramma nesabruks.
- Izmantojiet izplatīšanas operatoru (`...`), lai elastīgi nodotu vairākus argumentus.

### Pasākumu klausītāju reģistrēšana

...

**Turbīnā turpmāks klausītājs**:
Ja klausītājs atgriež `false`, nekādi papildu klausītāji šim pasākumam netiks izpildīti. Tas ļauj jums apturēt pasākumu ķēdi, pamatojoties uz noteiktām nosacījumiem. Atcerieties, ka klausītāju secība ir svarīga, jo pirmais, kas atgriež `false`, apturēs pārējo izpildi.

**Piemērs**:
```php
Flight::onEvent('user.login', function ($username) {
    if (isBanned($username)) {
        logoutUser($username);
        return false; // Aptur nākamos klausītājus
    }
});
Flight::onEvent('user.login', function ($username) {
    sendWelcomeEmail($username); // šis nekad netiks nosūtīts
});
```

## Pasākumu metožu pārdefinēšana

`Flight::onEvent()` un `Flight::triggerEvent()` ir pieejami, lai tiktu [paplašināti](/learn/extending), tas nozīmē, ka jūs varat pārdefinēt to darbību. Tas ir lieliski piemērots pieredzējušiem lietotājiem, kuri vēlas pielāgot pasākumu sistēmu, piemēram, pievienojot reģistrēšanu vai mainot, kā pasākumi tiek izsisti.

### Piemērs: Pielāgojot `onEvent`
```php
Flight::map('onEvent', function (string $event, callable $callback) {
    // Reģistrēt katru pasākumu reģistrāciju
    error_log("Jauns pasākumu klausītājs pievienots: $event");
    // Izsist standarta uzvedību (pieņemot, ka ir iekšēja pasākumu sistēma)
    Flight::_onEvent($event, $callback);
});
```
Tagad, katru reizi, kad jūs reģistrējat pasākumu, tas tiek ierakstīts pirms turpināšanas.

### Kāpēc pārdefinēt?
- Pievienot atkļūdošanu vai uzraudzību.
- Ierobežot pasākumus noteiktās vidēs (piemēram, izslēgt testēšanas laikā).
- Integrēt ar citu pasākumu bibliotēku.

## Kur ievietot savus pasākumus

Kā iesācējs, jūs varat jautāt: *kur reģistrēt visus šos pasākumus savā lietojumprogrammā?* Flight vienkāršība nozīmē, ka nav stingru noteikumu—jūs varat tos ievietot kur vien tas ir piemērots jūsu projektam. Tomēr, saglabājot tos organizētus, jūs palīdzat uzturēt savu kodu, kad jūsu lietojumprogramma aug. Šeit ir daži praktiski varianti un labākās prakses, pielāgotas Flight vieglajai būtībai:

### Variants 1: Jūsu galvenajā `index.php`
Nelielām lietojumprogrammām vai ātriem prototipiem jūs varat reģistrēt pasākumus tieši savā `index.php` failā kopā ar maršrutiem. Tas viss saglabā vienā vietā, kas ir labi, kad vienkāršība ir jūsu galvenā prioritāte.

```php
require 'vendor/autoload.php';

// Reģistrēt pasākumus
Flight::onEvent('user.login', function ($username) {
    error_log("$username pieteicies " . date('Y-m-d H:i:s'));
});

// Definēt maršrutus
Flight::route('/login', function () {
    $username = 'bob';
    Flight::triggerEvent('user.login', $username);
    echo "Pieteicies!";
});

Flight::start();
```
- **Priekšrocības**: Vienkārši, nav papildu failu, lieliski maziem projektiem.
- **Trūkumi**: Var kļūt haotiski, kad jūsu lietojumprogramma aug ar vairāk pasākumiem un maršrutiem.

### Variants 2: Atsevišķs `events.php` fails
Nedaudz lielākai lietojumprogrammai apsveriet iespēju pārvietot pasākumu reģistrācijas uz veltītu failu, piemēram, `app/config/events.php`. Iekļaujiet šo failu savā `index.php` pirms maršrutiem. Tas atdarina, kā maršruti bieži tiek organizēti `app/config/routes.php` Flight projektos.

```php
// app/config/events.php
Flight::onEvent('user.login', function ($username) {
    error_log("$username pieteicies " . date('Y-m-d H:i:s'));
});

Flight::onEvent('user.registered', function ($email, $name) {
    echo "E-pasts nosūtīts uz $email: Laipni lūdzam, $name!";
});
```

```php
// index.php
require 'vendor/autoload.php';
require 'app/config/events.php';

Flight::route('/login', function () {
    $username = 'bob';
    Flight::triggerEvent('user.login', $username);
    echo "Pieteicies!";
});

Flight::start();
```
- **Priekšrocības**: Saglabā `index.php` koncentrēts uz maršrutiem, organizē pasākumus loģiski, viegli atrast un rediģēt.
- **Trūkumi**: Pievieno nedaudz struktūras, kas var šķist pārspīlēti ļoti maziem gabaliem.

### Variants 3: Tuvojoties, kur viņi tiek izsisti
Vēl viens pieejas veids ir reģistrēt pasākumus tuvu vietai, kur tie tiek izsisti, piemēram, iekšā kontrolierī vai maršruta definīcijā. Tas labi darbojas, ja pasākums ir specifisks vienai jūsu lietojumprogrammas daļai.

```php
Flight::route('/signup', function () {
    // Reģistrēt pasākumu šeit
    Flight::onEvent('user.registered', function ($email) {
        echo "Laipni lūdzam e-pastā nosūtītā $email!";
    });

    $email = 'jane@example.com';
    Flight::triggerEvent('user.registered', $email);
    echo "Pieteicies!";
});
```
- **Priekšrocības**: Saglabā saistīto kodu kopā, labi izolētām funkcijām.
- **Trūkumi**: Izkliedē pasākumu reģistrācijas, apgrūtina visu pasākumu vienlaicīgu apskati; riski dubultiem reģistrācijām, ja neesat uzmanīgs.

### Labākā prakse Flight
- **Sāciet ar vienkāršu**: Ļoti maziem gabaliem ievietojiet pasākumus `index.php`. Tas ir ātri un atbilst Flight minimālismam.
- **Audziniet gudri**: Kad jūsu lietojumprogramma paplašinās (piemēram, vairāk nekā 5-10 pasākumi), izmantojiet `app/config/events.php` failu. Tas ir dabisks solis uz augšu, piemēram, maršrutu organizēšana, un saglabā jūsu kodu kārtīgu bez sarežģītu sistēmu pievienošanas.
- **Izvairieties no pārmērīgas struktūras**: Nepievienojiet pilnvērtīgu "pasākumu pārvaldīšanas" klasi vai direktoriju, ja jūsu lietojumprogramma kļūst milzīga—Flight uzplaukst vienkāršībā, tādēļ saglabājiet to vieglu.

### Padoms: Grupējiet pēc mērķa
Failā `events.php` grupējiet saistītus pasākumus (piemēram, visus lietotāju saistītos pasākumus kopā) ar komentāriem skaidrībai:

```php
// app/config/events.php
// Lietotāju pasākumi
Flight::onEvent('user.login', function ($username) {
    error_log("$username pieteicies");
});
Flight::onEvent('user.registered', function ($email) {
    echo "Laipni lūdzam uz $email!";
});

// Lapas pasākumi
Flight::onEvent('page.updated', function ($pageId) {
    unset($_SESSION['pages'][$pageId]);
});
```

Šī struktūra labi paplašinās un paliks draudzīga iesācējiem.

## Piemēri iesācējiem

Pastaigāsim cauri dažiem reālās pasaules scenārijiem, lai parādītu, kā pasākumi darbojas un kāpēc tie ir noderīgi.

### Piemērs 1: Lietotāja pieteikšanās reģistrēšana
```php
// Solis 1: Reģistrēt klausītāju
Flight::onEvent('user.login', function ($username) {
    $time = date('Y-m-d H:i:s');
    error_log("$username pieteicies plkst. $time");
});

// Solis 2: Izsist to savā lietojumprogrammā
Flight::route('/login', function () {
    $username = 'bob'; // Pieņemiet, ka tas nāk no formas
    Flight::triggerEvent('user.login', $username);
    echo "Sveiki, $username!";
});
```
**Kāpēc tas ir noderīgi**: Pieteikšanās kods nenojauš par reģistrēšanu—tas tikai izsisti pasākumu. Jūs vēlāk varat pievienot vairāk klausītāju (piemēram, nosūtīt sveiciena e-pastu) bez maršruta mainīšanas.

### Piemērs 2: Paziņošana par jauniem lietotājiem
```php
// Klausītājs jaunām reģistrācijām
Flight::onEvent('user.registered', function ($email, $name) {
    // Simulējiet e-pasta nosūtīšanu
    echo "E-pasts nosūtīts uz $email: Laipni lūdzam, $name!";
});

// Izsisti to, kad kāds piesakās
Flight::route('/signup', function () {
    $email = 'jane@example.com';
    $name = 'Jane';
    Flight::triggerEvent('user.registered', $email, $name);
    echo "Paldies, ka reģistrējāties!";
});
```
**Kāpēc tas ir noderīgi**: Reģistrācijas loģika koncentrējas uz lietotāja radīšanu, kamēr pasākums apstrādā paziņojumus. Jūs varētu vēlāk pievienot vairāk klausītāju (piemēram, reģistrēt reģistrāciju) gadījumā, ja tas būs nepieciešams.

### Piemērs 3: Kešatmiņas dzēšana
```php
// Klausītājs kešatmiņas dzēšanai
Flight::onEvent('page.updated', function ($pageId) {
    unset($_SESSION['pages'][$pageId]); // Izmet kešatmiņu sesijā, ja nepieciešams
    echo "Kešatmiņa dzēsta lapai $pageId.";
});

// Izsist to, kad lapa tiek rediģēta
Flight::route('/edit-page/(@id)', function ($pageId) {
    // Pieņemiet, ka mēs esam atjauninājuši lapu
    Flight::triggerEvent('page.updated', $pageId);
    echo "Lapa $pageId atjaunināta.";
});
```
**Kāpēc tas ir noderīgi**: Rediģēšanas kods nezina par kešatmiņu—tas tikai signalizē par atjauninājumu. Citas lietojumprogrammas daļas var reaģēt, kā nepieciešams.

## Labākās prakses

- **Nosakiet pasākumus skaidri**: Izmantojiet specifiskus nosaukumus, piemēram, `'user.login'` vai `'page.updated'`, lai būtu acīmredzams, ko viņi dara.
- **Saglabājiet klausītājus vienkāršus**: Neievietojiet lēnus vai sarežģītus uzdevumus klausītājos—turiet savu lietojumprogrammu ātru.
- **Testējiet savus pasākumus**: Manuāli izsistiet tos, lai pārliecinātos, ka klausītāji darbojas kā paredzēts.
- **Izmantojiet pasākumus saprātīgi**: Tie ir lieliski, lai atsvaidzinātu, bet pārāk daudz var padarīt jūsu kodu grūti izsekojamu—izmantojiet tos, kad tas ir jēgas.

Pasākumu sistēma Flight PHP, izmantojot `Flight::onEvent()` un `Flight::triggerEvent()`, piedāvā jums vienkāršu, bet spēcīgu veidu, kā veidot elastīgas lietojumprogrammas. Ļaujot dažādām jūsu lietojumprogrammas daļām sazināties viena ar otru caur pasākumiem, jūs varat saglabāt savu kodu organizētu, atkārtoti izmantojamu un viegli paplašināmu. Neatkarīgi no tā, vai reģistrējat darbības, nosūtāt paziņojumus vai pārvaldāt atjauninājumus, pasākumi palīdz to darīt bez loģikas sapīšanos. Turklāt, ar iespēju pārdefinēt šīs metodes, jums ir brīvība pielāgot sistēmu savām vajadzībām. Sāciet ar mazu pasākumu un skatieties, kā tas transformē jūsu lietojumprogrammas struktūru!

## Iebūvēti pasākumi

Flight PHP nāk ar dažiem iebūvētiem pasākumiem, kurus jūs varat izmantot, lai pieslēgtos rāmja dzīves ciklam. Šie pasākumi tiek izsisti noteiktos punktos pieprasījuma/atbildes ciklā, ļaujot jums izpildīt pielāgotu loģiku, kad noteiktas darbības notiek.

### Iebūvēto pasākumu saraksts
- **flight.request.received**: `function(Request $request)` Izsists, kad pieprasījums ir saņemts, analizēts un apstrādāts.
- **flight.error**: `function(Throwable $exception)` Izsists, kad notiek kļūda pieprasījuma dzīves ciklā.
- **flight.redirect**: `function(string $url, int $status_code)` Izsists, kad tiek uzsākta novirzīšana.
- **flight.cache.checked**: `function(string $cache_key, bool $hit, float $executionTime)` Izsists, kad kešatmiņa tiek pārbaudīta attiecīgajam atslēgai un, vai kešatmiņa ir situsi vai ne.
- **flight.middleware.before**: `function(Route $route)` Izsists pēc tam, kad pirms starpprogrammas ir izpildīta.
- **flight.middleware.after**: `function(Route $route)` Izsists pēc tam, kad pēc starpprogrammas ir izpildīta.
- **flight.middleware.executed**: `function(Route $route, $middleware, string $method, float $executionTime)` Izsists pēc tam, kad jebkura starpprogramma ir izpildīta
- **flight.route.matched**: `function(Route $route)` Izsists, kad maršruts ir saskaņots, bet vēl nav izpildīts.
- **flight.route.executed**: `function(Route $route, float $executionTime)` Izsists pēc tam, kad maršruts ir izpildīts un apstrādāts. `$executionTime` ir laiks, kas bija vajadzīgs maršruta izpildei (kontroliera izsaukšanai utt.).
- **flight.view.rendered**: `function(string $template_file_path, float $executionTime)` Izsists pēc tam, kad skats ir izveidots. `$executionTime` ir laiks, kas bija vajadzīgs, lai parādītu veidni. **Piezīme: Ja jūs pārdefinējat `render` metodi, jums būs nepieciešams atkārtoti izsist šo pasākumu.**
- **flight.response.sent**: `function(Response $response, float $executionTime)` Izsists pēc tam, kad atbilde ir nosūtīta klientam. `$executionTime` ir laiks, kas bija vajadzīgs, lai izveidotu atbildi.