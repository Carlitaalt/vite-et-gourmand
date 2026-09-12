/**
 * Espace client : mise en surbrillance des étoiles au survol et à la sélection d'une note.
 * (Onglets, formulaires dépliables et confirmations sont gérés par main.js.)
 */
document.querySelectorAll('.avis-form__stars').forEach(conteneur => {
    const libelles = conteneur.querySelectorAll('.star-label');
    const boutons = conteneur.querySelectorAll('input[type="radio"]');

    function colorer(jusquA) {
        libelles.forEach((libelle, index) => {
            libelle.querySelector('.star-icon').classList.toggle('star-active', index <= jusquA);
        });
    }

    function noteChoisie() {
        const coche = conteneur.querySelector('input:checked');
        return coche ? Number(coche.value) - 1 : -1;
    }

    libelles.forEach((libelle, index) => libelle.addEventListener('mouseenter', () => colorer(index)));
    boutons.forEach(bouton => bouton.addEventListener('change', () => colorer(noteChoisie())));
    conteneur.addEventListener('mouseleave', () => colorer(noteChoisie()));
});
