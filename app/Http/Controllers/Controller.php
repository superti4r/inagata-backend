<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Responses\APIResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller
{
    use APIResponse;
    use AuthorizesRequests;
}
