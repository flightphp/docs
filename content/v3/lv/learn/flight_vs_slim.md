# Flight pret Slim

## Kas ir Slim?
[Slim](https://slimframework.com) ir PHP mikro ietvars, kas palīdz jums ātri rakstīt vienkāršas, bet spēcīgas tīmekļa lietojumprogrammas un API.

Liela daļa iedvesmas dažām no Flight 3. versijas funkcijām faktiski nāca no Slim. Maršrutu grupēšana un starpprogrammatūras izpilde noteiktā secībā ir divas funkcijas, kas tika iedvesmotas no Slim. Slim 3. versija iznāca ar mērķi uz vienkāršību, bet attiecībā uz 4. versiju ir bijušas [pretrunīgas atsauksmes](https://github.com/slimphp/Slim/issues/2770).

## Priekšrocības salīdzinājumā ar Flight

- Slim ir lielāka izstrādātāju kopiena, kuri savukārt izveido noderīgus moduļus, lai palīdzētu jums neizgudrot ratu no jauna.
- Slim ievēro daudzas saskarnes un standartus, kas ir izplatīti PHP kopienā, kas palielina savietojamību.
- Slim ir labas dokumentācijas un apmācību materiāli, ko var izmantot, lai mācītos ietvaru (nekas salīdzinājumā ar Laravel vai Symfony gan).
- Slim ir dažādi resursi, piemēram, YouTube apmācības un tiešsaistes raksti, ko var izmantot, lai mācītos ietvaru.
- Slim ļauj izmantot jebkuru komponentu, ko vēlaties, lai apstrādātu galvenās maršrutēšanas funkcijas, jo tas atbilst PSR-7.

## Trūkumi salīdzinājumā ar Flight

- Pārsteidzoši, Slim nav tik ātrs, kā jūs domājat, ka tas būtu mikro-ietvaram. Skatiet 
  [TechEmpower etalonus](https://www.techempower.com/benchmarks/#hw=ph&test=fortune&section=data-r22&l=zik073-cn3) 
  vairāk informācijas.
- Flight ir paredzēts izstrādātājam, kurš vēlas izveidot vieglu, ātru un viegli lietojamu tīmekļa lietojumprogrammu.
- Flight nav atkarību, savukārt [Slim ir dažas atkarības](https://github.com/slimphp/Slim/blob/4.x/composer.json), kuras jums jāinstalē.
- Flight ir paredzēts vienkāršībai un vieglai lietošanai.
- Viena no Flight galvenajām funkcijām ir tā, ka tā dara visu iespējamo, lai uzturētu atpakaļsaderību. Slim no 3. uz 4. versiju bija salauzta izmaiņa.
- Flight ir paredzēts izstrādātājiem, kuri pirmo reizi dodas ietvaru pasaulē.
- Flight var veikt arī uzņēmējdarbības līmeņa lietojumprogrammas, bet tam nav tik daudz piemēru un apmācību kā Slim.
  Tas arī prasīs lielāku disciplīnu no izstrādātāja puses, lai saglabātu lietas organizētas un labi strukturētas.
- Flight dod izstrādātājam lielāku kontroli pār lietojumprogrammu, savukārt Slim var ieviesies dažus burvjus aizkulisēs.
- Flight ir vienkāršs [PdoWrapper](/learn/pdo-wrapper), ko var izmantot, lai mijiedarbotos ar jūsu datubāzi. Slim prasa izmantot trešās puses bibliotēku.
- Flight ir atļauju spraudnis [/awesome-plugins/permissions], ko var izmantot, lai nodrošinātu jūsu lietojumprogrammu. Slim prasa izmantot trešās puses bibliotēku.
- Flight ir ORM, ko sauc par [active-record](/awesome-plugins/active-record), ko var izmantot, lai mijiedarbotos ar jūsu datubāzi. Slim prasa izmantot trešās puses bibliotēku.
- Flight ir CLI lietojumprogramma, ko sauc par [runway](/awesome-plugins/runway), ko var izmantot, lai palaistu jūsu lietojumprogrammu no komandrindas. Slim to nedara.