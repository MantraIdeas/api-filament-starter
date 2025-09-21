<?php

namespace App\Pagination;

use Illuminate\Pagination\LengthAwarePaginator;

class CustomPaginator extends LengthAwarePaginator
{
    /**
     * Get the instance as an array.
     */
    public function toArray(): array
    {
        return [
            'total' => $this->total(),
            'perPage' => $this->perPage(),
            'currentPage' => $this->currentPage(),
        ];
    }
}
