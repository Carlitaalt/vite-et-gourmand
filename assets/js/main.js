/**
 * Fonctions communes à toutes les pages (chargé avant le script propre à chaque page).
 */

// --- Appel à l'API de l'application (fetch) ---

/**
 * Envoie une requête à un point d'accès de api/ et renvoie la réponse JSON.
 * Le jeton CSRF de la page est ajouté à chaque appel ; en cas d'erreur HTTP, le message du serveur est levé.
 */
async function appelApi(url, options = {}) {
    const jetonCsrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    const reponse = await fetch(url, {
        ...options,
        headers: {
            Accept: 'application/json',
            'X-CSRF-Token': jetonCsrf,
            ...(options.headers ?? {}),
        },
    });

    const donnees = await reponse.json().catch(() => ({}));
    if (!reponse.ok) {
        throw new Error(donnees.erreur ?? 'Une erreur est survenue. Veuillez réessayer.');
    }

    return donnees;
}

function formaterPrix(montant) {
    return Number(montant).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' €';
}

/** Échappe un texte avant de l'insérer dans du HTML (protection XSS côté navigateur). */
function echapperHtml(texte) {
    const element = document.createElement('div');
    element.textContent = texte ?? '';
    return element.innerHTML;
}

/** Message lu par les lecteurs d'écran après une action faite sans rechargement. */
function annoncer(message) {
    const zone = document.getElementById('annonces');
    if (zone) {
        zone.textContent = '';
        setTimeout(() => { zone.textContent = message; }, 50);
    }
}

// --- Onglets accessibles (motif ARIA « tabs ») : clic, flèches du clavier, onglet indiqué dans l'URL ---

function activerOnglet(bouton) {
    const liste = bouton.closest('[role="tablist"]');

    liste.querySelectorAll('[role="tab"]').forEach(onglet => {
        const actif = onglet === bouton;
        onglet.classList.toggle('active', actif);
        onglet.setAttribute('aria-selected', String(actif));
        onglet.tabIndex = actif ? 0 : -1;
        document.getElementById(onglet.getAttribute('aria-controls'))?.classList.toggle('active', actif);
    });

    if (bouton.dataset.tab) {
        history.replaceState(null, '', `#${bouton.dataset.tab}`);
    }
    document.dispatchEvent(new CustomEvent('onglet-affiche', { detail: bouton.dataset.tab ?? bouton.dataset.sous }));
}

document.querySelectorAll('[role="tablist"]').forEach(liste => {
    const onglets = [...liste.querySelectorAll('[role="tab"]')];

    onglets.forEach((onglet, index) => {
        onglet.tabIndex = onglet.classList.contains('active') ? 0 : -1;
        onglet.addEventListener('click', () => activerOnglet(onglet));
        onglet.addEventListener('keydown', evenement => {
            if (evenement.key !== 'ArrowRight' && evenement.key !== 'ArrowLeft') return;
            const decalage = evenement.key === 'ArrowRight' ? 1 : -1;
            const suivant = onglets[(index + decalage + onglets.length) % onglets.length];
            suivant.focus();
            activerOnglet(suivant);
        });
    });
});

// Après un formulaire, le contrôleur redirige vers page.php#onglet : on rouvre cet onglet
if (location.hash) {
    const ongletDemande = document.querySelector(`[role="tab"][data-tab="${CSS.escape(location.hash.slice(1))}"]`);
    if (ongletDemande) activerOnglet(ongletDemande);
}

// --- Blocs dépliables : un bouton data-bascule="id" affiche / masque l'élément correspondant ---

document.addEventListener('click', evenement => {
    const bouton = evenement.target.closest('[data-bascule]');
    if (!bouton) return;

    const cible = document.getElementById(bouton.dataset.bascule);
    if (!cible) return;

    cible.hidden = !cible.hidden;
    document.querySelectorAll(`[data-bascule="${bouton.dataset.bascule}"][aria-expanded]`)
        .forEach(declencheur => declencheur.setAttribute('aria-expanded', String(!cible.hidden)));

    if (!cible.hidden) {
        cible.querySelector('input:not([type="hidden"]), select, textarea')?.focus();
    }
});

// --- Confirmation avant une action irréversible (formulaires data-confirmation) ---

document.addEventListener('submit', evenement => {
    const message = evenement.target.dataset.confirmation;
    if (message && !confirm(message)) {
        evenement.preventDefault();
        evenement.stopImmediatePropagation();
    }
}, true);

// --- Les messages de succès disparaissent après quelques secondes ---

document.querySelectorAll('.auth-alert--success').forEach(alerte => {
    setTimeout(() => {
        alerte.style.transition = 'opacity 0.5s ease';
        alerte.style.opacity = '0';
        setTimeout(() => alerte.remove(), 500);
    }, 6000);
});
