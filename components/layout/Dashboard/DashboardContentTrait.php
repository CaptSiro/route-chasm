<?php

namespace components\layout\Dashboard;

trait DashboardContentTrait {
    protected Dashboard $dashboard;

    public function setDashboard(Dashboard $dashboard): static {
        $this->dashboard = $dashboard;
        return $this;
    }
}