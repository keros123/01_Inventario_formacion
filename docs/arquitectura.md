# Arquitectura del proyecto

## Visión general

Aplicación web **PHP 8+** con patrón **MVC personalizado**, sin framework ni autoloader. El punto de entrada HTTP es `public/index.php`; la configuración vive en `config/`; la lógica en `app/`.

```
Inventario/
├── index.php                 # Redirige a public/
├── Dockerfile                # Imagen (Render lo busca en la raíz)
├── render.yaml               # Ruta de build para Render
├── .env / .env.example       # Credenciales (no versionar .env)
├── config/
│   ├── app.php               # Nombre, base_url, rutas de uploads
│   ├── env.php               # Carga de variables de entorno
│   └── database.php          # Conexión Supabase (URL, anon key, prefix)
├── public/                   # Document root del servidor web
│   ├── index.php             # Front controller + registro de rutas
│   ├── .htaccess             # Reescritura de URLs
│   ├── css/style.css         # Estilos propios (tema Word/Fluent)
│   ├── js/                   # JavaScript por módulo
│   └── uploads/              # Archivos subidos (fotos)
├── app/
│   ├── core/                 # Núcleo del framework
│   ├── controllers/          # Controladores HTTP
│   ├── models/               # Acceso a datos y reglas de negocio
│   └── views/                # Plantillas PHP
├── database/
│   ├── inventario_cm.sql     # Esquema histórico MySQL + datos semilla
│   ├── postgres_inv.sql      # Dump PostgreSQL/Supabase
│   ├── patch_app_schema.sql  # Ajustes de esquema para la app
│   └── migrations/           # Scripts SQL históricos (manuales)
├── scripts/                  # Mantenimiento CLI (no forman parte del flujo web)
├── docker/                   # Apache, php.ini, entrypoint y Compose
└── docs/                     # Documentación del proyecto
```

## Capas de la aplicación

### 1. Front controller (`public/index.php`)

Orquesta el arranque de cada petición:

1. Carga `Database`, `Migrator`, `Auth`.
2. Inicia sesión (`Auth::startSession()`).
3. Ejecuta migraciones (`Migrator::run()`).
4. Instancia `Router`, registra rutas y despacha la petición.

Todas las rutas HTTP se declaran en un único archivo (~50 rutas GET/POST).

### 2. Router (`app/core/Router.php`)

- Registro: `$router->get('/ruta', 'Controlador@accion')` y `$router->post(...)`.
- `dispatch($uri, $method)`:
  - Elimina el prefijo de `SCRIPT_NAME` (soporte para subcarpetas como `/Inventario/public`).
  - Normaliza la ruta.
  - Resuelve el handler, carga el controlador con `require_once` e invoca el método.
- Respuestas de error: 404 (ruta/controlador/acción no encontrados), 500 (error interno).
- **No hay middleware global** — la autorización se aplica dentro de cada controlador.

### 3. Controller (`app/core/Controller.php`)

Clase base de todos los controladores. Proporciona:

| Método | Función |
|--------|---------|
| `view($vista, $datos, $layout)` | Renderiza `app/views/{vista}.php` dentro del layout |
| `redirect($ruta)` | Redirección con `base_url` |
| `json($datos, $codigo)` | Respuesta JSON |
| `isPost()` | Comprueba método POST |
| `setFlash($tipo, $mensaje)` | Mensaje único en sesión |
| `getFlash()` | Lee y elimina el flash |

**Renderizado de vistas:**

```
Controller::view('inventario/index', $data)
    → ob_start() + require vista
    → $content = buffer
    → require layouts/main.php (inyecta $content)
```

Layouts alternativos: `null` para páginas sueltas (login, documentos de impresión).

### 4. Model (`app/core/Model.php`)

- Obtiene conexión PDO singleton desde `Database::getConnection()`.
- Sin ORM: consultas SQL preparadas en clases hijas.
- La lógica de negocio compleja reside en modelos como `MovimientoModel` y `SolicitudPrestamoModel` (transacciones, validaciones, actualización de stock).

### 5. Database (`app/core/Database.php`)

- Patrón singleton sobre PDO.
- MySQL con `utf8mb4`, `ERRMODE_EXCEPTION`, `FETCH_ASSOC`.
- Prefijo de tablas desde `config/database.php` (`prefix`): `Database::table()`, `Database::sql('{Tabla}')`.

### 6. Auth (`app/core/Auth.php`)

Autenticación basada en sesión PHP:

| Método | Descripción |
|--------|-------------|
| `login($user)` | Guarda `cedula`, `nombres`, `tipo` en `$_SESSION['user']` |
| `logout()` | Destruye la sesión |
| `check()` | ¿Hay sesión activa? |
| `user()` | Datos del usuario o `null` |
| `isAdmin()` | `tipo === 'Admin'` |
| `requireLogin()` | Redirige a `/login` si no hay sesión |
| `requireAdmin()` | Login + redirige a `/dashboard` si no es admin |

### 7. Migrator (`app/core/Migrator.php`)

Migraciones **idempotentes en PHP**, ejecutadas en cada request. Pasos principales:

1. Columnas y tablas faltantes (`Cedula_cuentadante`, `Consecutivo`, `Categorias`, fotos, solicitudes…).
2. Normalización de tipos de movimiento (Dar de baja).
3. Eliminación de columnas obsoletas.
4. Unificación `Cuentadantes` → `Usuarios`.

Los archivos en `database/migrations/*.sql` son scripts manuales históricos; el runtime usa el Migrator PHP.

## Controladores y responsabilidades

| Controlador | Autenticación | Modelos principales |
|-------------|---------------|---------------------|
| `AuthController` | Público (login) | `UsuarioModel` |
| `DashboardController` | Login | Varios (estadísticas) |
| `InventarioController` | Login; escritura Admin | `InventarioModel`, `CategoriaModel` |
| `MovimientosController` | Admin | `MovimientoModel`, `InventarioModel`, `CuentadanteModel` |
| `SolicitudesController` | Login; acciones admin según método | `SolicitudPrestamoModel`, `InventarioModel` |
| `AgendaComputadoresController` | Login; escritura usuario, resolución Admin | `AgendaComputadorModel` |
| `UsuariosController` | Admin | `UsuarioModel` |
| `CategoriasController` | Admin | `CategoriaModel` |
| `ReportesController` | Admin | Varios |
| `CuentadantesController` | Admin | Redirige a usuarios (legacy) |

## Modelos

| Modelo | Tablas |
|--------|--------|
| `InventarioModel` | `Inventario`, joins con `Categorias` |
| `MovimientoModel` | `Movimientos`, `Det_Movimientos`, `Movimiento_Fotos` |
| `UsuarioModel` | `Usuarios` |
| `CategoriaModel` | `Categorias` |
| `SolicitudPrestamoModel` | `Solicitudes_Prestamo`, `Det_Solicitudes` |
| `AgendaComputadorModel` | `Agenda_Computadores` (`inv_form_agenda_computadores`) |
| `CuentadanteModel` | Fachada sobre `UsuarioModel` (usuarios activos para préstamo) |

## Vistas

- **Layout principal:** `app/views/layouts/main.php` — header, sidebar según rol, contenido, footer.
- **Por módulo:** carpetas `inventario/`, `movimientos/`, `solicitudes/`, etc.
- **Documentos imprimibles:** vistas sin layout en `movimientos/*-documento.php`.
- **Escape XSS:** `htmlspecialchars()` en toda salida dinámica.

## Frontend

| Tecnología | Uso |
|------------|-----|
| Bootstrap 5.3.3 (CDN) | Grid, componentes, modales, alertas |
| Bootstrap Icons 1.11.3 | Iconografía en navegación y acciones |
| `public/css/style.css` | Tema corporativo (variables CSS `--word-*`) |
| JavaScript vanilla | `app.js` + scripts por módulo (`ingreso.js`, `prestamo.js`, etc.) |

Sin npm, webpack ni frameworks JS.

## Archivos estáticos y uploads

Configuración en `config/app.php`:

- `upload_dir` → `public/uploads/inventario/` (fotos de elementos).
- `upload_dar_baja` → `public/uploads/dar-baja/` (evidencia fotográfica de bajas).

Formatos permitidos: jpg, jpeg, png, gif, webp.

## Diagrama de capas

```
┌─────────────────────────────────────────────────────────┐
│  Navegador (HTML + Bootstrap + JS)                      │
└───────────────────────────┬─────────────────────────────┘
                            │ HTTP
┌───────────────────────────▼─────────────────────────────┐
│  public/index.php  →  Router  →  Controller             │
└───────────────────────────┬─────────────────────────────┘
                            │
         ┌──────────────────┼──────────────────┐
         ▼                  ▼                  ▼
    Auth (sesión)      Model (PDO)        view() → PHP
         │                  │                  │
         │                  ▼                  ▼
         │             MySQL/MariaDB      layouts/main.php
         └──────────────────────────────────────────────┘
```

## Convenciones de nombres

| Elemento | Convención | Ejemplo |
|----------|------------|---------|
| Clases PHP | PascalCase + sufijo | `InventarioController` |
| Archivos PHP | Igual que la clase | `InventarioController.php` |
| Rutas URL | minúsculas, kebab | `/movimientos/ingreso/store` |
| Vistas | carpeta/archivo minúsculas | `inventario/index` |
| Tablas/columnas DB | PascalCase español | `Cedula_cuentadante` |
| Campos de formulario | snake_case español | `linea_codigo[]` |

## Dependencias externas

- **PHP** con extensiones PDO MySQL y sesiones.
- **MySQL o MariaDB**.
- **Servidor web** con soporte para `.htaccess` (Apache) o equivalente.
- **CDN jsDelivr** para Bootstrap (requiere conexión a internet en cliente).

## Scripts de mantenimiento (`scripts/`)

No forman parte del flujo web. Herramientas operativas:

- `scripts/run_migration.php` — ejecuta Migrator manualmente.
- `scripts/reset_system.php`, `scripts/update_database_schema.php`, `scripts/fix_database_structure.php`, etc.

Usar solo en entornos de desarrollo o bajo supervisión del administrador.

## Configuración

**`config/app.php`**

```php
'name'       => 'Inventarios Ambientes de Formación CCyS',
'base_url'   => '/Inventario/public',
'upload_dir' => .../public/uploads/inventario/,
```

**`config/database.php`**

```php
'host', 'dbname', 'username', 'password', 'charset' => 'utf8mb4', 'prefix' => 'inv_form_'
```

> **Nota:** El SQL semilla (`database/inventario_cm.sql`) crea la base `Inventario_cm`; la configuración puede apuntar a `inventario_base`. Las tablas usan el prefijo `prefix`. Ajustar según el entorno de despliegue.
