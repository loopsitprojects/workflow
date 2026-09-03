<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class FeatureManager
{
    private const SETTINGS_FILE = 'settings.json';

    /**
     * Get all feature flags.
     */
    public static function getAll(): array
    {
        if (Storage::disk('local')->exists(self::SETTINGS_FILE)) {
            $content = Storage::disk('local')->get(self::SETTINGS_FILE);
            $data = json_decode($content, true);
            if (is_array($data)) {
                return array_merge(self::defaults(), $data);
            }
        }

        return self::defaults();
    }

    /**
     * Default feature flags.
     */
    public static function defaults(): array
    {
        return [
            'client_review_enabled' => true,
        ];
    }

    /**
     * Check if the Client Review / Send to Client feature is enabled.
     */
    public static function isClientReviewEnabled(): bool
    {
        $settings = self::getAll();
        return (bool) ($settings['client_review_enabled'] ?? true);
    }

    /**
     * Toggle the Client Review feature.
     */
    public static function setClientReviewEnabled(bool $enabled): void
    {
        $current = self::getAll();
        $current['client_review_enabled'] = $enabled;
        $current['updated_at'] = now()->toDateTimeString();

        Storage::disk('local')->put(self::SETTINGS_FILE, json_encode($current, JSON_PRETTY_PRINT));
    }
}
