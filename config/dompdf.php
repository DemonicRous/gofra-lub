<?php

return [
    'defines' => [
        'DOMPDF_FONT_DIR' => storage_path('fonts/'),
        'DOMPDF_FONT_CACHE' => storage_path('fonts/'),
        'DOMPDF_UNICODE_ENABLED' => true,
        'DOMPDF_ENABLE_PHP' => false,
        'DOMPDF_ENABLE_REMOTE' => true,
    ],
    'font_cache' => storage_path('fonts/'),
];
