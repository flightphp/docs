# Brīnišķīgi spraudņi

Flight ir neaprakstāmi paplašinājams. Ir daudz spraudņu, kas var tikt izmantoti, lai pievienotu funkcionalitāti jūsu Flight lietotnei. Daži ir oficiāli atbalstīti ar "Flight" komandu, bet citi ir mikro/lite bibliotēkas, lai jums palīdzētu sākt.

## Autentifikācija/Autorizācija

Autentifikācija un autorizācija ir būtiskas jebkurai lietotnei, kas prasa, lai kontroles būtu par to, kas var piekļūt kādam.

- [flightphp/permissions](/awesome-plugins/permissions) - Oficiālā "Flight" atļauju bibliotēka. Šī bibliotēka ir vienkāršs veids, kā pievienot lietotāju un lietotnes līmeņa atļaujas jūsu lietojumprogrammai.

## Kešošana

Kešošana ir lielisks veids, kā paātrināt jūsu lietojumprogrammu. Ir daudz kešošanas bibliotēku, kas var tikt izmantotas ar "Flight".

- [Wruczek/PHP-File-Cache](/awesome-plugins/php-file-cache) - Gaismas, vienkārša un autonomā PHP failu kešošanas klase

## CLI

CLI lietojumprogrammas ir lielisks veids, kā mijiedarboties ar jūsu lietojumprogrammu. Jūs varat izmantot tās, lai ģenerētu vadītājus, attēlotu visus maršrutus un daudz vairāk.

- [flightphp/runway](/awesome-plugins/runway) - "Runway" ir CLI lietojumprogramma, kas palīdz jums pārvaldīt jūsu "Flight" lietojumprogrammas.

## Sīkdatnes

Sīkdatnes ir lielisks veids, kā saglabāt nelielas datu daļiņas klienta pusē. To var izmantot, lai saglabātu lietotāja preferences, lietotnes iestatījumus, un daudz vairāk.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP sīkdatne ir PHP bibliotēka, kas nodrošina vienkāršu un efektīvu veidu, kā pārvaldīt sīkdatnes.

## Atkļūvošana

Atkļūvošana ir būtiska, kad jūs izstrādājat savā lokālajā vidē. Ir daži spraudņi, kas var uzlabot jūsu atkļūvošanas pieredzi.

- [tracy/tracy](/awesome-plugins/tracy) - Tā ir pilnīga ārkārtas vadība, kas var tikt izmantota ar "Flight". Tai ir vairākas paneļu, kas var palīdzēt jums atkļūvot jūsu lietojumprogrammu. Tas ir arī ļoti viegli paplašināms un pievieno savus paneļus.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Lietots ar [Tracy](/awesome-plugins/tracy) kļūdu apstrādātāju, šis spraudnis pievieno vairākus papildu paneļus, lai palīdzētu ar atkļūvošanu īpaši "Flight" projektiem.

## Datubāzes

Datubāzes ir pamats lielākajai daļai lietojumprogrammu. Tā ir veids, kā saglabāt un izgūt datus. Dažas datubāzes bibliotēkas ir vienkārši apvalki, lai rakstītu vaicājumus un dažas ir pilnvērtīgas ORM.

- [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Oficiālais "Flight" PDO apvalks, kas ir sastāvdaļa no pamata. Tas ir vienkāršs apvalks, kas palīdz vienkāršot vaicājumu rakstīšanas un to izpildes procesu. Tas nav ORM.
- [flightphp/active-record](/awesome-plugins/active-record) - Oficiālais "Flight" aktīvais ieraksts ORM/Mapētājs. Lieliska maza bibliotēka, lai viegli iegūtu un saglabātu datus jūsu datubāzē.

## Šifrēšana

Šifrēšana ir būtiska jebkurai lietojumprogrammai, kas saglabā jutīgus datus. Datus šifrēt un atšifrēt nav ļoti grūti, bet pareizi uzglabāt šifrēšanas atslēgu var būt grūti. Visbūtiskākais ir nekad neuzglabāt savu šifrēšanas atslēgu publiskajā katalogā vai to neievadīt savā kodu repozitorijā.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - Šī ir bibliotēka, kas var tikt izmantota, lai šifrētu un atšifrētu datus. Sākt darbu ar to ir diezgan vienkārši, lai sāktu šifrēt un atšifrēt datus.

## Sesija

Sesijas nav īsti noderīgas API, bet, ja veidojat tīmekļa lietojumprogrammu, sesijas var būt būtiskas, lai uzturētu stāvokli un pieteikšanās informāciju.

- [Ghostff/Session](/awesome-plugins/session) - PHP sesiju pārvaldītājs (nenovēršami, ātri, segmenti, sesijas šifrēšana). Izmanto PHP open_ssl, lai iespējotu sesijas datu šifrēšanu/atšifrēšanu.

## Veidne

Veidne ir būtiska jebkurai tīmekļa lietojumprogrammai ar UI. Ir daudz veidņu dzinēju, kas var tikt izmantoti ar "Flight".

- [flightphp/core View](/learn#views) - Tas ir ļoti pamata veidnes dzinējs, kas ir sastāvdaļa no pamata. Nav ieteicams lietot to, ja jums ir vairāk kā pāris lapas jūsu projektā.
- [latte/latte](/awesome-plugins/latte) - "Latte" ir pilnīgi atbalstīts veidnes dzinējs, kas ir ļoti viegli lietojams un jūtas tuvāk PHP sintaksei nekā Twig vai Smarty. Tas ir arī ļoti viegli paplašināms un pievienot savus filtrus un funkcijas.

## Contributing

Vai ir jums spraudni, ko vēlaties kopīgot? Iesniedziet pieprasījumu izvilkt to uz sarakstu!