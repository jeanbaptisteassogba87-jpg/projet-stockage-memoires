
// Rôle : validation côté client des formulaires de dépôt et modification de mémoire
// Chargé uniquement sur les pages qui en ont besoin  via $extraJs dans footer.php       


// ── Zone de dépôt PDF — drag & drop ─────────────────────────

/**
 * Quand l'utilisateur survole la zone avec un fichier
 * Met en évidence visuelle la zone
 */
function dragOver(event) {
  event.preventDefault();
  const zone = document.getElementById('zone-upload');
  if (!zone) return;
  zone.style.borderColor  = 'var(--primary)';
  zone.style.background   = '#EEF2F8';
}

/**
 * Quand l'utilisateur quitte la zone sans déposer
 * Remet l'apparence normale
 */
function dragLeave(event) {
  const zone = document.getElementById('zone-upload');
  if (!zone) return;
  zone.style.borderColor = 'var(--border)';
  zone.style.background  = '';
}

/**
 * Quand l'utilisateur lâche le fichier sur la zone
 * Transfère le fichier dans l'input file caché
 */
function dropFichier(event) {
  event.preventDefault();
  dragLeave(event);

  const input = document.getElementById('fichier_pdf');
  if (!input) return;

  const fichiers = event.dataTransfer.files;
  if (!fichiers.length) return;

  // Simuler la sélection dans l'input
  const dt = new DataTransfer();
  dt.items.add(fichiers[0]);
  input.files = dt.files;

  // Déclencher l'affichage du nom de fichier
  afficherFichier(input);
}

/**
 * Affiche le nom et la taille du fichier sélectionné
 * Change l'icône et le texte de la zone upload
 * Vérifie le type et la taille avant validation
 *
 * @param {HTMLInputElement} input  l'input file
 */
function afficherFichier(input) {
  const zone    = document.getElementById('zone-upload');
  const texte   = document.getElementById('upload-texte');
  const errEl   = document.getElementById('pdf-error');

  if (!input.files || !input.files.length) return;

  const fichier = input.files[0];
  const tailleMo = (fichier.size / 1024 / 1024).toFixed(2);

  // Réinitialiser l'erreur
  if (errEl) {
    errEl.textContent = '';
    errEl.style.display = 'none';
  }

  // Vérification du type MIME côté client (double-check du serveur)
  if (fichier.type !== 'application/pdf' && !fichier.name.endsWith('.pdf')) {
    afficherErreurPdf('Le fichier doit être au format PDF.');
    input.value = '';
    return;
  }

  // Vérification de la taille (10 Mo max = 10 * 1024 * 1024 octets)
  if (fichier.size > 10 * 1024 * 1024) {
    afficherErreurPdf('Fichier trop lourd (' + tailleMo + ' Mo). Maximum : 10 Mo.');
    input.value = '';
    return;
  }

  // Fichier valide — afficher les infos
  if (zone) {
    zone.style.borderColor  = 'var(--success)';
    zone.style.background   = '#F0FFF4';
  }
  if (texte) {
    texte.innerHTML =
      '<i class="bi bi-file-earmark-pdf" style="color:#DC3545;font-size:1.5rem"></i>' +
      '<div style="margin-top:6px;font-weight:500;color:var(--text-main)">' +
        escapeHtml(fichier.name) +
      '</div>' +
      '<small style="color:var(--text-muted)">' + tailleMo + ' Mo</small>';
  }
}

/**
 * Affiche un message d'erreur sous la zone upload
 * et remet l'apparence d'erreur sur la zone
 */
function afficherErreurPdf(message) {
  const zone  = document.getElementById('zone-upload');
  const errEl = document.getElementById('pdf-error');
  const texte = document.getElementById('upload-texte');

  if (zone) {
    zone.style.borderColor = 'var(--danger)';
    zone.style.background  = '#FFF5F5';
  }
  if (errEl) {
    errEl.textContent   = message;
    errEl.style.display = 'block';
  }
  if (texte) {
    texte.innerHTML =
      '<i class="bi bi-exclamation-circle" style="color:var(--danger);font-size:1.5rem"></i>' +
      '<div style="margin-top:6px;color:var(--danger)">' + escapeHtml(message) + '</div>';
  }
}

// ── Validation du formulaire avant soumission ────────────────

document.addEventListener('DOMContentLoaded', function () {

  const form = document.getElementById('form-depot') ||
               document.getElementById('form-modification');

  if (!form) return;

  form.addEventListener('submit', function (e) {

    let valide = true;

    // Effacer les erreurs précédentes
    form.querySelectorAll('.is-invalid').forEach(function (el) {
      el.classList.remove('is-invalid');
    });

    // Vérifier chaque champ obligatoire
    form.querySelectorAll('[required]').forEach(function (champ) {

      // Les inputs file sont gérés séparément
      if (champ.type === 'file') return;

      const valeur = champ.value.trim();

      if (!valeur) {
        champ.classList.add('is-invalid');
        valide = false;
      }
    });

    // Vérifier l'input file séparément
    const inputPdf = document.getElementById('fichier_pdf');
    if (inputPdf && inputPdf.hasAttribute('required') && !inputPdf.files.length) {
      afficherErreurPdf('Merci de joindre votre mémoire en PDF.');
      valide = false;
    }

    // Vérifier l'année
    const anneeInput = document.getElementById('annee_soutenance');
    if (anneeInput) {
      const annee = parseInt(anneeInput.value, 10);
      const anneeMax = new Date().getFullYear() + 1;
      if (isNaN(annee) || annee < 2000 || annee > anneeMax) {
        anneeInput.classList.add('is-invalid');
        valide = false;
      }
    }

    if (!valide) {
      e.preventDefault();
      // Scroller jusqu'à la première erreur
      const premiere = form.querySelector('.is-invalid, [style*="border-color: var(--danger)"]');
      if (premiere) {
        premiere.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    } else {
      // Désactiver le bouton pour éviter le double-clic
      const btn = document.getElementById('btn-soumettre');
      if (btn) {
        btn.disabled    = true;
        btn.innerHTML   = '<span class="spinner-border spinner-border-sm me-2"></span>Envoi en cours…';
      }
    }
  });

  // Retirer l'erreur d'un champ dès que l'utilisateur commence à taper
  form.querySelectorAll('input, select, textarea').forEach(function (champ) {
    champ.addEventListener('input', function () {
      this.classList.remove('is-invalid');
    });
  });

});

// ── Utilitaire : échapper le HTML pour éviter XSS ────────────

/**
 * Échappe les caractères HTML dangereux
 * Utilisé avant d'injecter du texte dans innerHTML
 *
 * @param {string} str
 * @returns {string}
 */
function escapeHtml(str) {
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}