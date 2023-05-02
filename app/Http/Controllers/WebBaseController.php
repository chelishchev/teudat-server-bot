<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

abstract class WebBaseController extends BaseController
{
    use DispatchesJobs, ValidatesRequests;

    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }
}
