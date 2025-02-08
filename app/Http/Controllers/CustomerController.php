<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;

class CustomerController extends BaseController
{
    public function updateCustomerInfo(Request $request)
    {
        try {
            $customer = JWTAuth::parseToken()->authenticate();
            $customer->update($request->only(['first_name', 'last_name', 'email']));
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

            if ($cart) {
                return $this->sendSuccess('Cart fetched successfully', $cart->cartItems());
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
