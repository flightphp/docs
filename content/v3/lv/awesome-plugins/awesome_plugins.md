# Lieliski spraudņi

Flight ir neticami paplašināms. Ir vairāki spraudņi, ko var izmantot, lai pievienotu funkcionalitāti jūsu Flight lietojumprogrammai. Daži no tiem oficiāli atbalsta Flight komanda, bet citi ir mikro/lite bibliotēkas, lai palīdzētu jums sākt.

## API dokumentācija

API dokumentācija ir izšķiroša jebkuram API. Tā palīdz izstrādātājiem saprast, kā mijiedarboties ar jūsu API un ko sagaidīt pretī. Ir pieejami vairāki rīki, lai palīdzētu jums ģenerēt API dokumentāciju jūsu Flight projektiem.

- [FlightPHP OpenAPI Generator](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - Bloga ieraksts, ko uzrakstījis Daniels Šreibers par to, kā izmantot OpenAPI specifikāciju ar FlightPHP, lai izveidotu jūsu API, izmantojot API pirmo pieeju.
- [SwaggerUI](https://github.com/zircote/swagger-php) - Swagger UI ir lielisks rīks, lai palīdzētu ģenerēt API dokumentāciju jūsu Flight projektiem. To ir ļoti viegli izmantot, un to var pielāgot jūsu vajadzībām. Šī ir PHP bibliotēka, lai palīdzētu ģenerēt Swagger dokumentāciju.

## Lietojumprogrammas veiktspējas uzraudzība (APM)

Lietojumprogrammas veiktspējas uzraudzība (APM) ir izšķiroša jebkurai lietojumprogrammai. Tā palīdz saprast, kā jūsu lietojumprogramma darbojas un kur ir pudeles kakli. Ir vairāki APM rīki, ko var izmantot ar Flight.
- <span class="badge bg-primary">oficiāls</span> [flightphp/apm](/awesome-plugins/apm) - Flight APM ir vienkārša APM bibliotēka, ko var izmantot, lai uzraudzītu jūsu Flight lietojumprogrammas. To var izmantot, lai uzraudzītu jūsu lietojumprogrammas veiktspēju un palīdzētu identificēt pudeles kaklus.

## Async

Flight jau ir ātrs ietvars, bet pievienojot tam turbīnas dzinēju, viss kļūst jautrāks (un izaicināmāks)!

- [flightphp/async](/awesome-plugins/async) - Oficiālā Flight Async bibliotēka. Šī bibliotēka ir vienkāršs veids, kā pievienot asinhrono apstrādi jūsu lietojumprogrammai. Tā izmanto Swoole/Openswoole zem pārsega, lai nodrošinātu vienkāršu un efektīvu veidu, kā palaist uzdevumus asinhroni.

## Autorizācija/Atļaujas

Autorizācija un atļaujas ir izšķirošas jebkurai lietojumprogrammai, kas prasa kontroles, lai noteiktu, kas var piekļūt kam.

- <span class="badge bg-primary">oficiāls</span> [flightphp/permissions](/awesome-plugins/permissions) - Oficiālā Flight Permissions bibliotēka. Šī bibliotēka ir vienkāršs veids, kā pievienot lietotāja un lietojumprogrammas līmeņa atļaujas jūsu lietojumprogrammai. 

## Kešošana

Kešošana ir lielisks veids, kā paātrināt jūsu lietojumprogrammu. Ir vairākas kešošanas bibliotēkas, ko var izmantot ar Flight.

- <span class="badge bg-primary">oficiāls</span> [flightphp/cache](/awesome-plugins/php-file-cache) - Viegls, vienkāršs un neatkarīgs PHP failu kešošanas klase

## CLI

CLI lietojumprogrammas ir lielisks veids, kā mijiedarboties ar jūsu lietojumprogrammu. Jūs varat izmantot tās, lai ģenerētu kontrolierus, parādītu visas maršrutus un vairāk.

- <span class="badge bg-primary">oficiāls</span> [flightphp/runway](/awesome-plugins/runway) - Runway ir CLI lietojumprogramma, kas palīdz pārvaldīt jūsu Flight lietojumprogrammas.

## Sīkfaili

Sīkfaili ir lielisks veids, kā uzglabāt mazas datu daļas klienta pusē. Tos var izmantot, lai uzglabātu lietotāja preferences, lietojumprogrammas iestatījumus un vairāk.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie ir PHP bibliotēka, kas nodrošina vienkāršu un efektīvu veidu, kā pārvaldīt sīkfaius.

## Kļūdu labošana

Kļūdu labošana ir izšķiroša, kad jūs attīstāt savā lokālajā vidē. Ir daži spraudņi, kas var uzlabot jūsu kļūdu labošanas pieredzi.

- [tracy/tracy](/awesome-plugins/tracy) - Šis ir pilnvērtīgs kļūdu apstrādātājs, ko var izmantot ar Flight. Tam ir vairāki paneļi, kas var palīdzēt labot kļūdas jūsu lietojumprogrammā. To ir arī ļoti viegli paplašināt un pievienot savus paneļus.
- <span class="badge bg-primary">oficiāls</span> [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Izmantojot ar [Tracy](/awesome-plugins/tracy) kļūdu apstrādātāju, šis spraudnis pievieno dažus papildu paneļus, lai palīdzētu ar kļūdu labošanu tieši Flight projektiem.

## Datubāzes

Datubāzes ir kodols lielākajai daļai lietojumprogrammu. Tādējādi jūs uzglabājat un izgūstat datus. Dažas datubāzu bibliotēkas ir tikai apvalki vaicājumu rakstīšanai, bet dažas ir pilnvērtīgas ORM.

- <span class="badge bg-primary">oficiāls</span> [flightphp/core PdoWrapper](/learn/pdo-wrapper) - Oficiālais Flight PDO apvalks, kas ir daļa no kodola. Šis ir vienkāršs apvalks, lai palīdzētu vienkāršot vaicājumu rakstīšanas un izpildes procesu. Tas nav ORM.
- <span class="badge bg-primary">oficiāls</span> [flightphp/active-record](/awesome-plugins/active-record) - Oficiālā Flight ActiveRecord ORM/Mapper. Lieliska maza bibliotēka vieglai datu izgūšanai un uzglabāšanai jūsu datubāzē.
- [byjg/php-migration](/awesome-plugins/migrations) - Spraudnis, lai sekotu visām datubāzes izmaiņām jūsu projektā.

## Šifrēšana

Šifrēšana ir izšķiroša jebkurai lietojumprogrammai, kas uzglabā sensitīvus datus. Datu šifrēšana un atšifrēšana nav pārāk grūta, bet pareiza šifrēšanas atslēgas uzglabāšana [var](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [būt](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [grūta](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key). Visnozīmīgākais ir nekad neuzglabāt jūsu šifrēšanas atslēgu publiskā direktorijā vai neiekļaut to jūsu koda repozitorijā.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - Šī ir bibliotēka, ko var izmantot, lai šifrētu un atšifrētu datus. Sākšana ir diezgan vienkārša, lai sāktu šifrēt un atšifrēt datus.

## Darbu rinda

Darbu rindas ir patiešām noderīgas, lai asinhroni apstrādātu uzdevumus. Tas var būt e-pasta sūtīšana, attēlu apstrāde vai jebkas, kam nav jāveic reāllaikā.

- [n0nag0n/simple-job-queue](/awesome-plugins/simple-job-queue) - Simple Job Queue ir bibliotēka, ko var izmantot, lai apstrādātu darbus asinhroni. To var izmantot ar beanstalkd, MySQL/MariaDB, SQLite un PostgreSQL.

## Sesija

Sesijas nav īsti noderīgas API, bet veidojot tīmekļa lietojumprogrammu, sesijas var būt izšķirošas stāvokļa un pieteikšanās informācijas uzturēšanai.

- <span class="badge bg-primary">oficiāls</span> [flightphp/session](/awesome-plugins/session) - Oficiālā Flight Session bibliotēka. Šī ir vienkārša sesijas bibliotēka, ko var izmantot, lai uzglabātu un izgūtu sesijas datus. Tā izmanto PHP iebūvēto sesijas apstrādi.
- [Ghostff/Session](/awesome-plugins/ghost-session) - PHP Session Manager (neblokējošs, zibspuldze, segments, sesijas šifrēšana). Izmanto PHP open_ssl opcionālai sesijas datu šifrēšanai/atšifrēšanai.

## Veidnes

Veidnes ir kodols jebkurai tīmekļa lietojumprogrammai ar UI. Ir vairāki veidņu dzinēji, ko var izmantot ar Flight.

- <span class="badge bg-warning">novecojis</span> [flightphp/core View](/learn#views) - Šis ir ļoti pamata veidņu dzinējs, kas ir daļa no kodola. Nav ieteicams izmantot, ja jūsu projektā ir vairāk nekā pāris lapas.
- [latte/latte](/awesome-plugins/latte) - Latte ir pilnvērtīgs veidņu dzinējs, ko ir ļoti viegli izmantot un tas šķiet tuvāks PHP sintaksei nekā Twig vai Smarty. To ir arī ļoti viegli paplašināt un pievienot savus filtrus un funkcijas.
- [knifelemon/comment-template](/awesome-plugins/comment-template) - CommentTemplate ir jaudīgs PHP veidņu dzinējs ar resursu kompilāciju, veidnes mantojumu un mainīgo apstrādi. Funkcijas ietver automātisku CSS/JS minimizāciju, kešošanu, Base64 kodēšanu un opcionālu Flight PHP ietvara integrāciju.

## WordPress integrācija

Vai vēlaties izmantot Flight savā WordPress projektā? Tam ir ērts spraudnis!

- [n0nag0n/wordpress-integration-for-flight-framework](/awesome-plugins/n0nag0n_wordpress) - Šis WordPress spraudnis ļauj palaist Flight tieši blakus WordPress. Tas ir ideāls, lai pievienotu pielāgotus API, mikroservisu vai pat pilnvērtīgas lietojumprogrammas jūsu WordPress vietnē, izmantojot Flight ietvaru. Ļoti noderīgi, ja vēlaties labāko no abām pasaulēm!

## Iesaiste

Vai jums ir spraudnis, ko vēlaties dalīties? Iesniedziet pieprasījumu pēc vilkšanas, lai pievienotu to sarakstam!