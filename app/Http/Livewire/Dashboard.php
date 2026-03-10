<?php

namespace App\Http\Livewire;

use App\Services\DashboardService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Dashboard extends Component
{
    public function render(DashboardService $dashboardService): View
    {
        return view('livewire.dashboard', $dashboardService->getStatsFor(auth()->user()))
            ->layout('layouts.app')
            ->title('Dashboard');
    }
}
