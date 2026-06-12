<?php

return [
    'database' => [
        'name' => env('DB_DATABASE', 'Office_Service'),
    ],

    'uploads' => [
        'disk' => env('FILESYSTEM_DISK', 'public'),
        'max_kilobytes' => (int) env('OFFICE_UPLOAD_MAX_KB', 10240),
        'allowed_mimes' => array_filter(explode(',', env('OFFICE_UPLOAD_MIMES', 'pdf,jpg,jpeg,png,doc,docx'))),
        'directories' => [
            'documents' => 'documents',
            'qr_codes' => 'qr-codes',
            'barcodes' => 'barcodes',
            'exports' => 'exports',
        ],
    ],
];
