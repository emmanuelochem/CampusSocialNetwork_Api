<?php

namespace App\Mixins;

class PaginationMixin
{
    /**
     * Format the list of paginated list of models.
     *
     * @return \Closure
     */
    public function withPaginated(int $perPage = 20)
    {
        return function($perPage, array $columns = ['*']) {
            $data = $this->paginate($perPage, $columns);
            $hasMore = $data->hasMorePages();
            $nextOffset = $hasMore ? $data->currentPage() + 1 : null;
            return [
                'data' => $data->items(),
                'item_count'=> $data->perPage(),
                'total_items'=>  $data->total(),
                'has_more' => $hasMore,
                'current_page' => $data->currentPage(),
                'next_page' => $nextOffset,
                'total_pages'=> $data->lastPage(),
            ];
        };
    }
}
