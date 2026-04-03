<?php

namespace App\Repositories\Contracts;

interface DropdownOptionRepositoryInterface
{
    public function isValidCategory(string $category): bool;

    public function getFullOptions(string $category): array;
}
