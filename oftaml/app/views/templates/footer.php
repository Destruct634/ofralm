<?php if (!empty($system_config['mostrar_footer_bar']) && $system_config['mostrar_footer_bar'] == 1): ?>
    <footer class="footer-bar bg-dark text-white-50 py-1 mt-4">
        <div class="container-fluid d-flex justify-content-between">
            <span id="footer-clock"></span>
            <span>HC<i>ophthalmós</i> Versión 3.3.16</span> </div>
    </footer>
<?php endif; ?>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-bs5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/lang/summernote-es-ES.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Configuración Global de Seguridad (CSRF)
    $.ajaxSetup({
        headers: {
            'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Configuración global para DataTables (Idioma Español Global)
    $.extend(true, $.fn.dataTable.defaults, {
        language: {
            "decimal": "",
            "emptyTable": "No hay datos disponibles en la tabla",
            "info": "Mostrando _START_ a _END_ de _TOTAL_ entradas",
            "infoEmpty": "Mostrando 0 a 0 de 0 entradas",
            "infoFiltered": "(filtrado de _MAX_ entradas totales)",
            "infoPostFix": "",
            "thousands": ",",
            "lengthMenu": "Mostrar _MENU_ entradas",
            "loadingRecords": "Cargando...",
            "processing": "Procesando...",
            "search": "Buscar:",
            "zeroRecords": "No se encontraron registros coincidentes",
            "paginate": { "first": "Primero", "last": "Último", "next": "Siguiente", "previous": "Anterior" },
            "aria": { "sortAscending": ": activar para ordenar la columna ascendente", "sortDescending": ": activar para ordenar la columna descendente" }
        },
        ajax: {
            error: function (jqXHR, textStatus, errorThrown) {
                if (jqXHR.status == 403) {
                    let msg = 'No tienes permiso para ver esta información.';
                    if (jqXHR.responseJSON && jqXHR.responseJSON.message) { msg = jqXHR.responseJSON.message; }
                    Swal.fire({ title: 'Acceso Denegado', text: msg, icon: 'warning', confirmButtonText: 'Entendido' }).then(() => { if (msg.includes('Token CSRF')) { location.reload(); } });
                    $(this).DataTable().clear().draw();
                } else {
                    // Opcional: Silenciar errores comunes de DataTables si no son críticos
                    console.error("DataTable Error:", textStatus, errorThrown);
                }
            }
        }
    });
</script>

<?php if (!empty($system_config['mostrar_footer_bar']) && $system_config['mostrar_footer_bar'] == 1): ?>
<script>
    function updateClock() {
        const now = new Date();
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
        document.getElementById('footer-clock').textContent = now.toLocaleDateString('es-ES', options);
    }
    setInterval(updateClock, 1000);
    updateClock();
</script>
<?php endif; ?>

<?php 
$page = $_GET['page'] ?? 'dashboard'; 

// --- GESTOR DE SCRIPTS JS ---
if ($page === 'pacientes'): ?>
    <script src="js/pacientes.js?v=<?php echo time(); ?>"></script>
<?php elseif ($page === 'citas'): ?>
    <script src="js/citas.js?v=<?php echo time(); ?>"></script>
<?php elseif ($page === 'medicos'): ?>
    <script src="js/medicos.js?v=<?php echo time(); ?>"></script>
<?php elseif ($page === 'especialidades'): ?>
    <script src="js/especialidades.js?v=<?php echo time(); ?>"></script>
<?php elseif ($page === 'historial'): ?>
    <script src="js/historial.js?v=<?php echo time(); ?>"></script>
<?php elseif ($page === 'textos_predefinidos'): ?>
    <script src="js/textos_predefinidos.js?v=<?php echo time(); ?>"></script>
<?php elseif ($page === 'usuarios'): ?>
    <script src="js/usuarios.js?v=<?php echo time(); ?>"></script>
<?php elseif ($page === 'grupos'): ?>
    <script src="js/grupos.js?v=<?php echo time(); ?>"></script>
<?php elseif ($page === 'aseguradoras'): ?>
    <script src="js/aseguradoras.js?v=<?php echo time(); ?>"></script>
<?php elseif ($page === 'tipos_isv'): ?>
    <script src="js/tipos_isv.js?v=<?php echo time(); ?>"></script>
<?php elseif ($page === 'proveedores'): ?>
    <script src="js/proveedores.js?v=<?php echo time(); ?>"></script>
<?php elseif ($page === 'categorias_producto'): ?>
    <script src="js/categorias_producto.js?v=<?php echo time(); ?>"></script>
<?php elseif ($page === 'productos'): ?>
    <script src="js/productos.js?v=<?php echo time(); ?>"></script>
<?php elseif ($page === 'configuracion'): ?>
    <script src="js/configuracion.js?v=<?php echo time(); ?>"></script>

<?php elseif ($page === 'mis_citas'): ?>
    <script src="js/historial.js?v=<?php echo time(); ?>"></script>
    <script src="js/portal_medico.js?v=<?php echo time(); ?>"></script>

<?php elseif ($page === 'inventario'): ?>
    <script src="js/movimientos.js?v=<?php echo time(); ?>"></script>
<?php elseif ($page === 'compras'): ?>
    <script src="js/compras.js?v=<?php echo time(); ?>"></script>
<?php elseif ($page === 'facturacion'): ?>
    <script src="js/facturacion.js?v=<?php echo time(); ?>"></script>
<?php elseif ($page === 'cuentas_por_cobrar'): ?>
    <script src="js/cuentas_por_cobrar.js?v=<?php echo time(); ?>"></script>
<?php elseif ($page === 'notas_credito'): ?>
    <script src="js/notas_credito.js?v=<?php echo time(); ?>"></script>
<?php elseif ($page === 'cotizaciones'): ?>
    <script src="js/cotizaciones.js?v=<?php echo time(); ?>"></script>
<?php elseif ($page === 'categorias_servicio'): ?>
    <script src="js/categorias_servicio.js?v=<?php echo time(); ?>"></script>
<?php elseif ($page === 'servicios'): ?>
    <script src="js/servicios.js?v=<?php echo time(); ?>"></script>
<?php elseif ($page === 'reporte_stock'): ?>
    <script src="js/reporte_stock.js?v=<?php echo time(); ?>"></script>
<?php elseif ($page === 'configuracion_facturacion'): ?>
    <script src="js/configuracion_facturacion.js?v=<?php echo time(); ?>"></script>
<?php elseif ($page === 'dashboard_settings'): ?>
    <script src="js/dashboard_settings.js?v=<?php echo time(); ?>"></script>
<?php elseif ($page === 'archivo_categorias'): ?>
    <script src="js/archivo_categorias.js?v=<?php echo time(); ?>"></script>
<?php elseif ($page === 'consulta_plantillas'): ?>
    <script src="js/consulta_plantillas.js?v=<?php echo time(); ?>"></script>
<?php elseif ($page === 'diagnosticos'): ?>
    <script src="js/diagnosticos.js?v=<?php echo time(); ?>"></script>
<?php elseif ($page === 'reportes_clinicos'): ?>
    <script src="js/reportes_clinicos.js?v=<?php echo time(); ?>"></script>
<?php elseif ($page === 'reportes_medicos'): ?>
    <script src="js/reportes_medicos.js?v=<?php echo time(); ?>"></script>
<?php elseif ($page === 'reportes_ventas'): ?>
    <script src="js/reportes_ventas.js?v=<?php echo time(); ?>"></script>
<?php endif; ?>

</body>
</html>