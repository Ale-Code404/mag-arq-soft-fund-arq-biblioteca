<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Settings\LibrarySettings;

interface SettingsRepository
{
    public function current(): LibrarySettings;

    /** Saves a new version effective now; the previous one stays as history. */
    public function save(LibrarySettings $settings, int $userId): void;
}
