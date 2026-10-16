<?php

declare(strict_types=1);

use App\Models\Shop;
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
        Schema::create('shops', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->string('name', 120);
            $blueprint->string('phone', 20)->nullable();
            $blueprint->string('address')->nullable();
            $blueprint->string('timezone', 64)->default(Shop::DEFAULT_TIMEZONE);
            $blueprint->string('receipt_footer')->nullable();
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shops');
    }
};
