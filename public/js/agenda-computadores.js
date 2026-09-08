(function () {
    const calendario = document.getElementById('calendarioAgendaComputadores');
    if (!calendario || calendario.dataset.puedeCrear !== '1') {
        return;
    }

    const baseUrl = calendario.dataset.baseUrl || '';

    calendario.querySelectorAll('.calendario-celda-clic').forEach(function (celda) {
        celda.addEventListener('dblclick', function (e) {
            if (e.target.closest('.calendario-evento')) {
                return;
            }
            const fecha = celda.dataset.fecha;
            if (fecha) {
                window.location.href = baseUrl + '/agenda-computadores/nueva?fecha=' + encodeURIComponent(fecha);
            }
        });
    });
})();
