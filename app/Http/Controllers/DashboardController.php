<?php

namespace App\Http\Controllers;

class DashboardController extends BaseController
{
    public function index()
    {
        return view('dashboard.index', $this->shareLayout([
            'title'      => 'Dashboard',
            'breadcrumb' => [],
        ]));
    }
}
