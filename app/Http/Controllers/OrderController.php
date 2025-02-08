<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;

class OrderController extends BaseController
{
    /**
     * For customers: Get all orders for the authenticated customer.
     */
    public function indexCustomer(Request $request)
    {
        try {
            $customer = JWTAuth::parseToken()->authenticate();
            $orders = Order::where('customer_id', $customer->id)
                           ->with('items.product')
                           ->get();

            return $this->sendSuccess('Orders retrieved successfully', $orders, 200);
        } catch (Exception $e) {
            return $this->sendError('Failed to retrieve orders', $e->getMessage());
        }
    }

    /**
     * For customers: Get details of a specific order.
     */
    public function getOrder($orderId)
    {
        try {
            $customer = JWTAuth::parseToken()->authenticate();
            $order = Order::where('id', $orderId)
                          ->where('customer_id', $customer->id)
                          ->with('items.product')
                          ->firstOrFail();

            return $this->sendSuccess('Order details retrieved successfully', $order, 200);
        } catch (Exception $e) {
            return $this->sendError('Failed to retrieve order details', $e->getMessage());
        }
    }

    /**
     * For admins: Get all orders.
     */
    public function indexAdmin()
    {
        try {
            $orders = Order::with('customer', 'items.product')->get();
            return $this->sendSuccess('All orders retrieved successfully', $orders, 200);
        } catch (Exception $e) {
            return $this->sendError('Failed to retrieve orders', $e->getMessage());
        }
    }

    /**
     * For admins: Update the status of an order.
     */
    public function updateStatus(Request $request, $orderId)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:pending,confirmed,shipped,delivered,cancelled',
            ]);
            $order = Order::findOrFail($orderId);
            $order->update(['status' => $validated['status']]);

            return $this->sendSuccess('Status updated successfully', $order, 204);
        } catch (Exception $e) {
            return $this->sendError('Failed to update order status', $e->getMessage(), 500);
        }
    }

    /**
     * Checkout: Convert a cart into an order.
     */
    public function checkout(Request $request)
    {
        DB::beginTransaction();
        try {
            $customer = JWTAuth::parseToken()->authenticate();

            $cart = $customer->cart;
            if (!$cart) {
                return $this->sendError('No active cart found.', null, 404);
            }

            if ($cart->cartItems->isEmpty()) {
                return $this->sendError('Cart is empty.', null, 404);
            }
            
            $totalPrice = $cart->cartItems()
                ->join('products', 'cart_items.product_id', '=', 'products.id')
                ->selectRaw('SUM(products.price * cart_items.quantity) as total')
                ->value('total');

            $order = Order::create([
                'customer_id' => $customer->id,
                'total_price' => $totalPrice,
                'status' => 'pending',
            ]);

            foreach ($cart->cartItems as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    'price_at_checkout' => $cartItem->product->price,
                ]);
            }
            
            $cart->cartItems()->delete();
            
            DB::commit();

            return $this->sendSuccess("Order created successfully", $order, 201);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->sendError('Checkout failed: ' . $e->getMessage(), 500);
        }
    }
}