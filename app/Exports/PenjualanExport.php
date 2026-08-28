<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomStartCell; // Tambahkan ini
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class PenjualanExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents, WithColumnFormatting, WithCustomStartCell
{
    protected Collection $details;
    protected string $title; // Tambahkan property title
    protected int $totalHargaJual = 0;

    // Terima title dari controller
    public function __construct(Collection $details, string $title)
    {
        $this->details = $details;
        $this->title = $title;
    }

    public function collection(): Collection
    {
        return $this->details;
    }

    // Tabel akan dimulai dari baris ke-3 (A3)
    public function startCell(): string
    {
        return 'A3';
    }

    public function headings(): array
    {
        return [
            'Tgl Transaksi',
            'Nama Kasir',
            'Nama Barang',
            'Varian',
            'Harga',
        ];
    }

    public function map($row): array
    {
        $this->totalHargaJual += $row->price;

        return [
            Carbon::parse($row->created_at)->format('d/m/Y H:i'),
            $row->nama_user ?? '-', 
            $row->product->name ?? $row->product_id ?? 'Produk Dihapus / Manual', 
            $row->variant->name ?? $row->variant_id ?? '-',
            $row->price,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'E' => '"Rp "#,##0', 
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // --- 1. SET JUDUL LAPORAN DI BARIS PALING ATAS (A1) ---
                $sheet->setCellValue('A1', $this->title);
                $sheet->mergeCells('A1:E1'); // Gabungkan kolom A sampai E
                
                // Styling untuk judul laporan
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

                // --- 2. FORMATTING TABEL (Mulai dari A3) ---
                // Header tabel sekarang ada di baris 3
                $sheet->getStyle('A3:E3')->getFont()->setBold(true);

                // Baris Total Keseluruhan di Paling Bawah
                $highestRow = $sheet->getHighestRow();
                $totalRow = $highestRow + 1;

                $sheet->setCellValue('A' . $totalRow, 'TOTAL KESELURUHAN');
                $sheet->mergeCells('A' . $totalRow . ':D' . $totalRow);
                $sheet->getStyle('A' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                $sheet->setCellValue('E' . $totalRow, $this->totalHargaJual);

                $sheet->getStyle('A' . $totalRow . ':E' . $totalRow)->getFont()->setBold(true);

                // Format sel total
                $sheet->getStyle('E' . $totalRow)->getNumberFormat()->setFormatCode('"Rp "#,##0');

                // Menambahkan Garis/Border ke Seluruh Tabel (Mulai dari baris 3)
                $sheet->getStyle('A3:E' . $totalRow)->applyFromArray([
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