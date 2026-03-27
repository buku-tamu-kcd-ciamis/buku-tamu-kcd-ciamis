<?php

namespace Tests\Feature;

use App\Models\BookingChat;
use App\Models\BukuTamu;
use App\Models\Pegawai;
use App\Models\RoleUser;
use App\Models\User;
use App\Services\BookingChatManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingChatFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_chat_is_bootstrapped_for_targeted_staff(): void
    {
        $staff = $this->makeUserWithRole('Staff', [
            'riwayat_tamu' => true,
            'buku_tamu' => true,
        ], 'Staff Target');

        $booking = $this->createBookingForStaff($staff->pegawai->nama);

        $threads = app(BookingChatManager::class)->bootstrapForBooking($booking);

        $this->assertCount(1, $threads);

        $this->assertDatabaseHas('booking_chats', [
            'buku_tamu_id' => $booking->id,
            'staff_user_id' => $staff->id,
        ]);

        $chat = BookingChat::query()->where('buku_tamu_id', $booking->id)->where('staff_user_id', $staff->id)->firstOrFail();

        $this->assertDatabaseHas('booking_chat_messages', [
            'booking_chat_id' => $chat->id,
            'is_system' => true,
        ]);
    }

    public function test_chat_message_flow_updates_unread_and_assigns_piket_sender(): void
    {
        $staff = $this->makeUserWithRole('Staff', [
            'riwayat_tamu' => true,
            'buku_tamu' => true,
        ], 'Staff Sender');

        $piket = $this->makeUserWithRole('Piket', [
            'buku_tamu' => true,
        ], 'Piket Receiver');

        $booking = $this->createBookingForStaff($staff->pegawai->nama);

        $chat = app(BookingChatManager::class)
            ->getOrCreateForBookingAndStaff($booking, $staff);

        app(BookingChatManager::class)->sendMessage($chat, $staff, 'Halo piket, tamu sudah di lobby.');

        $this->assertSame(1, $chat->fresh()->unreadCountFor($piket));

        $chat->markMessagesAsReadFor($piket);

        $this->assertSame(0, $chat->fresh()->unreadCountFor($piket));

        app(BookingChatManager::class)->sendMessage($chat->fresh(), $piket, 'Noted, saya arahkan ke ruang tunggu.');

        $this->assertSame($piket->id, $chat->fresh()->piket_user_id);
    }

    public function test_staff_chat_page_is_forbidden_without_permission(): void
    {
        $staff = $this->makeUserWithRole('Staff', [], 'Staff Tanpa Akses');

        $this->actingAs($staff)
            ->get('/staff/chat-booking')
            ->assertForbidden();
    }

    public function test_chat_pages_are_accessible_with_valid_permissions(): void
    {
        $staff = $this->makeUserWithRole('Staff', [
            'riwayat_tamu' => true,
        ], 'Staff Dengan Akses');

        $piket = $this->makeUserWithRole('Piket', [
            'buku_tamu' => true,
        ], 'Piket Dengan Akses');

        $this->actingAs($staff)
            ->get('/staff/chat-booking')
            ->assertOk();

        $this->actingAs($piket)
            ->get('/piket/chat-booking')
            ->assertOk();
    }

    /**
     * @param array<string, bool> $permissionOverrides
     */
    private function makeUserWithRole(string $roleName, array $permissionOverrides, string $pegawaiName): User
    {
        $permissions = array_merge(RoleUser::getDefaultPermissions(), $permissionOverrides);

        $role = RoleUser::create([
            'name' => $roleName,
            'need_approval' => false,
            'author_id' => null,
            'permissions' => $permissions,
        ]);

        $pegawai = Pegawai::create([
            'nama' => $pegawaiName,
            'nip' => fake()->unique()->numerify('##################'),
            'jabatan' => 'Staf',
            'nomor_hp' => '0812' . fake()->numerify('########'),
            'unit_kerja' => 'Bidang Layanan',
            'is_active' => true,
            'availability_status' => Pegawai::AVAILABILITY_AVAILABLE,
        ]);

        return User::factory()->create([
            'name' => $pegawai->nama,
            'role_user_id' => $role->id,
            'pegawai_id' => $pegawai->id,
        ]);
    }

    private function createBookingForStaff(string $staffName): BukuTamu
    {
        return BukuTamu::create([
            'jenis_id' => 'ktp',
            'nik' => fake()->unique()->numerify('################'),
            'nama_lengkap' => fake()->name(),
            'instansi' => 'Instansi Test',
            'nomor_hp' => '0812' . fake()->numerify('########'),
            'jabatan' => 'Pemohon',
            'kabupaten_kota' => 'Bandung',
            'staff_dituju' => $staffName,
            'email' => 'guest@example.test',
            'keperluan' => 'Koordinasi booking',
            'foto_selfie' => 'data:image/png;base64,AA==',
            'tanda_tangan' => 'data:image/png;base64,AA==',
            'status' => BukuTamu::STATUS_MENUNGGU,
        ]);
    }
}
