# Notikumu sistēma Flight PHP (v3.15.0+)

Flight PHP ievieš vieglu un intuitīvu notikumu sistēmu, kas ļauj reģistrēt un izsaukt pielāgotus notikumus jūsu lietojumprogrammi. Pievienojot `Flight::onEvent()` un `Flight::triggerEvent()`, tagad varat pieslēgties galvenajām jūsu lietojumprogrammas dzīves cikla brīžiem vai definēt savus notikumus, lai padarītu jūsu kodu modulārāku un izplešamu. Šīs metodes ir daļa no Flight **kartējamo metožu**, kas nozīmē, ka jūs varat pārrakstīt to uzvedību, lai atbilstu jūsu vajadzībām.

Šis ceļvedis aptver visu, kas jums jāzina, lai sāktu strādāt ar notikumiem, tostarp to, kāpēc tie ir vērtīgi, kā tos izmantot un praktiskus piemērus, lai palīdzētu iesācējiem saprast to jaudu.

## Kāpēc izmantot notikumus?

Notikumi ļauj jums atdalīt dažādas jūsu lietojumprogrammas daļas, lai tās nenovērstu pārāk stipri viena no otras. Šī atdalīšana—bieži saukta par **dekopēšanu**—padara jūsu kodu vieglāk atjaunināmu, paplašināmu vai atkļūdējamu. Tā vietā, lai rakstītu visu vienā lielā gabalā, jūs varat sadalīt loģiku mazākās, neatkarīgās daļās, kas reaģē uz specifiskām rīcībām (notikumiem).

Iedomājieties, ka jūs veidojat emuāra lietojumprogrammu:
- Kad lietotājs publicē komentāru, jūs varētu vēlēties:
  - Saglabāt komentāru datubāzē.
  - Nosūtīt e-pastu emuāra īpašniekam.
  - Pierakstīt rīcību drošībai.

Bez notikumiem jūs salieksiet visu šo vienā funkcijā. Ar notikumiem jūs varat to sadalīt: viena daļa saglabā komentāru, otra izsauc notikumu, piemēram, `'comment.posted'`, un atsevišķie klausītāji apstrādā e-pastu un ierakstīšanu. Tas padara jūsu kodu tīrāku un ļauj pievienot vai noņemt funkcijas (piemēram, paziņojumus) bez kodola loģikas skarsanas.

### Biežākās izmantošanas iespējas
- **Ierakstīšana**: Pierakstiet rīcības, piemēram, pieteikšanos vai kļūdas, bez jūsu galvenā koda sajaukšanas.
- **Paziņojumi**: Nosūtiet e-pastus vai brīdinājumus, kad notiek kaut kas.
- **Atjauninājumi**: Atsvaidziniet kešatmiņas vai paziņojiet citām sistēmām par izmaiņām.

## Notikumu klausītāju reģistrācija

Lai klausītos notikumu, izmantojiet `Flight::onEvent()`. Šī metode ļauj jums definēt, kas notiks, kad notikums notiks.

### Sintakse
```php
Flight::onEvent(string $event, callable $callback): void
```
- `$event`: Nosaukums jūsu notikumam (piemēram, `'user.login'`).
- `$callback`: Funkcija, kas tiks izpildīta, kad notikums tiks izsaukts.

### Kā tas darbojas
Jūs "abonējat" notikumu, sakot Flight, ko darīt, kad tas notiek. Atzīme var pieņemt argumentus, ko nodrošina notikuma izsaukums.

Flight notikumu sistēma ir sinhrona, kas nozīmē, ka katrs notikumu klausītājs tiek izpildīts secībā, viens pēc otra. Kad jūs izsaucat notikumu, visi reģistrētie klausītāji šim notikumam tiks izpildīti līdz beigām, pirms jūsu kods turpinās. Tas ir svarīgi saprast, jo tas atšķiras no asinhronām notikumu sistēmām, kur klausītāji var darboties paralēli vai vēlā laikā.

### Vienkāršs piemērs
```php
Flight::onEvent('user.login', function ($username) {
    echo "Laipni lūdzam atpakaļ, $username!";
});
```
Šeit, kad notiek notikums `'user.login'`, tas sveicina lietotāju pa vārdam.

### Galvenie punkti
- Jūs varat pievienot vairākus klausītājus vienam un tam pašam notikumam—tie izpildīsies reģistrācijas secībā.
- Atzīme var būt funkcija, anonīma funkcija vai metode no klases.

## Notikumu izsaukšana

Lai notikums notiktu, izmantojiet `Flight::triggerEvent()`. Tas saka Flight, lai izpildītu visus klausītājus, kuri ir reģistrēti šim notikumam, nododot visas datus, ko jūs sniedzat.

### Sintakse
```php
Flight::triggerEvent(string $event, ...$args): void
```
- `$event`: Notikuma nosaukums, kuru izsaucat (jāatbilst reģistrētajam notikumam).
- `...$args`: Opcionāli argumenti, ko nosūtīt klausītājiem (var būt jebkurš argumentu skaits).

### Vienkāršs piemērs
```php
$username = 'alice';
Flight::triggerEvent('user.login', $username);
```
Tas izsauc notikumu `'user.login'` un nosūta `'alice'` klausītājam, ko mēs iepriekš definējām, kas izvadīs: `Laipni lūdzam atpakaļ, alice!`.

### Galvenie punkti
- Ja nav reģistrētu klausītāju, nekas nenotiek—jūsu lietojumprogramma nesabrūk.
- Izmantojiet izklāšanas operatoru (`...`), lai elastīgi nodotu vairākus argumentus.

### Notikumu klausītāju reģistrācija

...

**Nākamo klausītāju apstādināšana**:
Ja klausītājs atgriež `false`, nekādi papildu klausītāji šim notikumam netiks izpildīti. Tas ļauj jums pārtraukt notikumu ķēdi, pamatojoties uz specifiskām nosacījumiem. Atcerieties, ka klausītāju secība ir svarīga, jo pirmais, kurš atgriež `false`, apstās pārējos no izpildes.

**Piemērs**:
```php
Flight::onEvent('user.login', function ($username) {
    if (isBanned($username)) {
        logoutUser($username);
        return false; // Apstājas sekojošo klausītāju izpilde
    }
});
Flight::onEvent('user.login', function ($username) {
    sendWelcomeEmail($username); // šis nekad netiks nosūtīts
});
```

## Notikumu metožu pārrakstīšana

`Flight::onEvent()` un `Flight::triggerEvent()` ir pieejami [paplašināšanai](/learn/extending), kas nozīmē, ka jūs varat pārrakstīt, kā tās darbojas. Tas ir lieliski piemērots progresīviem lietotājiem, kuri vēlas pielāgot notikumu sistēmu, piemēram, pievienojot ierakstīšanu vai mainot veidu, kā notikumi tiek izsaukti.

### Piemērs: Pielāgošana `onEvent`
```php
Flight::map('onEvent', function (string $event, callable $callback) {
    // Pieraksta katru notikumu reģistrāciju
    error_log("Jauns notikumu klausītājs pievienots: $event");
    // Izsaukt noklusējuma uzvedību (pieņemot, ka ir iekšēja notikumu sistēma)
    Flight::_onEvent($event, $callback);
});
```
Tagad, katru reizi, kad jūs reģistrējat notikumu, tas tiek pierakstīts pirms turpināšanas.

### Kāpēc pārrakstīt?
- Pievienot atkļūdošanu vai uzraudzību.
- Ierobežot notikumus noteiktās vidēs (piemēram, atslēgt testēšanas laikā).
- Integrēt ar citu notikumu bibliotēku.

## Kur ievietot savus notikumus

Kā iesācējam, jūs varētu brīnīties: *kur reģistrēt visus šos notikumus manā lietojumprogrammā?* Flight vienkāršība nozīmē, ka nav stingru noteikumu—jūs varat tos ievietot tur, kur tas ir jēgpilni jūsu projektam. Tomēr, saglabājot tos organizētus, jūs palīdzat uzturēt savu kodu, kad jūsu lietojumprogramma aug. Šeit ir daži praktiski varianti un labākās prakses, kas pielāgotas Flight vieglajai dabai:

### Variants 1: Jūsu galvenajā `index.php`
Mazām lietojumprogrammām vai ātrām prototipiem jūs varat reģistrēt notikumus tieši savā `index.php` failā blakus savām maršrutām. Tas viss saglabā vienā vietā, kas ir labi tad, kad vienkāršība ir jūsu prioritāte.

```php
require 'vendor/autoload.php';

// Reģistrēt notikumus
Flight::onEvent('user.login', function ($username) {
    error_log("$username piesakās plkst. " . date('Y-m-d H:i:s'));
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
- **Trūkumi**: Var kļūt nekārtīgi, kamēr jūsu lietojumprogramma aug ar vairāk notikumiem un maršrutiem.

### Variants 2: Atsevišķs `events.php` fails
Nedaudz lielākai lietojumprogrammai apsveriet iespēju pārvietot notikumu reģistrāciju uz veltītu failu, piemēram, `app/config/events.php`. Iekļaujiet šo failu savā `index.php` pirms maršrutiem. Tas atgādina par to, kā maršruti bieži tiek organizēti `app/config/routes.php` Flight projektos.

```php
// app/config/events.php
Flight::onEvent('user.login', function ($username) {
    error_log("$username piesakās plkst. " . date('Y-m-d H:i:s'));
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
    echo "Pieslēdzies!";
});

Flight::start();
```
- **Priekšrocības**: Saglabā `index.php` koncentrētas uz maršrutiem, organizē notikumus loģiski, viegli atrast un rediģēt.
- **Trūkumi**: Pievieno nelielu struktūru, kas var šķist pārspīlēta ļoti mazām lietojumprogrammām.

### Variants 3: Tuvojieties vietai, kur tie tiek izsaukti
Cits pieejas veids ir reģistrēt notikumus tuvu tam, kur tie tiek izsaukti, piemēram, kontroliera vai maršruta definīcijā. Tas labi darbojas, ja notikums ir specifisks vienai jūsu lietojumprogrammas daļai.

```php
Flight::route('/signup', function () {
    // Reģistrēt notikumu šeit
    Flight::onEvent('user.registered', function ($email) {
        echo "Laipni lūdzam e-pastā $email!";
    });

    $email = 'jane@example.com';
    Flight::triggerEvent('user.registered', $email);
    echo "Reģistrējies!";
});
```
- **Priekšrocības**: Saglabā saistīto kodu kopā, labi izolētām funkcijām.
- **Trūkumi**: Izkliedē notikumu reģistrācijas, padarot grūtāk redzēt visus notikumus uzreiz; risks dublēt reģistrācijas, ja neesat uzmanīgs.

### Labākā prakse Flight
- **Sākt vienkārši**: Īsām lietojumprogrammām ievietojiet notikumus `index.php`. Tas ir ātri un atbilst Flight minimālismam.
- **Izaugsme gudri**: Kad jūsu lietojumprogramma paplašinās (piemēram, vairāk nekā 5-10 notikumu), izmantojiet `app/config/events.php` failu. Tas ir dabisks solis uz augšu, kā organizēt maršrutus, un uztur jūsu kodu tīru bez sarežģītu ietvaru pievienošanas.
- **Izvairieties no pārmērīgas inženierijas**: Nelietojiet radīt pilnīgu "notikumu pārvaldnieka" klasi vai direktoriju, ja vien jūsu lietojumprogramma kļūst liela—Flight uzplaukst uz vienkāršības, tāpēc saglabājiet to vieglu.

### Ieteikums: Grupējiet pēc mērķa
Failā `events.php` grupējiet saistītus notikumus (piemēram, visus lietotājiem saistītos notikumus) ar komentāriem skaidrībai:

```php
// app/config/events.php
// Lietotāju notikumi
Flight::onEvent('user.login', function ($username) {
    error_log("$username piesakās");
});
Flight::onEvent('user.registered', function ($email) {
    echo "Laipni lūdzam $email!";
});

// Lapas notikumi
Flight::onEvent('page.updated', function ($pageId) {
    unset($_SESSION['pages'][$pageId]);
});
```

Šī struktūra labi attīsta un paliek draudzīga iesācējiem.

## Piemēri iesācējiem

Apskatīsim reālas dzīves scenārijus, lai parādītu, kā notikumi darbojas un kāpēc tie ir noderīgi.

### Piemērs 1: Lietotāja pieteikšanās ierakstīšana
```php
// 1. solis: Reģistrēt klausītāju
Flight::onEvent('user.login', function ($username) {
    $time = date('Y-m-d H:i:s');
    error_log("$username piesakās plkst. $time");
});

// 2. solis: Izsaukt to savā lietojumprogrammā
Flight::route('/login', function () {
    $username = 'bob'; // Iedomājieties, ka tas nāk no formas
    Flight::triggerEvent('user.login', $username);
    echo "Sveiks, $username!";
});
```
**Kāpēc tas ir noderīgi**: Pieteikšanās kodam nav jāzina par ierakstīšanu—tas vienkārši izsauc notikumu. Vēlāk jūs varat pievienot vairāk klausītāju (piemēram, nosūtīt sveiciena e-pastu) bez izmaiņām maršrutā.

### Piemērs 2: Jauno lietotāju paziņošana
```php
// Klausītājs jaunām reģistrācijām
Flight::onEvent('user.registered', function ($email, $name) {
    // Simulēt e-pasta sūtīšanu
    echo "E-pasts nosūtīts uz $email: Laipni lūdzam, $name!";
});

// Izsaukt to, kad kāds reģistrējas
Flight::route('/signup', function () {
    $email = 'jane@example.com';
    $name = 'Jane';
    Flight::triggerEvent('user.registered', $email, $name);
    echo "Paldies par reģistrēšanos!";
});
```
**Kāpēc tas ir noderīgi**: Reģistrācijas loģika koncentrējas uz lietotāja izveidi, bet notikums apstrādā paziņojumus. Jūs varat vēlāk pievienot vairāk klausītāju (piemēram, ierakstīt reģistrāciju) pēc nepieciešamības.

### Piemērs 3: Kešatmiņas notīrīšana
```php
// Klausītājs kešatmiņas tīrīšanai
Flight::onEvent('page.updated', function ($pageId) {
    unset($_SESSION['pages'][$pageId]); // Tīra sesijas kešatmiņu, ja piemērojams
    echo "Kešatmiņa notīrīta lapai $pageId.";
});

// Izsaukt to, kad lapa ir rediģēta
Flight::route('/edit-page/(@id)', function ($pageId) {
    // Iedomājieties, ka mēs atjauninājām lapu
    Flight::triggerEvent('page.updated', $pageId);
    echo "Lapa $pageId atjaunināta.";
});
```
**Kāpēc tas ir noderīgi**: Rediģēšanas kodam nav jārūpējas par kešatmiņu—tas vienkārši signalizē atjauninājumu. Citas lietojumprogrammas daļas var reaģēt pēc nepieciešamības.

## Labākās prakses

- **Skaidri nosauciet notikumus**: Izmantojiet specifiskus nosaukumus, piemēram, `'user.login'` vai `'page.updated'`, lai būtu acīmredzams, ko viņi dara.
- **Saglabājiet klausītājus vienkāršus**: Neievietojiet lēnus vai sarežģītus uzdevumus klausītājos—saglabājiet savu lietojumprogrammu ātru.
- **Pārbaudiet savus notikumus**: Izsauciet tos manuāli, lai nodrošinātu, ka klausītāji darbojas kā paredzēts.
- **Izmantojiet notikumus saprātīgi**: Tie ir lieliski dekopēšanai, bet pārāk daudzi var padarīt jūsu kodu grūti saprotamu—izmantojiet tos, kad tas ir jēgpilni.

Notikumu sistēma Flight PHP, ar `Flight::onEvent()` un `Flight::triggerEvent()`, sniedz jums vienkāršu, bet jaudīgu veidu, kā izveidot elastīgas lietojumprogrammas. Ļaujot dažādām jūsu lietojumprogrammas daļām sazināties viena ar otru caur notikumiem, jūs varat saglabāt savu kodu organizētu, atkārtoti izmantojamu un viegli paplašināmu. Neatkarīgi no tā, vai jūs reģistrējat darbības, sūtāt paziņojumus vai pārvaldat atjauninājumus, notikumi palīdz jums to izdarīt bez jūsu loģikas sajaukuma. Turklāt, ar iespēju pārrakstīt šīs metodes, jums ir brīvība pielāgot sistēmu jūsu vajadzībām. Sāciet ar vienu notikumu, un skatiet, kā tas transformē jūsu lietojumprogrammas struktūru!

## Iebūvētie notikumi

Flight PHP nāk ar dažiem iebūvētiem notikumiem, kurus varat izmantot, lai pieslēgtos ietvara dzīves ciklam. Šie notikumi tiek izsaukti konkrētos punktos pieprasījuma/atbildes ciklā, ļaujot jums izpildīt pielāgotu loģiku, kad notiek noteiktas darbības.

### Iebūvēto notikumu saraksts
- `flight.request.received`: Izsaukts, kad pieprasījums tiek saņemts, analizēts un apstrādāts.
- `flight.route.middleware.before`: Izsaukts pēc tam, kad ir izpildīta "pirms" starpdaļa.
- `flight.route.middleware.after`: Izsaukts pēc tam, kad ir izpildīta "pēcpusdienas" starpdaļa.
- `flight.route.executed`: Izsaukts, kad maršruts tiek izpildīts un apstrādāts.
- `flight.response.sent`: Izsaukts pēc tam, kad atbilde ir nosūtīta klientam.