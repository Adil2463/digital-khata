<?php

declare(strict_types=1);

namespace App\Modules\Dashboard\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Dashboard\Data\DashboardPageData;
use App\Modules\Shared\Auth\RequestActor;
use App\Modules\Shared\Http\Responders\PageResponder;
use App\Modules\Shared\Shop\CurrentShop;
use Illuminate\Http\Request;
use Inertia\Response;

final class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        return PageResponder::render(
            'modules/dashboard/pages/Index',
            DashboardPageData::fromShop(CurrentShop::of(RequestActor::from($request))),
        );
    }
}
