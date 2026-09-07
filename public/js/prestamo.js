document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formPrestamo');
    if (!form || typeof bootstrap === 'undefined' || !window.ModalBusqueda) {
        return;
    }

    const elementos = window.prestamoData?.elementos || [];
    const cuentadantes = window.prestamoData?.cuentadantes || [];
    const idCuentadante = document.getElementById('id_cuentadante');
    const cuentadante = document.getElementById('cuentadante');
    const modalCuentadante = new bootstrap.Modal(document.getElementById('modalCuentadante'));
    const modalElemento = new bootstrap.Modal(document.getElementById('modalElemento'));
    const modalConfirmar = new bootstrap.Modal(document.getElementById('modalConfirmarPrestamo'));
    const contenedorLineas = document.getElementById('lineasElemento');
    const lineasVacio = document.getElementById('lineasVacio');
    const resultadosCuentadante = document.getElementById('resultadosCuentadante');
    const resultadosElemento = document.getElementById('resultadosElemento');
    const buscarCuentadanteInput = document.getElementById('buscarCuentadanteInput');
    const buscarElementoInput = document.getElementById('buscarElementoInput');
    const alertModalElemento = document.getElementById('alertModalElemento');
    const btnConfirmarElemento = document.getElementById('btnConfirmarElemento');
    let confirmado = false;
    let elementoSeleccionado = null;

    function codigosEnGrilla() {
        return Array.from(contenedorLineas.querySelectorAll('.linea-elemento'))
            .map(function (fila) { return fila.dataset.codigo; });
    }

    function actualizarEstadoGrilla() {
        if (lineasVacio) {
            lineasVacio.hidden = contenedorLineas.querySelectorAll('.linea-elemento').length > 0;
        }
    }

    function mostrarAlerta(mensaje) {
        alertModalElemento.textContent = mensaje;
        alertModalElemento.classList.remove('d-none');
    }

    function ocultarAlerta() {
        alertModalElemento.classList.add('d-none');
        alertModalElemento.textContent = '';
    }

    function elementosDisponibles() {
        const usados = codigosEnGrilla();
        return elementos.filter(function (el) {
            return !usados.includes(el.codigo) && el.stock > 0;
        });
    }

    function resetModalCuentadante() {
        buscarCuentadanteInput.value = '';
        buscarCuentadantes('');
    }

    function resetModalElemento() {
        elementoSeleccionado = null;
        buscarElementoInput.value = '';
        btnConfirmarElemento.disabled = true;
        ocultarAlerta();
        buscarElementos('');
    }

    function marcarFilaSeleccionada(codigo) {
        resultadosElemento.querySelectorAll('tr.modal-busqueda-row-selectable').forEach(function (tr) {
            const activa = tr.dataset.codigo === codigo;
            tr.classList.toggle('selected', activa);
            tr.setAttribute('aria-selected', activa ? 'true' : 'false');
        });
    }

    function seleccionarElemento(item, tr) {
        elementoSeleccionado = item;
        btnConfirmarElemento.disabled = false;
        marcarFilaSeleccionada(item.codigo);
        if (tr) {
            tr.classList.add('selected');
        }
    }

    function agregarElemento(item) {
        ocultarAlerta();

        if (!item) {
            mostrarAlerta('Seleccione un elemento de la lista.');
            return;
        }

        const cantidad = 1;
        const stock = item.stock;

        if (stock <= 0) {
            mostrarAlerta('No hay stock disponible para este elemento.');
            return;
        }

        if (codigosEnGrilla().includes(item.codigo)) {
            mostrarAlerta('Este elemento ya fue agregado al préstamo.');
            return;
        }

        const fila = document.createElement('tr');
        fila.className = 'linea-elemento';
        fila.dataset.codigo = item.codigo;
        fila.dataset.stock = stock;
        fila.innerHTML =
            '<td><code>' + escapeHtml(item.codigo) + '</code></td>' +
            '<td class="linea-nombre">' + escapeHtml(item.elemento) + '</td>' +
            '<td class="linea-stock">' + stock + '</td>' +
            '<td><input type="number" name="linea_cantidad[]" class="form-control form-control-sm input-cantidad" min="1" max="' + stock + '" value="' + cantidad + '" required></td>' +
            '<td class="text-end">' +
                '<input type="hidden" name="linea_codigo[]" value="' + escapeAttr(item.codigo) + '">' +
                '<button type="button" class="btn btn-sm btn-outline-danger btn-quitar-linea" title="Quitar"><i class="bi bi-trash"></i></button>' +
            '</td>';

        contenedorLineas.appendChild(fila);
        actualizarEstadoGrilla();
        modalElemento.hide();
    }

    function abrirModalCuentadante() {
        resetModalCuentadante();
        modalCuentadante.show();
    }

    function buscarCuentadantes(termino) {
        const resultados = ModalBusqueda.filtrar(cuentadantes, termino, ['nombres', 'cedula']);

        ModalBusqueda.renderFilas(
            resultadosCuentadante,
            resultados,
            function (item) {
                const tr = document.createElement('tr');
                tr.innerHTML =
                    '<td>' + ModalBusqueda.escapeHtml(item.nombres) + '</td>' +
                    '<td><code>' + ModalBusqueda.escapeHtml(item.cedula) + '</code></td>' +
                    '<td class="text-end">' + ModalBusqueda.botonSeleccionar() + '</td>';
                tr.querySelector('button').addEventListener('click', function () {
                    idCuentadante.value = item.cedula;
                    cuentadante.value = item.nombres;
                    modalCuentadante.hide();
                });
                return tr;
            },
            'No se encontraron responsables con ese criterio.'
        );
    }

    function buscarElementos(termino) {
        const disponibles = elementosDisponibles();
        const resultados = ModalBusqueda.filtrar(disponibles, termino, ['codigo', 'elemento', 'categoria']);

        ModalBusqueda.renderFilas(
            resultadosElemento,
            resultados,
            function (item) {
                return ModalBusqueda.crearFilaElemento(item, {
                    onSeleccionar: seleccionarElemento,
                    onAgregar: agregarElemento,
                });
            },
            'No se encontraron elementos con stock disponibles.'
        );

        if (elementoSeleccionado) {
            marcarFilaSeleccionada(elementoSeleccionado.codigo);
        }
    }

    document.getElementById('btnModalCuentadante').addEventListener('click', abrirModalCuentadante);
    if (!idCuentadante.value.trim()) {
        abrirModalCuentadante();
    }

    ModalBusqueda.bindBusquedaEnVivo(buscarCuentadanteInput, buscarCuentadantes);
    ModalBusqueda.bindBusquedaEnVivo(buscarElementoInput, buscarElementos);

    document.getElementById('btnAgregarLinea').addEventListener('click', function () {
        if (elementosDisponibles().length === 0) {
            alert('No hay más elementos disponibles con stock para prestar.');
            return;
        }
        resetModalElemento();
        modalElemento.show();
    });

    document.getElementById('btnConfirmarElemento').addEventListener('click', function () {
        agregarElemento(elementoSeleccionado);
    });

    document.getElementById('modalElemento').addEventListener('hidden.bs.modal', resetModalElemento);
    document.getElementById('modalCuentadante').addEventListener('hidden.bs.modal', resetModalCuentadante);

    contenedorLineas.addEventListener('input', function (event) {
        const input = event.target.closest('.input-cantidad');
        if (!input) {
            return;
        }
        const fila = input.closest('.linea-elemento');
        const stock = parseInt(fila.dataset.stock, 10);
        const valor = parseInt(input.value, 10);
        if (valor > stock) {
            input.setCustomValidity('Máximo ' + stock + ' unidades disponibles.');
        } else {
            input.setCustomValidity('');
        }
    });

    contenedorLineas.addEventListener('click', function (event) {
        const btn = event.target.closest('.btn-quitar-linea');
        if (!btn) {
            return;
        }
        btn.closest('.linea-elemento').remove();
        actualizarEstadoGrilla();
    });

    document.getElementById('btnGuardarPrestamo').addEventListener('click', function () {
        if (!idCuentadante.value.trim()) {
            alert('Seleccione un cuentadante.');
            abrirModalCuentadante();
            return;
        }

        if (codigosEnGrilla().length === 0) {
            alert('Agregue al menos un elemento al préstamo.');
            return;
        }

        let stockInvalido = false;
        contenedorLineas.querySelectorAll('.linea-elemento').forEach(function (fila) {
            const input = fila.querySelector('.input-cantidad');
            const stock = parseInt(fila.dataset.stock, 10);
            const valor = parseInt(input.value, 10);
            if (!valor || valor <= 0 || valor > stock) {
                stockInvalido = true;
                input.reportValidity();
            }
        });

        if (stockInvalido) {
            alert('Revise las cantidades. No puede prestar más del stock disponible.');
            return;
        }

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const resumen = document.getElementById('resumenConfirmacion');
        const fecha = document.getElementById('fechaPrestamo').value;
        const descripcion = document.getElementById('descripcionPrestamo').value.trim();

        let html = '<dl class="row mb-3">' +
            '<dt class="col-sm-3">Fecha</dt><dd class="col-sm-9">' + escapeHtml(fecha) + '</dd>' +
            '<dt class="col-sm-3">Cuentadante</dt><dd class="col-sm-9">' + escapeHtml(cuentadante.value) + ' (' + escapeHtml(idCuentadante.value) + ')</dd>';

        if (descripcion) {
            html += '<dt class="col-sm-3">Descripción</dt><dd class="col-sm-9">' + escapeHtml(descripcion) + '</dd>';
        }
        html += '</dl>';

        html += '<div class="table-responsive"><table class="table table-sm mb-0">' +
            '<thead><tr><th>Código</th><th>Elemento</th><th>Stock</th><th>Cantidad</th></tr></thead><tbody>';

        contenedorLineas.querySelectorAll('.linea-elemento').forEach(function (fila) {
            html += '<tr>' +
                '<td><code>' + escapeHtml(fila.dataset.codigo) + '</code></td>' +
                '<td>' + escapeHtml(fila.querySelector('.linea-nombre').textContent.trim()) + '</td>' +
                '<td>' + escapeHtml(fila.dataset.stock) + '</td>' +
                '<td>' + escapeHtml(fila.querySelector('.input-cantidad').value) + '</td>' +
                '</tr>';
        });

        html += '</tbody></table></div>';
        html += '<p class="text-muted small mt-3 mb-0">Se descontará la cantidad del inventario. ¿Desea registrar este préstamo?</p>';
        resumen.innerHTML = html;
        modalConfirmar.show();
    });

    document.getElementById('btnConfirmarPrestamo').addEventListener('click', function () {
        confirmado = true;
        modalConfirmar.hide();
        form.submit();
    });

    form.addEventListener('submit', function (event) {
        if (!confirmado) {
            event.preventDefault();
        }
    });

    actualizarEstadoGrilla();

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function escapeAttr(text) {
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }
});
