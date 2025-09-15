> _Este artículo se publicó originalmente en [Airpair](https://web.archive.org/web/20220204014708/https://www.airpair.com/php/posts/best-practices-for-modern-php-development#5-unit-testing) en 2015. Todo el crédito se otorga a Airpair y Brian Fenton, quien escribió originalmente este artículo, aunque el sitio web ya no está disponible y el artículo solo existe en la [Wayback Machine](https://web.archive.org/web/20220204014708/https://www.airpair.com/php/posts/best-practices-for-modern-php-development#5-unit-testing). Este artículo se ha agregado al sitio con fines educativos y de aprendizaje para la comunidad de PHP en general._

1 Configuración y configuración inicial
-------------------------

### 1.1 Mantenerse actualizado

Digamos esto desde el principio: un número deprimentemente pequeño de instalaciones de PHP en uso están actualizadas o se mantienen actualizadas. Ya sea debido a restricciones de alojamiento compartido, valores predeterminados que nadie piensa en cambiar o falta de tiempo/presupuesto para las pruebas de actualización, los humildes binarios de PHP tienden a quedarse atrás. Por lo tanto, una práctica recomendada clara que necesita más énfasis es siempre usar una versión actual de PHP (5.6.x en el momento de este artículo). Además, también es importante programar actualizaciones regulares tanto de PHP como de cualquier extensión o bibliotecas de proveedores que pueda estar usando. Las actualizaciones le brindan nuevas características del lenguaje, mayor velocidad, menor uso de memoria y actualizaciones de seguridad. Cuanto más frecuentemente actualice, menos doloroso se vuelve el proceso.

### 1.2 Establecer valores predeterminados sensatos

PHP hace un trabajo decente al establecer buenos valores predeterminados de la caja con sus archivos _php.ini.development_ y _php.ini.production_, pero podemos hacerlo mejor. Por un lado, no establecen una zona horaria para nosotros. Eso tiene sentido desde una perspectiva de distribución, pero sin una, PHP lanzará un error E_WARNING cada vez que llamemos a una función relacionada con fecha/hora. Aquí hay algunas configuraciones recomendadas:

*   date.timezone - elija de la [lista de zonas horarias compatibles](http://php.net/manual/en/timezones.php)
*   session.save_path - si estamos usando archivos para sesiones y no algún otro controlador de guardado, establezca esto en algo fuera de _/tmp_. Dejar esto como _/tmp_ puede ser riesgoso en un entorno de alojamiento compartido ya que _/tmp_ suele tener permisos amplios. Incluso con el bit sticky establecido, cualquiera con acceso para listar el contenido de este directorio puede aprender todos sus ID de sesión activos.
*   session.cookie_secure - obvio, active esto si está sirviendo su código PHP a través de HTTPS.
*   session.cookie_httponly - establezca esto para evitar que las cookies de sesión de PHP sean accesibles a través de JavaScript
*   Más... use una herramienta como [iniscan](https://github.com/psecio/iniscan) para probar su configuración en busca de vulnerabilidades comunes

### 1.3 Extensiones

También es una buena idea deshabilitar (o al menos no habilitar) extensiones que no usará, como controladores de bases de datos. Para ver qué está habilitado, ejecute el comando `phpinfo()` o vaya a la línea de comandos y ejecute esto.

```bash
$ php -i
``` 

La información es la misma, pero phpinfo() tiene formato HTML agregado. La versión de CLI es más fácil de canalizar a grep para encontrar información específica. Ej.

```bash
$ php -i | grep error_log
```

Una advertencia de este método: es posible tener configuraciones de PHP diferentes que se aplican a la versión orientada a la web y a la versión de CLI.

2 Use Composer
--------------

Esto puede sorprender, pero una de las mejores prácticas para escribir PHP moderno es escribir menos de él. Si bien es cierto que una de las mejores formas de mejorar en la programación es hacerlo, hay un gran número de problemas que ya se han resuelto en el espacio de PHP, como enrutamiento, bibliotecas de validación de entrada básica, conversión de unidades, capas de abstracción de bases de datos, etc... Solo vaya a [Packagist](https://www.packagist.org/) y explore. Probablemente descubrirá que porciones significativas del problema que está intentando resolver ya se han escrito y probado.

Si bien es tentador escribir todo el código usted mismo (y no hay nada malo en escribir su propio framework o biblioteca como una experiencia de aprendizaje), debe luchar contra esos sentimientos de "No Inventado Aquí" y ahorrarse mucho tiempo y dolor de cabeza. Siga la doctrina de PIE en su lugar: Orgullosamente Inventado En Otro Lugar. Además, si decide escribir su propio "lo que sea", no lo libere a menos que haga algo significativamente diferente o mejor que las ofertas existentes.

[Composer](https://www.getcomposer.org/) es un gestor de paquetes para PHP, similar a pip en Python, gem en Ruby y npm en Node. Le permite definir un archivo JSON que lista las dependencias de su código y tratará de resolver esos requisitos descargando e instalando los paquetes de código necesarios.

### 2.1 Instalación de Composer

Suponiendo que esto es un proyecto local, instalemos una instancia de Composer solo para el proyecto actual. Navegue a su directorio de proyecto y ejecute esto:
```bash
$ curl -sS https://getcomposer.org/installer | php
```

Tenga en cuenta que canalizar cualquier descarga directamente a un intérprete de scripts (sh, ruby, php, etc.) es un riesgo de seguridad, así que lea el código de instalación y asegúrese de estar cómodo con él antes de ejecutar cualquier comando como este.

Por conveniencia (si prefieres escribir `composer install` en lugar de `php composer.phar install`), puedes usar este comando para instalar una copia única de composer de forma global:

```bash
$ mv composer.phar /usr/local/bin/composer
$ chmod +x composer
```

Es posible que necesite ejecutar esos con `sudo` dependiendo de sus permisos de archivo.

### 2.2 Usar Composer

Composer tiene dos categorías principales de dependencias que puede gestionar: "require" y "require-dev". Las dependencias listadas como "require" se instalan en todas partes, pero las dependencias "require-dev" solo se instalan cuando se solicitan específicamente. Por lo general, estas son herramientas para cuando el código está en desarrollo activo, como [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer). La línea a continuación muestra un ejemplo de cómo instalar [Guzzle](http://docs.guzzlephp.org/en/latest/), una biblioteca HTTP popular.

```bash
$ php composer.phar require guzzle/guzzle
```

Para instalar una herramienta solo con fines de desarrollo, agregue la bandera `--dev`:

```bash
$ php composer.phar require --dev 'sebastian/phpcpd'
```

Esto instala [PHP Copy-Paste Detector](https://github.com/sebastianbergmann/phpcpd), otra herramienta de calidad de código como una dependencia solo para desarrollo.

### 2.3 Instalar vs actualizar

Cuando ejecutamos `composer install` por primera vez, instalará cualquier biblioteca y sus dependencias que necesitemos, basadas en el archivo _composer.json_. Una vez hecho eso, composer crea un archivo de bloqueo, predeciblemente llamado _composer.lock_. Este archivo contiene una lista de las dependencias que composer encontró para nosotros y sus versiones exactas, con hashes. Luego, cualquier futura vez que ejecutemos `composer install`, mirará en el archivo de bloqueo e instalará esas versiones exactas.

`composer update` es un poco diferente. Ignorará el archivo _composer.lock_ (si está presente) e intentará encontrar las versiones más actualizadas de cada una de las dependencias que aún satisfagan las restricciones en _composer.json_. Luego escribe un nuevo archivo _composer.lock_ cuando termine.

### 2.4 Autocarga

Tanto composer install como composer update generarán un [autocargador](https://getcomposer.org/doc/04-schema.md#autoload) para nosotros que le indica a PHP dónde encontrar todos los archivos necesarios para usar las bibliotecas que acabamos de instalar. Para usarlo, solo agregue esta línea (generalmente a un archivo de inicialización que se ejecute en cada solicitud):
```php
require 'vendor/autoload.php';
```

3 Siga buenos principios de diseño
-------------------------------

### 3.1 SOLID

SOLID es un mnemotécnico para recordarnos cinco principios clave en el buen diseño de software orientado a objetos.

#### 3.1.1 S - Principio de Responsabilidad Única

Esto establece que las clases solo deben tener una responsabilidad, o dicho de otra manera, solo deben tener una sola razón para cambiar. Esto encaja bien con la filosofía de Unix de muchas herramientas pequeñas, haciendo una cosa bien. Las clases que solo hacen una cosa son mucho más fáciles de probar y depurar, y son menos propensas a sorprenderte. No quieres que una llamada a un método de una clase Validator actualice registros de base de datos. Aquí hay un ejemplo de una violación de SRP, como la que comúnmente verías en una aplicación basada en el [patrón ActiveRecord](http://en.wikipedia.org/wiki/Active_record_pattern).

```php
class Person extends Model
{
    public $name;
    public $birthDate;
    protected $preferences;
    public function getPreferences() {}
    public function save() {}
}
```
    

Entonces esto es un modelo de [entidad](http://lostechies.com/jimmybogard/2008/05/21/entities-value-objects-aggregates-and-roots/) bastante básico. Sin embargo, una de estas cosas no pertenece aquí. La única responsabilidad de un modelo de entidad debería ser el comportamiento relacionado con la entidad que representa, no debería ser responsable de persistirse a sí mismo.

```php
class Person extends Model
{
    public $name;
    public $birthDate;
    protected $preferences;
    public function getPreferences() {}
}
class DataStore
{
    public function save(Model $model) {}
}
```

Esto es mejor. El modelo Person está de vuelta a hacer solo una cosa, y el comportamiento de guardado se ha movido a un objeto de persistencia en su lugar. Nota también que solo hice una sugerencia de tipo en Model, no en Person. Volveremos a eso cuando lleguemos a las partes L y D de SOLID.

#### 3.1.2 O - Principio Abierto-Cerrado

Hay una prueba increíble para esto que resume bastante bien qué es este principio: piensa en una característica para implementar, probablemente la más reciente en la que trabajaste o en la que estás trabajando. ¿Puedes implementar esa característica en tu base de código existente SOLO agregando nuevas clases y no cambiando ninguna clase existente en tu sistema? Tu código de configuración y cableado obtiene un poco de indulgencia, pero en la mayoría de los sistemas esto es sorprendentemente difícil. Tienes que depender mucho de la despacho polimórfica y la mayoría de las bases de código simplemente no están configuradas para eso. Si estás interesado en eso, hay una buena charla de Google en YouTube sobre [polimorfismo y escribir código sin Ifs](https://www.youtube.com/watch?v=4F72VULWFvc) que profundiza más. Como bono, la charla la da [Miško Hevery](http://misko.hevery.com/), a quien muchos pueden conocer como el creador de [AngularJs](https://angularjs.org/).

#### 3.1.3 L - Principio de Sustitución de Liskov

Este principio lleva el nombre de [Barbara Liskov](http://en.wikipedia.org/wiki/Barbara_Liskov) y se imprime a continuación:

> "Los objetos en un programa deberían ser reemplazables con instancias de sus subtipos sin alterar la corrección de ese programa."

Esto suena bien, pero se ilustra más claramente con un ejemplo.

```php
abstract class Shape
{
    public function getHeight();
    public function setHeight($height);
    public function getLength();
    public function setLength($length);
}
```   

Esto va a representar nuestra forma básica de cuatro lados. Nada fancy aquí.

```php
class Square extends Shape
{
    protected $size;
    public function getHeight() {
        return $this->size;
    }
    public function setHeight($height) {
        $this->size = $height;
    }
    public function getLength() {
        return $this->size;
    }
    public function setLength($length) {
        $this->size = $length;
    }
}
```

Aquí está nuestra primera forma, el Cuadrado. Forma bastante directa, ¿verdad? Puedes asumir que hay un constructor donde establecemos las dimensiones, pero ves aquí de esta implementación que la longitud y la altura siempre van a ser las mismas. Los cuadrados son así.

```php
class Rectangle extends Shape
{
    protected $height;
    protected $length;
    public function getHeight() {
        return $this->height;
    }
    public function setHeight($height) {
        $this->height = $height;
    }
    public function getLength() {
        return $this->length;
    }
    public function setLength($length) {
        $this->length = $length;
    }
}
```

Así que aquí tenemos una forma diferente. Todavía tiene las mismas firmas de métodos, todavía es una forma de cuatro lados, pero ¿qué pasa si empezamos a intentar usarlos en lugar de uno del otro? De repente, si cambiamos la altura de nuestra Shape, ya no podemos asumir que la longitud de nuestra forma coincida. Hemos violado el contrato que teníamos con el usuario cuando les dimos nuestra forma Square.

Este es un ejemplo de libro de texto de una violación del LSP y necesitamos este tipo de principio en su lugar para hacer el mejor uso de un sistema de tipos. Incluso [duck typing](http://en.wikipedia.org/wiki/Duck_typing) no nos dirá si el comportamiento subyacente es diferente, y como no podemos saberlo sin verlo romperse, es mejor asegurarse de que no lo sea en primer lugar.

#### 3.1.3 I - Principio de Segregación de Interfaces

Este principio dice favorecer muchas interfaces pequeñas y detalladas en lugar de una grande. Las interfaces deberían basarse en el comportamiento en lugar de "es una de estas clases". Piense en interfaces que vienen con PHP. Traversable, Countable, Serializable, cosas como esas. Anuncian capacidades que posee el objeto, no de qué hereda. Así que mantenga sus interfaces pequeñas. No quieres una interfaz con 30 métodos, 3 es un objetivo mucho mejor.

#### 3.1.4 D - Principio de Inversión de Dependencias

Probablemente hayas oído hablar de esto en otros lugares que hablaron de [Inyección de Dependencias](http://en.wikipedia.org/wiki/Dependency_injection), pero Inversión de Dependencias e Inyección de Dependencias no son exactamente lo mismo. La inversión de dependencias es realmente solo una forma de decir que deberías depender de abstracciones en tu sistema y no de sus detalles. ¿Qué significa eso para ti en el día a día?

> No uses directamente mysqli_query() en todo tu código, usa algo como DataStore->query() en su lugar.

El núcleo de este principio es en realidad sobre abstracciones. Se trata más de decir "usa un adaptador de base de datos" en lugar de depender de llamadas directas a cosas como mysqli_query. Si estás usando directamente mysqli_query en la mitad de tus clases, estás atando todo directamente a tu base de datos. Nada en contra de MySQL aquí, pero si estás usando mysqli_query, ese tipo de detalle de bajo nivel debería estar oculto en solo un lugar y luego esa funcionalidad debería exponerse a través de un contenedor genérico.

Ahora sé que este es un ejemplo un poco trillado si lo piensas, porque el número de veces que vas a cambiar completamente el motor de tu base de datos después de que tu producto esté en producción es muy, muy bajo. Lo elegí porque pensé que la gente estaría familiarizada con la idea de su propio código. Además, incluso si tienes una base de datos con la que te vas a quedar, ese objeto contenedor abstracto te permite arreglar errores, cambiar el comportamiento o implementar características que deseas que tuviera tu base de datos elegida. También hace posible las pruebas unitarias donde las llamadas de bajo nivel no lo harían.

4 Calistenia de objetos
---------------------

Esto no es un buceo completo en estos principios, pero los dos primeros son fáciles de recordar, proporcionan un buen valor y se pueden aplicar inmediatamente a casi cualquier base de código.

### 4.1 No más de un nivel de indentación por método

Esta es una forma útil de pensar en descomponer métodos en fragmentos más pequeños, dejándote con código que es más claro y autodocumentado. Cuantos más niveles de indentación tengas, más está haciendo el método y más estado tienes que rastrear en tu cabeza mientras trabajas con él.

Inmediatamente sé que la gente objetará esto, pero esto es solo una guía/heurística, no una regla estricta. No espero que nadie haga cumplir reglas de PHP_CodeSniffer para esto (aunque [la gente ha](https://github.com/object-calisthenics/phpcs-calisthenics-rules)).

Pasemos por una muestra rápida de cómo podría verse esto:

```php
public function transformToCsv($data)
{
    $csvLines = array();
    $csvLines[] = implode(',', array_keys($data[0]));
    foreach ($data as $row) {
        if (!$row) {
            continue;
        }
        $csvLines[] = implode(',', $row);
    }
    return $csvLines;
}
```

Si bien este no es un código terrible (es técnicamente correcto, probables, etc.), podemos hacer mucho más para aclararlo. ¿Cómo reduciríamos los niveles de anidamiento aquí?

Sabemos que necesitamos simplificar enormemente el contenido del bucle foreach (o eliminarlo por completo), así que empecemos allí.

```php
if (!$row) {
    continue;
}
```   

Esta primera parte es fácil. Todo lo que está haciendo es ignorar filas vacías. Podemos acortar todo este proceso usando una función incorporada de PHP antes de llegar siquiera al bucle.

```php
$data = array_filter($data);
foreach ($data as $row) {
    $csvLines[] = implode(',', $row);
}
```

Ahora tenemos nuestro único nivel de anidamiento. Pero mirando esto, todo lo que estamos haciendo es aplicar una función a cada elemento de un arreglo. Ni siquiera necesitamos el bucle foreach para eso.

```php
$data = array_filter($data);
$csvLines = array_map(function($row) {
    return implode(',', $row);
}, $data);
```

Ahora no tenemos anidamiento en absoluto, y el código probablemente será más rápido ya que estamos haciendo todo el bucle con funciones nativas en C en lugar de PHP. Tenemos que participar en un poco de truco para pasar la coma a `implode`, así que podrías argumentar que detenerte en el paso anterior es mucho más comprensible.

### 4.2 Intenta no usar `else`

Esto realmente trata con dos ideas principales. La primera es múltiples declaraciones de retorno de un método. Si tienes suficiente información para tomar una decisión sobre el resultado del método, adelante, toma esa decisión y retorna. La segunda es una idea conocida como [Cláusulas de Guardia](http://c2.com/cgi/wiki?GuardClause). Estas son básicamente verificaciones de validación combinadas con retornos tempranos, generalmente cerca de la parte superior de un método. Déjame mostrarte lo que quiero decir.

```php
public function addThreeInts($first, $second, $third) {
    if (is_int($first)) {
        if (is_int($second)) {
            if (is_int($third)) {
                $sum = $first + $second + $third;
            } else {
                return null;
            }
        } else {
            return null;
        }
    } else {
        return null;
    }
    return $sum;
}
```

Entonces esto es bastante directo, suma 3 enteros y devuelve el resultado, o `null` si cualquiera de los parámetros no es un entero. Ignorando el hecho de que podríamos combinar todas esas verificaciones en una sola línea con operadores AND, creo que puedes ver cómo la estructura if/else anidada hace que el código sea más difícil de seguir. Ahora mira este ejemplo en su lugar.

```php
public function addThreeInts($first, $second, $third) {
    if (!is_int($first)) {
        return null;
    }
    if (!is_int($second)) {
        return null;
    }
    if (!is_int($third)) {
        return null;
    }
    return $first + $second + $third;
}
```   

Para mí, este ejemplo es mucho más fácil de seguir. Aquí estamos usando cláusulas de guardia para verificar nuestras aserciones iniciales sobre los parámetros que estamos pasando e inmediatamente saliendo del método si no pasan. También ya no tenemos la variable intermedia para rastrear la suma a lo largo del método. En este caso hemos verificado que ya estamos en el camino feliz y podemos simplemente hacer lo que vinimos a hacer. Nuevamente podríamos hacer todas esas verificaciones en un `if` solo, pero el principio debería estar claro.

5 Pruebas unitarias
--------------

Las pruebas unitarias son la práctica de escribir pruebas pequeñas que verifican el comportamiento en tu código. Casi siempre se escriben en el mismo lenguaje que el código (en este caso PHP) y están destinadas a ser lo suficientemente rápidas como para ejecutarse en cualquier momento. Son extremadamente valiosas como una herramienta para mejorar tu código. Además de los beneficios obvios de asegurar que tu código esté haciendo lo que crees que está haciendo, las pruebas unitarias pueden proporcionar retroalimentación de diseño muy útil también. Si un pedazo de código es difícil de probar, a menudo muestra problemas de diseño. También te dan una red de seguridad contra regresiones, y eso te permite refactorizar mucho más a menudo y evolucionar tu código a un diseño más limpio.

### 5.1 Herramientas

Hay varias herramientas de pruebas unitarias allí en PHP, pero con diferencia la más común es [PHPUnit](https://phpunit.de/). Puedes instalarla descargando un [PHAR](http://php.net/manual/en/intro.phar.php) [directamente](https://phar.phpunit.de/phpunit.phar), o instalarla con composer. Dado que estamos usando composer para todo lo demás, mostraremos ese método. Además, como PHPUnit probablemente no se desplegará en producción, podemos instalarlo como una dependencia de desarrollo con el siguiente comando:

```bash
composer require --dev phpunit/phpunit
```

### 5.2 Las pruebas son una especificación

El rol más importante de las pruebas unitarias en tu código es proporcionar una especificación ejecutable de lo que se supone que debe hacer el código. Incluso si el código de prueba está equivocado o el código tiene errores, el conocimiento de lo que el sistema se _supone_ que debe hacer es invaluable.

### 5.3 Escribe tus pruebas primero

Si has tenido la oportunidad de ver un conjunto de pruebas escritas antes del código y uno escrito después de que el código se terminó, son notablemente diferentes. Las pruebas "después" están mucho más preocupadas por los detalles de implementación de la clase y asegurándose de que tengan una buena cobertura de líneas, mientras que las pruebas "antes" se centran más en verificar el comportamiento externo deseado. Eso es realmente lo que nos importa con las pruebas unitarias de todos modos, es asegurarnos de que la clase exhiba el comportamiento correcto. Las pruebas enfocadas en la implementación realmente hacen que el refactoring sea más difícil porque se rompen si los internos de las clases cambian, y acabas de costarte los beneficios de ocultación de información de OOP.

### 5.4 Qué hace una buena prueba unitaria

Las buenas pruebas unitarias comparten muchas de las siguientes características:

*   Rápidas - deberían ejecutarse en milisegundos.
*   Sin acceso a la red - deberían poder apagar el inalámbrico/desconectar y todas las pruebas aún pasar.
*   Acceso limitado al sistema de archivos - esto agrega velocidad y flexibilidad si se despliega código a otros entornos.
*   Sin acceso a la base de datos - evita actividades costosas de configuración y desmontaje.
*   Prueba solo una cosa a la vez - una prueba unitaria debería tener solo una razón para fallar.
*   Bien nombradas - véase 5.2 arriba.
*   Mayormente objetos falsos - los únicos "reales" objetos en pruebas unitarias deberían ser el objeto que estamos probando y objetos de valor simples. El resto debería ser alguna forma de [doble de prueba](https://phpunit.de/manual/current/en/test-doubles.html)

Hay razones para ir en contra de algunas de estas, pero como guías generales te servirán bien.

### 5.5 Cuando las pruebas son dolorosas

> Las pruebas unitarias te obligan a sentir el dolor del mal diseño desde el principio - Michael Feathers

Cuando escribes pruebas unitarias, te obligas a usar realmente la clase para lograr cosas. Si escribes pruebas al final, o peor aún, solo arrojas el código sobre la pared para QA o quien sea para escribir pruebas, no obtienes retroalimentación sobre cómo se comporta realmente la clase. Si estamos escribiendo pruebas y la clase es un dolor real de usar, lo descubriremos mientras la escribimos, que es casi el momento más barato para arreglarlo.

Si una clase es difícil de probar, es un defecto de diseño. Diferentes defectos se manifiestan de diferentes maneras. Si tienes que hacer un montón de burlas, tu clase probablemente tiene demasiadas dependencias o tus métodos están haciendo demasiado. Cuanto más configuración tengas que hacer para cada prueba, más probable es que tus métodos estén haciendo demasiado. Si tienes que escribir escenarios de prueba realmente enredados para ejercer el comportamiento, los métodos de la clase probablemente están haciendo demasiado. Si tienes que cavar dentro de un montón de métodos privados y estado para probar cosas, quizás haya otra clase tratando de salir. Las pruebas unitarias son muy buenas para exponer "clases iceberg" donde el 80% de lo que hace la clase está oculto en código protegido o privado. Solía ser un gran fan de hacer lo más posible protegido, pero ahora me di cuenta de que solo estaba haciendo que mis clases individuales fueran responsables de demasiado, y la solución real era dividir la clase en piezas más pequeñas.

> **Escrito por Brian Fenton** - Brian Fenton ha sido un desarrollador de PHP durante 8 años en el Medio Oeste y el Área de la Bahía, actualmente en Thismoment. Se enfoca en la artesanía del código y los principios de diseño. Blog en www.brianfenton.us, Twitter en @brianfenton. Cuando no está ocupado siendo padre, disfruta de la comida, la cerveza, los juegos y el aprendizaje.