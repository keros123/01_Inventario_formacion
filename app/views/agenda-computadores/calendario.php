<?php
$meses = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
];
$diasCortos = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];

$porFecha = [];
foreach ($solicitudes as $s) {
    $porFecha[$s['Fecha_solicitud']][] = $s;
}
foreach ($porFecha as &$lista) {
    usort($lista, static function (array $a, array $b): int {
        return strcmp((string) ($a['Hora_inicio'] ?? ''), (string) ($b['Hora_inicio'] ?? ''));
    });
}
unset($lista);

$hoy = date('Y-m-d');
$baseUrl = $config['base_url'];
$esSemana = ($vista ?? 'mes') === 'semana';

$primerDia = mktime(0, 0, 0, $month, 1, $year);
$diasMes = (int) date('t', $primerDia);
$inicioSemana = (int) date('N', $primerDia);
$mesAnterior = $month === 1 ? 12 : $month - 1;
$anioAnterior = $month === 1 ? $year - 1 : $year;
$mesSiguiente = $month === 12 ? 1 : $month + 1;
$anioSiguiente = $month === 12 ? $year + 1 : $year;

$lunesTs = strtotime($inicio);
$semanaAnterior = date('Y-m-d', strtotime('-7 days', $lunesTs));
$semanaSiguiente = date('Y-m-d', strtotime('+7 days', $lunesTs));
$tituloSemana = date('d/m/Y', $lunesTs) . ' — ' . date('d/m/Y', strtotime($fin));

$queryMes = static function (int $y, int $m) use ($baseUrl): string {
    return $baseUrl . '/agenda-computadores?vista=mes&year=' . $y . '&month=' . $m;
};
$querySemana = static function (string $fecha) use ($baseUrl): string {
    return $baseUrl . '/agenda-computadores?vista=semana&fecha=' . urlencode($fecha);
};
?>

<div class="page-header d-flex justify-content-between align-items-start flex-wrap gap-2">
    <div>
        <h2 class="page-title">Agenda computadores</h2>
        <p class="page-subtitle">
            Calendario público de portátiles. Doble clic en un día para crear una solicitud.
            <?php if (Auth::isAdmin()): ?>
            Los administradores también pueden aprobar, rechazar o reprogramar.
            <?php endif; ?>
        </p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="<?= $baseUrl ?>/agenda-computadores/nueva" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg"></i> Nueva solicitud
        </a>
        <a href="<?= $baseUrl ?>/agenda-computadores/mis" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-list-check"></i> Mis solicitudes
        </a>
        <?php if (Auth::isAdmin()): ?>
        <a href="<?= $baseUrl ?>/agenda-computadores/pendientes" class="btn btn-warning btn-sm">
            <i class="bi bi-hourglass-split"></i> Pendientes de aprobación
        </a>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($flash)): ?>
<div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show" role="alert">
    <?= htmlspecialchars($flash['message']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card calendario-card">
    <div class="card-header bg-light d-flex justify-content-between align-items-center flex-wrap gap-2 py-2">
        <div class="d-flex align-items-center gap-2">
            <?php if ($esSemana): ?>
            <a href="<?= $querySemana($semanaAnterior) ?>" class="btn btn-sm btn-outline-secondary" title="Semana anterior">
                <i class="bi bi-chevron-left"></i>
            </a>
            <strong class="calendario-titulo"><?= htmlspecialchars($tituloSemana) ?></strong>
            <a href="<?= $querySemana($semanaSiguiente) ?>" class="btn btn-sm btn-outline-secondary" title="Semana siguiente">
                <i class="bi bi-chevron-right"></i>
            </a>
            <?php else: ?>
            <a href="<?= $queryMes($anioAnterior, $mesAnterior) ?>" class="btn btn-sm btn-outline-secondary" title="Mes anterior">
                <i class="bi bi-chevron-left"></i>
            </a>
            <strong class="calendario-titulo"><?= $meses[$month] ?> <?= $year ?></strong>
            <a href="<?= $queryMes($anioSiguiente, $mesSiguiente) ?>" class="btn btn-sm btn-outline-secondary" title="Mes siguiente">
                <i class="bi bi-chevron-right"></i>
            </a>
            <?php endif; ?>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <div class="btn-group btn-group-sm" role="group" aria-label="Vista del calendario">
                <a href="<?= $queryMes($year, $month) ?>"
                   class="btn <?= $esSemana ? 'btn-outline-secondary' : 'btn-primary' ?>">Mes</a>
                <a href="<?= $querySemana($esSemana ? $inicio : sprintf('%04d-%02d-%02d', $year, $month, 1)) ?>"
                   class="btn <?= $esSemana ? 'btn-primary' : 'btn-outline-secondary' ?>">Semana</a>
            </div>
            <div class="calendario-leyenda d-flex flex-wrap gap-2 small">
                <span><span class="calendario-dot estado-pendiente"></span> Pendiente</span>
                <span><span class="calendario-dot estado-reprogramada"></span> Reprogramada</span>
                <span><span class="calendario-dot estado-aprobada"></span> Aprobada</span>
                <span><span class="calendario-dot estado-rechazada"></span> Rechazada</span>
                <span><span class="calendario-dot estado-cancelada"></span> Cancelada</span>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="calendario-grid<?= $esSemana ? ' calendario-semana' : '' ?>" id="calendarioAgendaComputadores"
             data-base-url="<?= htmlspecialchars($baseUrl) ?>"
             data-puede-crear="<?= $puedeCrear ? '1' : '0' ?>">
            <?php foreach ($diasCortos as $dia): ?>
            <div class="calendario-dia-header"><?= $dia ?></div>
            <?php endforeach; ?>

            <?php if ($esSemana): ?>
                <?php for ($i = 0; $i < 7; $i++):
                    $fecha = date('Y-m-d', strtotime('+' . $i . ' days', $lunesTs));
                    $eventos = $porFecha[$fecha] ?? [];
                    $esHoy = $fecha === $hoy;
                    $numDia = (int) date('j', strtotime($fecha));
                ?>
            <div class="calendario-celda<?= $esHoy ? ' calendario-celda-hoy' : '' ?><?= $puedeCrear ? ' calendario-celda-clic' : '' ?>"
                 data-fecha="<?= $fecha ?>"
                 title="<?= $puedeCrear ? 'Doble clic para nueva solicitud' : '' ?>">
                <div class="calendario-numero"><?= $numDia ?></div>
                <div class="calendario-eventos">
                    <?php foreach ($eventos as $ev): ?>
                    <a href="<?= $baseUrl ?>/agenda-computadores/ver?id=<?= (int) $ev['id_agenda'] ?>"
                       class="calendario-evento estado-<?= strtolower($ev['Estado']) ?>"
                       title="#<?= (int) $ev['id_agenda'] ?> — <?= htmlspecialchars($ev['Nombres']) ?> — <?= htmlspecialchars($ev['Sede']) ?>">
                        <span class="calendario-evento-solicitante">
                            <?= htmlspecialchars($ev['Hora_inicio_fmt']) ?>–<?= htmlspecialchars($ev['Hora_final_fmt']) ?>
                            · <?= htmlspecialchars($ev['Sede']) ?>
                        </span>
                        <span class="calendario-evento-estado">
                            <?= htmlspecialchars($ev['Nombres']) ?> · <?= (int) $ev['Cantidad_portatiles'] ?> port.
                        </span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
                <?php endfor; ?>
            <?php else: ?>
                <?php for ($i = 1; $i < $inicioSemana; $i++): ?>
            <div class="calendario-celda calendario-celda-vacia"></div>
                <?php endfor; ?>

                <?php for ($dia = 1; $dia <= $diasMes; $dia++):
                    $fecha = sprintf('%04d-%02d-%02d', $year, $month, $dia);
                    $eventos = $porFecha[$fecha] ?? [];
                    $esHoy = $fecha === $hoy;
                ?>
            <div class="calendario-celda<?= $esHoy ? ' calendario-celda-hoy' : '' ?><?= $puedeCrear ? ' calendario-celda-clic' : '' ?>"
                 data-fecha="<?= $fecha ?>"
                 title="<?= $puedeCrear ? 'Doble clic para nueva solicitud' : '' ?>">
                <div class="calendario-numero"><?= $dia ?></div>
                <div class="calendario-eventos">
                    <?php foreach ($eventos as $ev): ?>
                    <a href="<?= $baseUrl ?>/agenda-computadores/ver?id=<?= (int) $ev['id_agenda'] ?>"
                       class="calendario-evento estado-<?= strtolower($ev['Estado']) ?>"
                       title="#<?= (int) $ev['id_agenda'] ?> — <?= htmlspecialchars($ev['Nombres']) ?> — <?= htmlspecialchars($ev['Sede']) ?>">
                        <span class="calendario-evento-solicitante">
                            <?= htmlspecialchars($ev['Hora_inicio_fmt']) ?> <?= htmlspecialchars($ev['Nombres']) ?>
                        </span>
                        <span class="calendario-evento-estado">
                            <?= htmlspecialchars($ev['Sede']) ?> · <?= (int) $ev['Cantidad_portatiles'] ?>
                        </span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
                <?php endfor; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if ($puedeCrear): ?>
<script src="<?= $baseUrl ?>/js/agenda-computadores.js"></script>
<?php endif; ?>
