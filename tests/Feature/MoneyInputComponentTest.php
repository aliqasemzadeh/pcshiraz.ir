<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MoneyInputComponentTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function money_input_renders_alpine_mask_without_escaped_dollars(): void
    {
        $html = Blade::render('<x-input money label="Price" />');

        $this->assertStringContainsString('$money($input', $html);
        $this->assertStringContainsString('$el.dispatchEvent(new Event', $html);
        $this->assertStringNotContainsString('\$money', $html);
        $this->assertStringNotContainsString('\$el', $html);
        $this->assertStringContainsString('x-mask:dynamic', $html);
    }
}
