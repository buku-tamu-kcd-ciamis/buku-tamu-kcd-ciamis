<?php

namespace App\Services;

use App\Repositories\Contracts\DropdownOptionRepositoryInterface;

class DropdownOptionService
{
    public function __construct(
        private readonly DropdownOptionRepositoryInterface $dropdownOptionRepository,
    ) {
    }

    public function getByCategory(string $category): ?array
    {
        if (!$this->dropdownOptionRepository->isValidCategory($category)) {
            return null;
        }

        return $this->dropdownOptionRepository->getFullOptions($category);
    }
}
