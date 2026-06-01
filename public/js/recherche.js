// Rôle : interactions de la page rechercher.php
//        - soumission du formulaire sur changement des filtres
//        - mise en évidence des mots-clés dans les résultats
//        - gestion du like sans rechargement complet (fetch)
//        - mémorisation des filtres dans l'URL


(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    initFiltresAuto();
    initSurlignage();
    initLikeAjax();
    initVideRecherche();
  });


  // ── Filtres auto-soumis au changement ────────────────────────
  // Les selects type / année / filière soumettent le formulaire immédiatement
  // L'input texte attend que l'utilisateur appuie sur Entrée ou clique Rechercher

  function initFiltresAuto() {
    var form    = document.getElementById('form-recherche');
    var selects = document.querySelectorAll('#form-recherche select[name]');

    if (!form) return;

    selects.forEach(function (select) {
      select.addEventListener('change', function () {
        form.submit();
      });
    });
  }


  // ── Surlignage des mots-clés dans les résultats ─────────────
  // Surligne les termes recherchés dans les titres et thèmes des cartes

  function initSurlignage() {
    var params    = new URLSearchParams(window.location.search);
    var motsCles  = (params.get('q') || '').trim();

    if (!motsCles) return;

    // Construire une regex sûre à partir des mots-clés
    var termes = motsCles.split(/\s+/).filter(Boolean);
    if (!termes.length) return;

    var pattern = new RegExp(
      '(' + termes.map(echapperRegex).join('|') + ')',
      'gi'
    );

    // Cibler uniquement les titres et thèmes des cartes résultats
    var cibles = document.querySelectorAll('.card h6, .card p');

    cibles.forEach(function (el) {
      // Ne pas traiter les éléments qui contiennent des enfants HTML complexes
      if (el.children.length > 0) return;

      var texte = el.textContent;
      if (!pattern.test(texte)) return;

      // Réinitialiser lastIndex après le test()
      pattern.lastIndex = 0;

      el.innerHTML = texte.replace(pattern, function (match) {
        return '<mark style="background:#FFF3CD;padding:0 2px;border-radius:2px">' +
               escapeHtml(match) +
               '</mark>';
      });
    });
  }


  // ── Like sans rechargement (fetch) ──────────────────────────
  // Intercepte les formulaires de like et les envoie en AJAX
  // Met à jour le compteur et l'icône sans recharger la page

  function initLikeAjax() {
    document.addEventListener('submit', function (e) {
      var form = e.target;

      // Ne traiter que les formulaires de like
      var actionInput = form.querySelector('input[name="action"]');
      if (!actionInput || actionInput.value !== 'toggler_like') return;

      e.preventDefault();

      var btn       = form.querySelector('button[type="submit"]');
      var memoireId = form.querySelector('input[name="memoire_id"]')?.value;

      if (!memoireId || !btn) return;

      // Désactiver pendant la requête pour éviter les doubles clics
      btn.disabled = true;

      var body = new FormData(form);

      fetch(form.action, {
        method:      'POST',
        body:        body,
        redirect:    'follow',
        credentials: 'same-origin',
      })
      .then(function () {
        // Recharger uniquement la section likes de la page courante
        actualiserLike(btn, memoireId);
      })
      .catch(function () {
        // En cas d'erreur réseau, soumettre normalement
        btn.disabled = false;
        form.submit();
      });
    });
  }

  /**
   * Met à jour visuellement le bouton like et le compteur
   * après une réponse AJAX réussie
   *
   * @param {HTMLElement} btn        le bouton like cliqué
   * @param {string}      memoireId  l'id du mémoire
   */
  function actualiserLike(btn, memoireId) {
    // Déterminer l'état actuel depuis les classes CSS
    var estLike = btn.classList.contains('btn-danger');

    // Basculer l'état
    if (estLike) {
      // Retirer le like
      btn.classList.remove('btn-danger');
      btn.classList.add('btn-outline-danger');
      btn.innerHTML = '<i class="bi bi-heart me-1"></i>J\'aime';
    } else {
      // Ajouter le like
      btn.classList.remove('btn-outline-danger');
      btn.classList.add('btn-danger');
      btn.innerHTML = '<i class="bi bi-heart-fill me-1"></i>Je n\'aime plus';
    }

    // Mettre à jour les compteurs affichés (icône + chiffre)
    actualiserCompteurLike(memoireId, estLike ? -1 : 1);

    // Animation légère sur le bouton
    btn.style.transform = 'scale(1.15)';
    setTimeout(function () {
      btn.style.transform = '';
      btn.disabled = false;
    }, 180);
  }

  /**
   * Incrémente ou décrémente le compteur de likes visible
   * Fonctionne sur la page résultats (cartes) et la page détail
   *
   * @param {string} memoireId
   * @param {number} delta  +1 ou -1
   */
  function actualiserCompteurLike(memoireId, delta) {
    // Page détail : un seul élément #nb-likes
    var spanDetail = document.getElementById('nb-likes');
    if (spanDetail) {
      var valeur = parseInt(spanDetail.textContent, 10) || 0;
      spanDetail.textContent = Math.max(0, valeur + delta);
    }

    // Page résultats : chercher par attribut data-memoire
    var spanCarte = document.querySelector('[data-likes="' + memoireId + '"]');
    if (spanCarte) {
      var v = parseInt(spanCarte.textContent, 10) || 0;
      spanCarte.textContent = Math.max(0, v + delta);
    }
  }


  // ── Bouton vider la recherche ────────────────────────────────
  // Affiche un × à droite de l'input texte quand il contient du texte

  function initVideRecherche() {
    var input = document.querySelector('#form-recherche input[name="q"]');
    if (!input) return;

    // Créer le bouton ×
    var btnVider = document.createElement('button');
    btnVider.type      = 'button';
    btnVider.innerHTML = '&times;';
    btnVider.title     = 'Vider la recherche';
    btnVider.style.cssText = [
      'position:absolute',
      'right:110px',
      'top:50%',
      'transform:translateY(-50%)',
      'background:none',
      'border:none',
      'font-size:1.2rem',
      'color:var(--text-muted)',
      'cursor:pointer',
      'padding:0 8px',
      'line-height:1',
      'display:none',
    ].join(';');

    // Positionner le parent en relatif pour l'absolu
    var groupe = input.closest('.input-group');
    if (groupe) {
      groupe.style.position = 'relative';
      groupe.appendChild(btnVider);
    }

    function majVisibilite() {
      btnVider.style.display = input.value.trim() ? 'block' : 'none';
    }

    input.addEventListener('input', majVisibilite);
    majVisibilite();

    btnVider.addEventListener('click', function () {
      input.value = '';
      majVisibilite();
      input.focus();
    });
  }


  // ── Utilitaires ──────────────────────────────────────────────

  function echapperRegex(str) {
    return str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
  }

  function escapeHtml(str) {
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

})();