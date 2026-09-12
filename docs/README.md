# Documentation — Vite & Gourmand

## Documentation technique

| Chapitre | Contenu |
|---|---|
| [01 — Choix techniques](technique/01-choix-techniques.md) | Analyse du besoin, choix et justification de la stack, architecture en couches |
| [02 — Environnement de travail](technique/02-environnement-de-travail.md) | Outils, conteneurs Docker, variables d'environnement, organisation Git, structure du projet |
| [03 — Modèle conceptuel de données](technique/03-mcd.md) | MCD (Merise), modèle physique MySQL, intégrité, données MongoDB |
| [04 — Diagramme de classes](technique/04-diagramme-de-classes.md) | Modèle métier, couches, principes de POO appliqués |
| [05 — Cas d'utilisation](technique/05-cas-d-utilisation.md) | Acteurs, cas d'utilisation et règles de gestion |
| [06 — Diagrammes de séquence](technique/06-diagrammes-de-sequence.md) | Filtre des menus (fetch), commande, connexion, modération d'un avis (fetch) |
| [07 — Sécurité](technique/07-securite.md) | Mesures de sécurité, justification, RGPD, limites connues |
| [08 — Déploiement](technique/08-deploiement.md) | Procédure de mise en ligne sur Railway, pas à pas, et problèmes rencontrés |

## Manuel d'utilisation

[Manuel d'utilisation](manuel/manuel-utilisation.md) : présentation de l'application, comptes de démonstration et parcours
visiteur, client, employé et administrateur.

## Versions PDF

- [pdf/documentation-technique.pdf](pdf/documentation-technique.pdf)
- [pdf/manuel-utilisation.pdf](pdf/manuel-utilisation.pdf)

Les PDF sont générés à partir des fichiers Markdown (diagrammes Mermaid compris), l'application tournant en local :

```bash
bash docs/outils/exporter-pdf.sh            # ou : bash docs/outils/exporter-pdf.sh http://localhost:8000
```
