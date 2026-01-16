# AI un izstrādātāju pieredze ar Flight

## Pārskats

Flight padara viegli uzlabot jūsu PHP projektus ar AI vadītiem rīkiem un moderniem izstrādātāju darba plūsmu. Ar iebūvētiem komandām savienojumiem ar LLM (Large Language Model) sniedzējiem un ģenerēšanu projektu specifiskām AI kodēšanas instrukcijām, Flight palīdz jums un jūsu komandai iegūt maksimālu labumu no AI asistentiem, piemēram, GitHub Copilot, Cursor, Windsurf un Antigravity (Gemini).

## Saprašana

AI kodēšanas asistenti ir visnoderīgākie, kad tie saprot jūsu projekta kontekstu, konvencijas un mērķus. Flight AI palīgi ļauj jums:
- Savienot jūsu projektu ar populāriem LLM sniedzējiem (OpenAI, Grok, Claude utt.)
- Ģenerēt un atjaunināt projektu specifiskas instrukcijas AI rīkiem, lai visi saņemtu konsekventu, atbilstošu palīdzību
- Turēt jūsu komandu saskaņotu un produktīvu, ar mazāk laika iztērētu konteksta skaidrošanai

Šīs funkcijas ir iebūvētas Flight kodola CLI un oficiālajā [flightphp/skeleton](https://github.com/flightphp/skeleton) sākuma projektā.

## Pamata izmantošana

### LLM akreditīvu iestatīšana

`ai:init` komanda ved jūs cauri jūsu projekta savienojumam ar LLM sniedzēju.

```bash
php runway ai:init
```

Jums tiks prasīts:
- Izvēlēties jūsu sniedzēju (OpenAI, Grok, Claude utt.)
- Ievadīt jūsu API atslēgu
- Iestatīt bāzes URL un modeļa nosaukumu

Tas izveido nepieciešamos akreditīvus, lai jūs varētu veikt turpmākus LLM pieprasījumus.

**Piemērs:**
```
Laipni lūgti AI Init!
Kuru LLM API jūs vēlaties izmantot? [1] openai, [2] grok, [3] claude: 1
Ievadiet bāzes URL LLM API [https://api.openai.com]:
Ievadiet jūsu API atslēgu openai: sk-...
Ievadiet modeļa nosaukumu, kuru vēlaties izmantot (piem. gpt-4, claude-3-opus utt.) [gpt-4o]:
Akreditīvi saglabāti .runway-creds.json
```

### Projekta specifisku AI instrukciju ģenerēšana

`ai:generate-instructions` komanda palīdz izveidot vai atjaunināt instrukcijas AI kodēšanas asistentiem, pielāgotas jūsu projektam.

```bash
php runway ai:generate-instructions
```

Jūs atbildēsiet uz dažiem jautājumiem par jūsu projektu (apraksts, datubāze, veidnes, drošība, komandas lielums utt.). Flight izmanto jūsu LLM sniedzēju, lai ģenerētu instrukcijas, pēc tam ieraksta tās:
- `.github/copilot-instructions.md` (GitHub Copilot)
- `.cursor/rules/project-overview.mdc` (Cursor)
- `.windsurfrules` (Windsurf)
- `.gemini/GEMINI.md` (Antigravity)

**Piemērs:**
```
Lūdzu, aprakstiet, kam ir paredzēts jūsu projekts? Mans lieliskais API
Kuru datubāzi jūs plānojat izmantot? MySQL
Kuru HTML veidņu dzinēju jūs plānojat izmantot (ja kādu)? latte
Vai drošība ir svarīgs elements šim projektam? (y/n) y
...
AI instrukcijas atjauninātas veiksmīgi.
```

Tagad jūsu AI rīki sniegs gudrākus, atbilstošākus ieteikumus, balstītus uz jūsu projekta reālajām vajadzībām.

## Uzlabota izmantošana

- Jūs varat pielāgot jūsu akreditīvu vai instrukciju failu atrašanās vietu, izmantojot komandas opcijas (skat. `--help` katrai komandai).
- AI palīgi ir paredzēti darbam ar jebkuru LLM sniedzēju, kas atbalsta OpenAI saderīgās API.
- Ja vēlaties atjaunināt jūsu instrukcijas, kad jūsu projekts attīstās, vienkārši palaidiet atkārtoti `ai:generate-instructions` un atbildiet uz uzvednēm vēlreiz.

## Skatīt arī

- [Flight Skeleton](https://github.com/flightphp/skeleton) – Oficiālais sākuma projekts ar AI integrāciju
- [Runway CLI](/awesome-plugins/runway) – Vairāk par CLI rīku, kas nodrošina šīs komandas

## Traucējummeklēšana

- Ja redzat "Missing .runway-creds.json", vispirms palaidiet `php runway ai:init`.
- Pārliecinieties, ka jūsu API atslēga ir derīga un tai ir piekļuve izvēlētajam modelim.
- Ja instrukcijas neatjaunojas, pārbaudiet failu atļaujas jūsu projektu direktorijā.

## Izmaiņu žurnāls

- v3.16.0 – Pievienotas `ai:init` un `ai:generate-instructions` CLI komandas AI integrācijai.