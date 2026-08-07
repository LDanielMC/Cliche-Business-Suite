{{-- Lightbox global reutilizable. Incluir una vez por página con <x-image-lightbox />
     y disparar con onclick="abrirLightbox('URL')" (o @click.stop en Alpine). --}}
<div id="lightbox-overlay" class="lightbox-overlay" onclick="if(event.target===this) cerrarLightbox()">
    <button type="button" class="lightbox-close" onclick="cerrarLightbox()" aria-label="Cerrar vista previa">
        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>

    <img id="lightbox-img" src="" alt="Vista previa de la fotografía" draggable="false">

    <div class="lightbox-zoom-bar" onclick="event.stopPropagation()">
        <button type="button" onclick="alejarLightbox()" aria-label="Alejar">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 104.5 4.5a7.5 7.5 0 0012.15 12.15z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 11h5.5"/>
            </svg>
        </button>
        <span id="lightbox-zoom-pct">100%</span>
        <button type="button" onclick="acercarLightbox()" aria-label="Acercar">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 104.5 4.5a7.5 7.5 0 0012.15 12.15z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 8.25v5.5m-2.75-2.75h5.5"/>
            </svg>
        </button>
    </div>
</div>

<style>
    .lightbox-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,.85);
        z-index: 1090;
        align-items: center;
        justify-content: center;
        padding: 3rem 2rem;
        cursor: zoom-out;
        overflow: hidden;
    }
    .lightbox-overlay.open { display: flex; }
    .lightbox-overlay img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        border-radius: 8px;
        box-shadow: 0 20px 60px rgba(0,0,0,.5);
        cursor: zoom-in;
        animation: lightbox-zoom-in .18s ease;
        touch-action: none;
        user-select: none;
    }
    .lightbox-overlay img.transicion { transition: transform .18s ease; }
    /* Zoomeada y quieta: manita para arrastrar. Zoomeada y arrastrando: manita cerrada. */
    .lightbox-overlay img.zoomed { cursor: grab; }
    .lightbox-overlay img.zoomed.dragging { cursor: grabbing; }
    @keyframes lightbox-zoom-in {
        from { transform: scale(.94); opacity: 0; }
        to   { transform: scale(1); opacity: 1; }
    }
    .lightbox-close {
        position: fixed;
        top: 1.25rem;
        right: 1.5rem;
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 50%;
        background: rgba(255,255,255,.12);
        border: none;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background .15s;
    }
    .lightbox-close:hover { background: rgba(255,255,255,.25); }

    .lightbox-zoom-bar {
        position: fixed;
        bottom: 1.5rem;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        align-items: center;
        gap: .75rem;
        background: rgba(255,255,255,.12);
        border-radius: 999px;
        padding: .4rem .6rem;
        cursor: default;
    }
    .lightbox-zoom-bar button {
        width: 2.1rem;
        height: 2.1rem;
        border-radius: 50%;
        background: rgba(255,255,255,.14);
        border: none;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background .15s;
    }
    .lightbox-zoom-bar button:hover { background: rgba(255,255,255,.3); }
    .lightbox-zoom-bar span {
        color: #fff;
        font-size: .8rem;
        font-weight: 600;
        min-width: 3.2em;
        text-align: center;
    }

    /* Miniaturas que abren el lightbox: manita normal de "esto es clicable" */
    [data-lightbox-trigger] { cursor: pointer; }
</style>

<script>
    (function () {
        const ZOOM_MIN  = 1;
        const ZOOM_MAX  = 3.5;
        const ZOOM_STEP = 0.5;

        let zoom = 1;
        let panX = 0, panY = 0;
        let arrastrando = false;
        let arrastreMovido = false;
        let inicioX = 0, inicioY = 0;
        let panInicioX = 0, panInicioY = 0;

        function overlay() { return document.getElementById('lightbox-overlay'); }
        function img()     { return document.getElementById('lightbox-img'); }

        function limitarPan() {
            const el = img();
            const maxPanX = Math.max(0, (el.offsetWidth  * zoom - window.innerWidth)  / 2);
            const maxPanY = Math.max(0, (el.offsetHeight * zoom - window.innerHeight) / 2);
            panX = Math.min(maxPanX, Math.max(-maxPanX, panX));
            panY = Math.min(maxPanY, Math.max(-maxPanY, panY));
        }

        function aplicar() {
            limitarPan();
            const el = img();
            el.style.transform = `translate(${panX}px, ${panY}px) scale(${zoom})`;
            el.classList.toggle('zoomed', zoom > ZOOM_MIN);
            document.getElementById('lightbox-zoom-pct').textContent = Math.round(zoom * 100) + '%';
        }

        function fijarZoom(nuevo) {
            zoom = Math.min(ZOOM_MAX, Math.max(ZOOM_MIN, nuevo));
            if (zoom === ZOOM_MIN) { panX = 0; panY = 0; }
            aplicar();
        }

        window.abrirLightbox = function (src) {
            const ov = overlay();
            if (!ov) return;
            const el = img();
            el.classList.remove('transicion');
            panX = 0; panY = 0;
            el.src = src;
            fijarZoom(1);
            requestAnimationFrame(() => el.classList.add('transicion'));
            ov.classList.add('open');
            document.body.style.overflow = 'hidden';
        };

        window.cerrarLightbox = function () {
            const ov = overlay();
            if (!ov) return;
            ov.classList.remove('open');
            document.body.style.overflow = '';
        };

        window.acercarLightbox = function () { fijarZoom(zoom + ZOOM_STEP); };
        window.alejarLightbox  = function () { fijarZoom(zoom - ZOOM_STEP); };

        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') cerrarLightbox();
            if (e.key === '+' || e.key === '=') acercarLightbox();
            if (e.key === '-') alejarLightbox();
        });

        document.addEventListener('DOMContentLoaded', () => {
            const el = img();
            if (!el) return;

            // Clic simple (sin arrastre): alterna acercar / restablecer.
            el.addEventListener('click', e => {
                e.stopPropagation();
                if (arrastreMovido) { arrastreMovido = false; return; }
                fijarZoom(zoom > ZOOM_MIN ? ZOOM_MIN : ZOOM_MIN + ZOOM_STEP);
            });

            // Rueda del mouse: acercar/alejar de forma continua.
            el.addEventListener('wheel', e => {
                e.preventDefault();
                fijarZoom(zoom + (e.deltaY < 0 ? 0.2 : -0.2));
            }, { passive: false });

            // Arrastrar para desplazarse por la foto, solo cuando ya está acercada.
            el.addEventListener('mousedown', e => {
                if (zoom <= ZOOM_MIN) return;
                e.preventDefault();
                arrastrando = true;
                arrastreMovido = false;
                inicioX = e.clientX;
                inicioY = e.clientY;
                panInicioX = panX;
                panInicioY = panY;
                el.classList.remove('transicion');
                el.classList.add('dragging');
            });

            window.addEventListener('mousemove', e => {
                if (!arrastrando) return;
                const dx = e.clientX - inicioX;
                const dy = e.clientY - inicioY;
                if (Math.abs(dx) > 3 || Math.abs(dy) > 3) arrastreMovido = true;
                panX = panInicioX + dx;
                panY = panInicioY + dy;
                aplicar();
            });

            window.addEventListener('mouseup', () => {
                if (!arrastrando) return;
                arrastrando = false;
                el.classList.add('transicion');
                el.classList.remove('dragging');
            });
        });
    })();
</script>
