/**
 * Page « Nos menus » : à chaque modification d'un filtre, la liste est redemandée au serveur
 * par un appel fetch (api/menus.php), puis réaffichée sans rechargement de la page.
 */
document.addEventListener('DOMContentLoaded', () => {
    const formulaire = document.getElementById('filtres-menus');
    if (!formulaire) return;

    const racine = formulaire.dataset.racine;
    const grille = document.getElementById('menus-grid');
    const etatVide = document.getElementById('menus-empty');
    const compteur = document.querySelector('.results-num');
    const curseurPrix = document.getElementById('f-price-max');
    const affichagePrix = document.querySelector('.f-price-max-val');

    let minuterie = null;
    let requeteEnCours = null;

    // Paramètres GET construits à partir des champs remplis du formulaire
    function lireFiltres() {
        const parametres = new URLSearchParams();
        for (const [nom, valeur] of new FormData(formulaire)) {
            if (valeur !== '') parametres.append(nom, valeur);
        }
        // Curseur au maximum = pas de limite de prix
        if (curseurPrix.value === curseurPrix.max) parametres.delete('prix_max');
        return parametres;
    }

    async function actualiserMenus() {
        const parametres = lireFiltres();

        // L'URL reflète les filtres : la recherche peut être partagée ou rechargée
        history.replaceState(null, '', parametres.toString() ? `?${parametres}` : location.pathname);

        // Si l'utilisateur modifie un filtre pendant un appel, on annule l'appel précédent
        requeteEnCours?.abort();
        requeteEnCours = new AbortController();
        grille.setAttribute('aria-busy', 'true');

        try {
            const reponse = await appelApi(`${racine}api/menus.php?${parametres}`, { signal: requeteEnCours.signal });

            grille.innerHTML = reponse.menus.map(creerCarteMenu).join('');
            compteur.textContent = reponse.nombre;
            grille.hidden = reponse.nombre === 0;
            etatVide.hidden = reponse.nombre !== 0;
        } catch (erreur) {
            if (erreur.name === 'AbortError') return;
            grille.hidden = false;
            grille.innerHTML = `<p class="auth-alert auth-alert--error" role="alert">${echapperHtml(erreur.message)}</p>`;
        } finally {
            grille.removeAttribute('aria-busy');
        }
    }

    // Même structure HTML que templates/partials/carte-menu.php
    function creerCarteMenu(menu) {
        const titre = echapperHtml(menu.titre);
        const image = menu.image
            ? `<img src="${racine}${echapperHtml(menu.image)}" alt="Photo du menu ${titre}">`
            : '<div class="menu-card__img-placeholder">Aucune image</div>';

        let stock;
        if (menu.stockDisponible <= 0) {
            stock = '<p class="menu-card__stock menu-card__stock--complet">Complet — plus de disponibilité</p>';
        } else if (menu.stockDisponible <= 3) {
            stock = `<p class="menu-card__stock menu-card__stock--faible">Plus que ${menu.stockDisponible} commande(s) possible(s) !</p>`;
        } else {
            stock = `<p class="menu-card__stock menu-card__stock--ok">Disponible — ${menu.stockDisponible} commandes possibles</p>`;
        }

        return `
            <article class="menu-card">
                <div class="menu-card__img">
                    ${image}
                    <span class="badge-theme badge-theme--${menu.themeId}">${echapperHtml(menu.theme)}</span>
                </div>
                <div class="menu-card__body">
                    <div class="menu-card__pills">
                        <span class="regime-pill regime--${echapperHtml(menu.regimeSlug)}">${echapperHtml(menu.regime)}</span>
                    </div>
                    <h2 class="menu-card__titre">${titre}</h2>
                    ${menu.description ? `<p class="menu-card__desc">${echapperHtml(menu.description)}</p>` : ''}
                    <div class="menu-card__meta">
                        <div class="menu-card__personnes">À partir de <strong>${menu.nombrePersonneMinimum} pers.</strong></div>
                        <div class="menu-card__prix">
                            ${formaterPrix(menu.prixMinimum)}
                            <small>${formaterPrix(menu.prixParPersonne)} / pers.</small>
                        </div>
                    </div>
                    ${stock}
                </div>
                <div class="menu-card__footer">
                    <a href="${racine}pages/menu-details.php?id=${menu.id}" class="btn btn-vg-primary w-100">
                        Voir le détail<span class="visually-hidden"> du menu ${titre}</span> →
                    </a>
                </div>
            </article>`;
    }

    // On attend 250 ms après la dernière frappe avant d'appeler le serveur
    function planifierActualisation() {
        affichagePrix.textContent = `${curseurPrix.value} €`;
        clearTimeout(minuterie);
        minuterie = setTimeout(actualiserMenus, 250);
    }

    function reinitialiser() {
        formulaire.reset();
        curseurPrix.value = curseurPrix.max;
        formulaire.querySelectorAll('input[type="number"]').forEach(champ => { champ.value = ''; });
        formulaire.querySelectorAll('select').forEach(liste => { liste.value = ''; });
        planifierActualisation();
    }

    formulaire.addEventListener('input', planifierActualisation);
    formulaire.addEventListener('submit', evenement => {
        evenement.preventDefault();
        actualiserMenus();
    });
    document.getElementById('btn-reset').addEventListener('click', reinitialiser);
    document.getElementById('btn-reset-empty').addEventListener('click', reinitialiser);
});
