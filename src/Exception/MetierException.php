<?php

namespace App\Exception;

use RuntimeException;

/**
 * Erreur liée à une règle métier (minimum de personnes non atteint, statut interdit, mot de passe trop faible...).
 * Son message est pensé pour être affiché tel quel à l'utilisateur, contrairement aux erreurs techniques.
 */
class MetierException extends RuntimeException
{
}
