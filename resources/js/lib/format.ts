const currencyFormatter = new Intl.NumberFormat('pl-PL', {
    style: 'currency',
    currency: 'PLN',
    maximumFractionDigits: 0,
});

const numberFormatter = new Intl.NumberFormat('pl-PL');

export function formatCurrency(value: string | number): string {
    return currencyFormatter.format(
        typeof value === 'string' ? Number(value) : value,
    );
}

export function formatNumber(value: number): string {
    return numberFormatter.format(value);
}

/**
 * Short day label for chart axes: '2026-06-18' -> '18.06'.
 */
export function formatDayLabel(isoDate: string): string {
    const [, month, day] = isoDate.split('-');

    return `${day}.${month}`;
}
