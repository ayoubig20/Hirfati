<template>
  <div class="min-h-screen bg-gray-50">
    <nav class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center">
            <router-link to="/" class="text-2xl font-bold text-blue-600">
              Hirfati
            </router-link>
            <div class="hidden sm:ml-8 sm:flex sm:space-x-4">
              <router-link
                to="/"
                class="px-3 py-2 text-sm font-medium text-gray-700 hover:text-blue-600 rounded-md"
                active-class="text-blue-600 bg-blue-50"
              >
                Home
              </router-link>
              <router-link
                to="/search"
                class="px-3 py-2 text-sm font-medium text-gray-700 hover:text-blue-600 rounded-md"
                active-class="text-blue-600 bg-blue-50"
              >
                Find Artisans
              </router-link>
              <router-link
                to="/admin"
                class="px-3 py-2 text-sm font-medium text-gray-700 hover:text-blue-600 rounded-md"
                active-class="text-blue-600 bg-blue-50"
              >
                Admin
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </nav>
    <main>
      <router-view />
    </main>
    <footer class="bg-white border-t border-gray-200 mt-12">
      <div class="max-w-7xl mx-auto px-4 py-6 text-center text-sm text-gray-500">
        Hirfati - Artisan Trust Platform
      </div>
    </footer>
  </div>
</template>

<script setup>
</script>
