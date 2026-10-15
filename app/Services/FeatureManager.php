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
            'direct_design_enabled' => true,
            'enabled_project_types' => [
                'retainer' => true,
                'campaign' => true,
                'pitch' => true,
            ],
        ];
    }

    /**
     * Check if the Direct Design feature is enabled.
     */
    public static function isDirectDesignEnabled(): bool
    {
        $settings = self::getAll();
        return (bool) ($settings['direct_design_enabled'] ?? true);
    }

    /**
     * Set the Direct Design feature status.
     */
    public static function setDirectDesignEnabled(bool $enabled): void
    {
        $current = self::getAll();
        $current['direct_design_enabled'] = $enabled;
        $current['updated_at'] = now()->toDateTimeString();

        Storage::disk('local')->put(self::SETTINGS_FILE, json_encode($current, JSON_PRETTY_PRINT));
    }

    /**
     * Toggle the Direct Design feature.
     */
    public static function toggleDirectDesign(): bool
    {
        $newStatus = !self::isDirectDesignEnabled();
        self::setDirectDesignEnabled($newStatus);
        return $newStatus;
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

    /**
     * Get the enabled project types array: ['retainer' => bool, 'campaign' => bool, 'pitch' => bool]
     */
    public static function getProjectTypesStatus(): array
    {
        $settings = self::getAll();
        $defaults = [
            'retainer' => true,
            'campaign' => true,
            'pitch' => true,
        ];

        $saved = $settings['enabled_project_types'] ?? [];
        return array_merge($defaults, is_array($saved) ? $saved : []);
    }

    /**
     * Get list of currently enabled project type keys, e.g. ['retainer', 'campaign', 'pitch']
     */
    public static function getEnabledProjectTypes(): array
    {
        $status = self::getProjectTypesStatus();
        return array_keys(array_filter($status));
    }

    /**
     * Check if a specific project type is enabled.
     */
    public static function isProjectTypeEnabled(string $type): bool
    {
        $status = self::getProjectTypesStatus();
        return (bool) ($status[$type] ?? false);
    }

    /**
     * Set a project type enabled or disabled.
     */
    public static function setProjectTypeEnabled(string $type, bool $enabled): void
    {
        $current = self::getAll();
        $types = self::getProjectTypesStatus();
        $types[$type] = $enabled;
        $current['enabled_project_types'] = $types;
        $current['updated_at'] = now()->toDateTimeString();

        Storage::disk('local')->put(self::SETTINGS_FILE, json_encode($current, JSON_PRETTY_PRINT));
    }

    /**
     * Toggle a project type. Returns new status.
     * Throws exception if attempting to disable the last enabled type.
     */
    public static function toggleProjectType(string $type): bool
    {
        $types = self::getProjectTypesStatus();
        $currentStatus = (bool) ($types[$type] ?? true);
        $newStatus = !$currentStatus;

        if (!$newStatus) {
            $activeCount = count(array_filter($types));
            if ($activeCount <= 1 && $currentStatus) {
                throw new \InvalidArgumentException('At least one project type must remain enabled.');
            }
        }

        self::setProjectTypeEnabled($type, $newStatus);
        return $newStatus;
    }
}
