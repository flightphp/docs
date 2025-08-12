# Lieliski spraudņi

Flight ir neticami paplašināms. Ir daži spraudņi, ko var izmantot, lai pievienotu funkcionalitāti jūsu Flight aplikācijai. Daži no tiem oficiāli atbalsta Flight komanda, bet citi ir mikro/lite bibliotēkas, lai palīdzētu jums sākt darbu.

## API dokumentācija

API dokumentācija ir ļoti svarīga jebkurai API. Tā palīdz izstrādātājiem saprast, kā mijiedarboties ar jūsu API un ko sagaidīt pretī. Ir pieejami daži rīki, lai palīdzētu ģenerēt API dokumentāciju jūsu Flight projektiem.

- [FlightPHP OpenAPI ģenerators](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - Emu raksts, ko sarakstījis Daniels Šraibers, par to, kā izmantot OpenAPI specifikāciju ar FlightPHP, lai izveidotu jūsu API, izmantojot API-first pieeju.
- [SwaggerUI](https://github.com/zircote/swagger-php) - Swagger UI ir lielisks rīks, lai palīdzētu ģenerēt API dokumentāciju jūsu Flight projektiem. To ir ļoti viegli izmantot un pielāgot savām vajadzībām. Tas ir PHP bibliotēka, kas palīdz ģenerēt Swagger dokumentāciju.

## Lietojumprogrammas veiktspējas uzraudzība (APM)

Lietojumprogrammas veiktspējas uzraudzība (APM) ir ļoti svarīga jebkurai aplikācijai. Tā palīdz saprast, kā jūsu aplikācija darbojas un kur ir aizkavēšanās. Ir pieejami vairāki APM rīki, ko var izmantot ar Flight.
- <span class="badge bg-primary">oficiāls</span> [flightphp/apm](/awesome-plugins/apm) - Flight APM ir vienkārša APM bibliotēka, ko var izmantot, lai uzraudzītu jūsu Flight aplikācijas. To var izmantot, lai uzraudzītu aplikācijas veiktspēju un palīdzētu identificēt aizkavēšanās.

## Autorizācija/Atslēgas

Autorizācija un atslēgas ir ļoti svarīgas jebkurai aplikācijai, kurai nepieciešama kontrole par to, kurš var piekļūt kam.

- <span class="badge bg-primary">oficiāls</span> [flightphp/permissions](/awesome-plugins/permissions) - Oficiālā Flight Permissions bibliotēka. Šī bibliotēka ir vienkāršs veids, kā pievienot lietotāja un aplikācijas līmeņa atslēgas jūsu aplikācijai.

## Kešošana

Kešošana ir lielisks veids, kā paātrināt jūsu aplikāciju. Ir pieejamas vairākas kešošanas bibliotēkas, ko var izmantot ar Flight.

- <span class="badge bg-primary">oficiāls</span> [flightphp/cache](/awesome-plugins/php-file-cache) - Viegls, vienkāršs un neatkarīgs PHP failu kešošanas klase

## CLI

CLI aplikācijas ir lielisks veids, kā mijiedarboties ar jūsu aplikāciju. Jūs varat izmantot tās, lai ģenerētu kontrolierus, parādītu visas maršrutus un vairāk.

- <span class="badge bg-primary">oficiāls</span> [flightphp/runway](/awesome-plugins/runway) - Runway ir CLI aplikācija, kas palīdz pārvaldīt jūsu Flight aplikācijas.

## Sīkdatnes

Sīkdatnes ir lielisks veids, kā glabāt mazus datu fragmentus klientu pusē. Tās var izmantot, lai glabātu lietotāja preferences, aplikācijas iestatījumus un vairāk.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie ir PHP bibliotēka, kas nodrošina vienkāršu un efektīvu veidu, kā pārvaldīt sīkdatnes.

## Kļūdu meklēšana

Kļūdu meklēšana ir ļoti svarīga, kad jūs attīstāt lokālā vidē. Ir daži spraudņi, kas var uzlabot jūsu kļūdu meklēšanas pieredzi.

- [tracy/tracy](/awesome-plugins/tracy) - Tas ir pilnībā aprīkots kļūdu apstrādātājs, ko var izmantot ar Flight. Tam ir vairākas paneļi, kas var palīdzēt kļūdu meklēšanā jūsu aplikācijā. Tas arī ir ļoti viegli paplašināms un pievienojams ar saviem paneļiem.
- <span class="badge bg-primary">oficiāls</span> [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Izmantojot kopā ar [Tracy](/awesome-plugins/tracy) kļūdu apstrādātāju, šis spraudnis pievieno dažus papildu paneļus, lai palīdzētu kļūdu meklēšanā tieši Flight projektos.

## Datubāzes

Datubāzes ir vairumu aplikāciju kodols. Tā ir veids, kā glabāt un izgūt datus. Dažas datubāzu bibliotēkas ir vienkārši aptuvenas, lai rakstītu vaicājumus, bet citas ir pilnībā izstrādātas ORM.

- <span class="badge bg-primary">oficiāls</span> [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Oficiālais Flight PDO Wrapper, kas ir daļa no kodola. Tas ir vienkāršs aptuvenais, lai vienkāršotu vaicājumu rakstīšanu un izpildi. Tas nav ORM.
- <span class="badge bg-primary">oficiāls</span> [flightphp/active-record](/awesome-plugins/active-record) - Oficiālais Flight ActiveRecord ORM/Mapper. Lieliska maza bibliotēka, lai viegli izgūtu un glabātu datus jūsu datubāzē.
- [byjg/php-migration](/awesome-plugins/migrations) - Spraudnis, lai izsekotu visas datubāzes izmaiņas jūsu projektā.

## Šifrēšana

Šifrēšana ir ļoti svarīga jebkurai aplikācijai, kas glabā jutīgus datus. Datiem šifrēt un atšifrēt nav pārāk grūti, bet pareizi glabāt šifrēšanas atslēgu [var](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [būt](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [grūti](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key). Vissvarīgākais ir nekad neglabāt savu šifrēšanas atslēgu publiskā direktorijā vai neiekļaut to jūsu koda repozitorijā.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - Tas ir bibliotēka, ko var izmantot, lai šifrētu un atšifrētu datus. Sākt darbu ir diezgan vienkārši, lai sāktu šifrēt un atšifrēt datus.

## Darba rinda

Darba rindas ir patiešām noderīgas, lai asinhroni apstrādātu uzdevumus. Tas var būt e-pastu sūtīšana, attēlu apstrāde vai jebkas, kam nav jānotiek reāllaikā.

- [n0nag0n/simple-job-queue](/awesome-plugins/simple-job-queue) - Simple Job Queue ir bibliotēka, ko var izmantot, lai apstrādātu darbus asinhroni. To var izmantot ar beanstalkd, MySQL/MariaDB, SQLite un PostgreSQL.

## Sesijas

Sesijas nav īsti noderīgas API, bet, lai izveidotu tīmekļa aplikāciju, sesijas var būt ļoti svarīgas, lai uzturētu stāvokli un pieteikšanās informāciju.

- <span class="badge bg-primary">oficiāls</span> [flightphp/session](/awesome-plugins/session) - Oficiālā Flight Session bibliotēka. Tas ir vienkāršs sesijas bibliotēka, ko var izmantot, lai glabātu un izgūtu sesijas datus. Tas izmanto PHP iebūvētās sesijas apstrādi.
- [Ghostff/Session](/awesome-plugins/ghost-session) - PHP Session Manager (nebloķējoša, flash, segment, sesijas šifrēšana). Izmanto PHP open_ssl, lai pēc izvēles šifrētu/atšifrētu sesijas datus.

## Veidņu izveide

Veidņu izveide ir jebkuras tīmekļa aplikācijas ar UI kodols. Ir pieejamas vairākas veidņu dzinēji, ko var izmantot ar Flight.

- <span class="badge bg-warning">novecojis</span> [flightphp/core View](/learn#views) - Tas ir ļoti pamata veidņu dzinējs, kas ir daļa no kodola. To nav ieteicams izmantot, ja jūsu projektā ir vairāk nekā pāris lapas.
- [latte/latte](/awesome-plugins/latte) - Latte ir pilnībā aprīkots veidņu dzinējs, ko ir ļoti viegli izmantot un tas ir tuvāks PHP sintaksei nekā Twig vai Smarty. Tas arī ir ļoti viegli paplašināms un pievienojams ar saviem filtriem un funkcijām.

## WordPress integrācija

Vēlaties izmantot Flight savā WordPress projektā? Ir ērts spraudnis tam!

- [n0nag0n/wordpress-integration-for-flight-framework](/awesome-plugins/n0nag0n_wordpress) - Šis WordPress spraudnis ļauj palaist Flight tieši blakus WordPress. Tas ir ideāli piemērots, lai pievienotu pielāgotus API, mikroservisus vai pat pilnas aplikācijas jūsu WordPress vietnei, izmantojot Flight framework. Ļoti noderīgi, ja vēlaties abus pasaules labākos aspektus!

## Dalība

Vai jums ir spraudnis, ko vēlaties dalīties? Iesniedziet pull request, lai pievienotu to sarakstam!