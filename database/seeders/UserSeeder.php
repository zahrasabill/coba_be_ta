<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // data admin dari .env
        $adminUsername = env('ADMIN_USERNAME');
        $adminPassword = env('ADMIN_PASSWORD');
        $adminEmail = env('ADMIN_EMAIL');
        $adminKodeAkses = env('ADMIN_KODE_AKSES');
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        // data dokter
        $dokterUsername = env('DOKTER_USERNAME');
        $dokterPassword = env('DOKTER_PASSWORD');
        $dokterEmail = env('DOKTER_EMAIL');
        $dokterKodeAkses = env('DOKTER_KODE_AKSES');
        $dokterRole = Role::firstOrCreate(['name' => 'dokter']);

        // data dokter
        $pasienUsername = env('PASIEN_USERNAME');
        $pasienPassword = env('PASIEN_PASSWORD');
        $pasienEmail = env('PASIEN_EMAIL');
        $pasienKodeAkses = env('PASIEN_KODE_AKSES');
        $pasienRole = Role::firstOrCreate(['name' => 'pasien']);

        //create admin
        $adminUser = User::firstOrCreate(
            ['email' => $adminEmail],
            [
                'kode_akses' => $adminKodeAkses,
                'name' => $adminUsername,
                'email' => $adminEmail,
                'tanggal_lahir' => '2000-01-01',
                'gender' => 'laki-laki',
                'no_telp' => '081234567890',
                'usia' => 25,
                'password' => bcrypt($adminPassword),
            ]
        );

        // create dokter
        $dokterUser = User::firstOrCreate(
            ['email' => $dokterEmail],
            [
                'kode_akses' => $dokterKodeAkses,
                'name' => $dokterUsername,
                'email' => $dokterEmail,
                'tanggal_lahir' => '2000-02-02',
                'gender' => 'laki-laki',
                'no_telp' => '081234567890',
                'usia' => 25,
                'password' => bcrypt($dokterPassword),
            ]
        );

        // create pasien
        $pasienUser = User::firstOrCreate(
            ['email' => $pasienEmail],
            [
                'kode_akses' => $pasienKodeAkses,
                'name' => $pasienUsername,
                'email' => $pasienEmail,
                'tanggal_lahir' => '2000-03-03',
                'gender' => 'laki-laki',
                'no_telp' => '081234567890',
                'usia' => 25,
                'password' => bcrypt($pasienPassword),
            ]
        );

        // Assign role 'admin' ke user
        if (!$adminUser->hasRole('admin')) {
            $adminUser->assignRole($adminRole);
        }
        if (!$dokterUser->hasRole('dokter')) {
            $dokterUser->assignRole($dokterRole);
        }
        if (!$pasienUser->hasRole('pasien')) {
            $pasienUser->assignRole($pasienRole);
        }

        $this->command->info('Admin, Dokter, Pasien berhasil dibuat dan diberi role admin!');
    }
}