const sidebar = document.querySelector('[data-sidebar]');
const overlay = document.querySelector('[data-sidebar-overlay]');

document.querySelectorAll('[data-sidebar-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        sidebar?.classList.toggle('open');
        overlay?.classList.toggle('open');
    });
});

overlay?.addEventListener('click', () => {
    sidebar?.classList.remove('open');
    overlay?.classList.remove('open');
});

document.querySelectorAll('[data-table-search]').forEach((input) => {
    input.addEventListener('input', () => {
        const table = input.closest('.results-card')?.querySelector('[data-report-table]');
        if (!table) return;
        const needle = input.value.toLowerCase();
        table.querySelectorAll('tbody tr').forEach((row) => {
            row.style.display = row.innerText.toLowerCase().includes(needle) ? '' : 'none';
        });
    });
});

const charts = window.simgosCharts;
if (window.Chart && charts) {
    const common = { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } };
    const axis = { color: '#9ca3af', grid: { color: '#eef2f2', drawBorder: false }, ticks: { font: { size: 10 }, color: '#9ca3af' } };
    const chartConfig = {
        trend: { type: 'line', options: { ...common, scales: { x: axis, y: { ...axis, beginAtZero: true } } }, dataset: { borderColor: '#027d78', backgroundColor: 'rgba(2,125,120,.1)', fill: true, tension: .35, pointRadius: 3, pointBackgroundColor: '#027d78' } },
        category: { type: 'bar', options: { ...common, scales: { x: { ...axis, grid: { display: false } }, y: { ...axis, beginAtZero: true } } }, dataset: { backgroundColor: ['#027d78', '#45a5a0', '#76beb9', '#a8d8d4', '#d2eeec'], borderRadius: 5 } },
        distribution: { type: 'doughnut', options: { ...common, cutout: '68%', plugins: { legend: { display: true, position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, padding: 12, font: { size: 10 } } } } }, dataset: { backgroundColor: ['#027d78', '#36a39e', '#f59e0b', '#94a3b8'], borderWidth: 0 } },
        unit: { type: 'bar', options: { ...common, indexAxis: 'y', scales: { x: { ...axis, beginAtZero: true }, y: { ...axis, grid: { display: false } } } }, dataset: { backgroundColor: '#3d9e99', borderRadius: 5 } },
    };
    Object.entries(charts).forEach(([name, data]) => {
        const canvas = document.querySelector(`[data-chart="${name}"]`);
        const config = chartConfig[name];
        if (!canvas || !config) return;
        new Chart(canvas, { type: config.type, data: { labels: data.labels, datasets: [{ ...config.dataset, label: name, data: data.data }] }, options: config.options });
    });
}
