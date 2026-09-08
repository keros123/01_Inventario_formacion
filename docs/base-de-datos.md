# Base de datos

## Motor y configuración

- **Motor:** Supabase (PostgreSQL) vía REST (`/rest/v1`) y Storage.
- **Acceso:** cliente HTTP en `app/core/Database.php` con la publishable/anon key.
- **Configuración:** `.env` y `config/database.php`.

| Parámetro | Valor |
|-----------|--------|
| url | `https://vcfxiuvtnhrvphypgsgq.supabase.co` |
| anon_key | publishable key (`sb_publishable_…`) |
| storage_bucket | `productos` |
| prefix | `inv_form_` |

Las tablas físicas son `{prefix}{nombre}` en minúsculas (por ejemplo `inv_form_usuarios`). El esquema se crea ejecutando `database/supabase.sql` en el SQL Editor de Supabase. Las fotos se guardan en el bucket `productos`.

## Diagrama entidad-relación

```
┌─────────────┐
│  Categorias │
│ id_categoria│
└──────┬──────┘
       │ 1:N
       ▼
┌─────────────┐         ┌──────────────────┐
│  Inventario │◄────────│ Det_Movimientos  │
│   Codigo    │         │ Codigo_elemento  │
└─────────────┘         └────────┬─────────┘
                                 │ N:1
┌─────────────┐         ┌────────▼─────────┐     ┌──────────────────┐
│   Usuarios  │◄────────│   Movimientos    │────►│ Movimiento_Fotos │
│   Cedula    │         │ id_movimiento    │     └──────────────────┘
└──────┬──────┘         │ id_movimiento_ref│ (auto-referencia)
       │                └────────┬─────────┘
       │                         │
       │    ┌────────────────────┘
       │    │
       ▼    ▼
┌─────────────────────┐         ┌─────────────────┐
│ Solicitudes_Prestamo│────────►│ Det_Solicitudes │
│ id_solicitud        │   1:N   │ Codigo_elemento │
│ id_movimiento (FK)  │         └─────────────────┘
└─────────────────────┘
```

## Tablas

### Usuarios

Responsables del sistema y **cuentadantes** de préstamos (entidad unificada; ya no existe tabla `Cuentadantes`).

| Columna | Tipo | Descripción |
|---------|------|-------------|
| Cedula | VARCHAR(20) PK | Identificador (puede ser numérico o `admin`) |
| Nombres | VARCHAR(150) | Nombre completo |
| Password | VARCHAR(255) | Hash bcrypt |
| Tipo | ENUM | `Admin`, `Usuario` |
| Estado | ENUM | `Activo`, `Inactivo` |

### Categorias

| Columna | Tipo | Descripción |
|---------|------|-------------|
| id_categoria | INT PK AI | |
| Nombre | VARCHAR(100) | |
| Estado | ENUM | `Activo`, `Inactivo`, `Eliminado` |

Categoría por defecto: **General** (semilla SQL).

### Inventario

| Columna | Tipo | Descripción |
|---------|------|-------------|
| Codigo | VARCHAR(50) PK | Código único del elemento |
| Elemento | VARCHAR(150) | Nombre |
| id_categoria | INT FK | → Categorias |
| Descripcion | TEXT | |
| Cantidad | INT | Stock actual (default 0) |
| Fotografia | VARCHAR(255) | Ruta relativa `uploads/inventario/...` |
| Estado | ENUM | `Activo`, `Inactivo`, `Eliminado` |

**Regla:** al crear un elemento, `Cantidad` inicia en **0**. Solo los movimientos de **Ingreso** aumentan el stock.

### Movimientos

Cabecera de cada operación de inventario.

| Columna | Tipo | Descripción |
|---------|------|-------------|
| id_movimiento | INT PK AI | |
| Tipo | ENUM | `Ingreso`, `Prestamo`, `Devolucion`, `Dar_Baja` |
| Consecutivo | INT | Numeración por tipo (documento humano) |
| Fecha | DATE | |
| Cedula_cuentadante | VARCHAR(20) FK | → Usuarios (responsable del préstamo) |
| Descripcion | TEXT | |
| Estado | ENUM | `Activo`, `Inactivo`, `Cerrado` |
| id_movimiento_ref | INT FK | → Movimientos (hijo de préstamo) |

**Jerarquía:** Devolución y Dar de baja apuntan al préstamo padre vía `id_movimiento_ref`.

### Det_Movimientos

Líneas de cada movimiento.

| Columna | Tipo | Descripción |
|---------|------|-------------|
| Consecutivo | INT PK AI | |
| id_movimiento | INT FK | → Movimientos |
| id_Detalle | INT | Número de línea |
| Codigo_elemento | VARCHAR(50) FK | → Inventario |
| Cantidad | INT | |
| Estado | ENUM | `Activo`, `Inactivo` |

### Movimiento_Fotos

Evidencia fotográfica (principalmente dar de baja).

| Columna | Tipo | Descripción |
|---------|------|-------------|
| id_foto | INT PK AI | |
| id_movimiento | INT FK | → Movimientos |
| Ruta | VARCHAR(255) | Ruta del archivo |
| Orden | TINYINT | 1–3 |

### Solicitudes_Prestamo

Flujo de solicitud antes del préstamo formal.

| Columna | Tipo | Descripción |
|---------|------|-------------|
| id_solicitud | INT PK AI | |
| Cedula_solicitante | VARCHAR(20) FK | → Usuarios |
| Fecha_solicitud | DATE | |
| Descripcion | TEXT | |
| Estado | ENUM | `Pendiente`, `Aprobada`, `Rechazada`, `Cancelada` |
| id_movimiento | INT FK | Movimiento generado al aprobar |
| Cedula_aprobador | VARCHAR(20) FK | Admin que resolvió |
| Fecha_resolucion | DATETIME | |
| Motivo_rechazo | TEXT | Obligatorio al rechazar |

### Agenda_Computadores

Reserva pública de portátiles (`inv_form_agenda_computadores`). Visible en calendario para todos los usuarios autenticados.

| Columna | Tipo | Descripción |
|---------|------|-------------|
| id_agenda | INT PK AI | |
| Cedula_solicitante | VARCHAR(20) FK | → Usuarios |
| Nombres | VARCHAR(150) | Nombre informado en el formulario |
| Telefono | VARCHAR(20) | Móvil |
| Fecha_solicitud | DATE | |
| Cantidad_portatiles | INT | ≥ 1 |
| Sede | VARCHAR | `Ternera`, `Emprender` |
| Hora_inicio / Hora_final | TIME | Hora_final > Hora_inicio |
| Ficha_programa | VARCHAR(150) | |
| Estado | VARCHAR | `Pendiente`, `Reprogramada`, `Aprobada`, `Rechazada`, `Cancelada` |
| Cedula_aprobador | VARCHAR(20) FK | Admin que resolvió |
| Fecha_resolucion | TIMESTAMPTZ | |
| Motivo_rechazo | TEXT | |
| Fecha_original, Hora_*_original | | Horario previo a reprogramar |
| Motivo_reprogramacion | TEXT | |

### Det_Solicitudes

Líneas solicitadas (elementos y cantidades).

| Columna | Tipo | Descripción |
|---------|------|-------------|
| id_detalle | INT PK AI | |
| id_solicitud | INT FK | → Solicitudes_Prestamo |
| id_linea | INT | Número de línea |
| Codigo_elemento | VARCHAR(50) FK | → Inventario |
| Cantidad | INT | |

## Reglas de negocio en datos

### Actualización de stock (`InventarioModel::ajustarCantidad`)

| Tipo movimiento | Efecto en Cantidad |
|-----------------|-------------------|
| Ingreso | + cantidad |
| Prestamo | − cantidad |
| Devolucion | + cantidad |
| Dar_Baja | Sin cambio (baja sobre ítems ya prestados) |

### Estado del préstamo

`MovimientoModel::sincronizarEstadoPrestamo()`:

- Calcula ítems pendientes: prestado − (devoluciones + bajas) por `Codigo_elemento`.
- Si pendiente = 0 → `Estado = 'Cerrado'`.

### Consecutivos

Cada `Tipo` de movimiento tiene su propia secuencia de `Consecutivo` (1, 2, 3…). Se usa en documentos imprimibles.

### Eliminación

| Entidad | Estrategia |
|---------|------------|
| Inventario | Soft delete: `Estado = 'Eliminado'` |
| Categorias | Soft delete: `Estado = 'Eliminado'` |
| Usuarios | DELETE físico (con restricciones en controlador) |

## Migraciones

### Runtime: `app/core/Migrator.php`

Se ejecuta en **cada petición** HTTP. Pasos (orden):

1. `applyTablePrefix`
2. `ensureCedulaCuentadante`
3. `ensureMovimientoRef`
4. `ensureConsecutivo`
5. `ensureCategorias`
6. `ensureInventarioCategoria`
7. `ensureMovimientoFotos`
8. `normalizeDarBajaTipos`
9. `removeDeprecatedColumns`
10. `unifyCuentadantesUsuarios`
11. `ensureSolicitudesPrestamo`

Características:

- Idempotente: comprueba columnas/tablas antes de alterar.
- Errores registrados en `error_log`, no detienen la aplicación.
- Permite desplegar código nuevo sobre bases antiguas sin script manual.

### Scripts SQL manuales (`database/migrations/`)

| Archivo | Propósito histórico |
|---------|---------------------|
| `001_add_cedula_cuentadante.sql` | Columna cuentadante en movimientos |
| `002_add_consecutivo_movimientos.sql` | Consecutivo por tipo |
| `003_categorias_reportes_fotos.sql` | Categorías, fotos, reportes |
| `004_solicitudes_unificar_usuarios.sql` | Solicitudes y unificación usuarios |

Referencia para DBA; el Migrator PHP es la fuente de verdad en runtime.

### Instalación inicial

```bash
# Opción A: importar esquema completo
mysql -u root < database/inventario_cm.sql

# Opción B: crear BD vacía y dejar que Migrator cree estructura
# (requiere que las tablas base existan o que Migrator las cree)
```

Script auxiliar: `scripts/run_migration.php` ejecuta Migrator y lista tablas.

## Datos semilla

Incluidos en `inventario_cm.sql`:

**Usuarios**

| Cedula | Tipo | Contraseña |
|--------|------|------------|
| admin | Admin | `123456` |
| 9876543210 | Usuario | `123456` |
| 1122334455 | Usuario | `123456` |

**Inventario de ejemplo:** INV-001, INV-002, INV-003 en categoría General.

## Consultas frecuentes (conceptual)

### Stock disponible para préstamo

Cantidad en `Inventario` para elementos `Activo`, descontando validaciones en `MovimientoModel` al registrar préstamo.

### Préstamos activos

Movimientos `Tipo = 'Prestamo'` con `Estado = 'Activo'`.

### Solicitudes pendientes

`Solicitudes_Prestamo.Estado = 'Pendiente'`.

## Integridad referencial

- FK con `ON UPDATE CASCADE` en la mayoría de relaciones.
- `Det_Movimientos` y `Movimiento_Fotos`: `ON DELETE CASCADE` desde movimiento.
- `id_movimiento_ref`: `ON DELETE SET NULL`.

## Consideraciones de rendimiento

- Migrator en cada request: aceptable en tráfico institucional bajo; en producción de alto tráfico valorar ejecutar migraciones solo en despliegue.
- Índices: PK y FK implícitos; búsquedas de inventario por `Codigo`, `Elemento`, categoría vía consultas en `InventarioModel`.

## Mantenimiento

Scripts en `scripts/` (uso administrativo):

| Script | Uso |
|--------|-----|
| `scripts/reset_system.php` | Reinicio de datos |
| `scripts/update_database_schema.php` | Ajustes de esquema |
| `scripts/fix_database_structure.php` | Correcciones estructurales |
| `scripts/fix_dar_baja.php` | Normalización dar de baja |
| `scripts/restore_missing_elements.php` | Recuperación de elementos |
| `scripts/update_admin.php` | Actualizar cuenta admin |
| `scripts/uppercase_database.php` | Normalización de mayúsculas |

Ejecutar solo con respaldo previo de la base de datos.
