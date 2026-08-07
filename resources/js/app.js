import Alpine from 'alpinejs';
import { Chart, registerables } from 'chart.js';
import html2canvas from 'html2canvas-pro';

window.Alpine = Alpine;
Alpine.start();

Chart.register(...registerables);
window.Chart = Chart;

window.exportarGraficaComoPdf = function (chart, formId) {
    const form = document.getElementById(formId);
    const input = form.querySelector('input[name="chart_image"]');
    input.value = chart.toBase64Image();
    form.submit();
};

// Captura el contenedor completo de un reporte (tarjetas + gráfica + tabla,
// tal cual se ve en pantalla) como una sola imagen con fondo blanco sólido,
// para que el PNG exportado no dependa del tema (claro/oscuro) ni quede
// transparente, y muestre todo el detalle en lugar de solo la gráfica.
window.descargarReporteComoPng = async function (containerId, nombreArchivo) {
    const el = document.getElementById(containerId);
    if (!el) return;

    const boton = document.activeElement;
    const textoOriginal = boton ? boton.textContent : null;
    if (boton) {
        boton.disabled = true;
        boton.textContent = 'Generando…';
    }

    try {
        const canvas = await html2canvas(el, { backgroundColor: '#ffffff', scale: 2 });
        const enlace = document.createElement('a');
        enlace.href = canvas.toDataURL('image/png');
        enlace.download = nombreArchivo;
        enlace.click();
    } finally {
        if (boton) {
            boton.disabled = false;
            boton.textContent = textoOriginal;
        }
    }
};

// Búsqueda "en tiempo real" en las tablas: envía el formulario de filtro
// solo, sin esperar al botón, tras una pausa breve de tecleo.
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('input[data-live-search]').forEach((input) => {
        if (input.value) {
            input.focus();
            input.setSelectionRange(input.value.length, input.value.length);
        }

        let timer = null;
        input.addEventListener('input', () => {
            clearTimeout(timer);
            timer = setTimeout(() => {
                if (input.form.requestSubmit) {
                    input.form.requestSubmit();
                } else {
                    input.form.submit();
                }
            }, 450);
        });
    });
});
