<?php

require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Migrator.php';
require_once __DIR__ . '/../app/core/Auth.php';

Auth::startSession();
Migrator::run();

require_once __DIR__ . '/../app/core/Router.php';

$router = new Router();

$router->get('/', 'DashboardController@index');
$router->get('/login', 'AuthController@login');
$router->post('/login', 'AuthController@login');
$router->get('/logout', 'AuthController@logout');
$router->get('/dashboard', 'DashboardController@index');

$router->get('/inventario', 'InventarioController@index');
$router->get('/inventario/export-csv', 'InventarioController@exportCsv');
$router->get('/inventario/create', 'InventarioController@create');
$router->post('/inventario/store', 'InventarioController@store');
$router->get('/inventario/edit', 'InventarioController@edit');
$router->post('/inventario/update', 'InventarioController@update');
$router->post('/inventario/delete', 'InventarioController@delete');

$router->get('/movimientos', 'MovimientosController@index');
$router->get('/movimientos/export-csv', 'MovimientosController@exportCsv');
$router->get('/movimientos/ingreso', 'MovimientosController@ingreso');
$router->post('/movimientos/ingreso/store', 'MovimientosController@ingresoStore');
$router->get('/movimientos/ingreso/documento', 'MovimientosController@ingresoDocumento');
$router->get('/movimientos/prestamo/documento', 'MovimientosController@prestamoDocumento');
$router->get('/movimientos/devolucion/documento', 'MovimientosController@devolucionDocumento');
$router->get('/movimientos/prestamo', 'MovimientosController@prestamo');
$router->post('/movimientos/prestamo/store', 'MovimientosController@prestamoStore');
$router->get('/prestamos', 'MovimientosController@listPrestamos');
$router->post('/prestamos/estado', 'MovimientosController@updateEstadoPrestamo');
$router->get('/prestamos/devolucion', 'MovimientosController@devolucion');
$router->post('/prestamos/devolucion/store', 'MovimientosController@devolucionStore');
$router->post('/prestamos/devolucion-total', 'MovimientosController@devolucionTotal');
$router->get('/prestamos/dar-de-baja', 'MovimientosController@darDeBaja');
$router->post('/prestamos/dar-de-baja/store', 'MovimientosController@darDeBajaStore');
$router->get('/prestamos/dar-de-baja/documento', 'MovimientosController@darDeBajaDocumento');

$router->get('/categorias', 'CategoriasController@index');
$router->get('/categorias/create', 'CategoriasController@create');
$router->post('/categorias/store', 'CategoriasController@store');
$router->get('/categorias/edit', 'CategoriasController@edit');
$router->post('/categorias/update', 'CategoriasController@update');
$router->post('/categorias/delete', 'CategoriasController@delete');

$router->get('/reportes', 'ReportesController@index');
$router->get('/reportes/export-csv', 'ReportesController@exportCsv');

$router->get('/solicitudes', 'SolicitudesController@index');
$router->get('/solicitudes/nueva', 'SolicitudesController@nueva');
$router->post('/solicitudes/store', 'SolicitudesController@store');
$router->get('/solicitudes/mis', 'SolicitudesController@mis');
$router->get('/solicitudes/pendientes', 'SolicitudesController@pendientes');
$router->get('/solicitudes/ver', 'SolicitudesController@ver');
$router->post('/solicitudes/aprobar', 'SolicitudesController@aprobar');
$router->post('/solicitudes/rechazar', 'SolicitudesController@rechazar');
$router->post('/solicitudes/cancelar', 'SolicitudesController@cancelar');

$router->get('/agenda-computadores', 'AgendaComputadoresController@index');
$router->get('/agenda-computadores/nueva', 'AgendaComputadoresController@nueva');
$router->post('/agenda-computadores/store', 'AgendaComputadoresController@store');
$router->get('/agenda-computadores/mis', 'AgendaComputadoresController@mis');
$router->get('/agenda-computadores/pendientes', 'AgendaComputadoresController@pendientes');
$router->get('/agenda-computadores/ver', 'AgendaComputadoresController@ver');
$router->post('/agenda-computadores/aprobar', 'AgendaComputadoresController@aprobar');
$router->post('/agenda-computadores/rechazar', 'AgendaComputadoresController@rechazar');
$router->post('/agenda-computadores/reprogramar', 'AgendaComputadoresController@reprogramar');
$router->post('/agenda-computadores/cancelar', 'AgendaComputadoresController@cancelar');

$router->get('/cuentadantes', 'CuentadantesController@index');
$router->get('/cuentadantes/create', 'CuentadantesController@create');
$router->post('/cuentadantes/store', 'CuentadantesController@store');
$router->get('/cuentadantes/edit', 'CuentadantesController@edit');
$router->post('/cuentadantes/update', 'CuentadantesController@update');
$router->post('/cuentadantes/delete', 'CuentadantesController@delete');

$router->get('/usuarios', 'UsuariosController@index');
$router->get('/usuarios/create', 'UsuariosController@create');
$router->post('/usuarios/store', 'UsuariosController@store');
$router->get('/usuarios/edit', 'UsuariosController@edit');
$router->post('/usuarios/update', 'UsuariosController@update');
$router->post('/usuarios/delete', 'UsuariosController@delete');

$uri = $_SERVER['REQUEST_URI'] ?? '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$router->dispatch($uri, $method);
