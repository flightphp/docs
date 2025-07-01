# IA y Experiencia del Desarrollador con Flight

Flight se trata de ayudarte a construir más rápido, más inteligente y con menos fricciones, especialmente cuando se trabaja con herramientas impulsadas por IA y flujos de trabajo de desarrollo modernos. Esta página cubre cómo Flight facilita superpotenciar tus proyectos con IA, y cómo comenzar con los nuevos ayudantes de IA integrados directamente en el framework y el proyecto esqueleto.

---

## Preparado para IA por Defecto: El Proyecto Esqueleto

El starter oficial [flightphp/skeleton](https://github.com/flightphp/skeleton) ahora incluye instrucciones y configuración para asistentes de codificación populares impulsados por IA:

- **GitHub Copilot**
- **Cursor**
- **Windsurf**

Estas herramientas vienen preconfiguradas con instrucciones específicas del proyecto, por lo que tú y tu equipo pueden obtener la ayuda más relevante y consciente del contexto mientras codifican. Esto significa:

- Los asistentes de IA entienden los objetivos, estilo y requisitos de tu proyecto
- Orientación consistente para todos los colaboradores
- Menos tiempo dedicado a explicar el contexto, más tiempo para construir

> **¿Por qué esto importa?**
>
> Cuando tus herramientas de IA conocen la intención y convenciones de tu proyecto, pueden ayudarte a estructurar características, refactorizar código y evitar errores comunes, lo que te hace (y a tu equipo) más productivo desde el primer día.

---

## Nuevos Comandos de IA en el Núcleo de Flight

_v3.16.0+_

El núcleo de Flight ahora incluye dos poderosos comandos de línea de comandos (CLI) para ayudarte a configurar y guiar tu proyecto con IA:

### 1. `ai:init` — Conéctate a tu Proveedor de LLM Favorito

Este comando te guía a través de la configuración de credenciales para un proveedor de LLM (Modelo de Lenguaje Grande), como OpenAI, Grok o Anthropic (Claude).

**Ejemplo:**
```bash
php runway ai:init
```
Serás invitado a seleccionar tu proveedor, ingresar tu clave de API y elegir un modelo. Esto facilita conectar tu proyecto a los servicios de IA más recientes, sin configuración manual requerida.

### 2. `ai:generate-instructions` — Instrucciones de Codificación con Conciencia del Proyecto

Este comando te ayuda a crear o actualizar instrucciones específicas del proyecto para asistentes de codificación de IA. Te hace unas preguntas simples sobre tu proyecto (como para qué sirve, qué base de datos usas, tamaño del equipo, etc.), luego usa tu proveedor de LLM para generar instrucciones personalizadas.

Si ya tienes instrucciones, se actualizarán para reflejar las respuestas que proporciones. Estas instrucciones se escriben automáticamente en:
- `.github/copilot-instructions.md` (para GitHub Copilot)
- `.cursor/rules/project-overview.mdc` (para Cursor)
- `.windsurfrules` (para Windsurf)

**Ejemplo:**
```bash
php runway ai:generate-instructions
```

> **¿Por qué esto es útil?**
>
> Con instrucciones actualizadas y específicas del proyecto, tus herramientas de IA pueden:
> - Ofrecer mejores sugerencias de código
> - Entender las necesidades únicas de tu proyecto
> - Ayudar a incorporar nuevos colaboradores más rápido
> - Reducir fricciones y confusiones a medida que tu proyecto evoluciona

---

## No Solo para Construir Aplicaciones de IA

Aunque puedes usar Flight para construir características impulsadas por IA (como chatbots, APIs inteligentes o integraciones), el verdadero poder radica en cómo Flight te ayuda a trabajar mejor con herramientas de IA como desarrollador. Se trata de:

- **Aumentar la productividad** con codificación asistida por IA
- **Mantener a tu equipo alineado** con instrucciones compartidas y en evolución
- **Facilitar la incorporación** de nuevos colaboradores
- **Permitirte enfocarte en construir**, no en luchar contra tus herramientas

---

## Aprende Más y Comienza

- Ver el [Esqueleto de Flight](https://github.com/flightphp/skeleton) para un starter listo para usar y amigable con IA
- Consulta el resto de la [documentación de Flight](/learn) para consejos sobre cómo construir aplicaciones PHP rápidas y modernas