/**
 * Inscription et réinitialisation : les règles du mot de passe sont cochées en direct pendant la saisie.
 * (Le serveur applique les mêmes règles : ce contrôle ne sert qu'au confort de l'utilisateur.)
 */
document.addEventListener('DOMContentLoaded', () => {
    const champ = document.getElementById('mot_de_passe');
    const confirmation = document.getElementById('mot_de_passe_conf');
    const messageCorrespondance = document.getElementById('mdp-match-msg');
    if (!champ || !document.getElementById('mdp-regles')) return;

    const regles = {
        longueur: valeur => valeur.length >= 10,
        majuscule: valeur => /[A-Z]/.test(valeur),
        minuscule: valeur => /[a-z]/.test(valeur),
        chiffre: valeur => /\d/.test(valeur),
        special: valeur => /[\W_]/.test(valeur),
    };

    function verifierRegles() {
        for (const [nom, test] of Object.entries(regles)) {
            const element = document.querySelector(`[data-regle="${nom}"]`);
            const respectee = test(champ.value);
            element.classList.toggle('mdp-regle--ok', respectee);
            element.setAttribute('aria-label', `${element.textContent} : ${respectee ? 'respecté' : 'non respecté'}`);
        }
    }

    function verifierCorrespondance() {
        if (!confirmation.value) {
            messageCorrespondance.textContent = '';
            return;
        }
        const identiques = confirmation.value === champ.value;
        messageCorrespondance.textContent = identiques ? 'Les mots de passe correspondent.' : 'Les mots de passe ne correspondent pas.';
        messageCorrespondance.classList.toggle('mdp-match-msg--ok', identiques);
    }

    champ.addEventListener('input', () => { verifierRegles(); verifierCorrespondance(); });
    confirmation?.addEventListener('input', verifierCorrespondance);
});
