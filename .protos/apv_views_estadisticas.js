export const meta = {
  title: 'Estadísticas',
  breadcrumb: 'Sistema · Estadísticas de plataforma',
};

// ── Mock analytics data ───────────────────────────
const D = {
  kpis: [
    { label: 'Visitas este mes',   val: '1,387', change: '+8.4%',  up: true,  icon: '◉', color: 'k-blue'   },
    { label: 'Usuarios activos',   val: '41',    change: '+5',     up: true,  icon: '◎', color: 'k-green'  },
    { label: 'Propiedades vistas', val: '894',   change: '+12.1%', up: true,  icon: '⬡', color: 'k-green'  },
    { label: 'Sesión promedio',    val: '4m 23s',change: '−0:18',  up: false, icon: '◷', color: 'k-orange' },
    { label: 'Tasa de rebote',     val: '34%',   change: '−2.1%',  up: true,  icon: '⇤', color: 'k-green'  },
  ],

  visits: {
    day: {
      labels: ['17/5','18/5','19/5','20/5','21/5','22/5','23/5','24/5','25/5','26/5','27/5','28/5','29/5','30/5'],
      values: [28, 42, 35, 58, 67, 49, 31, 45, 71, 63, 55, 48, 74, 68],
    },
    week: {
      labels: ['31 mar','7 abr','14 abr','21 abr','28 abr','5 may','12 may','19 may'],
      values: [287, 312, 298, 341, 268, 395, 412, 387],
    },
    month: {
      labels: ['Jun','Jul','Ago','Sep','Oct','Nov','Dic','Ene','Feb','Mar','Abr','May'],
      values: [842, 915, 788, 1024, 1156, 987, 1243, 1087, 1312, 1198, 1456, 1387],
    },
  },

  topProps: [
    { name: 'Makai Resid. — 2A',    views: 234, delta: '+12' },
    { name: 'Naviva Suite 1B',       views: 187, delta: '+8'  },
    { name: 'Makai Penthouse 4D',    views: 156, delta: '+21' },
    { name: 'LIV Studio 12C',        views: 124, delta: '−3'  },
    { name: 'Makai 3B',              views: 98,  delta: '+5'  },
    { name: 'Naviva Suite 2A',       views: 87,  delta: '+14' },
    { name: 'LIV at Cap Cana PH-1',  views: 74,  delta: '+9'  },
  ],

  topUsers: [
    { name: 'Carlos Méndez',  flag: '🇲🇽', sessions: 45, last: 'hace 2h',     pct: 100 },
    { name: 'Ana García',     flag: '🇪🇸', sessions: 38, last: 'ayer',         pct: 84  },
    { name: 'María López',    flag: '🇺🇸', sessions: 31, last: 'hace 3h',     pct: 69  },
    { name: 'James Thompson', flag: '🇺🇸', sessions: 27, last: 'hace 1 día',  pct: 60  },
    { name: 'Sophie Martin',  flag: '🇫🇷', sessions: 22, last: 'hace 2 días', pct: 49  },
    { name: 'Roberto Silva',  flag: '🇧🇷', sessions: 18, last: 'hace 3 días', pct: 40  },
  ],

  countries: [
    { name: 'Estados Unidos',  flag: '🇺🇸', pct: 42, sessions: 582 },
    { name: 'España',          flag: '🇪🇸', pct: 18, sessions: 249 },
    { name: 'México',          flag: '🇲🇽', pct: 12, sessions: 166 },
    { name: 'Rep. Dominicana', flag: '🇩🇴', pct: 10, sessions: 139 },
    { name: 'Colombia',        flag: '🇨🇴', pct: 8,  sessions: 111 },
    { name: 'Francia',         flag: '🇫🇷', pct: 5,  sessions: 69  },
    { name: 'UAE',             flag: '🇦🇪', pct: 3,  sessions: 42  },
    { name: 'Otros',           flag: '🌍',  pct: 2,  sessions: 29  },
  ],

  hours: [3,2,1,1,2,4,8,15,28,45,67,78,65,58,72,84,79,68,52,38,27,18,11,6],

  devices: [
    { label: 'Desktop', pct: 54, color: 'var(--green-txt)' },
    { label: 'Mobile',  pct: 38, color: 'var(--blue)'      },
    { label: 'Tablet',  pct: 8,  color: 'var(--orange)'    },
  ],

  sources: [
    { label: 'Acceso directo',    pct: 35, color: 'var(--green-txt)' },
    { label: 'Búsqueda orgánica', pct: 28, color: 'var(--blue)'      },
    { label: 'Redes sociales',    pct: 22, color: 'var(--orange)'    },
    { label: 'Referidos',         pct: 15, color: '#9b7ed4'          },
  ],

  funnel: [
    { label: 'Visitas únicas',   val: 1387 },
    { label: 'Ven una unidad',   val: 894  },
    { label: 'Descargan info',   val: 412  },
    { label: 'Contactan asesor', val: 163  },
    { label: 'Abren expediente', val: 41   },
  ],
};

// ── Chart helpers ─────────────────────────────────
function barChart(values, labels) {
  const max = Math.max(...values);
  return `
    <div style="display:flex;gap:3px;height:100%">
      ${values.map((v, i) => {
        const pct    = Math.max(Math.round((v / max) * 100), 3);
        const isLast = i === values.length - 1;
        const opacity = isLast ? 1 : 0.3 + (v / max) * 0.5;
        return `
          <div style="flex:1;min-width:0;display:flex;flex-direction:column;align-items:stretch">
            <div style="flex:1;display:flex;align-items:flex-end">
              <div title="${labels[i]}: ${v}"
                style="width:100%;height:${pct}%;background:var(--green-txt);border-radius:3px 3px 0 0;opacity:${opacity}">
              </div>
            </div>
            <div style="height:18px;display:flex;align-items:center;justify-content:center;font-size:9px;color:var(--sub);overflow:hidden;white-space:nowrap;padding:0 1px">
              ${labels[i]}
            </div>
          </div>`;
      }).join('')}
    </div>`;
}

function hBar(pct, opacity = 1) {
  return `<div style="background:var(--bg-surface);border-radius:4px;height:4px;flex:1;margin-top:5px">
    <div style="height:4px;border-radius:4px;background:var(--green-txt);width:${pct}%;opacity:${opacity}"></div>
  </div>`;
}

function segBar(items) {
  return `<div style="display:flex;height:8px;border-radius:6px;overflow:hidden;gap:1px;margin-bottom:16px">
    ${items.map(d => `<div style="width:${d.pct}%;background:${d.color};border-radius:2px"></div>`).join('')}
  </div>`;
}

// ── Render ────────────────────────────────────────
export function render(data) {
  const maxHour     = Math.max(...D.hours);
  const peakHour    = D.hours.indexOf(maxHour);
  const funnelMax   = D.funnel[0].val;
  const funnelColors = ['rgba(58,122,189,.9)','rgba(58,122,189,.7)','rgba(74,94,63,.85)','rgba(130,184,112,.75)','var(--green-txt)'];

  return `
<div class="view-container">

  <div class="view-header">
    <h1 class="view-title">Estadísticas</h1>
    <span style="font-size:11px;color:var(--sub)">1 – 30 de mayo 2026</span>
    <div class="view-actions">
      <button class="btn btn-ghost btn-sm" onclick="openExportModal('Estadísticas')">↓ Exportar</button>
    </div>
  </div>

  <!-- KPIs -->
  <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:10px;margin-bottom:20px">
    ${D.kpis.map(k => `
      <div class="kpi-card ${k.color}">
        <div class="kpi-label">${k.label}</div>
        <div class="kpi-value" style="font-size:22px;margin-bottom:4px">${k.val}</div>
        <div style="font-size:10px;color:${k.up ? 'var(--green-txt)' : 'var(--orange)'};font-weight:500">
          ${k.up ? '↑' : '↓'} ${k.change} <span style="color:var(--sub);font-weight:400">vs anterior</span>
        </div>
      </div>
    `).join('')}
  </div>

  <!-- Visitas + Propiedades más vistas -->
  <div style="display:grid;grid-template-columns:3fr 2fr;gap:14px;margin-bottom:14px">

    <div class="panel" style="display:flex;flex-direction:column">
      <div class="panel-header" style="flex-shrink:0">
        <div>
          <div class="panel-title">Visitas a la plataforma</div>
          <div style="font-size:10px;color:var(--sub);margin-top:2px">Usuarios únicos por período</div>
        </div>
        <div id="chart-tabs" style="display:flex;background:var(--bg-surface);border:1px solid var(--border);border-radius:6px;padding:2px;gap:1px;margin-left:auto">
          ${[['Día','day'],['Semana','week'],['Mes','month']].map(([l, k], i) => `
            <button class="chart-tab-btn" data-period="${k}"
              style="padding:4px 11px;font-size:10px;line-height:1;border-radius:4px;border:none;cursor:pointer;background:${i === 0 ? 'var(--bg-card)' : 'transparent'};color:${i === 0 ? 'var(--cream)' : 'var(--sub)'};transition:background .15s,color .15s;font-family:inherit;outline:none;appearance:none;-webkit-appearance:none;white-space:nowrap">
              ${l}
            </button>
          `).join('')}
        </div>
      </div>
      <div style="flex:1;min-height:0;padding:8px 16px 16px" id="visits-chart-wrap">
        ${barChart(D.visits.day.values, D.visits.day.labels)}
      </div>
    </div>

    <div class="panel">
      <div class="panel-header">
        <div class="panel-title">Propiedades más vistas</div>
        <span style="font-size:10px;color:var(--sub)">este mes</span>
      </div>
      <div style="padding:4px 16px 12px">
        ${D.topProps.map((p, i) => `
          <div style="display:flex;align-items:center;gap:10px;padding:8px 0;${i < D.topProps.length - 1 ? 'border-bottom:1px solid var(--border)' : ''}">
            <div style="font-size:11px;color:var(--sub);width:16px;text-align:right;flex-shrink:0;font-variant-numeric:tabular-nums">${i + 1}</div>
            <div style="flex:1;min-width:0">
              <div style="font-size:11px;font-weight:500;color:var(--cream-dim);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${p.name}</div>
              ${hBar(Math.round((p.views / D.topProps[0].views) * 100))}
            </div>
            <div style="text-align:right;flex-shrink:0">
              <div style="font-size:13px;font-weight:600;color:var(--cream);font-variant-numeric:tabular-nums">${p.views}</div>
              <div style="font-size:10px;color:${p.delta.startsWith('+') ? 'var(--green-txt)' : 'var(--orange)'};text-align:right">${p.delta}</div>
            </div>
          </div>
        `).join('')}
      </div>
    </div>
  </div>

  <!-- Top usuarios + Países + Horas -->
  <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;margin-bottom:14px">

    <div class="panel">
      <div class="panel-header">
        <div class="panel-title">Usuarios más activos</div>
        <span style="font-size:10px;color:var(--sub)">últimos 30 días</span>
      </div>
      <div style="padding:4px 16px 12px">
        ${D.topUsers.map((u, i) => `
          <div style="display:flex;align-items:center;gap:10px;padding:8px 0;${i < D.topUsers.length - 1 ? 'border-bottom:1px solid var(--border)' : ''}">
            <div style="width:30px;height:30px;border-radius:50%;background:var(--green-lite);display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:600;color:var(--green-txt);flex-shrink:0;letter-spacing:-.5px">
              ${u.name.split(' ').map(n => n[0]).join('').slice(0, 2)}
            </div>
            <div style="flex:1;min-width:0">
              <div style="font-size:11px;font-weight:500;color:var(--cream-dim);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${u.flag} ${u.name}</div>
              <div style="font-size:10px;color:var(--sub);margin-top:1px">Última sesión · ${u.last}</div>
            </div>
            <div style="text-align:right;flex-shrink:0">
              <div style="font-size:14px;font-weight:600;color:var(--cream);font-variant-numeric:tabular-nums">${u.sessions}</div>
              <div style="font-size:10px;color:var(--sub)">sesiones</div>
            </div>
          </div>
        `).join('')}
      </div>
    </div>

    <div class="panel">
      <div class="panel-header">
        <div class="panel-title">Países de origen</div>
        <span style="font-size:10px;color:var(--sub)">por sesiones</span>
      </div>
      <div style="padding:4px 16px 12px">
        ${D.countries.map((c, i) => {
          const opacity = 0.35 + (c.pct / D.countries[0].pct) * 0.65;
          return `
          <div style="display:flex;align-items:center;gap:10px;padding:6px 0;${i < D.countries.length - 1 ? 'border-bottom:1px solid var(--border)' : ''}">
            <span style="font-size:13px;flex-shrink:0;opacity:.85">${c.flag}</span>
            <div style="flex:1;min-width:0">
              <div style="display:flex;justify-content:space-between;align-items:baseline">
                <span style="font-size:11px;color:var(--cream-dim)">${c.name}</span>
                <span style="font-size:11px;font-weight:600;color:var(--cream);font-variant-numeric:tabular-nums">${c.pct}%</span>
              </div>
              ${hBar(c.pct, opacity)}
            </div>
          </div>`;
        }).join('')}
      </div>
    </div>

    <div class="panel">
      <div class="panel-header">
        <div class="panel-title">Actividad por hora</div>
        <span style="font-size:10px;color:var(--sub)">promedio diario</span>
      </div>
      <div style="padding:12px 16px 16px">
        <!-- Barra de horas -->
        <div style="display:flex;align-items:flex-end;gap:2px;height:64px">
          ${D.hours.map((v, h) => {
            const h2 = Math.max(Math.round((v / maxHour) * 100), 2);
            const isPeak = v > maxHour * 0.7;
            return `<div title="${String(h).padStart(2,'0')}:00 — ${v}" style="flex:1;height:${h2}%;background:${isPeak ? 'var(--green-txt)' : 'rgba(74,94,63,0.35)'};border-radius:2px 2px 0 0;transition:background .1s"></div>`;
          }).join('')}
        </div>
        <div style="display:flex;justify-content:space-between;margin-top:5px;margin-bottom:14px">
          <span style="font-size:9px;color:var(--muted)">0h</span>
          <span style="font-size:9px;color:var(--muted)">6h</span>
          <span style="font-size:9px;color:var(--muted)">12h</span>
          <span style="font-size:9px;color:var(--muted)">18h</span>
          <span style="font-size:9px;color:var(--muted)">23h</span>
        </div>
        <div style="background:var(--bg-surface);border-radius:8px;padding:10px 12px;border:1px solid var(--border)">
          <div style="font-size:9px;color:var(--sub);letter-spacing:.06em;text-transform:uppercase;margin-bottom:4px">Pico de actividad</div>
          <div style="font-size:15px;font-weight:600;color:var(--cream)">
            ${String(peakHour).padStart(2,'0')}:00 – ${String(peakHour + 1).padStart(2,'0')}:00h
          </div>
          <div style="font-size:10px;color:var(--sub);margin-top:3px">${maxHour} visitas · 38% sobre el promedio</div>
        </div>
      </div>
    </div>
  </div>

  <!-- Dispositivos + Fuentes + Embudo -->
  <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px">

    <div class="panel">
      <div class="panel-header">
        <div class="panel-title">Dispositivos</div>
      </div>
      <div style="padding:16px">
        ${segBar(D.devices)}
        ${D.devices.map(d => `
          <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px">
            <div style="width:8px;height:8px;border-radius:50%;background:${d.color};flex-shrink:0"></div>
            <div style="flex:1;font-size:11px;color:var(--cream-dim)">${d.label}</div>
            <div style="font-size:14px;font-weight:600;color:var(--cream);font-variant-numeric:tabular-nums">${d.pct}%</div>
          </div>
        `).join('')}
      </div>
    </div>

    <div class="panel">
      <div class="panel-header">
        <div class="panel-title">Fuentes de tráfico</div>
      </div>
      <div style="padding:16px">
        ${segBar(D.sources)}
        ${D.sources.map(s => `
          <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px">
            <div style="width:8px;height:8px;border-radius:50%;background:${s.color};flex-shrink:0"></div>
            <div style="flex:1;font-size:11px;color:var(--cream-dim)">${s.label}</div>
            <div style="font-size:14px;font-weight:600;color:var(--cream);font-variant-numeric:tabular-nums">${s.pct}%</div>
          </div>
        `).join('')}
      </div>
    </div>

    <div class="panel">
      <div class="panel-header">
        <div class="panel-title">Embudo de conversión</div>
        <span style="font-size:10px;color:var(--sub)">este mes</span>
      </div>
      <div style="padding:16px">
        ${D.funnel.map((f, i) => {
          const widthPct = Math.round((f.val / funnelMax) * 100);
          const dropPct  = i < D.funnel.length - 1
            ? Math.round((D.funnel[i + 1].val / f.val) * 100)
            : null;
          return `
          <div style="margin-bottom:${i < D.funnel.length - 1 ? '14' : '0'}px">
            <div style="display:flex;justify-content:space-between;align-items:baseline;margin-bottom:5px">
              <span style="font-size:11px;color:var(--cream-dim)">${f.label}</span>
              <span style="font-size:13px;font-weight:600;color:var(--cream);font-variant-numeric:tabular-nums">${f.val.toLocaleString()}</span>
            </div>
            <div style="background:var(--bg-surface);border-radius:4px;height:6px">
              <div style="height:6px;border-radius:4px;background:${funnelColors[i]};width:${widthPct}%;transition:width .3s"></div>
            </div>
            ${dropPct !== null
              ? `<div style="font-size:10px;color:var(--sub);margin-top:3px">→ ${dropPct}% continúan</div>`
              : `<div style="font-size:10px;color:var(--green-txt);margin-top:4px;font-weight:500">Conversión final: 2.96%</div>`
            }
          </div>`;
        }).join('')}
      </div>
    </div>
  </div>

</div>`;
}

// ── Init ──────────────────────────────────────────
export function init(data) {
  document.querySelectorAll('.chart-tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.chart-tab-btn').forEach(b => {
        b.style.background = 'transparent';
        b.style.color      = 'var(--sub)';
      });
      btn.style.background = 'var(--bg-card)';
      btn.style.color      = 'var(--cream)';

      const { values, labels } = D.visits[btn.dataset.period];
      const wrap = document.getElementById('visits-chart-wrap');
      if (wrap) wrap.innerHTML = barChart(values, labels);
    });
  });
}
