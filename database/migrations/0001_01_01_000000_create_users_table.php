<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\User;
use App\Modules\Users\Data\CreateUserData;
use Carbon\CarbonImmutable;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public static function seedUsers(): void
    {
        if (config('database.seed_users') !== true) {
            return;
        }

        $usersDataList = [
            new CreateUserData(
                name: 'Admin',
                email: 'admin@app.com',
                role: UserRole::Admin,
                password: 'Admin123!@#',
            ),
            new CreateUserData(
                name: 'User',
                email: 'user@app.com',
                role: UserRole::User,
                password: 'User123!@#',
            ),
        ];

        foreach ($usersDataList as $userDataList) {
            User::updateOrCreate(
                ['email' => $userDataList->email],
                [
                    ...$userDataList->transform(),
                    'email_verified_at' => $userDataList->role === UserRole::Admin ? CarbonImmutable::now() : null,
                ]
            );
        }
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->string('name');
            $blueprint->string('email')->unique();
            $blueprint->string('role')->default(UserRole::User->value);
            $blueprint->timestamp('email_verified_at')->nullable();
            $blueprint->string('password');
            $blueprint->rememberToken();
            $blueprint->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $blueprint): void {
            $blueprint->string('email')->primary();
            $blueprint->string('token');
            $blueprint->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $blueprint): void {
            $blueprint->string('id')->primary();
            $blueprint->foreignId('user_id')->nullable()->index();
            $blueprint->string('ip_address', 45)->nullable();
            $blueprint->text('user_agent')->nullable();
            $blueprint->longText('payload');
            $blueprint->integer('last_activity')->index();
        });

        Schema::create('personal_access_tokens', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->morphs('tokenable');
            $blueprint->string('name');
            $blueprint->string('token', 64)->unique();
            $blueprint->text('abilities')->nullable();
            $blueprint->timestamp('last_used_at')->nullable();
            $blueprint->timestamp('expires_at')->nullable();
            $blueprint->timestamps();
        });

        self::seedUsers();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('personal_access_tokens');
    }
};
