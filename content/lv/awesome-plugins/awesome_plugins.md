# Izcili Spraudņi

Flight ir neaprakstāmi paplašināms. Ir vairāki spraudņi, ko var izmantot, lai pievienotu funkcionalitāti saviem Flight lietojumiem. Daži oficiāli atbalstīti ar Flight komandu, citi ir mikro/lite bibliotēkas, kas palīdzēs jums sākt.

## Kešatmiņa

Kešatmiņa ir lielisks veids, kā paātrināt jūsu lietojumu. Ir vairākas kešatmiņas bibliotēkas, ko var izmantot ar Flight.

- [Wruczek/PHP-File-Cache](/awesome-plugins/php-file-cache) - Viegla, vienkārša un neatkarīga PHP failu kešatmiņas klase

## Sīkdatnes

Sīkdatnes ir lielisks veids, kā saglabāt mazus datu gabaliņus klienta pusē. Tās var tikt izmantotas, lai saglabātu lietotāja preferences, lietojumprogrammas iestatījumus un citus.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie ir PHP bibliotēka, kas nodrošina vienkāršu un efektīvu veidu, kā pārvaldīt sīkdatnes.

## Kļūdu novēršana

Kļūdu novēršana ir būtiska, kad jūs attīstāties savā lokālajā vidē. Ir daži spraudņi, kas var uzlabot jūsu kļūdu novēršanas pieredzi.

- [tracy/tracy](/awesome-plugins/tracy) - Tas ir pilnībā iekļauts kļūdu apstrādātājs, kas var tikt izmantots ar Flight. Tam ir vairāki paneļi, kas var palīdzēt jums novērst savu lietojumu kļūdas. To ir arī ļoti viegli paplašināt un pievienot savus paneļus.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Lietots ar [Tracy](/awesome-plugins/tracy) kļūdu apstrādātāju, šis spraudnis pievieno dažus papildu paneļus, lai palīdzētu ar kļūdu novēršanu speciāli Flight projektu gadījumā.

## Datu bāzes

Datu bāzes ir pamats lielākajai daļai lietojumu. Tā ir veids, kā saglabāt un atgūt datus. Dažas datu bāzes bibliotēkas ir vienkārši apvalki, lai rakstītu vaicājumus, un dažas ir pilntiesīgi ORM.

- [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Oficiālais Flight PDO apvalks, kas ir sastāvdaļa no pamatprogrammas. Tas ir vienkāršs apvalks, kas palīdz vienkāršot vaicājumu rakstīšanas un izpildes procesu. Tas nav ORM.
- [flightphp/active-record](/awesome-plugins/active-record) - Oficiālais Flight ActiveRecord ORM/Mapper. Lieliska maza bibliotēka, lai viegli atgūtu un saglabātu datus jūsu datu bāzē.

## Šifrēšana

Šifrēšana ir būtiska jebkuram lietojumprogrammai, kas saglabā jutīgus datus. Datus šifrēt un dešifrēt nav ļoti grūti, bet pareizi uzglabāt šifrēšanas atslēgu var būt grūti. Visnozīmīgākais ir nekad neuzglabāt savu šifrēšanas atslēgu publiskajā direktorijā vai iekļaut to koda atjaunojumos.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - Šī ir bibliotēka, ko var izmantot, lai šifrētu un dešifrētu datus. Darbībai ir diezgan vienkāršs sākums, lai sāktu šifrēt un dešifrēt datus.

## Sesija

Sesijas nav tiešām noderīgas API, bet, veidojot tīmekļa lietojumprogrammu, sesijas var būt būtiskas, lai uzturētu stāvokli un pieteikšanās informāciju.

- [Ghostff/Session](/awesome-plugins/session) - PHP sesiju pārvaldnieks (neliela aizture, flash, segmts, sesijas šifrēšana). Izmanto PHP open_ssl sesiju datu pēc iespējas šifrēšanai/dešifrēšanai.

## Veidnes

Veidnes ir būtiskas jebkurai tīmekļa lietojumprogrammai ar lietotāja interfeisu. Ir daudz veidņu dzinēju, ko var izmantot ar Flight.

- [flightphp/core View](/learn#views) - Šis ir ļoti pamata veidni dzinējs, kas ir sastāvdaļa no pamatprogrammas. Nav ieteicams lietot, ja jums ir vairāk nekā pāris lapas jūsu projektā.
- [latte/latte](/awesome-plugins/latte) - Latte ir pilnībā iekļauta veidņu dzinēja, kas ir ļoti viegli lietojama un sajūtas tuvāka PHP sintaksei nekā Twig vai Smarty. Tas ir arī ļoti viegli paplašināms un pievienot savus filtrus un funkcijas.

## Ieguldījumi

Vai ir spraudnis, ko vēlaties dalīties? Iesniedziet pull pieprasījumu, lai to pievienotu sarakstam!