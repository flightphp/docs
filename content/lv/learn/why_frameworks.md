# Kāpēc ietvars?

Daudzi programmētāji kategoriski iebilst pret ietvariem. Viņi argumentē, ka ietvari ir pūderīgi, lēni un grūti saprotami. Viņi saka, ka ietvari nav nepieciešami un ka jūs varat rakstīt labāku kodu bez tiem. Noteikti var izvirzīt dažus pamatotus iebildumus par ietvaru izmantošanas trūkumiem. Tomēr ir arī daudzas priekšrocības, izmantojot ietvarus.

## Iemesli Izmantot Ietvaru

Šeit ir daži iemesli, kāpēc varētu ņemt vērā ietvaru izmantošanu:

- **Ātra attīstība**: Ietvari nodrošina daudz funkcionalitātes uzreiz. Tas nozīmē, ka varat ātrāk izveidot tīmekļa lietojumprogrammas. Jums nav jāraksta tik daudz koda, jo ietvars nodrošina daudz funkcionalitātes, kas jums nepieciešamas.
- **Vienmērība**: Ietvari nodrošina vienotu veidu, kā darīt lietas. Tas atvieglo jums saprast, kā darbojas kods, un padara to vieglāku citiem izstrādātājiem saprast jūsu kodu. Ja to raksta pēc scenārija, jūs varat zaudēt vienmērību starp scenārijiem, it īpaši, ja strādājat ar izstrādātāju komandu.
- **Drošība**: Ietvari nodrošina drošības funkcijas, kas palīdz aizsargāt jūsu tīmekļa lietojumprogrammas no biežām drošības draudēm. Tas nozīmē, ka jums nav jāuztraucas tik daudz par drošību, jo ietvars par to lielu daļu jau rūpējas.
- **Kopiena**: Ietvariem ir lielas izstrādātāju kopienas, kas dod ieguldījumu ietvarā. Tas nozīmē, ka jūs varat saņemt palīdzību no citiem izstrādātājiem, kad ir jautājumi vai problēmas. Tas arī nozīmē, ka ir pieejami daudzi resursi, lai palīdzētu jums iemācīties, kā lietot ietvaru.
- **Labākās Prakses**: Ietvari tiek izstrādāti, izmantojot labākās prakses. Tas nozīmē, ka jūs varat mācīties no ietvara un izmantot tās pašas labākās prakses savā kodā. Tas var palīdzēt jums kļūt par labāku programmētāju. Dažreiz jums nav zināms, ko jūs nezināt, un tas var jums būt kaitīgi beigās.
- **Paplašināmība**: Ietvari ir izstrādāti, lai tiktu paplašināti. Tas nozīmē, ka varat pievienot savu funkcionalitāti ietvaram. Tas ļauj jums izveidot tīmekļa lietojumprogrammas, kas pielāgotas jūsu konkrētajām vajadzībām.

<<<<<<< Updated upstream
Flight ir mikroietvars. Tas nozīmē, ka tas ir mazs un viegls. Tas nenodrošina tik daudz funkcionalitātes kā lielāki ietvari, piemēram, Laravel vai Symfony. Tomēr tas nodrošina daudz no funkcionalitātes, kas jums nepieciešama, lai izveidotu tīmekļa lietojumprogrammas. Tas ir arī viegli mācīties un lietot. Tas padara to par labu izvēli, lai ātri un viegli izveidotu tīmekļa lietojumprogrammas. Ja jums ir jauni ietvaru, Flight ir lielisks sākuma ietvars iesācējam. Tas palīdzēs jums uzzināt par ietvaru izmantošanas priekšrocībām, neapburdot jūs ar pārāk lielu sarežģītību. Kad jums ir kāda pieredze ar Flight, būs vieglāk pāriet uz sarežģītākiem ietvariem, piemēram, Laravel vai Symfony, tomēr Flight joprojām var veiksmīgi izveidot izturīgu pieteikumu.

=======
Flight ir mikroietvars. Tas nozīmē, ka tas ir neliels un viegls. Tas nenodrošina tik daudz funkcionalitātes kā lielāki ietvari, piemēram, Laravel vai Symfony. Tomēr tas nodrošina daudz no funkcionalitātes, kas jums nepieciešama, lai izveidotu tīmekļa lietojumprogrammas. Tas ir arī viegli mācīties un lietot. Tas padara to par labu izvēli, lai ātri un viegli izveidotu tīmekļa lietojumprogrammas. Ja jums ir jauni ietvaru, Flight ir lielisks sākuma ietvars iesācējam. Tas palīdzēs jums uzzināt par ietvaru izmantošanas priekšrocībām, neapburdot jūs ar pārāk lielu sarežģītību. Kad jums ir kāda pieredze ar Flight, būs vieglāk pāriet uz sarežģītākiem ietvariem, piemēram, Laravel vai Symfony, tomēr Flight joprojām var veiksmīgi izveidot izturīgu pieteikumu.
>>>>>>> Stashed changes

## Kas ir Maršrutēšana?

Maršrutēšana ir Flight ietvara pamats, bet kas tieši tas ir? Maršrutēšana ir procesa, kurā tiek ņemta URL un tiek atrasta atbilstoša funkcija jūsu kodā. Tie ir veidi, kā panākt to, lai jūsu tīmekļa vietne darītu dažādas lietas, atkarībā no pieprasītās URL. Piemēram, jūs varētu vēlēties rādīt lietotāja profilu, kad viņi apmeklē `/lietotājs/1234`, bet rādīt visu lietotāju sarakstu, kad tie apmeklē `/lietotāji`. Tas viss tiek darīts, pateicoties maršrutēšanai.

Tam varētu izskatīties kaut kas šāds:

- Lietotājs dodas uz jūsu pārlūka un ievada `http://piemērs.com/lietotājs/1234`.
- Serveris saņem pieprasījumu un pārbauda URL, pārsūtīdams to jūsu Flight lietojumprogrammas kodam.
- Iedomāsimies, ka jūsu Flight kodā ir kaut kas līdzīgs `Flight::route('/lietotājs/@id', [ 'LietotājaKontrolieris', 'skatsLietotājaProfils' ]);`. Jūsu Flight lietojumprogrammas kods pārbauda URL un redz, ka tā atbilst definētajai maršrutai, un tad izpilda kodu, ko esat definējis šim maršrutam.
- Flight maršruta vadītājs tad izpildīs un izsauks `skatsLietotājaProfils($id)` metodi `LietotājaKontrolieris` klasē, padodot `1234` kā `$id` argumentu metodei.
- Kods jūsu `skatsLietotājaProfils()` metodē tad izpildīs un darīs to, ko jūs tam norādīsiet. Varat beigt ar teksta HTML izvadi lietotāja profilu lapai vai, ja tas ir RESTful API, varat izvadīt JSON atbildi ar lietotāja informāciju.
- Flight ietina šo skaisti, ģenerē atbildes galvenes un nosūta to atpakaļ lietotāja pārlūkam.
- Lietotājs ir piepildīts ar prieku un sev dod siltu apskāvi!

### Un Kāpēc Tas ir Svarīgi?

Proper centralizētam maršrutētājam faktiski var dramatiski atvieglot jūsu dzīvi! Tas var būt grūti saprast no paša sākuma. Šeit ir daži iemesli, kāpēc:

- **Centralizēta Maršrutēšana**: Jūs varat turēt visus savus maršrutus vienuviet. Tas padara to vieglāk redzēt, kādus maršrutus jums ir un ko tie dara. Tas arī atvieglo tos mainīt, ja nepieciešams.
- **Maršruta Parametri**: Jūs varat izmantot maršruta parametrus, lai nodotu datus savām maršruta metodēm. Tas ir lielisks veids, kā uzturēt jūsu kodu tīru un kārtīgu.
- **Maršrutu Grupas**: Jūs varat grupēt maršrutus kopā. Tas ir lielisks veids, kā uzturēt jūsu kodu kārtībā un piemērot [starpposma programmatūru](middleware) grupai maršrutu.
- **Maršrutu Aliasēšana**: Jūs varat piešķirt aliasu maršrutam, lai URL varētu dinamiski tikt ģenerēti vēlāk jūsu kodā (piemēram, veidnē). Piemērs: tā vietā, lai cietkodētu `/lietotājs/1234` savā kodā, jūs varētu norādīt aliasu `lietotājaSkats` un padot `id` kā parametru. Tas ir lielisks gadījumā, ja izlemjat to vēlāk mainīt uz `/administrators/lietotājs/1234`. Nav jāmaina visas jūsu cietkodētas URL, tikai URL, kas piesaistīts maršrutam.
- **Maršruta Starpposma Programmatūra**: Jūs varat pievienot starpposmu programmatūru savos maršrutos. Starpposma programma ir ļoti spēcīga, pievienojot konkrētas uzvedības jūsu lietojumprogrammai, piemēram, autentifikāciju, ka konkrēts lietotājs var piekļūt maršrutam vai maršrutu grupai.

Noteikti jums ir pazīstams veids, kā izveidot tīmekļa vietni skriptu pa skriptam. Jums varētu būt fails ar nosaukumu `index.php`, kurā ir daudz `if` izteikumu, lai pārbaudītu URL un pēc tam izpildītu konkrētu funkciju, atkarībā no URL. Tas ir veids, kā maršrutēšana, bet tas nav pārāk organizēts un tas ātri var ārprātīgi. Flight maršruta sistēma ir daudz labāk organizēta un spēcīgāka veida, kā apstrādāt maršrutēšanu.

Tas?

```php

// /lietotājs/skats_profils.php?id=1234
if ($_GET['id']) {
	$id = $_GET['id'];
	skatsLietotājaProfils($id);
}

// /lietotājs/labot_profils.php?id=1234
if ($_GET['id']) {
	$id = $_GET['id'];
	rediģētLietotājaProfils($id);
}

// utt...
```

Vai tas?

```php

// index.php
Flight::route('/lietotājs/@id', [ 'LietotājaKontrolieris', 'skatsLietotājaProfils' ]);
Flight::route('/lietotājs/@id/labot', [ 'LietotājaKontrolieris', 'rediģētLietotājaProfils' ]);

// Visticamāk jūsu app/controllers/UserController.php
class UserController {
	public function skatsLietotājaProfils($id) {
		// dariet kaut ko
	}

	public function rediģētLietotājaProfils($id) {
		// dariet kaut ko
	}
}
```

Jūsu varat sākt saprast ienesīguma būtību, izmantojot centralizētu maršrutēšanas sistēmu. Tā ir daudz vieglāk pārvaldāma un saprotamā ilgtermiņā!

## Pieprasījumi un Atbildes

Flight nodrošina vienkāršu un vieglu veidu, kā apstrādāt pieprasījumus un atbildes. Tas ir tīmekļa ietvara būtība. Tas paņem pieprasījumu no lietotāja pārlūkprogrammas, apstrādā to, un pēc tam atsūta atbildi. Tas ir veids, kā var izveidot tīmekļa lietojumprogrammas, kas var darīt lietas, piemēram, rādīt lietotāja profilu, ļaut lietotājam pierakstīties vai publicēt jaunu bloga ierakstu.

### Pieprasījumi

Pieprasījums ir tas, ko lietotāja pārlūkprogramma nosūta uz jūsu serveri, kad viņi apmeklē jūsu tīmekļa vietni. Šis pieprasījums satur informāciju par to, ko lietotājs vēlas darīt. Piemēram, tas var saturēt informāciju par to, uz kuru URL lietotājs vēlas apmeklēt, kādus datus lietotājs vēlas nosūtīt uz jūsu serveri vai kāda veida datus lietotājs vēlas saņemt no jūsu servera. Svarīgi zināt, ka pieprasījums ir tikai lasīšana. Jūs nevarat mainīt pieprasījumu, bet to varat lasīt.

Flight nodrošina vienkāršu veidu, kā piekļūt informācijai par pieprasījumu. Jūs varat piekļūt pieprasījuma informācijai, izmantojot `Flight::request()` metodi. Šī metode atgriež `Request` objektu, kas satur informāciju par pieprasījumu. Jūs varat izmantot šo objektu, lai piekļūtu informācijai par pieprasījumu, piemēram, URL, metodi vai datiem, ko lietotājs nosūtījis uz jūsu serveri.

### Atbildes

Atbilde ir tas, ko jūsu serveris atsūta atpakaļ uz lietotāja pārlūkprogrammu, kad viņi apmeklē jūsu tīmekļa vietni. Šī atbilde satur informāciju par to, ko jūsu serveris vēlas darīt. Piemēram, tajā var b# Kāpēc ietvars?

Daudzi programmētāji kategoriski iebilst pret ietvariem. Viņi argumentē, ka ietvari ir pūderīgi, lēni un grūti saprotami. Viņi saka, ka ietvari nav nepieciešami un ka jūs varat rakstīt labāku kodu bez tiem. Noteikti var izvirzīt dažus pamatotus iebildumus par ietvaru izmantošanas trūkumiem. Tomēr ir arī daudzas priekšrocības, izmantojot ietvarus.

## Iemesli Izmantot Ietvaru

Šeit ir daži iemesli, kāpēc varētu ņemt vērā ietvaru izmantošanu:

- **Ātra attīstība**: Ietvari nodrošina daudz funkcionalitātes uzreiz. Tas nozīmē, ka varat ātrāk izveidot tīmekļa lietojumprogrammas. Jums nav jāraksta tik daudz koda, jo ietvars nodrošina daudz funkcionalitātes, kas jums nepieciešamas.
- **Vienmērība**: Ietvari nodrošina vienotu veidu, kā darīt lietas. Tas atvieglo jums saprast, kā darbojas kods, un padara to vieglāku citiem izstrādātājiem saprast jūsu kodu. Ja to raksta pēc scenārija, jūs varat zaudēt vienmērību starp scenārijiem, it īpaši, ja strādājat ar izstrādātāju komandu.
- **Drošība**: Ietvari nodrošina drošības funkcijas, kas palīdz aizsargāt jūsu tīmekļa lietojumprogrammas no biežām drošības draudēm. Tas nozīmē, ka jums nav jāuztraucas tik daudz par drošību, jo ietvars par to lielu daļu jau rūpējas.
- **Kopiena**: Ietvariem ir lielas izstrādātāju kopienas, kas dod ieguldījumu ietvarā. Tas nozīmē, ka jūs varat saņemt palīdzību no citiem izstrādātājiem, kad ir jautājumi vai problēmas. Tas arī nozīmē, ka ir pieejami daudzi resursi, lai palīdzētu jums iemācīties, kā lietot ietvaru.
- **Labākās Prakses**: Ietvari tiek izstrādāti, izmantojot labākās prakses. Tas nozīmē, ka jūs varat mācīties no ietvara un izmantot tās pašas labākās prakses savā kodā. Tas var palīdzēt jums kļūt par labāku programmētāju. Dažreiz jums nav zināms, ko jūs nezināt, un tas var jums būt kaitīgi beigās.
- **Paplašināmība**: Ietvari ir izstrādāti, lai tiktu paplašināti. Tas nozīmē, ka varat pievienot savu funkcionalitāti ietvaram. Tas ļauj jums izveidot tīmekļa lietojumprogrammas, kas pielāgotas jūsu konkrētajām vajadzībām.

Flight ir mikroietvars. Tas nozīmē, ka tas ir neliels un viegls. Tas nenodrošina tik daudz funkcionalitātes kā lielāki ietvari, piemēram, Laravel vai Symfony. Tomēr tas nodrošina daudz no funkcionalitātes, kas jums nepieciešama, lai izveidotu tīmekļa lietojumprogrammas. Tas ir arī viegli mācīties un lietot. Tas padara to par labu izvēli, lai ātri un viegli izveidotu tīmekļa lietojumprogrammas. Ja jums ir jauni ietvaru, Flight ir lielisks sākuma ietvars iesācējam. Tas palīdzēs jums uzzināt par ietvaru izmantošanas priekšrocībām, neapburdot jūs ar pārāk lielu sarežģītību. Kad jums ir kāda pieredze ar Flight, būs vieglāk pāriet uz sarežģītākiem ietvariem, piemēram, Laravel vai Symfony, tomēr Flight joprojām var veiksmīgi izveidot izturīgu pieteikumu.

## Kas ir Maršrutēšana?

Maršrutēšana ir Flight ietvara pamats, bet kas tieši tas ir? Maršrutēšana ir procesa, kurā tiek ņemta URL un tiek atrasta atbilstoša funkcija jūsu kodā. Tie ir veidi, kā panākt to, lai jūsu tīmekļa vietne darītu dažādas lietas, atkarībā no pieprasītās URL. Piemēram, jūs varētu vēlēties rādīt lietotāja profilu, kad viņi apmeklē `/lietotājs/1234`, bet rādīt visu lietotāju sarakstu, kad tie apmeklē `/lietotāji`. Tas viss tiek darīts, pateicoties maršrutēšanai.

Tam varētu izskatīties kaut kas šāds:

- Lietotājs dodas uz jūsu pārlūka un ievada `http://piemērs.com/lietotājs/1234`.
- Serveris saņem pieprasījumu un pārbauda URL, pārsūtīdams to jūsu Flight lietojumprogrammas kodam.
- Iedomāsimies, ka jūsu Flight kodā ir kaut kas līdzīgs `Flight::route('/lietotājs/@id', [ 'LietotājaKontrolieris', 'skatsLietotājaProfils' ]);`. Jūsu Flight lietojumprogrammas kods pārbauda URL un redz, ka tā atbilst definētajai maršrutai, un tad izpilda kodu, ko esat definējis šim maršrutam.
- Flight maršruta vadītājs tad izpildīs un izsauks `skatsLietotājaProfils($id)` metodi `LietotājaKontrolieris` klasē, padodot `1234` kā `$id` argumentu metodei.
- Kods jūsu `skatsLietotājaProfils()` metodē tad izpildīs un darīs to, ko jūs tam norādīsiet. Varat beigt ar teksta HTML izvadi lietotāja profilu lapai vai, ja tas ir RESTful API, varat izvadīt JSON atbildi ar lietotāja informāciju.
- Flight ietina šo skaisti, ģenerē atbildes galvenes un nosūta to atpakaļ lietotāja pārlūkam.
- Lietotājs ir piepildīts ar prieku un sev dod siltu apskāvi!

### Un Kāpēc Tas ir Svarīgi?

Proper centralizētam maršrutētājam faktiski var dramatiski atvieglot jūsu dzīvi! Tas var būt grūti saprast no paša sākuma. Šeit ir daži iemesli, kāpēc:

- **Centralizēta Maršrutēšana**: Jūs varat turēt visus savus maršrutus vienuviet. Tas padara to vieglāk redzēt, kādus maršrutus jums ir un ko tie dara. Tas arī atvieglo tos mainīt, ja nepieciešams.
- **Maršruta Parametri**: Jūs varat izmantot maršruta parametrus, lai nodotu datus savām maršruta metodēm. Tas ir lielisks veids, kā uzturēt jūsu kodu tīru un kārtīgu.
- **Maršrutu Grupas**: Jūs varat grupēt maršrutus kopā. Tas ir lielisks veids, kā uzturēt jūsu kodu kārtībā un piemērot [starpposma programmatūru](middleware) grupai maršrutu.
- **Maršrutu Aliasēšana**: Jūs varat piešķirt aliasu maršrutam, lai URL varētu dinamiski tikt ģenerēti vēlāk jūsu kodā (piemēram, veidnē). Piemērs: tā vietā, lai cietkodētu `/lietotājs/1234` savā kodā, jūs varētu norādīt aliasu `lietotājaSkats` un padot `id` kā parametru. Tas ir lielisks gadījumā, ja izlemjat to vēlāk mainīt uz `/administrators/lietotājs/1234`. Nav jāmaina visas jūsu cietkodētas URL, tikai URL, kas piesaistīts maršrutam.
- **Maršruta Starpposma Programmatūra**: Jūs varat pievienot starpposmu programmatūru savos maršrutos. Starpposma programma ir ļoti spēcīga, pievienojot konkrētas uzvedības jūsu lietojumprogrammai, piemēram, autentifikāciju, ka konkrēts lietotājs var piekļūt maršrutam vai maršrutu grupai.

Noteikti jums ir pazīstams veids, kā izveidot tīmekļa vietni skriptu pa skriptam. Jums varētu būt fails ar nosaukumu `index.php`, kurā ir daudz `if` izteikumu, lai pārbaudītu URL un pēc tam izpildītu konkrētu funkciju, atkarībā no URL. Tas ir veids, kā maršrutēšana, bet tas nav pārāk organizēts un tas ātri var ārprātīgi. Flight maršruta sistēma ir daudz labāk organizēta un spēcīgāka veida, kā apstrādāt maršrutēšanu.

Tas?

```php

// /lietotājs/skats_profils.php?id=1234
if ($_GET['id']) {
	$id = $_GET['id'];
	skatsLietotājaProfils($id);
}

// /lietotājs/labot_profils.php?id=1234
if ($_GET['id']) {
	$id = $_GET['id'];
	rediģētLietotājaProfils($id);
}

// utt...
```

Vai tas?

```php

// index.php
Flight::route('/lietotājs/@id', [ 'LietotājaKontrolieris', 'skatsLietotājaProfils' ]);
Flight::route('/lietotājs/@id/labot', [ 'LietotājaKontrolieris', 'rediģētLietotājaProfils' ]);

// Visticamāk jūsu app/controllers/UserController.php
class UserController {
	public function skatsLietotājaProfils($id) {
		// dariet kaut ko
	}

	public function rediģētLietotājaProfils($id) {
		// dariet kaut ko
	}
}
```

Jūsu varat sākt saprast ienesīguma būtību, izmantojot centralizētu maršrutēšanas sistēmu. Tā ir daudz vieglāk pārvaldāma un saprotamā ilgtermiņā!

## Pieprasījumi un Atbildes

Flight nodrošina vienkāršu un vieglu veidu, kā apstrādāt pieprasījumus un atbildes. Tas ir tīmekļa ietvara būtība. Tas paņem pieprasījumu no lietotāja pārlūkprogrammas, apstrādā to, un pēc tam atsūta atbildi. Tas ir veids, kā var izveidot tīmekļa lietojumprogrammas, kas var darīt lietas, piemēram, rādīt lietotāja profilu, ļaut lietotājam pierakstīties vai publicēt jaunu bloga ierakstu.

### Pieprasījumi

Pieprasījums ir tas, ko lietotāja pārlūkprogramma nosūta uz jūsu serveri, kad viņi apmeklē jūsu tīmekļa vietni. Šis pieprasījums satur informāciju par to, ko lietotājs vēlas darīt. Piemēram, tas var saturēt informāciju par to, uz kuru URL lietotājs vēlas apmeklēt, kādus datus lietotājs vēlas nosūtīt uz jūsu serveri vai kāda veida datus lietotājs vēlas saņemt no jūsu servera. Svarīgi zināt, ka pieprasījums ir tikai lasīšana. Jūs nevarat mainīt pieprasījumu, bet to varat lasīt.

Flight nodrošina vienkāršu veidu, kā piekļūt informācijai par pieprasījumu. Jūs varat piekļūt pieprasījuma informācijai, izmantojot `Flight::request()` metodi. Šī metode atgriež `Request` objektu, kas satur informāciju par pieprasījumu. Jūs varat izmantot šo objektu, lai piekļūtu informācijai par pieprasījumu, piemēram, URL, metodi vai datiem, ko lietotājs nosūtījis uz jūsu serveri.

### Atbildes

Atbilde ir tas, ko jūsu serveris atsūta atpakaļ uz lietotāja pārlūkprogrammu, kad viņi apmeklē jūsu tīmekļa vietni. Šī atbilde satur informāciju par to, ko jūsu serveris vēlas darīt. Piemēram, tajā var būt informācija par to, kādu veidu datus jūsu serveris vēlas nosūtīt lietotājam, kādu veidu datus jūsu serveris vēlas saņemt no lietotāja vai kādu veidu datus jūsu serveris vēlas saglabāt lietotāja datorā.

Flight nodrošina vienkāršu veidu, kā nosūtīt atbildi uz lietotāja pārlūkprogrammu. Jūs varat nosūtīt atbildi, izmantojot `Flight::response()` metodi. Šī metode ņem `Response` objektu kā argumentu un nosūta atbildi uz lietotāja pārlūkprogrammu. Jūs varat izmantot šo objektu, lai nosūtītu atbildi uz lietotāja pārlūkprogramma, piemēram, HTML, JSON vai failu. Flight palīdz jums automātiski ģenerēt daļu no atbildes, lai atvieglotu lietas, bet visbeidzot jums ir kontrole par to, ko atsūtāt atpakaļ lietotājam.