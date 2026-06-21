<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Worker;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DashboardSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $workers = Worker::factory(15)->create();
        Worker::factory(3)->inactive()->create();

        $workers->each(function (Worker $worker): void {
            Order::factory(40)->for($worker)->completed()->create();
            Order::factory(8)->for($worker)->inProgress()->create();
            Order::factory(5)->for($worker)->create();
        });

        Order::factory(15)->recycle($workers)->delayed()->create();

        Order::factory(30)->recycle($workers)->recent()->completed()->create();
        Order::factory(12)->recycle($workers)->recent()->inProgress()->create();
        Order::factory(10)->recycle($workers)->recent()->create();
    }
}
