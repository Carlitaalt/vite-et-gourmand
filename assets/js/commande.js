/**
 * Page « Commander » : le récapitulatif est recalculé à chaque saisie par un appel fetch
 * vers api/prix-commande.php. Le calcul (minimum, remise, livraison) est fait par le serveur,
 * avec les mêmes règles que lors de l'enregistrement de la commande.
 */
document.addEventListener('DOMContentLoaded', () => {
    const formulaire = document.getElementById('commande-form');
    if (!formulaire) return;

    const racine = formulaire.dataset.racine;
    const champMenu = document.getElementById('menu_id');
    const champNombre = document.getElementById('nb_personnes');
    const champVille = document.getElementById('ville_prestation');
    const champDistance = document.getElementById('distance_km');
    const blocDistance = document.getElementById('bloc-distance');
    const aideNombre = document.getElementById('nb-aide');

    const recap = {
        menu: document.getElementById('recap-menu'),
        nombre: document.getElementById('recap-nb'),
        prix: document.getElementById('recap-prix-menu'),
        ligneRemise: document.getElementById('recap-remise-ligne'),
        remise: document.getElementById('recap-remise'),
        livraison: document.getElementById('recap-livraison'),
        total: document.getElementById('recap-total'),
        erreur: document.getElementById('recap-erreur'),
        conditions: document.getElementById('recap-conditions'),
        texteConditions: document.getElementById('recap-conditions-text'),
    };

    let minuterie = null;
    let requeteEnCours = null;

    function menuSelectionne() {
        const option = champMenu.selectedOptions[0];
        return option && option.value !== '' ? option : null;
    }

    // Le minimum de personnes et les conditions dépendent du menu choisi
    function adapterAuMenu() {
        const option = menuSelectionne();
        if (!option) {
            aideNombre.textContent = '';
            recap.conditions.hidden = true;
            return;
        }

        const minimum = Number(option.dataset.min);
        champNombre.min = minimum;
        if (Number(champNombre.value) < minimum) champNombre.value = minimum;
        aideNombre.textContent = `Minimum : ${minimum} personnes. Remise de 10 % à partir de ${minimum + 5} personnes.`;

        recap.texteConditions.textContent = option.dataset.conditions;
        recap.conditions.hidden = option.dataset.conditions === '';
    }

    // Pas de frais kilométriques à Bordeaux : le champ distance est masqué
    function adapterALaVille() {
        blocDistance.hidden = champVille.value.trim().toLowerCase() === 'bordeaux';
    }

    async function recalculerPrix() {
        adapterAuMenu();
        adapterALaVille();

        const option = menuSelectionne();
        if (!option) return;

        const parametres = new URLSearchParams({
            menu_id: champMenu.value,
            nb_personnes: champNombre.value,
            ville: champVille.value,
            distance_km: blocDistance.hidden ? 0 : (champDistance.value || 0),
        });

        requeteEnCours?.abort();
        requeteEnCours = new AbortController();

        try {
            const prix = await appelApi(`${racine}api/prix-commande.php?${parametres}`, { signal: requeteEnCours.signal });

            recap.erreur.hidden = true;
            recap.menu.textContent = option.dataset.titre;
            recap.nombre.textContent = `${prix.nombrePersonnes} pers.`;
            recap.prix.textContent = formaterPrix(prix.sousTotal);
            recap.ligneRemise.hidden = prix.remise === 0;
            recap.remise.textContent = `- ${formaterPrix(prix.remise)}`;
            recap.livraison.textContent = prix.fraisLivraison === 0 ? 'Offerte' : formaterPrix(prix.fraisLivraison);
            recap.total.textContent = formaterPrix(prix.total);
        } catch (erreur) {
            if (erreur.name === 'AbortError') return;
            recap.erreur.textContent = erreur.message;
            recap.erreur.hidden = false;
            recap.total.textContent = '-';
        }
    }

    function planifierCalcul() {
        clearTimeout(minuterie);
        minuterie = setTimeout(recalculerPrix, 250);
    }

    [champMenu, champNombre, champVille, champDistance].forEach(champ => {
        champ.addEventListener('input', planifierCalcul);
        champ.addEventListener('change', planifierCalcul);
    });

    document.getElementById('nb-plus').addEventListener('click', () => {
        champNombre.value = Number(champNombre.value) + 1;
        planifierCalcul();
    });
    document.getElementById('nb-moins').addEventListener('click', () => {
        if (Number(champNombre.value) > Number(champNombre.min)) {
            champNombre.value = Number(champNombre.value) - 1;
            planifierCalcul();
        }
    });

    recalculerPrix();
});
