<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Webhook;
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
            Log::info('Authenticated customer', ['customer_id' => $customer->id]);

            $validated = $request->validate([
                'shipping_address' => 'required|string',
                'shipping_city'    => 'required|string',
                'shipping_state'   => 'required|string',
                'shipping_zip'     => 'required|string',
                'shipping_country' => 'required|string',
            ]);

            $cart = $customer->cart;
            if (!$cart) {
                Log::warning('No active cart found for customer', ['customer_id' => $customer->id]);
                return response()->json(['error' => 'No active cart found.'], 404);
            }

            // Calculate shipping cost
            $totalQuantity = $cart->cartItems->sum('quantity');
            $shippingCost = $this->calculateShippingCost($totalQuantity);

            // Calculate total price including shipping
            $subtotal = $cart->cartItems()
                ->join('products', 'cart_items.product_id', '=', 'products.id')
                ->selectRaw('SUM(products.price * cart_items.quantity) as total')
                ->value('total');

            $finalAmount = $subtotal + $shippingCost;

            // Create order record
            $order = Order::create([
                'customer_id' => $customer->id,
                'total_price' => $finalAmount,
                'status' => 'pending',
                'shipping_address' => $validated['shipping_address'],
                'shipping_city' => $validated['shipping_city'],
                'shipping_state' => $validated['shipping_state'],
                'shipping_zip' => $validated['shipping_zip'],
                'shipping_country' => $validated['shipping_country'],
            ]);

            // Prepare line items for Stripe
            $lineItems = [];
            foreach ($cart->cartItems as $cartItem) {
                $product = $cartItem->product;
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    'price_at_checkout' => $product->price,
                ]);

                // Add product as line item
                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'usd',
                        'unit_amount' => (int)($product->price * 100), // Convert to cents
                        'product_data' => [
                            'name' => $product->name,
                            'description' => substr($product->description, 0, 100),
                            'images' => [$request->getSchemeAndHttpHost() . '/storage/' . $product->image1],
                        ],
                    ],
                    'quantity' => $cartItem->quantity,
                ];
            }

            // Add shipping as separate line item
            if ($shippingCost > 0) {
                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'usd',
                        'unit_amount' => (int)($shippingCost * 100),
                        'product_data' => [
                            'name' => 'Shipping Cost',
                            'description' => 'Shipping and handling fee',
                        ],
                    ],
                    'quantity' => 1,
                ];
            }

            // Create Stripe session with line items
            Stripe::setApiKey(env('STRIPE_SECRET'));
            $session = StripeSession::create([
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => 'payment',
                'metadata' => [
                    'order_id' => $order->id,
                ],
                'success_url' => 'https://79r.6cf.mytemp.website/payment/success',
                'cancel_url' => 'https://79r.6cf.mytemp.website/payment/failed',
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Checkout session created successfully.',
                'session_url' => $session->url,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Checkout failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Checkout failed: ' . $e->getMessage()], 500);
        }
    }

    private function calculateShippingCost($totalQuantity): float
    {
        if ($totalQuantity <= 0) return 0;
        if ($totalQuantity === 1) return 10.50;
        if ($totalQuantity === 2) return 14.70;
        
        // For quantities > 2, add 25% for each additional item
        $cost = 14.70; // Base cost for 2 items
        for ($i = 3; $i <= $totalQuantity; $i++) {
            $cost += $cost * 0.25;
        }
        return round($cost, 2);
    }

    // public function handleWebhook(Request $request)
    // {
    //     // Retrieve the raw payload and Stripe signature header
    //     $payload = $request->getContent();
    //     $sig_header = $request->header('Stripe-Signature');
    //     $endpoint_secret = env('STRIPE_WEBHOOK_SECRET'); // Set in your .env file

    //     try {
    //         // Set your Stripe secret key
    //         Stripe::setApiKey(env('STRIPE_SECRET'));

    //         // Verify the event by constructing it with Stripe's SDK
    //         $event = Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
    //     } catch (Exception $e) {
    //         Log::error('Stripe webhook verification failed', ['error' => $e->getMessage()]);
    //         return response()->json(['error' => 'Webhook Error'], 400);
    //     }

    //     // Handle the checkout.session.completed event
    //     if ($event->type === 'checkout.session.completed') {
    //         $session = $event->data->object; // The Stripe Checkout Session object

    //         // Retrieve our internal order ID from metadata
    //         $orderId = $session->metadata->order_id ?? null;
    //         if ($orderId) {
    //             $order = Order::find($orderId);
    //             if ($order) {
    //                 // Update the order status to 'paid'
    //                 $order->update(['status' => 'paid']);

    //                 // For each order item, reduce the product stock accordingly
    //                 foreach ($order->items as $orderItem) {
    //                     $product = $orderItem->product;
    //                     if ($product) {
    //                         $product->decrement('stock', $orderItem->quantity);
    //                     }
    //                 }

    //                 // Clear the customer's cart items (cart remains for future use)
    //                 $customer = $order->customer;
    //                 if ($customer && $customer->cart) {
    //                     $customer->cart->cartItems()->delete();
    //                 }

    //                 Log::info('Order successfully placed and processed', ['order_id' => $orderId]);
    //             } else {
    //                 Log::warning('Order not found for checkout session', ['order_id' => $orderId]);
    //             }
    //         } else {
    //             Log::warning('No order_id found in Stripe session metadata');
    //         }
    //     } else {
    //         Log::info('Unhandled event type: ' . $event->type);
    //     }

    //     // Return a 200 response to acknowledge receipt of the event
    //     return response()->json(['status' => 'success'], 200);
    // }
}