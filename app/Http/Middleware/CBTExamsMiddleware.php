<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;

class CBTExamsMiddleware
{
    
    public function handle(Request $request, Closure $next)
    {
        $assessment = $request->route('assessment');

        if( $assessment->is_standalone && Carbon::parse($assessment->start_date)->gt( now() ) ){

            abort(403, 'Assessment No Longer Valid');
        }

        if( $assessment->is_standalone && Carbon::parse($assessment->end_date)->lt( now() ) ){
            
            abort(403, 'Assessment No Longer Valid');
        }

        if( ! $assessment->is_published ) {

            abort(404);
        }

        return $next($request);
    }
}
