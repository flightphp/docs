# ¿Por qué un Framework?

Algunos programadores se oponen vehementemente a utilizar frameworks. Argumentan que los frameworks son pesados, lentos y difíciles de aprender. 
Dicen que los frameworks son innecesarios y que puedes escribir un código mejor sin ellos. 
Ciertamente se pueden hacer algunos puntos válidos sobre las desventajas de usar frameworks. Sin embargo, también existen muchas ventajas al utilizar frameworks. 

## Razones para usar un Framework

Aquí hay algunas razones por las cuales podrías considerar utilizar un framework:

- **Desarrollo Rápido**: Los frameworks proporcionan mucha funcionalidad de serie. Esto significa que puedes construir aplicaciones web más rápidamente. No necesitas escribir tanto código porque el framework proporciona mucha de la funcionalidad que necesitas.
- **Consistencia**: Los frameworks ofrecen una forma consistente de hacer las cosas. Esto facilita tu comprensión de cómo funciona el código y también facilita que otros desarrolladores entiendan tu código. Si lo tienes guion por guion, podrías perder consistencia entre guiones, especialmente si estás trabajando con un equipo de desarrolladores.
- **Seguridad**: Los frameworks ofrecen funciones de seguridad que ayudan a proteger tus aplicaciones web de amenazas de seguridad comunes. Esto significa que no tienes que preocuparte tanto por la seguridad porque el framework se encarga de gran parte de ello.
- **Comunidad**: Los frameworks cuentan con grandes comunidades de desarrolladores que contribuyen al framework. Esto significa que puedes obtener ayuda de otros desarrolladores cuando tengas preguntas o problemas. También significa que hay muchos recursos disponibles para ayudarte a aprender cómo utilizar el framework.
- **Mejores Prácticas**: Los frameworks están construidos utilizando mejores prácticas. Esto significa que puedes aprender del framework y usar las mismas mejores prácticas en tu propio código. Esto puede ayudarte a ser un mejor programador. A veces no sabes lo que no sabes y eso puede perjudicarte al final.
- **Extensibilidad**: Los frameworks están diseñados para ser extendidos. Esto significa que puedes agregar tu propia funcionalidad al framework. Esto te permite construir aplicaciones web adaptadas a tus necesidades específicas.

Flight es un micro-framework. Esto significa que es pequeño y ligero. No proporciona tanta funcionalidad como frameworks más grandes como Laravel o Symfony. 
Sin embargo, proporciona mucha de la funcionalidad que necesitas para construir aplicaciones web. También es fácil de aprender y usar. 
Esto lo convierte en una buena elección para construir aplicaciones web rápidamente y fácilmente. Si eres nuevo en los frameworks, Flight es un gran framework para principiantes con el que empezar. 
Te ayudará a entender las ventajas de usar frameworks sin abrumarte con demasiada complejidad. 
Después de tener algo de experiencia con Flight, será más fácil pasar a frameworks más complejos como Laravel o Symfony, 
sin embargo, Flight aún puede crear una aplicación robusta y exitosa.

## ¿Qué es el Enrutamiento?

El enrutamiento es el núcleo del framework Flight, ¿pero qué es exactamente? Enrutamiento es el proceso de tomar una URL y emparejarla con una función específica en tu código. 
Esto es cómo puedes hacer que tu sitio web haga cosas diferentes basadas en la URL que se solicita. Por ejemplo, es posible que desees mostrar el perfil de un usuario cuando 
visitan `/usuario/1234`, pero mostrar una lista de todos los usuarios cuando visitan `/usuarios`. Todo esto se hace a través del enrutamiento.

Podría funcionar algo así:

- Un usuario va a tu navegador y escribe `http://ejemplo.com/usuario/1234`.
- El servidor recibe la solicitud y mira la URL y la pasa a tu código de aplicación de Flight.
- Digamos que en tu código de Flight tienes algo así como `Flight::route('/usuario/@id', [ 'ControladorUsuario', 'verPerfilUsuario' ]);`. Tu código de la aplicación de Flight mira la URL y ve que coincide con una ruta que has definido, y luego ejecuta el código que has definido para esa ruta.  
- El enrutador de Flight luego ejecutará y llamará el método `verPerfilUsuario($id)` en la clase `ControladorUsuario`, pasando el `1234` como el argumento `$id` en el método.
- El código en tu método `verPerfilUsuario()` se ejecutará y hará lo que le hayas indicado. Podrías terminar imprimiendo algo de HTML para la página del perfil del usuario, o si se trata de una API RESTful, podrías imprimir una respuesta JSON con la información del usuario.
- Flight envuelve esto en un bonito lazo, genera los encabezados de respuesta y lo envía de vuelta al navegador del usuario.
- ¡El usuario se llena de alegría y se da un cálido abrazo a sí mismo!

### ¿Y por qué es importante?

¡Tener un enrutador centralizado adecuado puede realmente hacer tu vida mucho más fácil! Al principio, podría ser difícil verlo. Aquí hay algunas razones por las cuales:

- **Enrutamiento Centralizado**: Puedes mantener todas tus rutas en un solo lugar. Esto facilita ver qué rutas tienes y qué hacen. También facilita cambiarlas si es necesario.
- **Parámetros de Ruta**: Puedes usar parámetros de ruta para pasar datos a tus métodos de ruta. Esta es una excelente manera de mantener tu código limpio y organizado.
- **Grupos de Rutas**: Puedes agrupar rutas juntas. Esto es excelente para mantener tu código organizado y para aplicar [middleware](middleware) a un grupo de rutas.
- **Alias de Ruta**: Puedes asignar un alias a una ruta, para que la URL pueda generarse dinámicamente más tarde en tu código (como una plantilla, por ejemplo). Ej: en lugar de codificar `/usuario/1234` en tu código, podrías en su lugar hacer referencia al alias `vista_usuario` y pasar el `id` como parámetro. Esto es útil en caso de que decidas cambiarlo a `/admin/usuario/1234` más adelante. No tendrías que cambiar todas tus URLs codificadas, solo la URL vinculada a la ruta.
- **Middleware de Ruta**: Puedes agregar middleware a tus rutas. El middleware es increíblemente potente para agregar comportamientos específicos a tu aplicación como autenticar que cierto usuario pueda acceder a una ruta o grupo de rutas.

Seguro que estás familiarizado con la forma guion por guion de crear un sitio web. Podrías tener un archivo llamado `index.php` que tiene un montón de declaraciones `if` 
para verificar la URL y luego ejecutar una función específica basada en la URL. Esto es una forma de enrutamiento, pero no es muy organizado y puede 
salirse de control rápidamente. El sistema de enrutamiento de Flight es una forma mucho más organizada y poderosa de manejar el enrutamiento.

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
Flight::route('/usuario/@id', [ 'ControladorUsuario', 'verPerfilUsuario' ]);
Flight::route('/usuario/@id/editar', [ 'ControladorUsuario', 'editarPerfilUsuario' ]);

// En tal vez tu app/controladores/ControladorUsuario.php
class ControladorUsuario {
	public function verPerfilUsuario($id) {
		// hacer algo
	}

	public function editarPerfilUsuario($id) {
		// hacer algo
	}
}
```

¡Espero que comiences a ver los beneficios de usar un sistema de enrutamiento centralizado. Es mucho más fácil de gestionar y entender a largo plazo!

## Solicitudes y Respuestas

Flight proporciona una forma simple y fácil de manejar solicitudes y respuestas. Esto es lo básico de lo que hace un framework web. Toma una solicitud 
de un navegador del usuario, la procesa, y luego envía una respuesta. Así es como puedes construir aplicaciones web que hagan cosas como mostrar el perfil de un usuario, permitir a un usuario iniciar sesión o permitir a un usuario publicar una nueva publicación en un blog.

### Solicitudes

Una solicitud es lo que un navegador del usuario envía a tu servidor cuando visita tu sitio web. Esta solicitud contiene información sobre lo que el usuario 
quiere hacer. Por ejemplo, podría contener información sobre qué URL quiere visitar el usuario, qué datos quiere enviar el usuario a tu servidor, 
o qué tipo de datos quiere recibir del servidor. Es importante tener en cuenta que una solicitud es de solo lectura. No puedes cambiar la solicitud, 
pero puedes leer de ella.

Flight proporciona una forma simple de acceder a información sobre la solicitud. Puedes acceder a información sobre la solicitud utilizando el método `Flight::request()` 
Este método devuelve un objeto `Request` que contiene información sobre la solicitud. Puedes usar este objeto para acceder a información sobre la solicitud, 
como la URL, el método o los datos que el usuario envió a tu servidor.

### Respuestas

Una respuesta es lo que tu servidor envía de vuelta al navegador del usuario cuando visita tu sitio web. Esta respuesta contiene información sobre lo que 
tu servidor quiere hacer. Por ejemplo, podría contener información sobre qué tipo de datos tu servidor quiere enviar al usuario, qué tipo de datos 
tu servidor quiere recibir del usuario, o qué tipo de datos tu servidor quiere almacenar en la computadora del usuario.

Flight proporciona una forma simple de enviar una respuesta al navegador del usuario. Puedes enviar una respuesta usando el método `Flight::response()` 
Este método toma un objeto `Response` como argumento y envía la respuesta al navegador del usuario. Puedes usar este objeto para enviar una respuesta al navegador del usuario, 
como HTML, JSON o un archivo. Flight te ayuda a generar automáticamente algunas partes de la respuesta para facilitar las cosas, pero en última instancia tú tienes 
control sobre lo que envías de vuelta al usuario.