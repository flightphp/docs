# Forši spraudņi

Flight ir neticami paplašināms. Ir pieejams vairāk nekā daži spraudņi, kurus var izmantot, lai pievienotu funkcionalitāti jūsu Flight lietojumam. Daži no tiem tiek oficiāli atbalstīti no Flight komandas, bet citi ir mikro/vieglas bibliotēkas, lai palīdzētu jums sākt.

## API Dokumentācija

API dokumentācija ir izšķiroša nozīme jebkuram API. Tā palīdz izstrādātājiem saprast, kā mijiedarboties ar jūsu API un ko gaidīt pretī. Ir pieejami vairāki rīki, lai palīdzētu jums ģenerēt API dokumentāciju jūsu Flight projektiem.

- [FlightPHP OpenAPI ģenerators](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - Bloga ieraksts, ko uzrakstījis Daniels Šraibers par to, kā izmantot OpenAPI specifikāciju ar FlightPHP, lai izveidotu savu API, izmantojot API pirmās pieejas principu.
- [SwaggerUI](https://github.com/zircote/swagger-php) - Swagger UI ir lielisks rīks, lai palīdzētu ģenerēt API dokumentāciju jūsu Flight projektiem. To ir ļoti viegli izmantot un to var pielāgot, lai atbilstu jūsu vajadzībām. Šī ir PHP bibliotēka, lai palīdzētu jums ģenerēt Swagger dokumentāciju.

## Lietojumprogrammu veiktspējas uzraudzība (APM)

Lietojumprogrammu veiktspējas uzraudzība (APM) ir izšķiroša nozīme jebkurai lietojumprogrammai. Tā palīdz jums saprast, kā jūsu lietojumprogramma darbojas un kur ir šaurās vietas. Ir pieejami vairāki APM rīki, kurus var izmantot kopā ar Flight.
- <span class="badge bg-info">beta</span>[flightphp/flight-apm](/awesome-plugins/apm) - Flight APM ir vienkārša APM bibliotēka, ko var izmantot, lai uzraudzītu jūsu Flight lietojumprogrammas. To var izmantot, lai uzraudzītu jūsu lietojumprogrammas veiktspēju un palīdzētu identificēt šaurās vietas.

## Autentifikācija/Autorizācija

Autentifikācija un autorizācija ir izšķiroša nozīme jebkurai lietojumprogrammai, kas prasa noteikt kontroli par to, kas var piekļūt kam.

- <span class="badge bg-primary">oficiāls</span> [flightphp/permissions](/awesome-plugins/permissions) - Oficiālā Flight permissions bibliotēka. Šī bibliotēka ir vienkāršs veids, kā pievienot lietotāja un lietojumprogrammas līmeņa atļaujas jūsu lietojumprogrammai. 

## Kešatmiņa

Kešatmiņa ir lielisks veids, kā paātrināt jūsu lietojumprogrammu. Ir pieejamas vairākas kešatmiņas bibliotēkas, kuras var izmantot kopā ar Flight.

- <span class="badge bg-primary">oficiāls</span> [flightphp/cache](/awesome-plugins/php-file-cache) - Viegls, vienkāršs un patstāvīgs PHP iekšējo kešatmiņu klase

## CLI

CLI lietojumprogrammas ir lielisks veids, kā mijiedarboties ar jūsu lietojumprogrammu. Jūs varat tās izmantot, lai ģenerētu kontrolierus, parādītu visas maršrutus un daudz ko citu.

- <span class="badge bg-primary">oficiāls</span> [flightphp/runway](/awesome-plugins/runway) - Runway ir CLI lietojumprogramma, kas palīdz jums pārvaldīt jūsu Flight lietojumprogrammas.

## Sīkdatnes

Sīkdatnes ir lielisks veids, kā glabāt nelielus datus klienta pusē. Tos var izmantot, lai glabātu lietotāja preferences, lietojumprogrammas iestatījumus un daudz ko citu.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Sīkdatne ir PHP bibliotēka, kas nodrošina vienkāršu un efektīvu veidu, kā pārvaldīt sīkdatnes.

## Kļūdu novēršana

Kļūdu novēršana ir izšķiroša, kad jūs attīstāt savā vietējā vidē. Ir pieejami daži spraudņi, kas var uzlabot jūsu kļūdu novēršanas pieredzi.

- [tracy/tracy](/awesome-plugins/tracy) - Tas ir pilna funkcionalitātes kļūdu apstrādātājs, ko var izmantot kopā ar Flight. Tam ir daudz paneļu, kas var palīdzēt jums novērst kļūdas jūsu lietojumprogrammā. To ir arī ļoti viegli paplašināt un pievienot savus paneļus.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Izmantojot [Tracy](/awesome-plugins/tracy) kļūdu apstrādātāju, šis spraudnis pievieno dažus papildu paneļus, lai palīdzētu ar kļūdu novēršanu tieši Flight projektiem.

## Datu bāzes

Datu bāzes ir pamatā lielākajām lietojumprogrammām. Tā ir vieta, kur jūs glabājat un iegūstat datus. Dažas datu bāzu bibliotēkas ir vienkārši apvalki vaicājumu rakstīšanai, bet dažas ir pilnīgi izstrādātas ORM.

- <span class="badge bg-primary">oficiāls</span> [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Oficiālais Flight PDO Wrapper, kas ir daļa no pamatnes. Tas ir vienkāršs apvalks, lai vienkāršotu vaicājumu rakstīšanas un izpildes procesu. Tas nav ORM.
- <span class="badge bg-primary">oficiāls</span> [flightphp/active-record](/awesome-plugins/active-record) - Oficiālais Flight ActiveRecord ORM/Mapper. Lieliska mazā bibliotēka, lai viegli iegūtu un glabātu datus jūsu datu bāzē.
- [byjg/php-migration](/awesome-plugins/migrations) - Spraudnis, lai sekotu visām datu bāzes izmaiņām jūsu projektam.

## Šifrēšana

Šifrēšana ir izšķiroša jebkurai lietojumprogrammai, kas glabā sensitīvus datus. Datu šifrēšana un atšifrēšana nav grūta, bet pareiza šifrēšanas atslēgas glabāšana [var](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [būt](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [grūti](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key). Visizšķirošākais ir nekad neglabāt jūsu šifrēšanas atslēgu publiskajā direktorijā vai to nodot savā koda krātuvē.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - Šī ir bibliotēka, ko var izmantot datu šifrēšanai un atšifrēšanai. Sākt šifrēšanu un atšifrēšanu ir salīdzinoši vienkārši.

## Darbu rinda

Darbu rindas ir ļoti noderīgas, lai asinkroni apstrādātu uzdevumus. Tas var būt epastu sūtīšana, attēlu apstrāde vai jebkas, kas nav jāveic reāllaikā.

- [n0nag0n/simple-job-queue](/awesome-plugins/simple-job-queue) - Simple Job Queue ir bibliotēka, ko var izmantot, lai asinkroni apstrādātu darbus. To var izmantot kopā ar beanstalkd, MySQL/MariaDB, SQLite un PostgreSQL.

## Sesija

Sesijas nav īsti noderīgas API, bet, veidojot tīmekļa lietojumprogrammu, sesijas var būt izšķirošas stāvokļa un pieteikšanās informācijas uzturēšanā.

- <span class="badge bg-primary">oficiāls</span> [flightphp/session](/awesome-plugins/session) - Oficiālā Flight sesiju bibliotēka. Šī ir vienkārša sesiju bibliotēka, ko var izmantot, lai glabātu un iegūtu sesiju datus. Tā izmanto PHP iebūvēto sesiju apstrādi.
- [Ghostff/Session](/awesome-plugins/ghost-session) - PHP sesiju pārvaldnieks (nebloķējošs, mirkļi, segments, sesijas šifrēšana). Izmanto PHP open_ssl, lai optional šifrētu/atšifrētu sesijas datus.

## Šablonēšana

Šablonēšana ir pamatā jebkurai tīmekļa lietojumprogrammai ar UI. Ir pieejami vairāki šablonēšanas dzinēji, kurus var izmantot kopā ar Flight.

- <span class="badge bg-warning">atkalizmantots</span> [flightphp/core View](/learn#views) - Tas ir ļoti vienkāršs šablonēšanas dzinējs, kas ir daļa no pamatnes. To nav ieteicams izmantot, ja jūsu projektā ir vairāk nekā dažas lapas.
- [latte/latte](/awesome-plugins/latte) - Latte ir pilnīga funkcionalitātes šablonēšanas dzinēja, ko ir ļoti viegli izmantot un kas jūtas tuvāk PHP sintaksei nekā Twig vai Smarty. To ir arī ļoti viegli paplašināt un pievienot savus filtrus un funkcijas.

## Piedalīšanās

Vai jums ir spraudnis, ko vēlaties dalīties? Iesniedziet vilkšanas pieprasījumu, lai to pievienotu sarakstam!