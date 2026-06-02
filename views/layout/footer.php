  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Confirmation modal global -->
  <div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="confirmModalTitle">Confirmer l'action</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
        </div>
        <div class="modal-body" id="confirmModalBody">
          Êtes-vous sûr de vouloir poursuivre cette action ?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="button" class="btn btn-primary" id="confirmModalConfirm">Confirmer</button>
        </div>
      </div>
    </div>
  </div>

  <!-- JS commun -->
  <script src="/public/js/main.js"></script>
  <?php if (isset($extraJs)): ?>
    <script src="<?= $extraJs ?>"></script>
  <?php endif; ?>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const modalEl = document.getElementById('confirmModal');
      const confirmBtn = document.getElementById('confirmModalConfirm');
      const bodyEl = document.getElementById('confirmModalBody');
      const titleEl = document.getElementById('confirmModalTitle');
      let confirmTarget = null;
      const modal = new bootstrap.Modal(modalEl);

      document.body.addEventListener('click', function (event) {
        const trigger = event.target.closest('[data-confirm]');
        if (!trigger) {
          return;
        }

        event.preventDefault();
        confirmTarget = trigger;
        bodyEl.textContent = trigger.dataset.confirm;
        titleEl.textContent = trigger.dataset.confirmTitle || 'Confirmer l\'action';
        modal.show();
      });

      confirmBtn.addEventListener('click', function () {
        if (!confirmTarget) {
          modal.hide();
          return;
        }

        const target = confirmTarget;
        modal.hide();

        if (target.tagName === 'A') {
          window.location.href = target.href;
          return;
        }

        const form = target.closest('form');
        if (form) {
          form.submit();
          return;
        }

        if (target.dataset.confirmSubmit) {
          const formSelector = target.dataset.confirmSubmit;
          const customForm = document.querySelector(formSelector);
          if (customForm) {
            customForm.submit();
          }
        }
      });
    });
  </script>
</body>
</html>
