$(document).ready(function() {
    $('#tablaStock').DataTable({
        "ajax": { 
            "url": "../app/controllers/ProductoController.php", // Reutilizamos el controlador de productos
            "dataSrc": "data" 
        },
        "columns": [
            { "data": "id" },
            { "data": "nombre_producto" },
            { "data": "nombre_categoria", "defaultContent": "N/A" },
            { "data": "stock_actual" },
            { "data": "stock_minimo" },
            { 
                "data": null,
                "render": function(data, type, row) {
                    if (row.es_inventariable != 1) {
                        return '<span class="badge bg-secondary">No Inventariable</span>';
                    }
                    if (parseInt(row.stock_actual) <= parseInt(row.stock_minimo)) {
                        return '<span class="badge bg-danger">Bajo Stock</span>';
                    }
                    return '<span class="badge bg-success">OK</span>';
                }
            }
        ],
        "createdRow": function(row, data, dataIndex) {
            if (data.es_inventariable == 1 && parseInt(data.stock_actual) <= parseInt(data.stock_minimo)) {
                $(row).addClass('table-danger');
            }
        },

    });
});
