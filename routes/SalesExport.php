<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SalesExport implements FromCollection, WithHeadings
{
    protected $sales;

    public function __construct(array $data)
    {
        $this->sales = $data['sales'];
    }

    public function collection()
    {
        // Transformasi data penjualan ke format yang sesuai untuk Excel
        return $this->sales->map(function ($sale) {
            return [
                'Invoice' => $sale->invoice_number,
                'Cabang' => $sale->branch->name ?? '-',
                'Pelanggan' => $sale->customer->name ?? 'Umum',
                'Total' => $sale->grand_total,
                'Tanggal' => $sale->created_at->format('d M Y H:i'),
                'Status' => ucfirst($sale->status),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Invoice', 'Cabang', 'Pelanggan', 'Total', 'Tanggal', 'Status'
        ];
    }
}