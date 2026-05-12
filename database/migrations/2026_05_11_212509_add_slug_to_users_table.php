<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('name');
        });

        // Populate slugs for existing partners
        $partners = \App\Models\User::where('type', 'partner')->get();
        foreach ($partners as $partner) {
            $baseSlug = \Illuminate\Support\Str::slug($partner->business_name ?: $partner->name);
            $slug = $baseSlug;
            $count = 1;
            
            while (\App\Models\User::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $count++;
            }
            
            $partner->update(['slug' => $slug]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
