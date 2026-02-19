$(document).ready(function() {
    // Ocultar el botón "Nuevo" para forzar la creación desde el módulo de Usuarios
    // (Los médicos se crean vinculados a un usuario del sistema)
    $('#btnNuevoMedico').hide();

    let tablaMedicos = $('#tablaMedicos').DataTable({
        "ajax": { "url": "../app/controllers/MedicoController.php", "dataSrc": "data" },
        "columns": [
            { "data": "id" },
            { "data": "nombres" },
            { "data": "apellidos" },
            { "data": "especialidad" },
            { "data": "telefono" },
            { "data": "email" },
            { 
                "data": "estado", 
                "render": function(data) {
                    if (data === 'Activo') { 
                        return '<span class="badge bg-success">Activo</span>'; 
                    } else { 
                        return '<span class="badge bg-danger">Inactivo</span>'; 
                    }
                }
            },
            { 
                "data": null,
                "render": function(data, type, row) {
                    // La gestión de creación se centraliza en Usuarios, pero permitimos ver/editar aquí si se desea
                    // Por ahora mostramos un mensaje informativo o botones limitados
                    return "<div class='text-center'><i>Gestionar desde Usuarios</i></div>";
                }
            }
        ]
        // NOTA: Se eliminó la línea "language" para usar la configuración global del footer (español).
    });
});