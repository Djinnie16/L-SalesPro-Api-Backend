<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Warehouse;

class CheckWarehouseCapacity
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->isMethod('POST') && $request->routeIs('stock-transfers.store')) {
            $warehouse = Warehouse::find($request->destination_warehouse_id);
            
            if ($warehouse && !$warehouse->hasCapacity($request->quantity)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Destination warehouse does not have sufficient capacity'
                ], 422);
            }
        }

        return $next($request);
    }
}