<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Exceptions\ResourceNotFoundException;
use App\Domain\Repositories\SettingsRepository;
use App\Domain\Settings\LibrarySettings;
use App\Infrastructure\Persistence\Models\LibraryModel;
use App\Infrastructure\Persistence\Models\SettingsModel;

class EloquentSettingsRepository implements SettingsRepository
{
    public function current(): LibrarySettings
    {
        $model = SettingsModel::orderByDesc('effective_from')->orderByDesc('id')->first()
            ?? throw new ResourceNotFoundException('No hay una configuración vigente.');

        return $this->toDomain($model);
    }

    public function save(LibrarySettings $settings, int $userId): void
    {
        SettingsModel::create($this->toModel($settings) + [
            'library_id' => LibraryModel::firstOrFail()->id,
            'effective_from' => now(),
            'created_by' => $userId,
        ]);
    }

    private function toDomain(SettingsModel $m): LibrarySettings
    {
        return new LibrarySettings(
            $m->loan_days, $m->renewal_days, $m->max_renewals,
            $m->daily_fine_value, $m->clearance_cm, $m->default_thickness_cm,
        );
    }

    private function toModel(LibrarySettings $c): array
    {
        return [
            'loan_days' => $c->loanDays,
            'renewal_days' => $c->renewalDays,
            'max_renewals' => $c->maxRenewals,
            'daily_fine_value' => $c->dailyFineValue,
            'clearance_cm' => $c->clearanceCm,
            'default_thickness_cm' => $c->defaultThicknessCm,
        ];
    }
}
