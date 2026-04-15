<?php

namespace Tests\Feature\Reports;

use App\Livewire\Reports\SalesReport;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SalesReportExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_report_can_be_exported_as_an_excel_file(): void
    {
        Carbon::setTestNow('2026-04-15 10:30:45');

        Livewire::test(SalesReport::class)
            ->call('exportExcel')
            ->assertFileDownloaded('sales-report-20260415-103045.xlsx', null, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        Carbon::setTestNow();
    }
}