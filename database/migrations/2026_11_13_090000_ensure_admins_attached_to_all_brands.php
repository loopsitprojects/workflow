<?php

use App\Models\Brand;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $adminIds = User::whereIn('role', ['Admin', 'Owner'])->pluck('id')->toArray();
        if (empty($adminIds)) {
            return;
        }

        $brands = Brand::all();
        foreach ($brands as $brand) {
            $brand->members()->syncWithoutDetaching($adminIds);
            $brand->update(['total_members' => $brand->members()->count()]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op to preserve assignments
    }
};
