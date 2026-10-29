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
            'analytics_enabled' => true,
            'halloween_theme_enabled' => false,
            'christmas_theme_enabled' => false,
            'active_theme' => 'dark',
            'enabled_project_types' => [
                'retainer' => true,
                'campaign' => true,
                'pitch' => true,
            ],
        ];
    }

    /**
     * Get the active platform theme ('halloween', 'dark', 'light').
     */
    public static function getActiveTheme(): string
    {
        $settings = self::getAll();
        if (!empty($settings['active_theme'])) {
            return $settings['active_theme'];
        }
        return self::isHalloweenThemeEnabled() ? 'halloween' : 'dark';
    }

    /**
     * Set the active platform theme.
     */
    public static function setActiveTheme(string $theme): void
    {
        $current = self::getAll();
        $current['active_theme'] = $theme;
        $current['halloween_theme_enabled'] = ($theme === 'halloween');
        $current['christmas_theme_enabled'] = ($theme === 'christmas');
        $current['updated_at'] = now()->toDateTimeString();

        Storage::disk('local')->put(self::SETTINGS_FILE, json_encode($current, JSON_PRETTY_PRINT));
    }

    /**
     * Check if the Halloween Theme / Spooky Mode feature is enabled.
     */
    public static function isHalloweenThemeEnabled(): bool
    {
        $settings = self::getAll();
        if (isset($settings['active_theme'])) {
            return $settings['active_theme'] === 'halloween';
        }
        return (bool) ($settings['halloween_theme_enabled'] ?? false);
    }

    /**
     * Set the Halloween Theme feature status.
     */
    public static function setHalloweenThemeEnabled(bool $enabled): void
    {
        $current = self::getAll();
        $current['halloween_theme_enabled'] = $enabled;
        if ($enabled) {
            $current['active_theme'] = 'halloween';
            $current['christmas_theme_enabled'] = false;
        } else {
            $current['active_theme'] = 'dark';
        }
        $current['updated_at'] = now()->toDateTimeString();

        Storage::disk('local')->put(self::SETTINGS_FILE, json_encode($current, JSON_PRETTY_PRINT));
    }

    /**
     * Toggle the Halloween Theme feature.
     */
    public static function toggleHalloweenTheme(): bool
    {
        $newStatus = !self::isHalloweenThemeEnabled();
        self::setHalloweenThemeEnabled($newStatus);
        return $newStatus;
    }

    /**
     * Check if the Christmas Theme / Festive Mode feature is enabled.
     */
    public static function isChristmasThemeEnabled(): bool
    {
        $settings = self::getAll();
        if (isset($settings['active_theme'])) {
            return $settings['active_theme'] === 'christmas';
        }
        return (bool) ($settings['christmas_theme_enabled'] ?? false);
    }

    /**
     * Set the Christmas Theme feature status.
     */
    public static function setChristmasThemeEnabled(bool $enabled): void
    {
        $current = self::getAll();
        $current['christmas_theme_enabled'] = $enabled;
        if ($enabled) {
            $current['active_theme'] = 'christmas';
            $current['halloween_theme_enabled'] = false;
        } else {
            $current['active_theme'] = 'dark';
        }
        $current['updated_at'] = now()->toDateTimeString();

        Storage::disk('local')->put(self::SETTINGS_FILE, json_encode($current, JSON_PRETTY_PRINT));
    }

    /**
     * Toggle the Christmas Theme feature.
     */
    public static function toggleChristmasTheme(): bool
    {
        $newStatus = !self::isChristmasThemeEnabled();
        self::setChristmasThemeEnabled($newStatus);
        return $newStatus;
    }

    /**
     * Check if the Analytics tab & reports feature is enabled.
     */
    public static function isAnalyticsEnabled(): bool
    {
        $settings = self::getAll();
        return (bool) ($settings['analytics_enabled'] ?? true);
    }

    /**
     * Set the Analytics feature status.
     */
    public static function setAnalyticsEnabled(bool $enabled): void
    {
        $current = self::getAll();
        $current['analytics_enabled'] = $enabled;
        $current['updated_at'] = now()->toDateTimeString();

        Storage::disk('local')->put(self::SETTINGS_FILE, json_encode($current, JSON_PRETTY_PRINT));
    }

    /**
     * Toggle the Analytics feature.
     */
    public static function toggleAnalytics(): bool
    {
        $newStatus = !self::isAnalyticsEnabled();
        self::setAnalyticsEnabled($newStatus);
        return $newStatus;
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
