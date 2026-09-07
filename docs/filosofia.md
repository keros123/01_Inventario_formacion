# Filosofía del proyecto

## Propósito

**Inventarios Ambientes de Formación CCyS** es un sistema web institucional para el Centro de Formación de Comercio y Servicios. Su objetivo es registrar, controlar y auditar los elementos físicos de los ambientes de formación: existencias, ingresos, préstamos, devoluciones, bajas y solicitudes de préstamo por parte del personal.

No es un producto comercial genérico. Está pensado para un contexto educativo concreto: pocos usuarios, procesos administrativos claros y trazabilidad de los movimientos.

## Principios de diseño

### Simplicidad sobre complejidad

- PHP puro, sin frameworks ni Composer.
- Sin capas innecesarias: el código debe poder leerse y mantenerse por un desarrollador con conocimientos básicos de PHP y MySQL.
- Preferencia por soluciones directas (SQL preparado, formularios HTML, redirecciones) frente a abstracciones pesadas.

### Trazabilidad del inventario

- La cantidad de un elemento **no se modifica manualmente** en el formulario de edición.
- Todo cambio de stock pasa por un **movimiento** documentado: Ingreso, Préstamo, Devolución o Dar de baja.
- Cada movimiento tiene consecutivo, fecha, responsable (cuentadante) y líneas de detalle.
- Las solicitudes de préstamo, una vez aprobadas, generan un movimiento de préstamo vinculado.

### Roles claros

| Rol | Enfoque |
|-----|---------|
| **Admin** | Operación completa del sistema: inventario, categorías, movimientos, usuarios, reportes y aprobación de solicitudes. |
| **Usuario** | Consulta de inventario y gestión de sus propias solicitudes de préstamo. |

La interfaz y las rutas reflejan estos roles. No hay permisos granulares intermedios.

### Interfaz institucional

- Estilo visual inspirado en aplicaciones de oficina (Microsoft Word / Fluent): sobrio, azul corporativo, sin modas de diseño web.
- Textos en español, tono formal pero claro.
- Iconografía consistente con Bootstrap Icons en menús, botones y tarjetas.

Ver `docs/promp` para replicar el estilo gráfico con otra herramienta o IA.

### Evolución incremental

- El esquema de base de datos evoluciona mediante `Migrator`, que se ejecuta en cada petición de forma idempotente.
- Entidades obsoletas (por ejemplo, la tabla `Cuentadantes`) se migran y unifican sin romper el flujo actual.
- Scripts de mantenimiento en `scripts/` existen para tareas puntuales de DBA o recuperación.

## Qué prioriza el sistema

1. **Integridad del inventario** — el stock refleja la realidad de los movimientos registrados.
2. **Auditoría** — historial de movimientos, documentos imprimibles y reportes exportables a CSV.
3. **Usabilidad administrativa** — tablas con búsqueda, filtros, accesos rápidos en el panel y formularios con líneas dinámicas.
4. **Mantenibilidad** — estructura MVC predecible, convenciones de nombres estables y poca dependencia externa (solo Bootstrap por CDN).

## Qué el sistema deliberadamente no hace

- No es una API REST ni una SPA moderna.
- No implementa permisos por módulo más allá de Admin / Usuario.
- No usa tokens CSRF (entorno controlado institucional; documentar si se expone a internet).
- No incluye notificaciones por correo ni integraciones externas.
- No sustituye un ERP; cubre inventario y préstamos de ambientes de formación.

## Contexto institucional

- **Organización:** Centro de Formación de Comercio y Servicios (CCyS).
- **Usuarios típicos:** administradores del inventario, docentes o personal que solicita préstamo de equipos.
- **Despliegue:** imagen Docker (`docker compose -f docker/compose.yml up`) o LAMP local (XAMPP), carpeta pública en `public/`.

## Convenciones culturales del código

- Nombres de tablas y columnas en español con PascalCase (`Cedula_cuentadante`, `Fecha_solicitud`).
- Mensajes de usuario y etiquetas de formulario en español.
- Patrón POST → Redirect → GET (PRG) con mensajes flash para operaciones de escritura.
- Eliminación lógica (`Estado = 'Eliminado'`) en inventario y categorías; eliminación física solo en usuarios bajo reglas explícitas.

## Documentación relacionada

| Archivo | Contenido |
|---------|-----------|
| `docs/arquitectura.md` | Estructura MVC, capas y componentes |
| `docs/flujo-de-trabajo.md` | Ciclo de peticiones, roles y procesos de negocio |
| `docs/base-de-datos.md` | Esquema, relaciones y reglas de datos |
| `docs/promp` | Prompt para replicar estilo visual y stack tecnológico |
