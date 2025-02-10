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

            $totalPrice = $cart->cartItems()
                ->join('products', 'cart_items.product_id', '=', 'products.id')
                ->selectRaw('SUM(products.price * cart_items.quantity) as total')
                ->value('total');
            Log::info('Calculated total price for cart', ['total_price' => $totalPrice]);

            $finalAmount = $totalPrice;

            $order = Order::create([
                'customer_id'       => $customer->id,
                'total_price'       => $finalAmount,
                'status'            => 'pending',
                'shipping_address'  => $validated['shipping_address'],
                'shipping_city'     => $validated['shipping_city'],
                'shipping_state'    => $validated['shipping_state'],
                'shipping_zip'      => $validated['shipping_zip'],
                'shipping_country'  => $validated['shipping_country'],
            ]);
            Log::info('Order created', ['order_id' => $order->id]);

            foreach ($cart->cartItems as $cartItem) {
                OrderItem::create([
                    'order_id'           => $order->id,
                    'product_id'         => $cartItem->product_id,
                    'quantity'           => $cartItem->quantity,
                    'price_at_checkout'  => $cartItem->product->price,
                ]);
                Log::info('Order item created', [
                    'order_id'   => $order->id,
                    'product_id' => $cartItem->product_id,
                    'quantity'   => $cartItem->quantity,
                ]);
            }

            // Note: DO NOT clear cart items here. They will be cleared when the payment is confirmed via Stripe webhook.

            // Create a Stripe Checkout Session
            // Create a Stripe Price object using the final amount and product data
            Stripe::setApiKey(env('STRIPE_SECRET'));
            $priceObject = \Stripe\Price::create([
                'currency' => 'usd', // Adjust as necessary
                'unit_amount' => $finalAmount * 100, // Stripe requires amount in cents
                'product_data' => [
                    'name' => 'Order #' . $order->id,
                    // Additional product details can be included if needed
                ],
            ]);
            Log::info('Stripe Price object created', ['price_id' => $priceObject->id]);

            // Create a Stripe Checkout Session using the Price object
            $session = StripeSession::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price'    => $priceObject->id,
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'metadata' => [
                    'order_id' => $order->id,
                ],
                // Hardcoded success and cancel URLs for demonstration
                'success_url' => 'https://example.com/checkout/success',
                'cancel_url'  => 'https://example.com/checkout/cancel',
            ]);
            Log::info('Stripe Checkout Session created', ['session_id' => $session->id]);

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