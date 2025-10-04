# AI un izstrādātāju pieredze ar Flight

## Pārskats

Flight padara viegli uzlabot jūsu PHP projektus ar AI vadītiem rīkiem un mūsdienīgiem izstrādātāju darba plūsmu. Ar iebūvētiem komandām savienojumiem ar LLM (Large Language Model) sniedzējiem un ģenerēšanu projektu specifiskām AI kodēšanas instrukcijām, Flight palīdz jums un jūsu komandai iegūt maksimālo labumu no AI asistentiem, piemēram, GitHub Copilot, Cursor un Windsurf.

## Izpratne

AI kodēšanas asistenti ir visnoderīgākie, kad viņi saprot jūsu projekta kontekstu, konvencijas un mērķus. Flight AI palīgi ļauj jums:
- Savienot savu projektu ar populāriem LLM sniedzējiem (OpenAI, Grok, Claude utt.)
- Ģenerēt un atjaunināt projektu specifiskas instrukcijas AI rīkiem, lai visi saņemtu konsekventu, atbilstošu palīdzību
- Turēt jūsu komandu saskaņotu un produktīvu, ar mazāku laiku, kas pavadīts konteksta skaidrošanā

Šīs funkcijas ir iebūvētas Flight kodolā CLI un oficiālajā [flightphp/skeleton](https://github.com/flightphp/skeleton) starter projektā.

## Pamata lietošana

### LLM akreditīvu iestatīšana

`ai:init` komanda ved jūs cauri jūsu projekta savienojumam ar LLM sniedzēju.

```bash
php runway ai:init
```

Jums tiks uzdots:
- Izvēlēties savu sniedzēju (OpenAI, Grok, Claude utt.)
- Ievadīt savu API atslēgu
- Iestatīt bāzes URL un modeļa nosaukumu

Tas izveido `.runway-creds.json` failu jūsu projekta saknē (un nodrošina, ka tas ir jūsu `.gitignore`).

**Piemērs:**
```
Welcome to AI Init!
Which LLM API do you want to use? [1] openai, [2] grok, [3] claude: 1
Enter the base URL for the LLM API [https://api.openai.com]:
Enter your API key for openai: sk-...
Enter the model name you want to use (e.g. gpt-4, claude-3-opus, etc) [gpt-4o]:
Credentials saved to .runway-creds.json
```

### Ģenerēšana projektu specifiskām AI instrukcijām

`ai:generate-instructions` komanda palīdz jums izveidot vai atjaunināt instrukcijas AI kodēšanas asistentiem, pielāgotas jūsu projektam.

```bash
php runway ai:generate-instructions
```

Jūs atbildēsiet uz dažiem jautājumiem par jūsu projektu (apraksts, datubāze, veidnes, drošība, komandas lielums utt.). Flight izmanto jūsu LLM sniedzēju, lai ģenerētu instrukcijas, tad ieraksta tās:
- `.github/copilot-instructions.md` (for GitHub Copilot)
- `.cursor/rules/project-overview.mdc` (for Cursor)
- `.windsurfrules` (for Windsurf)

**Piemērs:**
```
Please describe what your project is for? My awesome API
What database are you planning on using? MySQL
What HTML templating engine will you plan on using (if any)? latte
Is security an important element of this project? (y/n) y
...
AI instructions updated successfully.
```

Tagad jūsu AI rīki sniegs gudrākus, atbilstošākus ieteikumus, balstītus uz jūsu projekta reālajām vajadzībām.

## Padziļinātā lietošana

- Jūs varat pielāgot jūsu akreditīvu vai instrukciju failu atrašanās vietu, izmantojot komandas opcijas (skatiet `--help` katrai komandai).
- AI palīgi ir paredzēti darbam ar jebkuru LLM sniedzēju, kas atbalsta OpenAI saderīgās API.
- Ja vēlaties atjaunināt jūsu instrukcijas, kad jūsu projekts attīstās, tikai palaidiet atkārtoti `ai:generate-instructions` un atbildiet uz uzvednēm vēlreiz.

## Skatīt arī

- [Flight Skeleton](https://github.com/flightphp/skeleton) – Oficiālais starter ar AI integrāciju
- [Runway CLI](/awesome-plugins/runway) – Vairāk par CLI rīku, kas nodrošina šīs komandas

## Traucējummeklēšana

- Ja jūs redzat "Missing .runway-creds.json", palaidiet `php runway ai:init` vispirms.
- Pārliecinieties, ka jūsu API atslēga ir derīga un tai ir piekļuve izvēlētajam modelim.
- Ja instrukcijas neatjaunojas, pārbaudiet failu atļaujas jūsu projektu direktorijā.

## Izmaiņu žurnāls

- v3.16.0 – Pievienotas `ai:init` un `ai:generate-instructions` CLI komandas AI integrācijai.