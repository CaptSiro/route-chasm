<?php

namespace components\layout\Dashboard;

interface DashboardContent {
    public function setDashboard(Dashboard $dashboard): static;
}