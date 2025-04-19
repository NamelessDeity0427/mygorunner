<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    use HasFactory;

    protected $table = 'system_settings';

    protected $fillable = [
        'key',
        'value',
    ];

    protected $casts = [
        'value' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Retrieve a setting's value
    public static function getValue(string $key, mixed $default = null): mixed
    {
        return Cache::remember("setting.{$key}", now()->addHours(24), function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            return $setting ? json_decode($setting->value, true) : $default;
        });
    }

    // Set or update a setting
    public static function setValue(string $key, mixed $value): void
    {
        self::updateOrCreate(
            ['key' => $key],
            ['value' => is_array($value) ? json_encode($value) : $value]
        );
        Cache::forget("setting.{$key}");
    }

    // Scopes
    public function scopeForCategory($query, string $category)
    {
        return $query->where('key', 'like', "{$category}%");
    }

    // Helper Methods
    public static function getServiceFee(string $serviceType): float
    {
        $fees = self::getValue('service_fees', []);
        return $fees[$serviceType] ?? 0.0;
    }
}