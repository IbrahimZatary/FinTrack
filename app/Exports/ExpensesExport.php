<?php

namespace App\Exports;

use App\Models\Expense;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpensesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $request;
    
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    
    public function title(): string
    {
        return 'Expenses';
    }
    
    public function collection()
    {
        $user = Auth::user();
        
        $query = $user->expenses()->with('category');
        
        //  filters
        if ($this->request->filled('start_date')) {
            $query->where('date', '>=', $this->request->start_date);
        }
        
        if ($this->request->filled('end_date')) {
            $query->where('date', '<=', $this->request->end_date);
        }
        
        if ($this->request->filled('category_id')) {
            $query->where('category_id', $this->request->category_id);
        }
        
        return $query->orderBy('date', 'desc')->get();
    }
    
    public function headings(): array
    {
        return [
            'ID',
            'Date',
            'Description',
            'Category',
            'Amount ($)',
            'Created At'
        ];
    }
    
    public function map($expense): array
    {
        return [
            $expense->id,
            $expense->date->format('Y-m-d'),
            $expense->description ?? '-',
            $expense->category ? $expense->category->name : 'Uncategorized',
            number_format($expense->amount, 2),
            $expense->created_at->format('Y-m-d H:i:s')
        ];
    }
    
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the header row
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4361EE']]
            ],
            
            //  amount column
            'E' => ['alignment' => ['horizontal' => 'right']],
            
            // Add borders
            'A1:F' . ($sheet->getHighestRow()) => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => 'thin',
                        'color' => ['rgb' => '000000']
                    ]
                ]
            ]
        ];
    }
}
