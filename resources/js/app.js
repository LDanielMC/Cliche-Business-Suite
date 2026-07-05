import Alpine from 'alpinejs';
import { Chart, registerables } from 'chart.js';

window.Alpine = Alpine;
Alpine.start();

Chart.register(...registerables);
window.Chart = Chart;

window.descargarGraficaComoPng = function (chart, nombreArchivo) {
    const enlace = document.createElement('a');
    enlace.href = chart.toBase64Image();
    enlace.download = nombreArchivo;
    enlace.click();
};

window.exportarGraficaComoPdf = function (chart, formId) {
    const form = document.getElementById(formId);
    const input = form.querySelector('input[name="chart_image"]');
    input.value = chart.toBase64Image();
    form.submit();
};
