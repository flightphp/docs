# Comparación entre Flight y Slim

## ¿Qué es Slim?
[Slim](https://slimframework.com) es un micro marco de PHP que te ayuda a escribir rápidamente aplicaciones web y APIs simples pero potentes.

Mucha de la inspiración para algunas de las características de la versión 3 de Flight realmente provino de Slim. Agrupar rutas y ejecutar middleware 
en un orden específico son dos características que fueron inspiradas por Slim. Slim v3 salió enfocado hacia la simplicidad, pero ha habido 
[resenas mixtas](https://github.com/slimphp/Slim/issues/2770) con respecto a la v4.

## Ventajas en comparación con Flight

- Slim tiene una comunidad más grande de desarrolladores, quienes a su vez crean módulos útiles para ayudarte a no reinventar la rueda.
- Slim sigue muchas interfaces y estándares comunes en la comunidad de PHP, lo que aumenta la interoperabilidad.
- Slim tiene una documentación decente y tutoriales que se pueden utilizar para aprender el marco de trabajo (aunque no tanto como Laravel o Symfony).
- Slim tiene varios recursos como tutoriales en YouTube y artículos en línea que se pueden utilizar para aprender el marco de trabajo.
- Slim te permite usar los componentes que desees para manejar las características básicas de enrutamiento, ya que cumple con PSR-7.

## Desventajas en comparación con Flight

- Sorprendentemente, Slim no es tan rápido como podrías pensar que sería para un micro marco de trabajo. Consulta las 
  [pruebas de TechEmpower](https://www.techempower.com/benchmarks/#hw=ph&test=fortune&section=data-r22&l=zik073-cn3) 
  para más información.
- Flight está enfocado hacia un desarrollador que busca construir una aplicación web ligera, rápida y fácil de usar.
- Flight no tiene dependencias, mientras que [Slim tiene algunas dependencias](https://github.com/slimphp/Slim/blob/4.x/composer.json) que debes instalar.
- Flight está enfocado hacia la simplicidad y la facilidad de uso.
- Una de las características principales de Flight es que hace todo lo posible por mantener la compatibilidad con versiones anteriores. Slim v3 a v4 fue un cambio rupturista.
- Flight está destinado a desarrolladores que se adentran en el mundo de los marcos de trabajo por primera vez.
- Flight también puede manejar aplicaciones a nivel empresarial, pero no tiene tantos ejemplos y tutoriales como Slim. También requerirá más disciplina por parte del desarrollador para mantener las cosas organizadas y bien estructuradas.
- Flight da al desarrollador más control sobre la aplicación, mientras que Slim puede incluir algo de magia detrás de escena.
- Flight tiene un sencillo [PdoWrapper](/awesome-plugins/pdo-wrapper) que se puede usar para interactuar con tu base de datos. Slim requiere que uses 
  una biblioteca de terceros.
- Flight tiene un plugin de [permisos](/awesome-plugins/permissions) que se puede utilizar para asegurar tu aplicación. Slim requiere que uses 
  una biblioteca de terceros.
- Flight tiene un ORM llamado [active-record](/awesome-plugins/active-record) que se puede utilizar para interactuar con tu base de datos. Slim requiere que uses 
  una biblioteca de terceros.
- Flight tiene una aplicación CLI llamada [runway](/awesome-plugins/runway) que se puede utilizar para ejecutar tu aplicación desde la línea de comandos. Slim no lo tiene.