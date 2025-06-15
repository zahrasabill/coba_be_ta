<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Spatie\Permission\Models\Role;

/**
 * @OA\Tag(
 *     name="User",
 *     description="User management endpoints",
 *     x={"order": 2}
 * )
 */

class UserController extends Controller
{
    /**
     * @OA\Post(
     *     path="/v1/register/pasien",
     *     summary="Register a new pasien",
     *     tags={"User"},
     *     @OA\RequestBody(
     *         required=true,
     *     @OA\JsonContent(
     *         required={"name", "tanggal_lahir", "gender", "no_telp"},
     *         
     *         @OA\Property(
     *             property="name",
     *             type="string",
     *             example="John Doe"
     *         ),
     *         
     *         @OA\Property(
     *             property="tanggal_lahir",
     *             type="string",
     *             format="date",
     *             example="2012-12-31"
     *         ),
     *         
     *         @OA\Property(
     *             property="gender",
     *             type="string",
     *             description="Jenis kelamin, hanya diisi 'laki-laki' atau 'perempuan'",
     *             example="laki-laki"
     *         ),
     *         
     *         @OA\Property(
     *             property="no_telp",
     *             type="string",
     *             description="Nomor telepon, hanya angka, 10 sampai 15 digit",
     *             example="081234567890"
     *         )
     *     )
     * ),
     *     @OA\Response(response=201, description="User registered successfully"),
     *     @OA\Response(
     *       response=400,
     *        description="Validation error",
     *        @OA\JsonContent(
     *            type="object",
     *            @OA\Property(property="message", type="string", example="The given data was invalid."),
     *            @OA\Property(
     *                property="errors",
     *                type="object",
     *                @OA\Property(
     *                    property="name",
     *                    type="array",
     *                    @OA\Items(type="string", example="Nama sudah terdaftar, silakan gunakan nama lain.")
     *                )
     *            )
     *        )
     *    )
     * )
     */
    public function registerPasien(Request $request)
    {
        try {
            // Validasi input
            $request->validate([
                'name' => 'required|string|max:255|unique:users,name',
                'tanggal_lahir' => 'required|date|before:today',
                'gender' => 'required|in:laki-laki,perempuan',
                'no_telp' => 'required|digits_between:10,15|regex:/^[0-9]+$/',
            ], [
                'name.unique' => 'Nama sudah terdaftar, silakan gunakan nama lain.',
                'tanggal_lahir.before' => 'Tanggal lahir tidak boleh melebihi hari ini.',
                'tanggal_lahir.date' => 'Format tanggal lahir tidak valid. Gunakan format YYYY-MM-DD.',
                'gender.in' => 'Jenis kelamin harus salah satu dari: laki-laki atau perempuan.',
                'no_telp.digits_between' => 'Nomor telepon harus 10-15 digit.',
                'no_telp.regex' => 'Nomor telepon harus berupa angka.',
            ]);

            // Hitung usia berdasarkan tanggal lahir
            $tanggal_lahir = Carbon::parse($request->tanggal_lahir);
            $usia = $tanggal_lahir->age; // Menghitung usia

            // Generate password otomatis (contoh: 8 karakter acak)
            $password = Str::random(8);

            // Generate kode_akses unik (contoh: ERS-X7Y9Z)
            $kode_akses = 'PRS-' . Str::upper(Str::random(5));

            // Simpan data pengguna ke database
            $user = User::create([
                'kode_akses' => $kode_akses,
                'name' => $request->name,
                'tanggal_lahir' => $request->tanggal_lahir,
                'usia' => $usia,
                'no_telp' => $request->no_telp,
                'gender' => $request->gender,
                'email' => null, // Email tidak wajib jika tidak digunakan
                'password' => Hash::make($password), // Hash password sebelum disimpan
            ]);

            // Auto assign role 'user'
            $userRole = Role::findByName('pasien', 'api'); // Ambil role 'user' dari database
            $user->assignRole($userRole); // Tetapkan role 'user' ke pengguna

            // Kirim respons dengan informasi login
            return response()->json([
                'message' => 'User registered successfully',
                'data' => [
                    'kode_akses' => $kode_akses,
                    'password' => $password, // Kirim password plain sebagai respons
                ]
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Format respons error validasi
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 400);
        } catch (\Exception $e) {
            // Format respons error umum
            return response()->json([
                'message' => 'An unexpected error occurred',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * @OA\Post(
     *     path="/v1/register/dokter",
     *     summary="Register a new dokter",
     *     tags={"User"},
     *     @OA\RequestBody(
     *         required=true,
     *     @OA\JsonContent(
     *         required={"name", "tanggal_lahir", "gender", "no_telp"},
     *         
     *         @OA\Property(
     *             property="name",
     *             type="string",
     *             example="John Doe"
     *         ),
     *         
     *         @OA\Property(
     *             property="tanggal_lahir",
     *             type="string",
     *             format="date",
     *             example="2012-12-31"
     *         ),
     *         
     *         @OA\Property(
     *             property="gender",
     *             type="string",
     *             description="Jenis kelamin, hanya diisi 'laki-laki' atau 'perempuan'",
     *             example="laki-laki"
     *         ),
     *         
     *         @OA\Property(
     *             property="no_telp",
     *             type="string",
     *             description="Nomor telepon, hanya angka, 10 sampai 15 digit",
     *             example="081234567890"
     *         )
     *     )
     * ),
     *     @OA\Response(response=201, description="User registered successfully"),
     *     @OA\Response(
     *       response=400,
     *        description="Validation error",
     *        @OA\JsonContent(
     *            type="object",
     *            @OA\Property(property="message", type="string", example="The given data was invalid."),
     *            @OA\Property(
     *                property="errors",
     *                type="object",
     *                @OA\Property(
     *                    property="name",
     *                    type="array",
     *                    @OA\Items(type="string", example="Nama sudah terdaftar, silakan gunakan nama lain.")
     *                )
     *            )
     *        )
     *    )
     * )
     */
    public function registerDokter(Request $request)
    {
        try {
            // Validasi input
            $request->validate([
                'name' => 'required|string|max:255|unique:users,name',
                'tanggal_lahir' => 'required|date|before:today',
                'gender' => 'required|in:laki-laki,perempuan',
                'no_str' => 'nullable|string',
                'no_telp' => 'required|digits_between:10,15|regex:/^[0-9]+$/', // Nomor telepon harus 10-15 digit
            ], [
                'name.unique' => 'Nama sudah terdaftar, silakan gunakan nama lain.',
                'tanggal_lahir.before' => 'Tanggal lahir tidak boleh melebihi hari ini.',
                'tanggal_lahir.date' => 'Format tanggal lahir tidak valid. Gunakan format YYYY-MM-DD.',
                'gender.in' => 'Jenis kelamin harus salah satu dari: laki-laki atau perempuan.',
                'no_telp.digits_between' => 'Nomor telepon harus 10-15 digit.',
                'no_telp.regex' => 'Nomor telepon harus berupa angka.',
            ]);

            // Hitung usia berdasarkan tanggal lahir
            $tanggal_lahir = Carbon::parse($request->tanggal_lahir);
            $usia = $tanggal_lahir->age; // Menghitung usia

            // Generate password otomatis (contoh: 8 karakter acak)
            $password = Str::random(8);

            // Generate kode_akses unik (contoh: ERS-X7Y9Z)
            $kode_akses = 'DRS-' . Str::upper(Str::random(5));

            // Simpan data pengguna ke database
            $user = User::create([
                'kode_akses' => $kode_akses,
                'name' => $request->name,
                'tanggal_lahir' => $request->tanggal_lahir,
                'usia' => $usia,
                'no_telp' => $request->no_telp,
                'no_str' => $request->no_str,
                'gender' => $request->gender,
                'email' => null, // Email tidak wajib jika tidak digunakan
                'password' => Hash::make($password), // Hash password sebelum disimpan
            ]);

            // Auto assign role 'user'
            $userRole = Role::findByName('dokter', 'api'); // Ambil role 'user' dari database
            $user->assignRole($userRole); // Tetapkan role 'user' ke pengguna

            // Kirim respons dengan informasi login
            return response()->json([
                'message' => 'User registered successfully',
                'data' => [
                    'kode_akses' => $kode_akses,
                    'password' => $password, // Kirim password plain sebagai respons
                ]
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Format respons error validasi
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 400);
        } catch (\Exception $e) {
            // Format respons error umum
            return response()->json([
                'message' => 'An unexpected error occurred',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * @OA\Put(
     *     path="/v1/users/{id}",
     *     summary="Update User",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="John Doe Updated"),
     *             @OA\Property(property="email", type="string", format="email", example="new.email@example.com"),
     *             @OA\Property(property="no_telp", type="string", example="08122751"),
     *             @OA\Property(property="tanggal_lahir", type="string", format="date", example="1995-05-15"),
     *             @OA\Property(property="role", type="string", enum={"user", "dokter", "admin"}, example="dokter"),
     *             @OA\Property(property="password", type="string", minLength=8, maxLength=8, example="newpassword")
     *         )
     *     ),
     *     @OA\Response(response=200, description="User updated successfully"),
     *     @OA\Response(response=404, description="User not found")
     * )
     */

    public function update(Request $request, $id)
    {
        // Validasi input
        $request->validate([
            'name' => 'string|max:255|unique:users,name,' . $id, // Opsional
            'email' => 'nullable|email|unique:users,email,' . $id, // Email unik, kecuali untuk pengguna saat ini
            'no_telp' => 'numeric', // No KTP numerik dan tepat 16 digit
            'no_str' => 'nullable|string',
            'tanggal_lahir' => 'date|before:today', // Tanggal lahir opsional
            'role' => 'string|in:user,dokter,admin', // Role opsional
            'password' => 'string|min:8|max:8', // Password opsional, tetapi harus 8 karakter
        ], [
            'email.unique' => 'Email sudah terdaftar, silakan gunakan email lain.',
            'no_telp.numeric' => 'Nomor Telepon harus berupa angka.',
            'tanggal_lahir.before' => 'Tanggal lahir tidak boleh melebihi hari ini.',
            'tanggal_lahir.date' => 'Format tanggal lahir tidak valid. Gunakan format YYYY-MM-DD.',
            'role.in' => 'Role harus salah satu dari: user, dokter, admin.',
            'password.min' => 'Password harus tepat 8 karakter.',
            'password.max' => 'Password harus tepat 8 karakter.',
        ]);

        // Temukan pengguna berdasarkan ID
        $user = User::findOrFail($id);

        // Data untuk diperbarui
        $data = [];

        if ($request->has('name')) {
            $data['name'] = $request->name;
        }

        if ($request->has('email')) {
            $data['email'] = $request->email;
        }

        if ($request->has('no_telp')) {
            $data['no_telp'] = $request->no_telp;
        }
        if ($request->has('no_str')) {
            $data['no_str'] = $request->no_str;
        }

        if ($request->has('tanggal_lahir')) {
            $tanggal_lahir = Carbon::parse($request->tanggal_lahir);
            $data['tanggal_lahir'] = $request->tanggal_lahir;
            $data['usia'] = $tanggal_lahir->age; // Hitung usia baru
        }

        if ($request->has('password')) {
            $data['password'] = Hash::make($request->password); // Hash password baru
        }

        // Update data pengguna
        $user->update($data);

        // Update role jika ada
        if ($request->has('role')) {
            $roleName = $request->role;
            $role = Role::findByName($roleName, 'api'); // Ambil role dari database
            $user->syncRoles([$role]); // Tetapkan role baru
        }

        // Kirim respons berhasil
        return response()->json(['message' => 'User updated successfully']);
    }

    /**
     * @OA\Delete(
     *     path="/v1/users/{id}",
     *     summary="Delete User",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="User deleted successfully"),
     *     @OA\Response(response=404, description="User not found")
     * )
     */

    public function delete($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }

    /**
     * @OA\Get(
     *     path="/v1/me",
     *     summary="Get authenticated user",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Authenticated user data",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */

    public function me()
    {
        return response()->json(Auth::guard('api')->user());
    }

    /**
     * @OA\Get(
     *     path="/v1/pasien",
     *     summary="Get all pasien",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of all users",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="email", type="string"),
     *                 @OA\Property(property="roles", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function getAllPasien()
    {
        // Ambil semua pengguna beserta role mereka
        $users = User::withTrashed()
            ->role('pasien')
            ->with('roles') // Muat relasi roles
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'kode_akses' => $user->kode_akses,
                    'name' => $user->name,
                    'email' => $user->email,
                    'tanggal_lahir' => $user->tanggal_lahir,
                    'usia' => $user->usia,
                    'no_telp' => $user->no_telp,
                    'gender' => $user->gender,
                    'roles' => $user->roles->pluck('name'), // Ambil nama role
                    'deleted_at' => $user->deleted_at,
                ];
            });

        return response()->json($users);
    }

    /**
     * @OA\Get(
     *     path="/v1/dokter",
     *     summary="Get all dokter",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of all users",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="email", type="string"),
     *                 @OA\Property(property="roles", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function getAllDokter()
    {
        // Ambil semua pengguna beserta role mereka
        $users = User::withTrashed()
            ->role('dokter')
            ->with('roles') // Muat relasi roles
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'kode_akses' => $user->kode_akses,
                    'name' => $user->name,
                    'email' => $user->email,
                    'tanggal_lahir' => $user->tanggal_lahir,
                    'usia' => $user->usia,
                    'no_telp' => $user->no_telp,
                    'no_str' => $user->no_str,
                    'gender' => $user->gender,
                    'roles' => $user->roles->pluck('name'), // Ambil nama role
                    'deleted_at' => $user->deleted_at,
                ];
            });

        return response()->json($users);
    }

    /**
     * @OA\Get(
     *     path="/v1/users/{id}",
     *     summary="Get user by ID",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="User details",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="roles", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=404, description="User not found")
     * )
     */
    public function show($id)
    {
        // Temukan pengguna berdasarkan ID, termasuk yang dihapus secara soft delete
        $user = User::withTrashed()
            ->with('roles') // Muat relasi roles
            ->findOrFail($id);

        // Format respons
        $response = [
            'id' => $user->id,
            'kode_akses' => $user->kode_akses,
            'name' => $user->name,
            'email' => $user->email,
            'tanggal_lahir' => $user->tanggal_lahir,
            'usia' => $user->usia,
            'no_telp' => $user->no_telp,
            'no_str' => $user->no_str,
            'gender' => $user->gender,
            'roles' => $user->roles->pluck('name'), // Ambil nama role
            'deleted_at' => $user->deleted_at,
        ];

        return response()->json($response);
    }

    /**
     * @OA\Delete(
     *     path="/v1/users/{id}/force-delete",
     *     summary="Permanently delete a user",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="User permanently deleted successfully"),
     *     @OA\Response(response=404, description="User not found")
     * )
     */

    public function forceDelete($id)
    {
        try {
            Log::info('Attempting to force delete user with ID: ' . $id);

            $user = User::withTrashed()->findOrFail($id);
            Log::info('User found: ' . json_encode($user));

            $user->forceDelete();
            Log::info('User permanently deleted successfully');

            return response()->json(['message' => 'User permanently deleted successfully']);
        } catch (\Exception $e) {
            Log::error('Error in forceDelete: ' . $e->getMessage());
            return response()->json(['error' => 'An unexpected error occurred: ' . $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/v1/users/{id}/restore",
     *     summary="Restore a soft deleted user",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="User restored successfully"),
     *     @OA\Response(response=404, description="User not found")
     * )
     */

    public function restore($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->restore(); // Restore user

        return response()->json(['message' => 'User restored successfully']);
    }
}
