// Rôle : comportements globaux chargés sur toutes les pages
//        - fermeture automatique des alertes
//        - confirmation avant soumission des formulaires destructifs
//        - retour en haut de page
//        - tooltips Bootstrap


(function () {
  'use strict';

  // ── Initialisation au chargement du DOM ─────────────────────
  document.addEventListener('DOMContentLoaded', function () {
    initAlertes();
    //initConfirmations()
    initBoutonHaut();
    initTooltips();
    initNavActive();
  });


  // ── Alertes : fermeture automatique après 5 secondes ────────
  // Les alertes success disparaissent seules, les erreurs restent

  function initAlertes() {
    var alertes = document.querySelectorAll('.alert-success');

    alertes.forEach(function (alerte) {
      // Ajouter un bouton de fermeture s'il n'y en a pas déjà
      if (!alerte.querySelector('[data-bs-dismiss]')) {
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn-close float-end';
        btn.style.cssText = 'opacity:0.6;cursor:pointer';
        btn.addEventListener('click', function () {
          fermerAlerte(alerte);
        });
        alerte.insertBefore(btn, alerte.firstChild);
      }

      // Fermeture automatique après 5s
      setTimeout(function () {
        fermerAlerte(alerte);
      }, 5000);
    });
  }

  function fermerAlerte(alerte) {
    alerte.style.transition = 'opacity 0.4s ease, max-height 0.4s ease';
    alerte.style.opacity    = '0';
    alerte.style.maxHeight  = alerte.offsetHeight + 'px';

    setTimeout(function () {
      alerte.style.maxHeight  = '0';
      alerte.style.overflow   = 'hidden';
      alerte.style.marginBottom = '0';
      alerte.style.padding    = '0';
    }, 50);

    setTimeout(function () {
      if (alerte.parentNode) {
        alerte.parentNode.removeChild(alerte);
      }
    }, 450);
  }


  // ── Bouton "retour en haut" ──────────────────────────────────
  // Apparaît après 300px de scroll, doux et discret

  function initBoutonHaut() {
    var btn = document.createElement('button');
    btn.id  = 'btn-haut';
    btn.innerHTML  = '<i class="bi bi-arrow-up"></i>';
    btn.title      = 'Retour en haut';
    btn.setAttribute('aria-label', 'Retour en haut de page');
    btn.style.cssText = [
      'position:fixed',
      'bottom:28px',
      'right:28px',
      'width:40px',
      'height:40px',
      'border-radius:50%',
      'border:none',
      'background:var(--primary)',
      'color:#fff',
      'font-size:1rem',
      'cursor:pointer',
      'opacity:0',
      'transform:translateY(10px)',
      'transition:opacity 0.25s ease, transform 0.25s ease',
      'z-index:999',
      'box-shadow:0 2px 8px rgba(0,0,0,0.2)',
      'display:flex',
      'align-items:center',
      'justify-content:center',
    ].join(';');

    document.body.appendChild(btn);

    window.addEventListener('scroll', function () {
      if (window.scrollY > 300) {
        btn.style.opacity   = '1';
        btn.style.transform = 'translateY(0)';
      } else {
        btn.style.opacity   = '0';
        btn.style.transform = 'translateY(10px)';
      }
    }, { passive: true });

    btn.addEventListener('click', function () {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }


  // ── Tooltips Bootstrap ───────────────────────────────────────
  // Active tous les éléments avec data-bs-toggle="tooltip"

  function initTooltips() {
    if (typeof bootstrap === 'undefined' || !bootstrap.Tooltip) return;

    var elements = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    elements.forEach(function (el) {
      new bootstrap.Tooltip(el, { trigger: 'hover' });
    });
  }


  // ── Marquer le lien actif dans la sidebar ────────────────────
  // Complète le PHP : gère les cas où le PHP ne peut pas le détecter

  function initNavActive() {
    var chemin  = window.location.pathname;
    var liens   = document.querySelectorAll('.sidebar .nav-link');

    liens.forEach(function (lien) {
      var href = lien.getAttribute('href');
      if (!href) return;

      // Correspondance exacte ou début de chemin (pour les pages avec paramètres)
      if (href === chemin || (href !== '/' && chemin.indexOf(href) === 0)) {
        lien.classList.add('active');
      }
    });
  }

})();