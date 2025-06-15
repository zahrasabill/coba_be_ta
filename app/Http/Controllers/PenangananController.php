<?php

namespace App\Http\Controllers;

use App\Http\Resources\PenangananResource;
use App\Models\Penanganan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Tag(
 *     name="Penanganan",
 *     description="API Endpoints untuk manajemen penanganan medis - Hanya untuk Dokter"
 * )
 */
class PenangananController extends Controller
{
    /**
     * Constructor untuk middleware role check
     */
    public function __construct()
    {
        // Middleware untuk memastikan hanya dokter yang bisa mengakses
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->hasRole('dokter')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Hanya dokter yang dapat mengakses menu penanganan.'
                ], 403);
            }
            return $next($request);
        });
    }

    /**
     * @OA\Get(
     *     path="/api/penanganan",
     *     summary="Mendapatkan daftar penanganan",
     *     description="Hanya dokter yang dapat melihat daftar penanganan",
     *     tags={"Penanganan"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter berdasarkan status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"pending", "selesai", "dibatalkan"})
     *     ),
     *     @OA\Parameter(
     *         name="tanggal_mulai",
     *         in="query",
     *         description="Filter tanggal mulai (Y-m-d)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="tanggal_akhir",
     *         in="query",
     *         description="Filter tanggal akhir (Y-m-d)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="my_patients",
     *         in="query",
     *         description="Filter hanya pasien yang ditangani dokter ini",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Berhasil mengambil data penanganan",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data penanganan berhasil diambil"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/PenangananResource"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Akses ditolak - Hanya dokter yang dapat mengakses"
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();
        $query = Penanganan::with(['pasien', 'dokter']);

        // Dokter bisa melihat semua penanganan atau hanya yang dia tangani
        if ($request->boolean('my_patients')) {
            $query->byDokter($user->id);
        }

        // Apply filters
        if ($request->has('status')) {
            $query->withStatus($request->status);
        }

        if ($request->has('tanggal_mulai')) {
            $query->whereDate('tanggal_penanganan', '>=', $request->tanggal_mulai);
        }

        if ($request->has('tanggal_akhir')) {
            $query->whereDate('tanggal_penanganan', '<=', $request->tanggal_akhir);
        }

        $penanganan = $query->latest()->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'Data penanganan berhasil diambil',
            'data' => PenangananResource::collection($penanganan->items()),
            'pagination' => [
                'current_page' => $penanganan->currentPage(),
                'total_pages' => $penanganan->lastPage(),
                'per_page' => $penanganan->perPage(),
                'total' => $penanganan->total(),
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/penanganan",
     *     summary="Membuat penanganan baru",
     *     description="Hanya dokter yang dapat membuat penanganan baru",
     *     tags={"Penanganan"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id", "tanggal_penanganan", "keluhan", "telinga_terkena"},
     *             @OA\Property(property="user_id", type="integer", description="ID pasien", example=1),
     *             @OA\Property(property="tanggal_penanganan", type="string", format="date", example="2025-06-15"),
     *             @OA\Property(property="keluhan", type="string", example="Telinga terasa sakit dan berdengung"),
     *             @OA\Property(property="riwayat_penyakit", type="string", example="Pernah mengalami infeksi telinga"),
     *             @OA\Property(property="diagnosis_manual", type="string", example="Otitis media akut"),
     *             @OA\Property(property="telinga_terkena", type="string", enum={"kiri", "kanan", "keduanya"}, example="kiri"),
     *             @OA\Property(property="tindakan", type="string", example="Pemberian antibiotik dan tetes telinga"),
     *             @OA\Property(property="status", type="string", enum={"pending", "selesai", "dibatalkan"}, example="pending")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Penanganan berhasil dibuat",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Penanganan berhasil dibuat"),
     *             @OA\Property(property="data", ref="#/components/schemas/PenangananResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Akses ditolak - Hanya dokter yang dapat mengakses"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'tanggal_penanganan' => 'required|date',
            'keluhan' => 'required|string|max:1000',
            'riwayat_penyakit' => 'nullable|string|max:1000',
            'diagnosis_manual' => 'nullable|string|max:500',
            'telinga_terkena' => 'required|in:kiri,kanan,keduanya',
            'tindakan' => 'nullable|string|max:1000',
            'status' => 'nullable|in:pending,selesai,dibatalkan',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if user_id is actually a patient using Spatie roles
        $patient = User::where('id', $request->user_id)
                      ->whereHas('roles', function($query) {
                          $query->where('name', 'pasien');
                      })
                      ->first();

        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'User yang dipilih bukan pasien'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $penanganan = Penanganan::create([
                'user_id' => $request->user_id,
                'dokter_id' => Auth::id(),
                'tanggal_penanganan' => $request->tanggal_penanganan,
                'keluhan' => $request->keluhan,
                'riwayat_penyakit' => $request->riwayat_penyakit,
                'diagnosis_manual' => $request->diagnosis_manual,
                'telinga_terkena' => $request->telinga_terkena,
                'tindakan' => $request->tindakan,
                'status' => $request->status ?? 'pending',
            ]);

            $penanganan->load(['pasien', 'dokter']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Penanganan berhasil dibuat',
                'data' => new PenangananResource($penanganan)
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat penanganan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/penanganan/{id}",
     *     summary="Mendapatkan detail penanganan",
     *     description="Hanya dokter yang dapat melihat detail penanganan",
     *     tags={"Penanganan"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detail penanganan berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Detail penanganan berhasil diambil"),
     *             @OA\Property(property="data", ref="#/components/schemas/PenangananResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Akses ditolak - Hanya dokter yang dapat mengakses"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Penanganan tidak ditemukan"
     *     )
     * )
     */
    public function show($id): JsonResponse
    {
        $penanganan = Penanganan::with(['pasien', 'dokter'])->find($id);

        if (!$penanganan) {
            return response()->json([
                'success' => false,
                'message' => 'Penanganan tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail penanganan berhasil diambil',
            'data' => new PenangananResource($penanganan)
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/penanganan/{id}",
     *     summary="Mengupdate penanganan",
     *     description="Hanya dokter yang dapat mengupdate penanganan",
     *     tags={"Penanganan"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="tanggal_penanganan", type="string", format="date"),
     *             @OA\Property(property="keluhan", type="string"),
     *             @OA\Property(property="riwayat_penyakit", type="string"),
     *             @OA\Property(property="diagnosis_manual", type="string"),
     *             @OA\Property(property="telinga_terkena", type="string", enum={"kiri", "kanan", "keduanya"}),
     *             @OA\Property(property="tindakan", type="string"),
     *             @OA\Property(property="status", type="string", enum={"pending", "selesai", "dibatalkan"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Penanganan berhasil diupdate"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Akses ditolak - Hanya dokter yang dapat mengakses"
     *     )
     * )
     */
    public function update(Request $request, $id): JsonResponse
    {
        $penanganan = Penanganan::with(['pasien', 'dokter'])->find($id);

        if (!$penanganan) {
            return response()->json([
                'success' => false,
                'message' => 'Penanganan tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'tanggal_penanganan' => 'sometimes|date',
            'keluhan' => 'sometimes|string|max:1000',
            'riwayat_penyakit' => 'nullable|string|max:1000',
            'diagnosis_manual' => 'nullable|string|max:500',
            'telinga_terkena' => 'sometimes|in:kiri,kanan,keduanya',
            'tindakan' => 'nullable|string|max:1000',
            'status' => 'sometimes|in:pending,selesai,dibatalkan',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $penanganan->update($request->only([
                'tanggal_penanganan',
                'keluhan',
                'riwayat_penyakit',
                'diagnosis_manual',
                'telinga_terkena',
                'tindakan',
                'status'
            ]));

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Penanganan berhasil diupdate',
                'data' => new PenangananResource($penanganan->fresh(['pasien', 'dokter']))
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate penanganan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/penanganan/{id}",
     *     summary="Menghapus penanganan",
     *     description="Hanya dokter yang dapat menghapus penanganan",
     *     tags={"Penanganan"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Penanganan berhasil dihapus"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Akses ditolak - Hanya dokter yang dapat mengakses"
     *     )
     * )
     */
    public function destroy($id): JsonResponse
    {
        $penanganan = Penanganan::find($id);

        if (!$penanganan) {
            return response()->json([
                'success' => false,
                'message' => 'Penanganan tidak ditemukan'
            ], 404);
        }

        try {
            $penanganan->delete();

            return response()->json([
                'success' => true,
                'message' => 'Penanganan berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus penanganan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/penanganan/pasien-list",
     *     summary="Mendapatkan daftar pasien untuk dropdown",
     *     description="Hanya dokter yang dapat melihat daftar pasien",
     *     tags={"Penanganan"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Daftar pasien berhasil diambil"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Akses ditolak - Hanya dokter yang dapat mengakses"
     *     )
     * )
     */
    public function getPasienList(): JsonResponse
    {
        $pasienList = User::whereHas('roles', function($query) {
                          $query->where('name', 'pasien');
                      })
                      ->select('id', 'name', 'email')
                      ->orderBy('name')
                      ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar pasien berhasil diambil',
            'data' => $pasienList
        ]);
    }
}