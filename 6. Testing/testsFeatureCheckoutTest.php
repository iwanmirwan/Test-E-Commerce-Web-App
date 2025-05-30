public function test_user_can_checkout()
{
    // Mock Midtrans API response
    Http::fake([
        'https://api.midtrans.com/*' => Http::response([
            'token' => 'mock-token',
            'redirect_url' => 'https://example.com/payment'
        ], 200),
    ]);

    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 100000]);

    // Add to cart
    $this->actingAs($user)
        ->post('/cart', ['product_id' => $product->id]);

    // Checkout
    $response = $this->actingAs($user)
        ->post('/checkout', [
            'delivery_address' => 'Jl. Contoh No. 123',
        ]);

    $response->assertRedirect();

    // Verify order was created
    $this->assertDatabaseHas('orders', [
        'user_id' => $user->id,
        'total_amount' => 100000,
        'status' => 'pending',
    ]);

    // Verify payment was created
    $this->assertDatabaseHas('payments', [
        'status' => 'pending',
    ]);
}