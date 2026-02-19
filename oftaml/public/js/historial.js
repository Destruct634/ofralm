$(document).ready(function() {

    // --- LÓGICA PARA BÚSQUEDA Y FILTRADO EN HISTORIAL ---
    function aplicarFiltrosHistorial() {
        let textoBusqueda = $('#filtroBusquedaHistorial').val().toLowerCase();
        let fechaDesde = $('#filtroFechaDesde').val();
        let fechaHasta = $('#filtroFechaHasta').val();
        $('.item-historial').show();
        $('.item-historial').each(function() {
            let textoItem = $(this).text().toLowerCase();
            let fechaItem = $(this).data('fecha');
            let coincideTexto = textoItem.includes(textoBusqueda);
            let coincideFecha = true;
            if (fechaDesde && fechaItem < fechaDesde) { coincideFecha = false; }
            if (fechaHasta && fechaItem > fechaHasta) { coincideFecha = false; }
            if (coincideTexto && coincideFecha) { $(this).show(); } else { $(this).hide(); }
        });
        if (textoBusqueda || fechaDesde || fechaHasta) {
            $('#contenedor-ver-mas-historial').hide();
        } else {
            $('#contenedor-ver-mas-historial').show();
            $('.item-historial').each(function(index) {
                if (index >= 3) {
                    if (!$('#btnVerMasHistorial').data('expanded')) { $(this).hide(); }
                }
            });
        }
    }
    $('#filtroBusquedaHistorial').on('keyup', aplicarFiltrosHistorial);
    $('#filtroFechaDesde, #filtroFechaHasta').on('change', aplicarFiltrosHistorial);
    
    // --- CAMBIO DE VISTA DE IMÁGENES (NUEVO) ---
    $('input[name="viewImgOption"]').on('change', function() {
        if ($('#viewImgFull').is(':checked')) {
            // Cambiar a vista completa (col-12)
            $('.container-img-historial').removeClass('col-md-6 col-lg-4').addClass('col-12');
        } else {
            // Regresar a vista cuadrícula (col-md-6 col-lg-4)
            $('.container-img-historial').removeClass('col-12').addClass('col-md-6 col-lg-4');
        }
    });

    $('#filtroBusquedaArchivos').on('keyup', function() {
        let textoBusqueda = $(this).val().toLowerCase();
        $('#tablaBodyArchivosPaciente tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(textoBusqueda) > -1)
        });
    });
    $('#modalListaArchivosPaciente').on('hidden.bs.modal', function () {
        $('#filtroBusquedaArchivos').val('');
        $('#tablaBodyArchivosPaciente tr').show();
    });
    $(document).on('click', '.btn-ver-log', function() {
        let historialId = $(this).data('historial-id');
        let logBody = $('#logBody');
        $('#modalHistorialLogLabel').text(`Historial de Cambios para Entrada #${historialId}`);
        logBody.html('<p class="text-center">Cargando historial...</p>');
        $('#modalHistorialLog').modal('show');
        $.ajax({
            url: `../app/controllers/HistorialController.php?get_log_for_id=${historialId}`,
            type: 'GET',
            success: function(logs) {
                logBody.empty();
                if (logs.length > 0) {
                    logs.forEach(function(log) {
                        let fecha = new Date(log.fecha_modificacion).toLocaleString('es-HN');
                        let logHtml = `<div class="card mb-3"><div class="card-header bg-light"><i class="fas fa-user-edit me-2"></i><strong>Modificado por:</strong> ${log.usuario_modifica} <br><i class="fas fa-clock me-2"></i><strong>Fecha:</strong> ${fecha}</div><div class="card-body"><h6 class="card-subtitle mb-2 text-muted">Valores Anteriores:</h6><strong>Diagnóstico:</strong><div>${log.diagnostico_anterior || '<em>Sin cambios o vacío</em>'}</div><hr><strong>Tratamiento:</strong><div>${log.tratamiento_anterior || '<em>Sin cambios o vacío</em>'}</div><hr><strong>Observaciones:</strong><div>${log.observaciones_anterior || '<em>Sin cambios o vacío</em>'}</div></div></div>`;
                        logBody.append(logHtml);
                    });
                } else {
                    logBody.html('<div class="alert alert-info">No se han registrado cambios para esta entrada.</div>');
                }
            },
            error: function() {
                logBody.html('<div class="alert alert-danger">Error al cargar el historial de cambios.</div>');
            }
        });
    });

    // --- GESTIÓN DE ENTRADAS DE HISTORIAL (CON PLANTILLAS INTEGRADAS) ---
    var PredefinidoButton = function (context) {
        var ui = $.summernote.ui;
        var buttonGroup = ui.buttonGroup([
            ui.button({ className: 'dropdown-toggle', contents: '<i class="fas fa-file-alt"></i> Textos Predefinidos', tooltip: "Insertar texto predefinido", data: { toggle: 'dropdown' } }),
            ui.dropdown({ className: 'dropdown-style dropdown-textos', items: ['Cargando...'] })
        ]);
        var $button = buttonGroup.render();
        $button.find('.dropdown-toggle').attr('data-bs-toggle', 'dropdown');
        $.ajax({
            url: '../app/controllers/TextoPredefinidoController.php?action=get_activos',
            type: 'GET',
            success: function(textos) {
                const menu = $button.find('.dropdown-menu');
                menu.empty();
                if (textos.length > 0) {
                    textos.forEach(function(texto) {
                        const item = $('<li><a class="dropdown-item" href="#">' + texto.titulo + '</a></li>');
                        item.on('click', function(e) { e.preventDefault(); context.invoke('editor.pasteHTML', texto.contenido); });
                        menu.append(item);
                    });
                } else { menu.append('<li><span class="dropdown-item disabled">No hay textos</span></li>'); }
            },
            error: function() { const menu = $button.find('.dropdown-menu'); menu.empty(); menu.append('<li><span class="dropdown-item disabled">Error al cargar</span></li>'); }
        });
        return $button;
    };
    
    // --- LÓGICA DEL MODAL DE CONSULTA (CON PESTAÑAS) ---
    
    let itemsConsulta = []; // <-- NUEVO ARRAY PARA PESTAÑA 6
    let tipos_isv_disponibles = []; // <-- NUEVO, PARA LOS ITEMS

    const toolbarFull = [
        ['style', ['bold', 'italic', 'underline', 'clear']],
        ['font', ['strikethrough']],
        ['para', ['ul', 'ol', 'paragraph']],
        ['insert', ['link']],
        ['mybutton', ['predefinido']]
    ];
    const toolbarLite = [
        ['style', ['bold', 'italic', 'underline']],
        ['para', ['ul', 'ol']]
    ];

    function initSummernote(selector, toolbar, placeholder, height) {
        if (!$(selector).data('summernote')) {
            $(selector).summernote({
                placeholder: placeholder,
                lang: 'es-ES',
                height: height,
                toolbar: toolbar,
                buttons: { predefinido: PredefinidoButton }
            });
        }
    }

    // --- INICIO DE MODIFICACIÓN 1: Inicialización de Pestañas ---
    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
        let target = $(e.target).data("bs-target");
        if (target === '#tab-examen') {
            initSummernote('#biomicroscopia', toolbarLite, 'Escriba los hallazgos...', 100);
            initSummernote('#fondo_ojo', toolbarLite, 'Escriba los hallazgos...', 100);
        } else if (target === '#tab-plan') {
            inicializarBuscadorDiagnosticos();
            initSummernote('#tratamiento', toolbarFull, 'Escriba el plan...', 150);
        } else if (target === '#tab-items') { // <-- NUEVO
            // Precargamos los tipos de ISV para el dropdown
            if (tipos_isv_disponibles.length === 0) {
                // Usamos la misma acción que ya existe en tu facturacion.js
                $.get('../app/controllers/ProductoController.php?action=get_isv', function(data) { 
                    tipos_isv_disponibles = data; 
                });
            }
            inicializarBuscadorItems();
        }
    });
    // --- FIN DE MODIFICACIÓN 1 ---

    // --- INICIO DE MODIFICACIÓN 2: Cerrar Modal ---
    $('#modalNuevaEntrada').on('hidden.bs.modal', function () { 
        $('.summernote-lite, .summernote-full').each(function() {
            if ($(this).data('summernote')) {
                $(this).summernote('destroy');
            }
        });
        if ($('#selectDiagnosticos').data('select2')) {
            $('#selectDiagnosticos').select2('destroy');
        }
        if ($('#selectItem').data('select2')) { // <-- NUEVO
            $('#selectItem').select2('destroy');
        }
    });
    // --- FIN DE MODIFICACIÓN 2 ---

    // --- INICIO DE MODIFICACIÓN 3: Botón Registrar ---
    $(document).on('click', '.btn-registrar-historial', function() {
        $('#formNuevaEntradaHistorial').trigger('reset');
        $('#historial_id').val(''); 
        $('#modalEntradaLabel').text('Registrar Nueva Entrada de Historial');
        $('#historial_id_cita').val($(this).data('cita-id'));
        $('#historial_id_paciente').val($(this).data('paciente-id'));
        $('#historial_id_medico').val($(this).data('medico-id'));
        
        $('#selectDiagnosticos').val(null).trigger('change');
        
        itemsConsulta = []; // <-- NUEVO
        actualizarTablaItemsHistorial(); // <-- NUEVO

        $('#consultaTab button[data-bs-target="#tab-consulta"]').tab('show');
        cargarPlantillasDropdown();
        $('#modalNuevaEntrada').modal('show');
    });
    // --- FIN DE MODIFICACIÓN 3 ---

    // --- INICIO DE MODIFICACIÓN 4: Botón Editar ---
    $(document).on('click', '.btn-editar-historial', function() {
        let historialId = $(this).data('historial-id');
        $('#formNuevaEntradaHistorial').trigger('reset');
        $('#historial_id').val(historialId);
        $('#modalEntradaLabel').text('Editar Entrada de Historial');
        
        $('#selectDiagnosticos').val(null).trigger('change');
        
        itemsConsulta = []; // <-- NUEVO
        actualizarTablaItemsHistorial(); // <-- NUEVO

        $('#consultaTab button[data-bs-target="#tab-consulta"]').tab('show');
        cargarPlantillasDropdown();

        $.ajax({
            url: `../app/controllers/HistorialController.php?id=${historialId}`, type: 'GET',
            success: function(data) {
                $('#modalNuevaEntrada').modal('show');
                
                $('#modalNuevaEntrada').one('shown.bs.modal', function () {
                    
                    // Pestaña 1: Consulta
                    $('#hea').val(data.hea);

                    // Pestaña 2: AV y PIO
                    $('#av_sc_od').val(data.av_sc_od);
                    $('#av_sc_os').val(data.av_sc_os);
                    $('#av_cc_od').val(data.av_cc_od);
                    $('#av_cc_os').val(data.av_cc_os);
                    $('#pio_od').val(data.pio_od);
                    $('#pio_os').val(data.pio_os);

                    // Pestaña 3: Refracción
                    if (data.refraccion) {
                        $('#tipo_refraccion').val(data.refraccion.tipo_refraccion);
                        $('#od_esfera').val(data.refraccion.od_esfera);
                        $('#od_cilindro').val(data.refraccion.od_cilindro);
                        $('#od_eje').val(data.refraccion.od_eje);
                        $('#od_av').val(data.refraccion.od_av);
                        $('#os_esfera').val(data.refraccion.os_esfera);
                        $('#os_cilindro').val(data.refraccion.os_cilindro);
                        $('#os_eje').val(data.refraccion.os_eje);
                        $('#os_av').val(data.refraccion.os_av);
                        $('#add').val(data.refraccion.add);
                        $('#refraccion_obs').val(data.refraccion.observaciones);
                    }

                    // Pestaña 4: Examen Clínico
                    initSummernote('#biomicroscopia', toolbarLite, 'Escriba los hallazgos...', 100);
                    $('#biomicroscopia').summernote('code', data.biomicroscopia);
                    initSummernote('#fondo_ojo', toolbarLite, 'Escriba los hallazgos...', 100);
                    $('#fondo_ojo').summernote('code', data.fondo_ojo);
                    $('#observaciones').val(data.observaciones);

                    // Pestaña 5: Diagnóstico y Plan
                    initSummernote('#tratamiento', toolbarFull, 'Escriba el plan...', 150);
                    $('#tratamiento').summernote('code', data.tratamiento);
                    
                    if (data.diagnosticos && data.diagnosticos.length > 0) {
                        let $selectDiagnosticos = $('#selectDiagnosticos');
                        $selectDiagnosticos.empty();
                        data.diagnosticos.forEach(function(d) {
                            var option = new Option(d.text, d.id, true, true);
                            $selectDiagnosticos.append(option);
                        });
                        $selectDiagnosticos.trigger('change');
                    }
                    
                    // Pestaña 6: Cargar Items (Servicios/Productos)
                    if (data.items_consulta && data.items_consulta.length > 0) {
                        itemsConsulta = data.items_consulta;
                        actualizarTablaItemsHistorial();
                    }
                });
            },
            error: function() { Swal.fire('Error', 'No se pudieron cargar los datos de la entrada.', 'error'); }
        });
    });
    // --- FIN DE MODIFICACIÓN 4 ---

    let plantillas_disponibles = [];
    function cargarPlantillasDropdown() {
        let select = $('#selectPlantilla');
        select.html('<option value="">-- Seleccionar una plantilla --</option>');
        $.ajax({
            url: '../app/controllers/ConsultaPlantillaController.php?action=get_activos',
            type: 'GET',
            success: function(plantillas) {
                plantillas_disponibles = plantillas;
                if (plantillas.length > 0) {
                    plantillas.forEach(function(p) {
                        select.append(`<option value="${p.id}">${p.titulo}</option>`);
                    });
                }
            }
        });
    }

    $('#selectPlantilla').on('change', function() {
        let plantillaId = $(this).val();
        if (plantillaId) {
            let plantillaSeleccionada = plantillas_disponibles.find(p => p.id == plantillaId);
            if (plantillaSeleccionada) {
                $('#hea').val(plantillaSeleccionada.contenido);
            }
        }
    });

    // --- INICIO DE MODIFICACIÓN 5: Guardar Formulario ---
    $('#formNuevaEntradaHistorial').submit(function(e) {
        e.preventDefault();
        let historialId = $('#historial_id').val();
        let esActualizacion = historialId !== '';
        
        let data = {};
        
        // Pestaña 1: Consulta
        data.hea = $('#hea').val() || null;

        // Pestaña 2: AV y PIO
        data.av_sc_od = $('#av_sc_od').val() || null;
        data.av_sc_os = $('#av_sc_os').val() || null;
        data.av_cc_od = $('#av_cc_od').val() || null;
        data.av_cc_os = $('#av_cc_os').val() || null;
        data.pio_od = $('#pio_od').val() || null;
        data.pio_os = $('#pio_os').val() || null;
        
        // Pestaña 3: Refracción
        data.refraccion = {
            tipo_refraccion: $('#tipo_refraccion').val(),
            od_esfera: $('#od_esfera').val() || null,
            od_cilindro: $('#od_cilindro').val() || null,
            od_eje: $('#od_eje').val() || null,
            od_av: $('#od_av').val() || null,
            os_esfera: $('#os_esfera').val() || null,
            os_cilindro: $('#os_cilindro').val() || null,
            os_eje: $('#os_eje').val() || null,
            os_av: $('#os_av').val() || null,
            add: $('#add').val() || null,
            observaciones: $('#refraccion_obs').val() || null
        };

        // Pestaña 4: Examen Clínico
        data.biomicroscopia = $('#biomicroscopia').summernote('code');
        data.fondo_ojo = $('#fondo_ojo').summernote('code');
        data.observaciones = $('#observaciones').val() || null;

        // Pestaña 5: Diagnóstico y Plan
        data.diagnosticos_ids = $('#selectDiagnosticos').val(); 
        data.tratamiento = $('#tratamiento').summernote('code');

        // Pestaña 6: Servicios y Productos
        data.items_consulta = itemsConsulta; // <-- NUEVO

        let ajaxType, ajaxUrl;
        
        if (esActualizacion) {
            ajaxType = 'PUT';
            ajaxUrl = '../app/controllers/HistorialController.php';
            data.id = historialId;
        } else {
            ajaxType = 'POST';
            ajaxUrl = '../app/controllers/HistorialController.php';
            data.id_cita = $('#historial_id_cita').val();
            data.id_paciente = $('#historial_id_paciente').val();
            data.id_medico = $('#historial_id_medico').val();
            if(!data.id_medico) { Swal.fire('Error', 'La cita seleccionada no tiene un médico asignado.', 'error'); return; }
        }

        $.ajax({
            url: ajaxUrl, type: ajaxType, contentType: 'application/json', data: JSON.stringify(data),
            success: function(response) {
                $('#modalNuevaEntrada').modal('hide');
                Swal.fire({ title: '¡Éxito!', text: response.message, icon: 'success' }).then(() => { location.reload(); });
            },
            error: function(jqXHR) {
                let errorMessage = jqXHR.responseJSON ? jqXHR.responseJSON.message : 'No se pudo completar la operación.';
                Swal.fire('Error', errorMessage, 'error');
            }
        });
    });
    // --- FIN DE MODIFICACIÓN 5 ---

    // --- MANEJO DE ARCHIVOS ---
    $(document).on('click', '.btn-adjuntar-archivos', function() {
        let historialId = $(this).data('historial-id');
        let pacienteId = $(this).data('paciente-id');
        $('#upload_id_historial').val(historialId);
        $('#upload_id_paciente').val(pacienteId);
        $('#modalArchivosLabel').text(`Archivos para la Entrada de Historial #${historialId}`);
        cargarArchivos(historialId);
        cargarCategoriasDropdown($('#selectCategoriaArchivo'));
        $('#modalArchivos').modal('show');
    });
    function cargarArchivos(historialId) {
        let lista = $('#listaArchivosAdjuntos');
        lista.html('<li class="list-group-item">Cargando...</li>');
        $.ajax({
            url: `../app/controllers/ArchivoController.php?historial_id=${historialId}`, type: 'GET',
            success: function(archivos) {
                lista.empty();
                if (archivos.length > 0) {
                    archivos.forEach(function(archivo) {
                        let url = `${archivo.ruta_archivo}${archivo.nombre_guardado}`;
                        let categoriaBadge = archivo.nombre_categoria ? `<span class="badge bg-primary ms-2">${archivo.nombre_categoria}</span>` : '';
                        let infoSubida = `<small class="d-block text-muted mt-1">Subido por: ${archivo.usuario_subida} el ${new Date(archivo.fecha_subida).toLocaleDateString()}</small>`;
                        let botonEliminar = (USER_GROUP_ID == 1 || (PERMISOS.historial && PERMISOS.historial.editar == 1)) ? `<button class="btn btn-danger btn-sm btn-eliminar-archivo" data-archivo-id="${archivo.id}"><i class="fas fa-trash"></i></button>` : '';
                        let item = `<li class="list-group-item d-flex justify-content-between align-items-center"><div><a href="#" class="btn-ver-archivo" data-url="${url}">${archivo.nombre_original}</a>${categoriaBadge}${infoSubida}</div>${botonEliminar}</li>`;
                        lista.append(item);
                    });
                } else { lista.html('<li class="list-group-item">No hay archivos adjuntos.</li>'); }
            },
            error: function(jqXHR) {
                let errorMessage = jqXHR.responseJSON ? jqXHR.responseJSON.message : 'No se pudo cargar la lista de archivos.';
                lista.html(`<li class="list-group-item text-danger">${errorMessage}</li>`);
            }
        });
    }
    $('#formSubirArchivos').submit(function(e) {
        e.preventDefault();
        let formData = new FormData(this);
        let historialId = $('#upload_id_historial').val();
        $.ajax({
            url: '../app/controllers/ArchivoController.php', type: 'POST', data: formData, contentType: false, processData: false,
            success: function(response) { Swal.fire('¡Éxito!', response.message, 'success'); cargarArchivos(historialId); $('#formSubirArchivos').trigger('reset'); },
            error: function() { Swal.fire('Error', 'No se pudieron subir los archivos.', 'error'); }
        });
    });
    $(document).on('click', '.btn-eliminar-archivo', function() {
        let archivoId = $(this).data('archivo-id');
        let historialId = $('#upload_id_historial').val();
        Swal.fire({ title: '¿Estás seguro?', text: "¡El archivo se eliminará permanentemente!", icon: 'warning', showCancelButton: true, confirmButtonText: 'Sí, ¡bórralo!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `../app/controllers/ArchivoController.php`, type: 'DELETE', contentType: 'application/json', data: JSON.stringify({ id: archivoId }),
                    success: function(response) { Swal.fire('¡Eliminado!', response.message, 'success'); cargarArchivos(historialId); },
                    error: function() { Swal.fire('Error', 'No se pudo eliminar el archivo.', 'error'); }
                });
            }
        });
    });
    $(document).on('click', '.btn-ver-archivo', function(e) {
        e.preventDefault();
        const url = $(this).data('url');
        window.open(url, 'Archivo', `width=800,height=600,top=${(screen.height/2)-300},left=${(screen.width/2)-400},resizable=yes,scrollbars=yes`);
    });
    let currentPage = 1, totalPages = 1, currentPacienteId = null;
    $('#btnVerArchivosPaciente').on('click', function() {
        currentPacienteId = $(this).data('paciente-id');
        currentPage = 1;
        cargarListaArchivosPaginada(currentPacienteId, currentPage);
        cargarCategoriasDropdown($('#selectCategoriaPaciente'));
    });
    function cargarCategoriasDropdown(selectElement) {
        selectElement.html('<option value="">-- Sin Categoría --</option>');
        $.ajax({
            url: '../app/controllers/ArchivoCategoriaController.php?action=get_activos', type: 'GET',
            success: function(categorias) {
                if (categorias.length > 0) {
                    categorias.forEach(function(cat) {
                        selectElement.append(`<option value="${cat.id}">${cat.nombre_categoria}</option>`);
                    });
                }
            }
        });
    }
    $('#formSubirArchivoPaciente').submit(function(e) {
        e.preventDefault();
        let formData = new FormData(this);
        $(this).find('button[type="submit"]').prop('disabled', true).text('Subiendo...');
        $.ajax({
            url: '../app/controllers/ArchivoController.php', type: 'POST', data: formData, contentType: false, processData: false,
            success: function(response) { Swal.fire('¡Éxito!', response.message, 'success'); cargarListaArchivosPaginada(currentPacienteId, currentPage); },
            error: function() { Swal.fire('Error', 'No se pudieron subir los archivos.', 'error'); },
            complete: function() {
                $('#formSubirArchivoPaciente').trigger('reset');
                $('#formSubirArchivoPaciente').find('button[type="submit"]').prop('disabled', false).text('Subir');
            }
        });
    });
    $('#btnPaginaSiguiente').on('click', function() { if (currentPage < totalPages) { currentPage++; cargarListaArchivosPaginada(currentPacienteId, currentPage); } });
    $('#btnPaginaAnterior').on('click', function() { if (currentPage > 1) { currentPage--; cargarListaArchivosPaginada(currentPacienteId, currentPage); } });
    function cargarListaArchivosPaginada(pacienteId, page) {
        let tablaBody = $('#tablaBodyArchivosPaciente');
        tablaBody.html('<tr><td colspan="5" class="text-center">Cargando...</td></tr>');
        $.ajax({
            url: `../app/controllers/ArchivoController.php?action=archivos_paciente&paciente_id=${pacienteId}&page=${page}`,
            type: 'GET',
            success: function(response) {
                tablaBody.empty();
                totalPages = response.total_pages;
                currentPage = response.current_page;
                $('#infoPaginas').text(`Página ${currentPage} de ${totalPages}`);
                $('#btnPaginaAnterior').prop('disabled', currentPage <= 1);
                $('#btnPaginaSiguiente').prop('disabled', currentPage >= totalPages);
                if (response.data.length > 0) {
                    response.data.forEach(function(archivo) {
                        let url = `${archivo.ruta_archivo}${archivo.nombre_guardado}`;
                        let categoriaCell = archivo.nombre_categoria ? `<span class="badge bg-primary ms-2">${archivo.nombre_categoria}</span>` : '';
                        let botonEliminar = '';
                        if (USER_GROUP_ID == 1 || (PERMISOS.historial && PERMISOS.historial.editar == 1)) {
                            botonEliminar = `<button class="btn btn-danger btn-sm btn-eliminar-archivo-paciente" data-archivo-id="${archivo.id}" title="Eliminar"><i class="fas fa-trash"></i></button>`;
                        }
                        let accionesCell = `<td class="text-center">${botonEliminar}</td>`;
                        let fila = `<tr><td><a href="#" class="btn-ver-archivo" data-url="${url}">${archivo.nombre_original}</a></td><td>${categoriaCell}</td><td>${archivo.usuario_subida || 'N/A'}</td><td>${new Date(archivo.fecha_subida).toLocaleString()}</td>${accionesCell}</tr>`;
                        tablaBody.append(fila);
                    });
                } else { tablaBody.html('<tr><td colspan="5" class="text-center">No se encontraron archivos.</td></tr>'); }
            },
            error: function() { tablaBody.html('<tr><td colspan="5" class="text-center text-danger">Error al cargar los archivos.</td></tr>'); }
        });
    }
    $(document).on('click', '.btn-eliminar-archivo-paciente', function() {
        let archivoId = $(this).data('archivo-id');
        Swal.fire({
            title: '¿Estás seguro?',
            text: "¡El archivo se eliminará permanentemente!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, ¡bórralo!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `../app/controllers/ArchivoController.php`,
                    type: 'DELETE',
                    contentType: 'application/json',
                    data: JSON.stringify({ id: archivoId }),
                    success: function(response) {
                        Swal.fire('¡Eliminado!', response.message, 'success');
                        cargarListaArchivosPaginada(currentPacienteId, currentPage);
                    },
                    error: function() {
                        Swal.fire('Error', 'No se pudo eliminar el archivo.', 'error');
                    }
                });
            }
        });
    });
    $(document).on('click', '#btnVerMas', function() {
        let boton = $(this); let isExpanded = boton.data('expanded');
        if (isExpanded) {
            $('.item-visita-pasada').each(function(index) { if (index >= 3) { $(this).slideUp(); } });
            boton.data('expanded', false).text('Ver más').removeClass('btn-secondary').addClass('btn-outline-primary');
        } else {
            $('.item-visita-pasada').slideDown();
            boton.data('expanded', true).text('Ver menos').removeClass('btn-outline-primary').addClass('btn-secondary');
        }
    });
    $(document).on('click', '#btnVerMasHistorial', function() {
        let boton = $(this); let isExpanded = boton.data('expanded');
        if (isExpanded) {
            $('.item-historial').each(function(index) { if (index >= 3) { $(this).slideUp(); } });
            boton.data('expanded', false).text('Ver más').removeClass('btn-secondary').addClass('btn-outline-primary');
        } else {
            $('.item-historial').slideDown();
            boton.data('expanded', true).text('Ver menos').removeClass('btn-outline-primary').addClass('btn-secondary');
        }
    });
    $(document).on('click', '#btnGenerarPdf', function(e) {
        e.preventDefault();
        const downloadToken = new Date().getTime();
        const url = $(this).data('url') + '&downloadToken=' + downloadToken;
        Swal.fire({ title: 'Generando PDF', html: 'Por favor espere...', timerProgressBar: true, didOpen: () => { Swal.showLoading(); }, allowOutsideClick: false, allowEscapeKey: false });
        window.location.href = url;
        let interval = setInterval(function() {
            let cookieValue = "; " + document.cookie;
            let parts = cookieValue.split("; downloadToken=");
            if (parts.length == 2) {
                let cookieToken = parts.pop().split(";").shift();
                if (cookieToken == downloadToken) {
                    clearInterval(interval);
                    Swal.close();
                    document.cookie = "downloadToken=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
                }
            }
        }, 1000);
    });

    // Lógica para el buscador de Diagnósticos (Select2)
    function inicializarBuscadorDiagnosticos() {
        if (!$('#selectDiagnosticos').data('select2')) {
            $('#selectDiagnosticos').select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#modalNuevaEntrada'), 
                placeholder: 'Buscar diagnósticos (CIE-10 o desc)...',
                minimumInputLength: 2,
                multiple: true,
                tags: true, 
                createTag: function (params) {
                    var term = $.trim(params.term);
                    if (term === '') { return null; }
                    return { id: term, text: term + ' (Nuevo)', newTag: true };
                },
                ajax: {
                    url: '../app/controllers/AjaxController.php',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            action: 'buscarDiagnosticos', 
                            term: params.term
                        };
                    },
                    processResults: function (data) { return { results: data }; },
                    cache: true
                }
            });
        }
    }
    
    // --- INICIO DE NUEVA SECCIÓN (Pestaña 6) ---
    function inicializarBuscadorItems() {
        if (!$('#selectItem').data('select2')) {
            $('#selectItem').select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#modalNuevaEntrada'),
                placeholder: 'Buscar servicio o producto...',
                minimumInputLength: 2,
                ajax: {
                    url: '../app/controllers/AjaxController.php',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            action: 'buscarItems', // Esta acción ya existe en tu AjaxController
                            term: params.term
                        };
                    },
                    processResults: function (data) { return { results: data }; },
                    cache: true
                },
                templateResult: formatItem,
                templateSelection: formatItemSelection
            });
        }
    }
    
    // Funciones helper para el buscador de Items
    function formatItem (item) {
        if (item.loading) return item.text;
        // La data de AjaxController tiene 'tipo', 'text', 'precio_venta'
        let tipoBadge = item.tipo === 'Servicio' 
            ? '<span class="badge bg-primary me-2">Servicio</span>' 
            : '<span class="badge bg-success me-2">Producto</span>';
        
        return $(`<div>${tipoBadge} ${item.text} <small class="text-muted">(Precio: L. ${parseFloat(item.precio_venta).toFixed(2)})</small></div>`);
    }
    function formatItemSelection (item) {
        return item.text || "Buscar servicio o producto...";
    }
    
    // Añadir el item seleccionado a la tabla
    $('#btnAnadirItemHistorial').on('click', function() {
        let itemSeleccionado = $('#selectItem').select2('data')[0];
        let cantidad = parseInt($('#itemCantidad').val());
        let descuento = parseFloat($('#itemDescuento').val()) || 0;

        if (!itemSeleccionado || !itemSeleccionado.id_item || cantidad <= 0) {
            Swal.fire('Error', 'Debes seleccionar un item válido y una cantidad mayor a cero.', 'error');
            return;
        }

        // Revisar si ya existe
        let itemExistente = itemsConsulta.find(i => i.id_raw === itemSeleccionado.id);
        
        if (itemExistente) {
            itemExistente.cantidad += cantidad;
            itemExistente.descuento += descuento; // Sumar descuentos
        } else {
            // Buscamos el ISV que le corresponde
            // (Asumimos que la data de Ajax 'buscarItems' también devuelve 'id_isv')
            let isv_id = itemSeleccionado.id_isv || 1; // 1 = Exento por defecto
            
            itemsConsulta.push({
                id_raw: itemSeleccionado.id, // ID "S-1" o "P-1"
                id_item: itemSeleccionado.id_item, // ID numérico (1)
                tipo: itemSeleccionado.tipo, // "Servicio" o "Producto"
                descripcion: itemSeleccionado.text,
                precio: parseFloat(itemSeleccionado.precio_venta),
                cantidad: cantidad,
                descuento: descuento,
                id_isv: isv_id
            });
        }
        
        actualizarTablaItemsHistorial();
        $('#selectItem').val(null).trigger('change');
        $('#itemCantidad').val(1);
        $('#itemDescuento').val('0.00');
    });

    // Dibujar la tabla de items
    function actualizarTablaItemsHistorial() {
        let tbody = $('#tbodyItemsHistorial');
        tbody.empty();

        if (itemsConsulta.length === 0) {
            tbody.html('<tr><td colspan="7" class="text-center text-muted">Aún no se han añadido items.</td></tr>');
            return;
        }

        itemsConsulta.forEach((item, index) => {
            let precioNum = parseFloat(item.precio) || 0;
            let cantidadNum = parseInt(item.cantidad) || 0;
            let descuentoNum = parseFloat(item.descuento) || 0;

            let tipoBadge = item.tipo === 'Servicio' 
                ? '<span class="badge bg-primary">Servicio</span>' 
                : '<span class="badge bg-success">Producto</span>';
            
            let subtotal = (precioNum * cantidadNum) - descuentoNum;

            let fila = `
                <tr>
                    <td>${tipoBadge}</td>
                    <td>${item.descripcion}</td>
                    <td><input type="number" class="form-control form-control-sm item-hist-cantidad" data-index="${index}" value="${cantidadNum}" min="1"></td>
                    <td><input type="number" class="form-control form-control-sm item-hist-precio" data-index="${index}" value="${precioNum.toFixed(2)}" min="0" step="0.01"></td>
                    <td><input type="number" class="form-control form-control-sm item-hist-descuento" data-index="${index}" value="${descuentoNum.toFixed(2)}" min="0" step="0.01"></td>
                    <td>L. ${subtotal.toFixed(2)}</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-danger btn-sm btnEliminarItemHistorial" data-index="${index}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            tbody.append(fila);
        });
    }

    // Eliminar un item de la tabla
    $(document).on('click', '.btnEliminarItemHistorial', function() {
        let index = $(this).data('index');
        itemsConsulta.splice(index, 1); // Elimina el item del array
        actualizarTablaItemsHistorial();
    });

    // Actualizar datos desde la tabla (cantidad, precio, descuento)
    $(document).on('change', '.item-hist-cantidad, .item-hist-precio, .item-hist-descuento', function() {
        let index = $(this).data('index');
        let $fila = $(this).closest('tr');
        
        let cant = parseInt($fila.find('.item-hist-cantidad').val());
        let prec = parseFloat($fila.find('.item-hist-precio').val());
        let desc = parseFloat($fila.find('.item-hist-descuento').val());

        if (cant > 0) itemsConsulta[index].cantidad = cant;
        if (prec >= 0) itemsConsulta[index].precio = prec;
        if (desc >= 0) itemsConsulta[index].descuento = desc;
        
        actualizarTablaItemsHistorial(); // Redibuja la tabla para actualizar el subtotal
    });
    // --- FIN DE NUEVA SECCIÓN ---

// --- INICIO DE MODIFICACIÓN: VISITA RÁPIDA ---
    $(document).on('click', '#btnVisitaRapida', function() {
        let pacienteId = $(this).data('paciente-id');
        
        // 1. Primero, obtenemos la lista de médicos
        $.ajax({
            url: '../app/controllers/HistorialController.php',
            type: 'GET',
            dataType: 'json',
            data: { action: 'get_medicos_activos' },
            success: function(medicosOptions) {
                // 2. Si tenemos médicos, mostramos el popup de selección
                Swal.fire({
                    title: 'Seleccione un Médico',
                    text: 'Se creará una nueva cita para registrar esta visita.',
                    input: 'select',
                    inputOptions: medicosOptions,
                    inputPlaceholder: 'Seleccione un médico...',
                    showCancelButton: true,
                    cancelButtonText: 'Cancelar',
                    confirmButtonText: 'Crear Visita',
                    inputValidator: (value) => {
                        return new Promise((resolve) => {
                            if (value) {
                                resolve();
                            } else {
                                resolve('¡Necesita seleccionar un médico!');
                            }
                        });
                    }
                }).then((result) => {
                    // 3. Si el usuario confirma, creamos la visita
                    if (result.isConfirmed) {
                        let medicoIdSeleccionado = result.value;
                        
                        Swal.fire({
                            title: 'Creando Visita Rápida...',
                            allowOutsideClick: false,
                            didOpen: () => { Swal.showLoading(); }
                        });

                        // 4. Hacemos el POST para crear la cita
                        $.ajax({
                            url: '../app/controllers/HistorialController.php',
                            type: 'POST',
                            contentType: 'application/json',
                            data: JSON.stringify({
                                action: 'crear_visita_rapida',
                                id_paciente: pacienteId,
                                id_medico: medicoIdSeleccionado
                            }),
                            success: function(response) {
                                Swal.close();
                                
                                // 5. Abrimos el modal de historial
                                $('#formNuevaEntradaHistorial').trigger('reset');
                                $('#historial_id').val(''); 
                                $('#modalEntradaLabel').text('Registrar Nueva Entrada (Visita Rápida)');
                                $('#historial_id_cita').val(response.cita_id);
                                $('#historial_id_paciente').val(response.paciente_id);
                                $('#historial_id_medico').val(response.medico_id);
                                
                                $('#selectDiagnosticos').val(null).trigger('change');
                                itemsConsulta = [];
                                actualizarTablaItemsHistorial(); 

                                $('#consultaTab button[data-bs-target="#tab-consulta"]').tab('show');
                                cargarPlantillasDropdown();
                                $('#modalNuevaEntrada').modal('show');
                            },
                            error: function(jqXHR) {
                                let errMsg = jqXHR.responseJSON ? jqXHR.responseJSON.message : 'No se pudo crear la visita.';
                                Swal.fire('Error', errMsg, 'error');
                            }
                        });
                    }
                });
            },
            error: function() {
                Swal.fire('Error', 'No se pudo cargar la lista de médicos.', 'error');
            }
        });
    });
    // --- FIN DE MODIFICACIÓN ---

});