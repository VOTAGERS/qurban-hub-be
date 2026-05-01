<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\CheckoutService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    protected $checkoutService;

    public function __construct(CheckoutService $checkoutService)
    {
        $this->checkoutService = $checkoutService;
    }

    public function process(Request $request)
    {
        try {
            // Basic validation, extend as needed
            $validator = Validator::make($request->all(), [
                'product_id' => 'required|exists:products_woo,id',
                'quantity' => 'required|integer|min:1',
                'total_price' => 'required|numeric|min:0',
                'billing.first_name' => 'required|string|max:255',
                'billing.phone' => 'required|string|max:50',
                'billing.email' => 'nullable|email|max:255',
                // Add more validation rules for billing and shipping if necessary
                'shipping.first_name' => 'required|string|max:255',
                'shipping.phone' => 'required|string|max:50',
                'shipping.email' => 'nullable|email|max:255',
                'recipients' => 'required|array|min:1',
                'recipients.*.qurban_name' => 'required|string|max:255',
                'recipients.*.email' => 'nullable|email|max:255',
                'recipients.*.phone_number' => 'nullable|string|max:50',
                'recipients.*.remarks' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $order = $this->checkoutService->processCheckout($request->all());

            return response()->json([
                'message' => 'Checkout processed successfully',
                'order' => $order,
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation Error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Log the error
            // Log::error('Checkout processing failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Checkout processing failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
