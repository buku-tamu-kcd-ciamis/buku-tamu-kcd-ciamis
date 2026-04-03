<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DropdownOptionService;

class DropdownOptionController extends Controller
{
    public function __construct(
        private readonly DropdownOptionService $dropdownOptionService,
    ) {
    }

    public function index(string $category)
    {
        $options = $this->dropdownOptionService->getByCategory($category);

        if ($options === null) {
            return response()->json(['error' => 'Invalid category'], 404);
        }

        return response()->json(
            $options,
            200,
            ['Cache-Control' => 'public, max-age=300, stale-while-revalidate=60']
        );
    }
}
