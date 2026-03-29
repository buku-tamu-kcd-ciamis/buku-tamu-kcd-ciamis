<?php

namespace App\Http\Controllers;

use App\Exports\PegawaiExport;
use App\Models\PengaturanKcd;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserManagementController extends Controller
{
    public function exportPegawaiExcel(): StreamedResponse
    {
        if (Auth::check()) {
            activity('cetak')
                ->causedBy(Auth::user())
                ->withProperties([
                    'tipe' => 'export_excel_pegawai',
                ])
                ->log('Export data pegawai ke Excel');
        }

        return (new PegawaiExport())->download();
    }

    public function printBulk(Request $request)
    {
        $selectedIds = collect(explode(',', (string) $request->query('ids', '')))
            ->map(fn (string $id): int => (int) trim($id))
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->values();

        $query = User::query()
            ->with('role_user')
            ->where(function (Builder $userQuery): void {
                $userQuery->whereDoesntHave('role_user', function (Builder $roleQuery): void {
                    $roleQuery->where('name', 'Super Admin');
                })
                ->orWhereNull('role_user_id');
            });

        if ($selectedIds->isNotEmpty()) {
            $query->whereIn('id', $selectedIds->all());
        }

        $users = $query
            ->orderBy('name')
            ->get();

        $kepalaCabdin = PengaturanKcd::getSettings();

        if (Auth::check()) {
            activity('cetak')
                ->causedBy(Auth::user())
                ->withProperties([
                    'tipe' => 'user_bulk',
                    'jumlah' => $users->count(),
                    'ids' => $selectedIds->all(),
                ])
                ->log('Mencetak data user (' . $users->count() . ' data)');
        }

        return view('print.users-bulk', compact('users', 'kepalaCabdin'));
    }
}
