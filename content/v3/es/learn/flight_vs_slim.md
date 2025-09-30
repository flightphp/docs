# Flight vs Slim

## ¿Qué es Slim?
[Slim](https://slimframework.com) es un micro framework de PHP que te ayuda a escribir rápidamente aplicaciones web y APIs simples pero potentes.

Mucho de la inspiración para algunas de las características de la versión 3 de Flight en realidad provino de Slim. Agrupar rutas y ejecutar middleware en un orden específico son dos características que fueron inspiradas por Slim. Slim v3 salió orientado a la simplicidad, pero ha habido [reseñas mixtas](https://github.com/slimphp/Slim/issues/2770) respecto a v4.

## Pros en comparación con Flight

- Slim tiene una comunidad más grande de desarrolladores, quienes a su vez crean módulos útiles para ayudarte a no reinventar la rueda.
- Slim sigue muchas interfaces y estándares que son comunes en la comunidad de PHP, lo que aumenta la interoperabilidad.
- Slim tiene documentación decente y tutoriales que se pueden usar para aprender el framework (nada comparable a Laravel o Symfony, sin embargo).
- Slim tiene varios recursos como tutoriales de YouTube y artículos en línea que se pueden usar para aprender el framework.
- Slim te permite usar los componentes que quieras para manejar las características principales de enrutamiento, ya que es compatible con PSR-7.

## Cons en comparación con Flight

- Sorprendentemente, Slim no es tan rápido como podrías pensar para un micro-framework. Consulta los 
  [benchmarks de TechEmpower](https://www.techempower.com/benchmarks/#hw=ph&test=fortune&section=data-r22&l=zik073-cn3) 
  para obtener más información.
- Flight está orientado a un desarrollador que busca construir una aplicación web ligera, rápida y fácil de usar.
- Flight no tiene dependencias, mientras que [Slim tiene algunas dependencias](https://github.com/slimphp/Slim/blob/4.x/composer.json) que debes instalar.
- Flight está orientado a la simplicidad y facilidad de uso.
- Una de las características principales de Flight es que hace lo posible por mantener la compatibilidad hacia atrás. El cambio de Slim v3 a v4 fue un cambio rompiente.
- Flight está destinado a desarrolladores que se adentran por primera vez en el mundo de los frameworks.
- Flight también puede manejar aplicaciones a nivel empresarial, pero no tiene tantos ejemplos y tutoriales como Slim.
  También requerirá más disciplina por parte del desarrollador para mantener las cosas organizadas y bien estructuradas.
- Flight da al desarrollador más control sobre la aplicación, mientras que Slim puede introducir algo de magia detrás de escena.
- Flight tiene un [PdoWrapper](/learn/pdo-wrapper) simple que se puede usar para interactuar con tu base de datos. Slim requiere que uses una biblioteca de terceros.
- Flight tiene un [plugin de permisos](/awesome-plugins/permissions) que se puede usar para asegurar tu aplicación. Slim requiere que uses una biblioteca de terceros.
- Flight tiene un ORM llamado [active-record](/awesome-plugins/active-record) que se puede usar para interactuar con tu base de datos. Slim requiere que uses una biblioteca de terceros.
- Flight tiene una aplicación CLI llamada [runway](/awesome-plugins/runway) que se puede usar para ejecutar tu aplicación desde la línea de comandos. Slim no la tiene.