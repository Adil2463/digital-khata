<?php

declare(strict_types=1);

namespace App\Modules\Dashboard\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Shared\Http\Responders\PageResponder;
use Inertia\Response;

final class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        return PageResponder::render('modules/dashboard/pages/Index');
    }
}
