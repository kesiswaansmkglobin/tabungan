<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Rekap Saldo Tabungan Siswa</title>
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
        <div class="subtitle">REKAP TABUNGAN SISWA</div>
        <p>Per: {{ now()->format('d/m/Y') }} | Total Siswa: {{ $students->count() }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:5%">No</th>
                <th style="width:12%">NIS</th>
                <th>Nama Siswa</th>
                <th style="width:12%">Kelas</th>
                <th style="width:20%">Saldo Akhir</th>
            </tr>
        </thead>
        <tbody>
            @forelse($students as $index => $s)
            <tr>
                <td class="tengah">{{ $index + 1 }}</td>
                <td class="tengah">{{ $s->nis }}</td>
                <td>{{ $s->name }}</td>
                <td class="tengah">{{ $s->class?->name ?? '-' }}</td>
                <td class="nominal"><span class="rp">Rp</span>{{ number_format($s->balance, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr><td colspan="5" style="text-align:center">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="total-bar">
        Total Saldo: Rp{{ number_format($students->sum('balance'), 0, ',', '.') }}
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
