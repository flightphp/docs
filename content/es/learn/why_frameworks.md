# ¿Por qué un Framework?

Algunos programadores se oponen vehementemente a usar frameworks. Argumentan que los frameworks son hinchados, lentos y difíciles de aprender. Dicen que los frameworks son innecesarios y que puedes escribir un código mejor sin ellos. Ciertamente hay algunos puntos válidos que se pueden hacer sobre las desventajas de usar frameworks. Sin embargo, también hay muchas ventajas al usar frameworks.

## Razones para Usar un Framework

Aquí hay algunas razones por las que podrías querer considerar usar un framework:

- **Desarrollo Rápido**: Los frameworks proporcionan mucha funcionalidad listo para usar. Esto significa que puedes construir aplicaciones web más rápidamente. No tienes que escribir tanto código porque el framework proporciona gran parte de la funcionalidad que necesitas.
- **Consistencia**: Los frameworks ofrecen una forma consistente de hacer las cosas. Esto facilita que comprendas cómo funciona el código y facilita que otros desarrolladores entiendan tu código. Si lo tienes guion por guion, podrías perder la consistencia entre los guiones, especialmente si estás trabajando con un equipo de programadores.
- **Seguridad**: Los frameworks ofrecen características de seguridad que ayudan a proteger tus aplicaciones web de amenazas comunes de seguridad. Esto significa que no tienes que preocuparte tanto por la seguridad porque el framework se encarga de gran parte de ella por ti.
- **Comunidad**: Los frameworks tienen grandes comunidades de desarrolladores que contribuyen al framework. Esto significa que puedes obtener ayuda de otros desarrolladores cuando tengas preguntas o problemas. También significa que hay muchos recursos disponibles para ayudarte a aprender a usar el framework.
- **Mejores Prácticas**: Los frameworks están construidos utilizando las mejores prácticas. Esto significa que puedes aprender del framework y utilizar las mismas mejores prácticas en tu propio código. Esto puede ayudarte a convertirte en un mejor programador. A veces no sabes lo que no sabes y eso puede perjudicarte al final.
- **Extensibilidad**: Los frameworks están diseñados para ser extendidos. Esto significa que puedes añadir tu propia funcionalidad al framework. Esto te permite construir aplicaciones web que se adaptan a tus necesidades específicas.

Flight es un micro-framework. Esto significa que es pequeño y ligero. No proporciona tanta funcionalidad como frameworks más grandes como Laravel o Symfony. Sin embargo, sí ofrece gran parte de la funcionalidad que necesitas para construir aplicaciones web. También es fácil de aprender y usar. Esto lo convierte en una buena elección para construir aplicaciones web de manera rápida y sencilla. Si eres nuevo en los frameworks, Flight es un buen framework para principiantes para empezar. Te ayudará a aprender sobre las ventajas de usar frameworks sin abrumarte con demasiada complejidad. Después de tener algo de experiencia con Flight, será más fácil pasar a frameworks más complejos como Laravel o Symfony, sin embargo, Flight todavía puede crear una aplicación robusta y exitosa.

## ¿Qué es el Enrutamiento?

El enrutamiento es el núcleo del framework Flight, ¿pero qué es exactamente? El enrutamiento es el proceso de tomar una URL y hacer coincidir con una función específica en tu código. Así es como puedes hacer que tu sitio web haga diferentes cosas según la URL que se solicite. Por ejemplo, es posible que desees mostrar el perfil de un usuario cuando visitan `/usuario/1234`, pero mostrar una lista de todos los usuarios cuando visitan `/usuarios`. Todo esto se hace a través del enrutamiento.

Podría funcionar algo así:

- Un usuario va a su navegador y escribe `http://ejemplo.com/usuario/1234`.
- El servidor recibe la solicitud y mira la URL y la pasa a tu código de aplicación de Flight.
- Digamos que en tu código de Flight tienes algo como `Flight::route('/usuario/@id', [ 'ControladorDeUsuario', 'verPerfilUsuario' ]);`. Tu código de aplicación de Flight mira la URL y ve que coincide con una ruta que has definido, y luego ejecuta el código que has definido para esa ruta.
- El enrutador de Flight luego se ejecutará y llamará al método `verPerfilUsuario($id)` en la clase `ControladorDeUsuario`, pasando el `1234` como argumento `$id` en el método.
- El código en tu método `verPerfilUsuario()` se ejecutará y hará lo que le hayas indicado. Puede que termines imprimiendo algo de HTML para la página del perfil del usuario, o si se trata de una API RESTful, podrías imprimir una respuesta JSON con la información del usuario.
- Flight envuelve esto en un bonito lazo, genera las cabeceras de respuesta y las envía de vuelta al navegador del usuario.
- ¡El usuario está lleno de alegría y se da un cálido abrazo a sí mismo!

### ¿Y por qué es importante?

¡Tener un enrutador centralizado adecuado puede hacer tu vida dramáticamente más fácil! Simplemente podría ser difícil verlo al principio. Aquí tienes algunas razones por las que:

- **Enrutamiento Centralizado**: Puedes mantener todas tus rutas en un solo lugar. Esto facilita ver qué rutas tienes y qué hacen. También facilita cambiarlas si es necesario.
- **Parámetros de Ruta**: Puedes usar parámetros de ruta para pasar datos a tus métodos de ruta. Esta es una excelente manera de mantener tu código limpio y organizado.
- **Grupos de Rutas**: Puedes agrupar rutas juntas. Esto es genial para mantener tu código organizado y para aplicar [middleware](middleware) a un grupo de rutas.
- **Alias de Rutas**: Puedes asignar un alias a una ruta, para que la URL pueda generarse dinámicamente más tarde en tu código (como una plantilla, por ejemplo). Por ejemplo, en lugar de codificar `/usuario/1234` en tu código, podrías hacer referencia al alias `vista_usuario` y pasar el `id` como parámetro. Esto es maravilloso en caso de que decidas cambiarlo a `/admin/usuario/1234` más tarde. No tendrás que cambiar todas tus URLs codificadas en duro, solo la URL asociada a la ruta.
- **Middleware de Ruta**: Puedes agregar middleware a tus rutas. El middleware es increíblemente poderoso para agregar comportamientos específicos a tu aplicación, como autenticar que cierto usuario puede acceder a una ruta o grupo de rutas.

Seguramente estás familiarizado con la forma guion por guion de crear un sitio web. Puede que tengas un archivo llamado `index.php` que tiene un montón de declaraciones `if` para comprobar la URL y luego ejecutar una función específica basada en la URL. Esto es una forma de enrutamiento, pero no es muy organizado y puede descontrolarse rápidamente. El sistema de enrutamiento de Flight es una forma mucho más organizada y poderosa de manejar el enrutamiento.

¿Esto?

```php

// /usuario/ver_perfil.php?id=1234
if ($_GET['id']) {
	$id = $_GET['id'];
	verPerfilUsuario($id);
}

// /usuario/editar_perfil.php?id=1234
if ($_GET['id']) {
	$id = $_GET['id'];
	editarPerfilUsuario($id);
}

// etc...
```

¿O esto?

```php

// index.php
Flight::route('/usuario/@id', [ 'ControladorDeUsuario', 'verPerfilUsuario' ]);
Flight::route('/usuario/@id/editar', [ 'ControladorDeUsuario', 'editarPerfilUsuario' ]);

// Quizás en tu app/controladores/ControladorDeUsuario.php
class ControladorDeUsuario {
	public function verPerfilUsuario($id) {
		// hacer algo
	}

	public function editarPerfilUsuario($id) {
		// hacer algo
	}
}
```

¡Espero que puedas empezar a ver los beneficios de usar un sistema de enrutamiento centralizado! ¡Es mucho más fácil de manejar y entender a largo plazo!

## Solicitudes y Respuestas

Flight proporciona una forma simple y fácil de manejar las solicitudes y respuestas. Esto es lo esencial de lo que hace un marco web. Toma una solicitud del navegador de un usuario, la procesa y luego envía una respuesta. Esto es como puedes construir aplicaciones web que hacen cosas como mostrar el perfil de un usuario, permitir que un usuario inicie sesión o permitir que un usuario publique una nueva publicación en un blog.

### Solicitudes

Una solicitud es lo que el navegador de un usuario envía a tu servidor cuando visitan tu sitio web. Esta solicitud contiene información sobre lo que el usuario desea hacer. Por ejemplo, podría contener información sobre qué URL quiere visitar el usuario, qué datos quiere enviar al servidor, o qué tipo de datos quiere recibir del servidor. Es importante saber que una solicitud es de solo lectura. No puedes cambiar la solicitud, pero puedes leerla.

Flight proporciona una forma sencilla de acceder a información sobre la solicitud. Puedes acceder a información sobre la solicitud usando el método `Flight::request()`. Este método devuelve un objeto `Request` que contiene información sobre la solicitud. Puedes utilizar este objeto para acceder a información sobre la solicitud, como la URL, el método o los datos que el usuario envió a tu servidor.

### Respuestas

Una respuesta es lo que tu servidor envía de vuelta al navegador de un usuario cuando visitan tu sitio web. Esta respuesta contiene información sobre lo que tu servidor desea hacer. Por ejemplo, podría contener información sobre qué tipo de datos desea enviar tu servidor al usuario, qué tipo de datos desea recibir tu servidor del usuario, o qué tipo de datos desea almacenar tu servidor en la computadora del usuario.

Flight proporciona una forma sencilla de enviar una respuesta al navegador de un usuario. Puedes enviar una respuesta usando el método `Flight::response()`. Este método toma un objeto `Response` como argumento y envía la respuesta al navegador del usuario. Puedes utilizar este objeto para enviar una respuesta al navegador del usuario, como HTML, JSON o un archivo. Flight te ayuda a generar automáticamente algunas partes de la respuesta para facilitar las cosas, pero en última instancia tienes control sobre lo que envías de vuelta al usuario.