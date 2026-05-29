<?php

namespace App\Services;

use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

class SupplierService
{
    public function getAllSuppliers()
    {
        // Hanya mengambil supplier yang aktif (belum di-soft delete)
        return Supplier::orderBy('code', 'asc')->get();
    }

    public function storeSupplier(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Auto-generate Kode Alternatif (Misal: A1, A2, A3)
            // withTrashed() digunakan agar jika A3 dihapus, data baru tetap menjadi A4 (mencegah duplikasi kode di riwayat)
            $lastSupplier = Supplier::withTrashed()->orderBy('id', 'desc')->first();
            $lastNumber = $lastSupplier ? (int) substr($lastSupplier->code, 1) : 0;
            $data['code'] = 'A' . ($lastNumber + 1);

            return Supplier::create($data);
        });
    }

    public function updateSupplier($id, array $data)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->update($data);

        return $supplier->fresh();
    }

    public function deleteSupplier($id)
    {
        $supplier = Supplier::findOrFail($id);

        // Menggunakan SoftDelete (hanya mengisi kolom deleted_at)
        // Ini memastikan tabel evaluations dan rankings riwayat bulan lalu tidak error
        return $supplier->delete();
    }
}
