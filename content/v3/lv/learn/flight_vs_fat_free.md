# Lidojums pret Fat-Free

## Kas ir Fat-Free?
[Fat-Free](https://fatfreeframework.com) (mīļi pazīstams kā **F3**) ir spēcīgs, taču viegli lietojams PHP mikro-karkass, kas ir izstrādāts, lai palīdzētu veidot dinamiskas un izturīgas tīmekļa lietojumprogrammas - ātri!

Lidojums salīdzināts ar Fat-Free daudzos veidos un visticamāk ir tuvākais radinieks funkciju un vienkāršības ziņā. Fat-Free ir daudz funkciju, ko Lidojumam nav, bet tajā pašā laikā ir daudz funkciju, kuras ir Lidojumam. Fat-Free sāk atklāt savu vecumu un nav tik populārs kā agrāk bija.

Atnovēršanas pasākumi kļūst aizvien retāki un kopiena nav tik aktīva kā agrāk. Kods ir pietiekami vienkāršs, taču dažreiz sintakses disciplīnas trūkums var padarīt to grūti lasāmu un saprotamu. Tas darbojas ar PHP 8.3, taču pats kods joprojām izskatās tāpat kā dzīvo PHP 5.3.

## Plusi salīdzinājumā ar Lidojumu

- Fat-Free ir nedaudz vairāk zvaigžņu GitHub nekā Lidojumam.
- Fat-Free ir dažas labas dokumentācijas daļas, bet tai trūkst skaidrības dažos aspektos.
- Fat-Free ir daži ierobežoti resursi, piemēram, YouTube pamācības un tiešsaistes raksti, kas var būt izmantoti, lai iemācītos karkasu.
- Fat-Free ir iebūvēti [daži noderīgi spraudņi](https://fatfreeframework.com/3.8/api-reference) kas dažreiz ir noderīgi.
- Fat-Free ir iebūvēts ORM, ko sauc par Kartētāju, ko var izmantot, lai sazinātos ar jūsu datu bāzi. Lidojumam ir [aktīvais-ieraksts](/awesome-plugins/active-record).
- Fat-Free ir sesijas, kešatmiņu un lokalizāciju iekļaušana. Lidojumam ir jāizmanto trešo pušu bibliotēkas, bet tas ir aprakstīts [dokumentācijā](/awesome-plugins).
- Fat-Free ir neliela grupa no [kopienā radītu spraudņi](https://fatfreeframework.com/3.8/development#Community) kas var izmantoti, lai paplašinātu karkasu. Lidojumam ir daži aprakstīti [dokumentācijā](/awesome-plugins) un [piemēru](/piemeri) lapās.
- Fat-Free, tāpat kā Lidojums, nav atkarīgs no citiem pakotņu komplektiem.
- Fat-Free, tāpat kā Lidojums, ir vērsts uz to, lai izstrādātājam dotu kontroli pār viņu lietojumprogrammu un vienkāršu izstrādātāja pieredzi.
- Fat-Free saglabā atpakaļējo saderību tāpat kā Lidojums (daļēji tāpēc, ka atjauninājumi kļūst [retāki](https://github.com/bcosca/fatfree/releases)).
- Fat-Free, tāpat kā Lidojums, ir paredzēts izstrādātājiem, kuri ienirst iet karkasu zemē pirmo reizi.
- Fat-Free ir iebūvēta veidne dzinējs, kas ir izturīgāks nekā Lidojuma veidne dzinējs. Lidojums iesaka [Latte](/awesome-plugins/latte) lai to sasniegtu.
- Fat-Free ir unikāla CLI veida "marsruta" komanda, kurā jūs varat izveidot CLI lietotnes Fat-Free ietvaros un to apsaukt tāpat kā `GET` pieprasījumu. Lidojums to sasniedz ar [skrejceli](/awesome-plugins/skrejcelis).

## Mīnusi salīdzinājumā ar Lidojumu

- Fat-Free ir dažas īstenošanas pārbaudes un pat ir savs [pārbaude](https://fatfreeframework.com/3.8/test) klase, kas ir ļoti pamata. Tomēr tas nav 100% vienību pārbaudīts kā Lidojums.
- Lai faktiski meklētu dokumentācijas vietni, jums jāizmanto meklētājmotoru, piemēram, Google.
- Lidojumam ir tumšais režīms to dokumentācijas vietnē. (mikrofons ietriecienās)
- Fat-Free ir dažas moduļi, kas pamatīgi nav uzturēti.
- Lidojumam ir vienkāršs [PdoAvalku](/awesome-plugins/pdo-wrapper) kas ir mazliet vienkāršāks nekā Fat-Free iebūvētā `DB\SQL` klase.
- Lidojumam ir atļauju spraudnis kas var tikt izmantots, lai nodrošinātu jūsu lietojumprogrammu. Slaids prasa jums izmantot 
  trešo pušu bibliotēku.
- Lidojumam ir ORM saukts [aktivs-ieraksts](/awesome-plugins/active-record) kas jūtas kā ORM nekā Fat-Free Kartētājs.
  Pievienotā priekšrocība ar `aktivs-ieraksts` ir tāda, ka jūs varat definēt attiecības starp ierakstiem automātiskajiem savienojumiem, kur Fat-Free Kartētājam
  nepieciešams izveidot [SQL skatus](https://fatfreeframework.com/3.8/databases#ProsandCons).
- Pārsteidzoši, Fat-Free nesatur saknes vardnīcu. Lidojums ir apakšvietāts visu laiku, lai nekolidētu ar jūsu pašu kodu.
  `Kešatmiņa` klase ir lielākais pārkāpējs šeit.
- Fat-Free nav starpniekprogrammatūra. Tā vietā ir `pirmsmājas` un `pēcmājas` kauli, kurus var izmantot, lai filtrētu pieprasījumus un atbildes kontrolieros.
- Fat-Free nevar grupēt maršrutus.
- Fat-Free ir atkarības injekcijas konteineru vadītājs, bet dokumentācija ir ļoti vērā ņemama par to, kā to izmantot.
- Labošana var kļūt mazliet sarežģīta, jo būtībā viss tiek glabāts tā sauktajā `HIVE`.