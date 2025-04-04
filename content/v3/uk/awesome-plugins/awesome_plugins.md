# Чудові плагіни

Flight надзвичайно розширювальний. Існує безліч плагінів, які можна використовувати для додавання функціональності до вашої програми Flight. Деякі з них офіційно підтримуються командою Flight, а інші є мікро/легкими бібліотеками, які допоможуть вам розпочати.

## Документація API

Документація API є критично важливою для будь-якого API. Вона допомагає розробникам зрозуміти, як взаємодіяти з вашим API і чого очікувати в зворотному напрямку. Існує кілька інструментів, які можуть допомогти вам створити документацію API для ваших проектів Flight.

- [FlightPHP OpenAPI Generator](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - Допис у блозі, написаний Даніелем Шрайбером про те, як використовувати OpenAPI Spec з FlightPHP для побудови вашого API з використанням підходу "API перш за все".
- [SwaggerUI](https://github.com/zircote/swagger-php) - Swagger UI — це чудовий інструмент, який допоможе вам створити документацію API для ваших проектів Flight. Його дуже просто використовувати, і його можна налаштувати відповідно до ваших потреб. Це бібліотека PHP для створення документації Swagger.

## Моніторинг продуктивності програми (APM)

Моніторинг продуктивності програми (APM) є критично важливим для будь-якої програми. Він допомагає вам зрозуміти, як працює ваша програма і де знаходяться вузькі місця. Існує кілька інструментів APM, які можна використовувати з Flight.
- <span class="badge bg-info">бета</span>[flightphp/apm](/awesome-plugins/apm) - Flight APM — це проста бібліотека APM, яка може використовуватися для моніторингу ваших програм Flight. Її можна використовувати для моніторингу продуктивності вашої програми та допомоги в ідентифікації вузьких місць.

## Аутентифікація/Авторизація

Аутентифікація та авторизація є критично важливими для будь-якої програми, яка вимагає контролю доступу до певних ресурсів.

- <span class="badge bg-primary">офіційний</span> [flightphp/permissions](/awesome-plugins/permissions) - Офіційна бібліотека прав доступу Flight. Ця бібліотека є простим способом додати рівні прав доступу для користувачів та програм до вашої програми.

## Кешування

Кешування є чудовим способом прискорення вашої програми. Існує кілька бібліотек кешування, які можна використовувати з Flight.

- <span class="badge bg-primary">офіційний</span> [flightphp/cache](/awesome-plugins/php-file-cache) - Легка, проста та автономна клас кешування PHP у файлі

## CLI

CLI-додатки є чудовим способом взаємодії з вашою програмою. Ви можете використовувати їх для генерації контролерів, відображення всіх маршрутів і багато іншого.

- <span class="badge bg-primary">офіційний</span> [flightphp/runway](/awesome-plugins/runway) - Runway — це CLI-додаток, який допомагає вам керувати вашими програмами Flight.

## Куки

Куки — це чудовий спосіб зберігати невелику кількість даних на стороні клієнта. Їх можна використовувати для зберігання уподобань користувача, налаштувань програми тощо.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie — це бібліотека PHP, яка надає простий та ефективний спосіб управління куками.

## Налагодження

Налагодження є критично важливим, коли ви розробляєте у своєму локальному середовищі. Існує кілька плагінів, які можуть покращити ваш досвід налагодження.

- [tracy/tracy](/awesome-plugins/tracy) - Це повнофункціональний обробник помилок, який можна використовувати з Flight. Він має кілька панелей, які можуть допомогти вам налагодити вашу програму. Його також дуже легко розширити та додати свої власні панелі.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Використовуючи [Tracy](/awesome-plugins/tracy) для обробки помилок, цей плагін додає кілька додаткових панелей, щоб допомогти з налагодженням спеціально для проектів Flight.

## Бази даних

Бази даних є основою більшості програм. Це те, як ви зберігаєте і отримуєте дані. Деякі бібліотеки баз даних є простими обгортками для написання запитів, а деякі є повноцінними ORM.

- <span class="badge bg-primary">офіційний</span> [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Офіційна обгортка Flight PDO, яка є частиною ядра. Це проста обгортка для спрощення процесу написання запитів та їх виконання. Це не ORM.
- <span class="badge bg-primary">офіційний</span> [flightphp/active-record](/awesome-plugins/active-record) - Офіційний ORM/Mapper Flight ActiveRecord. Чудова маленька бібліотека для легкого отримання і зберігання даних у вашій базі даних.
- [byjg/php-migration](/awesome-plugins/migrations) - Плагін для відстеження всіх змін бази даних для вашого проекту.

## Шифрування

Шифрування є критично важливим для будь-якої програми, яка зберігає чутливі дані. Шифрування та дешифрування даних не є особливо складним, але правильне зберігання ключа шифрування [може](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [бути](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [складно](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key). Найголовніше — ніколи не зберігайте свій ключ шифрування в публічному каталозі або не додавайте його до свого репозиторію коду.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - Це бібліотека, яку можна використовувати для шифрування та дешифрування даних. Розпочати досить просто, щоб розпочати шифрування та дешифрування даних.

## Робочий черга

Робочі черги дуже корисні для асинхронної обробки завдань. Це може бути надсилання електронних листів, обробка зображень або будь-що, що не потрібно виконувати в реальному часі.

- [n0nag0n/simple-job-queue](/awesome-plugins/simple-job-queue) - Simple Job Queue — це бібліотека, яку можна використовувати для асинхронної обробки завдань. Її можна використовувати з beanstalkd, MySQL/MariaDB, SQLite та PostgreSQL.

## Сесія

Сесії не зовсім корисні для API, але для побудови веб-додатку сесії можуть бути критично важливими для підтримки стану та інформації для входу.

- <span class="badge bg-primary">офіційний</span> [flightphp/session](/awesome-plugins/session) - Офіційна бібліотека сесій Flight. Це проста бібліотека сесій, яку можна використовувати для зберігання та отримання даних сесій. Вона використовує вбудовану обробку сесій PHP.
- [Ghostff/Session](/awesome-plugins/ghost-session) - Менеджер сесій PHP (неблокуючий, флеш, сегмент, шифрування сесій). Використовує PHP open_ssl для можливого шифрування/дешифрування даних сесій.

## Темплейтинг

Темплейтинг є основою будь-якого веб-додатку з інтерфейсом. Існує кілька механізмів темплейтингу, які можна використовувати з Flight.

- <span class="badge bg-warning">застарілий</span> [flightphp/core View](/learn#views) - Це дуже базовий механізм темплейтингу, який є частиною ядра. Його не рекомендується використовувати, якщо у вас є більше ніж кілька сторінок у вашому проекті.
- [latte/latte](/awesome-plugins/latte) - Latte — це повнофункціональний механізм темплейтингу, який дуже легко використовувати і відчувається ближче до синтаксису PHP, ніж Twig або Smarty. Також дуже легко розширити і додати свої фільтри та функції.

## Внесення внесків

Маєте плагін, яким хочете поділитись? Надішліть запит на злиття, щоб додати його до списку!