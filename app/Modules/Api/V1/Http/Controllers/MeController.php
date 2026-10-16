<?php

declare(strict_types=1);

namespace App\Modules\Api\V1\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Api\V1\Http\Resources\UserResource;
use App\Modules\Shared\Auth\RequestActor;
use App\Modules\Shared\Http\Responders\ApiResponder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class MeController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        return ApiResponder::resource(UserResource::make(RequestActor::from($request)));
    }
}
