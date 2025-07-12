<?php

namespace App\Filament\Widgets;

use App\Models\Client;
use App\Models\Milestone;
use App\Models\Payment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    protected function getStats(): array
    {
        return [
            Stat::make('Earning', Payment::sum('amount')),
            Stat::make('Pending Payment', Milestone::query()->where('is_paid', false)->sum('amount')),
            Stat::make('Total Clients', Client::count()),
        ];
    }
}
