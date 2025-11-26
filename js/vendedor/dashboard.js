function initializeDashboardCharts() {
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
                        borderColor: 'rgb(254, 189, 105)',
                        backgroundColor: 'rgba(254, 189, 105, 0.1)',
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

    const ctxStock = document.getElementById('stockChart');
    if(ctxStock) {
        try {
            const stockData = JSON.parse(ctxStock.dataset.stock || '[]');
            
            new Chart(ctxStock, {
                type: 'doughnut',
                data: {
                    labels: ['Stock Alto (20+)', 'Stock Medio (10-19)', 'Stock Bajo (1-9)', 'Sin Stock'],
                    datasets: [{
                        data: stockData,
                        backgroundColor: [
                            'rgb(28, 200, 138)',
                            'rgb(54, 185, 204)',
                            'rgb(246, 194, 62)',
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
            console.error('Error cargando gráfico de stock:', e);
        }
    }

    const ctxTopProductos = document.getElementById('topProductosChart');
    if(ctxTopProductos) {
        try {
            const productosNombres = JSON.parse(ctxTopProductos.dataset.nombres || '[]');
            const productosVentas = JSON.parse(ctxTopProductos.dataset.ventas || '[]');
            
            new Chart(ctxTopProductos, {
                type: 'bar',
                data: {
                    labels: productosNombres,
                    datasets: [{
                        label: 'Unidades vendidas',
                        data: productosVentas,
                        backgroundColor: 'rgba(254, 189, 105, 0.8)'
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
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        } catch(e) {
            console.error('Error cargando gráfico de productos:', e);
        }
    }

    const ctxCategorias = document.getElementById('categoriasChart');
    if(ctxCategorias) {
        try {
            const categoriasNombres = JSON.parse(ctxCategorias.dataset.nombres || '[]');
            const categoriasData = JSON.parse(ctxCategorias.dataset.cantidad || '[]');
            
            new Chart(ctxCategorias, {
                type: 'pie',
                data: {
                    labels: categoriasNombres,
                    datasets: [{
                        data: categoriasData,
                        backgroundColor: [
                            'rgb(78, 115, 223)',
                            'rgb(28, 200, 138)',
                            'rgb(54, 185, 204)',
                            'rgb(246, 194, 62)',
                            'rgb(231, 74, 59)',
                            'rgb(133, 135, 150)',
                            'rgb(90, 92, 105)'
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
            console.error('Error cargando gráfico de categorías:', e);
        }
    }
}

if(document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeDashboardCharts);
} else {
    setTimeout(function() {
        if(typeof initializeDashboardCharts === 'function') {
            initializeDashboardCharts();
        } else {
            console.error('initializeDashboardCharts no está disponible');
        }
    }, 100);
}
