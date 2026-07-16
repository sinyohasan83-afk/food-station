<?php
$appData = [
    'stats' => [
        'gudang' => ['tersedia' => 12, 'total' => 40, 'terisi' => 28],
        'toko'   => ['tersedia' => 5,  'total' => 25, 'terisi' => 20],
        'kantin' => ['tersedia' => 3,  'total' => 10, 'terisi' => 7],
    ],
    'summary' => [
        'total_penyewa'   => 128,
        'revenue_bulan'   => '450.000.000',
        'unit_tersedia'   => 20,
        'kontrak_aktif'   => 55,
    ],
    'penyewa' => [
        ['id'=>1,'nama'=>'Budi Santoso',     'unit'=>'Gudang A1',  'tipe'=>'Gudang','status'=>'Lunas',    'jatuhTempo'=>'2026-07-10','nominal'=>'Rp 8.500.000'],
        ['id'=>2,'nama'=>'Siti Aminah',      'unit'=>'Toko B3',    'tipe'=>'Toko',  'status'=>'Menunggu', 'jatuhTempo'=>'2026-06-20','nominal'=>'Rp 5.200.000'],
        ['id'=>3,'nama'=>'Warung Berkah',    'unit'=>'Kantin G-02','tipe'=>'Kantin','status'=>'Lunas',    'jatuhTempo'=>'2026-08-05','nominal'=>'Rp 3.100.000'],
        ['id'=>4,'nama'=>'CV. Maju Bersama','unit'=>'Gudang C-12', 'tipe'=>'Gudang','status'=>'Lunas',    'jatuhTempo'=>'2026-09-15','nominal'=>'Rp 12.000.000'],
        ['id'=>5,'nama'=>'PT. Logistik Jaya','unit'=>'Gudang D-05','tipe'=>'Gudang','status'=>'Terlambat','jatuhTempo'=>'2026-05-10','nominal'=>'Rp 9.750.000'],
        ['id'=>6,'nama'=>'Toko Makmur',     'unit'=>'Toko A1',    'tipe'=>'Toko',  'status'=>'Lunas',    'jatuhTempo'=>'2026-10-20','nominal'=>'Rp 4.800.000'],
        ['id'=>7,'nama'=>'UD. Sejahtera',   'unit'=>'Toko C5',    'tipe'=>'Toko',  'status'=>'Menunggu', 'jatuhTempo'=>'2026-06-28','nominal'=>'Rp 4.200.000'],
        ['id'=>8,'nama'=>'Kantin Pak Joko', 'unit'=>'Kantin T-01','tipe'=>'Kantin','status'=>'Lunas',    'jatuhTempo'=>'2026-11-01','nominal'=>'Rp 2.500.000'],
    ],
    'aktivitas' => [
        ['icon'=>'check','color'=>'emerald', 'judul'=>'Pembayaran Diterima',    'sub'=>'CV. Maju Bersama — Unit G-102',  'waktu'=>'2 jam lalu'],
        ['icon'=>'box',  'color'=>'indigo',  'judul'=>'Kontrak Sewa Baru',      'sub'=>'PT. Logistik Jaya — Gudang C-12','waktu'=>'Kemarin'],
        ['icon'=>'warn', 'color'=>'amber',   'judul'=>'Peringatan Jatuh Tempo', 'sub'=>'Siti Aminah — Toko B3',          'waktu'=>'3 hari lalu'],
        ['icon'=>'renew','color'=>'sky',     'judul'=>'Perpanjangan Kontrak',   'sub'=>'Warung Berkah — Kantin G-02',    'waktu'=>'1 minggu lalu'],
    ],
    'units' => [
        'gudang' => [
            ['id'=>'G-101','nama'=>'Gudang A1', 'luas'=>'120 m²','harga'=>'Rp 8.500.000','status'=>'Terisi','penyewa'=>'Budi Santoso'],
            ['id'=>'G-102','nama'=>'Gudang A2', 'luas'=>'120 m²','harga'=>'Rp 8.500.000','status'=>'Kosong','penyewa'=>''],
            ['id'=>'G-103','nama'=>'Gudang A3', 'luas'=>'150 m²','harga'=>'Rp 10.000.000','status'=>'Terisi','penyewa'=>'CV. Maju Bersama'],
            ['id'=>'G-104','nama'=>'Gudang B1', 'luas'=>'200 m²','harga'=>'Rp 14.000.000','status'=>'Kosong','penyewa'=>''],
            ['id'=>'G-105','nama'=>'Gudang B2', 'luas'=>'200 m²','harga'=>'Rp 14.000.000','status'=>'Terisi','penyewa'=>'PT. Logistik Jaya'],
            ['id'=>'G-106','nama'=>'Gudang C1', 'luas'=>'80 m²', 'harga'=>'Rp 6.000.000','status'=>'Kosong','penyewa'=>''],
            ['id'=>'G-107','nama'=>'Gudang C2', 'luas'=>'80 m²', 'harga'=>'Rp 6.000.000','status'=>'Terisi','penyewa'=>'UD. Surya'],
            ['id'=>'G-108','nama'=>'Gudang D1', 'luas'=>'250 m²','harga'=>'Rp 18.000.000','status'=>'Kosong','penyewa'=>''],
        ],
        'toko' => [
            ['id'=>'T-201','nama'=>'Toko A1','luas'=>'30 m²','harga'=>'Rp 4.800.000','status'=>'Terisi','penyewa'=>'Toko Makmur'],
            ['id'=>'T-202','nama'=>'Toko A2','luas'=>'30 m²','harga'=>'Rp 4.800.000','status'=>'Kosong','penyewa'=>''],
            ['id'=>'T-203','nama'=>'Toko B1','luas'=>'45 m²','harga'=>'Rp 6.500.000','status'=>'Terisi','penyewa'=>'Siti Aminah'],
            ['id'=>'T-204','nama'=>'Toko B2','luas'=>'45 m²','harga'=>'Rp 6.500.000','status'=>'Kosong','penyewa'=>''],
            ['id'=>'T-205','nama'=>'Toko C1','luas'=>'60 m²','harga'=>'Rp 8.000.000','status'=>'Terisi','penyewa'=>'UD. Sejahtera'],
            ['id'=>'T-206','nama'=>'Toko C2','luas'=>'60 m²','harga'=>'Rp 8.000.000','status'=>'Kosong','penyewa'=>''],
        ],
        'kantin' => [
            ['id'=>'K-301','nama'=>'Kantin G-01','luas'=>'20 m²','harga'=>'Rp 2.500.000','status'=>'Terisi','penyewa'=>'Warung Berkah'],
            ['id'=>'K-302','nama'=>'Kantin G-02','luas'=>'20 m²','harga'=>'Rp 2.500.000','status'=>'Terisi','penyewa'=>'Kantin Pak Joko'],
            ['id'=>'K-303','nama'=>'Kantin T-01','luas'=>'25 m²','harga'=>'Rp 3.000.000','status'=>'Kosong','penyewa'=>''],
            ['id'=>'K-304','nama'=>'Kantin T-02','luas'=>'25 m²','harga'=>'Rp 3.000.000','status'=>'Terisi','penyewa'=>'Dapur Bu Dewi'],
        ],
    ],
];
