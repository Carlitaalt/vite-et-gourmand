<?php

namespace App\Repository;

/**
 * Jetons de réinitialisation de mot de passe (table `password_reset_tokens`).
 * Seule l'empreinte SHA-256 du jeton est stockée : une fuite de la base ne permet pas d'utiliser les liens envoyés.
 */
class TokenReinitialisationRepository extends AbstractRepository
{
    public function creer(int $utilisateurId, string $jeton, int $dureeValiditeMinutes): void
    {
        // Un seul lien valide à la fois par utilisateur
        $this->pdo->prepare('DELETE FROM password_reset_tokens WHERE utilisateur_id = ?')->execute([$utilisateurId]);

        $this->pdo->prepare('
            INSERT INTO password_reset_tokens (utilisateur_id, token, expire_at, used)
            VALUES (?, ?, DATE_ADD(NOW(), INTERVAL ? MINUTE), 0)
        ')->execute([$utilisateurId, hash('sha256', $jeton), $dureeValiditeMinutes]);
    }

    /** Renvoie l'identifiant de l'utilisateur si le jeton est valide (non utilisé, non expiré), sinon null. */
    public function trouverUtilisateurValide(string $jeton): ?int
    {
        $stmt = $this->pdo->prepare('
            SELECT utilisateur_id FROM password_reset_tokens
            WHERE token = ? AND used = 0 AND expire_at > NOW()
            LIMIT 1
        ');
        $stmt->execute([hash('sha256', $jeton)]);
        $id = $stmt->fetchColumn();

        return $id === false ? null : (int) $id;
    }

    public function marquerUtilise(string $jeton): void
    {
        $this->pdo->prepare('UPDATE password_reset_tokens SET used = 1 WHERE token = ?')->execute([hash('sha256', $jeton)]);
    }
}
