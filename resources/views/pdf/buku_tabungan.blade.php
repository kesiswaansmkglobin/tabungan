<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Buku Tabungan - {{ $student->name }}</title>
    <style>
        @page { margin: 15px; }
        body { font-family: sans-serif; font-size: 8pt; }
        .header { text-align: center; margin-bottom: 12px; border-bottom: 2px solid #d4a520; padding-bottom: 8px; }
        .header h1 { margin: 0; font-size: 14pt; color: #1e3a5f; }
        .header .sub { color: #555; font-size: 8pt; margin: 2px 0; }
        .student-info { margin-bottom: 10px; }
        .student-info table { width: 100%; }
        .student-info td { padding: 2px 4px; font-size: 9pt; }
        .student-info .label { font-weight: bold; width: 100px; color: #1e3a5f; }
        table.detail { width: 100%; border-collapse: collapse; }
        table.detail th { background: #1e3a5f; color: white; padding: 4px 6px; text-align: left; font-size: 8pt; }
        table.detail td { border: 1px solid #ccc; padding: 3px 6px; font-size: 8pt; }
        table.detail tr:nth-child(even) { background: #f9f9f9; }
        .saldo-akhir { margin-top: 10px; padding: 8px; background: linear-gradient(135deg, #1e3a5f, #d4a520); color: white; text-align: center; border-radius: 4px; font-weight: bold; font-size: 11pt; }
        .footer { margin-top: 12px; text-align: center; font-size: 7pt; color: #888; border-top: 1px solid #ccc; padding-top: 6px; }
        @if($school && $school->signature_path)
        .signature { text-align: right; margin-top: 20px; font-size: 9pt; }
        .signature img { height: 50px; }
        @endif
        .page-number { text-align: center; font-size: 7pt; color: #aaa; margin-top: 8px; }
        .total-label { margin-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        @if($school)
        <h1>{{ $school->name }}</h1>
        <div class="sub">Buku Tabungan Siswa</div>
        @else
        <h1>Buku Tabungan Siswa</h1>
        @endif
    </div>

    <div class="student-info">
        <table>
            <tr><td class="label">NIS</td><td>: {{ $student->nis }}</td></tr>
            <tr><td class="label">Nama</td><td>: {{ $student->name }}</td></tr>
            <tr><td class="label">Kelas</td><td>: {{ $student->class?->name ?? '-' }}</td></tr>
        </table>
    </div>

    <table class="detail">
        <thead>
            <tr>
                <th style="width:60px">Tanggal</th>
                <th>Keterangan</th>
                <th style="width:70px">Setoran</th>
                <th style="width:70px">Penarikan</th>
                <th style="width:70px">Saldo</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $t)
            <tr>
                <td>{{ $t->transaction_date->format('d/m/Y') }}</td>
                <td>{{ $t->note ?? ($t->type === 'setor' ? 'Setoran' : 'Penarikan') }}</td>
                <td style="text-align:right">{{ $t->type === 'setor' ? 'Rp '.number_format($t->amount,0,',','.') : '-' }}</td>
                <td style="text-align:right">{{ $t->type === 'tarik' ? 'Rp '.number_format($t->amount,0,',','.') : '-' }}</td>
                <td style="text-align:right;font-weight:bold">Rp {{ number_format($t->balance_after,0,',','.') }}</td>
            </tr>
            @empty
            <tr><td colspan="5" style="text-align:center">Belum ada transaksi.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="saldo-akhir">
        Saldo Akhir: Rp {{ number_format($student->balance, 0, ',', '.') }}
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
                    @if($sigData)<p><img src="data:image/png;base64,{{ $sigData }}" style="height:50px;" alt="Tanda Tangan" /></p>@endif
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
                        @if($treasData)<p><img src="data:image/png;base64,{{ $treasData }}" style="height:50px;" alt="Tanda Tangan" /></p>@endif
                    @endif
                    <p>{{ $school->treasurer_name }}</p>
                @endif
            </td>
        </tr>
    </table>

    <div class="footer">
        Dicetak {{ now()->format('d/m/Y H:i') }} | Sistem Tabungan Siswa SMK Globin
    </div>
</body>
</html>
