<?php

declare(strict_types=1);

namespace Crumbls\SubscriptionsFilament\Support;

use NumberFormatter;

class CurrencyFormatter
{
    /**
     * @return array<string, array{code: string, name: string, symbol: string, label: string, decimals: int}>
     */
    public function getCurrencies(): array
    {
        return [
            'USD' => $this->currency('USD', 'US Dollar', '$'),
            'EUR' => $this->currency('EUR', 'Euro', 'EUR'),
            'GBP' => $this->currency('GBP', 'British Pound', 'GBP'),
            'CAD' => $this->currency('CAD', 'Canadian Dollar', 'CAD'),
            'AUD' => $this->currency('AUD', 'Australian Dollar', 'AUD'),
            'JPY' => $this->currency('JPY', 'Japanese Yen', 'JPY', 0),
        ];
    }

    public function symbol(string $code): string
    {
        $code = $this->normalizeCode($code);

        return $this->getCurrencies()[$code]['symbol'] ?? $code;
    }

    public function step(string $code): float|int
    {
        $decimals = $this->decimals($code);

        return $decimals === 0 ? 1 : 1 / (10 ** $decimals);
    }

    public function decimals(string $code): int
    {
        $code = $this->normalizeCode($code);

        return $this->getCurrencies()[$code]['decimals'] ?? 2;
    }

    public function normalizeDecimal(int|float|string|null $amount, string $code): string
    {
        if ($amount === null || $amount === '') {
            $amount = 0;
        }

        if (is_string($amount)) {
            $amount = str_replace(',', '.', $amount);
        }

        return number_format((float) $amount, $this->decimals($code), '.', '');
    }

    public function format(int|float|string|null $amount, string $code, ?string $locale = null): string
    {
        $code = $this->normalizeCode($code);
        $amount = (float) $this->normalizeDecimal($amount, $code);

        if (class_exists(NumberFormatter::class)) {
            $formatter = new NumberFormatter($locale ?? app()->getLocale(), NumberFormatter::CURRENCY);
            $formatted = $formatter->formatCurrency($amount, $code);

            if ($formatted !== false) {
                return $formatted;
            }
        }

        return "{$this->symbol($code)} " . number_format($amount, $this->decimals($code));
    }

    /**
     * @return array{code: string, name: string, symbol: string, label: string, decimals: int}
     */
    private function currency(string $code, string $name, string $symbol, int $decimals = 2): array
    {
        return [
            'code' => $code,
            'name' => $name,
            'symbol' => $symbol,
            'label' => "{$name} ({$code})",
            'decimals' => $decimals,
        ];
    }

    private function normalizeCode(string $code): string
    {
        return strtoupper($code ?: 'USD');
    }
}
