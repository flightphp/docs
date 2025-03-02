# Flight vs Fat-Free

## ¿Qué es Fat-Free?
[Fat-Free](https://fatfreeframework.com) (conocido afectuosamente como **F3**) es un micro marco PHP poderoso pero fácil de usar diseñado para ayudarte a construir aplicaciones web dinámicas y robustas - ¡rápido!

Flight se compara con Fat-Free en muchos aspectos y probablemente es el pariente más cercano en cuanto a características y simplicidad. Fat-Free tiene muchas características que Flight no tiene, pero también tiene muchas características que Flight sí tiene. Fat-Free está empezando a mostrar su edad y no es tan popular como solía ser.

Las actualizaciones son menos frecuentes y la comunidad no es tan activa como solía ser. El código es lo suficientemente simple, pero a veces la falta de disciplina sintáctica puede hacer que sea difícil de leer y entender. Funciona para PHP 8.3, pero el código en sí mismo todavía parece vivir en PHP 5.3.

## Pros en comparación con Flight

- Fat-Free tiene algunas estrellas más en GitHub que Flight.
- Fat-Free tiene cierta documentación decente, pero carece en algunas áreas en cuanto a claridad.
- Fat-Free tiene algunos recursos dispersos como tutoriales en YouTube y artículos en línea que se pueden utilizar para aprender el marco.
- Fat-Free tiene [algunos complementos útiles](https://fatfreeframework.com/3.8/api-reference) incorporados que a veces son útiles.
- Fat-Free tiene un ORM incorporado llamado Mapper que se puede utilizar para interactuar con tu base de datos. Flight tiene [active-record](/awesome-plugins/active-record).
- Fat-Free tiene Sesiones, Caché y localización incorporadas. Flight requiere que uses bibliotecas de terceros, pero está cubierto en la [documentación](/awesome-plugins).
- Fat-Free tiene un pequeño grupo de [complementos creados por la comunidad](https://fatfreeframework.com/3.8/development#Community) que se pueden utilizar para ampliar el marco. Flight tiene algunos cubiertos en las páginas de [documentación](/awesome-plugins) y [ejemplos](/examples).
- Fat-Free al igual que Flight no tiene dependencias.
- Fat-Free al igual que Flight está orientado a dar al desarrollador control sobre su aplicación y una experiencia de desarrollo simple.
- Fat-Free mantiene la compatibilidad hacia atrás al igual que Flight (parcialmente porque las actualizaciones están siendo [menos frecuentes](https://github.com/bcosca/fatfree/releases)).
- Fat-Free al igual que Flight está destinado a desarrolladores que se adentran por primera vez en el mundo de los marcos.
- Fat-Free tiene un motor de plantillas incorporado que es más robusto que el motor de plantillas de Flight. Flight recomienda [Latte](/awesome-plugins/latte) para lograr esto.
- Fat-Free tiene un comando de tipo CLI único "route" donde puedes construir aplicaciones CLI dentro de Fat-Free mismo y tratarlo de manera similar a una solicitud `GET`. Flight logra esto con [runway](/awesome-plugins/runway).

## Contras en comparación con Flight

- Fat-Free tiene algunas pruebas de implementación e incluso tiene su propia clase de [prueba](https://fatfreeframework.com/3.8/test) que es muy básica. Sin embargo, 
  no está probado al 100% como lo está Flight.
- Tienes que utilizar un motor de búsqueda como Google para buscar realmente en el sitio de documentación.
- Flight tiene modo oscuro en su sitio de documentación. (dejar caer el micrófono)
- Fat-Free tiene algunos módulos que están terriblemente descuidados.
- Flight tiene un [PdoWrapper](/awesome-plugins/pdo-wrapper) simple que es un poco más simple que la clase `DB\SQL` incorporada de Fat-Free.
- Flight tiene un complemento de [permisos](/awesome-plugins/permissions) que se puede utilizar para asegurar tu aplicación. Slim requiere que utilices
  una biblioteca de terceros.
- Flight tiene un ORM llamado [active-record](/awesome-plugins/active-record) que se siente más como un ORM que el Mapper de Fat-Free.
  El beneficio añadido de `active-record` es que puedes definir relaciones entre registros para uniones automáticas donde el Mapper de Fat-Free
  requiere que crees [vistas SQL](https://fatfreeframework.com/3.8/databases#ProsandCons).
- Sorprendentemente, Fat-Free no tiene un espacio de nombres raíz. Flight tiene espacios de nombres hasta el final para no chocar con tu propio código.
  la clase `Cache` es el mayor infractor aquí.
- Fat-Free no tiene middleware. En su lugar, hay ganchos `beforeroute` y `afterroute` que se pueden utilizar para filtrar solicitudes y respuestas en controladores.
- Fat-Free no puede agrupar rutas.
- Fat-Free tiene un manejador de contenedores de inyección de dependencias, pero la documentación es increíblemente escasa sobre cómo usarlo.
- El depurado puede complicarse un poco ya que básicamente todo se almacena en lo que se llama el [`HIVE`](https://fatfreeframework.com/3.8/quick-reference)