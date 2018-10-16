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
    protected function validatePaging(Request $request)
    {
        // Default limit
        $limit = 25;

        if ($request->has('limit')) {
            $this->validate($request, [
                'page' => 'nullable|integer',
                'limit' => 'nullable|integer|in:10,25,50,100',
            ]);

            $limit = (int) $request->query('limit');
        }

        return $limit;
    }
}
