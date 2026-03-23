# AI un izstrādātāju pieredze ar Flight

## Pārskats

Flight padara vieglu jūsu PHP projektu uzlādes ar AI vadītiem rīkiem un modernām izstrādātāju darba plūsmām. Ar iebūvētiem komandām savienojumiem ar LLM (Large Language Model) sniedzējiem un ģenerēšanu projektu specifiskām AI kodēšanas instrukcijām, Flight palīdz jums un jūsu komandai iegūt maksimumu no AI asistentiem kā GitHub Copilot, Cursor, Windsurf un Antigravity (Gemini).

## Saprašana

AI kodēšanas asistenti ir visnoderīgākie, kad viņi saprot jūsu projekta kontekstu, konvencijas un mērķus. Flight AI palīgi ļauj jums:
- Savienot jūsu projektu ar populāriem LLM sniedzējiem (OpenAI, Grok, Claude utt.)
- Ģenerēt un atjaunināt projektu specifiskas instrukcijas AI rīkiem, lai visi saņemtu konsekventu, relevantu palīdzību
- Turēt jūsu komandu saskaņotu un produktīvu, ar mazāku laiku, kas pavadīts konteksta skaidrošanā

Šīs funkcijas ir iebūvētas Flight kodola CLI un oficiālajā [flightphp/skeleton](https://github.com/flightphp/skeleton) sākuma projektā.

## Pamata izmantošana

### LLM akreditīvu iestatīšana

`ai:init` komanda ved jūs cauri jūsu projekta savienojumam ar LLM sniedzēju.

```bash
php runway ai:init
```

Jums tiks lūgts:
- Izvēlēties jūsu sniedzēju (OpenAI, Grok, Claude utt.)
- Ievadīt jūsu API atslēgu
- Iestatīt bāzes URL un modeļa nosaukumu

Tas izveido nepieciešamos akreditīvus, lai jūs varētu veikt nākotnes LLM pieprasījumus.

**Piemērs:**
```
Welcome to AI Init!
Which LLM API do you want to use? [1] openai, [2] grok, [3] claude: 1
Enter the base URL for the LLM API [https://api.openai.com]:
Enter your API key for openai: sk-...
Enter the model name you want to use (e.g. gpt-4, claude-3-opus, etc) [gpt-4o]:
Credentials saved to .runway-creds.json
```

### Ģenerēšana projektu specifisku AI instrukciju

`ai:generate-instructions` komanda palīdz jums izveidot vai atjaunināt instrukcijas AI kodēšanas asistentiem, pielāgotas jūsu projektam.

```bash
php runway ai:generate-instructions
```

Jūs atbildēsiet uz dažiem jautājumiem par jūsu projektu (apraksts, datubāze, veidnes, drošība, komandas lielums utt.). Flight izmanto jūsu LLM sniedzēju, lai ģenerētu instrukcijas, tad ieraksta tās:
- `.github/copilot-instructions.md` (for GitHub Copilot)
- `.cursor/rules/project-overview.mdc` (for Cursor)
- `.windsurfrules` (for Windsurf)
- `.gemini/GEMINI.md` (for Antigravity)

**Piemērs:**
```
Please describe what your project is for? My awesome API
What database are you planning on using? MySQL
What HTML templating engine will you plan on using (if any)? latte
Is security an important element of this project? (y/n) y
...
AI instructions updated successfully.
```

Tagad jūsu AI rīki sniegs gudrākus, relevantākus ieteikumus, balstītus uz jūsu projekta reālajām vajadzībām.

## Padziļinātā izmantošana

- Jūs varat pielāgot jūsu akreditīvu vai instrukciju failu atrašanās vietu, izmantojot komandas opcijas (skatīt `--help` katrai komandai).
- AI palīgi ir paredzēti darbam ar jebkuru LLM sniedzēju, kas atbalsta OpenAI saderīgās API.
- Ja jūs vēlaties atjaunināt jūsu instrukcijas, kad jūsu projekts attīstās, tikai palaidiet atkārtoti `ai:generate-instructions` un atbildiet uz uzvednēm atkārtoti.

## Skatīt arī

- [Flight Skeleton](https://github.com/flightphp/skeleton) – Oficiālais sākuma ar AI integrāciju
- [Runway CLI](/awesome-plugins/runway) – Vairāk par CLI rīku, kas nodrošina šīs komandas

## Traucējumu novēršana

- Ja jūs redzat "Missing .runway-creds.json", palaidiet `php runway ai:init` vispirms.
- Pārliecinieties, ka jūsu API atslēga ir derīga un tai ir piekļuve izvēlētajam modelim.
- Ja instrukcijas neatjauninātas, pārbaudiet failu atļaujas jūsu projektu direktorijā.

## Izmaiņu žurnāls

- v3.16.0 – Pievienotas `ai:init` un `ai:generate-instructions` CLI komandas AI integrācijai.