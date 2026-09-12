/**
 * Onglet « Statistiques & CA » de l'administrateur :
 * - graphique du nombre de commandes par menu (données MongoDB) avec Chart.js ;
 * - chiffre d'affaires filtrable par menu et par période (données MySQL).
 * Chaque changement de filtre recharge les données par fetch (api/statistiques.php), sans rechargement de la page.
 */
document.addEventListener('DOMContentLoaded', () => {
    const zone = document.getElementById('zone-statistiques');
    if (!zone) return;

    const racine = document.body.dataset.racine;
    const formulaire = document.getElementById('filtres-statistiques');
    const corpsTableau = document.getElementById('tableau-statistiques');
    const couleurs = ['#c4973a', '#2c4a2e', '#4a7a4a', '#4f47a8', '#9b1c2e', '#3d6b8f'];

    let donnees = JSON.parse(document.getElementById('donnees-statistiques').textContent);
    let typeGraphique = 'bar';
    let graphique = null;

    function dessinerGraphique() {
        // Chart.js a besoin que l'onglet soit visible pour connaître la taille du graphique
        if (!zone.closest('.compte-panel').classList.contains('active')) return;

        const statistiques = donnees.commandesParMenu;
        graphique?.destroy();
        graphique = new Chart(document.getElementById('graphique-menus'), {
            type: typeGraphique,
            data: {
                labels: statistiques.map(ligne => ligne.titre),
                datasets: [{
                    label: 'Nombre de commandes',
                    data: statistiques.map(ligne => ligne.nombreCommandes),
                    backgroundColor: couleurs,
                    borderRadius: typeGraphique === 'bar' ? 6 : 0,
                }],
            },
            options: {
                responsive: true,
                // La hauteur est fixée par le conteneur (.graphique-conteneur), la largeur suit l'écran
                maintainAspectRatio: false,
                plugins: { legend: { display: typeGraphique === 'pie' } },
                scales: typeGraphique === 'bar' ? { y: { beginAtZero: true, ticks: { stepSize: 1 } } } : {},
            },
        });
    }

    function afficherChiffres() {
        const chiffreAffaires = donnees.chiffreAffaires;
        document.getElementById('ca-total').textContent = formaterPrix(chiffreAffaires.total);
        document.getElementById('ca-nombre').textContent = `${chiffreAffaires.nombreCommandes} commande(s) terminée(s)`;

        // Fusion par menu : nombre de commandes (MongoDB) + chiffre d'affaires (MySQL)
        const lignes = new Map();
        donnees.commandesParMenu.forEach(stat => lignes.set(stat.menuId, { titre: stat.titre, commandes: stat.nombreCommandes, ca: 0 }));
        chiffreAffaires.parMenu.forEach(stat => {
            const ligne = lignes.get(stat.menuId) ?? { titre: stat.titre, commandes: 0, ca: 0 };
            ligne.ca = stat.chiffreAffaires;
            lignes.set(stat.menuId, ligne);
        });

        const menuFiltre = formulaire.elements.menu.value;
        const affichees = [...lignes.entries()].filter(([menuId]) => !menuFiltre || String(menuId) === menuFiltre);

        corpsTableau.innerHTML = affichees.length === 0
            ? '<tr><td colspan="4">Aucune donnée pour ces critères.</td></tr>'
            : affichees.map(([, ligne]) => {
                const part = chiffreAffaires.total > 0 ? Math.round(ligne.ca / chiffreAffaires.total * 100) : 0;
                return `
                    <tr>
                        <th scope="row">${echapperHtml(ligne.titre)}</th>
                        <td>${ligne.commandes}</td>
                        <td>${formaterPrix(ligne.ca)}</td>
                        <td>
                            <div class="admin-progress-container">
                                <div class="admin-progress-bar" aria-hidden="true"><div class="admin-progress-bar__fill" style="width:${part}%"></div></div>
                                <span>${part} %</span>
                            </div>
                        </td>
                    </tr>`;
            }).join('');
    }

    async function actualiser() {
        const parametres = new URLSearchParams();
        for (const [nom, valeur] of new FormData(formulaire)) {
            if (valeur !== '') parametres.append(nom, valeur);
        }

        try {
            donnees = await appelApi(`${racine}api/statistiques.php?${parametres}`);
            dessinerGraphique();
            afficherChiffres();
        } catch (erreur) {
            annoncer(erreur.message);
        }
    }

    formulaire.addEventListener('change', actualiser);
    formulaire.addEventListener('reset', () => setTimeout(actualiser));

    zone.querySelectorAll('.admin-chart-btn').forEach(bouton => {
        bouton.addEventListener('click', () => {
            typeGraphique = bouton.dataset.type;
            zone.querySelectorAll('.admin-chart-btn').forEach(autre => {
                autre.classList.toggle('active', autre === bouton);
                autre.setAttribute('aria-pressed', String(autre === bouton));
            });
            dessinerGraphique();
        });
    });

    // Le graphique est dessiné quand l'onglet devient visible (événement envoyé par main.js)
    document.addEventListener('onglet-affiche', evenement => {
        if (evenement.detail === 'statistiques') dessinerGraphique();
    });

    afficherChiffres();
    dessinerGraphique();
});
