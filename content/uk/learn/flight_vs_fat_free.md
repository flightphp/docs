# Flight vs Fat-Free

## Що таке Fat-Free?
[Fat-Free](https://fatfreeframework.com) (ласкаво відомий як **F3**) є потужним, але простим у використанні мікрофреймворком PHP, призначеним для допомоги у створенні динамічних і надійних веб-додатків - швидко!

Flight порівнюється з Fat-Free в багатьох аспектах і, мабуть, є найближчим родичем у термінах функціональності та простоти. Fat-Free має багато функцій, яких немає у Flight, але також має і багато функцій, які є у Flight. Fat-Free починає демонструвати свою старість і вже не є так популярним, як раніше.

Оновлення стають менш частими, а спільнота не така активна. Код досить простий, але іноді відсутність дисципліни в синтаксисі може ускладнити читання та розуміння. Він працює з PHP 8.3, але сам код все ще виглядає так, ніби живе у PHP 5.3.

## Плюси у порівнянні з Flight

- Fat-Free має кілька більше зірок на GitHub, ніж Flight.
- Fat-Free має деяку пристойну документацію, але в деяких областях їй не вистачає ясності.
- Fat-Free має деякі рідкісні ресурси, такі як уроки на YouTube та онлайн-статті, які можна використовувати для вивчення фреймворку.
- Fat-Free має [декілька корисних плагінів](https://fatfreeframework.com/3.8/api-reference), що вбудовані, які іноді можуть бути корисними.
- Fat-Free має вбудований ORM під назвою Mapper, який можна використовувати для взаємодії з вашою базою даних. Flight має [active-record](/awesome-plugins/active-record).
- Fat-Free має вбудовані Сесії, кешування та локалізацію. Flight вимагає використовувати сторонні бібліотеки, але це охоплено у [документації](/awesome-plugins).
- Fat-Free має невелику групу [плагінів, створених спільнотою](https://fatfreeframework.com/3.8/development#Community), які можна використовувати для розширення фреймворку. Flight має деякі з них, описані на [сторінках документації](/awesome-plugins) та [прикладів](/examples).
- Fat-Free, як і Flight, не має залежностей.
- Fat-Free, як і Flight, спрямований на надання розробнику контролю над своїм додатком та простого досвіду розробника.
- Fat-Free підтримує зворотну сумісність, подібно до Flight (частково тому, що оновлення стають [менш частими](https://github.com/bcosca/fatfree/releases)).
- Fat-Free, як і Flight, призначений для розробників, які вперше потрапляють у світ фреймворків.
- Fat-Free має вбудований шаблонний движок, який є більш потужним, ніж шаблонний движок Flight. Flight рекомендує [Latte](/awesome-plugins/latte) для досягнення цієї мети.
- Fat-Free має унікальну команду типу CLI "route", де ви можете створювати CLI додатки безпосередньо у Fat-Free і обробляти це, як звичайний запит `GET`. Flight досягає цього за допомогою [runway](/awesome-plugins/runway).

## Мінуси у порівнянні з Flight

- Fat-Free має певні тести реалізації і навіть має свій власний [тест](https://fatfreeframework.com/3.8/test) клас, який є дуже базовим. Проте,
  він не 100% модульно протестований, як Flight.
- Вам доведеться використовувати пошукову систему, таку як Google, щоб фактично знайти сайт документації.
- Flight має темний режим на своєму сайті документації. (падіння мікрофона)
- Fat-Free має кілька модулів, які, на жаль, не підтримуються.
- Flight має простий [PdoWrapper](/awesome-plugins/pdo-wrapper), який є трохи простішим, ніж вбудований `DB\SQL` клас Fat-Free.
- Flight має [плагін для управління правами доступу](/awesome-plugins/permissions), який можна використовувати для забезпечення безпеки вашого додатку. Slim вимагає використовувати 
  сторонню бібліотеку.
- Flight має ORM під назвою [active-record](/awesome-plugins/active-record), який виглядає більше як ORM, ніж Mapper Fat-Free.
  Додаткова перевага `active-record` полягає в тому, що ви можете визначати відносини між записами для автоматичних злиттів, тоді як Mapper Fat-Free
  вимагає від вас створення [SQL.views](https://fatfreeframework.com/3.8/databases#ProsandCons).
- Дивіться на це, Fat-Free не має кореневого простору назв. Flight має простір назв у всьому коді, щоб не стикатися з вашим власним кодом.
  клас `Cache` є найбільшим порушником у цьому.
- Fat-Free не має проміжного програмного забезпечення. Замість цього є хуки `beforeroute` і `afterroute`, які можна використовувати для фільтрації запитів і відповідей у контролерах.
- Fat-Free не може групувати маршрути.
- Fat-Free має обробник контейнера ін'єкції залежностей, але документація надзвичайно обмежена з приводу того, як його використовувати.
- Налагодження може бути трохи складним, оскільки в основному все зберігається в тому, що називається [`HIVE`](https://fatfreeframework.com/3.8/quick-reference)