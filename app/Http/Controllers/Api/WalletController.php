<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\WalletResource;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function __invoke(Request $request): WalletResource
    {
        return new WalletResource($request->user()->wallet()->firstOrFail());
    }
}
