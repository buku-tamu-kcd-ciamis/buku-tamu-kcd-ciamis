<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface GuestLookupRepositoryInterface
{
    /**
     * @param array{nik?: string, nama_lengkap?: string, nomor_hp?: string, email?: string} $criteria
     */
    public function findCandidates(array $criteria, int $limit = 40): Collection;
}
