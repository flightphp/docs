# AI & Izstrādātāja pieredze ar Flight

Flight ir viss par to, lai palīdzētu jums būvēt ātrāk, gudrāk un ar mazāku berzi — it īpaši, strādājot ar AI darbinātiem rīkiem un mūsdienīgiem izstrādātāju darba plūsmām. Šī lapa aptver, kā Flight padara viegli uzlabot jūsu projektus ar AI, un kā sākt ar jaunajiem AI palīgiem, kas iebūvēti tieši ietvarā un skeleton projektā.

---

## AI gatavs pēc noklusējuma: Skeleton projekts

Oficiālais [flightphp/skeleton](https://github.com/flightphp/skeleton) iesācējs tagad nāk ar instrukcijām un konfigurāciju populāriem AI kodēšanas palīgiem:

- **GitHub Copilot**
- **Cursor**
- **Windsurf**

Šie rīki ir iepriekš konfigurēti ar projekta specifiskām instrukcijām, tāpēc jūs un jūsu komanda varat saņemt visvairāk atbilstošu, konteksta izpratni palīdzību, kodējot. Tas nozīmē:

- AI palīgi saprot jūsu projekta mērķus, stilu un prasības
- Vienota vadlīnija visiem dalībniekiem
- Mazāk laika pavadīts, lai izskaidrotu kontekstu, vairāk laika būvējot

> **Kāpēc tas ir svarīgi?**
>
> Kad jūsu AI rīki zina jūsu projekta nodomu un konvencijas, tie var palīdzēt izveidot funkcijas, pārstrukturēt kodu un izvairīties no izplatītām kļūdām — padarot jūs (un jūsu komandu) produktīvāku no pirmās dienas.

---

## Jaunas AI komandas Flight kodolā

_v3.16.0+_

Flight kodols tagad ietver divas spēcīgas CLI komandas, lai palīdzētu jums iestatīt un vadīt jūsu projektu ar AI:

### 1. `ai:init` — Pieslēgties jūsu mīļākajam LLM sniedzējam

Šī komanda vada jūs cauri akreditāciju iestatīšanai LLM (Liela Valoda Modelis) sniedzējam, piemēram, OpenAI, Grok vai Anthropic (Claude).

**Piemērs:**
```bash
php runway ai:init
```
Jūs tiksiet aicināts izvēlēties savu sniedzēju, ievadīt savu API atslēgu un izvēlēties modeli. Tas padara viegli pieslēgt jūsu projektu jaunākajiem AI pakalpojumiem — bez manuālas konfigurācijas nepieciešamības.

### 2. `ai:generate-instructions` — Projekta izpratnes AI kodēšanas instrukcijas

Šī komanda palīdz izveidot vai atjaunot projekta specifiskās instrukcijas AI kodēšanas palīgiem. Tā uzdod jums dažus vienkāršus jautājumus par jūsu projektu (piemēram, kam tas ir paredzēts, kādu datu bāzi izmantojat, komandas lielumu utt.), pēc tam izmanto jūsu LLM sniedzēju, lai izveidotu pielāgotas instrukcijas.

Ja jums jau ir instrukcijas, tas tās atjaunina, lai atspoguļotu jūsu sniegtās atbildes. Šīs instrukcijas tiek automātiski ierakstītas:
- `.github/copilot-instructions.md` (for Github Copilot)
- `.cursor/rules/project-overview.mdc` (for Cursor)
- `.windsurfrules` (for Windsurf)

**Piemērs:**
```bash
php runway ai:generate-instructions
```

> **Kāpēc tas ir noderīgi?**
>
> Ar atjauninātu, projekta specifisku instrukciju, jūsu AI rīki var:
> - Dot labākus koda ieteikumus
> - Saprast jūsu projekta unikālās vajadzības
> - Palīdzēt jauniem dalībniekiem ātrāk iepazīties
> - Samazināt berzi un neskaidrības, kad jūsu projekts attīstās

---

## Ne tikai AI lietotņu veidošanai

Kaut arī jūs varat izmantot Flight, lai izveidotu AI darbinātas funkcijas (piemēram, čatbotus, gudras API vai integrācijas), īstā jauda ir tajā, kā Flight palīdz jums strādāt labāk ar AI rīkiem kā izstrādātājam. Tas ir par:

- **Palielinot produktivitāti** ar AI atbalstītu kodēšanu
- **Turēt komandu saskaņotu** ar kopīgām, attīstošām instrukcijām
- **Padarīt iesācēju vieglāku** jauniem dalībniekiem
- **Ļaujot jums koncentrēties uz būvniecību**, nevis cīņu ar rīkiem

---

## Uzziniet vairāk un sāciet

- Skatiet [Flight Skeleton](https://github.com/flightphp/skeleton) par gatavu, AI draudzīgu iesācēju
- Pārbaudiet pārējo [Flight dokumentāciju](/learn) padomiem, kā būvēt ātru, mūsdienīgu PHP lietotnes