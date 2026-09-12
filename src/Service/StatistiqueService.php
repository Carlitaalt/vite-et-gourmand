<?php

namespace App\Service;

use App\Entity\StatistiqueMenu;
use App\Repository\CommandeRepository;
use App\Repository\StatistiqueRepository;
use DateTimeImmutable;

/**
 * Statistiques de l'espace administrateur :
 * - nombre de commandes par menu : MongoDB (base NoSQL, exigence du cahier des charges) ;
 * - chiffre d'affaires : MySQL, car seules les commandes réellement terminées doivent être comptées.
 */
class StatistiqueService
{
    public function __construct(
        private StatistiqueRepository $statistiques = new StatistiqueRepository(),
        private CommandeRepository $commandes = new CommandeRepository(),
    ) {
    }

    /** @return StatistiqueMenu[] */
    public function commandesParMenu(?string $debut = null, ?string $fin = null): array
    {
        return $this->statistiques->commandesParMenu($this->date($debut), $this->date($fin));
    }

    /**
     * @return array{total: float, nombreCommandes: int, parMenu: StatistiqueMenu[]}
     */
    public function chiffreAffaires(?string $menuId = null, ?string $debut = null, ?string $fin = null): array
    {
        $parMenu = $this->commandes->chiffreAffairesParMenu(
            ctype_digit((string) $menuId) ? (int) $menuId : null,
            $this->date($debut),
            $this->date($fin)
        );

        return [
            'total' => round(array_sum(array_map(fn(StatistiqueMenu $s) => $s->getChiffreAffaires(), $parMenu)), 2),
            'nombreCommandes' => array_sum(array_map(fn(StatistiqueMenu $s) => $s->getNombreCommandes(), $parMenu)),
            'parMenu' => $parMenu,
        ];
    }

    /** Date au format AAAA-MM-JJ (champ <input type="date">), sinon null. */
    private function date(?string $valeur): ?DateTimeImmutable
    {
        if ($valeur === null || $valeur === '') {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $valeur);

        return $date === false ? null : $date;
    }
}
