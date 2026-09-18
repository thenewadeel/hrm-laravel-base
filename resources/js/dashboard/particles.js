/**
 * Lightweight canvas particle "constellation" field for the Executive
 * Dashboard hero. Nodes drift slowly, connect with faint lines when close,
 * and gently react to the pointer. Colours are derived from the active theme
 * and repaint on theme change. Honours prefers-reduced-motion and pauses when
 * the tab is hidden or the canvas scrolls out of view.
 */

const REDUCED_MOTION =
    typeof window.matchMedia === 'function' &&
    window.matchMedia('(prefers-reduced-motion: reduce)').matches;

function cssVar(name, fallback = '#1e3f20') {
    return getComputedStyle(document.documentElement).getPropertyValue(name).trim() || fallback;
}

function getColors() {
    const primary = cssVar('--color-primary', '#1e3f20');
    const rgb = cssVar('--color-primary-rgb', '30, 63, 32');

    return { primary, rgb };
}

export function createParticleField(canvas) {
    if (!canvas) {
        return null;
    }

    const ctx = canvas.getContext('2d');
    const controller = {
        canvas,
        ctx,
        raf: null,
        particles: [],
        mouse: { x: -9999, y: -9999 },
        running: true,
        colors: getColors(),
        destroy() {
            this.running = false;
            if (this.raf) {
                cancelAnimationFrame(this.raf);
            }
            this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
        },
    };

    const resize = () => {
        const rect = canvas.getBoundingClientRect();
        const dpr = Math.min(window.devicePixelRatio || 1, 2);
        canvas.width = Math.max(rect.width, 40) * dpr;
        canvas.height = Math.max(rect.height, 40) * dpr;
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        seedParticles();
    };

    const seedParticles = () => {
        const rect = canvas.getBoundingClientRect();
        const area = rect.width * rect.height;
        const count = Math.max(18, Math.min(70, Math.round(area / 12000)));
        controller.particles = Array.from({ length: count }, () => ({
            x: Math.random() * rect.width,
            y: Math.random() * rect.height,
            vx: (Math.random() - 0.5) * 0.25,
            vy: (Math.random() - 0.5) * 0.25,
            r: 0.8 + Math.random() * 1.6,
            phase: Math.random() * Math.PI * 2,
            accent: Math.random() < 0.12,
        }));
    };

    const LINK_DISTANCE = 120;
    const MOUSE_RADIUS = 150;

    const draw = (time) => {
        if (!controller.running) {
            return;
        }

        const rect = canvas.getBoundingClientRect();
        ctx.clearRect(0, 0, rect.width, rect.height);

        const particles = controller.particles;
        const { rgb } = controller.colors;

        // Lines
        for (let i = 0; i < particles.length; i++) {
            for (let j = i + 1; j < particles.length; j++) {
                const a = particles[i];
                const b = particles[j];
                const dx = a.x - b.x;
                const dy = a.y - b.y;
                const dist = Math.hypot(dx, dy);

                if (dist < LINK_DISTANCE) {
                    const alpha = (1 - dist / LINK_DISTANCE) * 0.28;
                    ctx.strokeStyle = `rgba(${rgb}, ${alpha})`;
                    ctx.lineWidth = 1;
                    ctx.beginPath();
                    ctx.moveTo(a.x, a.y);
                    ctx.lineTo(b.x, b.y);
                    ctx.stroke();
                }
            }
        }

        // Nodes + pointer drift
        const { primary } = controller.colors;
        const timeSeconds = time / 1000;

        for (const p of particles) {
            p.x += p.vx;
            p.y += p.vy;

            const dx = controller.mouse.x - p.x;
            const dy = controller.mouse.y - p.y;
            const dist = Math.hypot(dx, dy);
            if (dist < MOUSE_RADIUS) {
                const push = (1 - dist / MOUSE_RADIUS) * 0.35;
                p.x -= dx * push;
                p.y -= dy * push;
            }

            if (p.x < 0 || p.x > rect.width) p.vx *= -1;
            if (p.y < 0 || p.y > rect.height) p.vy *= -1;

            const pulse = 0.75 + Math.sin(timeSeconds * 1.5 + p.phase) * 0.25;

            if (p.accent) {
                ctx.fillStyle = '#0bf700';
                ctx.shadowColor = '#0bf700';
                ctx.shadowBlur = 8 * pulse;
            } else {
                ctx.fillStyle = primary;
                ctx.shadowBlur = 0;
            }

            ctx.beginPath();
            ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
            ctx.fill();
            ctx.shadowBlur = 0;
        }

        controller.raf = requestAnimationFrame(draw);
    };

    const onMouseMove = (event) => {
        const rect = canvas.getBoundingClientRect();
        controller.mouse.x = event.clientX - rect.left;
        controller.mouse.y = event.clientY - rect.top;
    };

    const onMouseLeave = () => {
        controller.mouse.x = -9999;
        controller.mouse.y = -9999;
    };

    const onVisibility = () => {
        if (document.hidden) {
            controller.running = false;
            cancelAnimationFrame(controller.raf);
        } else if (!REDUCED_MOTION) {
            controller.running = true;
            controller.raf = requestAnimationFrame(draw);
        }
    };

    const repaint = () => {
        controller.colors = getColors();
        if (REDUCED_MOTION) {
            draw(performance.now());
        }
    };

    if (!REDUCED_MOTION) {
        canvas.addEventListener('mousemove', onMouseMove, { passive: true });
        canvas.addEventListener('mouseleave', onMouseLeave, { passive: true });
        document.addEventListener('visibilitychange', onVisibility);
        document.addEventListener('theme-updated', repaint);
    }

    resize();

    if (REDUCED_MOTION) {
        // Static snapshot for users who prefer reduced motion.
        draw(performance.now());
    } else {
        controller.raf = requestAnimationFrame(draw);
    }

    if (typeof ResizeObserver === 'function') {
        new ResizeObserver(() => resize()).observe(canvas.parentElement);
    }

    return controller;
}

/**
 * Register the Alpine `particleField` data component.
 */
export function registerParticleField(Alpine) {
    Alpine.data('particleField', () => ({
        controller: null,
        init() {
            this.$nextTick(() => {
                this.controller = createParticleField(this.$refs.particleCanvas);
            });
        },
        destroy() {
            this.controller?.destroy();
        },
    }));
}