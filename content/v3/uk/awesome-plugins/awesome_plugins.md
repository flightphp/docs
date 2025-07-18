# Чудові Плагіни

Flight є надзвичайно гнучким. Існує низка плагінів, які можна використовувати для додавання функціональності до вашого додатку Flight. Деякі з них офіційно підтримуються командою Flight, а інші є мікро/лайт бібліотеками, щоб допомогти вам розпочати.

## Документація API

Документація API є критично важливою для будь-якого API. Вона допомагає розробникам зрозуміти, як взаємодіяти з вашим API та чого очікувати натомість. Існує кілька інструментів, доступних для генерації документації API для ваших проєктів Flight.

- [FlightPHP OpenAPI Generator](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - Блог-пост, написаний Деніелом Шрайбером, про те, як використовувати специфікацію OpenAPI з FlightPHP для побудови вашого API з використанням підходу "API спочатку".
- [SwaggerUI](https://github.com/zircote/swagger-php) - Swagger UI є чудовим інструментом для генерації документації API для ваших проєктів Flight. Він дуже простий у використанні та може бути налаштований відповідно до ваших потреб. Це PHP-бібліотека, яка допомагає генерувати документацію Swagger.

## Моніторинг Продуктивності Застосунків (APM)

Моніторинг продуктивності застосунків (APM) є критично важливим для будь-якого застосунку. Він допомагає вам зрозуміти, як працює ваш застосунок і де є вузькі місця. Існує низка інструментів APM, які можна використовувати з Flight.
- <span class="badge bg-info">beta</span>[flightphp/apm](/awesome-plugins/apm) - Flight APM є простою бібліотекою APM, яку можна використовувати для моніторингу ваших застосунків Flight. Вона може бути використана для моніторингу продуктивності вашого застосунку та допомоги в ідентифікації вузьких місць.

## Аутентифікація/Авторизація

Аутентифікація та авторизація є критично важливими для будь-якого застосунку, який вимагає контролю за тим, хто може доступатися до чого.

- <span class="badge bg-primary">official</span> [flightphp/permissions](/awesome-plugins/permissions) - Офіційна бібліотека Flight Permissions. Ця бібліотека є простим способом додавання прав користувача та рівня застосунку до вашого застосунку. 

## Кешування

Кешування є чудовим способом прискорення вашого застосунку. Існує низка бібліотек кешування, які можна використовувати з Flight.

- <span class="badge bg-primary">official</span> [flightphp/cache](/awesome-plugins/php-file-cache) - Легка, проста та автономна PHP-класа кешування у файлі

## CLI

CLI-застосунки є чудовим способом взаємодії з вашим застосунком. Ви можете використовувати їх для генерації контролерів, відображення всіх маршрутів та іншого.

- <span class="badge bg-primary">official</span> [flightphp/runway](/awesome-plugins/runway) - Runway є CLI-застосунком, який допомагає керувати вашими застосунками Flight.

## Кукі

Кукі є чудовим способом зберігання невеликих фрагментів даних на стороні клієнта. Їх можна використовувати для зберігання переваг користувача, налаштувань застосунку та іншого.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie є PHP-бібліотекою, яка надає простий та ефективний спосіб керування кукі.

## Відлагодження

Відлагодження є критично важливим, коли ви розробляєте у локальному середовищі. Існує кілька плагінів, які можуть покращити ваш досвід відлагодження.

- [tracy/tracy](/awesome-plugins/tracy) - Це повнофункціональний обробник помилок, який можна використовувати з Flight. Він має низку панелей, які можуть допомогти вам відлагодити ваш застосунок. Він також дуже простий у розширенні та додаванні власних панелей.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Використовується з обробником помилок [Tracy](/awesome-plugins/tracy), цей плагін додає кілька додаткових панелей для допомоги в відлагодженні спеціально для проєктів Flight.

## Бази Даних

Бази даних є основою для більшості застосунків. Це спосіб зберігання та вилучення даних. Деякі бібліотеки баз даних є просто обгортками для написання запитів, а деякі є повноцінними ORM.

- <span class="badge bg-primary">official</span> [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Офіційна обгортка Flight PDO, яка є частиною ядра. Це проста обгортка для спрощення процесу написання запитів та їх виконання. Це не ORM.
- <span class="badge bg-primary">official</span> [flightphp/active-record](/awesome-plugins/active-record) - Офіційний ORM/Mapper Flight ActiveRecord. Чудова маленька бібліотека для легкого вилучення та зберігання даних у вашій базі даних.
- [byjg/php-migration](/awesome-plugins/migrations) - Плагін для відстеження всіх змін бази даних для вашого проєкту.

## Шифрування

Шифрування є критично важливим для будь-якого застосунку, який зберігає чутливі дані. Шифрування та дешифрування даних не є надто складним, але правильне зберігання ключа шифрування [може](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [бути](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [складним](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key). Найважливіше - ніколи не зберігайте свій ключ шифрування у публічному каталозі або не коміть його до вашого репозиторію коду.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - Це бібліотека, яку можна використовувати для шифрування та дешифрування даних. Почати роботу досить просто, щоб розпочати шифрування та дешифрування даних.

## Черга Завдань

Черги завдань є дуже корисними для асинхронної обробки задач. Це може бути відправлення електронних листів, обробка зображень або будь-що, що не потрібно робити в реальному часі.

- [n0nag0n/simple-job-queue](/awesome-plugins/simple-job-queue) - Simple Job Queue є бібліотекою, яку можна використовувати для обробки завдань асинхронно. Вона може бути використана з beanstalkd, MySQL/MariaDB, SQLite та PostgreSQL.

## Сесія

Сесії не є особливо корисними для API, але для побудови веб-застосунку сесії можуть бути критично важливими для підтримки стану та інформації про вхід.

- <span class="badge bg-primary">official</span> [flightphp/session](/awesome-plugins/session) - Офіційна бібліотека Flight Session. Це проста бібліотека сесій, яку можна використовувати для зберігання та вилучення даних сесій. Вона використовує вбудовану обробку сесій PHP.
- [Ghostff/Session](/awesome-plugins/ghost-session) - Менеджер сесій PHP (неблокувальний, флеш, сегмент, шифрування сесій). Використовує PHP open_ssl для необов'язкового шифрування/дешифрування даних сесій.

## Шаблонізація

Шаблонізація є основою для будь-якого веб-застосунку з інтерфейсом. Існує низка двигунів шаблонізації, які можна використовувати з Flight.

- <span class="badge bg-warning">deprecated</span> [flightphp/core View](/learn#views) - Це дуже базовий двигун шаблонізації, який є частиною ядра. Не рекомендується використовувати, якщо у вашому проєкті більше ніж кілька сторінок.
- [latte/latte](/awesome-plugins/latte) - Latte є повнофункціональним двигуном шаблонізації, який дуже простий у використанні та відчувається ближчим до синтаксису PHP, ніж Twig або Smarty. Він також дуже простий у розширенні та додаванні власних фільтрів та функцій.

## Інтеграція з WordPress

Хочете використовувати Flight у своєму проєкті WordPress? Є зручний плагін для цього!

- [n0nag0n/wordpress-integration-for-flight-framework](/awesome-plugins/n0nag0n_wordpress) - Цей плагін WordPress дозволяє запускати Flight поруч з WordPress. Він ідеальний для додавання власних API, мікросервісів або навіть повноцінних застосунків до вашого сайту WordPress за допомогою фреймворку Flight. Дуже корисний, якщо ви хочете найкраще з обох світів!

## Співпраця

Маєте плагін, яким хочете поділитися? Подайте запит на злиття, щоб додати його до списку!