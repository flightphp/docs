# Flight salīdzinājumā ar Fat-Free

## Kas ir Fat-Free?
[Fat-Free](https://fatfreeframework.com) (mīļi saukts par **F3**) ir jaudīgs, bet viegli lietojams PHP mikroietvars, kas paredzēts, lai palīdzētu jums ātri izveidot dinamiskas un robustas
tīmekļa lietojumprogrammas!

Flight salīdzinājumā ar Fat-Free ir daudz kopīga un, iespējams, ir tuvākais radniecīgais ziņā par funkcijām un vienkāršību. Fat-Free ir
daudzas funkcijas, kuras Flight nepiedāvā, bet tam ir arī daudz funkciju, kuras Flight piedāvā. Fat-Free sāk parādīt savu vecumu
un nav tik populārs, kā tas reiz bija.

Atjauninājumi kļūst retāki, un kopiena nav tik aktīva, kā tas reiz bija. Kods ir pietiekami vienkāršs, bet dažreiz sintakses disciplīnas trūkums var padarīt to grūti lasāmu un saprotamu. Tas darbojas ar PHP 8.3, bet pats kods joprojām izskatās tā, it kā dzīvotu
PHP 5.3.

## Priekšrocības salīdzinājumā ar Flight

- Fat-Free ir nedaudz vairāk zvaigznes GitHub, nekā Flight.
- Fat-Free ir diezgan laba dokumentācija, bet tai trūkst skaidrības dažās jomās.
- Fat-Free ir daži resursi, piemēram, YouTube apmācības un tiešsaistes raksti, ko var izmantot, lai mācītos ietvaru.
- Fat-Free ir [daži noderīgi spraudņi](https://fatfreeframework.com/3.8/api-reference), kas iebūvēti un dažreiz ir noderīgi.
- Fat-Free ir iebūvēts ORM, ko sauc par Mapper, ko var izmantot, lai mijiedarbotos ar jūsu datubāzi. Flight ir [active-record](/awesome-plugins/active-record).
- Fat-Free ir iebūvētas sesijas, kešošana un lokalizācija. Flight prasa izmantot trešo pušu bibliotēkas, bet tas ir aprakstīts [dokumentācijā](/awesome-plugins).
- Fat-Free ir neliela [kopienas izveidoto spraudņu](https://fatfreeframework.com/3.8/development#Community) grupa, ko var izmantot, lai paplašinātu ietvaru. Flight ir daži, kas aprakstīti [dokumentācijā](/awesome-plugins) un [piemēru](/examples) lapās.
- Fat-Free, tāpat kā Flight, nav atkarību.
- Fat-Free, tāpat kā Flight, ir paredzēts, lai dotu izstrādātājam kontroli pār viņu lietojumprogrammu un vienkāršu izstrādātāja pieredzi.
- Fat-Free uztur atpakaļsaderību, tāpat kā Flight (daļēji tāpēc, ka atjauninājumi kļūst [retāki](https://github.com/bcosca/fatfree/releases)).
- Fat-Free, tāpat kā Flight, ir paredzēts izstrādātājiem, kuri tikai iepazīstas ar ietvaru pasauli pirmo reizi.
- Fat-Free ir iebūvēts veidņu dzinējs, kas ir robustāks nekā Flight veidņu dzinējs. Flight iesaka izmantot [Latte](/awesome-plugins/latte), lai to sasniegtu.
- Fat-Free ir unikāla CLI veida "route" komanda, kurā varat izveidot CLI lietojumprogrammas pašā Fat-Free un apstrādāt to gandrīz kā `GET` pieprasījumu. Flight to sasniegs ar [runway](/awesome-plugins/runway).

## Trūkumi salīdzinājumā ar Flight

- Fat-Free ir daži ieviešanas testi un pat savs [tests](https://fatfreeframework.com/3.8/test) klase, kas ir ļoti pamata. Tomēr,
  tas nav 100% vienības testēts, kā Flight.
- Jums jāizmanto meklēšanas dzinējs, piemēram, Google, lai faktiski meklētu dokumentācijas vietnē.
- Flight dokumentācijas vietnē ir tumšais režīms. (mic drop)
- Fat-Free ir daži moduļi, kas ir bēdīgi uzturēti.
- Flight ir vienkāršs [PdoWrapper](/learn/pdo-wrapper), kas ir nedaudz vienkāršāks nekā Fat-Free iebūvētais `DB\SQL` klase.
- Flight ir [atļauju spraudnis](/awesome-plugins/permissions), ko var izmantot, lai nodrošinātu jūsu lietojumprogrammu. Fat Free prasa izmantot 
  trešo pušu bibliotēku.
- Flight ir ORM, ko sauc par [active-record](/awesome-plugins/active-record), kas jūtas vairāk kā ORM nekā Fat-Free Mapper.
  Papildu ieguvums `active-record` ir tas, ka varat definēt saiknes starp ierakstiem automātiskiem savienojumiem, kur Fat-Free Mapper
  prasa izveidot [SQL skatus](https://fatfreeframework.com/3.8/databases#ProsandCons).
- Pārsteidzoši, bet Fat-Free nav saknes vārdtelpas. Flight ir vārdtelpota visā garumā, lai nekonfliktētu ar jūsu paša kodu.
  `Cache` klase ir lielākais pārkāpējs šeit.
- Fat-Free nav starpprogrammatūras. Tā vietā ir `beforeroute` un `afterroute` āķi, ko var izmantot, lai filtrētu pieprasījumus un atbildes kontroteros.
- Fat-Free nevar grupēt maršrutus.
- Fat-Free ir atkarību injekcijas konteinera apstrādātājs, bet dokumentācija ir neticami skopa par to, kā to izmantot.
- Kļūdu labošana var kļūt nedaudz sarežģīta, jo faktiski viss ir saglabāts tajā, ko sauc par [`HIVE`](https://fatfreeframework.com/3.8/quick-reference)