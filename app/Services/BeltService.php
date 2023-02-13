<?php

declare(strict_types = 1);

namespace App\Services;

use App\Models\Belt;
use App\Models\Member;
use Illuminate\Database\Eloquent\Collection;

class BeltService
{
    public function syncMemberBeltsFromRequest(Member $member, array $belts): void
    {
        $savedBelts = $member->belts()->get();
        $this->deleteNotExistingBelts($savedBelts, $belts);

        $saveMany = [];

        foreach ($belts as $belt) {
            $model = $belt['belt_id']
                ? $savedBelts->find($belt['belt_id'])
                : null;

            $date = isset($belt['date']) && '' !== $belt['date']
                ? $belt['date']
                : null;

            if ($model) {
                $model->name = $belt['name'];
                $model->date = $date;
                $saveMany[] = $model;
            } else {
                $saveMany[] = new Belt([
                    'name' => $belt['name'],
                    'date' => $date,
                    'member_id' => $member->id,
                ]);
            }
        }

        $member->belts()->saveMany($saveMany);
    }

    public function translateBeltName(string $name): string
    {
        return str_replace('_', ' ', strtoupper($name));
    }

    public function findHighestBeltFromRequest(array $belts): ?string
    {
        $highestLevel = null;
        $ownBelts = array_column($belts, 'name');
        $reOrderedBeltLevels = array_reverse(Belt::LEVELS);

        foreach ($reOrderedBeltLevels as $level) {
            if (in_array($level, $ownBelts, true)) {
                $highestLevel = $level;

                break;
            }
        }

        return $highestLevel;
    }

    private function deleteNotExistingBelts(Collection $savedBelts, array $belts): void
    {
        $beltsDeleteId = $savedBelts->pluck('id')->diff(array_column($belts, 'belt_id'));

        if ($beltsDeleteId) {
            Belt::destroy($beltsDeleteId);
        }
    }
}
