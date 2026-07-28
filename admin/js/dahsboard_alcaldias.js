$(document).ready(function () {
    var d = window.dashData || {};
    var C = {
        sky: '#60A5FA', violet: '#A78BFA', emerald: '#34D399', amber: '#FBBF24',
        rose: '#FB7185', cyan: '#22D3EE', pink: '#F472B6', soft: '#93C5FD',
        grid: 'rgba(255,255,255,.08)', tick: 'rgba(255,255,255,.82)', border: 'rgba(255,255,255,.18)'
    };
    var PIE_COLORS = [C.sky, C.violet, C.emerald, C.amber, C.rose, C.cyan, C.pink, C.soft];
    var gridCfg = { color: C.grid };

    // breakdown dots
    if (d.pieLabels) {
        for (var i = 0; i < d.pieLabels.length; i++) {
            var dot = document.getElementById('dot_' + i);
            if (dot) dot.style.background = secColor(d.pieLabels[i]);
        }
    }

    function createChart(id, config) {
        try {
            var el = document.getElementById(id);
            if (!el) return;
            var ctx = el.getContext('2d');
            if (!ctx) return;
            return new Chart(el, config);
        } catch (e) {
            console.warn('Chart ' + id + ':', e.message);
            return null;
        }
    }

    /* ---- Top Proyectos por Inversión ---- */
    if (d.topProyectosLabels && d.topProyectosLabels.length > 0) {
        createChart('chartTopProyectos', {
            type: 'bar',
            data: {
                labels: d.topProyectosLabels,
                datasets: [{
                    label: 'Inversión (Millones $)',
                    data: d.topProyectosValores,
                    backgroundColor: d.topProyectosLabels.map(function (_, i) { return PIE_COLORS[i % PIE_COLORS.length]; }),
                    borderColor: d.topProyectosLabels.map(function (_, i) { return PIE_COLORS[i % PIE_COLORS.length]; }),
                    borderWidth: 1, borderRadius: 8,
                    barThickness: 32, maxBarThickness: 40
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, indexAxis: 'y',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(15,23,42,.92)', titleColor: '#fff',
                        bodyColor: '#94a3b8', borderColor: 'rgba(255,255,255,.10)',
                        borderWidth: 1, padding: 10
                    }
                },
                scales: {
                    x: { grid: { color: 'rgba(255,255,255,.05)' }, ticks: { color: '#94a3b8', font: { weight: 'bold', size: 10 } }, beginAtZero: true },
                    y: { grid: { display: false }, ticks: { color: '#cbd5e1', font: { weight: 'bold', size: 10 } } }
                }
            }
        });
    }

    function secColor(name) { return d.secColorMap && d.secColorMap[name] ? d.secColorMap[name] : PIE_COLORS[0]; }

    /* ---- Top Secretarías Inversión ---- */
    if (d.ranking && d.ranking.length > 0) {
        createChart('chartTopSec', {
            type: 'pie',
            data: {
                labels: d.ranking.map(function (r) { return r.name; }),
                datasets: [{
                    data: d.ranking.map(function (r) { return r.score; }),
                    backgroundColor: d.ranking.map(function (r) { return secColor(r.name); }),
                    borderColor: 'rgba(255,255,255,.85)',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: { color: '#cbd5e1', font: { weight: 'bold', size: 11 }, padding: 14, boxWidth: 14 }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(15,23,42,.92)', titleColor: '#fff',
                        bodyColor: '#94a3b8', borderColor: 'rgba(255,255,255,.10)',
                        borderWidth: 1, padding: 10,
                        callbacks: {
                            label: function (ctx) {
                                var total = ctx.dataset.data.reduce(function (a, b) { return a + b; }, 0);
                                var pct = total > 0 ? ((ctx.parsed / total) * 100).toFixed(1) : 0;
                                return ctx.label + ': $' + ctx.parsed.toLocaleString('es-CO') + 'M (' + pct + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    /* ---- Pie ---- */
    if (d.pieLabels && d.pieLabels.length > 0 && d.proyectosConSec) {
        var pieChart = createChart('pieSecretarias', {
            type: 'pie',
            data: {
                labels: d.pieLabels,
                datasets: [{ data: d.pieValues, backgroundColor: d.pieLabels.map(function (l) { return secColor(l); }), borderColor: 'rgba(255,255,255,.85)', borderWidth: 2, hoverBorderWidth: 3 }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: function (ctx) { return ctx.label + ': ' + (ctx.parsed || 0) + ' proyectos'; } } }
                },
                onClick: function (e, elements) {
                    if (!elements || !elements.length) return;
                    var idx = elements[0].index;
                    var secretaria = this.data.labels[idx];
                    var proyectos = d.proyectosConSec.filter(function (p) { return p.secretaria === secretaria; });
                    if (!proyectos.length) return;
                    var html = '<table>' +
                        '<thead><tr><th>#</th><th>Proyecto</th><th>Valor</th><th>Estado</th></tr></thead><tbody>';
                    proyectos.forEach(function (p, i) {
                        var val = '$' + parseFloat(p.valor_proyecto || 0).toLocaleString('es-CO');
                        html += '<tr><td>' + (i + 1) + '</td><td>' + (p.proyecto || '') + '</td><td>' + val + '</td><td>' + (p.estado_proyecto || '') + '</td></tr>';
                    });
                    html += '</tbody></table></div>';
                    document.getElementById('modalPieTitle').textContent = secretaria + ' (' + proyectos.length + ' proyectos)';
                    document.getElementById('modalPieBody').innerHTML = html;
                    $('#modalPieSecretarias').modal('show');
                }
            }
        });
    }

    /* ---- Barras Proyectos por Secretaría ---- */
    if (d.barLabels && d.barLabels.length > 0) {
        createChart('barPlan', {
            type: 'bar',
            data: {
                labels: d.barLabels,
                datasets: [{
                    label: 'Proyectos', data: d.barValues,
                    backgroundColor: d.barLabels.map(function (l) { return secColor(l); }),
                    borderColor: 'rgba(255,255,255,.35)', borderWidth: 1, borderRadius: 14, barThickness: 26, maxBarThickness: 32
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                scales: {
                    x: { grid: gridCfg, ticks: { color: C.tick } },
                    y: { beginAtZero: true, grid: gridCfg, ticks: { color: C.tick } }
                },
                plugins: { legend: { display: false } }
            }
        });
    }

    /* ---- Radar ---- */
    if (d.radarLabels && d.radarLabels.length > 0) {
        createChart('radarBalance', {
            type: 'radar',
            data: {
                labels: d.radarLabels,
                datasets: [{
                    label: '%', data: d.radarValues,
                    borderColor: C.cyan, backgroundColor: 'rgba(34,211,238,.14)',
                    pointBackgroundColor: C.cyan, pointBorderColor: '#fff', pointRadius: 3
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                scales: { r: { grid: { color: 'rgba(255,255,255,.10)' }, angleLines: { color: 'rgba(255,255,255,.10)' }, pointLabels: { color: 'rgba(255,255,255,.86)', font: { weight: '900' } }, ticks: { color: 'rgba(255,255,255,.65)', backdropColor: 'rgba(0,0,0,0)' } } },
                plugins: { legend: { display: false } }
            }
        });
    }

    /* ---- Doughnut ---- */
    if (d.totalPactados > 0) {
        var restante = Math.max(0, d.totalPactados - d.totalCumplidos);
        createChart('doughMeta', {
            type: 'doughnut',
            data: {
                labels: ['Completados', 'Pendientes'],
                datasets: [{ data: [d.totalCumplidos, restante], backgroundColor: [C.emerald, '#475569'], borderColor: ['rgba(255,255,255,.40)', 'rgba(255,255,255,.08)'], borderWidth: 1, cutout: '70%' }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: true, labels: { color: C.tick, boxWidth: 12 } } } }
        });
    }

    /* ---- Inversión por Secretaría ---- */
    if (d.invSecLabels && d.invSecLabels.length > 0) {
        createChart('chartInvSec', {
            type: 'bar',
            data: {
                labels: d.invSecLabels,
                datasets: [{
                    label: 'Inversión (Millones $)',
                    data: d.invSecValores,
                    backgroundColor: d.invSecLabels.map(function (l) { return secColor(l); }),
                    borderColor: d.invSecLabels.map(function (l) { return secColor(l); }),
                    borderWidth: 1, borderRadius: 10, barThickness: 50, maxBarThickness: 70
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(15,23,42,.92)', titleColor: '#fff',
                        bodyColor: '#94a3b8', borderColor: 'rgba(255,255,255,.10)',
                        borderWidth: 1, padding: 10
                    }
                },
                scales: {
                    x: { grid: gridCfg, ticks: { color: C.tick, font: { weight: 'bold', size: 11 } } },
                    y: { grid: gridCfg, ticks: { color: C.tick, font: { weight: 'bold', size: 10 } }, beginAtZero: true }
                }
            }
        });
    }

    /* ---- Click visita card ---- */
    var cardVisitas = document.getElementById('cardVisitas');
    if (cardVisitas) {
        cardVisitas.addEventListener('click', function () {
            var list = d.visitasList || [];
            var html;
            if (list.length === 0) {
                html = '<div style="text-align:center;padding:40px;color:#64748b;font-weight:800;"><div style="font-size:48px;margin-bottom:10px;">📭</div><p>No hay visitas registradas para este municipio.</p></div>';
            } else {
                html = '<table><thead><tr><th>Fecha</th><th>Vereda</th><th>Tipo</th><th>Compromisos</th></tr></thead><tbody>';
                list.forEach(function (v) {
                    html += '<tr><td>' + (v.date || '') + '</td><td>' + (v.nombre_vereda || '') + '</td><td>' + (v.tipo_visita || '') + '</td><td>' + (v.compromisos || '') + '</td></tr>';
                });
                html += '</tbody></table>';
            }
            document.getElementById('modalVisitasBody').innerHTML = html;
            $('#modalVisitas').modal('show');
        });
    }
});
