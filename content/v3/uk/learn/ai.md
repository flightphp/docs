# AI та досвід розробника з Flight

## Огляд

Flight полегшує суперзарядку ваших PHP-проектів за допомогою інструментів на основі штучного інтелекту та сучасних робочих процесів розробників. З вбудованими командами для підключення до постачальників LLM (Large Language Model) та генерації проектно-специфічних інструкцій для кодування на основі AI, Flight допомагає вам та вашій команді отримати максимум від AI-помічників, таких як GitHub Copilot, Cursor, Windsurf та Antigravity (Gemini).

## Розуміння

AI-помічники для кодування найбільш корисні, коли вони розуміють контекст вашого проекту, конвенції та цілі. AI-допоміжники Flight дозволяють вам:
- Підключити ваш проект до популярних постачальників LLM (OpenAI, Grok, Claude тощо)
- Генерувати та оновлювати проектно-специфічні інструкції для AI-інструментів, щоб кожен отримував послідовну, релевантну допомогу
- Зберігати вашу команду узгодженою та продуктивною, витрачаючи менше часу на пояснення контексту

Ці функції вбудовані в основний CLI Flight та офіційний стартовий проект [flightphp/skeleton](https://github.com/flightphp/skeleton).

## Основне використання

### Налаштування облікових даних LLM

Команда `ai:init` проведе вас через процес підключення вашого проекту до постачальника LLM.

```bash
php runway ai:init
```

Вам буде запропоновано:
- Вибрати ваш постачальник (OpenAI, Grok, Claude тощо)
- Ввести ваш API-ключ
- Встановити базовий URL та назву моделі

Це створює необхідні облікові дані для майбутніх запитів LLM.

**Приклад:**
```
Welcome to AI Init!
Which LLM API do you want to use? [1] openai, [2] grok, [3] claude: 1
Enter the base URL for the LLM API [https://api.openai.com]:
Enter your API key for openai: sk-...
Enter the model name you want to use (e.g. gpt-4, claude-3-opus, etc) [gpt-4o]:
Credentials saved to .runway-creds.json
```

### Генерація проектно-специфічних AI-інструкцій

Команда `ai:generate-instructions` допомагає створити або оновити інструкції для AI-помічників кодування, адаптовані до вашого проекту.

```bash
php runway ai:generate-instructions
```

Ви відповість на кілька питань про ваш проект (опис, база даних, шаблонізація, безпека, розмір команди тощо). Flight використовує ваш постачальник LLM для генерації інструкцій, а потім записує їх у:
- `.github/copilot-instructions.md` (для GitHub Copilot)
- `.cursor/rules/project-overview.mdc` (для Cursor)
- `.windsurfrules` (для Windsurf)
- `.gemini/GEMINI.md` (для Antigravity)

**Приклад:**
```
Please describe what your project is for? My awesome API
What database are you planning on using? MySQL
What HTML templating engine will you plan on using (if any)? latte
Is security an important element of this project? (y/n) y
...
AI instructions updated successfully.
```

Тепер ваші AI-інструменти надаватимуть розумніші, більш релевантні пропозиції на основі реальних потреб вашого проекту.

## Розширене використання

- Ви можете налаштувати розташування файлів з обліковими даними або інструкціями за допомогою опцій команд (див. `--help` для кожної команди).
- AI-допоміжники розроблені для роботи з будь-яким постачальником LLM, який підтримує API, сумісні з OpenAI.
- Якщо ви хочете оновити ваші інструкції з еволюцією проекту, просто повторно запустіть `ai:generate-instructions` та відповістьте на запити знову.

## Див. також

- [Flight Skeleton](https://github.com/flightphp/skeleton) – Офіційний стартер з інтеграцією AI
- [Runway CLI](/awesome-plugins/runway) – Більше про CLI-інструмент, що живить ці команди

## Вирішення проблем

- Якщо ви бачите "Missing .runway-creds.json", спочатку запустіть `php runway ai:init`.
- Переконайтеся, що ваш API-ключ дійсний та має доступ до обраної моделі.
- Якщо інструкції не оновлюються, перевірте дозволи файлів у вашому проектному каталозі.

## Журнал змін

- v3.16.0 – Додано команди CLI `ai:init` та `ai:generate-instructions` для інтеграції AI.