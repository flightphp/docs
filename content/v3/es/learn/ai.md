# IA y Experiencia del Desarrollador con Flight

## Resumen

Flight facilita potenciar tus proyectos PHP con herramientas impulsadas por IA y flujos de trabajo modernos para desarrolladores. Con comandos integrados para conectar con proveedores de LLM (Modelo de Lenguaje Grande) y generar instrucciones de codificación específicas del proyecto con IA, Flight te ayuda a ti y a tu equipo a sacar el máximo provecho de asistentes de IA como GitHub Copilot, Cursor y Windsurf.

## Comprensión

Los asistentes de codificación con IA son más útiles cuando comprenden el contexto, las convenciones y los objetivos de tu proyecto. Los ayudantes de IA de Flight te permiten:
- Conectar tu proyecto con proveedores populares de LLM (OpenAI, Grok, Claude, etc.)
- Generar y actualizar instrucciones específicas del proyecto para herramientas de IA, para que todos reciban ayuda consistente y relevante
- Mantener a tu equipo alineado y productivo, con menos tiempo dedicado a explicar el contexto

Estas características están integradas en el CLI principal de Flight y en el proyecto inicial oficial [flightphp/skeleton](https://github.com/flightphp/skeleton).

## Uso Básico

### 1. Configuración de Credenciales de LLM

El comando `ai:init` te guía a través de la conexión de tu proyecto con un proveedor de LLM.

```bash
php runway ai:init
```

Se te pedirá que:
- Elijas tu proveedor (OpenAI, Grok, Claude, etc.)
- Ingreses tu clave API
- Establezcas la URL base y el nombre del modelo

Esto crea un archivo `.runway-creds.json` en la raíz de tu proyecto (y asegura que esté en tu `.gitignore`).

**Ejemplo:**
```
¡Bienvenido a AI Init!
¿Cuál API de LLM quieres usar? [1] openai, [2] grok, [3] claude: 1
Ingresa la URL base para la API de LLM [https://api.openai.com]:
Ingresa tu clave API para openai: sk-...
Ingresa el nombre del modelo que quieres usar (p.ej. gpt-4, claude-3-opus, etc) [gpt-4o]:
Credenciales guardadas en .runway-creds.json
```

### 2. Generación de Instrucciones Específicas del Proyecto para IA

El comando `ai:generate-instructions` te ayuda a crear o actualizar instrucciones para asistentes de codificación con IA, adaptadas a tu proyecto.

```bash
php runway ai:generate-instructions
```

Responderás algunas preguntas sobre tu proyecto (descripción, base de datos, plantillas, seguridad, tamaño del equipo, etc.). Flight usa tu proveedor de LLM para generar instrucciones y luego las escribe en:
- `.github/copilot-instructions.md` (para GitHub Copilot)
- `.cursor/rules/project-overview.mdc` (para Cursor)
- `.windsurfrules` (para Windsurf)

**Ejemplo:**
```
Por favor describe para qué es tu proyecto? Mi increíble API
¿Qué base de datos planeas usar? MySQL
¿Qué motor de plantillas HTML planeas usar (si aplica)? latte
¿Es la seguridad un elemento importante de este proyecto? (y/n) y
...
Instrucciones de IA actualizadas exitosamente.
```

Ahora, tus herramientas de IA darán sugerencias más inteligentes y relevantes basadas en las necesidades reales de tu proyecto.

## Uso Avanzado

- Puedes personalizar la ubicación de tus archivos de credenciales o instrucciones usando opciones de comando (ver `--help` para cada comando).
- Los ayudantes de IA están diseñados para funcionar con cualquier proveedor de LLM que soporte APIs compatibles con OpenAI.
- Si quieres actualizar tus instrucciones a medida que tu proyecto evoluciona, solo vuelve a ejecutar `ai:generate-instructions` y responde los prompts nuevamente.

## Ver También

- [Flight Skeleton](https://github.com/flightphp/skeleton) – El inicial oficial con integración de IA
- [Runway CLI](/awesome-plugins/runway) – Más sobre la herramienta CLI que impulsa estos comandos

## Solución de Problemas

- Si ves "Missing .runway-creds.json", ejecuta `php runway ai:init` primero.
- Asegúrate de que tu clave API sea válida y tenga acceso al modelo seleccionado.
- Si las instrucciones no se actualizan, verifica los permisos de archivos en tu directorio de proyecto.

## Registro de Cambios

- v3.16.0 – Agregados comandos CLI `ai:init` y `ai:generate-instructions` para integración de IA.