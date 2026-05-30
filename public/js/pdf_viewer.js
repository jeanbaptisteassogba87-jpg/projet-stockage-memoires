document.addEventListener('DOMContentLoaded', function () {

    const viewer = document.getElementById('pdfViewer');
    if (!viewer) return;

    // ── Vérification PDF.js 
    if (typeof pdfjsLib === 'undefined') {
        afficherErreur("PDF.js non chargé. Vérifiez le CDN dans consulter.php.");
        return;
    }

    pdfjsLib.GlobalWorkerOptions.workerSrc =
        'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    // ── Éléments 
    const canvas       = document.getElementById('pdfCanvas');
    const ctx          = canvas.getContext('2d');
    const spanPage     = document.getElementById('pageActuelle');
    const spanTotal    = document.getElementById('totalPages');
    const btnPrev      = document.getElementById('btnPrevPage');
    const btnNext      = document.getElementById('btnNextPage');
    const btnZoomMoins = document.getElementById('btnZoomMoins');
    const btnZoomPlus  = document.getElementById('btnZoomPlus');
    const overlay      = document.getElementById('pdfOverlay');

    // URL sécurisée (data-url posé dans consulter.php)
    const urlPdf = viewer.dataset.url;

    // ── État 
    let pdfDoc   = null;
    let pageCour = 1;
    let echelle  = 1.2;
    let enRendu  = false;

    // ── Chargement 
    pdfjsLib.getDocument(urlPdf).promise
        .then(pdf => {
            pdfDoc = pdf;
            spanTotal.textContent = pdf.numPages;
            afficherPage(pageCour);
        })
        .catch(() => afficherErreur("Impossible de charger le document PDF."));

    // ── Rendu d'une page
    function afficherPage(num) {
        if (!pdfDoc || enRendu) return;
        enRendu = true;
        spanPage.textContent = num;

        pdfDoc.getPage(num).then(page => {
            const vp = page.getViewport({ scale: echelle });
            canvas.width  = vp.width;
            canvas.height = vp.height;

            page.render({ canvasContext: ctx, viewport: vp })
                .promise.then(() => {
                    enRendu = false;
                    majBoutons();
                });
        });
    }

    // ── Navigation 
    btnPrev && btnPrev.addEventListener('click', () => {
        if (pageCour > 1) { pageCour--; afficherPage(pageCour); }
    });

    btnNext && btnNext.addEventListener('click', () => {
        if (pdfDoc && pageCour < pdfDoc.numPages) {
            pageCour++; afficherPage(pageCour);
        }
    });

    // Flèches clavier (désactivées quand le focus est dans un champ texte)
    document.addEventListener('keydown', e => {
        const tag = e.target.tagName;
        if (tag === 'TEXTAREA' || tag === 'INPUT') return;

        if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
            if (pdfDoc && pageCour < pdfDoc.numPages) {
                pageCour++; afficherPage(pageCour);
            }
        }
        if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
            if (pageCour > 1) { pageCour--; afficherPage(pageCour); }
        }
    });

    // ── Zoom 
    btnZoomMoins && btnZoomMoins.addEventListener('click', () => {
        if (echelle > 0.6) { echelle = +(echelle - 0.2).toFixed(1); afficherPage(pageCour); }
    });

    btnZoomPlus && btnZoomPlus.addEventListener('click', () => {
        if (echelle < 3.0) { echelle = +(echelle + 0.2).toFixed(1); afficherPage(pageCour); }
    });

    // ── Protections 

    // Clic droit sur canvas
    canvas.addEventListener('contextmenu', e => {
        e.preventDefault();
        toast("🔒 Le téléchargement de ce document est protégé.");
    });

    // Clic droit sur overlay
    overlay && overlay.addEventListener('contextmenu', e => e.preventDefault());

    // Drag
    canvas.addEventListener('dragstart',  e => e.preventDefault());
    overlay && overlay.addEventListener('dragstart', e => e.preventDefault());

    // Raccourcis clavier bloqués
    document.addEventListener('keydown', e => {
        const ctrl = e.ctrlKey || e.metaKey;

        if (ctrl && e.key === 's') {
            e.preventDefault();
            toast("🔒 L'enregistrement est désactivé.");
        }
        if (ctrl && e.key === 'p') {
            e.preventDefault();
            toast("🔒 L'impression est désactivée.");
        }
        if (e.key === 'PrintScreen') {
            voilerCanvas();
            toast("🔒 La capture d'écran est déconseillée.");
        }
    });

    // Sélection de texte désactivée sur le canvas
    canvas.style.userSelect         = 'none';
    canvas.style.webkitUserSelect   = 'none';
    canvas.addEventListener('selectstart', e => e.preventDefault());

    // ── Utilitaires 

    function majBoutons() {
        if (!pdfDoc) return;
        if (btnPrev) btnPrev.disabled = (pageCour <= 1);
        if (btnNext) btnNext.disabled = (pageCour >= pdfDoc.numPages);
    }

    // Voile temporaire du canvas (anti PrintScreen)
    function voilerCanvas() {
        ctx.save();
        ctx.fillStyle = '#1a1a2e';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.fillStyle = '#ffffff';
        ctx.font      = '18px Arial';
        ctx.textAlign = 'center';
        ctx.fillText('Document protégé', canvas.width / 2, canvas.height / 2);
        ctx.restore();
        setTimeout(() => afficherPage(pageCour), 2000);
    }

    // Toast d'avertissement (non bloquant)
    function toast(msg) {
        const ancien = document.getElementById('pdfToast');
        if (ancien) ancien.remove();

        const div = document.createElement('div');
        div.id = 'pdfToast';
        div.style.cssText = `
            position:fixed; bottom:20px; left:50%; transform:translateX(-50%);
            background:rgba(20,20,20,.9); color:#fff;
            padding:10px 22px; border-radius:8px; font-size:14px;
            z-index:9999; pointer-events:none;
            box-shadow:0 4px 12px rgba(0,0,0,.3);
        `;
        div.textContent = msg;
        document.body.appendChild(div);
        setTimeout(() => div.remove(), 3000);
    }

    // Message d'erreur dans la visionneuse
    function afficherErreur(msg) {
        viewer.innerHTML = `
            <div class="d-flex align-items-center justify-content-center h-100 text-white">
                <div class="text-center p-4">
                    <i class="bi bi-exclamation-triangle-fill fs-1 text-warning"></i>
                    <p class="mt-3">${msg}</p>
                </div>
            </div>`;
    }

});