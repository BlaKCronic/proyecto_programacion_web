function initializeAdminCharts() {
    const ctxVentas = document.getElementById('ventasDiasChart');
    if(ctxVentas) {
        try {
            const ventasData = JSON.parse(ctxVentas.dataset.ventas || '[]');
            const ventasFechas = JSON.parse(ctxVentas.dataset.fechas || '[]');
            
            new Chart(ctxVentas, {
                type: 'line',
                data: {
                    labels: ventasFechas,
                    datasets: [{
                        label: 'Ventas',
                        data: ventasData,
                        borderColor: 'rgb(78, 115, 223)',
                        backgroundColor: 'rgba(78, 115, 223, 0.1)',
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        } catch(e) {
            console.error('Error cargando gráfico de ventas:', e);
        }
    }

    const ctxEstado = document.getElementById('pedidosEstadoChart');
    if(ctxEstado) {
        try {
            const estadosData = JSON.parse(ctxEstado.dataset.estados || '[]');
            
            new Chart(ctxEstado, {
                type: 'doughnut',
                data: {
                    labels: ['Pendiente', 'Procesando', 'Enviado', 'Entregado', 'Cancelado'],
                    datasets: [{
                        data: estadosData,
                        backgroundColor: [
                            'rgb(246, 194, 62)',
                            'rgb(54, 185, 204)',
                            'rgb(78, 115, 223)',
                            'rgb(28, 200, 138)',
                            'rgb(231, 74, 59)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                font: {
                                    size: 11
                                }
                            }
                        }
                    }
                }
            });
        } catch(e) {
            console.error('Error cargando gráfico de estados:', e);
        }
    }

    const ctxProductosCategoria = document.getElementById('productosCategoria');
    if(ctxProductosCategoria) {
        try {
            const categoriasNombres = JSON.parse(ctxProductosCategoria.dataset.nombres || '[]');
            const categoriasData = JSON.parse(ctxProductosCategoria.dataset.cantidad || '[]');
            
            new Chart(ctxProductosCategoria, {
                type: 'bar',
                data: {
                    labels: categoriasNombres,
                    datasets: [{
                        label: 'Cantidad de productos',
                        data: categoriasData,
                        backgroundColor: 'rgba(78, 115, 223, 0.8)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        } catch(e) {
            console.error('Error cargando gráfico de categorías:', e);
        }
    }

    const ctxTopVendedores = document.getElementById('topVendedoresChart');
    if(ctxTopVendedores) {
        try {
            const vendedoresNombres = JSON.parse(ctxTopVendedores.dataset.vendedores || '[]');
            const vendedoresData = JSON.parse(ctxTopVendedores.dataset.ventas || '[]');
            
            new Chart(ctxTopVendedores, {
                type: 'bar',
                data: {
                    labels: vendedoresNombres,
                    datasets: [{
                        label: 'Ventas totales',
                        data: vendedoresData,
                        backgroundColor: 'rgba(28, 200, 138, 0.8)'
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        } catch(e) {
            console.error('Error cargando gráfico de vendedores:', e);
        }
    }
}

if(document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeAdminCharts);
} else {
    setTimeout(function() {
        if(typeof initializeAdminCharts === 'function') {
            initializeAdminCharts();
        } else {
            console.error('initializeAdminCharts no está disponible');
        }
    }, 100);
}
