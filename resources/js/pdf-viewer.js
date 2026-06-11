// Preview PDF di halaman detail dokumen — pdfjs-dist dibundel lokal
// (tanpa CDN) supaya tetap berfungsi di jaringan yang membatasi akses luar.
import * as pdfjsLib from 'pdfjs-dist';
import workerUrl from 'pdfjs-dist/build/pdf.worker.min.mjs?url';

pdfjsLib.GlobalWorkerOptions.workerSrc = workerUrl;

const container = document.getElementById('pdf-container');

if (container) {
    const status = document.getElementById('pdf-status');

    (async () => {
        try {
            const pdf = await pdfjsLib.getDocument(container.dataset.url).promise;
            status?.remove();

            for (let pageNumber = 1; pageNumber <= pdf.numPages; pageNumber++) {
                const page = await pdf.getPage(pageNumber);
                const containerWidth = container.clientWidth - 24; // padding p-3
                const unscaled = page.getViewport({ scale: 1 });
                const scale = containerWidth / unscaled.width;
                const outputScale = window.devicePixelRatio || 1;
                const viewport = page.getViewport({ scale });

                const canvas = document.createElement('canvas');
                canvas.width = Math.floor(viewport.width * outputScale);
                canvas.height = Math.floor(viewport.height * outputScale);
                canvas.style.width = Math.floor(viewport.width) + 'px';
                canvas.style.height = Math.floor(viewport.height) + 'px';
                canvas.className = 'mx-auto rounded shadow-sm bg-white';
                container.appendChild(canvas);

                await page.render({
                    canvasContext: canvas.getContext('2d'),
                    transform: outputScale !== 1 ? [outputScale, 0, 0, outputScale, 0, 0] : null,
                    viewport,
                }).promise;
            }
        } catch (error) {
            if (status) {
                status.textContent = 'Pratinjau gagal dimuat. Silakan unduh dokumen.';
            }
            console.error(error);
        }
    })();
}
