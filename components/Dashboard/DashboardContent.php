<?php

namespace components\Dashboard;

interface DashboardContent {
    public function setDashboard(Dashboard $dashboard): static;
}