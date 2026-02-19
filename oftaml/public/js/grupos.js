$(document).ready(function() {
    if (USER_GROUP_ID != 1 && (!PERMISOS.grupos || !PERMISOS.grupos.crear)) {
        $('#btnNuevo').hide();
    }

    let tabla = $('#tablaGrupos').DataTable({
        "ajax": { "url": "../app/controllers/GrupoController.php", "dataSrc": "data" },
        "columns": [
            { "data": "id" }, { "data": "nombre_grupo" },
            { 
                "data": null, 
                "render": function(data, type, row) {
                    let botones = "<div class='text-center'><div class='btn-group'>";
                    if (USER_GROUP_ID == 1 || (PERMISOS.grupos && PERMISOS.grupos.editar == 1)) {
                        botones += `<button class='btn btn-info btn-sm btnEditar'><i class='fas fa-edit'></i></button>`;
                        if (row.id != 1) {
                            botones += `<button class='btn btn-warning btn-sm btnPermisos text-white' title='Gestionar Permisos'><i class='fas fa-key'></i></button>`;
                        } else {
                            botones += `<button class='btn btn-secondary btn-sm text-white' disabled title='Admin Total'><i class='fas fa-key'></i></button>`;
                        }
                    }
                    if (USER_GROUP_ID == 1 || (PERMISOS.grupos && PERMISOS.grupos.borrar == 1)) {
                        if(row.id != 1 && row.id != 2 && row.id != 3 && row.id != 4) {
                             botones += "<button class='btn btn-danger btn-sm btnBorrar'><i class='fas fa-trash'></i></button>";
                        } else {
                             botones += "<button class='btn btn-danger btn-sm' disabled><i class='fas fa-trash'></i></button>";
                        }
                    }
                    botones += "</div></div>";
                    return botones;
                }
            }
        ],
    });

    $("#btnNuevo").click(function() {
        $("#formGrupo").trigger("reset");
        $('#id').val(null);
        $("#modalLabel").text("Nuevo Grupo");
        $("#modalGrupo").modal("show");
    });

    $('#formGrupo').submit(function(e) { e.preventDefault();
        let id = $.trim($('#id').val());
        let data = { id: id, nombre_grupo: $.trim($('#nombre_grupo').val()) };
        $.ajax({
            url: '../app/controllers/GrupoController.php', type: id ? 'PUT' : 'POST',
            contentType: 'application/json', data: JSON.stringify(data),
            success: function(r) { Swal.fire('¡Éxito!', r.message, 'success'); tabla.ajax.reload(null, false); $('#modalGrupo').modal('hide'); },
            error: function() { Swal.fire('Error', 'No se pudo completar la operación.', 'error'); }
        });
    });
    
    $(document).on("click", ".btnEditar", function() {
        let data = tabla.row($(this).closest("tr")).data();
        $('#id').val(data.id); $('#nombre_grupo').val(data.nombre_grupo);
        $("#modalLabel").text("Editar Grupo"); $("#modalGrupo").modal("show");
    });
    
    $(document).on("click", ".btnBorrar", function() {
        let fila = $(this).closest('tr');
        let data = tabla.row(fila).data();
        Swal.fire({
            title: '¿Estás seguro?', text: `Se eliminará el grupo "${data.nombre_grupo}".`,
            icon: 'warning', showCancelButton: true, confirmButtonText: 'Sí, ¡bórralo!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '../app/controllers/GrupoController.php', type: 'DELETE',
                    contentType: 'application/json', data: JSON.stringify({ id: data.id }),
                    success: function(response) {
                        Swal.fire('¡Eliminado!', response.message, 'success');
                        tabla.row(fila).remove().draw();
                    }
                });
            }
        });
    });

    // --- LÓGICA DE PERMISOS CON PESTAÑAS ---
    $(document).on("click", ".btnPermisos", function() {
        let data = tabla.row($(this).closest("tr")).data();
        $('#id_grupo_permiso').val(data.id);
        $('#nombreGrupoPermisos').text(data.nombre_grupo);
        
        // Limpiar y mostrar cargando
        $('#permisosTabs').empty();
        $('#permisosTabsContent').html('<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-3x text-primary"></i><p class="mt-2">Cargando permisos...</p></div>');
        
        $.ajax({
            url: `../app/controllers/GrupoController.php?action=get_permisos&id=${data.id}`, type: 'GET',
            success: function(permisos) {
                $('#permisosTabsContent').empty();

                // 1. Agrupar permisos por categoría
                let grupos = {};
                // Orden deseado de las pestañas
                const ordenCategorias = ['Clínica', 'Ventas', 'Inventario', 'Reportes', 'Configuración', 'Catálogos'];
                
                permisos.forEach(p => {
                    let cat = p.categoria || 'Otros';
                    if (!grupos[cat]) grupos[cat] = [];
                    grupos[cat].push(p);
                });

                // 2. Generar HTML
                let isFirst = true;
                
                // Iteramos según el orden preferido, y luego los que sobren
                let categoriasEncontradas = Object.keys(grupos);
                let categoriasOrdenadas = ordenCategorias.filter(c => categoriasEncontradas.includes(c));
                let categoriasRestantes = categoriasEncontradas.filter(c => !ordenCategorias.includes(c));
                let todasCategorias = [...categoriasOrdenadas, ...categoriasRestantes];

                todasCategorias.forEach((cat, index) => {
                    let safeCat = cat.replace(/[^a-zA-Z0-9]/g, '_'); // ID seguro para HTML
                    let activeClass = isFirst ? 'active' : '';
                    let showClass = isFirst ? 'show active' : '';
                    isFirst = false;

                    // Crear Pestaña
                    $('#permisosTabs').append(`
                        <li class="nav-item" role="presentation">
                            <button class="nav-link ${activeClass}" id="tab-${safeCat}" data-bs-toggle="tab" data-bs-target="#content-${safeCat}" type="button" role="tab">
                                ${cat}
                            </button>
                        </li>
                    `);

                    // Crear Contenido (Tabla)
                    let filas = '';
                    grupos[cat].forEach(p => {
                        filas += `
                            <tr data-id-modulo="${p.id_modulo}">
                                <td class="align-middle fw-bold text-primary">${p.nombre_display}</td>
                                <td class="text-center"><input class="form-check-input perm-check" type="checkbox" name="ver" ${p.ver == 1 ? 'checked' : ''}></td>
                                <td class="text-center"><input class="form-check-input perm-check" type="checkbox" name="crear" ${p.crear == 1 ? 'checked' : ''}></td>
                                <td class="text-center"><input class="form-check-input perm-check" type="checkbox" name="editar" ${p.editar == 1 ? 'checked' : ''}></td>
                                <td class="text-center"><input class="form-check-input perm-check" type="checkbox" name="borrar" ${p.borrar == 1 ? 'checked' : ''}></td>
                                <td class="text-center"><button type="button" class="btn btn-sm btn-outline-secondary btn-toggle-row" title="Todo/Nada"><i class="fas fa-check-double"></i></button></td>
                            </tr>
                        `;
                    });

                    let tablaHtml = `
                        <div class="tab-pane fade ${showClass}" id="content-${safeCat}" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered align-middle">
                                    <thead class="table-light text-center">
                                        <tr>
                                            <th class="text-start" style="width: 30%;">Módulo</th>
                                            <th>Ver</th>
                                            <th>Crear</th>
                                            <th>Editar</th>
                                            <th>Borrar</th>
                                            <th style="width: 50px;">All</th>
                                        </tr>
                                    </thead>
                                    <tbody>${filas}</tbody>
                                </table>
                            </div>
                        </div>
                    `;
                    $('#permisosTabsContent').append(tablaHtml);
                });

                $('#modalPermisos').modal('show');
            }
        });
    });
    
    // Botón para marcar toda la fila
    $(document).on('click', '.btn-toggle-row', function() {
        let fila = $(this).closest('tr');
        let checkboxes = fila.find('.perm-check');
        // Si todos están marcados, desmarcamos todos. Si falta alguno, marcamos todos.
        let todosMarcados = checkboxes.length === checkboxes.filter(':checked').length;
        checkboxes.prop('checked', !todosMarcados);
    });

    $('#formPermisos').submit(function(e) { e.preventDefault();
        let id_grupo = $('#id_grupo_permiso').val(); 
        let permisos = [];
        
        // Buscamos en TODAS las pestañas (no solo la visible)
        $('#permisosTabsContent tr[data-id-modulo]').each(function() {
            let f = $(this);
            permisos.push({
                id_modulo: f.data('id-modulo'), 
                ver: f.find('input[name="ver"]').is(':checked'),
                crear: f.find('input[name="crear"]').is(':checked'), 
                editar: f.find('input[name="editar"]').is(':checked'),
                borrar: f.find('input[name="borrar"]').is(':checked'),
            });
        });

        $.ajax({
            url: '../app/controllers/GrupoController.php?action=update_permisos',
            type: 'POST',
            data: { id_grupo: id_grupo, permisos: JSON.stringify(permisos) },
            success: function(r) { Swal.fire('¡Éxito!', r.message, 'success'); $('#modalPermisos').modal('hide'); },
            error: function(jqXHR) {
                let errorMessage = 'No se pudieron guardar los permisos.';
                if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                    errorMessage = jqXHR.responseJSON.message;
                }
                Swal.fire('Error', errorMessage, 'error');
            }
        });
    });
});