$(document).ready(function() {
    const widgetList = $('#widget-list');

    // Cargar los widgets existentes
    function cargarWidgets() {
        $.get('../app/controllers/DashboardWidgetController.php', function(widgets) {
            widgetList.empty();
            if (widgets.length === 0) {
                widgetList.append('<li class="list-group-item">No se encontraron widgets para configurar.</li>');
                return;
            }

            widgets.forEach(widget => {
                const isChecked = widget.activo == '1' ? 'checked' : '';
                
                const selectAdmin = widget.rol_requerido === 'Admin' ? 'selected' : '';
                const selectTodos = widget.rol_requerido === 'Todos' ? 'selected' : '';

                const itemHtml = `
                    <li class="list-group-item widget-item" data-id="${widget.id}">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h6 class="mb-1">${widget.titulo}</h6>
                                <small class="text-muted">${widget.descripcion}</small>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select form-select-sm widget-rol">
                                    <option value="Todos" ${selectTodos}>Visible para Todos</option>
                                    <option value="Admin" ${selectAdmin}>Solo Administradores</option>
                                </select>
                            </div>
                            <div class="col-md-3 text-end">
                                <div class="form-check form-switch d-inline-block">
                                    <input class="form-check-input widget-toggle" type="checkbox" role="switch" ${isChecked}>
                                    <label class="form-check-label">Visible</label>
                                </div>
                            </div>
                        </div>
                    </li>
                `;
                widgetList.append(itemHtml);
            });
        });
    }

    cargarWidgets();

    // Guardar los cambios
    $('#btnGuardarDashboardSettings').click(function() {
        const btn = $(this);
        const widgetConfig = [];

        $('.widget-item').each(function(index) {
            let item = $(this);
            widgetConfig.push({
                id: item.data('id'),
                activo: item.find('.widget-toggle').is(':checked') ? 1 : 0,
                orden: (index + 1) * 10, // Multiplicamos por 10 para dar espacio
                rol_requerido: item.find('.widget-rol').val()
            });
        });

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

        $.ajax({
            url: '../app/controllers/DashboardWidgetController.php',
            type: 'PUT',
            contentType: 'application/json',
            data: JSON.stringify(widgetConfig),
            success: function(response) {
                Swal.fire('¡Éxito!', response.message, 'success');
            },
            error: function(jqXHR) {
                Swal.fire('Error', jqXHR.responseJSON.message, 'error');
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i>Guardar Cambios');
            }
        });
    });
});