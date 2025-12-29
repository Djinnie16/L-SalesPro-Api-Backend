<?php

namespace App\Http\Middleware;

use App\Models\Customer;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckCreditLimit
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only check credit on order creation
        if ($request->route() && $request->route()->named('orders.store')) {
            $customerId = $request->input('customer_id');
            $totalAmount = $request->input('total_amount', 0);
            
            $customer = Customer::find($customerId);
            
            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer not found',
                    'errors' => ['customer_id' => 'Invalid customer']
                ], 404);
            }
            
            if ($customer->available_credit < $totalAmount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Credit limit exceeded',
                    'errors' => [
                        'credit_limit' => sprintf(
                            'Available credit: %s, Order amount: %s',
                            number_format($customer->available_credit, 2),
                            number_format($totalAmount, 2)
                        )
                    ]
                ], 422);
            }
        }
        
        return $next($request);
    }
}