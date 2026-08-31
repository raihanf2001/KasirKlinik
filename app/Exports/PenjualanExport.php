<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class PenjualanExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents, WithColumnFormatting, WithCustomStartCell
{
    protected Collection $exportData;
    protected string $title;
    protected int $totalHargaJual = 0;

    // Terima $transactions dari controller
    public function __construct(Collection $transactions, string $title)
    {
        $this->title = $title;
        $this->exportData = collect();

        // Meratakan data (Flatten) agar enak dibaca & difilter di Excel
        foreach ($transactions as $t) {
            foreach ($t->details as $d) {
                $this->exportData->push((object)[
                    'tgl_transaksi'  => $t->created_at,
                    'metode'         =>$t->payment_method,
                    'nama_kasir'     => $d->nama_user ?? ($t->user->name ?? 'Kasir'),
                    'nama_barang'    => $d->product->name ?? $d->product_id ?? 'Produk Dihapus',
                    'varian'         => $d->variant->name ?? $d->variant_id ?? '-',
                    'harga'          => $d->price,
                ]);
                
                // Hitung total hanya dari barang yang terjual (karena qty dihilangkan/diasumsikan 1 per baris sesuai request sebelumnya, atau jika harga ini sudah merepresentasikan total per item)
                $this->totalHargaJual += $d->price; 
            }
        }
    }

    public function collection(): Collection
    {
        return $this->exportData;
    }

    public function startCell(): string
    {
        return 'A3';
    }

    public function headings(): array
    {
        // Tambahkan kolom Nota/ID dan hilangkan Qty
        return [
            'Tgl Transaksi',
            'Nama Kasir',
            'metode pembayaran',
            'Nama Barang',
            'Varian',
            'Harga',
        ];
    }

    public function map($row): array
    {
        return [
            Carbon::parse($row->tgl_transaksi)->format('d/m/Y H:i'),
            $row->nama_kasir,
            $row->metode,
            $row->nama_barang,
            $row->varian,
            $row->harga,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'F' => '"Rp "#,##0', // Kolom harga sekarang bergeser ke F
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // --- 1. SET JUDUL LAPORAN DI BARIS PALING ATAS (A1) ---
                $sheet->setCellValue('A1', $this->title);
                $sheet->mergeCells('A1:F1'); // Gabungkan kolom A sampai F
                
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);

                // --- 2. FORMATTING TABEL ---
                $sheet->getStyle('A3:F3')->getFont()->setBold(true);

                // Baris Total Keseluruhan
                $highestRow = $sheet->getHighestRow();
                $totalRow = $highestRow + 1;

                $sheet->setCellValue('A' . $totalRow, 'TOTAL KESELURUHAN');
                $sheet->mergeCells('A' . $totalRow . ':E' . $totalRow); // Merge A sampai E
                $sheet->getStyle('A' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                $sheet->setCellValue('F' . $totalRow, $this->totalHargaJual);

                $sheet->getStyle('A' . $totalRow . ':F' . $totalRow)->getFont()->setBold(true);
                $sheet->getStyle('F' . $totalRow)->getNumberFormat()->setFormatCode('"Rp "#,##0');

                // Menambahkan Border
                $sheet->getStyle('A3:F' . $totalRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);
            },
        ];
    }
}