public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'category_id' => 'required|exists:categories,id',
        'description' => 'required|string',
        'price' => 'required|numeric|min:0',
        'stock' => 'required|integer|min:0',
        'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
    ]);

    $imagePath = $request->file('image')->store('products', 'public');

    Product::create([
        'name' => $request->name,
        'slug' => Str::slug($request->name),
        'category_id' => $request->category_id,
        'description' => $request->description,
        'price' => $request->price,
        'stock' => $request->stock,
        'image' => $imagePath,
    ]);

    return redirect()->route('admin.products.index')
        ->with('success', 'Product created successfully.');
}