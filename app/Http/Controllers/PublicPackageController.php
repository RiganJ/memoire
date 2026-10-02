<?php

namespace App\Http\Controllers;

use App\Models\ServicePackage;
use Illuminate\Http\JsonResponse;

class PublicPackageController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            ServicePackage::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(['id', 'name', 'price', 'description', 'features', 'badge'])
        );
    }
}
