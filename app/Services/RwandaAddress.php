<?php

namespace App\Services;

/** Rwanda's administrative hierarchy: province > district > sector > cell > village (names only). */
class RwandaAddress
{
    private ?array $tree = null;

    public function tree(): array
    {
        return $this->tree ??= json_decode(file_get_contents(resource_path('data/rwanda_address.json')), true) ?? [];
    }

    /** @return string[] every district name, sorted. */
    public function districts(): array
    {
        $districts = [];
        foreach ($this->tree() as $province) {
            array_push($districts, ...array_keys($province));
        }
        sort($districts, SORT_NATURAL | SORT_FLAG_CASE);

        return $districts;
    }

    /** True when the district > sector > cell > village chain exists. */
    public function isValid(?string $district, ?string $sector, ?string $cell, ?string $village): bool
    {
        foreach ($this->tree() as $province) {
            if (in_array($village, $province[$district][$sector][$cell] ?? [], true)) {
                return true;
            }
        }

        return false;
    }
}
