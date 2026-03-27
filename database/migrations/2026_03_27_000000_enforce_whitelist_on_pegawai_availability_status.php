<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();
        $allowed = "'available','busy','out_of_office'";

        DB::statement("UPDATE pegawai SET availability_status = 'available' WHERE availability_status IS NULL OR availability_status NOT IN ({$allowed})");

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE pegawai MODIFY COLUMN availability_status ENUM({$allowed}) NOT NULL DEFAULT 'available'");

            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE pegawai DROP CONSTRAINT IF EXISTS pegawai_availability_status_check');
            DB::statement("ALTER TABLE pegawai ADD CONSTRAINT pegawai_availability_status_check CHECK (availability_status IN ({$allowed}))");

            return;
        }

        if ($driver === 'sqlsrv') {
            DB::statement("IF EXISTS (SELECT 1 FROM sys.check_constraints WHERE name = 'CK_pegawai_availability_status') ALTER TABLE pegawai DROP CONSTRAINT CK_pegawai_availability_status");
            DB::statement("ALTER TABLE pegawai WITH CHECK ADD CONSTRAINT CK_pegawai_availability_status CHECK (availability_status IN ({$allowed}))");
        }
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE pegawai MODIFY COLUMN availability_status VARCHAR(20) NOT NULL DEFAULT 'available'");

            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE pegawai DROP CONSTRAINT IF EXISTS pegawai_availability_status_check');

            return;
        }

        if ($driver === 'sqlsrv') {
            DB::statement("IF EXISTS (SELECT 1 FROM sys.check_constraints WHERE name = 'CK_pegawai_availability_status') ALTER TABLE pegawai DROP CONSTRAINT CK_pegawai_availability_status");
        }
    }
};
