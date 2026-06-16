<?php

namespace App\Helpers;
use Illuminate\Pagination\LengthAwarePaginator;

trait autoPaginator
{
    protected function paginateReturn(LengthAwarePaginator $lap){
        return [
            'current_page' => $lap->currentPage(),
            'last_page' => $lap->lastPage(),
            'per_page' => $lap->perPage(),
            'total' => $lap->total(),
            'prev_page_url' => $lap->previousPageUrl(),
            'next_page_url' => $lap->nextPageUrl(),
        ];
    }
}
