<?php

$meses = [

    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',

    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',

    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',

];

$porFecha = [];

foreach ($solicitudes as $s) {

    $porFecha[$s['Fecha_solicitud']][] = $s;

}

$primerDia = mktime(0, 0, 0, $month, 1, $year);

$diasMes = (int) date('t', $primerDia);

$inicioSemana = (int) date('N', $primerDia);

$mesAnterior = $month === 1 ? 12 : $month - 1;

$anioAnterior = $month === 1 ? $year - 1 : $year;

$mesSiguiente = $month === 12 ? 1 : $month + 1;

$anioSiguiente = $month === 12 ? $year + 1 : $year;

$hoy = date('Y-m-d');

$baseUrl = $config['base_url'];

?>



<div class="page-header d-flex justify-content-between align-items-start flex-wrap gap-2">

    <div>

        <h2 class="page-title">Solicitudes de préstamo</h2>

        <p class="page-subtitle">

            <?= $puedeCrear

                ? 'Calendario de solicitudes. Doble clic en un día para crear una nueva solicitud.'

                : 'Calendario de solicitudes de préstamo' ?>

        </p>

    </div>

    <div class="d-flex flex-wrap gap-2">

        <?php if ($puedeCrear): ?>

        <a href="<?= $baseUrl ?>/solicitudes/nueva" class="btn btn-primary btn-sm">

            <i class="bi bi-plus-lg"></i> Nueva solicitud

        </a>

        <a href="<?= $baseUrl ?>/solicitudes/mis" class="btn btn-outline-primary btn-sm">

            <i class="bi bi-list-check"></i> Mis solicitudes

        </a>

        <?php elseif (Auth::isAdmin()): ?>

        <a href="<?= $baseUrl ?>/solicitudes/pendientes" class="btn btn-warning btn-sm">

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

            <a href="<?= $baseUrl ?>/solicitudes?year=<?= $anioAnterior ?>&month=<?= $mesAnterior ?>"

               class="btn btn-sm btn-outline-secondary" title="Mes anterior">

                <i class="bi bi-chevron-left"></i>

            </a>

            <strong class="calendario-titulo"><?= $meses[$month] ?> <?= $year ?></strong>

            <a href="<?= $baseUrl ?>/solicitudes?year=<?= $anioSiguiente ?>&month=<?= $mesSiguiente ?>"

               class="btn btn-sm btn-outline-secondary" title="Mes siguiente">

                <i class="bi bi-chevron-right"></i>

            </a>

        </div>

        <div class="calendario-leyenda d-flex flex-wrap gap-2 small">

            <span><span class="calendario-dot estado-pendiente"></span> Pendiente</span>

            <span><span class="calendario-dot estado-aprobada"></span> Aprobada</span>

            <span><span class="calendario-dot estado-rechazada"></span> Rechazada</span>

            <span><span class="calendario-dot estado-cancelada"></span> Cancelada</span>

        </div>

    </div>

    <div class="card-body p-0">

        <div class="calendario-grid" id="calendarioSolicitudes" data-base-url="<?= htmlspecialchars($baseUrl) ?>"

             data-puede-crear="<?= $puedeCrear ? '1' : '0' ?>">

            <?php foreach (['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'] as $dia): ?>

            <div class="calendario-dia-header"><?= $dia ?></div>

            <?php endforeach; ?>



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

                    <a href="<?= $baseUrl ?>/solicitudes/ver?id=<?= (int) $ev['id_solicitud'] ?>"

                       class="calendario-evento estado-<?= strtolower($ev['Estado']) ?>"

                       title="#<?= (int) $ev['id_solicitud'] ?> — <?= htmlspecialchars($ev['Solicitante']) ?> — <?= htmlspecialchars($ev['Estado']) ?>">

                        <span class="calendario-evento-solicitante"><?= htmlspecialchars($ev['Solicitante']) ?></span>

                        <span class="calendario-evento-estado"><?= htmlspecialchars($ev['Estado']) ?></span>

                    </a>

                    <?php endforeach; ?>

                </div>

            </div>

            <?php endfor; ?>

        </div>

    </div>

</div>



<?php if ($puedeCrear): ?>

<script src="<?= $baseUrl ?>/js/solicitudes-calendario.js"></script>

<?php endif; ?>

