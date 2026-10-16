<?php

declare(strict_types=1);

namespace App\Modules\Users\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Shared\Auth\RequestActor;
use App\Modules\Shared\Http\Responders\PageResponder;
use App\Modules\Users\Data\UsersIndexPageData;
use App\Modules\Users\Handlers\UserCommandHandler;
use App\Modules\Users\Handlers\UserQueryHandler;
use App\Modules\Users\Http\Requests\UserCreateRequest;
use App\Modules\Users\Http\Requests\UserDeactivateRequest;
use App\Modules\Users\Http\Requests\UserIndexRequest;
use App\Modules\Users\Http\Requests\UserReactivateRequest;
use App\Modules\Users\Http\Requests\UserUpdateRequest;
use App\Modules\Users\Support\UserActionContext;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;

final class UserController extends Controller
{
    public function __construct(
        private readonly UserQueryHandler $userQueryHandler,
        private readonly UserCommandHandler $userCommandHandler,
    ) {}

    public function index(UserIndexRequest $userIndexRequest): Response
    {
        $lengthAwarePaginator = $this->userQueryHandler
            ->index($userIndexRequest->toDto(), RequestActor::from($userIndexRequest))
            ->withQueryString();

        return PageResponder::render(
            'modules/users/pages/Index',
            UsersIndexPageData::fromPaginator($lengthAwarePaginator),
        );
    }

    public function store(UserCreateRequest $userCreateRequest): RedirectResponse
    {
        $userCommandResult = $this->userCommandHandler->create(
            $userCreateRequest->toDto(),
            UserActionContext::fromRequest($userCreateRequest),
        );

        return redirect()->route('app.admin.users.index')
            ->with('message', sprintf('%s can now log in as %s.', $userCommandResult->user->name, $userCommandResult->user->email));
    }

    public function update(UserUpdateRequest $userUpdateRequest, User $user): RedirectResponse
    {
        $this->userCommandHandler->update(
            $user,
            $userUpdateRequest->toDto(),
            UserActionContext::fromRequest($userUpdateRequest),
        );

        return redirect()->route('app.admin.users.index')
            ->with('message', sprintf('%s was updated.', $user->name));
    }

    public function deactivate(UserDeactivateRequest $userDeactivateRequest, User $user): RedirectResponse
    {
        $this->userCommandHandler->deactivate($user, UserActionContext::fromRequest($userDeactivateRequest));

        return redirect()->route('app.admin.users.index')
            ->with('message', sprintf('%s was deactivated and signed out everywhere.', $user->name));
    }

    public function reactivate(UserReactivateRequest $userReactivateRequest, User $user): RedirectResponse
    {
        $this->userCommandHandler->reactivate($user, UserActionContext::fromRequest($userReactivateRequest));

        return redirect()->route('app.admin.users.index')
            ->with('message', sprintf('%s can log in again.', $user->name));
    }
}
