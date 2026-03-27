<?php

namespace Tests\Feature;

use App\Filament\Staff\Pages\KetersediaanStatus;
use App\Filament\Staff\Resources\PegawaiIzinResource\Pages\CreatePegawaiIzin;
use App\Models\BukuTamu;
use App\Models\Pegawai;
use App\Models\RoleUser;
use App\Models\StaffNotification;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class StaffCriticalFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_routes_are_forbidden_when_permissions_are_missing(): void
    {
        $user = $this->makeStaffUser();

        $routes = [
            '/staff',
            '/staff/notifikasi-tamu',
            '/staff/pegawai-izins',
            '/staff/direktori-pegawai',
            '/staff/riwayat-kunjungan',
            '/staff/ketersediaan-status',
            '/staff/download-dokumen',
            '/staff/faq',
        ];

        foreach ($routes as $route) {
            $this->actingAs($user)->get($route)->assertForbidden();
        }
    }

    public function test_izin_identity_is_overridden_server_side_from_authenticated_user(): void
    {
        $user = $this->makeStaffUser([
            'pegawai_izin' => true,
        ]);

        $this->actingAs($user);

        $component = app(CreatePegawaiIzin::class);
        $reflection = new \ReflectionMethod($component, 'mutateFormDataBeforeCreate');
        $reflection->setAccessible(true);

        $payload = [
            'nama_pegawai' => 'Hacker Name',
            'nip' => '000000000000000000',
            'jabatan' => 'Injected Jabatan',
            'unit_kerja' => 'Injected Unit',
            'nomor_hp' => '081111111111',
            'jenis_izin' => 'sakit',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDay()->toDateString(),
        ];

        $result = $reflection->invoke($component, $payload);

        $this->assertSame($user->pegawai->nama, $result['nama_pegawai']);
        $this->assertSame($user->pegawai->nip, $result['nip']);
        $this->assertSame($user->pegawai->jabatan, $result['jabatan']);
        $this->assertSame($user->pegawai->unit_kerja, $result['unit_kerja']);
        $this->assertSame($user->pegawai->nomor_hp, $result['nomor_hp']);
        $this->assertSame('menunggu', $result['status']);
    }

    public function test_reject_notification_syncs_buku_tamu_status_to_ditolak(): void
    {
        $user = $this->makeStaffUser([
            'buku_tamu' => true,
        ]);

        $visit = BukuTamu::create([
            'jenis_id' => 'ktp',
            'nik' => '3201010101010101',
            'nama_lengkap' => 'Tamu Uji',
            'instansi' => 'Instansi Uji',
            'nomor_hp' => '081234567890',
            'jabatan' => 'Pemohon',
            'kabupaten_kota' => 'Bandung',
            'staff_dituju' => $user->pegawai->nama,
            'email' => 'tamu@example.com',
            'keperluan' => 'Pengujian',
            'foto_selfie' => 'data:image/png;base64,AA==',
            'tanda_tangan' => 'data:image/png;base64,AA==',
            'status' => BukuTamu::STATUS_MENUNGGU,
        ]);

        $notification = StaffNotification::create([
            'user_id' => $user->id,
            'buku_tamu_id' => $visit->id,
            'type' => 'tamu_baru',
            'message' => 'Ada tamu baru',
            'is_read' => false,
        ]);

        $notification->respondAndSyncVisitStatus(StaffNotification::RESPONSE_DITOLAK);

        $this->assertDatabaseHas('staff_notifications', [
            'id' => $notification->id,
            'response' => StaffNotification::RESPONSE_DITOLAK,
        ]);

        $this->assertDatabaseHas('buku_tamu', [
            'id' => $visit->id,
            'status' => BukuTamu::STATUS_DITOLAK,
        ]);
    }

    public function test_ketersediaan_status_action_rejects_invalid_value_and_accepts_valid_value(): void
    {
        $user = $this->makeStaffUser([
            'data_pegawai' => true,
        ]);

        Livewire::actingAs($user)
            ->test(KetersediaanStatus::class)
            ->call('updateStatus', 'invalid_value');

        $this->assertDatabaseHas('pegawai', [
            'id' => $user->pegawai->id,
            'availability_status' => Pegawai::AVAILABILITY_AVAILABLE,
        ]);

        Livewire::actingAs($user)
            ->test(KetersediaanStatus::class)
            ->call('updateStatus', Pegawai::AVAILABILITY_BUSY);

        $this->assertDatabaseHas('pegawai', [
            'id' => $user->pegawai->id,
            'availability_status' => Pegawai::AVAILABILITY_BUSY,
        ]);
    }

    public function test_database_whitelist_blocks_invalid_availability_status_on_supported_drivers(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            $this->markTestSkipped('Database-level availability constraint is not enforced on sqlite in this project.');
        }

        $this->expectException(QueryException::class);

        DB::table('pegawai')->insert([
            'nama' => 'Pegawai Invalid',
            'nip' => '199001012026031001',
            'jabatan' => 'Staf',
            'nomor_hp' => '081200000000',
            'unit_kerja' => 'TU',
            'is_active' => true,
            'availability_status' => 'not_allowed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * @param array<string, bool> $permissionOverrides
     */
    private function makeStaffUser(array $permissionOverrides = []): User
    {
        $permissions = array_merge(RoleUser::getDefaultPermissions(), $permissionOverrides);

        $role = RoleUser::create([
            'name' => 'Staff',
            'need_approval' => false,
            'author_id' => null,
            'permissions' => $permissions,
        ]);

        $pegawai = Pegawai::create([
            'nama' => 'Staff Penguji',
            'nip' => '199001012026031000',
            'jabatan' => 'Analis',
            'nomor_hp' => '081234567899',
            'unit_kerja' => 'Bidang Pengujian',
            'is_active' => true,
            'availability_status' => Pegawai::AVAILABILITY_AVAILABLE,
        ]);

        return User::factory()->create([
            'name' => $pegawai->nama,
            'role_user_id' => $role->id,
            'pegawai_id' => $pegawai->id,
        ]);
    }
}
