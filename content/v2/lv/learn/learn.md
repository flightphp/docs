# Mācīties

Šī lapa ir ceļvedis, kā apgūt Flight. Tajā tiek aplūkoti pamati par ietvaru un tā izmantošanu.

## <a name="routing"></a> Maršrutēšana

Maršrutēšana Flight tiek veikta, saskaņojot URL paraugu ar atgriezeniskās sasaistes funkciju.

``` php
Flight::route('/', function(){
    echo 'sveika pasaule!';
});
```

Atgriezeniskā funkcija var būt jebkurš objekts, kas ir izsaucams. Tātad jūs varat izmantot parasto funkciju:

``` php
function hello(){
    echo 'sveika pasaule!';
}

Flight::route('/', 'hello');
```

Vai arī klases metodi:

``` php
class Greeting {
    public static function hello() {
        echo 'sveika pasaule!';
    }
}

Flight::route('/', array('Greeting','hello'));
```

Vai arī objekta metodi:

``` php
class Greeting
{
    public function __construct() {
        $this->name = 'Džons Deo';
    }

    public function hello() {
        echo "Sveiks, {$this->name}!";
    }
}

$greeting = new Greeting();

Flight::route('/', array($greeting, 'hello'));
```

Maršruti tiek saskaņoti secībā, kā tie tiek definēti. Pirmais maršruts, kas atbilst pieprasījumam, tiks izsaukts.

### Metodes maršrutēšana

Pēc noklusējuma maršruta paraugi tiek saskaņoti pret visām pieprasījuma metodēm. Jūs varat atbildēt uz konkrētām metodēm, novietojot identifikatoru pirms URL.

``` php
Flight::route('GET /', function(){
    echo 'Es saņēmu GET pieprasījumu.';
});

Flight::route('POST /', function(){
    echo 'Es saņēmu POST pieprasījumu.';
});
```

Jūs varat arī maršrutēt vairākas metodes uz vienu atgriezenisko sasaisti, izmantojot `|` delimitatoru:

``` php
Flight::route('GET|POST /', function(){
    echo 'Es saņēmu vai nu GET, vai POST pieprasījumu.';
});
```

### Regulārie izteicieni

Jūs varat izmantot regulāros izteicienus savos maršrutos:

``` php
Flight::route('/user/[0-9]+', function(){
    // Tas atbilst /user/1234
});
```

### Nosauktie parametri

Jūs varat norādīt nosauktos parametrus savos maršrutos, kuri tiks nodoti jūsu atgriezeniskās funkcijai.

``` php
Flight::route('/@name/@id', function($name, $id){
    echo "sveiks, $name ($id)!";
});
```

Jūs varat arī iekļaut regulāros izteicienus ar saviem nosauktajiem parametriem, izmantojot `:` delimitatoru:

``` php
Flight::route('/@name/@id:[0-9]{3}', function($name, $id){
    // Tas atbilst /bob/123
    // Bet neatbilst /bob/12345
});
```

### Nepieciešamie parametri

Jūs varat norādīt nosauktos parametrus, kuri ir nepiespieduši atbilstību, aptverot segmentus iekavās.

``` php
Flight::route('/blog(/@year(/@month(/@day)))', function($year, $month, $day){
    // Tas atbilst šādiem URL:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
});
```

Visi nepiespiedu parametri, kas netika atbilstoši saskaņoti, tiks nodoti kā NULL.

### Meža

Atbilstība tiek veikta tikai atsevišķiem URL segmentiem. Ja vēlaties saskaņot vairākus segmentus, varat izmantot `*` mežu.

``` php
Flight::route('/blog/*', function(){
    // Tas atbilst /blog/2000/02/01
});
```

Lai maršrutētu visus pieprasījumus uz vienu atgriezenisko funkciju, varat veikt:

``` php
Flight::route('*', function(){
    // Dariet kaut ko
});
```

### Pārsūtīšana

Jūs varat nodot izpildi nākamajam saskaņotajam maršrutam, atgriežot `true` no jūsu atgriezeniskās funkcijas.

``` php
Flight::route('/user/@name', function($name){
    // Pārbaudiet dažus nosacījumus
    if ($name != "Bob") {
        // Turpiniet uz nākamo maršrutu
        return true;
    }
});

Flight::route('/user/*', function(){
    // Tas tiks izsaukts
});
```

### Maršruta informācija

Ja vēlaties apskatīt saskaņotā maršruta informāciju, varat pieprasīt, lai maršruta objekts tiktu nodots jūsu atgriezeniskajā funkcijā, iekļaujot `true` kā trešo parametru maršruta metodē. Maršruta objekts vienmēr būs pēdējais parametrs, kas nodots jūsu atgriezeniskajai funkcijai.

``` php
Flight::route('/', function($route){
    // HTTP metožu masīvs, pret kurām tika saskaņots
    $route->methods;

    // Nosaukto parametru masīvs
    $route->params;

    // Atbilstošais regulārais izteiciens
    $route->regex;

    // Satur jebkura '*' saturu, kas izmantots URL paraugā
    $route->splat;
}, true);
```
### Maršruta grupēšana

Var gadīties, ka vēlaties grupēt saistītus maršrutus kopā (piemēram, `/api/v1`).
Jūs varat to izdarīt, izmantojot `group` metodi:

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// Atbilst /api/v1/users
  });

  Flight::route('/posts', function () {
	// Atbilst /api/v1/posts
  });
});
```

Jūs pat varat ieviest grupas no grupām:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() iegūst mainīgos, tas nenosaka maršrutu! Skatiet objekta kontekstu zemāk 
	Flight::route('GET /users', function () {
	  // Atbilst GET /api/v1/users
	});

	Flight::post('/posts', function () {
	  // Atbilst POST /api/v1/posts
	});

	Flight::put('/posts/1', function () {
	  // Atbilst PUT /api/v1/posts
	});
  });
  Flight::group('/v2', function () {

	// Flight::get() iegūst mainīgos, tas nenosaka maršrutu! Skatiet objekta kontekstu zemāk
	Flight::route('GET /users', function () {
	  // Atbilst GET /api/v2/users
	});
  });
});
```

#### Grupēšana ar objekta kontekstu

Jūs joprojām varat izmantot maršruta grupēšanu ar `Engine` objektu šādā veidā:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {
  $router->get('/users', function () {
	// Atbilst GET /api/v1/users
  });

  $router->post('/posts', function () {
	// Atbilst POST /api/v1/posts
  });
});
```

### Maršruta aliasēšana

Jūs varat piešķirt aliasu maršrutam, lai URL varētu dinamiski ģenerēt vēlāk jūsu kodā (piemēram, veidnes gadījumā).

```php
Flight::route('/users/@id', function($id) { echo 'lietotājs:'.$id; }, false, 'user_view');

// vēlāk kodā kaut kur
Flight::getUrl('user_view', [ 'id' => 5 ]); // atgriezīs '/users/5'
```

Tas ir īpaši noderīgi, ja jūsu URL gadās mainīties. Iepriekšējā piemērā, pieņemot, ka lietotāji tika pārvietoti uz `/admin/users/@id`.
Ar aliasāciju, jums nav jāmaina neviena vieta, kur atsaucaties uz aliasu, jo alias tagad atgriezīs `/admin/users/5`, kā iepriekšējā piemērā.

Maršruta aliasācija joprojām darbojas grupās:

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'lietotājs:'.$id; }, false, 'user_view');
});

// vēlāk kodā kaut kur
Flight::getUrl('user_view', [ 'id' => 5 ]); // atgriezīs '/users/5'
```

## <a name="extending"></a> Paplašināšana

Flight ir izstrādāts kā paplašināms ietvars. Ietvars nāk ar vairākiem noklusējuma metodēm un komponentiem, taču ļauj jums kartēt savas metodes, reģistrēt savas klases vai pat pārrakstīt esošās klases un metodes.

### Metožu kartēšana

Lai kartētu savu pielāgoto metodi, jūs izmantojat `map` funkciju:

``` php
// Kartējiet savu metodi
Flight::map('hello', function($name){
    echo "sveiks $name!";
});

// Izsauciet savu pielāgoto metodi
Flight::hello('Bob');
```

### Klases reģistrēšana

Lai reģistrētu savu klasi, jūs izmantojat `register` funkciju:

``` php
// Reģistrējiet savu klasi
Flight::register('user', 'User');

// Iegūstiet jūsu klases instanci
$user = Flight::user();
```

Reģistrēšanas metode arī ļauj jums nodot parametrus jūsu klases konstruktoram. Tātad, kad jūs ielādējat savu pielāgoto klasi, tā tiks priekšīstenizēta.
Jūs varat definēt konstruktora parametrus, pārvadājot papildu masīvu.
Šeit ir piemērs par datubāzes savienojuma ielādi:

``` php
// Reģistrējiet klasi ar konstruktora parametriem
Flight::register('db', 'PDO', array('mysql:host=localhost;dbname=test','user','pass'));

// Iegūstiet jūsu klases instanci
// Tas izveidos objektu ar definētajiem parametiem
//
//     new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();
```

Ja jūs pārvadājat papildu atgriezeniskās funkcijas parametru, tas tiks izpildīts nekavējoties pēc klases konstrukcijas. Tas ļauj jums veikt jebkādas sagatavošanas procedūras jūsu jaunajam objektam. Atgriezeniskās funkcijas parametram ir jānāk ar vienu, jaunu objekta instanci.

``` php
// Atgriezeniskā funkcija tiks nodota izveidotajam objektam
Flight::register('db', 'PDO', array('mysql:host=localhost;dbname=test','user','pass'),
  function($db){
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

Noklusējuma gadījumā katru reizi, kad ielādējat savu klasi, jūs iegūsiet kopīgu instanci.
Lai iegūtu jaunu klases instanci, vienkārši pārvadājiet `false` kā parametru:

``` php
// Kopīga klases instanta
$shared = Flight::db();

// Jauna klases instanta
$new = Flight::db(false);
```

Atcerieties, ka kartētās metodes ir prioritātes pār reģistrētajām klasēm. Ja jūs deklarējat abus ar to pašu nosaukumu, tiks izsaukta tikai kartētā metode.

## <a name="overriding"></a> Pārrakstīšana

Flight ļauj jums pārrakstīt tās noklusējuma funkcionalitāti, lai pielāgotu savām vajadzībām, neizmainot nevienu kodu.

Piemēram, kad Flight nevar saskaņot URL ar maršrutu, tas izsauc `notFound` metodi, kas nosūta vispārēju `HTTP 404` atbildi. Jūs varat pārrakstīt šo uzvedību, izmantojot `map` metodi:

``` php
Flight::map('notFound', function(){
    // Parādiet pielāgotu 404 lapu
    include 'errors/404.html';
});
```

Flight arī ļauj jums aizstāt ietvara kodola komponentus.
Piemēram, jūs varat aizstāt noklusējuma maršrutētāja klasi ar savu pielāgoto klasi:

``` php
// Reģistrējiet savu pielāgoto klasi
Flight::register('router', 'MyRouter');

// Kad Flight ielādē maršrutētāja instanci, tas ielādēs jūsu klasi
$myrouter = Flight::router();
```

Tomēr ietvara metodes, piemēram, `map` un `register`, nevar tikt pārrakstītas. Jūs saņemsiet kļūdu, ja mēģināsiet to izdarīt.

## <a name="filtering"></a> Filtrēšana

Flight ļauj jums filtrēt metodes pirms un pēc to izsaukšanas. Nav nepieciešamības iegaumēt iepriekšnoteiktus āķus. Jūs varat filtrēt jebkuru noklusējuma ietvara metodi, kā arī jebkuras pielāgotās metodes, ko jūs esat kartējuši.

Filtrēšanas funkcija izskatās šādi:

``` php
function(&$params, &$output) {
    // Filtrēšanas kods
}
```

Izmantojot nodotos mainīgos, jūs varat manipulēt ar ievades parametriem un/vai izeju.

Jūs varat veikt filtru pirms metodes, darot:

``` php
Flight::before('start', function(&$params, &$output){
    // Dariet kaut ko
});
```

Jūs varat veikt filtru pēc metodes, darot:

``` php
Flight::after('start', function(&$params, &$output){
    // Dariet kaut ko
});
```

Jūs varat pievienot tik daudz filtru, cik vēlaties jebkurai metodei. Tie tiks izsaukti secībā, kādā tie tiek deklarēti.

Šeit ir piemērs par filtrēšanas procesu:

``` php
// Kartējiet pielāgoto metodi
Flight::map('hello', function($name){
    return "Sveiks, $name!";
});

// Pievienojiet pirms filtrētāju
Flight::before('hello', function(&$params, &$output){
    // Manipulējiet parametru
    $params[0] = 'Freids';
});

// Pievienojiet pēc filtrētāju
Flight::after('hello', function(&$params, &$output){
    // Manipulējiet izeju
    $output .= " Lai jums jauka diena!";
});

// Izsauciet pielāgoto metodi
echo Flight::hello('Bob');
```

Tas rādīs:

``` html
Sveiks Freids! Lai jums jauka diena!
```

Ja esat definējis vairākus filtrus, jūs varat pārtraukt ķēdi, atgriežot `false` jebkurā no savām filtrēšanas funkcijām:

``` php
Flight::before('start', function(&$params, &$output){
    echo 'viens';
});

Flight::before('start', function(&$params, &$output){
    echo 'divi';

    // Tas beigs ķēdi
    return false;
});

// Tas netiks izsaukts
Flight::before('start', function(&$params, &$output){
    echo 'trīs';
});
```

Ņemiet vērā, ka kodola metodes, piemēram, `map` un `register`, nevar tikt filtrētas, jo tās tiek izsauktas tieši un nevis dinamiski.

## <a name="variables"></a> Mainīgie

Flight ļauj jums saglabāt mainīgos, lai tos varētu izmantot visur jūsu lietojumprogrammā.

``` php
// Saglabājiet savu mainīgo
Flight::set('id', 123);

// Citur jūsu lietojumprogrammā
$id = Flight::get('id');
```

Lai redzētu, vai mainīgais ir iestatīts, jūs varat darīt:

``` php
if (Flight::has('id')) {
     // Dariet kaut ko
}
```

Jūs varat notīrīt mainīgo šādi:

``` php
// Notīra id mainīgo
Flight::clear('id');

// Notīra visus mainīgos
Flight::clear();
```

Flight arī izmanto mainīgos konfigurācijas nolūkos.

``` php
Flight::set('flight.log_errors', true);
```

## <a name="views"></a> Skati

Flight nodrošina dažas pamata veidlapu funkcionalitātes pēc noklusējuma. Lai parādītu skata veidni, izsauciet `render` metodi ar veidņu faila nosaukumu un izvēles veidņu datiem:

``` php
Flight::render('hello.php', array('name' => 'Bob'));
```

Veidņu dati, kurus jūs nododat, tiek automātiski injicēti veidnē un var tikt atsaukti kā lokālais mainīgais. Veidņu faili ir vienkārši PHP faili. Ja `hello.php` veidņu faila saturs ir:

``` php
Sveiks, '<?php echo $name; ?>'!
```

Izvade būtu:

``` html
Sveiks, Bob!
```

Jūs varat arī manuāli iestatīt skata mainīgos, izmantojot `set` metodi:

``` php
Flight::view()->set('name', 'Bob');
```

Mainīgais `name` tagad ir pieejams visos jūsu skatos. Tātad jūs varat vienkārši darīt:

``` php
Flight::render('hello');
```

Piezīme, ka, norādot veidnes nosaukumu `render` metodē, varat izlaist `.php` paplašinājumu.

Noklusējuma gadījumā Flight meklēs `views` direktoriju veidņu failiem. Jūs varat iestatīt alternatīvo ceļu savām veidnēm, iestatot sekojošo konfigurāciju:

``` php
Flight::set('flight.views.path', '/path/to/views');
```

### Iekārtas

Bieži vien vietnēm ir viena iegarenas veidnes faila ar mainīgajām saturu. Lai attēlotu saturu, ko izmantos iecerē, jūs varat nodot izvēles parametru `render` metodei.

``` php
Flight::render('header', array('heading' => 'Sveiks'), 'header_content');
Flight::render('body', array('body' => 'Pasaule'), 'body_content');
```

Jūsu skatā tad būs saglabātie mainīgie ar nosaukumu `header_content` un `body_content`.
Jūs varat iegūt savu izkārtojumu, darot:

``` php
Flight::render('layout', array('title' => 'Sākumlapas'));
```

Ja veidņu faili izskatās šādi:

`header.php`:

``` php
<h1><?php echo $heading; ?></h1>
```

`body.php`:

``` php
<div><?php echo $body; ?></div>
```

`layout.php`:

``` php
<html>
<head>
<title><?php echo $title; ?></title>
</head>
<body>
<?php echo $header_content; ?>
<?php echo $body_content; ?>
</body>
</html>
```

Izvade būtu:

``` html
<html>
<head>
<title>Sākumlapa</title>
</head>
<body>
<h1>Sveiks</h1>
<div>Pasaule</div>
</body>
</html>
```

### Pielāgotie skati

Flight ļauj jums nomainīt noklusējuma skata dzinēju, vienkārši reģistrējot savu skata klasi. Šeit ir tas, kā jūs izmantotu [Smarty](http://www.smarty.net/) veidņu dzinēju saviem skatiem:

``` php
// Ielādējiet Smarty bibliotēku
require './Smarty/libs/Smarty.class.php';

// Reģistrējiet Smarty kā skata klasi
// Tāpat nododiet atgriezenisko funkciju, lai konfigurētu Smarty ielādes brīdī
Flight::register('view', 'Smarty', array(), function($smarty){
    $smarty->template_dir = './templates/';
    $smarty->compile_dir = './templates_c/';
    $smarty->config_dir = './config/';
    $smarty->cache_dir = './cache/';
});

// Piešķiriet veidņu datus
Flight::view()->assign('name', 'Bob');

// Parādiet veidni
Flight::view()->display('hello.tpl');
```

Lai būtu pilnīgs, jums vajadzētu arī pārrakstīt Flight noklusējuma renderēšanas metodi:

``` php
Flight::map('render', function($template, $data){
    Flight::view()->assign($data);
    Flight::view()->display($template);
});
```

## <a name="errorhandling"></a> Kļūdu apstrāde

### Kļūdas un izņēmumi

Visas kļūdas un izņēmumi tiek noķerti ar Flight un nodoti `error` metodei.
Noklusējuma uzvedība ir nosūtīt vispārēju `HTTP 500 Iekšēja servera kļūda` atbildi ar kādu kļūdu informāciju.

Jūs varat pārrakstīt šo uzvedību savām vajadzībām:

``` php
Flight::map('error', function(Exception $ex){
    // Apstrādājiet kļūdu
    echo $ex->getTraceAsString();
});
```

Noklusējuma gadījumā kļūdas netiek reģistrētas tīmekļa serverī. Jūs varat to iespējot, mainot konfigurāciju:

``` php
Flight::set('flight.log_errors', true);
```

### Neatrašanās

Kad URL nevar atrast, Flight izsauc `notFound` metodi. Noklusējuma uzvedība ir nosūtīt `HTTP 404 Neatrasts` atbildi ar vienkāršu ziņojumu.

Jūs varat pārrakstīt šo uzvedību savām vajadzībām:

``` php
Flight::map('notFound', function(){
    // Apstrādājiet neatrašanos
});
```

## <a name="redirects"></a> Pārsūtīšana

Jūs varat pārsūtīt pašreizējo pieprasījumu, izmantojot `redirect` metodi un nododot jaunu URL:

``` php
Flight::redirect('/new/location');
```

Noklusējuma gadījumā Flight nosūta HTTP 303 statusa kodu. Jūs varat arī iestatīt pielāgotu kodu:

``` php
Flight::redirect('/new/location', 401);
```

## <a name="requests"></a> Pieprasījumi

Flight iepako HTTP pieprasījumu vienā objektā, kuru var piekļūt šādi:

``` php
$request = Flight::request();
```

Pieprasījuma objekts nodrošina šādas īpašības:

``` html
url - Pieprasītais URL
base - URL vecāka apakšdirektorija
method - Pieprasījuma metode (GET, POST, PUT, DELETE)
referrer - Atsauces URL
ip - Klienta IP adrese
ajax - Vai pieprasījums ir AJAX pieprasījums
scheme - Servera protokols (http, https)
user_agent - Pārlūkprogrammas informācija
type - Satura veids
length - Satura garums
query - Vaicājuma virknes parametri
data - Post dati vai JSON dati
cookies - Sīkdatņu dati
files - Augšupielādētie faili
secure - Vai savienojums ir drošs
accept - HTTP pieprasījuma parametri
proxy_ip - Klienta starpniekservera IP adrese
```

Jūs varat piekļūt `query`, `data`, `cookies` un `files` īpašībām kā masīviem vai objektiem.

Tādējādi, lai iegūtu vaicājuma virknes parametru, jūs varat darīt:

``` php
$id = Flight::request()->query['id'];
```

Vai arī jūs varat darīt:

``` php
$id = Flight::request()->query->id;
```

### RAW pieprasījuma ķermenis

Lai iegūtu neapstrādātu HTTP pieprasījuma ķermeni, piemēram, strādājot ar PUT pieprasījumiem, jūs varat darīt:

``` php
$body = Flight::request()->getBody();
```

### JSON ievade

Ja jūs nosūtāt pieprasījumu ar tipu `application/json` un datiem `{"id": 123}`, tie būs pieejami no `data` īpašības:

``` php
$id = Flight::request()->data->id;
```

## <a name="stopping"></a> Apstāšanās

Jūs varat apstāties ietvarā jebkurā brīdī, izsaucot `halt` metodi:

``` php
Flight::halt();
```

Jūs varat arī norādīt izvēles `HTTP` statusa kodu un ziņojumu:

``` php
Flight::halt(200, 'Būsim atpakaļ drīz...');
```

Izsaukšana `halt` atbrīvos visu atbildes saturu līdz tam brīdim. Ja vēlaties apstāties ietvarā un izvadīt pašreizējo atbildi, izmantojiet `stop` metodi:

``` php
Flight::stop();
```

## <a name="httpcaching"></a> HTTP kešatmiņa

Flight nodrošina iebūvētu atbalstu HTTP līmeņa kešatmiņai. Ja kešatmiņas nosacījums ir izpildīts, Flight atgriezīs HTTP `304 Nav mainīts` atbildi. Nākamajā reizē, kad klients pieprasīs to pašu resursu, viņiem tiks piedāvāts izmantot savu lokālo kešatmiņu.

### Pēdējā modifikācija

Jūs varat izmantot `lastModified` metodi un nodot UNIX laika zīmogu, lai iestatītu datumu un laiku, kad lapa tika pēdējo reizi modificēta. Klients turpinās izmantot savu kešatmiņu līdz pēdējā modificētā vērtība tiek mainīta.

``` php
Flight::route('/news', function(){
    Flight::lastModified(1234567890);
    echo 'Šis saturs tiks kešots.';
});
```

### ETag

`ETag` kešatmiņa ir līdzīga `Last-Modified`, izņemot to, ka varat norādīt jebkuru ID, kuru vēlaties, resursam:

``` php
Flight::route('/news', function(){
    Flight::etag('mans-unikālais-id');
    echo 'Šis saturs tiks kešots.';
});
```

Ņemiet vērā, ka izsaucot kādu no `lastModified` vai `etag`, abos gadījumos tiks iestatīta un pārbaudīta kešatmiņas vērtība. Ja kešatmiņas vērtība ir tāda pati starp pieprasījumiem, Flight nekavējoties nosūtīs `HTTP 304` atbildi un apstāsies apstrādē.

## <a name="json"></a> JSON

Flight nodrošina atbalstu JSON un JSONP atbilžu nosūtīšanai. Lai nosūtītu JSON atbildi, jūs nododat dažus datus, lai tiktu JSON kodēti:

``` php
Flight::json(array('id' => 123));
```

JSONP pieprasījumiem jūs varat, izvēles kā parametru nosūtīt nosaukumu, ko izmantojat, lai definētu savu atgriezenisko funkciju:

``` php
Flight::jsonp(array('id' => 123), 'q');
```

Tādējādi, veicot GET pieprasījumu, izmantojot `?q=my_func`, jums vajadzētu saņemt izvadi:

``` json
my_func({"id":123});
```

Ja jūs nenosūtāt atgriezeniskās funkcijas parametra nosaukumu, tas noklusējuma gadījumā būs `jsonp`.

## <a name="configuration"></a> Konfigurācija

Jūs varat pielāgot noteiktas Flight uzvedības, iestatot konfigurācijas vērtības, izmantojot `set` metodi.

``` php
Flight::set('flight.log_errors', true);
```

Tālāk ir sniegts pieejamo konfigurācijas iestatījumu saraksts:

``` html 
flight.base_url - Pārdefinēt pieprasījuma pamat URL. (noklusējums: null)
flight.case_sensitive - Lielo un mazo burtu atšķirība URL. (noklusējums: false)
flight.handle_errors - Atļaut Flight apstrādāt visas kļūdas iekšēji. (noklusējums: true)
flight.log_errors - Reģistrēt kļūdas tīmekļa servera kļūdu reģistrā. (noklusējums: false)
flight.views.path - Katalogs, kurā atrodas skatu veidņu faili. (noklusējums: ./views)
flight.views.extension - Skatu veidņu faila paplašinājums. (noklusējums: .php)
```

## <a name="frameworkmethods"></a> Ietvara metodes

Flight ir izstrādāts, lai būtu viegli lietojams un saprotams. Tālāk ir sniegts pilns metožu kopums ietvam. Tas sastāv no kodola metodēm, kas ir parastas statiskās metodes, un paplašināmām metodēm, kas ir kartētas metodes, kuras var filtrēt vai pārrakstīt.

### Kodola metodes

```php
Flight::map(string $name, callable $callback, bool $pass_route = false) // Izveido pielāgotu ietvara metodi.
Flight::register(string $name, string $class, array $params = [], ?callable $callback = null) // Reģistrē klasi ietvara metodei.
Flight::before(string $name, callable $callback) // Pievieno filtru pirms ietvara metodes.
Flight::after(string $name, callable $callback) // Pievieno filtru pēc ietvara metodes.
Flight::path(string $path) // Pievieno ceļu automātiskajai klasēšanai.
Flight::get(string $key) // Iegūst mainīgo.
Flight::set(string $key, mixed $value) // Iestata mainīgo.
Flight::has(string $key) // Pārbauda, vai mainīgais ir iestatīts.
Flight::clear(array|string $key = []) // Notīra mainīgo.
Flight::init() // Inicializē ietvaru uz noklusējuma iestatījumiem.
Flight::app() // Iegūst lietojumprogrammas objekta instanci
```

### Paplašināmās metodes

```php
Flight::start() // Sāk ietvaru.
Flight::stop() // Apstādina ietvaru un nosūta atbildi.
Flight::halt(int $code = 200, string $message = '') // Apstādiniet ietvaru ar izvēles statusa kodu un ziņojumu.
Flight::route(string $pattern, callable $callback, bool $pass_route = false) // Kartē URL paraugu uz atgriezenisko funkciju.
Flight::group(string $pattern, callable $callback) // Izveido grupēšanu URL, paraugs ir jābūt virknē.
Flight::redirect(string $url, int $code) // Pārsūta uz citu URL.
Flight::render(string $file, array $data, ?string $key = null) // Renderē veidņu failu.
Flight::error(Throwable $error) // Nosūta HTTP 500 atbildi.
Flight::notFound() // Nosūta HTTP 404 atbildi.
Flight::etag(string $id, string $type = 'string') // Veic ETag HTTP kešatmiņu.
Flight::lastModified(int $time) // Veic pēdējās modifikācijas HTTP kešatmiņu.
Flight::json(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Nosūta JSON atbildi.
Flight::jsonp(mixed $data, string $param = 'jsonp', int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Nosūta JSONP atbildi.
```

Visi pielāgotie metodes, ko pievienojuši `map` un `register`, var arī filtrēt.

## <a name="frameworkinstance"></a> Ietvara instants

Vietā, lai palaistu Flight kā globālu statisku klasi, jūs varat izvēles palaist to kā objektu instanci.

``` php
require 'flight/autoload.php';

use flight\Engine;

$app = new Engine();

$app->route('/', function(){
    echo 'sveika pasaule!';
});

$app->start();
```

Tādējādi vietā, lai izsauktu statisko metodi, jūs izsauksiet instces metodi ar to pašu vārdu uz Engine objektu.
