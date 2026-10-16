<?php

declare(strict_types=1);

namespace App\Modules\Shop\Console\Commands;

use App\Enums\ShopTimezone;
use App\Models\User;
use App\Modules\Shared\Auth\DemoAccounts;
use App\Modules\Shop\Commands\ShopCommands;
use App\Modules\Shop\Data\InstallShopData;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Password;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

final class InstallShopCommand extends Command
{
    protected $signature = 'khata:install
        {--shop-name= : Shop name printed on receipts}
        {--timezone='.ShopTimezone::DEFAULT->value.' : Shop timezone used for business dates (V1 supports Asia/Karachi only)}
        {--owner-name= : Owner display name}
        {--owner-email= : Owner login (email format; no email is ever sent)}';

    protected $description = 'Create the shop and its verified owner account. Runs once per installation.';

    public function __construct(
        private readonly ShopCommands $shopCommands,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        if (ShopCommands::isInstalled()) {
            $this->components->error('Digital Khata is already installed. Nothing was changed.');

            return self::FAILURE;
        }

        if (! $this->input->isInteractive()) {
            $this->components->error('Run khata:install interactively: the owner password is only accepted from a hidden prompt.');

            return self::FAILURE;
        }

        $input = [
            'shop_name' => mb_trim($this->optionOrAsk('shop-name', 'Shop name')),
            'timezone' => mb_trim($this->input()->string('timezone')->toString()),
            'owner_name' => mb_trim($this->optionOrAsk('owner-name', 'Owner name')),
            'owner_email' => Str::lower(mb_trim($this->optionOrAsk('owner-email', 'Owner login email'))),
            'owner_password' => password('Owner password'),
            'owner_password_confirmation' => password('Confirm owner password'),
        ];

        $validator = Validator::make($input, [
            'shop_name' => ['required', 'string', 'max:120'],
            'timezone' => ['required', 'string', new Enum(ShopTimezone::class)],
            'owner_name' => ['required', 'string', 'max:255'],
            'owner_email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class.',email'],
            'owner_password' => ['required', 'string', 'confirmed', Password::defaults()],
        ], [
            'timezone' => 'The timezone must be one of: '.implode(', ', array_column(ShopTimezone::cases(), 'value')).'.',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->components->error($message);
            }

            $this->components->warn('Nothing was changed.');

            return self::FAILURE;
        }

        $demoAccounts = DemoAccounts::usersWithPublishedPasswords();

        if ($demoAccounts !== []) {
            $this->components->warn("These accounts still use the starter kit's published demo passwords:");
            $this->components->bulletList(array_map(static fn (User $user): string => $user->email, $demoAccounts));

            if (! confirm('Lock them? Each gets a random unknown password and loses its sessions and API tokens; no data is deleted.', default: true)) {
                $this->components->warn('Installation cancelled. Nothing was changed.');

                return self::FAILURE;
            }
        }

        $shopInstallationResult = $this->shopCommands->install(
            new InstallShopData(
                shopName: $input['shop_name'],
                timezone: ShopTimezone::from($input['timezone']),
                ownerName: $input['owner_name'],
                ownerEmail: $input['owner_email'],
                ownerPassword: $input['owner_password'],
            ),
            $demoAccounts,
        );

        $this->components->info('Digital Khata is installed.');
        $this->components->twoColumnDetail('Shop', $shopInstallationResult->shop->name);
        $this->components->twoColumnDetail('Timezone', $shopInstallationResult->shop->timezone->value);
        $this->components->twoColumnDetail('Owner login', $shopInstallationResult->owner->email);
        $this->components->twoColumnDetail('Locked demo accounts', (string) count($shopInstallationResult->lockedAccountEmails));
        $this->components->twoColumnDetail('Existing accounts attached to the shop', (string) $shopInstallationResult->attachedUserCount);

        return self::SUCCESS;
    }

    private function optionOrAsk(string $option, string $label): string
    {
        $value = $this->input()->string($option)->toString();

        return $value !== '' ? $value : text($label, required: true);
    }
}
