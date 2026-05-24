<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Transaksi</title>
    <style>
        body { font-family: sans-serif; font-size: 10pt; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 16pt; }
        .header .subtitle { margin: 2px 0; font-size: 12pt; font-weight: bold; }
        .header p { margin: 2px 0; color: #555; font-size: 9pt; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 4px 6px; font-size: 9pt; }
        th { background: #1e3a5f; color: white; text-align: center; }
        td.nominal { text-align: right; white-space: nowrap; }
        td.nominal span.rp { float: left; margin-right: 2px; }
        td.tengah { text-align: center; }
        .footer { margin-top: 20px; text-align: center; font-size: 8pt; color: #888; }
        .total-bar { margin-top: 5px; padding: 6px; background: #f0f0f0; text-align: right; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $school && $school->name ? $school->name : 'Sistem Tabungan Siswa' }}</h1>
        <div class="subtitle">LAPORAN TRANSAKSI TABUNGAN SISWA</div>
        <p>Periode: {{ $filters['date_from'] ?? 'Awal' }} s/d {{ $filters['date_to'] ?? 'Sekarang' }}</p>
        <p>Total Transaksi: {{ $transactions->count() }} | Dicetak: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:5%">No</th>
                <th style="width:13%">Tanggal</th>
                <th style="width:10%">NIS</th>
                <th>Nama</th>
                <th style="width:10%">Jenis</th>
                <th style="width:16%">Jumlah</th>
                <th style="width:16%">Saldo</th>
                <th>Petugas</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $index => $t)
            <tr>
                <td class="tengah">{{ $index + 1 }}</td>
                <td class="tengah">{{ $t->transaction_date instanceof \Carbon\Carbon ? $t->transaction_date->format('d/m/Y') : \Carbon\Carbon::parse($t->transaction_date)->format('d/m/Y') }}</td>
                <td class="tengah">{{ $t->student->nis ?? '-' }}</td>
                <td>{{ $t->student->name ?? '-' }}</td>
                <td class="tengah">{{ $t->type === 'setor' ? 'Setoran' : 'Penarikan' }}</td>
                <td class="nominal"><span class="rp">Rp</span>{{ number_format($t->amount, 0, ',', '.') }}</td>
                <td class="nominal"><span class="rp">Rp</span>{{ number_format($t->balance_after, 0, ',', '.') }}</td>
                <td>{{ $t->createdBy->name ?? '-' }}</td>
            </tr>
            @empty
            <tr><td colspan="8" style="text-align:center">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="total-bar">
        Total Setoran: <span class="rp">Rp</span>{{ number_format($transactions->where('type','setor')->sum('amount'), 0, ',', '.') }} |
        Total Penarikan: <span class="rp">Rp</span>{{ number_format($transactions->where('type','tarik')->sum('amount'), 0, ',', '.') }}
    </div>

    <div style="margin-top:20px; text-align:center;">
        <p>Mengetahui,</p>
    </div>

    <table style="width:100%; border:none;">
        <tr>
            <td style="border:none; text-align:center; width:50%; vertical-align:top;">
                @if($school && $school->headmaster_name)
                    <p>Kepala Sekolah</p>
                    <br><br><br>
                    @if($school->signature_path)
                        @php $sigPath = storage_path('app/public/'.$school->signature_path); $sigData = file_exists($sigPath) ? base64_encode(file_get_contents($sigPath)) : ''; @endphp
                        @if($sigData)<p><img src="data:image/png;base64,{{ $sigData }}" style="height:50px;" /></p>@endif
                    @endif
                    <p>{{ $school->headmaster_name }}</p>
                @endif
            </td>
            <td style="border:none; text-align:center; width:50%; vertical-align:top;">
                @if($school && $school->treasurer_name)
                    <p>Bendahara,</p>
                    <br><br><br>
                    @if($school->treasurer_signature_path)
                        @php $treasPath = storage_path('app/public/'.$school->treasurer_signature_path); $treasData = file_exists($treasPath) ? base64_encode(file_get_contents($treasPath)) : ''; @endphp
                        @if($treasData)<p><img src="data:image/png;base64,{{ $treasData }}" style="height:50px;" /></p>@endif
                    @endif
                    <p>{{ $school->treasurer_name }}</p>
                @endif
            </td>
        </tr>
    </table>

    <div class="footer">
        Sistem Tabungan Siswa &mdash; Dicetak {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>
