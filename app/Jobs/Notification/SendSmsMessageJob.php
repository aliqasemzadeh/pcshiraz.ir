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
     * Normalize Iranian mobile to start with 98 while preserving original.
     * Returns [original, normalized].
     */
    private function normalizeIranMobile(string $mobile): array
    {
        $original = trim($mobile);

        // Remove spaces, dashes and plus sign
        $m = preg_replace('/[^0-9+]/', '', $original) ?? $original;

        // Convert leading +98 to 98
        if (str_starts_with($m, '+98')) {
            $m = substr($m, 1); // remove +
        }

        // If starts with 0, replace with 98
        if (str_starts_with($m, '0')) {
            $m = '98'.substr($m, 1);
        }

        // If already starts with 98, keep
        if (str_starts_with($m, '98')) {
            return [$original, $m];
        }

        // If starts with 9 and looks like 9xxxxxxxxx, prepend 98
        if (str_starts_with($m, '9') && strlen($m) >= 10) {
            $m = '98'.$m;
        }

        return [$original, $m];
    }

    /**
     * Send SMS via پنل پیامک ستارگان.
     */
    private function sendViaSetaregan(string $normalizedTo, string $text, string $originalTo): void
    {
        try {
            $response = Http::withToken((string) Config::get('sms.token'))
                ->acceptJson()
                ->asJson()
                ->post((string) Config::get('sms.url'), [
                    'to' => $normalizedTo,
                    'message' => $text,
                    'gateway' => (string) Config::get('sms.gateway'),
                ]);

            $responseData = $response->json();

            Log::info('SMS send attempt via Setaregan', [
                'to' => $normalizedTo,
                'original' => $originalTo,
                'message' => $text,
                'http_status' => $response->status(),
                'response' => $responseData,
            ]);

            $ok = (bool) ($responseData['ok'] ?? false);
            $code = $responseData['code'] ?? null;

            if (! $response->successful() || ! $ok || $code !== 'queued') {
                Log::error('Send SMS Error: '.($responseData['message'] ?? 'unknown error'), [
                    'code' => $code,
                    'http_status' => $response->status(),
                ]);
            }
        } catch (Throwable $e) {
            Log::error('Failed to send SMS: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
