<?php

namespace OpenDominion\Services\Dominion;

use OpenDominion\Helpers\SpellHelper;
use OpenDominion\Models\Dominion;
use OpenDominion\Models\InfoOp;
use OpenDominion\Models\Realm;

class InfoOpService
{
    /** @var SpellHelper */
    protected $spellHelper;

    /**
     * InfoOpService constructor.
     */
    public function __construct()
    {
        $this->spellHelper = app(SpellHelper::class);
    }

    public function hasInfoOps(Realm $sourceRealm, Dominion $targetDominion): bool
    {
//        return ($sourceRealm
//                ->infoOps()
//                ->targetDominion($targetDominion)
//                ->notInvalid()
//                ->count() > 0);

        return ($sourceRealm->infoOps->filter(function (InfoOp $infoOp) use ($targetDominion) {
                return (
                    !$infoOp->isInvalid() &&
                    ($infoOp->target_dominion_id === $targetDominion->id)
                );
            })->count() > 0);
    }

    public function hasInfoOp(Realm $sourceRealm, Dominion $targetDominion, string $type): bool
    {
//        return ($sourceRealm
//                ->infoOps()
//                ->targetDominion($targetDominion)
//                ->whereType($type)
//                ->notInvalid()
//                ->count() === 1);

        return ($sourceRealm->infoOps->filter(function (InfoOp $infoOp) use ($targetDominion, $type) {
                return (
                    !$infoOp->isInvalid() &&
                    ($infoOp->target_dominion_id === $targetDominion->id) &&
                    ($infoOp->type === $type)
                );
            })->count() === 1);
    }

    public function getInfoOp(Realm $sourceRealm, Dominion $targetDominion, string $type): InfoOp
    {
        return $sourceRealm->infoOps->filter(function (InfoOp $infoOp) use ($targetDominion, $type) {
            return (
                !$infoOp->isInvalid() &&
                ($infoOp->target_dominion_id === $targetDominion->id) &&
                ($infoOp->type === $type)
            );
        })->first();
    }

    public function getOffensivePower(Realm $sourceRealm, Dominion $targetDominion): ?int
    {
        // clear sight (units)

        return null;
    }

    public function getOffensivePowerString(Realm $sourceRealm, Dominion $targetDominion): string
    {
        $op = $this->getOffensivePower($sourceRealm, $targetDominion);

        if ($op === null) {
            return '???';
        }

        return 'todo';
    }

    public function getDefensivePower(Realm $sourceRealm, Dominion $targetDominion): ?int
    {
        // clear sight (units + draftees + land

        return null;
    }

    public function getDefensivePowerString(Realm $sourceRealm, Dominion $targetDominion): string
    {
        $dp = $this->getDefensivePower($sourceRealm, $targetDominion);

        if ($dp === null) {
            return '???';
        }

        return 'todo';
    }

    public function getLand(Realm $sourceRealm, Dominion $targetDominion): ?int
    {
        if (!$this->hasInfoOp($sourceRealm, $targetDominion, 'clear_sight')) {
            return null;
        }

        $clearSightInfoOp = $this->getInfoOp($sourceRealm, $targetDominion, 'clear_sight');

        return $clearSightInfoOp->data['land'];
    }

    public function getLandString(Realm $sourceRealm, Dominion $targetDominion): string
    {
        $land = $this->getLand($sourceRealm, $targetDominion);

        if ($land === null) {
            return '???';
        }

        $clearSight = $this->getInfoOp($sourceRealm, $targetDominion, 'clear_sight');

        $return = number_format($clearSight->data['land']);

        if ($clearSight->isStale()) {
            $return .= '?';
        }

        return $return;
    }

    public function getNetworth(Realm $sourceRealm, Dominion $targetDominion): ?int
    {
        if (!$this->hasInfoOp($sourceRealm, $targetDominion, 'clear_sight')) {
            return null;
        }

        $clearSightInfoOp = $this->getInfoOp($sourceRealm, $targetDominion, 'clear_sight');

        return $clearSightInfoOp->data['networth'];
    }

    public function getNetworthString(Realm $sourceRealm, Dominion $targetDominion): string
    {
        $networth = $this->getNetworth($sourceRealm, $targetDominion);

        if ($networth === null) {
            return '???';
        }

        $clearSight = $this->getInfoOp($sourceRealm, $targetDominion, 'clear_sight');

        $return = number_format($clearSight->data['networth']);

        if ($clearSight->isStale()) {
            $return .= '?';
        }

        return $return;
    }

    public function getLastInfoOp(Realm $sourceRealm, Dominion $targetDominion): InfoOp
    {
        return $sourceRealm->infoOps->filter(function ($infoOp) use ($targetDominion) {
            return ($infoOp->target_dominion_id === $targetDominion->id);
        })
            ->sortByDesc('updated_at')
            ->first();
    }

    public function getLastInfoOpSpellName(Realm $sourceRealm, Dominion $targetDominion): string
    {
        return $this->spellHelper->getInfoOpSpells()->filter(function ($spell) use ($sourceRealm, $targetDominion) {
            return ($spell['key'] === $this->getLastInfoOp($sourceRealm, $targetDominion)->type);
        })->first()['name'];
    }

    public function getNumberOfActiveInfoOps(Realm $sourceRealm, Dominion $targetDominion): int
    {
        return $this->spellHelper->getInfoOpSpells()->filter(function ($value) use ($sourceRealm, $targetDominion) {
            return $this->hasInfoOp($sourceRealm, $targetDominion, $value['key']);
        })->count();
    }

    public function getMaxInfoOps(): int
    {
        return $this->spellHelper->getInfoOpSpells()->count();
    }
}
