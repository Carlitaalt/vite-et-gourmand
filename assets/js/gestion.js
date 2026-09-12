/**
 * Espaces employé et administrateur : filtres, horaires et modération des avis en fetch.
 */
(() => {
    const racine = document.body.dataset.racine;

    // --- Filtre des commandes (statut et client) ---

    const filtreStatut = document.getElementById('filtre-statut');
    const filtreClient = document.getElementById('filtre-client');

    function filtrerCommandes() {
        const statut = filtreStatut.value;
        const client = filtreClient.value.trim().toLowerCase();
        let visibles = 0;

        document.querySelectorAll('.employe-commande-item').forEach(carte => {
            const affichee = (!statut || carte.dataset.statut === statut) && (!client || carte.dataset.client.includes(client));
            carte.hidden = !affichee;
            if (affichee) visibles++;
        });

        document.getElementById('nb-commandes-visibles').textContent = visibles;
        document.getElementById('aucune-commande').hidden = visibles > 0;
    }

    filtreStatut?.addEventListener('change', filtrerCommandes);
    filtreClient?.addEventListener('input', filtrerCommandes);

    // --- Filtre des plats (nom et menu) ---

    const filtrePlatNom = document.getElementById('filtre-plat-nom');
    const filtrePlatMenu = document.getElementById('filtre-plat-menu');

    function filtrerPlats() {
        const nom = filtrePlatNom.value.trim().toLowerCase();
        const menu = filtrePlatMenu.value;
        let visibles = 0;

        document.querySelectorAll('.js-plat').forEach(carte => {
            const affiche = carte.dataset.nom.includes(nom) && (!menu || carte.dataset.menus.includes(`,${menu},`));
            carte.hidden = !affiche;
            if (affiche) visibles++;
        });

        document.getElementById('aucun-plat').hidden = visibles > 0;
    }

    filtrePlatNom?.addEventListener('input', filtrerPlats);
    filtrePlatMenu?.addEventListener('change', filtrerPlats);

    // --- Horaires : les heures d'un jour fermé sont désactivées ---

    document.querySelectorAll('.js-horaire-ouvert').forEach(caseOuvert => {
        const heures = document.getElementById(caseOuvert.dataset.cible);
        const libelle = caseOuvert.closest('.horaire-toggle').querySelector('.horaire-toggle__label');

        const appliquer = () => {
            heures.querySelectorAll('input').forEach(champ => { champ.disabled = !caseOuvert.checked; });
            heures.style.opacity = caseOuvert.checked ? '1' : '0.4';
            libelle.textContent = caseOuvert.checked ? 'Ouvert' : 'Fermé';
        };

        caseOuvert.addEventListener('change', appliquer);
        appliquer();
    });

    // --- Modération des avis sans rechargement (fetch vers api/avis.php) ---

    function mettreAJourCompteursAvis(variation) {
        document.querySelectorAll('[data-compteur="avis-attente"]').forEach(compteur => {
            const valeur = Math.max(0, Number(compteur.textContent) + variation);
            compteur.textContent = valeur;
            if (compteur.classList.contains('compte-tab__badge')) compteur.hidden = valeur === 0;
        });

        const restants = document.querySelectorAll('#liste-avis-attente [data-avis-id]').length;
        document.getElementById('avis-attente-vide').hidden = restants > 0;
        document.getElementById('avis-traites-vide').hidden = true;
    }

    document.addEventListener('submit', async evenement => {
        const formulaire = evenement.target.closest('.js-moderation');
        if (!formulaire) return;
        evenement.preventDefault();

        const carte = formulaire.closest('[data-avis-id]');
        const actions = carte.querySelector('.js-actions-avis');
        actions.querySelectorAll('button').forEach(bouton => { bouton.disabled = true; });

        try {
            const resultat = await appelApi(`${racine}api/avis.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ avis_id: Number(carte.dataset.avisId), decision: formulaire.dataset.decision }),
            });

            const badge = carte.querySelector('.js-statut-avis');
            badge.textContent = resultat.libelle;
            badge.className = `commande-statut js-statut-avis ${resultat.classe}`;

            actions.remove();
            document.getElementById('liste-avis-traites').prepend(carte);
            mettreAJourCompteursAvis(-1);
            annoncer(`Avis ${resultat.libelle.toLowerCase()}.`);
        } catch (erreur) {
            actions.querySelectorAll('button').forEach(bouton => { bouton.disabled = false; });
            annoncer(erreur.message);
            alert(erreur.message);
        }
    });
})();
