> _Šis raksts sākotnēji tika publicēts vietnē [Airpair](https://web.archive.org/web/20220204014708/https://www.airpair.com/php/posts/best-practices-for-modern-php-development#5-unit-testing) 2015. gadā. Visi nopelni tiek doti Airpair un Brianam Fentonam, kurš sākotnēji uzrakstīja šo rakstu, lai gan vietne vairs nav pieejama, un raksts pastāv tikai [Wayback Machine](https://web.archive.org/web/20220204014708/https://www.airpair.com/php/posts/best-practices-for-modern-php-development#5-unit-testing). Šis raksts ir pievienots vietnei mācību un izglītības mērķiem PHP kopienai kopumā._

1 Iestatīšana un konfigurācija
-----------------------------

### 1.1 Turiet to aktuālu

Sāksim ar to – pārsteidzoši maz PHP instalāciju praksē ir aktuālas vai tiek uzturētas aktuālas. Vai tas ir dēļ koplietotas hostinga ierobežojumiem, noklusējumiem, kurus neviens nemaina, vai laika/budžeta trūkuma atjaunināšanas testēšanai, PHP binārās faili mēdz tikt atstāti novārtā. Tāpēc viena skaidra labākā prakse, kurai vajadzētu pievērst vairāk uzmanības, ir vienmēr izmantot aktuālu PHP versiju (5.6.x šajā rakstā). Turklāt ir svarīgi plānot regulāras atjaunināšanas gan pašam PHP, gan jebkādiem paplašinājumiem vai piegādātāju bibliotēkām, kuras jūs izmantojat. Atjaunināšanas sniedz jaunas valodas funkcijas, uzlabotu ātrumu, mazāku atmiņas lietojumu un drošības atjauninājumus. Jo biežāk jūs atjauninat, jo mazāk sāpīgs process kļūst.

### 1.2 Iestatiet saprātīgus noklusējumus

PHP veic pienācīgu darbu, iestatot labus noklusējumus ar saviem _php.ini.development_ un _php.ini.production_ failiem, bet mēs varam darīt labāk. Piemēram, tie mums nekur nenorāda datumu/laiku joslu. Tas ir saprātīgi no izplatīšanas viedokļa, bet bez tās PHP izmetīs E_WARNING kļūdu katru reizi, kad izsaucam datuma/laika saistītu funkciju. Lūk, daži ieteikti iestatījumi:

*   date.timezone – izvēlieties no [atbalstīto laika joslu saraksta](http://php.net/manual/en/timezones.php)
*   session.save_path – ja mēs izmantojam failus sesijām un ne kādu citu saglabāšanas pārvaldnieku, iestatiet to kaut kur ārpus _/tmp_. Atstāt to kā _/tmp_ var būt riskanti koplietotā hostinga vidē, jo _/tmp_ parasti ir plašas atļaujas. Pat ar sticky-bit iestatītu, ikviens, kam ir pieeja šīs direktorijas satura sarakstam, var uzzināt visus jūsu aktīvos sesijas ID.
*   session.cookie_secure – acīmredzami, ieslēdziet to, ja jūsu PHP kodu pasniedzat pār HTTPS.
*   session.cookie_httponly – iestatiet to, lai novērstu piekļuvi PHP sesijas sīkdatnēm caur JavaScript
*   Vairāk... izmantojiet rīku kā [iniscan](https://github.com/psecio/iniscan), lai pārbaudītu savu konfigurāciju pret biežām ievainojamībām

### 1.3 Paplašinājumi

Tas arī ir laba ideja deaktivizēt (vai vismaz neaktivizēt) paplašinājumus, kurus jūs neizmantosiet, piemēram, datu bāzes draiverus. Lai redzētu, kas ir aktivizēts, izpildiet `phpinfo()` komandu vai dodieties uz komandrindu un izpildiet šo.

```bash
$ php -i
``` 

Informācija ir tā pati, bet phpinfo() pievieno HTML formatējumu. CLI versija ir vieglāk caurvadāma uz grep, lai atrastu specifisku informāciju. Piem.

```bash
$ php -i | grep error_log
```

Viens ierobežojums šai metodei: iespējams, ka dažādas PHP iestatījumi attiecas uz tīmekļa versiju un CLI versiju.

2 Izmantojiet Composer
----------------------

Tas varētu būt pārsteigums, bet viena no labākajām praksēm mūsdienu PHP rakstīšanai ir rakstīt mazāk no tā. Lai gan ir taisnība, ka viens no labākajiem veidiem, kā kļūt labākam programmēšanā, ir darīt to, ir daudz problēmu, kas jau ir atrisinātas PHP telpā, piemēram, maršrutēšana, pamata ievades validācijas bibliotēkas, vienību konvertēšana, datu bāzes abstrakcijas slāņi utt... Vienkārši apmeklējiet [Packagist](https://www.packagist.org/) un izpētiet. Jūs, visticamāk, atradīsiet, ka ievērojamas daļas no problēmas, kuru mēģināt atrisināt, jau ir uzrakstītas un testētas.

Kaut arī vilinoši ir rakstīt visu kodu pašam (un ar to nav nekā slikta – rakstīt savu ietvaru vai bibliotēku kā mācību pieredzi), jums vajadzētu cīnīties pret šīm "Neizgudrots Šeit" izjūtām un ietaupīt sev daudz laika un galvassāpju. Sekojiet PIE doktrīnai – Lepni Izgudrots Citur. Arī, ja jūs izvēlaties rakstīt savu kaut ko, neatklaidiet to, ja tas nedara kaut ko ievērojami atšķirīgu vai labāku nekā esošie piedāvājumi.

[Composer](https://www.getcomposer.org/) ir pakotņu pārvaldnieks PHP, līdzīgs pip Python, gem Ruby un npm Node. Tas ļauj definēt JSON failu, kas uzskaita jūsu koda atkarības, un tas mēģinās atrisināt šīs prasības, lejupielādējot un instalējot nepieciešamo kodu saišķus.

### 2.1 Composer instalēšana

Mēs pieņemam, ka tas ir lokāls projekts, tāpēc instalēsim Composer instanci tikai šim projektam. Dodieties uz savu projektu direktoriju un izpildiet šo:
```bash
$ curl -sS https://getcomposer.org/installer | php
```

Atcerieties, ka caurvadīt jebkuru lejupielādi tieši uz skripta interpreters (sh, ruby, php utt...) ir drošības risks, tāpēc izlasiet instalēšanas kodu un pārliecinieties, ka esat ar to mierā, pirms izpildāt jebkuru šādu komandu.

Ērtības dēļ (ja jūs dodat priekšroku rakstīt `composer install` nevis `php composer.phar install`), jūs varat izmantot šo komandu, lai instalētu vienu Composer kopiju globāli:

```bash
$ mv composer.phar /usr/local/bin/composer
$ chmod +x composer
```

Jums var būt nepieciešams izpildīt šos ar `sudo`, atkarībā no jūsu failu atļaujām.

### 2.2 Composer izmantošana

Composer ir divas galvenās atkarību kategorijas, kuras tas var pārvaldīt: "require" un "require-dev". Atkarības, kas uzskaitītas kā "require", tiek instalētas visur, bet "require-dev" atkarības tiek instalētas tikai tad, kad tās tiek īpaši pieprasītas. Parasti tās ir rīki, kad kods ir aktīvā attīstībā, piemēram, [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer). Zemāk redzams piemērs, kā instalēt [Guzzle](http://docs.guzzlephp.org/en/latest/), populāru HTTP bibliotēku.

```bash
$ php composer.phar require guzzle/guzzle
```

Lai instalētu rīku tikai attīstības mērķiem, pievienojiet karogu `--dev`:

```bash
$ php composer.phar require --dev 'sebastian/phpcpd'
```

Tas instalē [PHP Copy-Paste Detector](https://github.com/sebastianbergmann/phpcpd), citu koda kvalitātes rīku kā attīstības atkarību.

### 2.3 Install vs update

Kad mēs pirmo reizi izpildām `composer install`, tas instalēs jebkuras bibliotēkas un to atkarības, balstoties uz _composer.json_ failu. Kad tas ir paveikts, composer izveido bloķēšanas failu, paredzami sauktu _composer.lock_. Šis fails satur sarakstu ar atkarībām, kuras composer atrada mums, un to precīzajām versijām ar hashiem. Tad jebkuru nākamo reizi, kad izpildām `composer install`, tas paskatīsies bloķēšanas failā un instalēs tās precīzās versijas.

`composer update` ir mazliet atšķirīga būtne. Tas ignorēs _composer.lock_ failu (ja tāds ir) un mēģinās atrast visjaunākās katras atkarības versijas, kas joprojām apmierina ierobežojumus _composer.json_. Tad tas rakstīs jaunu _composer.lock_ failu, kad tas ir pabeigts.

### 2.4 Autoloadēšana

Gan composer install, gan composer update ģenerēs [autoloader](https://getcomposer.org/doc/04-schema.md#autoload) mums, kas stāsta PHP, kur atrast visas nepieciešamās failus, lai izmantotu bibliotēkas, kuras mēs tikko instalējām. Lai to izmantotu, vienkārši pievienojiet šo rindiņu (parasti uz bootstrap failu, kas tiek izpildīts katru pieprasījumu):
```php
require 'vendor/autoload.php';
```

3 Sekojiet labiem dizaina principiem
-----------------------------------

### 3.1 SOLID

SOLID ir mnemonika, lai atgādinātu mums par piecām galvenajām principiem labā objekt-orientētā programmatūras dizainā.

#### 3.1.1 S - Single Responsibility Principle

Tas norāda, ka klasēm vajadzētu būt tikai vienai atbildībai, vai, citādi sakot, tām vajadzētu būt tikai viens iemesls izmaiņām. Tas labi iekļaujas Unix filozofijā ar daudzām mazām rīkām, kas dara vienu lietu labi. Klases, kas dara tikai vienu lietu, ir daudz vieglāk testēt un atkļūdot, un tās mazāk jūs pārsteigs. Jūs nevēlaties, lai metodes izsaukums uz Validator klasi atjaunina db ierakstus. Lūk, piemērs ar SRP pārkāpumu, kādu jūs bieži redzētu aplikācijā, kas balstīta uz [ActiveRecord pattern](http://en.wikipedia.org/wiki/Active_record_pattern).

```php
class Person extends Model
{
    public $name;
    public $birthDate;
    protected $preferences;
    public function getPreferences() {}
    public function save() {}
}
```

Tā ir diezgan pamata [entity](http://lostechies.com/jimmybogard/2008/05/21/entities-value-objects-aggregates-and-roots/) modelis. Bet viena no šīm lietām šeit nepieder. Entitātes modelim vajadzētu būt tikai uzvedībai, kas saistīta ar entītāti, ko tā pārstāv, tam nevajadzētu būt atbildīgam par pašas saglabāšanu.

```php
class Person extends Model
{
    public $name;
    public $birthDate;
    protected $preferences;
    public function getPreferences() {}
}
class DataStore
{
    public function save(Model $model) {}
}
```

Tas ir labāk. Person modelis atgriežas pie tā, ka dara tikai vienu lietu, un saglabāšanas uzvedība ir pārvietota uz noturības objektu. Ņemiet vērā, ka es tikai type hinted uz Model, ne Person. Mēs atgriezīsimies pie tā, kad nonāksim pie L un D SOLID daļām.

#### 3.1.2 O - Open Closed Principle

Ir lielisks tests šim, kas diezgan labi apkopo, par ko šis princips ir: padomājiet par funkciju, ko ieviest, iespējams, pēdējo, pie kuras strādājāt vai strādājat. Vai jūs varat ieviest šo funkciju esošajā kodsbazē TIKAI pievienojot jaunas klases un nemainot nevienu esošu klasi sistēmā? Jūsu konfigurācija un vadošais kods saņem mazliet atlaides, bet lielākajā daļā sistēmu tas ir pārsteidzoši grūti. Jums ir jāpaļaujas daudz uz polimorfisku nosūtīšanu, un lielākā daļā kodsbazju tas vienkārši nav iestatīts. Ja jūs interesējaties par to, ir labs Google runas video YouTube par [polimorfismu un kodu rakstīšanu bez If](https://www.youtube.com/watch?v=4F72VULWFvc), kas izpēta to tālāk. Kā bonuss, runu vada [Miško Hevery](http://misko.hevery.com/), kuru daudzi var zināt kā [AngularJs](https://angularjs.org/) izveidotāju.

#### 3.1.3 L - Liskov Substitution Principle

Šis princips ir nosaukts [Barbara Liskov](http://en.wikipedia.org/wiki/Barbara_Liskov) vārdā, un tas ir izdrukāts zemāk:

> "Objekti programmā vajadzētu būt aizvietojami ar viņu apakštipu instancēm, nekaitējot programmas pareizībai."

Tas viss izklausās labi, bet tas ir skaidrāk ilustrēts ar piemēru.

```php
abstract class Shape
{
    public function getHeight();
    public function setHeight($height);
    public function getLength();
    public function setLength($length);
}
```

Šis pārstāvēs mūsu pamata četrām pusēm formu. Nekas izsmalcināts šeit.

```php
class Square extends Shape
{
    protected $size;
    public function getHeight() {
        return $this->size;
    }
    public function setHeight($height) {
        $this->size = $height;
    }
    public function getLength() {
        return $this->size;
    }
    public function setLength($length) {
        $this->size = $length;
    }
}
```

Šī ir mūsu pirmā forma, Kvadrāts. Diezgan taisni uz priekšu forma, vai ne? Jūs varat pieņemt, ka ir konstruktors, kur mēs iestatām izmērus, bet no šīs realizācijas jūs redzat, ka garums un augstums vienmēr būs vienādi. Kvadrāti vienkārši ir tādi.

```php
class Rectangle extends Shape
{
    protected $height;
    protected $length;
    public function getHeight() {
        return $this->height;
    }
    public function setHeight($height) {
        $this->height = $height;
    }
    public function getLength() {
        return $this->length;
    }
    public function setLength($length) {
        $this->length = $length;
    }
}
```

Tā ir cita forma. Joprojām ir tās pašas metodes paraksti, tā joprojām ir četrām pusēm forma, bet ko, ja mēs sākam mēģināt izmantot tās citu vietā? Tagad pēkšņi, ja mēs mainām formas augstumu, mēs vairs nevaram pieņemt, ka formas garums sakritīs. Mēs esam pārkāpuši līgumu, ko bijām noslēguši ar lietotāju, kad devām viņam mūsu Kvadrāta formu.

Tas ir tipisks LSP pārkāpuma piemērs, un mums ir vajadzīgs šāds princips, lai vislabāk izmantotu tipa sistēmu. Pat [duck typing](http://en.wikipedia.org/wiki/Duck_typing) nepateiks mums, ja pamata uzvedība ir atšķirīga, un, tā kā mēs to nevaram zināt bez tam lūstot, ir labāk pārliecināties, ka tā nav atšķirīga.

#### 3.1.3 I - Interface Segregation Principle

Šis princips prasa priekšroku daudzām mazām, smalkām saskarnēm pret vienu lielu. Saskarnes vajadzētu balstīties uz uzvedību, nevis "tas ir viena no šīm klasēm". Padomājiet par saskarnēm, kas nāk ar PHP. Traversable, Countable, Serializable, tādas lietas. Tās reklamē spējas, ko objekts piemīt, nevis ko tas manto. Tāpēc turiet savas saskarnes mazas. Jūs nevēlaties, lai saskarnei būtu 30 metodes, 3 ir daudz labāks mērķis.

#### 3.1.4 D - Dependency Inversion Principle

Jūs, iespējams, esat dzirdējuši par to citās vietās, kur runāja par [Dependency Injection](http://en.wikipedia.org/wiki/Dependency_injection), bet Dependency Inversion un Dependency Injection nav gluži viena un tā pati lieta. Dependency inversion patiesībā ir veids, kā teikt, ka jums vajadzētu paļauties uz abstrakcijām savā sistēmā, nevis uz tās detaļām. Ko tas nozīmē jums ikdienā?

> Neizmantojiet mysqli_query() tieši visā savā kodā, izmantojiet kaut ko kā DataStore->query() tā vietā.

Šī principa kodols ir par abstrakcijām. Tas vairāk ir par teikumu "izmantojiet datu bāzes adapteri" nevis paļaujoties uz tiešiem izsaukumiem uz lietām kā mysqli_query. Ja jūs tieši izmantojat mysqli_query pusē no savām klasēm, tad jūs visu saistāt tieši ar savu datu bāzi. Nekas pret MySQL šeit, bet, ja jūs izmantojat mysqli_query, šāda veida zemlaukuma detaļas vajadzētu būt paslēptas tikai vienā vietā, un tad šī funkcionalitāte vajadzētu būt pieejama caur ģenerisku aploksni.

Tagad es zinu, ka tas ir mazliet izmantots piemērs, ja jūs par to domājat, jo reizes, kad jūs pilnībā mainīsiet savu datu bāzes dzinēju pēc produkta ieviešanas, ir ļoti, ļoti zemas. Es to izvēlējos, jo domāju, ka cilvēki būs pazīstami ar ideju no sava koda. Arī, pat ja jums ir datu bāze, ar kuru jūs zināt, ka paliksiet, šī abstraktā aploksnes objekts ļauj jums labot kļūdas, mainīt uzvedību vai ieviest funkcijas, kuras jūs vēlaties, lai jūsu izvēlētajai datu bāzei būtu. Tas arī padara unit testēšanu iespējamo, kur zemlaukuma izsaukumi to nedarītu.

4 Objektu vingrinājumi
----------------------

Šis nav pilns izpēte šiem principiem, bet pirmie divi ir viegli atcerēties, sniedz labu vērtību un var tikt nekavējoties piemēroti gandrīz jebkurai kodsbazei.

### 4.1 Ne vairāk kā viens indentācijas līmenis uz metodi

Tas ir noderīgs veids, kā domāt par metožu sadalīšanu mazākos gabalos, atstājot kodu, kas ir skaidrāks un vairāk pašdokumentējošs. Jo vairāk indentācijas līmeņu jums ir, jo vairāk metode dara un jo vairāk stāvokļa jums jāseko prātā, kamēr jūs ar to strādājat.

Tūlīt es zinu, ka cilvēki iebildīs pret to, bet tas ir tikai vadlīnija/heiistika, ne cieta un ātra likums. Es negaidu, ka kāds izpildīs PHP_CodeSniffer noteikumus tam (lai gan [cilvēki ir](https://github.com/object-calisthenics/phpcs-calisthenics-rules)).

Apskatīsim ātru paraugu, kā tas varētu izskatīties:

```php
public function transformToCsv($data)
{
    $csvLines = array();
    $csvLines[] = implode(',', array_keys($data[0]));
    foreach ($data as $row) {
        if (!$row) {
            continue;
        }
        $csvLines[] = implode(',', $row);
    }
    return $csvLines;
}
```

Kamēr šis nav briesmīgs kods (tas tehniski ir pareizs, testējams utt...), mēs varam darīt daudz vairāk, lai to padarītu skaidru. Kā mēs samazinātu ligzdas līmeņus šeit?

Mēs zinām, ka mums ir jāvienkāršo foreach cikla saturs (vai to noņemt pilnībā), tāpēc sāksim tur.

```php
if (!$row) {
    continue;
}
```

Šī pirmā daļa ir viegla. Tas tikai ignorē tukšas rindas. Mēs varam saīsināt šo procesu, izmantojot iebūtu PHP funkciju pirms pat nokļūšanas ciklā.

```php
$data = array_filter($data);
foreach ($data as $row) {
    $csvLines[] = implode(',', $row);
}
```

Tagad mums ir mūsu viens ligzdas līmenis. Bet, skatoties uz to, viss, ko mēs darām, ir funkcijas piemērošana katram vienumam masīvā. Mums pat nav vajadzīgs foreach cikls, lai to darītu.

```php
$data = array_filter($data);
$csvLines = array_map(function($row) {
    return implode(',', $row);
}, $data);
```

Tagad mums vispār nav ligzdas, un kods, visticamāk, būs ātrāks, jo mēs darām visu ciklu ar iebūtiem C funkcijām nevis PHP. Mums ir jāiesaistās mazliet viltībā, lai nodotu komatu uz `implode`, tāpēc jūs varētu argumentēt, ka apstāšanās pie iepriekšējā soļa ir daudz saprotamāka.

### 4.2 Mēģiniet neizmantot `else`

Tas tiešām attiecas uz divām galvenajām idejām. Pirmā ir vairākas return paziņojumi no metodes. Ja jums ir pietiekami daudz informācijas, lai pieņemtu lēmumu par metodes rezultātu, ejiet uz priekšu un pieņemiet to un return. Otrā ir ideja, kas pazīstama kā [Guard Clauses](http://c2.com/cgi/wiki?GuardClause). Tās ir pamata validācijas pārbaudes, kas kombinētas ar agrīniem return, parasti metodes augšdaļā. Ļaujiet man parādīt, ko es domāju.

```php
public function addThreeInts($first, $second, $third) {
    if (is_int($first)) {
        if (is_int($second)) {
            if (is_int($third)) {
                $sum = $first + $second + $third;
            } else {
                return null;
            }
        } else {
            return null;
        }
    } else {
        return null;
    }
    return $sum;
}
```

Tā ir diezgan taisna uz priekšu atkal, tā pievieno 3 intus kopā un atgriež rezultātu, vai `null`, ja kāds no parametriem nav integers. Ignorējot to, ka mēs varētu apvienot visas šīs pārbaudes vienā rindā ar AND operatoriem, es domāju, ka jūs redzat, cik ligzotais if/else struktūra padara kodu grūtāk sekot. Tagad paskatieties uz šo piemēru.

```php
public function addThreeInts($first, $second, $third) {
    if (!is_int($first)) {
        return null;
    }
    if (!is_int($second)) {
        return null;
    }
    if (!is_int($third)) {
        return null;
    }
    return $first + $second + $third;
}
```

Man šis piemērs ir daudz vieglāk sekot. Šeit mēs izmantojam guard clauses, lai verificētu mūsu sākotnējos pieņēmumus par parametriem, ko mēs pārejam, un nekavējoties izkāpjam no metodes, ja tās neizdodas. Mēs arī vairs neesam ar starpposma mainīgo, lai izsekotu summu caur visu metodi. Šajā gadījumā mēs esam verificējuši, ka mēs jau esam uz laimīgā ceļa, un varam vienkārši darīt to, ko mēs atnācām darīt. Atkal mēs varētu vienkārši darīt visas šīs pārbaudes vienā `if`, bet princips vajadzētu būt skaidrs.

5 Unit testēšana
----------------

Unit testēšana ir prakse rakstīt mazus testus, kas verificē uzvedību jūsu kodā. Tie gandrīz vienmēr tiek rakstīti tajā pašā valodā kā kods (šajā gadījumā PHP) un ir paredzēti, lai būtu pietiekami ātri, lai tos izpildītu jebkurā laikā. Tie ir ārkārtīgi vērtīgi kā rīks, lai uzlabotu jūsu kodu. Papildus acīmredzamajām priekšrocībām, nodrošinot, ka jūsu kods dara to, ko jūs domājat, unit testēšana var sniegt ļoti noderīgu dizaina atsauksmi. Ja koda gabals ir grūti testējams, tas bieži parāda dizaina problēmas. Tie arī dod jums drošības tīklu pret regresijām, un tas ļauj jums refactorēt daudz biežāk un attīstīt savu kodu tīrākam dizainam.

### 5.1 Rīki

Ir vairāki unit testēšanas rīki PHP, bet tālu un prom visizplatītākais ir [PHPUnit](https://phpunit.de/). Jūs varat instalēt to, lejupielādējot [PHAR](http://php.net/manual/en/intro.phar.php) failu [tieši](https://phar.phpunit.de/phpunit.phar), vai instalēt to ar composer. Tā kā mēs izmantojam composer visam pārējam, mēs parādīsim šo metodi. Arī, tā kā PHPUnit, visticamāk, netiks izvietots ražošanā, mēs varam instalēt to kā dev atkarību ar šo komandu:

```bash
composer require --dev phpunit/phpunit
```

### 5.2 Testi ir specifikācija

Vissvarīgākā unit testu loma jūsu kodā ir nodrošināt izpildāmu specifikāciju tam, ko kods ir paredzēts darīt. Pat ja testa kods ir nepareizs vai kodā ir kļūdas, zināšanas par to, ko sistēma _ir paredzēts_ darīt, ir nenovērtējama.

### 5.3 Rakstiet savus testus vispirms

Ja jums ir bijusi iespēja redzēt testu kopu, kas rakstīta pirms koda, un vienu, kas rakstīta pēc koda pabeigšanas, tās ir satriecoši atšķirīgas. "Pēc" testi ir daudz vairāk uztraukti par klases īstenošanas detaļām un labas līnijas seguma nodrošināšanu, savukārt "pirms" testi vairāk ir par vēlamās ārējās uzvedības verificēšanu. Tas tiešām ir tas, par ko mums rūp ar unit testiem, ir pārliecināties, ka klase izrāda pareizo uzvedību. Uz īstenošanu fokusēti testi faktiski padara refactorēšanu grūtāku, jo tie lūzt, ja klases iekšējās daļas mainās, un jūs tikko esat pazaudējis OOP informācijas slēptuves priekšrocības.

### 5.4 Kas padara labu unit testu

Labiem unit testiem ir daudz no šīm īpašībām:

*   Ātrs – vajadzētu darboties milise kundās.
*   Nav tīkla piekļuve – vajadzētu būt spējīgam izslēgt bezvadu/nepievienot un visi testi joprojām izdodas.
*   Ierobežota failu sistēmas piekļuve – tas pievieno ātrumu un elastīgumu, ja izvieto kodu citās vidēs.
*   Nav datu bāzes piekļuve – izvairās no dārgiem iestatīšanas un izjaukšanas aktivitātēm.
*   Testē tikai vienu lietu vienlaicīgi – unit testam vajadzētu būt tikai viens iemesls, kāpēc tas neizdodas.
*   Labi nosaukts – skatiet 5.2 augstāk.
*   Galvenokārt viltoti objekti – vienīgie "īstie" objekti unit testos vajadzētu būt objektam, ko mēs testējam, un vienkāršiem vērtību objektiem. Pārējie vajadzētu būt kādas formas [test double](https://phpunit.de/manual/current/en/test-doubles.html)

Ir iemesli iet pret dažiem no šiem, bet kā vispārēji vadlīnijas tās jums kalpos labi.

### 5.5 Kad testēšana ir sāpīga

> Unit testēšana liek jums izjust sliktā dizaina sāpes uz priekšu – Michael Feathers

Kad jūs rakstāt unit testus, jūs piespiežat sevi faktiski izmantot klasi, lai paveiktu lietas. Ja jūs rakstāt testus beigās, vai vēl sliktāk, vienkārši metat kodu pāri sienai QA vai kam, lai rakstītu testus, jūs nesaņemat atsauksmi par to, kā klase faktiski uzvedas. Ja mēs rakstām testus un klase ir reāla sāpe izmantot, mēs to uzzināsim, kamēr mēs to rakstām, kas ir gandrīz lētākais laiks to labot.

Ja klase ir grūti testējama, tas ir dizaina trūkums. Dažādi trūkumi parādās dažādos veidos, kaut gan. Ja jums ir jādara daudz mocking, jūsu klasei, iespējams, ir pārāk daudz atkarību vai jūsu metodes dara pārāk daudz. Jo vairāk iestatīšanas jums ir jāveic katram testam, jo vairāk iespējams, ka jūsu metodes dara pārāk daudz. Ja jums ir jāraksta patiešām sarežģīti testa scenāriji, lai izmantotu uzvedību, klases metodes, iespējams, dara pārāk daudz. Ja jums ir jāizrok iekšā daudz privātu metožu un stāvokļa, lai testētu lietas, varbūt tur ir cita klase, kas mēģina izkļūt. Unit testēšana ir ļoti laba, lai atklātu "aisberga klases", kur 80% no tā, ko klase dara, ir paslēpts aiz protected vai private koda. Es kādreiz biju liels fans padarīt cik iespējams daudz protected, bet tagad es sapratu, ka es tikai liku savām individuālajām klasēm atbildēt par pārāk daudz, un īstā risinājums bija sadalīt klasi mazākos gabalos.

> **Rakstījis Brian Fenton** - Brian Fenton ir bijis PHP izstrādātājs 8 gadus Vidējos Rietumos un Bay Area, šobrīd Thismoment. Viņš fokusējas uz kodu amatniecību un dizaina principiem. Blogs www.brianfenton.us, Twitter @brianfenton. Kad viņš nav aizņemts būdams tēvs, viņam patīk ēdiens, alus, spēles un mācīšanās.