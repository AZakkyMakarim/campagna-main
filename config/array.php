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
            'reservation' => [
                'display_name' => 'Reservasi'
            ],
            'dine_in' => [
                'display_name' => 'Dine In'
            ],
            'take_away' => [
                'display_name' => 'Take Away'
            ],
//            'delivery' => [
//                'display_name' => 'Delivery'
//            ]
        ],
        'channel' => [
            'dine_in' => [
                'display_name' => 'Dine In'
            ],
            'take_away' => [
                'display_name' => 'Take Away'
            ],
            'reservation' => [
                'display_name' => 'Reservasi'
            ],
//            'dine_in_regular' => [
//                'display_name' => 'Dine-In Biasa'
//            ],
//            'dine_in_vip' => [
//                'display_name' => 'Dine-In VIP'
//            ],
//            'booking_birthday' => [
//                'display_name' => 'Booking Ulang Tahun'
//            ],
//            'booking_meeting' => [
//                'display_name' => 'Booking Meeting'
//            ],
//            'booking_event' => [
//                'display_name' => 'Booking Event'
//            ],
        ],
        'status' => [
            'open' => [
                'label' => 'Aktif',
                'class' => 'bg-gray-100 text-gray-700',
                'icon'  => 'fa-receipt'
            ],
//            'kitchen' => [
//                'label' => 'Di Dapur',
//                'class' => 'bg-amber-100 text-amber-700',
//                'icon'  => 'fa-kitchen-set'
//            ],
//            'ready' => [
//                'label' => 'Siap',
//                'class' => 'bg-green-100 text-green-700',
//                'icon'  => 'fa-bell'
//            ],
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
        [
            "name" => "BANK BCA",
            "code" => "014"
        ],
        [
            "name" => "BANK BRI",
            "code" => "002"
        ],
        [
            "name" => "BANK EKSPOR INDONESIA",
            "code" => "003"
        ],
        [
            "name" => "BANK MANDIRI",
            "code" => "008"
        ],
        [
            "name" => "BANK BNI",
            "code" => "009"
        ],
        [
            "name" => "BANK DANAMON",
            "code" => "011"
        ],
        [
            "name" => "PERMATA BANK",
            "code" => "013"
        ],
        [
            "name" => "BANK BII",
            "code" => "016"
        ],
        [
            "name" => "BANK PANIN",
            "code" => "019"
        ],
        [
            "name" => "BANK ARTA NIAGA KENCANA",
            "code" => "020"
        ],
        [
            "name" => "BANK CIMB NIAGA",
            "code" => "022"
        ],
        [
            "name" => "BANK BUANA IND",
            "code" => "023"
        ],
        [
            "name" => "BANK LIPPO",
            "code" => "026"
        ],
        [
            "name" => "BANK NISP",
            "code" => "028"
        ],
        [
            "name" => "AMERICAN EXPRESS BANK LTD",
            "code" => "030"
        ],
        [
            "name" => "CITIBANK N.A.",
            "code" => "031"
        ],
        [
            "name" => "JP. MORGAN CHASE BANK, N.A.",
            "code" => "032"
        ],
        [
            "name" => "BANK OF AMERICA, N.A",
            "code" => "033"
        ],
        [
            "name" => "ING INDONESIA BANK",
            "code" => "034"
        ],
        [
            "name" => "BANK MULTICOR TBK.",
            "code" => "036"
        ],
        [
            "name" => "BANK ARTHA GRAHA",
            "code" => "037"
        ],
        [
            "name" => "BANK CREDIT AGRICOLE INDOSUEZ",
            "code" => "039"
        ],
        [
            "name" => "THE BANGKOK BANK COMP. LTD",
            "code" => "040"
        ],
        [
            "name" => "THE HONGKONG & SHANGHAI B.C.",
            "code" => "041"
        ],
        [
            "name" => "THE BANK OF TOKYO MITSUBISHI UFJ LTD",
            "code" => "042"
        ],
        [
            "name" => "BANK SUMITOMO MITSUI INDONESIA",
            "code" => "045"
        ],
        [
            "name" => "BANK DBS INDONESIA",
            "code" => "046"
        ],
        [
            "name" => "BANK RESONA PERDANIA",
            "code" => "047"
        ],
        [
            "name" => "BANK MIZUHO INDONESIA",
            "code" => "048"
        ],
        [
            "name" => "STANDARD CHARTERED BANK",
            "code" => "050"
        ],
        [
            "name" => "BANK ABN AMRO",
            "code" => "052"
        ],
        [
            "name" => "BANK KEPPEL TATLEE BUANA",
            "code" => "053"
        ],
        [
            "name" => "BANK CAPITAL INDONESIA, TBK.",
            "code" => "054"
        ],
        [
            "name" => "BANK BNP PARIBAS INDONESIA",
            "code" => "057"
        ],
        [
            "name" => "BANK UOB INDONESIA",
            "code" => "058"
        ],
        [
            "name" => "KOREA EXCHANGE BANK DANAMON",
            "code" => "059"
        ],
        [
            "name" => "RABOBANK INTERNASIONAL INDONESIA",
            "code" => "060"
        ],
        [
            "name" => "ANZ PANIN BANK",
            "code" => "061"
        ],
        [
            "name" => "DEUTSCHE BANK AG.",
            "code" => "067"
        ],
        [
            "name" => "BANK WOORI INDONESIA",
            "code" => "068"
        ],
        [
            "name" => "BANK OF CHINA LIMITED",
            "code" => "069"
        ],
        [
            "name" => "BANK BUMI ARTA",
            "code" => "076"
        ],
        [
            "name" => "BANK EKONOMI",
            "code" => "087"
        ],
        [
            "name" => "BANK ANTARDAERAH",
            "code" => "088"
        ],
        [
            "name" => "BANK HAGA",
            "code" => "089"
        ],
        [
            "name" => "BANK IFI",
            "code" => "093"
        ],
        [
            "name" => "BANK CENTURY, TBK.",
            "code" => "095"
        ],
        [
            "name" => "BANK MAYAPADA",
            "code" => "097"
        ],
        [
            "name" => "BANK JABAR",
            "code" => "110"
        ],
        [
            "name" => "BANK DKI",
            "code" => "111"
        ],
        [
            "name" => "BPD DIY",
            "code" => "112"
        ],
        [
            "name" => "BANK JATENG",
            "code" => "113"
        ],
        [
            "name" => "BANK JATIM",
            "code" => "114"
        ],
        [
            "name" => "BPD JAMBI",
            "code" => "115"
        ],
        [
            "name" => "BPD ACEH",
            "code" => "116"
        ],
        [
            "name" => "BANK SUMUT",
            "code" => "117"
        ],
        [
            "name" => "BANK NAGARI",
            "code" => "118"
        ],
        [
            "name" => "BANK RIAU",
            "code" => "119"
        ],
        [
            "name" => "BANK SUMSEL",
            "code" => "120"
        ],
        [
            "name" => "BANK LAMPUNG",
            "code" => "121"
        ],
        [
            "name" => "BPD KALSEL",
            "code" => "122"
        ],
        [
            "name" => "BPD KALIMANTAN BARAT",
            "code" => "123"
        ],
        [
            "name" => "BPD KALTIM",
            "code" => "124"
        ],
        [
            "name" => "BPD KALTENG",
            "code" => "125"
        ],
        [
            "name" => "BPD SULSEL",
            "code" => "126"
        ],
        [
            "name" => "BANK SULUT",
            "code" => "127"
        ],
        [
            "name" => "BPD NTB",
            "code" => "128"
        ],
        [
            "name" => "BPD BALI",
            "code" => "129"
        ],
        [
            "name" => "BANK NTT",
            "code" => "130"
        ],
        [
            "name" => "BANK MALUKU",
            "code" => "131"
        ],
        [
            "name" => "BPD PAPUA",
            "code" => "132"
        ],
        [
            "name" => "BANK BENGKULU",
            "code" => "133"
        ],
        [
            "name" => "BPD SULAWESI TENGAH",
            "code" => "134"
        ],
        [
            "name" => "BANK SULTRA",
            "code" => "135"
        ],
        [
            "name" => "BANK NUSANTARA PARAHYANGAN",
            "code" => "145"
        ],
        [
            "name" => "BANK SWADESI",
            "code" => "146"
        ],
        [
            "name" => "BANK MUAMALAT",
            "code" => "147"
        ],
        [
            "name" => "BANK MESTIKA",
            "code" => "151"
        ],
        [
            "name" => "BANK METRO EXPRESS",
            "code" => "152"
        ],
        [
            "name" => "BANK SHINTA INDONESIA",
            "code" => "153"
        ],
        [
            "name" => "BANK MASPION",
            "code" => "157"
        ],
        [
            "name" => "BANK HAGAKITA",
            "code" => "159"
        ],
        [
            "name" => "BANK GANESHA",
            "code" => "161"
        ],
        [
            "name" => "BANK WINDU KENTJANA",
            "code" => "162"
        ],
        [
            "name" => "HALIM INDONESIA BANK",
            "code" => "164"
        ],
        [
            "name" => "BANK HARMONI INTERNATIONAL",
            "code" => "166"
        ],
        [
            "name" => "BANK KESAWAN",
            "code" => "167"
        ],
        [
            "name" => "BANK TABUNGAN NEGARA (PERSERO)",
            "code" => "200"
        ],
        [
            "name" => "BANK HIMPUNAN SAUDARA 1906, TBK .",
            "code" => "212"
        ],
        [
            "name" => "BANK TABUNGAN PENSIUNAN NASIONAL",
            "code" => "213"
        ],
        [
            "name" => "BANK SWAGUNA",
            "code" => "405"
        ],
        [
            "name" => "BANK JASA ARTA",
            "code" => "422"
        ],
        [
            "name" => "BANK MEGA",
            "code" => "426"
        ],
        [
            "name" => "BANK JASA JAKARTA",
            "code" => "427"
        ],
        [
            "name" => "BANK BUKOPIN",
            "code" => "441"
        ],
        [
            "name" => "BANK SYARIAH MANDIRI",
            "code" => "451"
        ],
        [
            "name" => "BANK SYARIAH INDONESIA (BSI)",
            "code" => "451"
        ],
        [
            "name" => "BANK BISNIS INTERNASIONAL",
            "code" => "459"
        ],
        [
            "name" => "BANK SRI PARTHA",
            "code" => "466"
        ],
        [
            "name" => "BANK JASA JAKARTA",
            "code" => "472"
        ],
//            [
//                "name" => "BANK BINTANG MANUNGGAL",
//                "code" => "484"
//            ],
        [
            "name" => "BANK BUMIPUTERA",
            "code" => "485"
        ],
        [
            "name" => "BANK NEO COMMERCE", // BANK YUDHA BHAKTI
            "code" => "490"
        ],
        [
            "name" => "BANK MITRANIAGA",
            "code" => "491"
        ],
        [
            "name" => "BANK AGRO NIAGA",
            "code" => "494"
        ],
        [
            "name" => "BANK INDOMONEX",
            "code" => "498"
        ],
        [
            "name" => "BANK ROYAL INDONESIA",
            "code" => "501"
        ],
        [
            "name" => "BANK ALFINDO",
            "code" => "503"
        ],
        [
            "name" => "BANK SYARIAH MEGA",
            "code" => "506"
        ],
        [
            "name" => "BANK INA PERDANA",
            "code" => "513"
        ],
        [
            "name" => "BANK HARFA",
            "code" => "517"
        ],
        [
            "name" => "PRIMA MASTER BANK",
            "code" => "520"
        ],
        [
            "name" => "BANK PERSYARIKATAN INDONESIA",
            "code" => "521"
        ],
        [
            "name" => "BANK AKITA",
            "code" => "525"
        ],
        [
            "name" => "LIMAN INTERNATIONAL BANK",
            "code" => "526"
        ],
        [
            "name" => "ANGLOMAS INTERNASIONAL BANK",
            "code" => "531"
        ],
        [
            "name" => "BANK DIPO INTERNATIONAL",
            "code" => "523"
        ],
        [
            "name" => "BANK KESEJAHTERAAN EKONOMI",
            "code" => "535"
        ],
        [
            "name" => "SEABANK",
            "code" => "535"
        ],
//            [
//                "name" => "BANK UIB",
//                "code" => "536"
//            ],
        [
            "name" => "BANK BCA SYARIAH",
            "code" => "536"
        ],
        [
            "name" => "BANK ARTOS IND",
            "code" => "542"
        ],
        [
            "name" => "BANK PURBA DANARTA",
            "code" => "547"
        ],
        [
            "name" => "BANK MULTI ARTA SENTOSA",
            "code" => "548"
        ],
        [
            "name" => "BANK MAYORA",
            "code" => "553"
        ],
        [
            "name" => "BPD SUMSEL",
            "code" => "120"
        ],
        [
            "name" => "BANK INDEX SELINDO",
            "code" => "555"
        ],
        [
            "name" => "BANK VICTORIA INTERNATIONAL",
            "code" => "566"
        ],
        [
            "name" => "BANK EKSEKUTIF",
            "code" => "558"
        ],
        [
            "name" => "CENTRATAMA NASIONAL BANK",
            "code" => "559"
        ],
        [
            "name" => "BANK FAMA INTERNASIONAL",
            "code" => "562"
        ],
        [
            "name" => "BANK SINAR HARAPAN BALI",
            "code" => "564"
        ],
        [
            "name" => "ALLOBANK",
            "code" => "567"
        ],
        [
            "name" => "BANK FINCONESIA",
            "code" => "945"
        ],
        [
            "name" => "BANK MERINCORP",
            "code" => "946"
        ],
        [
            "name" => "BANK MAYBANK INDOCORP",
            "code" => "947"
        ],
        [
            "name" => "BANK OCBC – INDONESIA",
            "code" => "948"
        ],
        [
            "name" => "BANK CHINA TRUST INDONESIA",
            "code" => "949"
        ],
        [
            "name" => "BANK COMMONWEALTH",
            "code" => "950"
        ],
        [
            "name" => "BANK JAGO",
            "code" => "542"
        ],
        [
            "name" => "WESTPAC BANK",
            "code" => "WPACNZ2W"
        ],
        [
            "name" => "BANK AUSTRALIA BSB",
            "code" => "313 140"
        ],
        [
            "name" => "BANK HSBC",
            "code" => "087"
        ],
        [
            "name" => "BPD JATENG SYARIAH",
            "code" => "725"
        ],
//        [
//            "name" => "VA DANA",
//            "code" => "-"
//        ],
//        [
//            "name" => "VA OVO",
//            "code" => "-"
//        ],
        [
            "name" => "BLUE By BCA",
            "code" => "501"
        ],
        [
            "name" => "BANK SINARMAS",
            "code" => "153"
        ],
        [
            "name" => "BANK KEB HANA INDONESIA",
            "code" => "484"
        ],
    ]
];
