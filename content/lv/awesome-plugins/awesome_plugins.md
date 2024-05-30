# Lieliskie Spraudņi

Flight ir neaprakstāmi paplašināms. Ir daudz spraudņu, kas var tikt izmantoti, lai pievienotu funkcionalitāti jūsu Flight lietojumprogrammai. Daži no tiem ir oficiāli atbalstīti ar Flight komandas palīdzību, bet citi ir mikro/lite bibliotēkas, lai jums palīdzētu uzsākt darbu.

## Kešatmiņa

Kešatmiņa ir lielisks veids, kā paātrināt jūsu lietojumprogrammu. Ir daudz kešatmiņas bibliotēku, kas var tikt izmantotas kopā ar Flight.

- [Wruczek/PHP-File-Cache](/awesome-plugins/php-file-cache) - Viegla, vienkārša un autonomā PHP faila kešatmiņas klase

## CLI

CLI lietojumprogrammas ir lielisks veids, kā mijiedarboties ar jūsu lietojumprogrammu. Ar tām varat izveidot kontrolierus, parādīt visus maršrutus un citus darbus.

- [flightphp/runway](/awesome-plugins/runway) - Runway ir CLI lietojumprogramma, kas palīdz jums pārvaldīt jūsu Flight lietojumprogrammas.

## Sīkfaili

Sīkfaili ir lielisks veids, kā saglabāt nelielas datu daļiņas klienta pusē. Tos var izmantot, lai saglabātu lietotāja preferences, lietojumprogrammas iestatījumus un citus darbus.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie ir PHP bibliotēka, kas nodrošina vienkāršu un efektīvu veidu, kā pārvaldīt sīkfailus.

## Atkļūdošana

Atkļūdošana ir būtiska, kad jūs strādājat savā lokālajā vides. Ir daži spraudņi, kas var uzlabot jūsu atkļūdošanas pieredzi.

- [tracy/tracy](/awesome-plugins/tracy) - Tā ir pilnīga iezīmētāju apstrādes klase, kas var tikt izmantota kopā ar Flight. Tā piedāvā daudz panelu, kas var palīdzēt jums atkļūdot jūsu lietojumprogrammu. To ir arī ļoti viegli paplašināt un pievienot savus paneļus.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Lietots ar [Tracy](/awesome-plugins/tracy) iezīmētāja, šis spraudnis pievieno dažus papildu paneļus, lai palīdzētu ar atkļūdošanu speciāli Flight projektos.

## Datubāzes

Datubāzes ir pamats lielākajai daļai lietojumprogrammu. Tā ir veids, kā uzglabāt un iegūt datus. Dažas datubāzu bibliotēkas ir vienkārši aploksnes, lai rakstītu vaicājumus, un dažas ir pilnīga ORMs.

- [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Oficiālais Flight PDO aploksnes, kas ir daļa no kodola. Tas ir vienkāršs aptinējs, kas palīdz vienkāršot vaicājumu rakstīšanas un izpildes procesu. Tas nav ORM.
- [flightphp/active-record](/awesome-plugins/active-record) - Oficiālais Flight ActiveRecord ORM/Mapper. Lieliska maza bibliotēka, lai viegli izgūtu un saglabātu datus jūsu datubāzē.

## Šifrēšana

Šifrēšana ir būtiska jebkurai lietojumprogrammai, kas uzglabā jutīgus datus. Datus šifrēt un dešifrēt nav ļoti grūti, bet pareizi uzglabāt šifrēšanas atslēgu var būt grūti. Visbiežāk svarīgākais ir nekad nelikt jūsu šifrēšanas atslēgu publiskajā direktorijā vai to neiekļaut jūsu kodola krātuvē.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - Tā ir bibliotēka, kuru var izmantot, lai šifrētu un atšifrētu datus. Iedarbināšana un sākšana ir diezgan vienkārša, lai sāktu šifrēt un atšifrēt datus.

## Sesijas

Sesijas nav īsti noderīgas API, bet, lai izveidotu tīmekļa lietojumprogrammu, sesijas var būt būtiskas, lai uzturētu stāvokli un pieteikšanās informāciju.

- [Ghostff/Session](/awesome-plugins/session) - PHP sesiju vadītājs (nieroborējošs, zibatmiņa, segmens, sesijas šifrēšana). Izmanto PHP open_ssl, lai atklātā veidā šifrētu/atkodu sesiju datus.

## Veidne

Veidne ir būtiska jebkurai tīmekļa lietojumprogrammai ar lietotāja saskarni. Ir daudz veidņu dzinēju, kas var tikt izmantoti ar Flight.

- [flightphp/core View](/learn#views) - Tā ir ļoti pamata veidņu dzinējs, kas ir daļa no kodola. Netiek ieteicams izmantot to, ja jūsu projektā ir vairāk nekā pāris lapas.
- [latte/latte](/awesome-plugins/latte) - Latte ir pilna funkciju veidņu dzinējs, kas ir ļoti viegli lietojams un jūtas tuvāks PHP sintaksei nekā Twig vai Smarty. To ir arī ļoti viegli paplašināt un pievienot savus filtrus un funkcijas.

## Piedalīšanās

Vai ir spraudnis, ko vēlaties koplietot? iesniedziet Pull pieprasījumu, lai to pievienotu sarakstam!