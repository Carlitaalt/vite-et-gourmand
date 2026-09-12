<?php

namespace App\Repository;

use App\Entity\Avis;
use App\Enum\StatutAvis;

class AvisRepository extends AbstractRepository
{
    private const SELECT = '
        SELECT a.*, u.prenom, u.nom,
               (SELECT m.titre FROM commande_menu cm INNER JOIN menu m ON m.menu_id = cm.menu_id
                WHERE cm.commande_id = a.commande_id LIMIT 1) AS menu_titre
        FROM avis a
        INNER JOIN utilisateur u ON u.utilisateur_id = a.utilisateur_id
    ';

    /** Avis validés, affichés sur la page d'accueil. @return Avis[] */
    public function findPublies(int $limite = 6): array
    {
        $stmt = $this->pdo->prepare(self::SELECT . ' WHERE a.statut_avis_id = ? ORDER BY a.created_at DESC LIMIT ?');
        $stmt->execute([StatutAvis::Publie->value, $limite]);

        return array_map(fn(array $l) => $this->hydrater($l), $stmt->fetchAll());
    }

    /** Tous les avis, ceux en attente de modération en premier. @return Avis[] */
    public function findAll(): array
    {
        $stmt = $this->pdo->prepare(self::SELECT . ' ORDER BY (a.statut_avis_id = ?) DESC, a.created_at DESC');
        $stmt->execute([StatutAvis::EnAttente->value]);

        return array_map(fn(array $l) => $this->hydrater($l), $stmt->fetchAll());
    }

    /**
     * @param int[] $commandeIds
     * @return array<int, Avis> indexés par commande_id
     */
    public function findByCommandeIds(array $commandeIds): array
    {
        if (empty($commandeIds)) {
            return [];
        }

        $stmt = $this->pdo->prepare(self::SELECT . ' WHERE a.commande_id IN (' . $this->marqueurs($commandeIds) . ')');
        $stmt->execute(array_values($commandeIds));

        $avis = [];
        foreach ($stmt->fetchAll() as $l) {
            $avis[(int) $l['commande_id']] = $this->hydrater($l);
        }

        return $avis;
    }

    public function inserer(Avis $avis): void
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO avis (commande_id, utilisateur_id, statut_avis_id, note, description)
            VALUES (?, ?, ?, ?, ?)
        ');
        $stmt->execute([
            $avis->getCommandeId(),
            $avis->getUtilisateurId(),
            $avis->getStatut()->value,
            $avis->getNote(),
            $avis->getCommentaire(),
        ]);
    }

    /** Renvoie false si l'avis n'existe pas. */
    public function changerStatut(int $avisId, StatutAvis $statut): bool
    {
        $stmt = $this->pdo->prepare('UPDATE avis SET statut_avis_id = ? WHERE avis_id = ?');
        $stmt->execute([$statut->value, $avisId]);

        return $stmt->rowCount() === 1 || $this->existe($avisId);
    }

    private function existe(int $avisId): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM avis WHERE avis_id = ?');
        $stmt->execute([$avisId]);

        return (int) $stmt->fetchColumn() > 0;
    }

    private function hydrater(array $l): Avis
    {
        return new Avis(
            (int) $l['avis_id'],
            (int) $l['commande_id'],
            (int) $l['utilisateur_id'],
            (int) $l['note'],
            $l['description'] ?? '',
            StatutAvis::from((int) $l['statut_avis_id']),
            $this->date($l['created_at']),
            $l['prenom'],
            $l['nom'],
            $l['menu_titre'],
        );
    }
}
