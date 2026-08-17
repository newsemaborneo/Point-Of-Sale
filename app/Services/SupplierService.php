<?php

namespace App\Services;

use App\Models\Supplier;
use Illuminate\Support\Str;

class SupplierService
{
    /**
     * Membuat supplier baru.
     */
    public function createSupplier(array $data): Supplier
    {
        if (empty($data['code'])) {
            $data['code'] = 'SUP-' . Str::upper(Str::random(5));
        }

        return Supplier::create($data);
    }

    /**
     * Memperbarui data supplier.
     */
    public function updateSupplier(Supplier $supplier, array $data): Supplier
    {
        $supplier->update($data);
        return $supplier;
    }
}
