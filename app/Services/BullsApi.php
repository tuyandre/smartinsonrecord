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
        if (is_array($cached = Cache::get('bulls_api'))) {
            return $cached;
        }

        // The external service can be slow or down: give up quickly so a page never waits long for it.
        try {
            $data = Http::connectTimeout(2)->timeout(4)->get(config('services.bulls.url'))->json();
        } catch (\Throwable) {
            $data = null;
        }

        if (is_array($data)) {
            Cache::put('bulls_api', $data, 300);

            return $data;
        }

        // Remember the failure only briefly, so the service is retried soon without slowing every page.
        Cache::put('bulls_api', [], 30);

        return [];
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
