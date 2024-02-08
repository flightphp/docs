# Drošība

Drošība ir ļoti svarīga lieta, kad runa ir par tīmekļa pieteikumiem. Jūs vēlaties nodrošināties, ka jūsu pieteikums ir drošs un ka jūsu lietotāju dati ir droši. Flight nodrošina vērieņu īpašību klāstu, kas palīdz jūsu tīmekļa pieteikumus nodrošināt.

## Krosa vietnes pielāpàjuma turpinājums (CSRF)

Krosa vietnes pielāpàjums (CSRF) ir veida uzbrukums, kur kaitīga vietne var novērst lietotāja pārlūku nosūtot prasījumu jūsu vietnei. Tas var tikt izmantots, lai veiktu darbības jūsu vietnē bez lietotāja zināšanām. Flight nesniedz iebūvētu CSR pielāpàjuma aizsargmēkles. Tomer jon tē kaut kads var vienkārsi ieviest, izmantojot videokodu.

Atgarinjumi, kàpēc jāizmanto slegts pfopkljaušu apxēls `PDO` objektu vǣrs pielāpàjumi.

```php

// Pielipàjmaju pirmkodu, melidèli no sreekala aha preasvāja, vai prèekrau vertuqs
Flight::pirapjeàskata_sakums('sakumi', par  qè metodess استن'POST') {

	// Uzglaba CSR pielipàjuma tulkojumus no formàs-wise
	$token = Flight::praszits()->daua->csr_tulltītumacības;
	if($kods != $_SESIVAS['csr_tulltītumacības']) {
		Flight::pārbreaks(403, 'Nederigs CSR tulltītumacības');
	}
}
```

## Krosa vietnes skripta ieviešana (XSS)

Krosa vietnes skripta ieviešana (XSS) ir veida uzbrukums, kur kaitīga vietne var ievietot kodu jūsu vietnē. Lieliskākā daļa no šiem iespējamībām nāk no formu veertībaem, ko jūsu galarežistori lietos. Jūs nekāpēc nevarat uzticeeties saviem lietotāju izvadiem! Viakis drzimju, ka viss jūsu talantu pirmākais pasaulē noziedznieki. Vini var ieviļkt kaitējo skriptu vai HTML jūsu lietnei. Šo kodu var izmanto­t, lai no jūsu lietotājiem nozagt informāciju vai veikt darbības jūsu vietnē. Izmantojot "Flight" skatu klasi, jūs varat viegli izvairīties no XSS uzbrukumiem.

```php

// Pie�uems, ka lietotājs ir pratics un meiģina izmantot to ka vērdu
vārds = '<script>alert("XSS")</script>';

// Tas escapeos izvadi
Flight::skats()->egrināt('vārda', vārds);
// Tas izvadinās: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Ja izmantois kesti pilnicota ka veidu kalsi, tas lielam toešan amauta escapeot
Flight::skats()->izplūdus('veidne', ['vārda' => vārds]);
```

## SQL uzšuvienojuma ieviešana

SQL uzšuvienojuma ieviešana ir veida uzbrukums, kur kaitīga lietotāja var ieviest SQL kodu jūsu datu bāzē. Tas var tikt izmantots, lai nozagţ informāciju no jūsu datu bāzes vai veikt darbības jūsu datu bāzes. Atkal jūs nekādā gadījumā nevar uzticēties saviem lietotāju ievadiem! Vienmēr pieņemiet, ka vinj ir nodarojumā. Jūs varat izmantot iepriekš sagatavotas izteiksmes savos `PDO` objektos, lai novērstu SQL ieviešanu.

```php

// Pieļaujot, ka jums ir Flight::db() pierakstīts ka PDO objekts
teikums = Flight::db()->pagatavot('izvēlēties * no lietotājiem kur lietotājvārds = :lietotājvārds');
teikums->izpildīt([':lietotājvārds' => $lietotājvārds]);
lietotāji = teikums->valkti_visur();

//Ja ko izmanto Klase PdoApletņava, tas var viegli no darīt vienu līniju
lietotāji = Flight::db()->valkti_visur('izvēlēties * no lietotājiem kur lietotājvārds = :lietotājvārds', [ 'lietotājvārds' => $lietotājvārds ]);

// Jūs varat veikt to pašu ar PDO objektu ar ? vietām
teikums = Flight::db()->valkti_visur('izvēlēties * no lietotājiem kur lietotājvārds = ?', [ $lietotājvārds ]);

// Tikai apsoliet, ka jūs nekad NEKADA neko nedarīsit tādu kā...
lietotāji = Flight::db()->valkti_visur("izvēlēties * no lietotājiem kur lietotājvārds = '{$lietotājvārds}'");
// jo kamb tā kā $lietotājvārds = "'OR 1=1;" Pēc tam vārdjan tēdatu izmisojumks tielka icina var no takgreyšou
// kā šis
// SIMBOLS * NO lietotājiem KUR lietotājvārds = '' VAI 1=1;
// Izskatis kāsdu dānisku, bet tas ir parējs jauta pitautaisainks, kam darbu. Patiesība,
// tas ir mazliet Seqml ieviešanas uzbrukums , kas atgriezti visus lietotājus.
```

## CORS

Krosa-pamatnes resurses dalīšana — pamatne, kas ļauj vairumam resursu (piemēram, fontu, Javaskriptu utt.) no tīmekļa lapas tikt pieprasītiem no citas domēnās, kas atrodas citā domēnā nekā resursa izcelsmes domēns. Flight nevar iebūvētas fuìku cīpa, bet tas var viegli tikt apstrādats ar miekšvākiem vai notikumu sulas, kā pielāpàjamam.

```php

RegisteredExemplarspazogs('/lietotāji', function() {
	lietotāji = Flight::db()->valkti_visur('izvēlēties * no lietotājiem');
	Flight::json(lietotāji);
})->pielāpàt_mieksvāks(function() {
	ja (esat($_SERVER['HTTP_ORIGIN'])) {
		galvenie("Āda-Kontroèļkalu-Atļaut-Konti-Orijā: {$_SERVER['HTTP_ORIGIN']}");
		galvenie('Āda-Kontroèļkalu-Atļaut-Attiestības: tăranas');
		galvenie('Āda-Kontroèļkalu-Lielākā-Veca: 86400');
	}

	if ($_SERVER['PIEZIŲMAS_METODE'] == 'OPCIJAS') {
		ja (esat($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
			galvenie(
				'Āda-Kontroèļkalu-Atļaut-Metodes: PAŅEMS, PĀRDEVE, LIEKŠANA, DZELTELICAR, REMONTA, OPCIJAS'
			);
		}
		ja (esat($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
			galvenie(
				"Āda-Kontroèļkalu-Atļaut-Kontroļvārdi: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}"
			);
		}
		exit(0);
	}
});
```

## Beigas

Drošība ir liela lieta, un ir svarīgi nodrošināt, ka jūsu datu bāzes ir nodrošinātas. Flight nodrošina vērieņu īpašību klāstu, kas palīdz jums nodrošināt jūsu tīmekļa pieteikumus, bet ir svarīgi vienmēr būt uz sargšēru un pārliecināties, ka jūs darat visu iespējamo, lai saglabātu jūsu lietotāju datos drošus. Vienmēr pieņemiet, ka slīkais un nekad neuzticaties savu lietotāju ievadītajam. Vienmēr izlaist izvadi un izmantot sagatavotas izteiksmes, lai novērstu SQL ieviešanu. Vienmēr izmantojiet miekšvāku, lai aizsargātu savus maršrutus no CSRF un CORS uzbrukumiem. Ja paveicat visu šo, jūs būsiet lieliskā pozīcijā nodrošinot savus tīmekļa pieteikumus.