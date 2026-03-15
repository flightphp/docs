# IA & Expérience Développeur avec Flight

## Aperçu

Flight facilite le renforcement de vos projets PHP avec des outils alimentés par l'IA et des flux de travail modernes pour les développeurs. Avec des commandes intégrées pour se connecter à des fournisseurs de LLM (Large Language Model) et générer des instructions de codage IA spécifiques au projet, Flight vous aide, vous et votre équipe, à tirer le meilleur parti des assistants IA comme GitHub Copilot, Cursor, Windsurf et Antigravity (Gemini).

## Compréhension

Les assistants de codage IA sont les plus utiles lorsqu'ils comprennent le contexte, les conventions et les objectifs de votre projet. Les helpers IA de Flight vous permettent de :
- Connecter votre projet à des fournisseurs de LLM populaires (OpenAI, Grok, Claude, etc.)
- Générer et mettre à jour des instructions spécifiques au projet pour les outils IA, afin que tout le monde obtienne une aide cohérente et pertinente
- Garder votre équipe alignée et productive, avec moins de temps passé à expliquer le contexte

Ces fonctionnalités sont intégrées au CLI principal de Flight et au projet de démarrage officiel [flightphp/skeleton](https://github.com/flightphp/skeleton).

## Utilisation de Base

### Configuration des Identifiants LLM

La commande `ai:init` vous guide à travers la connexion de votre projet à un fournisseur de LLM.

```bash
php runway ai:init
```

Vous serez invité à :
- Choisir votre fournisseur (OpenAI, Grok, Claude, etc.)
- Entrer votre clé API
- Définir l'URL de base et le nom du modèle

Cela crée les identifiants nécessaires pour effectuer des requêtes LLM futures.

**Exemple :**
```
Welcome to AI Init!
Which LLM API do you want to use? [1] openai, [2] grok, [3] claude: 1
Enter the base URL for the LLM API [https://api.openai.com]:
Enter your API key for openai: sk-...
Enter the model name you want to use (e.g. gpt-4, claude-3-opus, etc) [gpt-4o]:
Credentials saved to .runway-creds.json
```

### Génération d'Instructions IA Spécifiques au Projet

La commande `ai:generate-instructions` vous aide à créer ou mettre à jour des instructions pour les assistants de codage IA, adaptées à votre projet.

```bash
php runway ai:generate-instructions
```

Vous répondrez à quelques questions sur votre projet (description, base de données, templating, sécurité, taille de l'équipe, etc.). Flight utilise votre fournisseur de LLM pour générer des instructions, puis les écrit dans :
- `.github/copilot-instructions.md` (pour GitHub Copilot)
- `.cursor/rules/project-overview.mdc` (pour Cursor)
- `.windsurfrules` (pour Windsurf)
- `.gemini/GEMINI.md` (pour Antigravity)

**Exemple :**
```
Please describe what your project is for? My awesome API
What database are you planning on using? MySQL
What HTML templating engine will you plan on using (if any)? latte
Is security an important element of this project? (y/n) y
...
AI instructions updated successfully.
```

Maintenant, vos outils IA fourniront des suggestions plus intelligentes et plus pertinentes basées sur les besoins réels de votre projet.

## Utilisation Avancée

- Vous pouvez personnaliser l'emplacement de vos fichiers d'identifiants ou d'instructions en utilisant des options de commande (voir `--help` pour chaque commande).
- Les helpers IA sont conçus pour fonctionner avec n'importe quel fournisseur de LLM qui prend en charge les API compatibles avec OpenAI.
- Si vous souhaitez mettre à jour vos instructions au fur et à mesure que votre projet évolue, relancez simplement `ai:generate-instructions` et répondez aux invites à nouveau.

## Voir Aussi

- [Flight Skeleton](https://github.com/flightphp/skeleton) – Le démarrage officiel avec intégration IA
- [Runway CLI](/awesome-plugins/runway) – Plus d'informations sur l'outil CLI qui alimente ces commandes

## Dépannage

- Si vous voyez "Missing .runway-creds.json", exécutez d'abord `php runway ai:init`.
- Assurez-vous que votre clé API est valide et a accès au modèle sélectionné.
- Si les instructions ne se mettent pas à jour, vérifiez les permissions des fichiers dans votre répertoire de projet.

## Journal des Modifications

- v3.16.0 – Ajout des commandes CLI `ai:init` et `ai:generate-instructions` pour l'intégration IA.