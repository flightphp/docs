# ¿Por qué un Framework?

Algunos programadores se oponen vehementemente a usar frameworks. Argumentan que los frameworks son pesados, lentos y difíciles de aprender. Dicen que los frameworks son innecesarios y que se puede escribir un mejor código sin ellos. Ciertamente, hay algunos puntos válidos que se pueden hacer sobre las desventajas de usar frameworks. Sin embargo, también hay muchas ventajas en utilizar frameworks.

## Razones para Usar un Framework

Aquí hay algunas razones por las que podrías querer considerar utilizar un framework:

- **Desarrollo rápido**: Los frameworks proporcionan mucha funcionalidad listo para usar. Esto significa que puedes construir aplicaciones web más rápidamente. No tienes que escribir tanto código porque el framework proporciona gran parte de la funcionalidad que necesitas.
- **Consistencia**: Los frameworks proporcionan una forma consistente de hacer las cosas. Esto hace que sea más fácil para ti entender cómo funciona el código y facilita que otros desarrolladores entiendan tu código. Si tienes cada script por separado, podrías perder consistencia entre scripts, especialmente si estás trabajando con un equipo de desarrolladores.
- **Seguridad**: Los frameworks proporcionan características de seguridad que ayudan a proteger tus aplicaciones web de amenazas de seguridad comunes. Esto significa que no tienes que preocuparte tanto por la seguridad porque el framework se encarga de gran parte de ella por ti.
- **Comunidad**: Los frameworks tienen grandes comunidades de desarrolladores que contribuyen al framework. Esto significa que puedes obtener ayuda de otros desarrolladores cuando tengas preguntas o problemas. También significa que hay muchos recursos disponibles para ayudarte a aprender cómo usar el framework.
- **Mejores prácticas**: Los frameworks están construidos utilizando mejores prácticas. Esto significa que puedes aprender del framework y utilizar las mismas mejores prácticas en tu propio código. Esto puede ayudarte a ser un mejor programador. A veces no sabes lo que no sabes y eso puede perjudicarte al final.
- **Extensibilidad**: Los frameworks están diseñados para ser ampliables. Esto significa que puedes agregar tu propia funcionalidad al framework. Esto te permite construir aplicaciones web adaptadas a tus necesidades específicas.

Flight es un micro-framework. Esto significa que es pequeño y ligero. No proporciona tanta funcionalidad como frameworks más grandes como Laravel o Symfony. Sin embargo, proporciona gran parte de la funcionalidad que necesitas para construir aplicaciones web. También es fácil de aprender y usar. Esto lo convierte en una buena elección para construir aplicaciones web de forma rápida y sencilla. Si eres nuevo en los frameworks, Flight es un excelente framework para principiantes con el que empezar. Te ayudará a aprender acerca de las ventajas de usar frameworks sin saturarte con demasiada complejidad. Después de ganar experiencia con Flight, será más fácil pasar a frameworks más complejos como Laravel o Symfony, sin embargo, Flight aún puede crear una aplicación sólida y exitosa.

## ¿Qué es el Enrutamiento?

El enrutamiento es el núcleo del framework Flight, pero ¿qué es exactamente? El enrutamiento es el proceso de tomar una URL y hacerla coincidir con una función específica en tu código. De esta manera puedes hacer que tu sitio web haga diferentes cosas basadas en la URL solicitada. Por ejemplo, es posible que desees mostrar el perfil de un usuario cuando visitan `/usuario/1234`, pero mostrar una lista de todos los usuarios cuando visitan `/usuarios`. Todo esto se hace a través del enrutamiento.

Podría funcionar algo así:

- Un usuario va a tu navegador y escribe `http://ejemplo.com/usuario/1234`.
- El servidor recibe la solicitud y mira la URL y la pasa a tu código de la aplicación Flight.
- Digamos que en tu código de Flight tienes algo como `Flight::route('/usuario/@id', [ 'ControladorUsuario', 'verPerfilUsuario' ]);`. Tu código de la aplicación Flight mira la URL y ve que coincide con una ruta que has definido, y luego ejecuta el código que has definido para esa ruta.
- Luego, el enrutador de Flight ejecutará y llamará al método `verPerfilUsuario($id)` en la clase `ControladorUsuario`, pasando el `1234` como el argumento `$id` en el método.
- El código en tu método `verPerfilUsuario()` se ejecutará y hará lo que le has indicado. Podrías terminar imprimiendo algo de HTML para la página del perfil del usuario, o si se trata de una API RESTful, podrías imprimir una respuesta JSON con la información del usuario.
- Flight envuelve esto con un bonito lazo, genera los encabezados de respuesta y lo envía de vuelta al navegador del usuario.
- ¡El usuario está lleno de alegría y se da un cálido abrazo a sí mismo!

### ¿Y Por Qué es Importante?

¡Tener un enrutador centralizado adecuado puede hacer tu vida dramáticamente más fácil! Puede ser difícil verlo al principio. Aquí tienes algunas razones:

- **Enrutamiento Centralizado**: Puedes mantener todas tus rutas en un solo lugar. Esto facilita ver qué rutas tienes y qué hacen. También facilita cambiarlas si es necesario.
- **Parámetros de Ruta**: Puedes usar los parámetros de ruta para pasar datos a tus métodos de ruta. Esta es una excelente manera de mantener tu código limpio y organizado.
- **Grupos de Rutas**: Puedes agrupar rutas juntas. Esto es genial para mantener tu código organizado y para aplicar [middleware](middleware) a un grupo de rutas.
- **Alias de Ruta**: Puedes asignar un alias a una ruta, para que la URL pueda generarse dinámicamente más tarde en tu código (como una plantilla, por ejemplo). Por ejemplo, en lugar de codificar `/usuario/1234` en tu código, podrías hacer referencia al alias `vista_usuario` y pasar el `id` como parámetro. Esto resulta maravilloso en caso de que decidas cambiarlo más tarde a `/admin/usuario/1234`. No tendrás que cambiar todas tus urls codificadas, solo la URL asociada a la ruta.
- **Middleware de Ruta**: Puedes agregar middleware a tus rutas. El middleware es increíblemente poderoso para agregar comportamientos específicos a tu aplicación como autenticar que un cierto usuario pueda acceder a una ruta o grupo de rutas.

Seguramente estás familiarizado con la forma script por script de crear un sitio web. Tal vez tengas un archivo llamado `index.php` que tiene un montón de declaraciones `if` para revisar la URL y luego ejecutar una función específica basada en la URL. Esto es una forma de enrutamiento, pero no está muy organizada y puede descontrolarse rápidamente. El sistema de enrutamiento de Flight es una forma mucho más organizada y poderosa de manejar el enrutamiento.

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

O esto?

```php

// index.php
Flight::route('/usuario/@id', [ 'ControladorUsuario', 'verPerfilUsuario' ]);
Flight::route('/usuario/@id/editar', [ 'ControladorUsuario', 'editarPerfilUsuario' ]);

// Tal vez en tu app/controladores/ControladorUsuario.php
class ControladorUsuario {
	public function verPerfilUsuario($id) {
		// hacer algo
	}

	public function editarPerfilUsuario($id) {
		// hacer algo
	}
}
```

Espero que empieces a ver los beneficios de utilizar un sistema de enrutamiento centralizado. ¡Es mucho más fácil de gestionar y entender a largo plazo!

## Solicitudes y Respuestas

Flight proporciona una forma simple y fácil de manejar solicitudes y respuestas. Esto es el núcleo de lo que hace un framework web. Toma una solicitud del navegador de un usuario, la procesa y luego envía una respuesta. Así es como puedes construir aplicaciones web que hagan cosas como mostrar el perfil de un usuario, permitir que un usuario inicie sesión o permitir a un usuario publicar una nueva publicación de blog.

### Solicitudes

Una solicitud es lo que envía el navegador de un usuario a tu servidor cuando visita tu sitio web. Esta solicitud contiene información sobre lo que el usuario desea hacer. Por ejemplo, podría contener información sobre qué URL desea visitar el usuario, qué datos quiere enviar al servidor o qué tipo de datos desea recibir del servidor. Es importante saber que una solicitud es de solo lectura. No puedes cambiar la solicitud, pero puedes leerla.

Flight proporciona una forma sencilla de acceder a la información sobre la solicitud. Puedes acceder a la información sobre la solicitud utilizando el método `Flight::request()`. Este método devuelve un objeto `Request` que contiene información sobre la solicitud. Puedes usar este objeto para acceder a información sobre la solicitud, como la URL, el método o los datos que el usuario envió a tu servidor.

### Respuestas

Una respuesta es lo que tu servidor envía de vuelta al navegador de un usuario cuando visita tu sitio web. Esta respuesta contiene información sobre lo que tu servidor desea hacer. Por ejemplo, podría contener información sobre qué tipo de datos quiere enviar al usuario tu servidor, qué tipo de datos desea recibir del usuario tu servidor o qué tipo de datos quiere almacenar en la computadora del usuario tu servidor.

Flight proporciona una forma simple de enviar una respuesta al navegador de un usuario. Puedes enviar una respuesta utilizando el método `Flight::response()`. Este método toma un objeto `Response` como argumento y envía la respuesta al navegador del usuario. Puedes usar este objeto para enviar una respuesta al navegador del usuario, como HTML, JSON o un archivo. Flight te ayuda a generar automáticamente algunas partes de la respuesta para facilitar las cosas, pero en última instancia tienes control sobre lo que envías de vuelta al usuario.