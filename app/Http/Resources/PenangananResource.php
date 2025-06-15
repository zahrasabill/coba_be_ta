<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="PenangananResource",
 *     type="object",
 *     title="Penanganan Resource",
 *     description="Resource untuk data penanganan medis",
 *     @OA\Property(property="id", type="integer", example=1, description="ID penanganan"),
 *     @OA\Property(property="user_id", type="integer", example=2, description="ID pasien"),
 *     @OA\Property(property="dokter_id", type="integer", example=1, description="ID dokter"),
 *     @OA\Property(property="tanggal_penanganan", type="string", format="date", example="2025-06-15", description="Tanggal penanganan"),
 *     @OA\Property(property="keluhan", type="string", example="Telinga terasa sakit dan berdengung", description="Keluhan pasien"),
 *     @OA\Property(property="riwayat_penyakit", type="string", example="Pernah mengalami infeksi telinga", description="Riwayat penyakit pasien"),
 *     @OA\Property(property="diagnosis_manual", type="string", example="Otitis media akut", description="Diagnosis manual dari dokter"),
 *     @OA\Property(property="telinga_terkena", type="string", enum={"kiri", "kanan", "keduanya"}, example="kiri", description="Telinga yang terkena"),
 *     @OA\Property(property="tindakan", type="string", example="Pemberian antibiotik dan tetes telinga", description="Tindakan yang diberikan"),
 *     @OA\Property(property="status", type="string", enum={"pending", "selesai", "dibatalkan"}, example="pending", description="Status penanganan"),
 *     @OA\Property(property="created_at", type="string", format="datetime", example="2025-06-15T10:30:00Z", description="Waktu dibuat"),
 *     @OA\Property(property="updated_at", type="string", format="datetime", example="2025-06-15T10:30:00Z", description="Waktu diupdate"),
 *     @OA\Property(
 *         property="pasien",
 *         type="object",
 *         description="Data pasien",
 *         @OA\Property(property="id", type="integer", example=2),
 *         @OA\Property(property="name", type="string", example="John Doe"),
 *         @OA\Property(property="kode_akses", type="string", example="PRS-ABC12"),
 *         @OA\Property(property="tanggal_lahir", type="string", format="date", example="1990-05-15"),
 *         @OA\Property(property="usia", type="integer", example=35),
 *         @OA\Property(property="gender", type="string", example="laki-laki"),
 *         @OA\Property(property="no_telp", type="string", example="081234567890")
 *     ),
 *     @OA\Property(
 *         property="dokter",
 *         type="object",
 *         description="Data dokter",
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="Dr. Jane Smith"),
 *         @OA\Property(property="kode_akses", type="string", example="DRS-XYZ98"),
 *         @OA\Property(property="tanggal_lahir", type="string", format="date", example="1980-03-20"),
 *         @OA\Property(property="usia", type="integer", example=45),
 *         @OA\Property(property="gender", type="string", example="perempuan"),
 *         @OA\Property(property="no_telp", type="string", example="081234567891"),
 *         @OA\Property(property="no_str", type="string", example="STR123456")
 *     )
 * )
 */
class PenangananResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}