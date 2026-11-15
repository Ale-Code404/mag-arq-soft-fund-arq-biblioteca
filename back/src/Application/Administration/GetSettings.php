<?php

declare(strict_types=1);

namespace App\Application\Administration;

use App\Application\Administration\Dto\SettingsOutput;
use App\Application\Shared\QueryInput;
use App\Domain\Repositories\SettingsRepository;

/** Supporting query for CU-13: current settings. */
final class GetSettings
{
    public function __construct(private SettingsRepository $settings) {}

    public function execute(QueryInput $e): SettingsOutput
    {
        return SettingsOutput::from($this->settings->current());
    }
}
