<?php

namespace App\Http\Controllers;

use App\Services\RwandaAddress;
use Illuminate\Http\JsonResponse;

class LocationController extends Controller
{
    public function __invoke(RwandaAddress $address): JsonResponse
    {
        return response()->json($address->tree())->header('Cache-Control', 'private, max-age=86400');
    }
}
