<template>
  <div>
    <div class="mb-4">
      <select v-model="selectedCategory" @change="filterProducts" class="p-2 border rounded">
        <option value="all">All Categories</option>
        <option v-for="category in categories" :value="category.id">{{ category.name }}</option>
      </select>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div v-for="product in filteredProducts" :key="product.id" class="bg-white rounded-lg shadow overflow-hidden">
        <img :src="product.image_url" :alt="product.name" class="w-full h-48 object-cover">
        <div class="p-4">
          <h3 class="font-semibold text-lg">{{ product.name }}</h3>
          <p class="text-gray-600">{{ product.category.name }}</p>
          <div class="mt-4 flex justify-between items-center">
            <span class="text-xl font-bold">${{ product.price }}</span>
            <button @click="addToCart(product)" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
              Add to Cart
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    initialProducts: Array,
    initialCategories: Array
  },
  data() {
    return {
      products: this.initialProducts,
      categories: this.initialCategories,
      selectedCategory: 'all'
    }
  },
  computed: {
    filteredProducts() {
      if (this.selectedCategory === 'all') {
        return this.products;
      }
      return this.products.filter(product => product.category_id == this.selectedCategory);
    }
  },
  methods: {
    addToCart(product) {
      axios.post('/cart', { product_id: product.id })
        .then(response => {
          this.$emit('cart-updated');
          alert('Product added to cart');
        })
        .catch(error => {
          console.error(error);
        });
    }
  }
}
</script>