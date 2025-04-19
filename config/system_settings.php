<?php
return [
    'cancellation_window_minutes' => 30,
    'service_types' => ['delivery', 'errand', 'courier'],
    'max_delivery_distance_km' => 50,
    'qr_code_expiry_minutes' => 15,
    'current_attendance_qr_code' => null, // Dynamically updated by admin
    'notification_channels' => ['mail', 'database'],
    'default_currency' => 'USD',
    'max_upload_size_mb' => 2,
];