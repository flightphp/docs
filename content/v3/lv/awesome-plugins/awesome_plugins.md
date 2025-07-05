# Brīnišķīgi spraudņi

Flight ir neticami paplašināms. Ir daudz spraudņu, ko var izmantot, lai pievienotu funkcionalitāti jūsu Flight lietotnei. Daži no tiem oficiāli atbalsta Flight komanda, bet citi ir mikro/lite bibliotēkas, lai palīdzētu jums sākt darbu.

## API dokumentācija

API dokumentācija ir ļoti svarīga jebkurai API. Tā palīdz izstrādātājiem saprast, kā mijiedarboties ar jūsu API un ko sagaidīt pretī. Ir pieejami daži rīki, lai palīdzētu ģenerēt API dokumentāciju jūsu Flight projektam.

- [FlightPHP OpenAPI ģenerators](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - Emu raksts, ko rakstījis Daniels Šraibers, par to, kā izmantot OpenAPI specifikāciju ar FlightPHP, lai izveidotu jūsu API, izmantojot API pirmo pieeju.
- [SwaggerUI](https://github.com/zircote/swagger-php) - Swagger UI ir lielisks rīks, lai palīdzētu ģenerēt API dokumentāciju jūsu Flight projektam. To ir ļoti viegli izmantot un var pielāgot atbilstoši jūsu vajadzībām. Tas ir PHP bibliotēka, kas palīdz ģenerēt Swagger dokumentāciju.

## Lietojumprogrammas veiktspējas monitorings (APM)

Lietojumprogrammas veiktspējas monitorings (APM) ir ļoti svarīgs jebkurai lietotnei. Tas palīdz saprast, kā jūsu lietotne darbojas un kur ir pudeles kakli. Ir daudz APM rīku, ko var izmantot ar Flight.
- <span class="badge bg-info">beta</span>[flightphp/apm](/awesome-plugins/apm) - Flight APM ir vienkārša APM bibliotēka, ko var izmantot, lai uzraudzītu jūsu Flight lietotnes. To var izmantot, lai uzraudzītu lietotnes veiktspēju un palīdzētu identificēt pudeles kaklus.

## Autentifikācija/Autorizācija

Autentifikācija un autorizācija ir ļoti svarīga jebkurai lietotnei, kurai nepieciešama kontrole par to, kas var piekļūt kam.

- <span class="badge bg-primary">oficiāli</span> [flightphp/permissions](/awesome-plugins/permissions) - Oficiālā Flight Permissions bibliotēka. Šī bibliotēka ir vienkāršs veids, kā pievienot lietotāja un lietotnes līmeņa atļaujas jūsu lietotnei. 

## Kešošana

Kešošana ir lielisks veids, kā paātrināt jūsu lietotni. Ir daudz kešošanas bibliotēku, ko var izmantot ar Flight.

- <span class="badge bg-primary">oficiāli</span> [flightphp/cache](/awesome-plugins/php-file-cache) - Viegls, vienkāršs un patstāvīgs PHP failā kešošanas klase

## CLI

CLI lietotnes ir lielisks veids, kā mijiedarboties ar jūsu lietotni. Jūs varat izmantot tās, lai ģenerētu kontrolierus, parādītu visas maršrutus un daudz ko citu.

- <span class="badge bg-primary">oficiāli</span> [flightphp/runway](/awesome-plugins/runway) - Runway ir CLI lietotne, kas palīdz pārvaldīt jūsu Flight lietotnes.

## Sīkdatnes

Sīkdatnes ir lielisks veids, kā uzglabāt mazus datu gabalus klientu pusē. To var izmantot, lai uzglabātu lietotāja preferences, lietotnes iestatījumus un daudz ko citu.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie ir PHP bibliotēka, kas nodrošina vienkāršu un efektīvu veidu, kā pārvaldīt sīkdatnes.

## Debugošana

Debugošana ir ļoti svarīga, kad jūs attīstāt savā lokālajā vidē. Ir daži spraudņi, kas var uzlabot jūsu debugošanas pieredzi.

- [tracy/tracy](/awesome-plugins/tracy) - Tas ir pilnībā aprīkots kļūdu apstrādātājs, ko var izmantot ar Flight. Tam ir daudz paneļu, kas var palīdzēt debugot jūsu lietotni. Tas arī ir ļoti viegli paplašināt un pievienot savus paneļus.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Izmantojot ar [Tracy](/awesome-plugins/tracy) kļūdu apstrādātāju, šis spraudnis pievieno dažus papildu paneļus, lai palīdzētu ar debugošanu tieši Flight projektos.

## Datubāzes

Datubāzes ir pamats lielākajai daļai lietotņu. Tā ir veids, kā uzglabāt un izgūt datus. Dažas datubāzu bibliotēkas ir vienkārši apvalki, lai rakstītu vaicājumus, bet citas ir pilnvērtīgas ORM.

- <span class="badge bg-primary">oficiāli</span> [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Oficiālais Flight PDO Wrapper, kas ir daļa no kodola. Tas ir vienkāršs apvalks, lai vienkāršotu vaicājumu rakstīšanas un izpildes procesu. Tas nav ORM.
- <span class="badge bg-primary">oficiāli</span> [flightphp/active-record](/awesome-plugins/active-record) - Oficiālais Flight ActiveRecord ORM/Mapper. Lieliska maza bibliotēka, lai viegli izgūtu un uzglabātu datus jūsu datubāzē.
- [byjg/php-migration](/awesome-plugins/migrations) - Spraudnis, lai izsekotu visas datubāzes izmaiņas jūsu projektā.

## Šifrēšana

Šifrēšana ir ļoti svarīga jebkurai lietotnei, kas uzglabā jutīgus datus. Dati šifrēšana un atšifrēšana nav pārāk sarežģīta, bet pareiza šifrēšanas atslēgas uzglabāšana [var](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [būt](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [grūta](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key). Vissvarīgākais ir nekad neuzglabāt savu šifrēšanas atslēgu publiskā direktorijā vai neiekļaut to jūsu koda repozitorijā.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - Tas ir bibliotēka, ko var izmantot, lai šifrētu un atšifrētu datus. Sākt darbu ir diezgan vienkārši, lai sāktu šifrēt un atšifrēt datus.

## Darba rinda

Darba rindas ir patiešām noderīgas, lai asinhroni apstrādātu uzdevumus. Tas var būt e-pastu sūtīšana, attēlu apstrāde vai jebkas, kas nav jāveic reāllaikā.

- [n0nag0n/simple-job-queue](/awesome-plugins/simple-job-queue) - Simple Job Queue ir bibliotēka, ko var izmantot, lai apstrādātu darbus asinhroni. To var izmantot ar beanstalkd, MySQL/MariaDB, SQLite un PostgreSQL.

## Sesija

Sesijas nav īsti noderīgas API, bet, izveidojot tīmekļa lietotni, sesijas var būt ļoti svarīgas, lai uzturētu stāvokli un pieteikšanās informāciju.

- <span class="badge bg-primary">oficiāli</span> [flightphp/session](/awesome-plugins/session) - Oficiālā Flight Session bibliotēka. Tas ir vienkāršs sesijas bibliotēka, ko var izmantot, lai uzglabātu un izgūtu sesijas datus. Tā izmanto PHP iebūvētās sesijas apstrādi.
- [Ghostff/Session](/awesome-plugins/ghost-session) - PHP Session Manager (nebloķējošs, zibspuldze, segments, sesijas šifrēšana). Izmanto PHP open_ssl, lai opcionāli šifrētu/atšifrētu sesijas datus.

## Veidlapas

Veidlapas ir pamats jebkurai tīmekļa lietotnei ar lietotāja interfeisu. Ir daudz veidlapu dzinēju, ko var izmantot ar Flight.

- <span class="badge bg-warning">novecojis</span> [flightphp/core View](/learn#views) - Tas ir ļoti pamata veidlapu dzinējs, kas ir daļa no kodola. To nav ieteicams izmantot, ja jūsu projektā ir vairāk nekā pāris lapas.
- [latte/latte](/awesome-plugins/latte) - Latte ir pilnībā aprīkots veidlapu dzinējs, ko ir ļoti viegli izmantot un tas jūtas tuvāk PHP sintaksei nekā Twig vai Smarty. Tas arī ir ļoti viegli paplašināt un pievienot savus filtrus un funkcijas.

## WordPress integrācija

Vai vēlaties izmantot Flight savā WordPress projektā? Ir ērts spraudnis tam!

- [n0nag0n/wordpress-integration-for-flight-framework](/awesome-plugins/n0nag0n_wordpress) - Šis WordPress spraudnis ļauj jums darbināt Flight blakus WordPress. Tas ir ideāli piemērots, lai pievienotu pielāgotas API, mikroservisus vai pat pilnas lietotnes jūsu WordPress vietnei, izmantojot Flight framework. Ļoti noderīgi, ja vēlaties abu pasaules labāko!

## Dalība

Vai jums ir spraudnis, ko vēlaties dalīties? Iesniedziet pull request, lai pievienotu to sarakstam!