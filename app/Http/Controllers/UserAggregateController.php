<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserAggregateController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth:api', 'verified']);
    }

    /**
     * Return how many users have signed up per day.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function fetchCreated(Request $request)
    {
        $this->validate($request, ['range' => 'nullable|in:month,day,year']);

        $range = $request->input('range') ?: 'month';

        if ($range == 'day') {
            $time = 'hours';
            $totalItems = 24;
            $date = 'DATE_FORMAT(created_at, \'%l%p\')';
            $days = 1;
            $format = 'ga';
        } elseif ($range == 'month') {
            $time = 'days';
            $totalItems = 30;
            $date = 'DATE_FORMAT(created_at, \'%d\')';
            $days = 30;
            $format = 'd';
        } elseif ($range == 'year') {
            $time = 'months';
            $totalItems = 12;
            $date = 'DATE_FORMAT(created_at, \'%b\')';
            $days = 365;
            $format = 'M';
        }

        $data = DB::table('users')
            ->select(DB::raw($date.' as created_at, count(*) as total'))
            ->whereDate('created_at', '>=', Carbon::now()->subDays($days))
            ->groupBy(DB::raw($date))
            ->orderBy('created_at', 'asc')
            ->get();

        // Add missing dates
        $dates = [];

        for ($i = 0; $i < $totalItems; $i++) {
            $day = date($format, strtotime('-'.$i.' '.$time));
            $dates[$i] = [];

            // Search of the date
            foreach ($data as $item) {
                if (strtolower($item->created_at) == $day) {
                    $dates[$i]['created_at'] = $item->created_at;
                    $dates[$i]['total'] = $item->total;
                    break;
                }
            }

            if (!isset($dates[$i]['created_at'])) {
                $dates[$i]['created_at'] = $day;
                $dates[$i]['total'] = 0;
            }
        }

        return response()->json(['data' => array_reverse($dates)]);
    }
}
