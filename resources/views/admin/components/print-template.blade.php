<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>گزارش چاپی</title>
    
    <!-- Bootstrap RTL CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Tahoma', 'Arial', sans-serif;
            direction: rtl;
            text-align: right;
            margin: 0;
            padding: 20px;
            background: white;
        }
        
        .print-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        
        .print-title {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin: 0 0 10px 0;
        }
        
        .print-info {
            font-size: 14px;
            color: #666;
            display: flex;
            justify-content: space-between;
        }
        
        .print-table-container {
            overflow-x: auto;
            margin: 20px 0;
        }
        
        .print-table {
            font-size: 12px;
            margin-bottom: 0;
        }
        
        .print-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #333;
            border: 1px solid #dee2e6;
            padding: 8px;
            text-align: right;
            vertical-align: middle;
        }
        
        .print-table td {
            border: 1px solid #dee2e6;
            padding: 8px;
            text-align: right;
            vertical-align: middle;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        
        .print-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .print-footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        
        /* Print-specific styles */
        @media print {
            body {
                margin: 0;
                padding: 10px;
                font-size: 10px;
            }
            
            .print-header {
                margin-bottom: 15px;
                padding-bottom: 10px;
            }
            
            .print-title {
                font-size: 18px;
                margin-bottom: 5px;
            }
            
            .print-info {
                font-size: 10px;
            }
            
            .print-table {
                font-size: 9px;
                margin: 10px 0;
            }
            
            .print-table th,
            .print-table td {
                padding: 4px 6px;
                font-size: 9px;
            }
            
            .print-footer {
                margin-top: 15px;
                font-size: 8px;
                padding-top: 8px;
            }
            
            .print-table-container {
                overflow: visible;
            }
            
            /* Force table to fit on one page */
            .print-table {
                width: 100% !important;
                max-width: 100% !important;
            }
            
            .print-table th,
            .print-table td {
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                max-width: 120px;
            }
            
            @page {
                margin: 0.5cm;
                size: A4;
            }
        }
        
        /* Responsive breakpoints */
        @media (max-width: 768px) {
            .print-table {
                font-size: 10px;
            }
            
            .print-table th,
            .print-table td {
                padding: 4px;
                font-size: 10px;
            }
        }
        
        @media (max-width: 576px) {
            .print-table {
                font-size: 8px;
            }
            
            .print-table th,
            .print-table td {
                padding: 2px;
                font-size: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="print-header">
        <h1 class="print-title">{{ $title }}</h1>
        <div class="print-info">
            <span>تاریخ: {{ $date }}</span>
            <span>تعداد رکوردها: {{ $records }}</span>
        </div>
    </div>
    
    <div class="print-table-container">
        <div class="table-responsive">
            <table class="table table-bordered table-striped print-table">
                <thead>
                    <tr>
                        @foreach($fieldLabels as $label)
                            <th>{{ $label }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($tableData as $row)
                        <tr>
                            @foreach($row as $cell)
                                <td>{{ $cell }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="print-footer">
        این گزارش به صورت خودکار توسط سیستم مدیریت تولید شده است
    </div>

    <script>
        // Auto-print when page loads
        window.onload = function() {
            setTimeout(() => {
                window.print();
                window.close();
            }, 500);
        };
    </script>
</body>
</html>
