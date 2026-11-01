(function () {
  document.querySelectorAll('[data-protected-pdf]').forEach((viewer) => {
    const id = Number(viewer.dataset.memoireId);
    const image = viewer.querySelector('[data-pdf-page]');
    const status = viewer.querySelector('[data-pdf-status]');
    const previous = viewer.querySelector('[data-pdf-previous]');
    const next = viewer.querySelector('[data-pdf-next]');
    let page = 1;
    let pageCount = 0;

    const showPage = () => {
      image.src = `/scripts/serve_pdf_page.php?id=${id}&page=${page}`;
      status.textContent = `Page ${page} / ${pageCount}`;
      previous.disabled = page <= 1;
      next.disabled = page >= pageCount;
    };

    previous.addEventListener('click', () => {
      if (page > 1) { page -= 1; showPage(); }
    });
    next.addEventListener('click', () => {
      if (page < pageCount) { page += 1; showPage(); }
    });

    fetch(`/scripts/serve_pdf_page.php?id=${id}&format=info`, { credentials: 'same-origin' })
      .then((response) => {
        if (!response.ok) throw new Error('Accès au document refusé.');
        return response.json();
      })
      .then((data) => {
        pageCount = Number(data.pages);
        if (!Number.isInteger(pageCount) || pageCount < 1) throw new Error('Document vide.');
        showPage();
      })
      .catch((error) => {
        status.textContent = error.message;
        previous.hidden = true;
        next.hidden = true;
      });
  });
})();
