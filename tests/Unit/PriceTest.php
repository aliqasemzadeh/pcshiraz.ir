<?php

namespace Tests\Unit;

use App\Enums\PriceUnitEnum;
use App\Settings\GeneralSettings;
use App\Support\Price;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PriceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('fa');
    }

    #[Test]
    public function it_formats_toman_amounts_without_conversion(): void
    {
        $this->setPriceUnit(PriceUnitEnum::Toman);

        $this->assertSame(12500000.0, Price::toDisplay(12500000));
        $this->assertSame(12500000.0, Price::fromDisplay(12500000));
        $this->assertSame('12,500,000 تومان', format_price(12500000));
        $this->assertSame('12,500,000', format_price_number(12500000));
        $this->assertSame('تومان', price_unit_label());
    }

    #[Test]
    public function it_converts_display_amounts_when_unit_is_rial(): void
    {
        $this->setPriceUnit(PriceUnitEnum::Rial);

        $this->assertSame(125000000.0, Price::toDisplay(12500000));
        $this->assertSame(12500000.0, Price::fromDisplay(125000000));
        $this->assertSame('125,000,000 ریال', format_price(12500000));
        $this->assertSame('ریال', price_unit_label());
        $this->assertStringContainsString('ریال', price_in_words(12500000));
    }

    #[Test]
    public function it_unmasks_thousands_separators_before_converting_from_display(): void
    {
        $this->setPriceUnit(PriceUnitEnum::Toman);

        $this->assertSame('12500000', Price::unmask('12,500,000'));
        $this->assertSame('12500000', Price::unmask('12 500 000'));
        $this->assertSame(12500000.0, Price::fromDisplay('12,500,000'));
    }

    #[Test]
    public function it_unmasks_rial_display_amounts_before_converting_to_toman(): void
    {
        $this->setPriceUnit(PriceUnitEnum::Rial);

        $this->assertSame(12500000.0, Price::fromDisplay('125,000,000'));
    }

    protected function setPriceUnit(PriceUnitEnum $unit): void
    {
        $settings = app(GeneralSettings::class);
        $settings->price_unit = $unit->value;
        $settings->save();
    }
}
