<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// No UUID needed if using standard auto-incrementing ID

class SystemSetting extends Model
{
    use HasFactory;

    protected $table = 'system_settings'; // Explicit table name

    // Using standard auto-incrementing ID, so no UUID traits/properties needed.

    /**
     * The attributes that are mass assignable.
     * Only 'key' and 'value' are typically needed.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * The attributes that should be cast.
     * Consider casting 'value' if it stores specific types like JSON, boolean, number.
     *
     * @var array<string, string>
     */
    protected $casts = [
        // Example: 'value' => 'json',
        // Example: 'value' => 'boolean',
        // Example: 'value' => 'integer',
    ];

    /**
     * Helper method to retrieve a setting's value.
     * Caches the value for the duration of the request.
     *
     * @param string $key The setting key.
     * @param mixed|null $default The default value if the key is not found.
     * @return mixed
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        static $settingsCache = null;

        if (is_null($settingsCache)) {
            // Cache all settings in a single query for efficiency
            $settingsCache = self::all()->pluck('value', 'key');
        }

        return $settingsCache->get($key, $default);
    }
}