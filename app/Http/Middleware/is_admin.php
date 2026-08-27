<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class is_admin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $auth = Auth::user();
       
        if($auth != null){
            if(Auth::user()->is_admin == 1){
                return $next($request);
            }
            
        }
        return redirect('/pos'); 
    }
}
