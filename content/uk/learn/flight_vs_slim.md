# Flight vs Slim

## Що таке Slim?
[Slim](https://slimframework.com) - це мікро-фреймворк PHP, який допомагає вам швидко писати прості, але потужні веб-додатки та API.

Багато з натхнення для деяких з функцій v3 Flight насправді прийшло від Slim. Групування маршрутів і виконання проміжного програмного забезпечення в 
конкретному порядку - це дві функції, які були натхнені Slim. Slim v3 з'явився, спрямований на простоту, але були 
[змішані відгуки](https://github.com/slimphp/Slim/issues/2770) щодо v4.

## Переваги у порівнянні з Flight

- У Slim є більша спільнота розробників, які в свою чергу створюють зручні модулі, щоб допомогти вам не винаходити велосипед заново.
- Slim дотримується багатьох інтерфейсів і стандартів, які є поширеними у спільноті PHP, що підвищує взаємозв'язок.
- Slim має прийнятну документацію та навчальні матеріали, які можна використовувати для вивчення фреймворку (навіть не порівняти з Laravel або Symfony).
- Slim має різноманітні ресурси, такі як навчальні відео на YouTube та онлайн-статті, які можна використовувати для вивчення фреймворку.
- Slim дозволяє вам використовувати будь-які компоненти, які ви хочете, для обробки основних функцій маршрутизації, оскільки він відповідає стандарту PSR-7.

## Недоліки у порівнянні з Flight

- Дивно, але Slim не такий швидкий, як ви могли б подумати для мікро-фреймворку. Дивіться 
  [TechEmpower benchmarks](https://www.techempower.com/benchmarks/#hw=ph&test=fortune&section=data-r22&l=zik073-cn3) 
  для отримання додаткової інформації.
- Flight орієнтований на розробника, який хоче створити легкий, швидкий і простий у використанні веб-додаток.
- У Flight немає залежностей, тоді як [Slim має кілька залежностей](https://github.com/slimphp/Slim/blob/4.x/composer.json), які ви повинні встановити.
- Flight орієнтований на простоту та зручність використання.
- Одна з основних функцій Flight полягає в тому, що він робить все можливе, щоб зберегти зворотну сумісність. Зміна з Slim v3 до v4 була руйнівною зміною.
- Flight призначений для розробників, які вперше пробують себе у світі фреймворків.
- Flight також може реалізовувати додатки на корпоративному рівні, але в нього не так багато прикладів і навчальних матеріалів, як у Slim.
  Це також вимагатиме більшу дисципліну з боку розробника, щоб зберігати все організованим і добре структурованим.
- Flight дає розробнику більше контролю над додатком, тоді як Slim може ввести в дію деяку магію за лаштунками.
- Flight має простий [PdoWrapper](/awesome-plugins/pdo-wrapper), який можна використовувати для взаємодії з вашою базою даних. Slim вимагає використовувати 
  сторонню бібліотеку.
- Flight має [плагін для управління доступом](/awesome-plugins/permissions), який можна використовувати для захисту вашого додатка. Slim вимагає використовувати 
  сторонню бібліотеку.
- Flight має ORM під назвою [active-record](/awesome-plugins/active-record), який можна використовувати для взаємодії з вашою базою даних. Slim вимагає використовувати 
  сторонню бібліотеку.
- Flight має CLI-додаток під назвою [runway](/awesome-plugins/runway), який можна використовувати для запуску вашого додатка з командного рядка. У Slim цього немає.