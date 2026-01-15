# Uzzināt par Flight

Flight ir ātrs, vienkāršs, paplašināms ietvars PHP. Tas ir diezgan daudzpusīgs un to var izmantot jebkura veida tīmekļa lietojumprogrammas izveidei. 
Tas ir izveidots ar vienkāršību prātā un ir uzrakstīts tā, lai būtu viegli saprast un izmantot.

> **Piezīme:** Jūs redzēsiet piemērus, kas izmanto `Flight::` kā statisku mainīgo un dažus, kas izmanto `$app->` Engine objektu. Abi darbojas savstarpēji aizstājami. `$app` un `$this->app` kontrolierī/vidējā ir ieteicamais pieeja no Flight komandas.

## Kodola Komponenti

### [Maršrutēšana](/learn/routing)

Uzziniet, kā pārvaldīt maršrutus savai tīmekļa lietojumprogrammai. Tas ietver arī maršrutu grupēšanu, maršruta parametrus un vidējo.

### [Vidējais](/learn/middleware)

Uzziniet, kā izmantot vidējo, lai filtrētu pieprasījumus un atbildes savā lietojumprogrammā.

### [Autoloadēšana](/learn/autoloading)

Uzziniet, kā automātiski ielādēt savas klases savā lietojumprogrammā.

### [Pieprasījumi](/learn/requests)

Uzziniet, kā apstrādāt pieprasījumus un atbildes savā lietojumprogrammā.

### [Atbildes](/learn/responses)

Uzziniet, kā nosūtīt atbildes saviem lietotājiem.

### [HTML Veidnes](/learn/templates)

Uzziniet, kā izmantot iebūvēto skata dzinēju, lai renderētu savas HTML veidnes.

### [Drošība](/learn/security)

Uzziniet, kā aizsargāt savu lietojumprogrammu no izplatītiem drošības draudiem.

### [Konfigurācija](/learn/configuration)

Uzziniet, kā konfigurēt ietvaru savai lietojumprogrammai.

### [Notikumu Pārvaldnieks](/learn/events)

Uzziniet, kā izmantot notikumu sistēmu, lai pievienotu pielāgotus notikumus savai lietojumprogrammai.

### [Paplašināšana Flight](/learn/extending)

Uzziniet, kā paplašināt ietvaru, pievienojot savas metodes un klases.

### [Metodes Āķi un Filtrēšana](/learn/filtering)

Uzziniet, kā pievienot notikumu āķus savām metodēm un iekšējām ietvara metodēm.

### [Atkarību Injekcijas Konteiners (DIC)](/learn/dependency-injection-container)

Uzziniet, kā izmantot atkarību injekcijas konteinerus (DIC), lai pārvaldītu savas lietojumprogrammas atkarības.

## Palīglasu Klases

### [Kolekcijas](/learn/collections)

Kolekcijas tiek izmantotas, lai turētu datus un tās būtu pieejamas kā masīvs vai kā objekts vieglākai izmantošanai.

### [JSON Apvalks](/learn/json)

Šim ir dažas vienkāršas funkcijas, lai jūsu JSON kodēšana un dekodēšana būtu konsekventa.

### [SimplePdo](/learn/simple-pdo)

PDO dažreiz var radīt vairāk galvassāpju nekā nepieciešams. SimplePdo ir moderna PDO palīgklašu ar ērtām metodēm, piemēram, `insert()`, `update()`, `delete()` un `transaction()`, lai datubāzes operācijas padarītu daudz vieglākas.

### [PdoWrapper](/learn/pdo-wrapper) (Novecojis)

Sākotnējais PDO apvalks ir novecojis no v3.18.0. Lūdzu, izmantojiet [SimplePdo](/learn/simple-pdo) tā vietā.

### [Augšupielādētā Faila Pārvaldnieks](/learn/uploaded-file)

Vienkārša klase, lai palīdzētu pārvaldīt augšupielādētos failus un pārvietot tos uz pastāvīgu atrašanās vietu.

## Svarīgi Koncepti

### [Kāpēc Ietvars?](/learn/why-frameworks)

Šeit ir īss raksts par to, kāpēc jums vajadzētu izmantot ietvaru. Ir laba ideja saprast ietvara izmantošanas priekšrocības, pirms sākat to izmantot.

Turklāt ir izveidots lielisks mācību ceļvedis no [@lubiana](https://git.php.fail/lubiana). Lai gan tas neiet dziļi Flight specifiski, 
šis ceļvedis palīdzēs jums saprast dažus no galvenajiem konceptiem, kas saistīti ar ietvaru, un kāpēc tie ir izdevīgi izmantot. 
Jūs varat atrast mācību ceļvedi [šeit](https://git.php.fail/lubiana/no-framework-tutorial/src/branch/master/README.md).

### [Flight Salīdzinājumā ar Citiem Ietvariem](/learn/flight-vs-another-framework)

Ja jūs migrējat no cita ietvara, piemēram, Laravel, Slim, Fat-Free vai Symfony uz Flight, šī lapa palīdzēs jums saprast atšķirības starp abiem.

## Citi Temati

### [Vienības Testēšana](/learn/unit-testing)

Sekojiet šim ceļvedim, lai uzzinātu, kā veikt vienības testēšanu savam Flight kodam, lai tas būtu stingrs kā klints.

### [AI & Izstrādātāja Pieredze](/learn/ai)

Uzziniet, kā Flight darbojas ar AI rīkiem un modernām izstrādātāja darba plūsmēm, lai palīdzētu jums kodēt ātrāk un gudrāk.

### [Migrēšana v2 -> v3](/learn/migrating-to-v3)

Atpakaļsaderība lielākoties ir saglabāta, bet ir daži izmaiņas, par kurām jums vajadzētu zināt, migrējot no v2 uz v3.