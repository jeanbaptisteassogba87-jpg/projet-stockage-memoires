document.addEventListener('DOMContentLoaded', function () {

    // ── Éléments du DOM 
    const champMotCle   = document.getElementById('mot_cle');
    const listeSugg     = document.getElementById('suggestions');
    const selectFiliere = document.getElementById('filiere');
    const selectNiveau  = document.getElementById('niveau');
    const selectAnnee   = document.getElementById('annee');
    const formRecherche = document.getElementById('formRecherche');
    const listeMemoires = document.getElementById('listeMemoires');

    const DELAI_MS = 300;  
    let   timer    = null;

    // ── 1. SUGGESTIONS DYNAMIQUES 

    if (champMotCle && listeSugg) {

        champMotCle.addEventListener('input', function () {
            clearTimeout(timer);
            const val = this.value.trim();

            if (val.length < 2) { cacherSugg(); return; }

            timer = setTimeout(() => fetchSugg(val), DELAI_MS);
        });

        function fetchSugg(terme) {
            const url = `/views/commentateur/dashboard.php?action=suggestions&q=${encodeURIComponent(terme)}`;

            fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.ok ? r.json() : Promise.reject())
            .then(data => afficherSugg(data))
            .catch(() => cacherSugg());
        }

        function afficherSugg(titres) {
            listeSugg.innerHTML = '';

            if (!titres || titres.length === 0) { cacherSugg(); return; }

            titres.slice(0, 8).forEach(titre => {
                const li    = document.createElement('li');
                li.className = 'list-group-item list-group-item-action';
                li.style.cursor = 'pointer';

                // Met en gras la partie saisie
                const re = new RegExp(`(${escReg(champMotCle.value.trim())})`, 'gi');
                li.innerHTML = htmlEsc(titre).replace(re, '<strong>$1</strong>');

                li.addEventListener('mousedown', e => {
                    e.preventDefault();
                    champMotCle.value = titre;
                    cacherSugg();
                    formRecherche.submit();
                });

                listeSugg.appendChild(li);
            });

            listeSugg.classList.remove('d-none');
        }

        function cacherSugg() {
            listeSugg.innerHTML = '';
            listeSugg.classList.add('d-none');
        }

        // Ferme au clic extérieur
        document.addEventListener('click', e => {
            if (e.target !== champMotCle) cacherSugg();
        });

        // Ferme à Échap
        champMotCle.addEventListener('keydown', e => {
            if (e.key === 'Escape') cacherSugg();
        });
    }

    // ── 2. SOUMISSION AUTO SUR CHANGEMENT DE FILTRE 

    [selectFiliere, selectNiveau, selectAnnee].forEach(sel => {
        if (sel) sel.addEventListener('change', () => formRecherche.submit());
    });

    // ── 3. MISE EN ÉVIDENCE DES MOTS-CLÉS 

    if (listeMemoires && champMotCle && champMotCle.value.trim().length > 0) {
        const terme = champMotCle.value.trim();
        const re    = new RegExp(`(${escReg(terme)})`, 'gi');

        listeMemoires
            .querySelectorAll('.card-title, .card-text')
            .forEach(el => {
                el.innerHTML = el.innerHTML.replace(
                    re,
                    '<mark class="bg-warning px-0">$1</mark>'
                );
            });
    }

    // ── 4. ANIMATION D'ENTRÉE DES CARTES 

    document.querySelectorAll('.carte-memoire').forEach((c, i) => {
        c.style.opacity   = '0';
        c.style.transform = 'translateY(16px)';
        c.style.transition = 'opacity .3s ease, transform .3s ease';
        setTimeout(() => {
            c.style.opacity   = '1';
            c.style.transform = 'translateY(0)';
        }, i * 70);
    });

    // ── UTILITAIRES 

    /** Échappe les caractères spéciaux RegExp */
    function escReg(s) {
        return s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    /** Échappe le HTML pour l'affichage dans innerHTML */
    function htmlEsc(s) {
        return s
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

});