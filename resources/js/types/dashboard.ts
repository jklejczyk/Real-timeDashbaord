export interface RevenueComparison {
    current: string;
    previous: string;
    changePercent: number;
}

export interface StatusCounts {
    pending: number;
    inProgress: number;
    completed: number;
    delayed: number;
}

export interface WorkerStat {
    workerId: number;
    name: string;
    ordersCount: number;
}

export interface MonthlyRevenue {
    month: string;
    label: string;
    total: string;
}

export interface DailyOrderCount {
    date: string;
    count: number;
}

export interface DashboardFilterValues {
    startDate: string;
    endDate: string;
    workerId: number | null;
    type: string | null;
}

export interface WorkerOption {
    id: number;
    name: string;
}

export interface OrderTypeOption {
    value: string;
    label: string;
}

export interface ActivityItem {
    id: number;
    workerName: string;
    type: string;
    status: string;
    amount: string;
    createdAt: string;
}
