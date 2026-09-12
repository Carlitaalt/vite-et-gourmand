<?php

namespace App\Repository;

use App\Entity\Employe;
use App\Enum\Role;

class EmployeRepository extends AbstractRepository
{
    private const SELECT = '
        SELECT u.*, e.poste, e.salaire_horaire, e.date_embauche
        FROM utilisateur u
        INNER JOIN employes e ON e.utilisateur_id = u.utilisateur_id
    ';

    /** @return Employe[] */
    public function findAll(): array
    {
        $stmt = $this->pdo->prepare(self::SELECT . ' WHERE u.role_id = ? ORDER BY u.nom, u.prenom');
        $stmt->execute([Role::Employe->value]);

        return array_map(fn(array $ligne) => $this->hydrater($ligne), $stmt->fetchAll());
    }

    public function findByUtilisateurId(int $utilisateurId): ?Employe
    {
        $stmt = $this->pdo->prepare(self::SELECT . ' WHERE u.utilisateur_id = ?');
        $stmt->execute([$utilisateurId]);
        $ligne = $stmt->fetch();

        return $ligne ? $this->hydrater($ligne) : null;
    }

    /** Le compte utilisateur doit déjà être enregistré (il fournit l'identifiant). */
    public function inserer(Employe $employe): void
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO employes (utilisateur_id, poste, salaire_horaire, date_embauche)
            VALUES (?, ?, ?, CURDATE())
        ');
        $stmt->execute([
            $employe->getUtilisateur()->getId(),
            $employe->getPoste(),
            $employe->getSalaireHoraire(),
        ]);
    }

    public function mettreAJourContrat(Employe $employe): void
    {
        $stmt = $this->pdo->prepare('UPDATE employes SET poste = ?, salaire_horaire = ? WHERE utilisateur_id = ?');
        $stmt->execute([
            $employe->getPoste(),
            $employe->getSalaireHoraire(),
            $employe->getUtilisateur()->getId(),
        ]);
    }

    private function hydrater(array $ligne): Employe
    {
        return new Employe(
            (new UtilisateurRepository($this->pdo))->hydrater($ligne),
            $ligne['poste'],
            $ligne['salaire_horaire'] !== null ? (float) $ligne['salaire_horaire'] : null,
            $this->date($ligne['date_embauche']),
        );
    }
}
