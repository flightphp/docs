# Flight vs Fat-Free

## ¿Qué es Fat-Free?
[Fat-Free](https://fatfreeframework.com) (conocido afectuosamente como **F3**) es un micro-framework PHP potente pero fácil de usar diseñado para ayudarte a construir aplicaciones web dinámicas y robustas - ¡rápidamente!

Flight se compara con Fat-Free en muchos aspectos y probablemente es el primo más cercano en términos de características y simplicidad. Fat-Free tiene
muchas características que Flight no tiene, pero también tiene muchas características que Flight sí tiene. Fat-Free está empezando a mostrar su edad
y no es tan popular como lo fue una vez.

Las actualizaciones se están volviendo menos frecuentes y la comunidad no es tan activa como lo fue antes. El código es lo suficientemente simple, pero a veces la falta de
disciplina en la sintaxis puede hacerlo difícil de leer y entender. Funciona para PHP 8.3, pero el código en sí todavía parece vivir en
PHP 5.3.

## Pros comparado con Flight

- Fat-Free tiene algunas estrellas más en GitHub que Flight.
- Fat-Free tiene una documentación decente, pero carece de claridad en algunas áreas.
- Fat-Free tiene algunos recursos escasos como tutoriales de YouTube y artículos en línea que se pueden usar para aprender el framework.
- Fat-Free tiene [algunos plugins útiles](https://fatfreeframework.com/3.8/api-reference) integrados que a veces son útiles.
- Fat-Free tiene un ORM integrado llamado Mapper que se puede usar para interactuar con tu base de datos. Flight tiene [active-record](/awesome-plugins/active-record).
- Fat-Free tiene Sessions, Caching y localización integrados. Flight requiere que uses bibliotecas de terceros, pero está cubierto en la [documentación](/awesome-plugins).
- Fat-Free tiene un pequeño grupo de [plugins creados por la comunidad](https://fatfreeframework.com/3.8/development#Community) que se pueden usar para extender el framework. Flight tiene algunos cubiertos en las páginas de [documentación](/awesome-plugins) y [ejemplos](/examples).
- Fat-Free, al igual que Flight, no tiene dependencias.
- Fat-Free, al igual que Flight, está orientado a dar al desarrollador control sobre su aplicación y una experiencia de desarrollador simple.
- Fat-Free mantiene la compatibilidad hacia atrás como lo hace Flight (parcialmente porque las actualizaciones se están volviendo [menos frecuentes](https://github.com/bcosca/fatfree/releases)).
- Fat-Free, al igual que Flight, está destinado a desarrolladores que se adentran en el mundo de los frameworks por primera vez.
- Fat-Free tiene un motor de plantillas integrado que es más robusto que el motor de plantillas de Flight. Flight recomienda [Latte](/awesome-plugins/latte) para lograr esto.
- Fat-Free tiene un comando único de tipo CLI "route" donde puedes construir aplicaciones CLI dentro de Fat-Free mismo y tratarlo mucho como una solicitud `GET`. Flight logra esto con [runway](/awesome-plugins/runway).

## Cons comparado con Flight

- Fat-Free tiene algunas pruebas de implementación e incluso tiene su propia [clase de prueba](https://fatfreeframework.com/3.8/test) que es muy básica. Sin embargo,
  no está 100% probado con pruebas unitarias como lo está Flight. 
- Tienes que usar un motor de búsqueda como Google para buscar realmente en el sitio de documentación.
- Flight tiene modo oscuro en su sitio de documentación. (mic drop)
- Fat-Free tiene algunos módulos que están lamentablemente sin mantener.
- Flight tiene un [PdoWrapper](/learn/pdo-wrapper) simple que es un poco más simple que la clase `DB\SQL` integrada de Fat-Free.
- Flight tiene un [plugin de permisos](/awesome-plugins/permissions) que se puede usar para asegurar tu aplicación. Fat-Free requiere que uses 
  una biblioteca de terceros.
- Flight tiene un ORM llamado [active-record](/awesome-plugins/active-record) que se siente más como un ORM que el Mapper de Fat-Free.
  El beneficio adicional de `active-record` es que puedes definir relaciones entre registros para uniones automáticas donde el Mapper de Fat-Free
  requiere que crees [vistas SQL](https://fatfreeframework.com/3.8/databases#ProsandCons).
- Sorprendentemente, Fat-Free no tiene un espacio de nombres raíz. Flight está con espacios de nombres hasta el final para no colisionar con tu propio código.
  la clase `Cache` es la mayor infractora aquí.
- Fat-Free no tiene middleware. En su lugar, hay ganchos `beforeroute` y `afterroute` que se pueden usar para filtrar solicitudes y respuestas en controladores.
- Fat-Free no puede agrupar rutas.
- Fat-Free tiene un manejador de contenedor de inyección de dependencias, pero la documentación es increíblemente escasa sobre cómo usarlo.
- La depuración puede volverse un poco complicada ya que básicamente todo se almacena en lo que se llama el [`HIVE`](https://fatfreeframework.com/3.8/quick-reference)