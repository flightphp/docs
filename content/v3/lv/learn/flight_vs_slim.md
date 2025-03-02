# Flight pret Slim

## Kas ir Slim?
[Slim](https://slimframework.com) ir PHP mikrostruktūra, kas palīdz ātri izveidot vienkāršas, bet jaudīgas tīmekļa lietojumprogrammas un API.

Daudz no iedvesmas v3 funkciju lidojumam patiešām nāca no Slīma. Ceļu grupēšana un vidējā programmatūra izpilde konkrētā secībā ir divas funkcijas, kas tika iedvesmotas no Slīma. Slīma v3 tika izlaista orientējoties uz vienkāršību, bet ir bijis [dažādi vērtējumi](https://github.com/slimphp/Slim/issues/2770) attiecībā uz v4.

## Plusi salīdzinot ar Lidojumu

- Slīmam ir lielāka attīstītāju kopiena, kas savukārt izveido noderīgas moduļus, lai palīdzētu jums neuzgriezt riteni.
- Slīms seko daudziem interfeisiem un standartiem, kas ir kopīgi PHP kopienā, palielinot savstarpējo saderību.
- Slīmam ir pieņemama dokumentācija un pamācības, kas var tikt izmantotas, lai iemācītos pamatus (nekas salīdzinot ar Laravel vai Symfony tomēr).
- Slīmam ir dažādi resursi, piemēram YouTube pamācības un tiešsaistes raksti, kas var tikt izmantoti, lai iemācītos pamatus.
- Slīms ļauj jums izmantot jebkurus komponentus, ko vēlaties, lai apstrādātu galvenās maršrutēšanas funkcijas, jo tas atbilst PSR-7.

## Mīnusi salīdzinot ar lidojumu

- Pārsteidzoši, Slīms nav tik ātrs, cik varētu domāt par mikrostruktūru. Skatiet
  [TechEmpower pārbaudes](https://www.techempower.com/benchmarks/#hw=ph&test=fortune&section=data-r22&l=zik073-cn3)
  plašākai informācijai.
- Lidojums ir orientēts uz izstrādātāju, kas vēlas izveidot vieglu, ātru un viegli lietojamu tīmekļa lietojumprogrammu.
- Lidojumam nav atkarību, bet [Slimam ir dažas atkarības](https://github.com/slimphp/Slim/blob/4.x/composer.json), kas jāinstalē.
- Lidojums ir orientēts uz vienkāršību un lietojamību.
- Viena no Lidojuma pamatfunkcijām ir tā, ka tas cenšas labāk uzturēt atpakaļējo saderību. Slīma no v3 līdz v4 bija pārtraucošs pārmaiņas.
- Lidojums paredzēts izstrādātājiem, kuri ienāk ietvaros pirmo reizi.
- Lidojumam ir iespējamas lielas mēroga programmas, bet tam nav tik daudz piemēru un pamācību kā Slīmam.
  Tas prasīs arī vairāk disciplīnas no izstrādātāja puses, lai saglabātu lietas kārtībā un labi strukturētu.
- Lidojums ļauj izstrādātājam vairāk kontroles pār lietojumprogrammu, turpretī Slīms var slīpt aizkulises kāds maģija.
- Lidojumam ir vienkāršs [PdoWrapper](/awesome-plugins/pdo-wrapper), kas var tikt izmantots, lai mijiedarbotos ar jūsu datu bāzi. Slīmam ir jāizmanto
  trešās puses bibliotēka.
- Lidojumu var izmantot atļauju spraudnis](/awesome-plugins/permissions), lai nodrošinātu lietojumprogrammu. Slīmam ir nepieciešams izmantot
  trešās puses bibliotēka.
- Lidojumam ir ORM, kas saucas [active-record](/awesome-plugins/active-record), kas var tikt izmantots, lai mijiedarbotos ar jūsu datu bāzi. Slīmam ir jāizmanto
  trešās puses bibliotēka.
- Lidojumam ir CLI lietojumprogramma, kas saucas [runway](/awesome-plugins/runway), kas var tikt izmantota, lai palaistu jūsu lietojumprogrammu no komandrindas. Slīmam nav.