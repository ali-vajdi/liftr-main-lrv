<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PrintController extends Controller
{
    public function template(Request $request)
    {
        $printData = json_decode($request->input('print_data'), true);
        
        if (!$printData) {
            abort(400, 'Invalid print data');
        }
        
        return view('admin.components.print-template', [
            'title' => $printData['title'] ?? 'گزارش',
            'date' => $printData['date'] ?? date('Y/m/d'),
            'records' => $printData['records'] ?? 0,
            'fieldLabels' => $printData['fieldLabels'] ?? [],
            'tableData' => $printData['tableData'] ?? []
        ]);
    }
}
