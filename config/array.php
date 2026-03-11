<?php

return [
    'printer' => [
        'role' => ['cashier', 'kitchen'],
        'section' => ['all', 'makanan', 'minuman'],
        'connection_type' => ['usb', 'lan'],
    ],
    'ingredient' => [
        'types' => ['raw','semi','finished'],
        'units' => [
            'liter' => [
                'symbol' => 'l'
            ],
            'mililiter' => [
                'symbol' => 'ml'
            ],
            'kilogram' => [
                'symbol' => 'kg'
            ],
            'gram' => [
                'symbol' => 'gram'
            ],
            'pieces' => [
                'symbol' => 'pcs'
            ],
            'porsi' => [
                'symbol' => 'porsi'
            ],
            'pack' => [
                'symbol' => 'pack'
            ]
        ],
    ],
    'order' => [
        'type' => [
            'dine_in' => [
                'display_name' => 'Dine In'
            ],
            'take_away' => [
                'display_name' => 'Take Away'
            ],
            'delivery' => [
                'display_name' => 'Delivery'
            ]
        ],
        'channel' => [
            'dine_in_regular' => [
                'display_name' => 'Dine-In Biasa'
            ],
            'dine_in_vip' => [
                'display_name' => 'Dine-In VIP'
            ],
            'booking_birthday' => [
                'display_name' => 'Booking Ulang Tahun'
            ],
            'booking_meeting' => [
                'display_name' => 'Booking Meeting'
            ],
            'booking_event' => [
                'display_name' => 'Booking Event'
            ],
        ],
        'status' => [
            'open' => [
                'label' => 'Aktif',
                'class' => 'bg-gray-100 text-gray-700',
                'icon'  => 'fa-receipt'
            ],
            'kitchen' => [
                'label' => 'Di Dapur',
                'class' => 'bg-amber-100 text-amber-700',
                'icon'  => 'fa-kitchen-set'
            ],
            'ready' => [
                'label' => 'Siap',
                'class' => 'bg-green-100 text-green-700',
                'icon'  => 'fa-bell'
            ],
            'completed' => [
                'label' => 'Selesai',
                'class' => 'bg-emerald-100 text-emerald-700',
                'icon'  => 'fa-check'
            ],
            'cancelled' => [
                'label' => 'Batal',
                'class' => 'bg-red-100 text-red-700',
                'icon'  => 'fa-xmark'
            ],
        ],
        'payment_status' => [
            'unpaid'  => [
                'label' => 'Belum Bayar',
                'class' => 'bg-red-100 text-red-700',
                'icon'  => 'fa-wallet'
            ],
            'partial' => [
                'label' => 'DP',
                'class' => 'bg-yellow-100 text-yellow-700',
                'icon'  => 'fa-coins'
            ],
            'paid' => [
                'label' => 'Lunas',
                'class' => 'bg-green-100 text-green-700',
                'icon'  => 'fa-circle-check'
            ],
        ]
    ],
    'banks' => [
        'BCA',
        'BRI',
        'BNI',
        'MANDIRI',
    ]
];
