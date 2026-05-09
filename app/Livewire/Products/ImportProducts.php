<?php

namespace App\Livewire\Products;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportProducts extends Component
{
    use WithFileUploads;

    public $file = null;
    public array $preview = [];
    public array $importErrors = [];
    public int $imported = 0;
    public bool $done = false;

    protected function rules(): array
    {
        return [
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ];
    }

    public function updatedFile(): void
    {
        $this->preview = [];
        $this->importErrors = [];
        $this->done = false;
        $this->imported = 0;

        $this->validate();

        $path = $this->file->getRealPath();
        $spreadsheet = IOFactory::load($path);
        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);

        // Skip header row
        $headers = array_shift($rows);
        $preview = [];
        foreach (array_slice($rows, 0, 5) as $row) {
            $preview[] = array_slice($row, 0, 6);
        }
        $this->preview = $preview;
    }

    public function import(): void
    {
        $this->validate();
        $this->importErrors = [];
        $this->imported = 0;

        $path = $this->file->getRealPath();
        $spreadsheet = IOFactory::load($path);
        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);

        // Expect columns: name, barcode, sku, category, cost_price, selling_price, tax_rate, min_stock_alert, pieces_per_box, description
        array_shift($rows); // remove header

        $categoryCache = [];
        $unitCache = [];

        foreach ($rows as $i => $row) {
            $rowNum = $i + 2;
            $name = trim((string) ($row[0] ?? ''));

            if ($name === '') {
                continue;
            }

            $barcode    = trim((string) ($row[1] ?? '')) ?: null;
            $sku        = trim((string) ($row[2] ?? '')) ?: null;
            $categoryName = trim((string) ($row[3] ?? ''));
            $costPrice  = is_numeric($row[4] ?? null) ? (float) $row[4] : 0;
            $sellPrice  = is_numeric($row[5] ?? null) ? (float) $row[5] : 0;
            $taxRate    = is_numeric($row[6] ?? null) ? (float) $row[6] : 0;
            $minStock   = is_numeric($row[7] ?? null) ? (int) $row[7] : 5;
            $piecesPerBox = is_numeric($row[8] ?? null) && (int) $row[8] > 0 ? (int) $row[8] : null;
            $description = trim((string) ($row[9] ?? '')) ?: null;

            // Unique checks
            if ($barcode && Product::withTrashed()->where('barcode', $barcode)->exists()) {
                $this->importErrors[] = "Row {$rowNum}: barcode '{$barcode}' already exists — skipped.";
                continue;
            }
            if ($sku && Product::withTrashed()->where('sku', $sku)->exists()) {
                $this->importErrors[] = "Row {$rowNum}: SKU '{$sku}' already exists — skipped.";
                continue;
            }

            // Resolve category
            $categoryId = null;
            if ($categoryName !== '') {
                if (!isset($categoryCache[$categoryName])) {
                    $categoryCache[$categoryName] = Category::firstOrCreate(
                        ['name' => $categoryName],
                        ['slug' => Str::slug($categoryName)]
                    )->id;
                }
                $categoryId = $categoryCache[$categoryName];
            }

            // Slug
            $slug = Str::slug($name);
            if (Product::withTrashed()->where('slug', $slug)->exists()) {
                $slug .= '-' . time() . '-' . $rowNum;
            }

            Product::create([
                'name'          => $name,
                'slug'          => $slug,
                'barcode'       => $barcode,
                'sku'           => $sku,
                'category_id'   => $categoryId,
                'cost_price'    => $costPrice,
                'selling_price' => $sellPrice,
                'tax_rate'      => $taxRate,
                'min_stock_alert' => $minStock,
                'pieces_per_box' => $piecesPerBox,
                'description'   => $description,
                'track_inventory' => true,
                'is_active'     => true,
            ]);

            $this->imported++;
        }

        $this->done = true;
        $this->file = null;
        $this->preview = [];
    }

    public function downloadTemplate()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $headers = ['name', 'barcode', 'sku', 'category', 'cost_price', 'selling_price', 'tax_rate', 'min_stock_alert', 'pieces_per_box', 'description'];
        foreach ($headers as $col => $header) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1);
            $sheet->setCellValue($colLetter . '1', $header);
        }
        $sample = ['Sample Product', '123456789', 'SKU-001', 'Electronics', '10.00', '15.00', '0', '5', '12', 'A sample product'];
        foreach ($sample as $col => $val) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1);
            $sheet->setCellValue($colLetter . '2', $val);
        }

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $tmpFile = tempnam(sys_get_temp_dir(), 'products_template_') . '.xlsx';
        $writer->save($tmpFile);

        return response()->download($tmpFile, 'products_import_template.xlsx')->deleteFileAfterSend(true);
    }

    public function render()
    {
        return view('livewire.products.import-products')
            ->layout('layouts.app', ['title' => 'Import Products']);
    }
}
