<?php

namespace App\Http\Controllers;

use App\Mpesa\MpesaPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MpesaCallbackController extends Controller
{
    public function __construct(private readonly MpesaPaymentService $mpesa) {}

    public function __invoke(Request $request): JsonResponse
    {
        $this->mpesa->handleCallback($request->all());

        return response()->json([
            'ResultCode' => 0,
            'ResultDesc' => 'Accepted',
        ]);
    }
}
