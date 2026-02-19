$(document).ready(function() {
    
    // Cargar el gráfico de Top 10 Diagnósticos
    $.ajax({
        url: '../app/controllers/AjaxController.php',
        type: 'GET',
        dataType: 'json',
        data: {
            action: 'getTopDiagnosticos'
        },
        success: function(response) {
            if (response.labels && response.data) {
                renderizarGraficoTop10(response.labels, response.data);
            }
        },
        error: function() {
            console.error('Error al cargar datos para el gráfico Top 10 Diagnósticos.');
            $('#chartTopDiagnosticos').parent().html('<p class="text-danger text-center">No se pudieron cargar los datos del gráfico.</p>');
        }
    });

    // --- INICIO DE NUEVA SECCIÓN ---
    // Cargar el gráfico de Distribución de Población
    $.ajax({
        url: '../app/controllers/AjaxController.php',
        type: 'GET',
        dataType: 'json',
        data: {
            action: 'getDistribucionPoblacion'
        },
        success: function(response) {
            if (response.labels && response.datasets) {
                renderizarGraficoPoblacion(response);
            }
        },
        error: function() {
            console.error('Error al cargar datos para el gráfico de Población.');
            $('#chartDistribucionPoblacion').parent().html('<p class="text-danger text-center">No se pudieron cargar los datos del gráfico.</p>');
        }
    });
    // --- FIN DE NUEVA SECCIÓN ---

    // --- INICIO DE NUEVO GRÁFICO: ERRORES REFRACTIVOS ---
    $.ajax({
        url: '../app/controllers/AjaxController.php',
        type: 'GET',
        dataType: 'json',
        data: {
            action: 'getDistribucionErrores' // La acción que definimos en el Paso 1
        },
        success: function(response) {
            // El modelo ya envía los datos formateados
            if (response.labels && response.datasets) {
                renderizarGraficoErrores(response);
            }
        },
        error: function() {
            console.error('Error al cargar datos para el gráfico de Errores Refractivos.');
            $('#chartErroresRefractivos').parent().html('<p class="text-danger text-center">No se pudieron cargar los datos del gráfico.</p>');
        }
    });
    // --- FIN DE NUEVO GRÁFICO ---


    function renderizarGraficoTop10(labels, data) {
        const ctx = document.getElementById('chartTopDiagnosticos');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'N° de Casos',
                    data: data,
                    backgroundColor: 'rgba(78, 115, 223, 0.8)',
                    borderColor: 'rgba(78, 115, 223, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y', // Hace el gráfico de barras horizontal
                maintainAspectRatio: false,
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1 
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ` Casos: ${context.parsed.x}`;
                            }
                        }
                    }
                }
            }
        });
    }

    // --- INICIO DE NUEVA FUNCIÓN ---
    function renderizarGraficoPoblacion(data) {
        const ctx = document.getElementById('chartDistribucionPoblacion');
        new Chart(ctx, {
            type: 'doughnut', // Gráfico de Dona
            data: data, // El modelo ya envía los datos formateados
            options: {
                maintainAspectRatio: false,
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed !== null) {
                                    label += context.parsed + ' pacientes';
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    }
    // --- FIN DE NUEVA FUNCIÓN ---

    // --- INICIO DE NUEVA FUNCIÓN ---
    function renderizarGraficoErrores(data) {
        const ctx = document.getElementById('chartErroresRefractivos');
        new Chart(ctx, {
            type: 'doughnut', // Gráfico de Dona
            data: data, // El modelo ya envía los datos listos para Chart.js
            options: {
                maintainAspectRatio: false,
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed !== null) {
                                    // El dataset.label viene del modelo
                                    label += context.parsed + ' ' + context.dataset.label;
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    }
    // --- FIN DE NUEVA FUNCIÓN ---

});