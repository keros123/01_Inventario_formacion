window.ModalBusqueda = (function () {
    function normalizar(texto) {
        return String(texto || '').toLowerCase().trim();
    }

    function filtrar(items, termino, campos) {
        const term = normalizar(termino);
        if (!term) {
            return items.slice();
        }

        return items.filter(function (item) {
            return campos.some(function (campo) {
                return normalizar(item[campo]).includes(term);
            });
        });
    }

    function botonSeleccionar() {
        return '<button type="button" class="btn btn-sm btn-success" title="Seleccionar" aria-label="Seleccionar">' +
            '<i class="bi bi-check-lg"></i></button>';
    }

    function crearFilaElemento(item, opciones) {
        const tr = document.createElement('tr');
        tr.className = 'modal-busqueda-row-selectable';
        tr.dataset.codigo = item.codigo;
        tr.innerHTML =
            '<td><code>' + escapeHtml(item.codigo) + '</code></td>' +
            '<td>' + escapeHtml(item.elemento) + '</td>' +
            '<td>' + escapeHtml(item.categoria || '—') + '</td>' +
            '<td class="text-center">' + item.stock + '</td>';

        tr.addEventListener('click', function () {
            if (typeof opciones.onSeleccionar === 'function') {
                opciones.onSeleccionar(item, tr);
            }
        });

        tr.addEventListener('dblclick', function (event) {
            event.preventDefault();
            if (typeof opciones.onAgregar === 'function') {
                opciones.onAgregar(item);
            }
        });

        return tr;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function renderFilas(tbody, items, renderRow, mensajeVacio) {
        tbody.innerHTML = '';

        if (!items.length) {
            const tr = document.createElement('tr');
            tr.innerHTML = '<td colspan="99" class="modal-busqueda-empty">' + escapeHtml(mensajeVacio) + '</td>';
            tbody.appendChild(tr);
            return;
        }

        items.forEach(function (item) {
            tbody.appendChild(renderRow(item));
        });
    }

    function bindBusquedaEnVivo(input, onBuscar, delayMs) {
        let timer = null;
        const delay = delayMs ?? 200;

        input.addEventListener('input', function () {
            clearTimeout(timer);
            timer = setTimeout(function () {
                onBuscar(input.value.trim());
            }, delay);
        });
    }

    function mostrarEstadoInicial(tbody, mensaje) {
        renderFilas(tbody, [], function () {}, mensaje);
    }

    return {
        filtrar: filtrar,
        renderFilas: renderFilas,
        bindBusquedaEnVivo: bindBusquedaEnVivo,
        mostrarEstadoInicial: mostrarEstadoInicial,
        escapeHtml: escapeHtml,
        botonSeleccionar: botonSeleccionar,
        crearFilaElemento: crearFilaElemento,
    };
})();
