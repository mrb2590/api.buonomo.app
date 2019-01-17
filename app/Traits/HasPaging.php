<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait HasPaging
{
    /**
     * Validate the limit and page for pagination.
     *
     * @param  \Illuminate\Http\Request $request
     * @return integer $limit
     */
    protected function validatePaging(Request $request, $sortableCols = [])
    {
        // Default limit
        $limit = 10;

        if ($request->has('limit')) {
            $this->validate($request, [
                'page' => 'nullable|integer',
                'limit' => 'nullable|integer|in:5,10,25,50,100',
                'sort' => 'required_with:sort_col|string|in:asc,desc',
                'sortby' => 'required_with:sort|string|in:'.implode(',', $sortableCols),
                'search' => 'nullable|string',
            ]);

            $limit = (int) $request->query('limit');
        }

        return $limit;
    }
}
