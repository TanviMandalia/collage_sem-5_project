(function(){
  if (!window.CHART_DATA) return;
  const { catLabels = [], catData = [], monthLabels = [], ordersData = [], lineSeries } = window.CHART_DATA;

  // Static theme: fixed look & feel irrespective of incoming series colors
  const THEME = {
    seriesColorMap: {
      'Visits': '#ef4444',
      'Bounce Rate': '#f59e0b',
      'Pageviews': '#3b82f6',
      'Sales': '#10b981',
      'Revenue': '#8b5cf6'
    },
    defaultPalette: ['#ef4444','#f59e0b','#10b981','#3b82f6','#8b5cf6','#14b8a6','#f97316','#e11d48'],
    line: { width: 2, tension: 0.35 },
    point: { radius: 2, hoverRadius: 3, hitRadius: 6 },
    gridColor: 'rgba(0,0,0,0.06)'
  };

  function pickColor(label, idx){
    if (label && THEME.seriesColorMap[label] ) return THEME.seriesColorMap[label];
    return THEME.defaultPalette[idx % THEME.defaultPalette.length];
  }

  const pieEl = document.getElementById('chartProductsByCategory');
  if (pieEl && typeof Chart !== 'undefined') {
    new Chart(pieEl, {
      type: 'pie',
      data: {
        labels: catLabels,
        datasets: [{
          label: 'Products',
          data: catData,
          backgroundColor: ['#60a5fa','#34d399','#f59e0b','#ef4444','#a78bfa','#22c55e','#f97316','#f43f5e'],
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        layout: { padding: 0 },
        plugins: {
          legend: { position: 'bottom', labels: { padding: 8, boxWidth: 12 } },
          tooltip: { enabled: true }
        }
      }
    });
  }

  const lineEl = document.getElementById('chartMonthlyOrders');
  if (lineEl && typeof Chart !== 'undefined') {
    const series = Array.isArray(lineSeries) && lineSeries.length
      ? lineSeries
      : [{ label: 'Orders', data: ordersData }];

    const ctx = lineEl.getContext('2d');

    function hexToRgb(hex){
      const m = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
      return m ? { r: parseInt(m[1],16), g: parseInt(m[2],16), b: parseInt(m[3],16) } : { r:79,g:70,b:229 };
    }
    function makeAreaGradient(ctx, color){
      const { r,g,b } = hexToRgb(color);
      const gradient = ctx.createLinearGradient(0, 0, 0, ctx.canvas.height || 200);
      gradient.addColorStop(0, `rgba(${r},${g},${b},0.25)`);
      gradient.addColorStop(1, `rgba(${r},${g},${b},0.02)`);
      return gradient;
    }

    const datasets = series.map((s, idx) => {
      const label = s.label ?? `Series ${idx+1}`;
      const color = pickColor(label, idx);
      return {
        label,
        data: Array.isArray(s.data) ? s.data : [],
        borderColor: color,
        backgroundColor: makeAreaGradient(ctx, color),
        fill: false,
        tension: THEME.line.tension,
        pointRadius: THEME.point.radius,
        pointHoverRadius: THEME.point.hoverRadius,
        borderWidth: THEME.line.width,
        spanGaps: true,
        cubicInterpolationMode: 'monotone'
      };
    });

    const smallHeight = (lineEl.clientHeight || lineEl.height || 0) <= 80;

    new Chart(lineEl, {
      type: 'line',
      data: {
        labels: monthLabels,
        datasets
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        layout: { padding: 0 },
        plugins: {
          legend: smallHeight ? { display: false } : { display: true, position: 'top', align: 'start', labels: { usePointStyle: true, padding: 12 } },
          tooltip: { mode: 'index', intersect: false }
        },
        interaction: { mode: 'index', intersect: false },
        elements: { line: { borderWidth: smallHeight ? 1.5 : THEME.line.width }, point: { radius: smallHeight ? 0 : THEME.point.radius, hitRadius: THEME.point.hitRadius, hoverRadius: smallHeight ? 0 : THEME.point.hoverRadius } },
        scales: {
          x: smallHeight ? {
            display: false
          } : {
            offset: false,
            grid: { display: true, drawBorder: false, color: THEME.gridColor },
            ticks: { maxRotation: 0, autoSkipPadding: 8 }
          },
          y: smallHeight ? {
            display: false
          } : {
            beginAtZero: true,
            grace: '8%',
            ticks: { precision: 0, padding: 6 },
            grid: { drawBorder: false, color: THEME.gridColor }
          }
        }
      }
    });
  }
})();

