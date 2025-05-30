public function process(Request $request)
{
    $request->validate([
        'delivery_address' => 'required|string|max:500',
    ]);

    $user = $request->user();
    $cartItems = $user->cartItems()->with('product')->get();

    if ($cartItems->isEmpty()) {
        return redirect()->back()->with('error', 'Your cart is empty');
    }

    $order = DB::transaction(function () use ($user, $cartItems, $request) {
        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'ORD-' . time(),
            'delivery_address' => $request->delivery_address,
            'total_amount' => $cartItems->sum(function ($item) {
                return $item->quantity * $item->product->price;
            }),
            'status' => 'pending',
        ]);

        foreach ($cartItems as $item) {
            OrderDetail::create([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'price' => $item->product->price,
            ]);
        }

        $user->cartItems()->delete();

        return $order;
    });

    $paymentService = new MidtransService();
    $paymentData = $paymentService->createTransaction($order);

    Payment::create([
        'order_id' => $order->id,
        'amount' => $order->total_amount,
        'method' => 'midtrans',
        'status' => 'pending',
        'transaction_id' => $paymentData['transaction_id'],
    ]);

    return redirect($paymentData['redirect_url']);
}