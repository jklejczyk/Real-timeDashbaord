import {
    ArcElement,
    BarElement,
    CategoryScale,
    Chart as ChartJS,
    Filler,
    Legend,
    LinearScale,
    LineElement,
    PointElement,
    Tooltip,
} from 'chart.js';

let registered = false;

/**
 * Register only the Chart.js building blocks the dashboard uses.
 * Idempotent so chart components can call it freely on setup.
 */
export function registerCharts(): void {
    if (registered) {
        return;
    }

    ChartJS.register(
        ArcElement,
        BarElement,
        LineElement,
        PointElement,
        CategoryScale,
        LinearScale,
        Filler,
        Legend,
        Tooltip,
    );

    registered = true;
}
