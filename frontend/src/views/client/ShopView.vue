<template>
  <div class="min-h-screen bg-slate-50 text-slate-800">

    <!-- NAVBAR -->
    <nav class="bg-white shadow-sm border-b sticky top-0 z-40 backdrop-blur-md bg-white/95">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <!-- Logo & Back Link -->
          <div class="flex items-center gap-4">
            <button 
              @click="router.push('/client')" 
              class="flex items-center gap-1.5 text-slate-500 hover:text-indigo-600 font-medium text-sm transition-all"
            >
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
              </svg>
              Volver al Panel
            </button>
            <span class="text-slate-300">|</span>
            <h1 class="text-xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
              Pet Spa Boutique 🛍️
            </h1>
          </div>

          <!-- Cart Indicator & User info -->
          <div class="flex items-center gap-6">
            <div class="text-right hidden sm:block">
              <p class="text-sm font-semibold text-slate-900">
                {{ user?.nombre_completo }}
              </p>
              <p class="text-xs text-slate-500">
                Cliente preferencial
              </p>
            </div>

            <!-- Cart Trigger Button -->
            <button 
              @click="toggleCart"
              class="relative bg-indigo-50 text-indigo-600 hover:bg-indigo-100 p-2.5 rounded-full transition-all duration-300"
              aria-label="Abrir carrito"
            >
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
              </svg>
              <span 
                v-if="cartCount > 0"
                class="absolute -top-1 -right-1 bg-gradient-to-r from-pink-500 to-rose-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center animate-bounce shadow-sm"
              >
                {{ cartCount }}
              </span>
            </button>
          </div>
        </div>
      </div>
    </nav>

    <!-- HERO HEADER -->
    <header class="bg-gradient-to-r from-indigo-900 via-purple-900 to-slate-900 text-white py-12 px-4 shadow-inner relative overflow-hidden">
      <!-- Decorative vector glow circles -->
      <div class="absolute -top-24 -left-24 w-96 h-96 bg-indigo-500/20 rounded-full blur-3xl"></div>
      <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-purple-500/20 rounded-full blur-3xl"></div>

      <div class="max-w-7xl mx-auto text-center relative z-10">
        <span class="bg-indigo-500/30 border border-indigo-400/40 text-indigo-200 text-xs font-semibold px-3 py-1 rounded-full uppercase tracking-wider">
          Accesorios y Cuidado Exclusivo
        </span>
        <h2 class="text-3xl md:text-5xl font-extrabold mt-4 bg-gradient-to-r from-indigo-100 to-purple-200 bg-clip-text text-transparent">
          El Consentimiento de tu Mascota en Casa
        </h2>
        <p class="text-indigo-200 max-w-2xl mx-auto mt-3 text-base md:text-lg">
          Explora nuestra selección de fórmulas de higiene profesional, fragancias de larga duración y accesorios premium probados por nuestros estilistas.
        </p>
      </div>
    </header>

    <!-- CONTENT WRAPPER -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      
      <!-- CONTROLS: SEARCH & FILTERS -->
      <div class="bg-white rounded-2xl shadow-sm border p-6 mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        
        <!-- Search bar -->
        <div class="relative flex-1 max-w-md">
          <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 pointer-events-none">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
              <path stroke-linecap="round" stroke-linejoin="round" d="m21-21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.602 10.602Z" />
            </svg>
          </span>
          <input 
            v-model="searchQuery" 
            type="text" 
            placeholder="Buscar champú, loción, cepillo..."
            class="block w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm transition-all"
          />
        </div>

        <!-- Category Pills -->
        <div class="flex flex-wrap items-center gap-2">
          <button 
            v-for="cat in categories" 
            :key="cat.value"
            @click="activeCategory = cat.value"
            class="px-4 py-1.5 rounded-full text-xs font-semibold transition-all duration-300"
            :class="activeCategory === cat.value 
              ? 'bg-indigo-600 text-white shadow-md shadow-indigo-100' 
              : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
          >
            {{ cat.label }}
          </button>
        </div>
      </div>

      <!-- MAIN CONTAINER: PRODUCTS GRID & SIDEBAR -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- PRODUCTS CATALOG (8 cols on large) -->
        <div class="lg:col-span-8">
          
          <!-- Loading State -->
          <div v-if="loading" class="flex flex-col items-center justify-center py-20 bg-white rounded-3xl border shadow-sm">
            <div class="animate-spin rounded-full h-12 w-12 border-4 border-indigo-200 border-t-indigo-600 mb-4"></div>
            <p class="text-slate-500 font-medium animate-pulse">Cargando productos exclusivos...</p>
          </div>

          <!-- Empty Catalog State -->
          <div v-else-if="filteredProducts.length === 0" class="text-center py-16 px-6 bg-white rounded-3xl border shadow-sm">
            <div class="text-6xl mb-4">🐾</div>
            <h3 class="text-xl font-bold text-slate-800">No encontramos productos</h3>
            <p class="text-slate-500 max-w-md mx-auto mt-2">
              No hay productos disponibles que coincidan con "{{ searchQuery }}" en la categoría seleccionada. Intenta cambiar los filtros.
            </p>
            <button 
              @click="resetFilters" 
              class="mt-6 inline-flex items-center gap-1.5 text-sm font-semibold bg-indigo-50 text-indigo-600 px-5 py-2 rounded-xl hover:bg-indigo-100 transition-all"
            >
              Restablecer Filtros
            </button>
          </div>

          <!-- Catalog Grid -->
          <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            
            <!-- PRODUCT CARD -->
            <div 
              v-for="product in filteredProducts" 
              :key="product.id"
              class="bg-white rounded-2xl border border-slate-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden flex flex-col group"
            >
              <!-- Card Top Decoration (colored header depending on category) -->
              <div 
                class="h-2 w-full transition-all group-hover:h-3"
                :class="{
                  'bg-gradient-to-r from-emerald-400 to-teal-500': product.categoria === 'INSUMO',
                  'bg-gradient-to-r from-indigo-400 to-purple-500': product.categoria === 'ALIMENTO',
                  'bg-gradient-to-r from-pink-400 to-rose-500': product.categoria === 'ACCESORIO',
                  'bg-gradient-to-r from-amber-400 to-orange-500': product.categoria === 'GENERAL' || !product.categoria
                }"
              ></div>

              <!-- Product Details -->
              <div class="p-6 flex-1 flex flex-col justify-between">
                
                <div>
                  <!-- Category & Stock Badges -->
                  <div class="flex justify-between items-center mb-3">
                    <span 
                      class="text-[10px] font-extrabold uppercase tracking-wider px-2 py-0.5 rounded-full"
                      :class="{
                        'bg-emerald-50 text-emerald-700 border border-emerald-100': product.categoria === 'INSUMO',
                        'bg-indigo-50 text-indigo-700 border border-indigo-100': product.categoria === 'ALIMENTO',
                        'bg-pink-50 text-pink-700 border border-pink-100': product.categoria === 'ACCESORIO',
                        'bg-amber-50 text-amber-700 border border-amber-100': product.categoria === 'GENERAL' || !product.categoria
                      }"
                    >
                      {{ product.categoria || 'GENERAL' }}
                    </span>

                    <!-- Stock Warning / Info -->
                    <span 
                      v-if="product.stock_actual === 0" 
                      class="text-[10px] bg-rose-100 text-rose-800 font-bold px-2 py-0.5 rounded-full"
                    >
                      AGOTADO
                    </span>
                    <span 
                      v-else-if="product.stock_actual <= product.stock_minimo" 
                      class="text-[10px] bg-amber-100 text-amber-800 font-medium px-2 py-0.5 rounded-full flex items-center gap-1"
                    >
                      ⚠️ Pocas unidades ({{ product.stock_actual }})
                    </span>
                    <span 
                      v-else 
                      class="text-[10px] bg-slate-100 text-slate-600 font-medium px-2 py-0.5 rounded-full"
                    >
                      Disponibles: {{ product.stock_actual }}
                    </span>
                  </div>

                  <!-- Product Name -->
                  <h4 class="text-lg font-bold text-slate-900 group-hover:text-indigo-600 transition-colors line-clamp-1">
                    {{ product.nombre }}
                  </h4>

                  <!-- Description -->
                  <p class="text-sm text-slate-500 mt-2 line-clamp-2 min-h-[40px]">
                    {{ product.descripcion || 'Fórmula premium recomendada por el equipo de estilistas estéticos de Pet Spa.' }}
                  </p>

                  <!-- Code tag -->
                  <span class="inline-block bg-slate-50 text-slate-400 font-mono text-[10px] px-1.5 py-0.5 rounded mt-3">
                    {{ product.codigo || 'BOUT-GEN' }}
                  </span>
                </div>

                <!-- Price & Add-to-cart block -->
                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                  <!-- Price Tag -->
                  <div>
                    <span class="text-xs text-slate-400 block font-medium">Precio</span>
                    <span class="text-2xl font-extrabold text-slate-900">${{ parseFloat(product.precio_venta || 0).toFixed(2) }}</span>
                  </div>

                  <!-- Add to Cart button -->
                  <button 
                    @click="addToCart(product)"
                    :disabled="product.stock_actual === 0"
                    class="flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-bold transition-all duration-300 border"
                    :class="product.stock_actual === 0
                      ? 'bg-slate-100 text-slate-400 border-slate-200 cursor-not-allowed'
                      : 'bg-indigo-600 hover:bg-indigo-700 text-white shadow-md shadow-indigo-100 hover:shadow-lg border-indigo-600'"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Añadir
                  </button>
                </div>
              </div>
            </div>

          </div>
        </div>

        <!-- SHOPPING CART PANEL (4 cols on large - Sticky) -->
        <div class="lg:col-span-4 lg:sticky lg:top-24">
          
          <div class="bg-white rounded-3xl border border-slate-200 shadow-xl overflow-hidden flex flex-col max-h-[calc(100vh-140px)]">
            <!-- Cart Title -->
            <div class="p-6 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-slate-100 flex justify-between items-center">
              <h3 class="font-extrabold text-slate-900 flex items-center gap-2">
                <span>🛒 Tu Carrito</span>
                <span 
                  v-if="cart.length > 0"
                  class="bg-indigo-600 text-white text-xs px-2 py-0.5 rounded-full font-bold"
                >
                  {{ cartCount }}
                </span>
              </h3>
              
              <button 
                v-if="cart.length > 0"
                @click="clearCart"
                class="text-xs text-rose-500 hover:text-rose-700 font-bold transition-all flex items-center gap-1"
              >
                Vaciar
              </button>
            </div>

            <!-- Cart Items List -->
            <div class="flex-1 overflow-y-auto p-6 space-y-4">
              <!-- Empty state -->
              <div v-if="cart.length === 0" class="text-center py-12 px-4 flex flex-col items-center">
                <span class="text-5xl mb-4">🛒</span>
                <p class="font-bold text-slate-800">Carrito vacío</p>
                <p class="text-xs text-slate-400 mt-1 max-w-[200px] mx-auto">
                  Añade shampoos o accesorios de la lista de productos para comenzar tu orden.
                </p>
              </div>

              <!-- Item row -->
              <div 
                v-for="item in cart" 
                :key="item.product.id"
                class="flex items-center justify-between gap-4 p-3 bg-slate-50 rounded-xl hover:bg-slate-100/70 border border-slate-100 transition-all duration-300"
              >
                <div class="flex-1 min-w-0">
                  <h5 class="font-bold text-slate-900 text-sm truncate">{{ item.product.nombre }}</h5>
                  <p class="text-xs text-slate-400 mt-0.5">Precio: ${{ parseFloat(item.product.precio_venta || 0).toFixed(2) }}</p>
                  <p class="text-xs font-semibold text-indigo-600 mt-1">Subtotal: ${{ parseFloat(item.product.precio_venta * item.quantity).toFixed(2) }}</p>
                </div>

                <!-- Quantity Controls -->
                <div class="flex items-center gap-2">
                  <button 
                    @click="decreaseQuantity(item.product.id)"
                    class="bg-white hover:bg-slate-200 border border-slate-200 rounded-lg w-7 h-7 flex items-center justify-center text-slate-600 hover:text-slate-800 font-bold transition"
                  >
                    -
                  </button>
                  <span class="font-bold text-sm text-slate-800 w-4 text-center">{{ item.quantity }}</span>
                  <button 
                    @click="increaseQuantity(item.product.id)"
                    class="bg-white hover:bg-slate-200 border border-slate-200 rounded-lg w-7 h-7 flex items-center justify-center text-slate-600 hover:text-slate-800 font-bold transition"
                  >
                    +
                  </button>
                </div>
              </div>
            </div>

            <!-- Cart Footer (Summary & Checkout) -->
            <div class="p-6 bg-slate-50 border-t border-slate-200/80">
              <div class="space-y-2 mb-6">
                <div class="flex justify-between items-center text-xs text-slate-500 font-medium">
                  <span>Subtotal</span>
                  <span>${{ cartSubtotal.toFixed(2) }}</span>
                </div>
                <div class="flex justify-between items-center text-xs text-slate-500 font-medium">
                  <span>Descuento</span>
                  <span class="text-emerald-600">-$0.00</span>
                </div>
                <div class="flex justify-between items-center pt-2 border-t border-slate-200">
                  <span class="font-extrabold text-slate-900 text-base">Total Estimado</span>
                  <span class="font-black text-indigo-700 text-xl">${{ cartSubtotal.toFixed(2) }}</span>
                </div>
              </div>

              <!-- PLACE ORDER BUTTON -->
              <button 
                @click="placeOrder"
                :disabled="cart.length === 0"
                class="w-full flex items-center justify-center gap-2 py-3 rounded-2xl text-sm font-extrabold transition-all duration-300"
                :class="cart.length === 0
                  ? 'bg-slate-200 text-slate-400 cursor-not-allowed'
                  : 'bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white shadow-lg shadow-emerald-100 hover:shadow-emerald-200'"
              >
                <!-- WhatsApp SVG icon -->
                <svg class="w-5 h-5 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                  <path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L3.2 496l133.9-35.1c32.7 17.8 69 27.2 106.2 27.2h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-79.8 20.9 21.3-77.8-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/>
                </svg>
                Hacer Pedido por WhatsApp
              </button>

              <p class="text-[10px] text-slate-400 text-center mt-3 leading-relaxed">
                *Nota: Los pedidos se coordinan y abonan directamente a través de recepción en tienda física o en tu próximo turno estético.*
              </p>
            </div>

          </div>

        </div>

      </div>

    </main>

    <!-- FLOATING MOBILE CART TRIGGER (Shown on mobile only if cart has items) -->
    <div 
      v-if="cart.length > 0"
      class="lg:hidden fixed bottom-6 right-6 z-40"
    >
      <button 
        @click="scrollToCart"
        class="bg-indigo-600 hover:bg-indigo-700 text-white shadow-xl p-4 rounded-full flex items-center gap-2 transition active:scale-95 duration-200"
      >
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
          <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
        </svg>
        <span class="font-extrabold text-sm">${{ cartSubtotal.toFixed(2) }}</span>
        <span class="bg-white text-indigo-600 text-xs px-2 py-0.5 rounded-full font-black">{{ cartCount }}</span>
      </button>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import api from '@/services/api'

const router = useRouter()
const authStore = useAuthStore()

// State
const products = ref([])
const loading = ref(true)
const searchQuery = ref('')
const activeCategory = ref('ALL')
const cart = ref([])

// Dropdown / panel controls
const isCartOpen = ref(false)

// Mock/Real Categories list
const categories = [
  { label: 'Todos los productos', value: 'ALL' },
  { label: 'Champús e Higiene', value: 'INSUMO' },
  { label: 'Nutrición y Alimentos', value: 'ALIMENTO' },
  { label: 'Accesorios y Juguetes', value: 'ACCESORIO' }
]

// Current user details
const user = computed(() => authStore.user)

// Get products from API
const fetchProducts = async () => {
  loading.value = true
  try {
    const res = await api.get('/inventario/productos')
    // We only want active products in the shop
    products.value = (res.data.productos || []).filter(p => p.activo)
  } catch (error) {
    console.error('Error fetching boutique products:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchProducts()
})

// Filtered products list
const filteredProducts = computed(() => {
  return products.value.filter(p => {
    // Search query matches name or description or code
    const matchesSearch = 
      p.nombre.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      (p.descripcion && p.descripcion.toLowerCase().includes(searchQuery.value.toLowerCase())) ||
      (p.codigo && p.codigo.toLowerCase().includes(searchQuery.value.toLowerCase()))

    // Category matches active pill
    const matchesCategory = 
      activeCategory.value === 'ALL' || 
      p.categoria === activeCategory.value

    return matchesSearch && matchesCategory
  })
})

// Cart Helpers & Actions
const cartCount = computed(() => {
  return cart.value.reduce((total, item) => total + item.quantity, 0)
})

const cartSubtotal = computed(() => {
  return cart.value.reduce((total, item) => total + (item.product.precio_venta * item.quantity), 0)
})

const addToCart = (product) => {
  // Check if item is already in cart
  const existingItem = cart.value.find(item => item.product.id === product.id)
  
  if (existingItem) {
    // Prevent adding more than available stock
    if (existingItem.quantity >= product.stock_actual) {
      alert(`Lo sentimos, no hay más stock disponible de ${product.nombre}. (Máximo: ${product.stock_actual})`)
      return
    }
    existingItem.quantity++
  } else {
    cart.value.push({
      product,
      quantity: 1
    })
  }
}

const increaseQuantity = (productId) => {
  const item = cart.value.find(item => item.product.id === productId)
  if (item) {
    if (item.quantity >= item.product.stock_actual) {
      alert(`Lo sentimos, no hay más stock disponible de ${item.product.nombre}.`)
      return
    }
    item.quantity++
  }
}

const decreaseQuantity = (productId) => {
  const itemIndex = cart.value.findIndex(item => item.product.id === productId)
  if (itemIndex > -1) {
    const item = cart.value[itemIndex]
    if (item.quantity > 1) {
      item.quantity--
    } else {
      cart.value.splice(itemIndex, 1)
    }
  }
}

const clearCart = () => {
  if (confirm('¿Estás seguro de que deseas vaciar tu carrito de compras?')) {
    cart.value = []
  }
}

const toggleCart = () => {
  isCartOpen.value = !isCartOpen.value
}

const resetFilters = () => {
  searchQuery.value = ''
  activeCategory.value = 'ALL'
}

// Scroll to Cart (for mobile)
const scrollToCart = () => {
  window.scrollTo({
    top: document.body.scrollHeight,
    behavior: 'smooth'
  })
}

// Order Generation & Direct Whatsapp Link
const placeOrder = () => {
  if (cart.value.length === 0) return

  // Format WhatsApp message
  const spaPhone = '59170000000' // Business phone (can be replaced by real config)
  
  let msg = `🐾 *¡Hola Pet Spa! Quiero realizar un pedido:* \n`
  msg += `--------------------------------------------\n`
  msg += `👤 *Cliente:* ${user.value?.nombre_completo || 'Cliente Registrado'}\n`
  msg += `📞 *Teléfono:* ${user.value?.telefono || 'No registrado'}\n`
  msg += `📦 *Detalle del Pedido:* \n\n`
  
  cart.value.forEach(item => {
    const unitPrice = parseFloat(item.product.precio_venta || 0).toFixed(2)
    const sub = parseFloat(item.product.precio_venta * item.quantity).toFixed(2)
    msg += `• *${item.quantity}x* ${item.product.nombre} \n`
    msg += `   _($${unitPrice} c/u) → subtotal: $${sub}_\n\n`
  })

  msg += `--------------------------------------------\n`
  msg += `💰 *Total Compra:* *$${cartSubtotal.value.toFixed(2)}*\n`
  msg += `📍 *Método de entrega:* A coordinar en mi próxima cita de grooming 📅\n`
  msg += `--------------------------------------------\n`
  msg += `✨ _¡Muchas gracias! Espero su confirmación._`

  const encodedMsg = encodeURIComponent(msg)
  const whatsappUrl = `https://wa.me/${spaPhone}?text=${encodedMsg}`
  
  // Clear cart on checkout (optional, user-friendly after redirection)
  setTimeout(() => {
    cart.value = []
  }, 1000)

  // Redirect to whatsapp in a new tab
  window.open(whatsappUrl, '_blank')
}
</script>

<style scoped>
.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;  
  overflow: hidden;
}
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;  
  overflow: hidden;
}
</style>
