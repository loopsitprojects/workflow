<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MaintenanceController extends Controller
{
    private const MAINTENANCE_FILE = 'maintenance.json';

    public static function getStatus(): array
    {
        if (Storage::exists(self::MAINTENANCE_FILE)) {
            $content = Storage::get(self::MAINTENANCE_FILE);
            $data = json_decode($content, true);
            if (is_array($data)) {
                return [
                    'enabled'    => !empty($data['enabled']),
                    'message'    => $data['message'] ?? 'We are currently updating the application. Please check back shortly.',
                    'enabled_at' => $data['enabled_at'] ?? null,
                ];
            }
        }

        return [
            'enabled'    => false,
            'message'    => 'We are currently updating the application. Please check back shortly.',
            'enabled_at' => null,
        ];
    }

    public function toggle(Request $request)
    {
        $current = self::getStatus();
        $newStatus = !$current['enabled'];

        $data = [
            'enabled'    => $newStatus,
            'message'    => $request->input('message', $current['message']),
            'enabled_at' => $newStatus ? now()->toDateTimeString() : null,
        ];

        Storage::put(self::MAINTENANCE_FILE, json_encode($data, JSON_PRETTY_PRINT));

        $msg = $newStatus 
            ? 'Maintenance Mode has been ENABLED. Non-admin users will now see the maintenance screen.' 
            : 'Maintenance Mode has been DISABLED. Normal system access is restored.';

        return redirect()->route('admin.settings')->with('success', $msg);
    }

    public function updateMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $current = self::getStatus();
        $current['message'] = $request->input('message');

        Storage::put(self::MAINTENANCE_FILE, json_encode($current, JSON_PRETTY_PRINT));

        return redirect()->route('admin.settings')->with('success', 'Maintenance message updated successfully.');
    }
}
