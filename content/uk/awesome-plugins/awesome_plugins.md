# Чудові плагіни

Flight надзвичайно розширюваний. Існує кілька плагінів, які можна використовувати для додавання функціональності у вашій поетапній аплікації. Деякі з них офіційно підтримуються командою Flight, а інші є мікро/легкими бібліотеками, щоб допомогти вам почати.

## Документація API

Документація API є критично важливою для будь-якого API. Вона допомагає розробникам розуміти, як взаємодіяти з вашим API та чого очікувати у відповідь. Є кілька інструментів, доступних для допомоги у створенні документації API для ваших проектів Flight.

- [FlightPHP OpenAPI Generator](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - Пост у блозі, написаний Даніелем Шрайбером про те, як використовувати OpenAPI Generator з FlightPHP для створення документації API.
- [Swagger UI](https://github.com/zircote/swagger-php) - Swagger UI - це чудовий інструмент, щоб допомогти вам згенерувати документацію API для ваших проектів Flight. Його дуже легко використовувати, і його можна налаштувати під ваші потреби. Це бібліотека PHP, яка допомагає вам генерувати документацію Swagger.

## Аутентифікація/Авторизація

Аутентифікація та авторизація є критично важливими для будь-якої програми, яка вимагає управлінських контролів щодо того, хто може отримати доступ до яких ресурсів.

- [flightphp/permissions](/awesome-plugins/permissions) - Офіційна бібліотека дозволів Flight. Ця бібліотека є простим способом додати дозволи на рівні користувача та аплікації до вашої програми.

## Кешування

Кешування є чудовим способом прискорити вашу аплікацію. Існує кілька бібліотек кешування, які можна використовувати з Flight.

- [flightphp/cache](/awesome-plugins/php-file-cache) - Легка, проста та незалежна клас кешування PHP у файлі

## CLI

CLI аплікації є чудовим способом взаємодії з вашою аплікацією. Ви можете використовувати їх для створення контролерів, відображення всіх маршрутів тощо.

- [flightphp/runway](/awesome-plugins/runway) - Runway - це CLI програма, яка допомагає вам керувати вашими аплікаціями Flight.

## Файли cookie

Файли cookie є чудовим способом зберігати маленькі обсяги даних на стороні клієнта. Вони можуть використовуватися для зберігання налаштувань користувача, налаштувань аплікації тощо.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie - це бібліотека PHP, яка надає простий і ефективний спосіб керувати файлами cookie.

## Налагодження

Налагодження є критично важливим, коли ви розробляєте у своєму локальному середовищі. Є кілька плагінів, які можуть підвищити ваш досвід налагодження.

- [tracy/tracy](/awesome-plugins/tracy) - Це повнофункціональний обробник помилок, який можна використовувати з Flight. Він має кілька панелей, які можуть допомогти вам у налагодженні вашої аплікації. Також його дуже просто розширити та додати свої панелі.
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Використовується з [Tracy](/awesome-plugins/tracy) обробником помилок, цей плагін додає кілька додаткових панелей, щоб допомогти з налагодженням спеціально для проектів Flight.

## Бази даних

Бази даних є основою більшості аплікацій. Ось як ви зберігаєте та отримуєте дані. Деякі бібліотеки бази даних є просто обгортками для написання запитів, а деякі є повноцінними ORM.

- [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Офіційна обгортка Flight PDO, яка є частиною ядра. Це проста обгортка, щоб спростити процес написання запитів і їх виконання. Це не ORM.
- [flightphp/active-record](/awesome-plugins/active-record) - Офіційний Flight ActiveRecord ORM/Mapper. Чудова маленька бібліотека для легкого отримання та зберігання даних у вашій базі даних.
- [byjg/php-migration](/awesome-plugins/migrations) - Плагін для відстеження всіх змін у базі даних вашого проекту.

## Шифрування

Шифрування є критично важливим для будь-якої програми, яка зберігає чутливі дані. Шифрування та дешифрування даних не є надто складним, але правильне зберігання ключа шифрування [може](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [бути](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [складним](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key). Найважливіше - ніколи не зберігати ключ шифрування у публічному каталозі або комітити його у ваш репозиторій коду.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - Це бібліотека, яка може бути використана для шифрування та дешифрування даних. Розпочати роботу досить просто, щоб почати шифрувати та дешифрувати дані.

## Сесія

Сесії насправді не є корисними для API, але для побудови веб-додатку сесії можуть бути критично важливими для підтримки стану та інформації про вхід.

- [Ghostff/Session](/awesome-plugins/session) - PHP Менеджер сесій (неблокуючий, спалах, сегмент, шифрування сесії). Використовує PHP open_ssl для необов'язкового шифрування/дешифрування даних сесій.

## Шаблонізація

Шаблонізація є основою будь-якого веб-додатку з інтерфейсом. Існує кілька движків шаблонізації, які можна використовувати з Flight.

- [flightphp/core View](/learn#views) - Це дуже базовий движок шаблонізації, який є частиною ядра. Не рекомендується використовувати, якщо у вас більше, ніж кілька сторінок у проекті.
- [latte/latte](/awesome-plugins/latte) - Latte - це повнофункціональний движок шаблонізації, який дуже легко використовувати і відчуває себе ближче до синтаксису PHP, ніж Twig або Smarty. Його також дуже легко розширити і додати свої фільтри та функції.

## Внесок

Є плагін, яким ви хочете поділитися? Надсилайте запит на приєднання, щоб додати його до списку!