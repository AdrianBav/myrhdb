<?php

namespace App\Services;

use App\Concert;
use App\Perform;
use App\Release;
use Illuminate\Support\Facades\Cache;

class MetricsService
{
    /**
     * Number of concerts attented.
     *
     * @return  integer
     */
    public function concertCount()
    {
        return Cache::rememberForever('concertCount', function () {
            return Concert::count();
        });
    }

    /**
     * List of countries visited.
     *
     * @return  array
     */
    public function concertCountries()
    {
        return Cache::rememberForever('concertCountries', function () {
            return Concert::selectRaw('COUNT(*) AS total')->selectRaw('country')
                ->groupBy('country')
                ->orderBy('total', 'desc')
                ->get();
        });
    }

    /**
     * Count the number of distinct countires.
     *
     * @return  integer
     */
    public function concertCountryCount()
    {
        return Cache::rememberForever('concertCountryCount', function () {
            return Concert::distinct('country')->count('country');
        });
    }

    /**
     * Most concerts attended in the same year.
     *
     * @return  string
     */
    public function mostConcertsInYear()
    {
        $most = Cache::rememberForever('mostConcertsInYear', function () {
            return Concert::selectRaw('COUNT(*) AS total')
                ->selectRaw('YEAR(date) AS year')
                ->groupBy('year')
                ->orderBy('total', 'desc')
                ->first();
        });

        return sprintf('%s (%s)', $most['total'], $most['year']);
    }

    /**
     * List of min, max and average song counts.
     *
     * @return  string
     */
    public function averageSongCount()
    {
        $concertSongs = Cache::rememberForever('averageSongCount', function () {
            return Concert::withCount('setlist')->pluck('setlist_count');
        });

        return sprintf('Min: %s, Avg: %s, Max: %s',
            $concertSongs->min(),
            round($concertSongs->avg()),
            $concertSongs->max()
        );
    }

    /**
     * Get the album coverage data in bar chart format.
     *
     * @return  array
     */
    public function albumCoverageChart()
    {
        $albumPercentages = Cache::rememberForever('albumCoverageChart', function () {
            return $this->albumPercentages();
        });

        return [
            'labels' => $albumPercentages->pluck('title'),
            'datasets' => [
                [
                    'label' => '% of album played',
                    'data' => $albumPercentages->pluck('percentage'),
                    'backgroundColor' => Release::albumChartColors(),
                ]
            ]
        ];
    }

    /**
     * Percentage of tracks played on each album.
     *
     * @return  Collection
     */
    private function albumPercentages()
    {
        $allSongsPerformed = $this->allSongsPerformed()->unique();

        return Cache::rememberForever('albumPercentages', function () use ($allSongsPerformed) {
            return Release::where('isAlbum', true)
                ->get()
                ->map(function ($album) use ($allSongsPerformed) {
                    return $this->calculatePercentage($album, $allSongsPerformed);
                })
                ->sortByDesc('percentage');
        });
    }

    /**
     * Calculate the percentage of album songs played.
     *
     * @param   App\Release  $album
     * @param   Collection   $allSongsPerformed
     * @return  array
     */
    private function calculatePercentage($album, $allSongsPerformed)
    {
        $albumSongs = $album->songs->pluck('title');
        $songsPlayed = $albumSongs->intersect($allSongsPerformed);

        $percentagePlayed = round(($songsPlayed->count() / $albumSongs->count()) * 100);

        return [
            'title' => $album->title,
            'percentage' => $percentagePlayed
        ];
    }

    /**
     * Percentage of tracks played on each album formatted for display in a pie chart.
     *
     * @return  Collection
     */
    public function albumDistributionChart()
    {
        $albumPercentages = Cache::rememberForever('albumDistributionChart', function () {
            return $this->albumDistribution();
        });

        return [
            'datasets' => [
                [
                    'backgroundColor' => Release::albumChartColors(),
                    'data' => $albumPercentages->pluck('number'),
                ],
            ],
            'labels' => $albumPercentages->pluck('title'),
        ];
    }

    /**
     * Calculate the distribution of songs across all albums.
     *
     * @return  Collection
     */
    public function albumDistribution()
    {
        $allSongsPerformed = $this->allSongsPerformed()->unique();

        $albums = Release::where('isAlbum', true)
            ->get()
            ->map(function ($album) use ($allSongsPerformed) {
                return $this->numberOfTracksPlayed($album, $allSongsPerformed);
            })
            ->sortByDesc('number');

        $nonAlbum = [
            'title' => 'Non-album',
            'number' => ($this->songUniqueCount() - $albums->sum('number')),
        ];

        $albums->push($nonAlbum);

        return $albums->reject(function ($album) {
            return $album['number'] == 0;
        });
    }

    /**
     * Calcualte the number of tracks played from the given album.
     *
     * @param   Object  $album
     * @param   Collection  $allSongsPerformed
     * @return  array
     */
    private function numberOfTracksPlayed($album, $allSongsPerformed)
    {
        $albumSongs = $album->songs->pluck('title');
        $songsPlayed = $albumSongs->intersect($allSongsPerformed);

        return [
            'title' => $album->title,
            'number' => $songsPlayed->count(),
        ];
    }

    /**
     * Total song count.
     *
     * @return  integer
     */
    public function songCount()
    {
        return Cache::rememberForever('songCount', function () {
            return Perform::count();
        });
    }

    /**
     * Number of unique songs.
     *
     * @return  integer
     */
    public function songUniqueCount()
    {
        return Cache::rememberForever('songUniqueCount', function () {
            return $this->allSongsPerformed()->unique()->count();
        });
    }

    /**
     * Top ten songs on frequency played.
     *
     * @return  Collection
     */
    public function topTenSongs()
    {
        $concertCount = $this->concertCount();

        return Cache::rememberForever('topTenSongs', function () use ($concertCount) {
            return $this->allSongsPerformed()
                ->countBy()
                ->map(function($count, $title) use ($concertCount) {
                    return [
                        'title' => $title,
                        'count' => $count,
                        'percentage' => round(($count / $concertCount) * 100),
                    ];
                })
                ->sortByDesc(function ($song) {
                    return $song['count'];
                })
                ->take(10);
        });
    }

    /**
     * Get a collection of all songs performed.
     *
     * @return  Collection
     */
    private function allSongsPerformed()
    {
        return Cache::rememberForever('allSongsPerformed', function () {
            return Perform::with('song:id,title')
                ->get()
                ->map(function ($performed) {
                    return $performed->song->title;
                })
                ->reject(function ($title) {
                    return $title == 'Encore';
                });
        });
    }
}