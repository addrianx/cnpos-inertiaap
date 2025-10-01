<?php

namespace App\Traits;

use Illuminate\Validation\ValidationException;

trait PaymentValidationTrait
{
    /**
     * Validasi pembayaran minimal sama dengan total harga
     */
    public function validatePayment($dibayar, $total)
    {
        if ($dibayar < $total) {
            throw ValidationException::withMessages([
                'dibayar' => 'Harga bayar tidak mencukupi. Minimal harus sebesar Rp ' . number_format($total, 0, ',', '.'),
            ]);
        }

        return true;
    }
}
