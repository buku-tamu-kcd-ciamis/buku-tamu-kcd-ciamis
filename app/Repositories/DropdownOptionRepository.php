<?php

namespace App\Repositories;

use App\Models\DropdownOption;
use App\Repositories\Contracts\DropdownOptionRepositoryInterface;

class DropdownOptionRepository implements DropdownOptionRepositoryInterface
{
    public function isValidCategory(string $category): bool
    {
        return array_key_exists($category, DropdownOption::CATEGORY_LABELS);
    }

    public function getFullOptions(string $category): array
    {
        return DropdownOption::getFullOptions($category);
    }
}
