<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TransactionController extends Controller
{
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $transactions = $request->user()
            ->transactions()
            ->latest()
            ->paginate($request->integer('per_page', 20));

        return TransactionResource::collection($transactions);
    }
}
