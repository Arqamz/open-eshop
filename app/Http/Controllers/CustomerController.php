<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;
use Illuminate\Support\Facades\Log;

class CustomerController extends BaseController
{
    
    public function updateCustomerInfo(Request $request)
    {
        try {

            $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'phone_number' => 'nullable|string|max:20',
                'address_line1' => 'nullable|string|max:255',
                'address_line2' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:255',
                'state' => 'nullable|string|max:255',
                'zip_code' => 'nullable|string|max:20',
                'country' => 'nullable|string|max:255',
            ]);

            $customer = JWTAuth::parseToken()->authenticate();
            $customer->update($request->only([
                'first_name', 
                'last_name', 
                'phone_number', 
                'address_line1', 
                'address_line2', 
                'city', 
                'state', 
                'zip_code', 
                'country'
            ]));

            return $this->sendSuccess('Customer info updated successfully', $customer);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function getCart(Request $request)
    {
        try {
            $customer = JWTAuth::parseToken()->authenticate();
            $cart = $customer->cart()->first();

            $cartItems = $cart->cartItems()->get();

            if ($cartItems->isNotEmpty()) {
                return $this->sendSuccess('Cart fetched successfully', $cartItems);
            } else {
                return $this->sendError('No items in cart.');
            }
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function addItemToCart(Request $request)
    {
        try {
            $customer = JWTAuth::parseToken()->authenticate();
            $cart = $customer->cart()->first();

            if (!$cart) {
                $cart = Cart::create(['customer_id' => $customer->id]);
            }

            $cart->cartItems()->create([
                'product_id' => $request->product_id,
                'quantity' => $request->quantity
            ]);

            return $this->sendSuccess('Item added to cart');
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function updateCartItem(Request $request, $cartItemId)
    {
        try {
            $cartItem = CartItem::findOrFail($cartItemId);
            $request->validate([
                'quantity' => 'required|integer|min:1'
            ]);

            $cartItem->update(['quantity' => $request->quantity]);

            return $this->sendSuccess('Cart item updated');
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function removeItemFromCart($cartItemId)
    {
        try {
            $cartItem = CartItem::findOrFail($cartItemId);
            $cartItem->delete();

            return $this->sendSuccess('Item removed from cart');
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }
}
