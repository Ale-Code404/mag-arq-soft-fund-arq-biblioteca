<?php

declare(strict_types=1);

namespace App\Domain\Catalog;

use App\Domain\Exceptions\BusinessRuleException;

/** Physical book measurements in centimeters. Assumed ranges (R-02, P-03). */
final readonly class Dimensions
{
    public const HEIGHT_MIN = 5.0;

    public const HEIGHT_MAX = 60.0;

    public const WIDTH_MIN = 5.0;

    public const WIDTH_MAX = 50.0;

    public const THICKNESS_MIN = 0.1;

    public const THICKNESS_MAX = 15.0;

    public function __construct(
        public float $heightCm,
        public float $widthCm,
        public float $thicknessCm,
    ) {
        self::validateRange('height', $heightCm, self::HEIGHT_MIN, self::HEIGHT_MAX);
        self::validateRange('width', $widthCm, self::WIDTH_MIN, self::WIDTH_MAX);
        self::validateRange('thickness', $thicknessCm, self::THICKNESS_MIN, self::THICKNESS_MAX);
    }

    private static function validateRange(string $measure, float $value, float $min, float $max): void
    {
        if ($value < $min || $value > $max) {
            throw new BusinessRuleException('R-02', "El {$measure} debe estar entre {$min} y {$max} cm.");
        }
    }
}
