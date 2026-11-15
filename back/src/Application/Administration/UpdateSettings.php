<?php

declare(strict_types=1);

namespace App\Application\Administration;

use App\Application\Administration\Dto\SettingsOutput;
use App\Application\Administration\Dto\UpdateSettingsInput;
use App\Application\Ports\CapacityCache;
use App\Domain\Repositories\SettingsRepository;
use App\Domain\Settings\LibrarySettings;

/** CU-13 Update settings: saves a new current configuration. */
final class UpdateSettings
{
    public function __construct(
        private SettingsRepository $settings,
        private CapacityCache $cache,
    ) {}

    public function execute(UpdateSettingsInput $e): SettingsOutput
    {
        $newSettings = new LibrarySettings(
            $e->loanDays, $e->renewalDays, $e->maxRenewals,
            $e->dailyFineValue, $e->clearanceCm, $e->defaultThicknessCm,
        );
        $this->settings->save($newSettings, $e->userId);
        $this->cache->invalidate();   // clearance and default thickness change the metrics

        return SettingsOutput::from($newSettings);
    }
}
