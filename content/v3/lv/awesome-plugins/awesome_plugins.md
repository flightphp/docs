# Brīnišķīgi Spraudņi

Flight ir neticami paplašināms. Ir daudz spraudņu, kas var tikt izmantoti, lai pievienotu funkcionalitāti jūsu Flight lietojumam. Daži ir oficiāli atbalstīti no Flight komandas, bet citi ir mikro/lite bibliotēkas, kas palīdzēs sākt.

## API Dokumentācija

API dokumentācija ir ļoti svarīga jebkuram API. Tā palīdz izstrādātājiem saprast, kā mijiedarboties ar jūsu API un ko gaidīt pretī. Ir pieejami vairāki rīki, kas palīdzēs jums ģenerēt API dokumentāciju jūsu Flight projektiem.

- [FlightPHP OpenAPI ģenerators](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - Emuāra ieraksts, ko uzrakstījis Daniel Schreiber par to, kā izmantot OpenAPI specifikāciju ar FlightPHP, lai izveidotu savu API, izmantojot API pirmā pieeja.
- [SwaggerUI](https://github.com/zircote/swagger-php) - Swagger UI ir lielisks rīks, lai palīdzētu ģenerēt API dokumentāciju jūsu Flight projektiem. To ir ļoti viegli izmantot un to var pielāgot, lai atbilstu jūsu vajadzībām. Šī ir PHP bibliotēka, kas palīdz jums ģenerēt Swagger dokumentāciju.

## Autentifikācija/Autorizācija

Autentifikācija un autorizācija ir būtiskas jebkurai lietojumprogrammai, kurai nepieciešami kontroles mehānismi, lai noteiktu, kas var piekļūt kādam.

- [flightphp/permissions](/awesome-plugins/permissions) - Oficiālā Flight Permissions bibliotēka. Šī bibliotēka ir vienkāršs veids, kā pievienot lietotāju un lietojumprogrammas līmeņa atļaujas jūsu lietojumprogrammai.

## Kešatmiņa

Kešatmiņa ir lielisks veids, kā paātrināt jūsu lietojumprogrammu. Ir pieejamas vairākas kešatmiņas bibliotēkas, kas var tikt izmantotas kopā ar Flight.

- [flightphp/cache](/awesome-plugins/php-file-cache) - Viegls, vienkāršs un patstāvīgs PHP failu kešatmiņas klase

## CLI

CLI lietojumprogrammas ir lielisks veids, kā mijiedarboties ar jūsu lietojumprogrammu. Jūs varat tās izmantot, lai ģenerētu kontrolierus, parādītu visas maršrutus un vēl vairāk.

- [flightphp/runway](/awesome-plugins/runway) - Runway ir CLI lietojum programma, kas palīdz jums pārvaldīt jūsu Flight lietojumprogrammas.

## Sīkdatnes

Sīkdatnes ir lielisks veids, kā uzglabāt mazas datu daļiņas klienta pusē. Tās var tikt izmantotas, lai uzglabātu lietotāju preferences, lietojumprogrammas iestatījumus un vēl daudz ko citu.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Sīkdatne ir PHP bibliotēka, kas nodrošina vienkāršu un efektīvu veidu, kā pārvaldīt sīkdatnes.

## Atlūzīšana

Atlūzīšana ir ļoti svarīga, kad jūs izstrādājat savā lokālajā vidē. Ir daži spraudņi, kas var uzlabot jūsu atlūzīšanas pieredzi.

- [tracy/tracy](/awesome-plugins/tracy) - Šis ir pilnīgi funkciju bagāts kļūdu apstrādātājs, ko var izmantot kopā ar Flight. Tam ir vairāki paneļi, kas var palīdzēt jums atklāt kļūdas jūsu lietojumprogrammā. Tas ir arī ļoti viegli paplašināms un pievienot savus paneļus.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Izmantojot [Tracy](/awesome-plugins/tracy) kļūdu apstrādātāju, šis spraudnis pievieno dažus papildu paneļus, lai palīdzētu saistībā ar kļūdu noteikšanu tieši Flight projektiem.

## Datu bāzes

Datu bāzes ir pamats lielākajai daļai lietojumprogrammu. Tas ir veids, kā jūs glabājat un izgūstat datus. Dažas datu bāzu bibliotēkas ir vienkārši apvalki vaicājumu rakstīšanai un dažas ir pilnīgas ORM.

- [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Oficiālais Flight PDO Wrapper, kas ir daļa no kodola. Šis ir vienkāršs apvalks, lai vienkāršotu vaicājumu rakstīšanas un izpildes procesu. Tas nav ORM.
- [flightphp/active-record](/awesome-plugins/active-record) - Oficiālais Flight ActiveRecord ORM/Mapper. Lieliska neliela bibliotēka, lai viegli izgūtu un uzglabātu datus jūsu datu bāzē.
- [byjg/php-migration](/awesome-plugins/migrations) - Spraudnis, lai sekotu visām datu bāzu izmaiņām jūsu projektā.

## Šifrēšana

Šifrēšana ir ļoti svarīga jebkurai lietojumprogrammai, kas uzglabā sensitīvus datus. Datu šifrēšana un atšifrēšana nav ļoti sarežģīta, taču pareiza šifrēšanas atslēgas uzglabāšana [var](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [būt](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [grūti](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key). Visbūtiskākais ir nekad neuzglabāt jūsu šifrēšanas atslēgu publiskā direktorijā vai iekļaut to sava koda glabātuvē.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - Šī ir bibliotēka, ko var izmantot datu šifrēšanai un atšifrēšanai. Sākt izmantot to ir salīdzinoši vienkārši, lai sāktu šifrēt un atšifrēt datus.

## Darbu rinda

Darbu rindas ir patiešām noderīgas, lai asinhroni apstrādātu uzdevumus. Tas var būt e-pastu sūtīšana, attēlu apstrāde vai jebkas, kas nav jāveic reāllaikā.

- [n0nag0n/simple-job-queue](/awesome-plugins/simple-job-queue) - Vienkārša Darbu Rinda ir bibliotēka, ko var izmantot, lai asinhroni apstrādātu darbus. To var izmantot ar beanstalkd, MySQL/MariaDB, SQLite un PostgreSQL.

## Sesija

Sesijas nav ļoti noderīgas API, bet, veidojot tīmekļa lietojumprogrammu, sesijas var būt būtiskas, lai uzturētu stāvokli un pieteikšanās informāciju.

- [Ghostff/Session](/awesome-plugins/session) - PHP Sesiju pārvaldnieks (nebloķējošs, mirgojošs, segments, sesijas šifrēšana). Izmanto PHP open_ssl, lai opcionalizētu sesiju datu šifrēšanu/atšifrēšanu.

## Veidne

Veidne ir pamats jebkurai tīmekļa lietojumprogrammai ar lietotāja interfeisu. Ir pieejamas vairākas veidņu dzinēji, kas var tikt izmantoti kopā ar Flight.

- [flightphp/core View](/learn#views) - Šis ir ļoti vienkāršs veidņu dzinējs, kas ir daļa no kodola. To ieteicams neizmantot, ja jums ir vairāk nekā pāris lapas jūsu projektā.
- [latte/latte](/awesome-plugins/latte) - Latte ir pilnīgs veidņu dzinējs, kas ir ļoti viegli lietojams un šķiet tuvāks PHP sintaksei nekā Twig vai Smarty. To ir arī ļoti viegli paplašināt un pievienot savus filtrus un funkcijas.

## Ieguldījums

Vai jums ir spraudnis, ko vēlaties dalīties? Iesniedziet pieprasījumu, lai pievienotu to sarakstam!