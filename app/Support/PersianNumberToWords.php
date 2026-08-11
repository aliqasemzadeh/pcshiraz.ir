<?php

namespace App\Support;

class PersianNumberToWords
{
    private const ONES = [
        '',
        'یک',
        'دو',
        'سه',
        'چهار',
        'پنج',
        'شش',
        'هفت',
        'هشت',
        'نه',
        'ده',
        'یازده',
        'دوازده',
        'سیزده',
        'چهارده',
        'پانزده',
        'شانزده',
        'هفده',
        'هجده',
        'نوزده',
    ];

    private const TENS = [
        '',
        '',
        'بیست',
        'سی',
        'چهل',
        'پنجاه',
        'شصت',
        'هفتاد',
        'هشتاد',
        'نود',
    ];

    private const HUNDREDS = [
        '',
        'صد',
        'دویست',
        'سیصد',
        'چهارصد',
        'پانصد',
        'ششصد',
        'هفتصد',
        'هشتصد',
        'نهصد',
    ];

    private const SCALES = [
        '',
        'هزار',
        'میلیون',
        'میلیارد',
        'تریلیون',
    ];

    public static function convert(int|string|float $amount): string
    {
        $number = (int) round((float) $amount);

        if ($number < 0) {
            return 'منفی '.self::convert(abs($number));
        }

        if ($number === 0) {
            return 'صفر';
        }

        $parts = [];
        $scaleIndex = 0;

        while ($number > 0) {
            $chunk = $number % 1000;

            if ($chunk > 0) {
                $chunkWords = self::convertBelowThousand($chunk);
                $scale = self::SCALES[$scaleIndex] ?? '';
                $parts[] = $scale !== '' ? $chunkWords.' '.$scale : $chunkWords;
            }

            $number = intdiv($number, 1000);
            $scaleIndex++;
        }

        return implode(' و ', array_reverse($parts));
    }

    private static function convertBelowThousand(int $number): string
    {
        $parts = [];

        $hundreds = intdiv($number, 100);
        $remainder = $number % 100;

        if ($hundreds > 0) {
            $parts[] = self::HUNDREDS[$hundreds];
        }

        if ($remainder > 0) {
            if ($remainder < 20) {
                $parts[] = self::ONES[$remainder];
            } else {
                $tens = intdiv($remainder, 10);
                $ones = $remainder % 10;
                $tensWords = self::TENS[$tens];

                if ($ones > 0) {
                    $parts[] = $tensWords.' و '.self::ONES[$ones];
                } else {
                    $parts[] = $tensWords;
                }
            }
        }

        return implode(' و ', $parts);
    }
}
