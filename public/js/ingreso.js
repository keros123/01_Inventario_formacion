document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formIngreso');
    if (!form || typeof bootstrap === 'undefined' || !window.ModalBusqueda) {
        return;
    }

    const elementos = window.ingresoData?.elementos || [];
    const modalElemento = new bootstrap.Modal(document.getElementById('modalElemento'));
    const modalConfirmar = new bootstrap.Modal(document.getElementById('modalConfirmarIngreso'));
    const contenedorLineas = document.getElementById('lineasElemento');
    const lineasVacio = document.getElementById('lineasVacio');
    const resultadosElemento = document.getElementById('resultadosElemento');
    const buscarElementoInput = document.getElementById('buscarElementoInput');
    const alertModalElemento = document.getElementById('alertModalElemento');
    const alertModalCrearElemento = document.getElementById('alertModalCrearElemento');
    const panelBuscar = document.getElementById('panelBuscarElemento');
    const panelCrear = document.getElementById('panelCrearElemento');
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

    function mostrarAlerta(el, mensaje) {
        el.textContent = mensaje;
        el.classList.remove('d-none');
    }

    function ocultarAlerta(el) {
        el.classList.add('d-none');
        el.textContent = '';
    }

    function elementosDisponibles() {
        const usados = codigosEnGrilla();
        return elementos.filter(function (el) { return !usados.includes(el.codigo); });
    }

    function resetBuscarPanel() {
        elementoSeleccionado = null;
        buscarElementoInput.value = '';
        btnConfirmarElemento.disabled = true;
        ocultarAlerta(alertModalElemento);
        buscarElementos('');
    }

    function resetCrearPanel() {
        document.getElementById('codigoNuevoElemento').value = '';
        document.getElementById('nombreNuevoElemento').value = '';
        document.getElementById('descripcionNuevoElemento').value = '';
        document.getElementById('cantidadNuevoElemento').value = '1';
        ocultarAlerta(alertModalCrearElemento);
    }

    function mostrarPanelBuscar() {
        panelBuscar.hidden = false;
        panelCrear.hidden = true;
        resetBuscarPanel();
    }

    function mostrarPanelCrear() {
        panelBuscar.hidden = true;
        panelCrear.hidden = false;
        resetCrearPanel();
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

    function agregarElementoExistente(item) {
        ocultarAlerta(alertModalElemento);

        if (!item) {
            mostrarAlerta(alertModalElemento, 'Seleccione un elemento de la lista.');
            return;
        }

        if (codigosEnGrilla().includes(item.codigo)) {
            mostrarAlerta(alertModalElemento, 'Este elemento ya fue agregado al ingreso.');
            return;
        }

        agregarLinea({
            codigo: item.codigo,
            nombre: item.elemento,
            stock: item.stock,
            cantidad: 1,
            esNuevo: false,
        });
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
                    onAgregar: agregarElementoExistente,
                });
            },
            'No se encontraron elementos con ese criterio.'
        );

        if (elementoSeleccionado) {
            marcarFilaSeleccionada(elementoSeleccionado.codigo);
        }
    }

    function agregarLinea(datos) {
        const fila = document.createElement('tr');
        fila.className = 'linea-elemento';
        fila.dataset.codigo = datos.codigo;
        fila.innerHTML =
            '<td><code>' + escapeHtml(datos.codigo) + '</code></td>' +
            '<td class="linea-nombre">' + escapeHtml(datos.nombre) +
                (datos.esNuevo ? ' <span class="badge bg-info">Nuevo</span>' : '') + '</td>' +
            '<td class="linea-stock">' + escapeHtml(String(datos.stock)) + '</td>' +
            '<td><input type="number" name="linea_cantidad[]" class="form-control form-control-sm" min="1" value="' + datos.cantidad + '" required></td>' +
            '<td class="text-end">' +
                '<input type="hidden" name="linea_codigo[]" value="' + escapeAttr(datos.codigo) + '">' +
                '<input type="hidden" name="linea_nuevo[]" value="' + (datos.esNuevo ? '1' : '0') + '">' +
                '<input type="hidden" name="linea_elemento_nuevo[]" value="' + escapeAttr(datos.esNuevo ? datos.nombre : '') + '">' +
                '<input type="hidden" name="linea_categoria_nuevo[]" value="' + escapeAttr(datos.esNuevo ? datos.categoria : '') + '">' +
                '<input type="hidden" name="linea_descripcion_nuevo[]" value="' + escapeAttr(datos.esNuevo ? datos.descripcion : '') + '">' +
                '<button type="button" class="btn btn-sm btn-outline-danger btn-quitar-linea" title="Quitar">' +
                    '<i class="bi bi-trash"></i>' +
                '</button>' +
            '</td>';

        contenedorLineas.appendChild(fila);
        actualizarEstadoGrilla();
        modalElemento.hide();
    }

    document.getElementById('btnAgregarLinea').addEventListener('click', function () {
        if (elementosDisponibles().length === 0) {
            mostrarPanelCrear();
        } else {
            mostrarPanelBuscar();
        }
        modalElemento.show();
    });

    ModalBusqueda.bindBusquedaEnVivo(buscarElementoInput, buscarElementos);

    document.getElementById('btnIrCrearElemento').addEventListener('click', mostrarPanelCrear);
    document.getElementById('btnVolverBuscarElemento').addEventListener('click', mostrarPanelBuscar);

    document.getElementById('btnConfirmarElemento').addEventListener('click', function () {
        agregarElementoExistente(elementoSeleccionado);
    });

    document.getElementById('btnConfirmarNuevoElemento').addEventListener('click', function () {
        ocultarAlerta(alertModalCrearElemento);

        const codigo = document.getElementById('codigoNuevoElemento').value.trim();
        const nombre = document.getElementById('nombreNuevoElemento').value.trim();
        const categoria = document.getElementById('categoriaNuevoElemento').value;
        const descripcion = document.getElementById('descripcionNuevoElemento').value.trim();
        const cantidad = parseInt(document.getElementById('cantidadNuevoElemento').value, 10);

        if (!codigo || !nombre) {
            mostrarAlerta(alertModalCrearElemento, 'Complete código y nombre del nuevo elemento.');
            return;
        }

        if (elementos.some(function (el) { return el.codigo === codigo; })) {
            mostrarAlerta(alertModalCrearElemento, 'Ese código ya existe en el inventario.');
            return;
        }

        if (!cantidad || cantidad <= 0) {
            mostrarAlerta(alertModalCrearElemento, 'La cantidad debe ser mayor a cero.');
            return;
        }

        if (codigosEnGrilla().includes(codigo)) {
            mostrarAlerta(alertModalCrearElemento, 'Este elemento ya fue agregado al ingreso.');
            return;
        }

        agregarLinea({
            codigo: codigo,
            nombre: nombre,
            stock: 0,
            cantidad: cantidad,
            esNuevo: true,
            categoria: categoria,
            descripcion: descripcion,
        });
    });

    document.getElementById('modalElemento').addEventListener('hidden.bs.modal', function () {
        mostrarPanelBuscar();
    });

    contenedorLineas.addEventListener('click', function (event) {
        const btn = event.target.closest('.btn-quitar-linea');
        if (!btn) {
            return;
        }
        btn.closest('.linea-elemento').remove();
        actualizarEstadoGrilla();
    });

    document.getElementById('btnGuardarIngreso').addEventListener('click', function () {
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        if (codigosEnGrilla().length === 0) {
            alert('Agregue al menos un elemento al ingreso.');
            return;
        }

        const resumen = document.getElementById('resumenConfirmacion');
        const fecha = document.getElementById('fechaIngreso').value;
        const cedula = document.getElementById('usuario_cedula').value;
        const nombre = document.getElementById('usuario_nombre').value;
        const descripcion = document.getElementById('descripcionIngreso').value.trim();
        let html = '<dl class="row mb-3">' +
            '<dt class="col-sm-3">Fecha</dt><dd class="col-sm-9">' + escapeHtml(fecha) + '</dd>' +
            '<dt class="col-sm-3">Cédula</dt><dd class="col-sm-9">' + escapeHtml(cedula) + '</dd>' +
            '<dt class="col-sm-3">Nombre</dt><dd class="col-sm-9">' + escapeHtml(nombre) + '</dd>';

        if (descripcion) {
            html += '<dt class="col-sm-3">Descripción</dt><dd class="col-sm-9">' + escapeHtml(descripcion) + '</dd>';
        }
        html += '</dl>';

        html += '<div class="table-responsive"><table class="table table-sm mb-0">' +
            '<thead><tr><th>Código</th><th>Elemento</th><th>Cantidad</th></tr></thead><tbody>';

        contenedorLineas.querySelectorAll('.linea-elemento').forEach(function (fila) {
            const nombreEl = fila.querySelector('.linea-nombre').cloneNode(true);
            nombreEl.querySelectorAll('.badge').forEach(function (b) { b.remove(); });
            html += '<tr>' +
                '<td><code>' + escapeHtml(fila.dataset.codigo) + '</code></td>' +
                '<td>' + escapeHtml(nombreEl.textContent.trim()) + '</td>' +
                '<td>' + escapeHtml(fila.querySelector('input[name="linea_cantidad[]"]').value) + '</td>' +
                '</tr>';
        });

        html += '</tbody></table></div>';
        html += '<p class="text-muted small mt-3 mb-0">¿Desea registrar este ingreso?</p>';
        resumen.innerHTML = html;
        modalConfirmar.show();
    });

    document.getElementById('btnConfirmarIngreso').addEventListener('click', function () {
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
