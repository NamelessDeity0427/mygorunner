<?php
namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use MatanYadaev\EloquentSpatial\Objects\Point;

class ValidPoint implements Rule
{
    protected $latitudeField;
    protected $longitudeField;

    public function __construct(string $latitudeField, string $longitudeField)
    {
        $this->latitudeField = $latitudeField;
        $this->longitudeField = $longitudeField;
    }

    public function passes($attribute, $value): bool
    {
        $latitude = request()->input($this->latitudeField);
        $longitude = request()->input($this->longitudeField);

        if (!is_numeric($latitude) || !is_numeric($longitude)) {
            return false;
        }

        try {
            new Point((float) $latitude, (float) $longitude);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function message(): string
    {
        return 'The provided coordinates are invalid.';
    }
}