<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\ShopFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string|null $phone
 * @property string|null $address
 * @property string $timezone
 * @property string|null $receipt_footer
 * @property CarbonInterface|null $created_at
 * @property CarbonInterface|null $updated_at
 */
final class Shop extends Model
{
    /** @use HasFactory<ShopFactory> */
    use HasFactory;

    public const string DEFAULT_TIMEZONE = 'Asia/Karachi';

    protected $guarded = [];

    /**
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
