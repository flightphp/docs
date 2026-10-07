# Plugins Increíbles

Flight es increíblemente extensible. Hay una serie de plugins que se pueden utilizar para añadir funcionalidad a tu aplicación Flight. Algunos son oficialmente soportados por el Equipo Flight y otros son bibliotecas micro/lite para ayudarte a empezar.

## Herramientas de IA

Flight puede volverse aún más genial con plugins impulsados por IA.

- [Flight MCP](/awesome-plugins/mcp) - Un plugin para integrar MCP (Protocolo de Control de Modelos) con Flight, habilitando funcionalidad impulsada por IA sin problemas. Centrado principalmente en las páginas de documentación, ayuda a mantener bajos los costos de tokens al proporcionar la información más actualizada sobre tus proyectos Flight.
- [stribus/mcp-flightphp-server-skeleton](https://github.com/stribus/mcp-flightphp-server-skeleton) - Un esqueleto de servidor FlightPHP MCP con HTTP y stdio, además de auto-descubrimiento de herramientas, prompts y recursos.

## Documentación de API

La documentación de API es crucial para cualquier API. Ayuda a los desarrolladores a entender cómo interactuar con tu API y qué esperar a cambio. Hay un par de herramientas disponibles para ayudarte a generar documentación de API para tus proyectos Flight.

- [FlightPHP OpenAPI Generator](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - Publicación de blog escrita por Daniel Schreiber sobre cómo usar la especificación OpenAPI con FlightPHP para construir tu API utilizando un enfoque API primero.
- [SwaggerUI](https://github.com/zircote/swagger-php) - Swagger UI es una gran herramienta para ayudarte a generar documentación de API para tus proyectos Flight. Es muy fácil de usar y se puede personalizar para adaptarse a tus necesidades. Esta es la biblioteca PHP que te ayuda a generar la documentación de Swagger.

## Monitoreo de Rendimiento de Aplicaciones (APM)

El Monitoreo de Rendimiento de Aplicaciones (APM) es crucial para cualquier aplicación. Ayuda a entender cómo está funcionando tu aplicación y dónde están los cuellos de botella. Hay una serie de herramientas APM que se pueden usar con Flight.
- <span class="badge bg-primary">oficial</span> [flightphp/apm](/awesome-plugins/apm) - Flight APM es una biblioteca APM simple que se puede usar para monitorear tus aplicaciones Flight. Se puede utilizar para monitorear el rendimiento de tu aplicación y ayudarte a identificar cuellos de botella.

## Asíncrono

Flight ya es un framework rápido, ¡pero ponerle un motor turbo lo hace todo más divertido (y desafiante)!

- [flightphp/async](/awesome-plugins/async) - Biblioteca oficial Flight Async. Esta biblioteca es una forma sencilla de añadir procesamiento asíncrono a tu aplicación. Utiliza Swoole/Openswoole internamente para proporcionar una manera simple y efectiva de ejecutar tareas de forma asíncrona.

## Autorización/Permisos

La autorización y los permisos son cruciales para cualquier aplicación que requiera controles para saber quién puede acceder a qué.

- <span class="badge bg-primary">oficial</span> [flightphp/permissions](/awesome-plugins/permissions) - Biblioteca oficial de permisos de Flight. Esta biblioteca es una forma sencilla de añadir permisos a nivel de usuario y de aplicación a tu aplicación.

## Autenticación

La autenticación es esencial para aplicaciones que necesitan verificar la identidad del usuario y asegurar los endpoints de API.

- [firebase/php-jwt](/awesome-plugins/jwt) - Biblioteca JSON Web Token (JWT) para PHP. Una forma simple y segura de implementar autenticación basada en tokens en tus aplicaciones Flight. Perfecta para autenticación de API sin estado, protección de rutas con middleware e implementación de flujos de autorización estilo OAuth.

## Caché

El almacenamiento en caché es una excelente manera de acelerar tu aplicación. Hay varias bibliotecas de caché que se pueden usar con Flight.

- <span class="badge bg-primary">oficial</span> [flightphp/cache](/awesome-plugins/php-file-cache) - Clase de caché en archivo PHP ligera, simple y autónoma

## CLI

Las aplicaciones CLI son una excelente manera de interactuar con tu aplicación. Puedes usarlas para generar controladores, mostrar todas las rutas y más.

- <span class="badge bg-primary">oficial</span> [flightphp/runway](/awesome-plugins/runway) - Runway es una aplicación CLI que te ayuda a gestionar tus aplicaciones Flight.

## Cookies

Las cookies son una excelente manera de almacenar pequeños fragmentos de datos en el lado del cliente. Se pueden usar para guardar preferencias del usuario, configuración de la aplicación y más.

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie es una biblioteca PHP que proporciona una forma simple y efectiva de gestionar cookies.

## Depuración

La depuración es crucial cuando desarrollas en tu entorno local. Hay algunos plugins que pueden elevar tu experiencia de depuración.

- [tracy/tracy](/awesome-plugins/tracy) - Este es un manejador de errores completo que se puede usar con Flight. Tiene una serie de paneles que pueden ayudarte a depurar tu aplicación. También es muy fácil de extender y añadir tus propios paneles.
- <span class="badge bg-primary">oficial</span> [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - Usado con el manejador de errores [Tracy](/awesome-plugins/tracy), este plugin añade algunos paneles extra para ayudar con la depuración específicamente para proyectos Flight.

## Bases de datos

Las bases de datos son el núcleo de la mayoría de las aplicaciones. Así es como almacenas y recuperas datos. Algunas bibliotecas de bases de datos son simplemente envoltorios para escribir consultas y otras son ORMs completos.

- <span class="badge bg-primary">oficial</span> [flightphp/core SimplePdo](/learn/simple-pdo) - Ayudante oficial de PDO de Flight que forma parte del núcleo. Es un envoltorio moderno con métodos auxiliares convenientes como `insert()`, `update()`, `delete()` y `transaction()` para simplificar las operaciones de base de datos. Todos los resultados se devuelven como Collections para un acceso flexible a arrays/objetos. No es un ORM, solo una mejor manera de trabajar con PDO.
- <span class="badge bg-warning">obsoleto</span> [flightphp/core PdoWrapper](/learn/pdo-wrapper) - Envoltorio oficial de PDO de Flight que forma parte del núcleo (obsoleto a partir de v3.18.0). Usa SimplePdo en su lugar.
- <span class="badge bg-primary">oficial</span> [flightphp/active-record](/awesome-plugins/active-record) - ORM/Mapeador ActiveRecord oficial de Flight. Gran pequeña biblioteca para recuperar y almacenar datos fácilmente en tu base de datos.
- [byjg/php-migration](/awesome-plugins/migrations) - Plugin para hacer un seguimiento de todos los cambios de base de datos de tu proyecto.
- [knifelemon/easy-query](/awesome-plugins/easy-query) - Constructor de consultas SQL ligero y fluido que genera SQL y parámetros para sentencias preparadas. Funciona muy bien con [SimplePdo](/learn/simple-pdo).

## Cifrado

El cifrado es crucial para cualquier aplicación que almacene datos sensibles. Cifrar y descifrar los datos no es terriblemente difícil, pero almacenar correctamente la clave de cifrado [puede](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [ser](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [difícil](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key). Lo más importante es nunca almacenar tu clave de cifrado en un directorio público ni hacer commit de ella en tu repositorio de código.

- [defuse/php-encryption](/awesome-plugins/php-encryption) - Esta es una biblioteca que se puede usar para cifrar y descifrar datos. Ponerla en marcha es bastante simple para comenzar a cifrar y descifrar datos.

## Correo electrónico

El envío de correos electrónicos es una necesidad central para la mayoría de las aplicaciones web: mensajes de bienvenida, restablecimientos de contraseña, notificaciones. Estas bibliotecas lo hacen sin esfuerzo mientras mantienen una entrega sólida.

- [ryanstubbs/flightmail](/awesome-plugins/flightmail) - FlightMail envuelve a Symfony Mailer con una API fluida y amigable con Flight. Envía a través de SMTP o cualquier proveedor importante mediante cadenas DSN simples, enruta diferentes proveedores por mensaje y renderiza los cuerpos con plantillas Twig o Latte. Este es un plugin no oficial para Flight y no es mantenido por el equipo de Flight.

## Cola de trabajos

Las colas de trabajos son muy útiles para procesar tareas de forma asíncrona. Esto puede ser enviar correos electrónicos, procesar imágenes o cualquier cosa que no necesite hacerse en tiempo real.

- [n0nag0n/simple-job-queue](/awesome-plugins/simple-job-queue) - Simple Job Queue es una biblioteca que se puede utilizar para procesar trabajos de forma asíncrona. Se puede usar con beanstalkd, MySQL/MariaDB, SQLite y PostgreSQL.

## Sesión

Las sesiones no son realmente útiles para las API, pero para construir una aplicación web, las sesiones pueden ser cruciales para mantener el estado y la información de inicio de sesión.

- <span class="badge bg-primary">oficial</span> [flightphp/session](/awesome-plugins/session) - Biblioteca oficial de sesiones de Flight. Esta es una biblioteca de sesiones simple que se puede usar para almacenar y recuperar datos de sesión. Utiliza el manejo de sesiones integrado de PHP.
- [Ghostff/Session](/awesome-plugins/ghost-session) - Administrador de sesiones PHP (no bloqueante, flash, segmento, cifrado de sesión). Utiliza PHP open_ssl para el cifrado/descifrado opcional de los datos de sesión.

## Plantillas

El motor de plantillas es central para cualquier aplicación web con interfaz de usuario. Hay una serie de motores de plantillas que se pueden usar con Flight.

- <span class="badge bg-warning">obsoleto</span> [flightphp/core View](/learn#views) - Este es un motor de plantillas muy básico que forma parte del núcleo. No se recomienda usarlo si tienes más de un par de páginas en tu proyecto.
- [latte/latte](/awesome-plugins/latte) - Latte es un motor de plantillas completo que es muy fácil de usar y se siente más cercano a la sintaxis de PHP que Twig o Smarty. También es muy fácil de extender y añadir tus propios filtros y funciones.
- [twig/twig](/awesome-plugins/twig) - Twig es un motor de plantillas flexible, rápido y seguro (el mismo que usa Symfony). Las herramientas de IA y muchos desarrolladores de PHP lo conocen bien, escapa automáticamente la salida por defecto y tiene un enorme ecosistema de extensiones.
- [knifelemon/comment-template](/awesome-plugins/comment-template) - CommentTemplate es un potente motor de plantillas PHP con compilación de assets, herencia de plantillas y procesamiento de variables. Incluye minificación automática de CSS/JS, caché, codificación Base64 e integración opcional con el framework PHP Flight.

## Integración con WordPress

¿Quieres usar Flight en tu proyecto WordPress? ¡Hay un plugin útil para eso!

- [n0nag0n/wordpress-integration-for-flight-framework](/awesome-plugins/n0nag0n_wordpress) - Este plugin de WordPress te permite ejecutar Flight junto a WordPress. Es perfecto para añadir APIs personalizadas, microservicios o incluso aplicaciones completas a tu sitio WordPress usando el framework Flight. ¡Súper útil si quieres lo mejor de ambos mundos!

## Contribuir

¿Tienes un plugin que te gustaría compartir? ¡Envía un pull request para añadirlo a la lista!