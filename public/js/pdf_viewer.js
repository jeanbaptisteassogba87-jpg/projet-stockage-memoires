

(function () {
  'use strict';

  // ── Variables d'état ──────────────────────────────────────
  let pdfDoc      = null;   // document PDF chargé
  let pageActuelle = 1;     // page affichée
  let totalPages  = 0;      // nombre total de pages
  let echelle     = 1.2;    // zoom actuel (1.2 = 120%)
  let enRendu     = false;  // verrou pour éviter le double rendu

  // ── Éléments du DOM ───────────────────────────────────────
  const canvas      = document.getElementById('pdf-canvas');
  const conteneur   = document.getElementById('pdf-container');

  // Si la visionneuse n'est pas sur cette page, on arrête
  if (!canvas || !conteneur) return;

  const ctx         = canvas.getContext('2d');
  const urlPdf      = canvas.dataset.url; // URL du PDF passée via data-url

  if (!urlPdf) return;

  // ── Configurer PDF.js ─────────────────────────────────────
  // Indiquer où trouver le worker PDF.js
  if (typeof pdfjsLib !== 'undefined') {
    pdfjsLib.GlobalWorkerOptions.workerSrc =
      'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
  } else {
    // PDF.js non chargé — afficher un message d'erreur
    conteneur.innerHTML =
      '<p style="color:#fff;padding:20px;text-align:center">' +
      'Impossible de charger la visionneuse PDF.</p>';
    return;
  }

  // ── Charger le document PDF ───────────────────────────────
  pdfjsLib.getDocument({
    url:                urlPdf,
    disableRange:       false,  // permettre le chargement progressif
    disableStream:      false,
    disableAutoFetch:   false,
  }).promise
    .then(function (doc) {
      pdfDoc     = doc;
      totalPages = doc.numPages;
      mettreAJourInfosPage();
      rendrePage(pageActuelle);
    })
    .catch(function (err) {
      console.error('Erreur chargement PDF :', err);
      conteneur.innerHTML =
        '<p style="color:#fff;padding:20px;text-align:center">' +
        'Erreur lors du chargement du document.</p>';
    });

  // ── Rendu d'une page sur le canvas ───────────────────────
  function rendrePage(numero) {
    if (enRendu) return;
    enRendu = true;

    // Afficher un indicateur de chargement
    const loading = document.getElementById('pdf-loading');
    if (loading) loading.style.display = 'flex';

    pdfDoc.getPage(numero).then(function (page) {

      const viewport = page.getViewport({ scale: echelle });

      // Adapter le canvas à la taille de la page
      canvas.width  = viewport.width;
      canvas.height = viewport.height;

      const contexteRendu = {
        canvasContext: ctx,
        viewport:      viewport,
      };

      page.render(contexteRendu).promise.then(function () {
        enRendu = false;
        if (loading) loading.style.display = 'none';
        mettreAJourBoutons();
      });
    });
  }

  // ── Mise à jour des infos de page ────────────────────────
  function mettreAJourInfosPage() {
    const el = document.getElementById('pdf-page-info');
    if (el) el.textContent = 'Page ' + pageActuelle + ' / ' + totalPages;
  }

  function mettreAJourBoutons() {
    mettreAJourInfosPage();
    const btnPrec = document.getElementById('pdf-precedent');
    const btnSuiv = document.getElementById('pdf-suivant');
    if (btnPrec) btnPrec.disabled = (pageActuelle <= 1);
    if (btnSuiv) btnSuiv.disabled = (pageActuelle >= totalPages);
  }

  // ── Navigation ───────────────────────────────────────────
  window.pdfPrecedent = function () {
    if (pageActuelle <= 1) return;
    pageActuelle--;
    rendrePage(pageActuelle);
  };

  window.pdfSuivant = function () {
    if (pageActuelle >= totalPages) return;
    pageActuelle++;
    rendrePage(pageActuelle);
  };

  // ── Zoom ─────────────────────────────────────────────────
  window.pdfZoomIn = function () {
    if (echelle >= 3.0) return; // max 300%
    echelle += 0.2;
    rendrePage(pageActuelle);
  };

  window.pdfZoomOut = function () {
    if (echelle <= 0.6) return; // min 60%
    echelle -= 0.2;
    rendrePage(pageActuelle);
  };

  // ── Blocages de sécurité ─────────────────────────────────

  // 1. Bloquer le clic droit sur le canvas et le conteneur
  [canvas, conteneur].forEach(function (el) {
    el.addEventListener('contextmenu', function (e) {
      e.preventDefault();
      return false;
    });
  });

  // 2. Bloquer la sélection de texte sur la zone PDF
  conteneur.style.userSelect       = 'none';
  conteneur.style.webkitUserSelect = 'none';
  conteneur.style.msUserSelect     = 'none';

  // 3. Bloquer les raccourcis clavier dangereux
  //    Ctrl+S = sauvegarder, Ctrl+P = imprimer,
  //    Ctrl+U = voir source, Ctrl+Shift+I = devtools (copie possible)
  document.addEventListener('keydown', function (e) {
    const ctrl = e.ctrlKey || e.metaKey; // metaKey = Cmd sur Mac

    if (ctrl && (
      e.key === 's' ||   // Ctrl+S — sauvegarder
      e.key === 'p' ||   // Ctrl+P — imprimer
      e.key === 'u'      // Ctrl+U — voir source
    )) {
      e.preventDefault();
      return false;
    }
  });

  // 4. Bloquer le glisser-déposer du canvas (évite de faire glisser l'image)
  canvas.addEventListener('dragstart', function (e) {
    e.preventDefault();
    return false;
  });

})();