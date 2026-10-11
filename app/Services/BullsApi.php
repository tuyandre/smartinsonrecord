<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Government bull/semen catalogue (external API used by the old system).
 * Failures never break a page: callers get an empty list.
 */
class BullsApi
{
    public function all(): array
    {
        return Cache::remember('bulls_api', 300, function () {
            try {
                $data = Http::timeout(5)->get(config('services.bulls.url'))->json();
            } catch (\Throwable) {
                return [];
            }

            return is_array($data) ? $data : [];
        });
    }

    public function count(): int
    {
        return count($this->all());
    }

    public function names(): array
    {
        return array_values(array_filter(array_column($this->all(), 'Name')));
    }
}
