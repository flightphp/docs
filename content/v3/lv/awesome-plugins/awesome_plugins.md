# Lieliski spraudņi

Flight ir neticami paplašināms. Ir vairāki spraudņi, kurus var izmantot, lai pievienotu funkcionalitāti jūsu Flight lietotnei. Daži ir oficiāli atbalstīti no Flight komandas, bet citi ir mikrolibratūras, lai palīdzētu jums sākt.

## API dokumentācija

API dokumentācija ir svarīga jebkuram API. Tā palīdz izstrādātājiem saprast, kā mijiedarboties ar jūsu API un ko sagaidīt pretī. Ir pieejami daži rīki, kas palīdzēs jums izveidot API dokumentāciju jūsu Flight projektiem.

- [FlightPHP OpenAPI ģenerators](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - Daniel Schreiber raksts par to, kā izmantot OpenAPI specifikāciju ar FlightPHP, lai izveidotu savu API, izmantojot API pirmajā pieejā.
- [SwaggerUI](https://github.com/zircote/swagger-php) - Swagger UI ir lielisks rīks, kas palīdz jums izveidot API dokumentāciju jūsu Flight projektiem. To ir ļoti viegli izmantot, un to var pielāgot jūsu vajadzībām. Tas ir PHP bibliotēka, kas palīdz jums ģenerēt Swagger dokumentāciju.

## Autentifikācija/Autorizācija

Autentifikācija un autorizācija ir svarīgas jebkurai lietotnei, kurai ir nepieciešama kontrole, kas var piekļūt kam.

- <span class="badge bg-primary">oficiāli</span> [flightphp/permissions](/awesome-plugins/permissions) - Oficiālā Flight atļauju bibliotēka. Šī bibliotēka ir vienkāršs veids, kā pievienot lietotāja un lietojumprogrammas līmeņa atļaujas jūsu lietotnei.

## Kešatmiņa

Kešatmiņa ir lielisks veids, kā paātrināt jūsu lietotni. Ir pieejami vairāki kešatmiņas bibliotēkas, ko var izmantot ar Flight.

- <span class="badge bg-primary">oficiāli</span> [flightphp/cache](/awesome-plugins/php-file-cache) - Viegls, vienkāršs un patstāvīgs PHP kešatmiņas klase fails

## CLI

CLI lietotnes ir lielisks veids, kā mijiedarboties ar jūsu lietotni. Jūs varat tās izmantot, lai ģenerētu kontrolierus, parādītu visus maršrutus un vēl vairāk.

- <span class="badge bg-primary">oficiāli</span> [flightphp/runway](/awesome-plugins/runway) - Runway ir CLI lietotne, kas palīdz jums pārvaldīt jūsu Flight lietotnes.

## Sīkfaili

Sīkfaili ir lielisks veids, kā glabāt nelielas datu daļiņas klienta pusē. Tos var izmantot, lai glabātu lietotāja preferences, lietojumprogrammas iestatījumus un vēl vairāk.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Sīkfails ir PHP bibliotēka, kas piedāvā vienkāršu un efektīvu veidu, kā pārvaldīt sīkfailus.

## Kļūdu novēršana

Kļūdu novēršana ir svarīga, kad jūs izstrādājat savā vietējā vidē. Ir daži spraudņi, kas var uzlabot jūsu kļūdu novēršanas pieredzi.

- [tracy/tracy](/awesome-plugins/tracy) - Tas ir pilnībā funkcionalizēts kļūdu apstrādātājs, ko var izmantot ar Flight. Tam ir vairāki paneļi, kas var palīdzēt jums novērst kļūdas jūsu lietotnē. To ir arī ļoti viegli paplašināt un pievienot savus paneļus.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Šis spraudnis, kas tiek izmantots kopā ar [Tracy](/awesome-plugins/tracy) kļūdu apstrādātāju, pievieno dažus papildu paneļus, lai palīdzētu ar kļūdu novēršanu īpaši Flight projektiem.

## Datu bāzes

Datu bāzes ir galvenā jebkurai lietotnei. Tā ir veids, kā jūs glabājat un iegūstat datus. Dažas datu bāzu bibliotēkas ir vienkārši apvalki, lai rakstītu vaicājumus, un dažas ir pilnīgas ORM.

- <span class="badge bg-primary">oficiāli</span> [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Oficiālais Flight PDO apvalks, kas ir daļa no kodola. Tas ir vienkāršs apvalks, kas palīdz vienkāršot vaicājumu rakstīšanas un izpildes procesu. Tas nav ORM.
- <span class="badge bg-primary">oficiāli</span> [flightphp/active-record](/awesome-plugins/active-record) - Oficiālā Flight ActiveRecord ORM/Karte. Lieliska maza bibliotēka, lai viegli iegūtu un glabātu datus jūsu datu bāzē.
- [byjg/php-migration](/awesome-plugins/migrations) - Spraudnis, kas ļauj sekot līdzi visām izmaiņām datu bāzē jūsu projektam.

## Šifrēšana

Šifrēšana ir svarīga jebkurai lietotnei, kas glabā sensitīvus datus. Datu šifrēšana un atšifrēšana nav pārāk grūta, taču pareiza šifrēšanas atslēgas glabāšana [var](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [būt](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [grūti](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key). Svarīgākais ir nekad neglabāt savu šifrēšanas atslēgu publiskajā direktorijā vai neieguldīt to savā kodu krātuvē.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - Tā ir bibliotēka, ko var izmantot datu šifrēšanai un atšifrēšanai. Sākt izmantot ir diezgan vienkārši, lai sāktu šifrēt un atšifrēt datus.

## Uzdevumu rinda

Uzdevumu rindas ir ļoti noderīgas, lai asinkroni apstrādātu uzdevumus. Tas var būt e-pastu sūtīšana, attēlu apstrāde vai viss, kas nav jāveic tiešsaistē.

- [n0nag0n/simple-job-queue](/awesome-plugins/simple-job-queue) - Vienkārša uzdevumu rinda ir bibliotēka, ko var izmantot, lai apstrādātu uzdevumus asinkroni. To var izmantot ar beanstalkd, MySQL/MariaDB, SQLite un PostgreSQL.

## Sesija

Sesijas nav īpaši noderīgas API, bet, veidojot tīmekļa lietotni, sesijas var būt būtiskas stāvokļa un pieteikšanās informācijas saglabāšanai.

- <span class="badge bg-primary">oficiāli</span> [flightphp/session](/awesome-plugins/session) - Oficiālā Flight sesiju bibliotēka. Šī ir vienkārša sesiju bibliotēka, ko var izmantot, lai glabātu un iegūtu sesijas datus. Tā izmanto PHP iebūvēto sesiju apstrādi.
- [Ghostff/Session](/awesome-plugins/ghost-session) - PHP sesiju pārvaldnieks (nebloķējošs, flash, segments, sesijas šifrēšana). Izmanto PHP open_ssl, lai opcionalizētu sesijas datu šifrēšanu/atšifrēšanu.

## Veidnes

Veidnes ir pamatā jebkurai tīmekļa lietotnei ar UI. Ir pieejami vairāki veidņu dzinēji, ko var izmantot ar Flight.

- <span class="badge bg-warning">novecojis</span> [flightphp/core View](/learn#views) - Tā ir ļoti pamata veidņu dzinējs, kas ir daļa no kodola. To nav ieteicams izmantot, ja jūsu projektā ir vairāk nekā dažas lapas.
- [latte/latte](/awesome-plugins/latte) - Latte ir pilnībā funkcionāls veidņu dzinējs, ko ir ļoti viegli izmantot un kas atgādina PHP sintaksi vairāk nekā Twig vai Smarty. To ir arī ļoti viegli paplašināt un pievienot savus filtrus un funkcijas.

## Ieguldījumi

Vai jums ir spraudnis, ko vēlētos dalīties? Iesniedziet pieprasījumu par pievienošanu tā sarakstam!