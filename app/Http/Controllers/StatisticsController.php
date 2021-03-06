<?php

namespace App\Http\Controllers;

use App\Concert;
use App\Release;
use App\Services\ConcertMetricsService;

class StatisticsController extends Controller
{
    /**
     * Show the statistics for all concerts.
     *
     * @return View
     */
    public function index()
    {
        return view('index');
    }

    /**
     * Show the statistics for the specified concert.
     *
     * @return View
     */
    public function concert(Concert $concert)
    {
        $concertMetrics = new ConcertMetricsService($concert);

        return view('concert', compact('concert', 'concertMetrics'));
    }

    /**
     * Show the details for the specified release.
     *
     * @return View
     */
    public function release(Release $release)
    {
        return view('release', compact('release'));
    }
}
