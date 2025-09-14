<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use SplTempFileObject;

class ReportsExportController extends Controller
{
    public function exportSales(Request $request)
    {
        $this->authorize('viewAny', Sale::class);

        $start = $request->get('start', now()->subMonth()->toDateString());
        $end = $request->get('end', now()->toDateString());
        $franchise = $request->get('franchise');

        $query = Sale::with('user','franchise')->whereBetween('date', [$start, $end]);

        if ($franchise) {
            $query->where('franchise_id', $franchise);
        } elseif (auth()->user()->role !== 'super_admin') {
            $query->where('franchise_id', auth()->user()->franchise_id);
        }

        $sales = $query->get();

        $csv = new SplTempFileObject();
        $csv->fputcsv(['id','date','franchise','user','total','payment_method','discount','receipt_number']);

        foreach ($sales as $s) {
            $csv->fputcsv([
                $s->id,
                $s->date,
                optional($s->franchise)->name,
                optional($s->user)->name,
                number_format($s->total,2,'.',''),
                $s->payment_method,
                number_format($s->discount ?? 0,2,'.',''),
                $s->receipt_number,
            ]);
        }

        $filename = "sales_{$start}_{$end}" . ($franchise ? "_fr{$franchise}" : '') . ".csv";

        return response((string)$csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ]);
    }
}
