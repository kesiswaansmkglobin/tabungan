<?php

namespace App\Services;

use App\Models\Student;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    private string $driver;

    private ?string $apiKey;

    private ?string $apiUrl;

    public function __construct()
    {
        $this->driver = config('whatsapp.driver', 'log');
        $this->apiKey = config('whatsapp.api_key');
        $this->apiUrl = config('whatsapp.api_url');
    }

    public function sendTransactionNotification(Student $student, string $type, int $amount, int $balance): void
    {
        $message = $this->buildMessage($student, $type, $amount, $balance);

        $this->send($student, $message);
    }

    private function buildMessage(Student $student, string $type, int $amount, int $balance): string
    {
        $typeLabel = $type === 'setor' ? 'SETORAN' : 'PENARIKAN';
        $sign = $type === 'setor' ? '+' : '-';
        $formattedAmount = number_format($amount, 0, ',', '.');
        $formattedBalance = number_format($balance, 0, ',', '.');
        $kelas = $student->class?->name ?? '-';

        $quotes = [
            'Menabung hari ini, panen mimpi esok hari.',
            'Setiap rupiah yang kamu tabung adalah langkah menuju masa depan cerah.',
            'Kebiasaan kecil hari ini, kesuksesan besar di masa depan.',
            'Orang sukses bukan mereka yang selalu menang, tapi mereka yang tidak pernah menyerah menabung.',
            'Rejeki tidak datang tiba-tiba, tapi dikumpulkan sedikit demi sedikit.',
            'Masa depan dimiliki oleh mereka yang percaya pada mimpi dan menabung untuk mewujudkannya.',
            'Bukan seberapa besar penghasilanmu, tapi seberapa baik kamu mengelolanya.',
            'Tabunganmu hari ini adalah senyum bahagia keluargamu nanti.',
            'Jangan menunggu kaya untuk menabung, menabunglah agar menjadi kaya.',
            'Disiplin menabung adalah investasi paling berharga untuk dirimu sendiri.',
        ];
        $quote = $quotes[array_rand($quotes)];

        return "Tabungan Siswa SMK Globin\n"
            ."\n"
            ."Nama: {$student->name}\n"
            ."NIS: {$student->nis}\n"
            ."Kelas: {$kelas}\n"
            ."Jenis Transaksi: {$typeLabel}\n"
            ."Jumlah Transaksi: Rp{$sign}{$formattedAmount}\n"
            ."Saldo Akhir: Rp{$formattedBalance}\n"
            ."\n"
            ."Terima kasih\n"
            ."\n"
            ."*_{$quote}_*";
    }

    private function send(Student $student, string $message): void
    {
        if (! $student->phone) {
            Log::info('[WhatsApp] Nomor telepon tidak tersedia, notifikasi dilewati', ['student_id' => $student->id]);

            return;
        }

        $target = $this->normalizePhone($student->phone);

        if (! $this->isValidPhone($target)) {
            Log::warning('[WhatsApp] Nomor tidak valid', [
                'student_id' => $student->id,
                'target' => $target,
            ]);

            return;
        }

        if ($this->driver === 'log') {
            Log::info('[WhatsApp Notification]', [
                'to' => $target,
                'message' => $message,
            ]);

            return;
        }

        if ($this->driver !== 'fonnte' || ! $this->apiKey || ! $this->apiUrl) {
            Log::warning('[WhatsApp] Driver atau API key tidak dikonfigurasi');

            return;
        }

        try {
            $response = Http::withoutVerifying()->asJson()->withHeaders([
                'Authorization' => $this->apiKey,
            ])->post($this->apiUrl, [
                'target' => $target,
                'message' => $message,
                'countryCode' => '62',
            ]);

            if ($response->successful()) {
                Log::info('[WhatsApp] Berhasil dikirim', [
                    'to' => $target,
                    'response' => $response->body(),
                ]);
            } else {
                Log::error('[WhatsApp] Gagal dikirim', [
                    'to' => $target,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('[WhatsApp] Exception: '.$e->getMessage(), [
                'to' => $target,
            ]);
        }
    }

    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phone) > 0 && $phone[0] === '0') {
            $phone = '62'.substr($phone, 1);
        }

        return $phone;
    }

    private function isValidPhone(string $phone): bool
    {
        return strlen($phone) >= 11 && strlen($phone) <= 15;
    }
}
