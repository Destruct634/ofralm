$(document).ready(function() {
    $('#formConfigFacturacion').submit(function(e) {
        e.preventDefault();
        let data = {
            prefijo_correlativo: $('#prefijo_correlativo').val(),
            siguiente_numero: $('#siguiente_numero').val()
        };

        $.ajax({
            url: '../app/controllers/ConfiguracionFacturacionController.php',
            type: 'PUT',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(response) {
                Swal.fire('¡Éxito!', response.message, 'success');
            },
            error: function(jqXHR) {
                let errorMessage = 'No se pudo completar la operación.';
                if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                    errorMessage = jqXHR.responseJSON.message;
                }
                Swal.fire('Error', errorMessage, 'error');
            }
        });
    });
});
