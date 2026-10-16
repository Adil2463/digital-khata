<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Gate;

Gate::define('manage-users', static fn (User $user): bool => $user->isOwner());
