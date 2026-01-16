# AI и опыт разработчика с Flight

## Обзор

Flight упрощает суперзарядку ваших PHP-проектов с помощью инструментов на базе ИИ и современных рабочих процессов разработчика. С встроенными командами для подключения к провайдерам LLM (Large Language Model) и генерации специфических для проекта инструкций по кодированию с ИИ, Flight помогает вам и вашей команде извлечь максимум из ИИ-помощников, таких как GitHub Copilot, Cursor, Windsurf и Antigravity (Gemini).

## Понимание

ИИ-помощники по кодированию наиболее полезны, когда они понимают контекст, конвенции и цели вашего проекта. ИИ-помощники Flight позволяют вам:
- Подключить ваш проект к популярным провайдерам LLM (OpenAI, Grok, Claude и т.д.)
- Генерировать и обновлять специфические для проекта инструкции для ИИ-инструментов, чтобы все получали последовательную, релевантную помощь
- Сохранять вашу команду в согласованности и продуктивности, тратя меньше времени на объяснение контекста

Эти функции встроены в основной CLI Flight и официальный стартовый проект [flightphp/skeleton](https://github.com/flightphp/skeleton).

## Базовое использование

### Настройка учетных данных LLM

Команда `ai:init` проведет вас через процесс подключения вашего проекта к провайдеру LLM.

```bash
php runway ai:init
```

Вам будет предложено:
- Выбрать вашего провайдера (OpenAI, Grok, Claude и т.д.)
- Ввести ваш API-ключ
- Установить базовый URL и имя модели

Это создаст необходимые учетные данные для будущих запросов к LLM.

**Пример:**
```
Welcome to AI Init!
Which LLM API do you want to use? [1] openai, [2] grok, [3] claude: 1
Enter the base URL for the LLM API [https://api.openai.com]:
Enter your API key for openai: sk-...
Enter the model name you want to use (e.g. gpt-4, claude-3-opus, etc) [gpt-4o]:
Credentials saved to .runway-creds.json
```

### Генерация специфических для проекта инструкций ИИ

Команда `ai:generate-instructions` помогает создать или обновить инструкции для ИИ-помощников по кодированию, адаптированные к вашему проекту.

```bash
php runway ai:generate-instructions
```

Вы ответите на несколько вопросов о вашем проекте (описание, база данных, шаблонизация, безопасность, размер команды и т.д.). Flight использует вашего провайдера LLM для генерации инструкций, затем записывает их в:
- `.github/copilot-instructions.md` (для GitHub Copilot)
- `.cursor/rules/project-overview.mdc` (для Cursor)
- `.windsurfrules` (для Windsurf)
- `.gemini/GEMINI.md` (для Antigravity)

**Пример:**
```
Please describe what your project is for? My awesome API
What database are you planning on using? MySQL
What HTML templating engine will you plan on using (if any)? latte
Is security an important element of this project? (y/n) y
...
AI instructions updated successfully.
```

Теперь ваши ИИ-инструменты будут давать более умные, релевантные предложения на основе реальных нужд вашего проекта.

## Расширенное использование

- Вы можете настроить расположение файлов учетных данных или инструкций с помощью опций команд (см. `--help` для каждой команды).
- ИИ-помощники предназначены для работы с любым провайдером LLM, поддерживающим API, совместимые с OpenAI.
- Если вы хотите обновить инструкции по мере эволюции вашего проекта, просто перезапустите `ai:generate-instructions` и ответьте на подсказки снова.

## См. также

- [Flight Skeleton](https://github.com/flightphp/skeleton) – Официальный стартер с интеграцией ИИ
- [Runway CLI](/awesome-plugins/runway) – Подробнее о CLI-инструменте, питающем эти команды

## Устранение неисправностей

- Если вы видите "Missing .runway-creds.json", сначала запустите `php runway ai:init`.
- Убедитесь, что ваш API-ключ действителен и имеет доступ к выбранной модели.
- Если инструкции не обновляются, проверьте разрешения на файлы в директории вашего проекта.

## Журнал изменений

- v3.16.0 – Добавлены CLI-команды `ai:init` и `ai:generate-instructions` для интеграции ИИ.