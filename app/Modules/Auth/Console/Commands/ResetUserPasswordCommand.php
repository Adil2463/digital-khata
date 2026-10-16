<?php

declare(strict_types=1);

namespace App\Modules\Auth\Console\Commands;

use App\Models\User;
use App\Modules\Shared\Auth\CredentialRotator;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

use function Laravel\Prompts\password;

final class ResetUserPasswordCommand extends Command
{
    protected $signature = 'khata:user:reset-password
        {email : Login email of the account to recover}';

    protected $description = 'Set a new password for an account without email delivery. Revokes its sessions and API tokens.';

    public function __construct(
        private readonly CredentialRotator $credentialRotator,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        if (! $this->input->isInteractive()) {
            $this->components->error('Run khata:user:reset-password interactively: passwords are only accepted from a hidden prompt.');

            return self::FAILURE;
        }

        $email = Str::lower(mb_trim($this->input()->string('email')->toString()));
        $user = User::query()->where('email', $email)->first();

        if (! $user instanceof User) {
            $this->components->error('No account uses that login email. Nothing was changed.');

            return self::FAILURE;
        }

        $input = [
            'password' => password('New password'),
            'password_confirmation' => password('Confirm new password'),
        ];

        $validator = Validator::make($input, [
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->components->error($message);
            }

            $this->components->warn('Nothing was changed.');

            return self::FAILURE;
        }

        $this->credentialRotator->replacePassword($user, $input['password']);
        event(new PasswordReset($user));

        Log::channel('audit')->info('audit.password_reset', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'channel' => 'console',
        ]);

        $this->components->info(sprintf('Password updated for %s. Existing sessions and API tokens were revoked.', $user->email));

        return self::SUCCESS;
    }
}
