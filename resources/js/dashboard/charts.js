/**
 * Signature D3 + SVG chart components for the Executive Dashboard.
 *
 * Every renderer draws into a supplied <svg> element using the application's
 * CSS-variable design tokens, re-renders when the theme changes, sizes
 * responsively, and honours prefers-reduced-motion.
 */
import * as d3 from 'd3';

const REDUCED_MOTION =
    typeof window.matchMedia === 'function' &&
    window.matchMedia('(prefers-reduced-motion: reduce)').matches;

const DURATION = REDUCED_MOTION ? 0 : 650;

/** Resolve a CSS variable instrumented by themes.css/themes.css. */
function cssVar(name, fallback = 'transparent') {
    const value = getComputedStyle(document.documentElement)
        .getPropertyValue(name)
        .trim();

    return value || fallback;
}

/** Snapshot of the active theme palette. */
function palette() {
    return {
        primary: cssVar('--color-primary', '#1e3f20'),
        primaryLight: cssVar('--color-primary-light', '#4c784e'),
        success: cssVar('--color-success', '#00a86b'),
        warning: cssVar('--color-warning', '#ff9900'),
        error: cssVar('--color-error', '#dc3545'),
        text: cssVar('--color-text-primary', '#1a1a1a'),
        muted: cssVar('--color-text-muted', '#80868b'),
        border: cssVar('--color-border-secondary', '#e8eaed'),
        track: cssVar('--color-bg-tertiary', '#ebebeb'),
        surface: cssVar('--color-surface', '#ffffff'),
        accent: '#0bf700',
    };
}

/** Compact human-friendly number (e.g. $1.2M) for chart labels. */
function formatCompact(value, currency = false) {
    const abs = Math.abs(value);
    let out;

    if (abs >= 1e9) {
        out = `${(value / 1e9).toFixed(1)}B`;
    } else if (abs >= 1e6) {
        out = `${(value / 1e6).toFixed(1)}M`;
    } else if (abs >= 1e3) {
        out = `${(value / 1e3).toFixed(1)}K`;
    } else {
        out = String(Math.round(value * 100) / 100);
    }

    return currency ? `$${out}` : out;
}

function fullNumber(value, currency = false) {
    const out = new Intl.NumberFormat('en-US', {
        style: currency ? 'currency' : 'decimal',
        currency: 'USD',
        maximumFractionDigits: 0,
    }).format(value);

    return out;
}

/** Clear the previous render and return a sized D3 selection. */
function base(el, options = {}) {
    const width = Math.max(el.clientWidth, 40);
    const height = Math.max(el.clientHeight, 40);

    el.setAttribute('viewBox', `0 0 ${width} ${height}`);
    el.setAttribute('preserveAspectRatio', 'xMidYMid meet');

    const svg = d3.select(el);
    svg.selectAll('*').remove();

    return { svg, width, height };
}

/** Remove any previously attached tooltip node. */
function clearTooltip(wrapper) {
    if (wrapper) {
        wrapper.querySelector?.('.dashboard-chart-tooltip')?.remove();
    }
}

function showTooltip(wrapper, svgEl, event, html) {
    if (!wrapper) {
        return;
    }

    clearTooltip(wrapper);

    const tooltip = document.createElement('div');
    tooltip.className = 'dashboard-chart-tooltip';
    tooltip.innerHTML = html;

    wrapper.appendChild(tooltip);

    const svgRect = svgEl.getBoundingClientRect();
    const wrapRect = wrapper.getBoundingClientRect();
    const pointX = (event.pageX ?? svgRect.left) - wrapRect.left;
    const pointY = (event.pageY ?? svgRect.top) - wrapRect.top;

    const tooltipWidth = tooltip.offsetWidth;
    const tooltipHeight = tooltip.offsetHeight;

    tooltip.style.left = `${Math.min(Math.max(8, pointX - tooltipWidth / 2), wrapRect.width - tooltipWidth - 8)}px`;
    tooltip.style.top = `${Math.max(8, pointY - tooltipHeight - 14)}px`;
}

/** Derive an SVG path gradient definition for an area fill. */
function addGradient(svg, id, color, opacity = 0.28) {
    const defs = svg.append('defs');
    const gradient = defs
        .append('linearGradient')
        .attr('id', id)
        .attr('x1', '0')
        .attr('y1', '0')
        .attr('x2', '0')
        .attr('y2', '1');

    gradient.append('stop').attr('offset', '0%').attr('stop-color', color).attr('stop-opacity', opacity);
    gradient.append('stop').attr('offset', '100%').attr('stop-color', color).attr('stop-opacity', 0.02);
}

/* -------------------------------------------------------------------------- */
/* Area                                                                        */
/* -------------------------------------------------------------------------- */

function renderArea(el, options) {
    const colors = palette();
    const { svg, width, height } = base(el, options);

    const margin = { top: 12, right: 12, bottom: 28, left: 42 };
    const innerWidth = width - margin.left - margin.right;
    const innerHeight = height - margin.top - margin.bottom;

    const series = options.series ?? [];
    const labels = options.labels ?? [];

    if (series.length === 0 || labels.length === 0) {
        svg.append('text')
            .attr('x', width / 2)
            .attr('y', height / 2)
            .attr('text-anchor', 'middle')
            .attr('fill', colors.muted)
            .attr('font-size', 13)
            .text('No activity yet');
        return;
    }

    const allValues = series.flatMap((s) => s.data);
    const yMax = Math.max(1, ...allValues) * 1.15;

    const x = d3.scalePoint()
        .domain(labels)
        .range([0, innerWidth])
        .padding(0.5);

    const y = d3.scaleLinear()
        .domain([0, yMax])
        .nice()
        .range([innerHeight, 0]);

    const plot = svg.append('g').attr('transform', `translate(${margin.left},${margin.top})`);

    // Horizontal gridlines
    plot.append('g')
        .attr('class', 'dashboard-chart-grid')
        .call(
            d3
                .axisLeft(y)
                .ticks(4)
                .tickSize(-innerWidth)
                .tickFormat((d) => formatCompact(d, options.currency))
        )
        .call((g) => {
            g.selectAll('.tick line').attr('stroke', colors.track);
            g.selectAll('.tick text').attr('fill', colors.muted).attr('font-size', 10);
            g.select('.domain').remove();
        });

    // X axis labels (skip every other to avoid crowding)
    plot.append('g')
        .attr('transform', `translate(0,${innerHeight})`)
        .call(d3.axisBottom(x).tickFormat((d, i) => (labels.length > 8 && i % 2 === 1 ? '' : d)))
        .call((g) => {
            g.selectAll('.tick text').attr('fill', colors.muted).attr('font-size', 10);
            g.select('.domain').attr('stroke', colors.track);
            g.selectAll('.tick line').remove();
        });

    const areaGen = d3
        .area()
        .x((d) => x(d.label))
        .y0(innerHeight)
        .y1((d) => y(d.value))
        .curve(d3.curveCatmullRom.alpha(0.5));

    const lineGen = d3
        .line()
        .x((d) => x(d.label))
        .y((d) => y(d.value))
        .curve(d3.curveCatmullRom.alpha(0.5));

    const seriesColors = [colors.success, colors.error];

    series.forEach((s, i) => {
        const data = labels.map((label, j) => ({ label, value: s.data[j] ?? 0 }));
        const color = seriesColors[i % seriesColors.length];
        const gradientId = `da-area-${i}-${Math.random().toString(36).slice(2, 8)}`;

        addGradient(svg, gradientId, color);

        plot.append('path')
            .datum(data)
            .attr('class', 'dashboard-chart-area')
            .attr('d', areaGen)
            .attr('fill', `url(#${gradientId})`)
            .attr('stroke', 'none')
            .style('opacity', 0)
            .transition()
            .duration(DURATION)
            .delay(i * 120)
            .style('opacity', 1);

        plot.append('path')
            .datum(data)
            .attr('d', lineGen)
            .attr('fill', 'none')
            .attr('stroke', color)
            .attr('stroke-width', 2)
            .attr('stroke-linecap', 'round')
            .style('opacity', 0)
            .transition()
            .duration(DURATION)
            .delay(i * 120)
            .style('opacity', 1);
    });

    // Interactive crosshair + tooltip
    const wrapper = el.closest('.dashboard-chart-wrap') ?? el.parentElement;
    const focus = plot
        .append('line')
        .attr('class', 'dashboard-chart-crosshair')
        .attr('stroke', colors.muted)
        .attr('stroke-dasharray', '3 3')
        .attr('opacity', 0)
        .attr('y1', 0)
        .attr('y2', innerHeight);

    const dots = plot
        .selectAll('.dashboard-chart-dot')
        .data(labels)
        .enter()
        .append('circle')
        .attr('class', 'dashboard-chart-dot')
        .attr('cx', (d) => x(d))
        .attr('cy', innerHeight)
        .attr('r', 3)
        .attr('fill', colors.surface)
        .attr('stroke', colors.primaryLight)
        .attr('stroke-width', 1.5)
        .style('pointer-events', 'none');

    svg.append('rect')
        .attr('class', 'dashboard-chart-hitbox')
        .attr('x', margin.left)
        .attr('y', margin.top)
        .attr('width', innerWidth)
        .attr('height', innerHeight)
        .attr('fill', 'transparent')
        .style('cursor', 'crosshair')
        .on('mousemove', (event) => {
            const [mx] = d3.pointer(event, plot.node());
            const idx = Math.round(((mx / innerWidth) * (labels.length - 1)));
            if (idx < 0 || idx >= labels.length) {
                return;
            }

            const cx = x(labels[idx]);
            focus.attr('x1', cx).attr('x2', cx).attr('opacity', 1);

            dots
                .attr('cy', (d, j) => y(Math.max(0, ...series.map((s) => s.data[j] ?? 0))))
                .attr('r', (d, j) => (j === idx ? 5 : 3))
                .attr('fill', (d, j) => (j === idx ? colors.primary : colors.surface));

            const rows = series
                .map((s) => `<div class="dashboard-chart-tooltip-row"><span style="color:${seriesColors[series.indexOf(s)]};">●</span><span>${s.name}</span><strong>${fullNumber(s.data[idx], options.currency)}</strong></div>`)
                .join('');

            showTooltip(
                wrapper,
                el,
                event,
                `<div class="dashboard-chart-tooltip-label">${labels[idx]}</div>${rows}`
            );
        })
        .on('mouseleave', () => {
            focus.attr('opacity', 0);
            dots.attr('cy', innerHeight).attr('r', 3).attr('fill', colors.surface);
            clearTooltip(wrapper);
        });
}

/* -------------------------------------------------------------------------- */
/* Horizontal bars                                                             */
/* -------------------------------------------------------------------------- */

function renderBars(el, options) {
    const colors = palette();
    const { svg, width, height } = base(el, options);

    const margin = { top: 6, right: 56, bottom: 8, left: 4 };
    const innerWidth = width - margin.left - margin.right;
    const innerHeight = height - margin.top - margin.bottom;

    const labels = options.labels ?? [];
    const values = options.values ?? [];

    if (labels.length === 0) {
        svg.append('text')
            .attr('x', width / 2)
            .attr('y', height / 2)
            .attr('text-anchor', 'middle')
            .attr('fill', colors.muted)
            .attr('font-size', 13)
            .text('No data yet');
        return;
    }

    const yMax = Math.max(1, ...values);
    const y = d3.scaleBand()
        .domain(labels)
        .range([0, innerHeight])
        .paddingInner(0.45);

    const x = d3.scaleLinear().domain([0, yMax]).range([0, innerWidth]);

    const plot = svg.append('g').attr('transform', `translate(${margin.left},${margin.top})`);

    plot.append('g')
        .attr('transform', `translate(0,${innerHeight})`)
        .call(d3.axisBottom(x).ticks(0))
        .call((g) => g.select('.domain').attr('stroke', colors.track))
        .call((g) => g.selectAll('.tick').remove());

    const fill = options.currency ? colors.primary : colors.primaryLight;

    plot.selectAll('.dashboard-chart-bar')
        .data(labels.map((label, i) => ({ label, value: values[i] ?? 0 })))
        .enter()
        .append('rect')
        .attr('class', 'dashboard-chart-bar')
        .attr('y', (d) => y(d.label))
        .attr('x', 0)
        .attr('width', 0)
        .attr('height', y.bandwidth())
        .attr('rx', Math.min(5, y.bandwidth() / 2))
        .attr('fill', fill)
        .attr('opacity', 0.85)
        .transition()
        .duration(DURATION)
        .delay((d, i) => i * 70)
        .attr('width', (d) => x(d.value))
        .attr('opacity', 1);

    plot.selectAll('.dashboard-chart-bar-label')
        .data(labels.map((label, i) => ({ label, value: values[i] ?? 0 })))
        .enter()
        .append('text')
        .attr('class', 'dashboard-chart-bar-label')
        .attr('x', (d) => x(d.value) + 8)
        .attr('y', (d) => y(d.label) + y.bandwidth() / 2)
        .attr('dy', '0.35em')
        .attr('fill', colors.muted)
        .attr('font-size', 11)
        .attr('opacity', 0)
        .text((d) => formatCompact(d.value, options.currency))
        .transition()
        .duration(DURATION)
        .delay((d, i) => i * 70 + 350)
        .attr('opacity', 1);

    // Wrap long labels
    plot.selectAll('.dashboard-chart-bar-label').each(function (d) {
        const node = d3.select(this);
        const text = node.text();
        const maxChars = Math.floor(innerWidth / 7);
        if (text.length > maxChars) {
            node.text(text.slice(0, maxChars - 1) + '…').attr('title', text);
        }
    });
}

/* -------------------------------------------------------------------------- */
/* Donut                                                                       */
/* -------------------------------------------------------------------------- */

function renderDonut(el, options) {
    const colors = palette();
    const { svg, width, height } = base(el, options);

    const wrapper = el.closest('.dashboard-chart-wrap') ?? el.parentElement;
    wrapper.querySelectorAll?.('.dashboard-chart-legend').forEach((n) => n.remove());

    const segments = options.data ?? [];
    const total = segments.reduce((sum, s) => sum + (Number(s.value) || 0), 0);

    const size = Math.min(width, height);
    const radius = size / 2 - 8;
    const innerRadius = radius * 0.6;

    const center = { x: width / 2, y: height / 2 - 8 };

    const typeColors = { success: colors.success, warning: colors.warning, error: colors.error };

    const pie = d3
        .pie()
        .value((d) => d.value)
        .sort(null)
        .padAngle(0.025);

    const arc = d3.arc().innerRadius(innerRadius).outerRadius(radius).cornerRadius(4);

    const g = svg.append('g').attr('transform', `translate(${center.x},${center.y})`);

    const colorFor = (colorKey) => typeColors[colorKey] ?? colors.primary;

    const slices = g
        .selectAll('.dashboard-chart-slice')
        .data(pie(segments.filter((s) => s.value > 0)))
        .enter()
        .append('path')
        .attr('class', 'dashboard-chart-slice')
        .attr('fill', (d) => colorFor(d.data.color))
        .attr('stroke', colors.surface)
        .attr('stroke-width', 1.5);

    if (REDUCED_MOTION) {
        slices.attr('d', arc).attr('opacity', 1);
    } else {
        slices
            .attr('d', () => arc({ ...{ startAngle: 0, endAngle: 0 } }))
            .attr('opacity', 1);

        slices
            .transition()
            .duration(DURATION)
            .delay((d, i) => i * 120)
            .attrTween('d', function (d) {
                const interpolate = d3.interpolate(0, d.endAngle - d.startAngle);
                return (t) =>
                    arc({
                        ...d,
                        startAngle: d.startAngle,
                        endAngle: d.startAngle + interpolate(t),
                    });
            });
    }

    // Center total
    g.append('text')
        .attr('text-anchor', 'middle')
        .attr('dy', '-0.2em')
        .attr('fill', colors.text)
        .attr('font-size', size > 180 ? 20 : 16)
        .attr('font-weight', 600)
        .text(formatCompact(total, options.currency));

    g.append('text')
        .attr('text-anchor', 'middle')
        .attr('dy', '1.2em')
        .attr('fill', colors.muted)
        .attr('font-size', 11)
        .text(options.totalLabel ?? 'Total');

    // HTML legend inside the widget body, below the svg.
    const legend = document.createElement('div');
    legend.className = 'dashboard-chart-legend';
    legend.innerHTML = segments
        .map((segment) => {
            const share = total > 0 ? Math.round(((Number(segment.value) || 0) * 100) / total) : 0;
            return `<div class="dashboard-chart-legend-item" data-label="${segment.label}">
                        <span class="dashboard-chart-legend-dot" style="background:${colorFor(segment.color)}"></span>
                        <span class="dashboard-chart-legend-label">${segment.label}</span>
                        <span class="dashboard-chart-legend-value">${formatCompact(segment.value)} · ${share}%</span>
                    </div>`;
        })
        .join('');
    wrapper.appendChild(legend);
    wrapper.classList.add('dashboard-chart-wrap-has-legend');

    // Hover highlight + legend emphasis
    const legendItems = legend.querySelectorAll('.dashboard-chart-legend-item');
    const legendNodes = Array.from(legendItems);

    slices
        .style('cursor', 'pointer')
        .on('mouseover', function (event, d) {
            slices.attr('opacity', 0.3).attr('transform', 'none');
            d3.select(this).attr('opacity', 1).attr('transform', 'scale(1.035)');
            legendNodes.forEach((node) => node.classList.remove('is-highlighted'));
            const index = segments.findIndex((s) => s.label === d.data.label);
            if (legendNodes[index]) {
                legendNodes[index].classList.add('is-highlighted');
            }
        })
        .on('mouseleave', () => {
            slices.attr('opacity', 1).attr('transform', 'none');
            legendNodes.forEach((node) => node.classList.remove('is-highlighted'));
        });
}

/* -------------------------------------------------------------------------- */
/* Sparkline                                                                   */
/* -------------------------------------------------------------------------- */

function renderSpark(el, options) {
    const colors = palette();
    const { svg, width, height } = base(el, options);

    const values = options.values ?? [];
    const labels = options.labels ?? [];

    if (values.length === 0) {
        return;
    }

    const margin = { top: 8, right: 8, bottom: 8, left: 8 };
    const innerWidth = width - margin.left - margin.right;
    const innerHeight = height - margin.top - margin.bottom;

    const yMax = Math.max(1, ...values);
    const x = d3.scalePoint()
        .domain(labels)
        .range([0, innerWidth])
        .padding(0.5);
    const y = d3.scaleLinear()
        .domain([0, yMax])
        .range([innerHeight, 0]);

    const plot = svg.append('g').attr('transform', `translate(${margin.left},${margin.top})`);

    const data = labels.map((label, i) => ({ label, value: values[i] ?? 0 }));
    const gradientId = `da-spark-${Math.random().toString(36).slice(2, 8)}`;

    addGradient(svg, gradientId, colors.primary, 0.35);

    const areaGen = d3
        .area()
        .x((d) => x(d.label))
        .y0(innerHeight)
        .y1((d) => y(d.value))
        .curve(d3.curveCatmullRom.alpha(0.5));

    const lineGen = d3
        .line()
        .x((d) => x(d.label))
        .y((d) => y(d.value))
        .curve(d3.curveCatmullRom.alpha(0.5));

    plot.append('path')
        .datum(data)
        .attr('d', areaGen)
        .attr('fill', `url(#${gradientId})`)
        .attr('stroke', 'none');

    plot.append('path')
        .datum(data)
        .attr('d', lineGen)
        .attr('fill', 'none')
        .attr('stroke', colors.primary)
        .attr('stroke-width', 2)
        .attr('stroke-linecap', 'round');

    const last = data[data.length - 1];
    plot.append('circle')
        .attr('cx', x(last.label))
        .attr('cy', y(last.value))
        .attr('r', 4)
        .attr('fill', colors.surface)
        .attr('stroke', colors.primary)
        .attr('stroke-width', 2);

    // Subtle hover tooltip
    const wrapper = el.closest('.dashboard-chart-wrap') ?? el.parentElement;

    plot.append('rect')
        .attr('width', innerWidth)
        .attr('height', innerHeight)
        .attr('fill', 'transparent')
        .on('mousemove touchmove', (event) => {
            const [mx] = d3.pointer(event, plot.node());
            const idx = Math.max(0, Math.min(labels.length - 1, Math.round((mx / innerWidth) * (labels.length - 1))));
            showTooltip(wrapper, el, event, `<div class="dashboard-chart-tooltip-label">${labels[idx]}</div><strong>${values[idx]}</strong> present`);
        })
        .on('mouseleave', () => clearTooltip(wrapper));
}

/* -------------------------------------------------------------------------- */
/* Radial gauge                                                                */
/* -------------------------------------------------------------------------- */

function renderGauge(el, options) {
    const colors = palette();
    const { svg, width, height } = base(el, options);

    const value = Number(options.value) || 0;
    const absValue = Math.abs(value);

    const size = Math.min(width * 0.86, height * 1.05);
    const radius = (size / 2) - 6;
    const cx = width / 2;
    const cy = height * 0.78;

    const angle = (value / Math.max(absValue * 1.25, 1000)) * Math.PI;

    const bgArc = d3.arc()
        .innerRadius(radius * 0.68)
        .outerRadius(radius)
        .cornerRadius(6);

    svg.append('path')
        .attr('d', bgArc({ startAngle: Math.PI, endAngle: 2 * Math.PI }))
        .attr('transform', `translate(${cx},${cy})`)
        .attr('fill', colors.track);

    const clamp = Math.max(-Math.PI, Math.min(Math.PI, angle));

    const valueArc = d3.arc()
        .innerRadius(radius * 0.68)
        .outerRadius(radius)
        .cornerRadius(6);

    if (REDUCED_MOTION) {
        svg.append('path')
            .attr('d', valueArc({ startAngle: Math.PI, endAngle: Math.PI + clamp }))
            .attr('transform', `translate(${cx},${cy})`)
            .attr('fill', value < 0 ? colors.error : colors.success)
            .attr('opacity', 0.9);
    } else {
        svg.append('path')
            .attr('d', valueArc({ startAngle: Math.PI, endAngle: Math.PI }))
            .attr('transform', `translate(${cx},${cy})`)
            .attr('fill', value < 0 ? colors.error : colors.success)
            .attr('opacity', 0.9)
            .transition()
            .duration(DURATION)
            .attrTween('d', function () {
                const interpolate = d3.interpolate(0, clamp);
                return (t) => valueArc({ startAngle: Math.PI, endAngle: Math.PI + interpolate(t) });
            });
    }

    svg.append('text')
        .attr('x', cx)
        .attr('y', cy - 4)
        .attr('text-anchor', 'middle')
        .attr('fill', colors.text)
        .attr('font-size', Math.min(26, radius * 0.42))
        .attr('font-weight', 600)
        .text(formatCompact(value, options.currency));

    svg.append('text')
        .attr('x', cx)
        .attr('y', cy + 20)
        .attr('text-anchor', 'middle')
        .attr('fill', colors.muted)
        .attr('font-size', 11)
        .text(value < 0 ? 'Negative balance' : 'Available balance');
}

/* -------------------------------------------------------------------------- */
/* Dispatch                                                                    */
/* -------------------------------------------------------------------------- */

export function renderChart(el, options = {}) {
    switch (options.type) {
        case 'area':
            renderArea(el, options);
            break;
        case 'bars':
            renderBars(el, options);
            break;
        case 'donut':
            renderDonut(el, options);
            break;
        case 'spark':
            renderSpark(el, options);
            break;
        case 'gauge':
            renderGauge(el, options);
            break;
        default:
            break;
    }
}

const instances = new WeakSet();

/**
 * Register the Alpine `sigChart` data component.
 */
export function registerCharts(Alpine) {
    Alpine.data('sigChart', (options = {}) => ({
        options,
        resizeObserver: null,
        render() {
            const el = this.$refs.chart;
            if (!el) {
                return;
            }
            renderChart(el, this.options);
            instances.add(el);
        },
        init() {
            this.render();

            const wrapper = this.$el;
            if (typeof ResizeObserver === 'function') {
                this.resizeObserver = new ResizeObserver(
                    () => {
                        this.render();
                    },
                );
                this.resizeObserver.observe(wrapper);
            }

            // Repaint against the new palette when the theme is toggled.
            document.addEventListener('theme-updated', () => {
                this.render();
            });
        },
    }));
}