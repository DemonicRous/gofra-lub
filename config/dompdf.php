<?php

return [
    'defines' => [
        'DOMPDF_FONT_DIR' => storage_path('fonts/'),
        'DOMPDF_FONT_CACHE' => storage_path('fonts/'),
        'DOMPDF_UNICODE_ENABLED' => true,
        'DOMPDF_ENABLE_PHP' => false,
        'DOMPDF_ENABLE_REMOTE' => true,
        'DOMPDF_ENABLE_CSS_FLOAT' => true,
        'DOMPDF_ENABLE_JAVASCRIPT' => false,
        'DOMPDF_ENABLE_HTML5PARSER' => true,
        'DOMPDF_LOG_OUTPUT_FILE' => storage_path('logs/dompdf.log'),
        'DOMPDF_DEFAULT_MEDIA_TYPE' => 'screen',
        'DOMPDF_DEFAULT_PAPER_SIZE' => 'a4',
        'DOMPDF_DEFAULT_FONT' => 'DejaVu Sans',
        'DOMPDF_DPI' => 150,
        'DOMPDF_ENABLE_ACCESSIBILITY' => false,
        'DOMPDF_FONT_HEIGHT_RATIO' => 1.1,
        'DOMPDF_ENABLE_FONT_SUBSETTING' => false,
    ],
    'font_cache' => storage_path('fonts/'),
    'temp_dir' => sys_get_temp_dir(),
    'log_output_file' => storage_path('logs/dompdf.log'),
    'default_font' => 'DejaVu Sans',
];
