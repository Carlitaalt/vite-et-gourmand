<?php

namespace App\Repository;

use App\Entity\Utilisateur;
use App\Enum\Role;

class UtilisateurRepository extends AbstractRepository
{
    public function findById(int $id): ?Utilisateur
    {
        $stmt = $this->pdo->prepare('SELECT * FROM utilisateur WHERE utilisateur_id = ?');
        $stmt->execute([$id]);
        $ligne = $stmt->fetch();

        return $ligne ? $this->hydrater($ligne) : null;
    }

    public function findByEmail(string $email): ?Utilisateur
    {
        $stmt = $this->pdo->prepare('SELECT * FROM utilisateur WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $ligne = $stmt->fetch();

        return $ligne ? $this->hydrater($ligne) : null;
    }

    /**
     * @param int[] $ids
     * @return array<int, Utilisateur> indexés par identifiant
     */
    public function findByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $stmt = $this->pdo->prepare('SELECT * FROM utilisateur WHERE utilisateur_id IN (' . $this->marqueurs($ids) . ')');
        $stmt->execute(array_values($ids));

        $utilisateurs = [];
        foreach ($stmt->fetchAll() as $ligne) {
            $utilisateurs[(int) $ligne['utilisateur_id']] = $this->hydrater($ligne);
        }

        return $utilisateurs;
    }

    public function emailExiste(string $email, ?int $saufUtilisateurId = null): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM utilisateur WHERE email = ? AND utilisateur_id <> ?');
        $stmt->execute([$email, $saufUtilisateurId ?? 0]);

        return (int) $stmt->fetchColumn() > 0;
    }

    public function inserer(Utilisateur $utilisateur): void
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO utilisateur (role_id, prenom, nom, email, mot_de_passe, telephone, adresse_postale, ville, pays, actif)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([
            $utilisateur->getRole()->value,
            $utilisateur->getPrenom(),
            $utilisateur->getNom(),
            $utilisateur->getEmail(),
            $utilisateur->getMotDePasseHash(),
            $utilisateur->getTelephone(),
            $utilisateur->getAdressePostale(),
            $utilisateur->getVille(),
            $utilisateur->getPays(),
            (int) $utilisateur->estActif(),
        ]);

        $utilisateur->setId((int) $this->pdo->lastInsertId());
    }

    public function mettreAJourProfil(Utilisateur $utilisateur): void
    {
        $stmt = $this->pdo->prepare('
            UPDATE utilisateur
            SET prenom = ?, nom = ?, email = ?, telephone = ?, adresse_postale = ?, ville = ?, pays = ?, updated_at = NOW()
            WHERE utilisateur_id = ?
        ');
        $stmt->execute([
            $utilisateur->getPrenom(),
            $utilisateur->getNom(),
            $utilisateur->getEmail(),
            $utilisateur->getTelephone(),
            $utilisateur->getAdressePostale(),
            $utilisateur->getVille(),
            $utilisateur->getPays(),
            $utilisateur->getId(),
        ]);
    }

    public function mettreAJourMotDePasse(Utilisateur $utilisateur): void
    {
        $stmt = $this->pdo->prepare('UPDATE utilisateur SET mot_de_passe = ?, updated_at = NOW() WHERE utilisateur_id = ?');
        $stmt->execute([$utilisateur->getMotDePasseHash(), $utilisateur->getId()]);
    }

    public function mettreAJourActivation(Utilisateur $utilisateur): void
    {
        $stmt = $this->pdo->prepare('UPDATE utilisateur SET actif = ?, updated_at = NOW() WHERE utilisateur_id = ?');
        $stmt->execute([(int) $utilisateur->estActif(), $utilisateur->getId()]);
    }

    public function supprimer(int $id): void
    {
        $this->pdo->prepare('DELETE FROM utilisateur WHERE utilisateur_id = ?')->execute([$id]);
    }

    /** Transforme une ligne de la table `utilisateur` en objet Utilisateur. */
    public function hydrater(array $ligne): Utilisateur
    {
        return new Utilisateur(
            (int) $ligne['utilisateur_id'],
            Role::from((int) $ligne['role_id']),
            $ligne['prenom'],
            $ligne['nom'],
            $ligne['email'],
            $ligne['mot_de_passe'],
            $ligne['telephone'],
            $ligne['adresse_postale'],
            $ligne['ville'],
            $ligne['pays'],
            (bool) $ligne['actif'],
            $this->date($ligne['created_at']),
            $this->date($ligne['updated_at']),
        );
    }
}
