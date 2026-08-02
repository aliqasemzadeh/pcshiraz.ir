<?php

namespace App\Jobs\Notification;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendSmsMessageJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $mobile,
        public string $text
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        [$originalMobile, $normalizedMobile] = $this->normalizeIranMobile($this->mobile);

        $this->sendViaSetaregan($normalizedMobile, $this->text, $originalMobile);
    }

    /**
     * Normalize Iranian mobile to 09xxxxxxxxx for پنل پیامک ستارگان.
     * Returns [original, normalized].
     */
    private function normalizeIranMobile(string $mobile): array
    {
        $original = trim($mobile);

        $m = preg_replace('/\D+/', '', $original) ?? $original;

        // +98 / 98 → strip country code
        if (str_starts_with($m, '98') && strlen($m) >= 12) {
            $m = substr($m, 2);
        }

        // 9xxxxxxxxx → 09xxxxxxxxx
        if (str_starts_with($m, '9') && strlen($m) === 10) {
            $m = '0'.$m;
        }

        return [$original, $m];
    }

    /**
     * Send SMS via پنل پیامک ستارگان (https://srscrm.ir/api/sms/send).
     */
    private function sendViaSetaregan(string $normalizedTo, string $text, string $originalTo): void
    {
        try {
            $response = Http::timeout(30)
                ->connectTimeout(10)
                ->withToken((string) Config::get('sms.token'))
                ->acceptJson()
                ->asJson()
                ->post((string) Config::get('sms.url'), [
                    'to' => $normalizedTo,
                    'message' => $text,
                    'gateway' => (string) Config::get('sms.gateway'),
                ]);

            $responseData = $response->json() ?? [];
            $ok = (bool) ($responseData['ok'] ?? false);
            $code = $responseData['code'] ?? null;
            $message = $responseData['message'] ?? 'unknown error';

            Log::info('SMS send attempt via Setaregan', [
                'to' => $normalizedTo,
                'original' => $originalTo,
                'http_status' => $response->status(),
                'code' => $code,
                'response' => $responseData,
            ]);

            if (! $response->successful() || ! $ok || $code !== 'queued') {
                Log::error('Send SMS Error: '.$message, [
                    'code' => $code,
                    'http_status' => $response->status(),
                    'to' => $normalizedTo,
                ]);
            }
        } catch (Throwable $e) {
            Log::error('Failed to send SMS: '.$e->getMessage(), [
                'to' => $normalizedTo,
                'original' => $originalTo,
            ]);
        }
    }
}
