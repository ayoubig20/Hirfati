<template>
  <div class="min-h-screen">
    <!-- Navigation (hidden on login page) -->
    <nav
      v-if="!hideLayout"
      :class="[
        'fixed top-0 left-0 right-0 z-50 transition-all duration-300',
        scrolled ? 'nav-scrolled py-3' : 'bg-transparent py-5'
      ]"
    >
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between">
          <!-- Logo -->
          <router-link to="/" class="flex items-center gap-2">
            <div class="w-10 h-10 bg-amber-500 rounded-lg flex items-center justify-center">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
            </div>
            <span class="text-2xl font-bold text-white">Hirfati</span>
          </router-link>

          <!-- Desktop Nav -->
          <div class="hidden md:flex items-center gap-8">
            <router-link
              to="/"
              class="text-sm font-medium text-white/80 hover:text-amber-400 transition-colors"
              active-class="!text-amber-400"
            >
              Home
            </router-link>
            <a href="#services" class="text-sm font-medium text-white/80 hover:text-amber-400 transition-colors">
              Services
            </a>
            <a href="#about" class="text-sm font-medium text-white/80 hover:text-amber-400 transition-colors">
              About
            </a>
            <router-link
              to="/search"
              class="text-sm font-medium text-white/80 hover:text-amber-400 transition-colors"
              active-class="!text-amber-400"
            >
              Find Artisans
            </router-link>
            <!-- Admin link only visible to admins -->
            <router-link
              v-if="isAdmin()"
              to="/admin"
              class="text-sm font-medium text-white/80 hover:text-amber-400 transition-colors"
              active-class="!text-amber-400"
            >
              Admin
            </router-link>
          </div>

          <!-- Right side buttons -->
          <div class="hidden md:flex items-center gap-3">
            <!-- Logged-in admin: show name + logout -->
            <template v-if="isAuthenticated()">
              <span class="text-sm text-amber-400 font-medium">{{ authState.user?.name }}</span>
              <button
                @click="handleLogout"
                class="px-5 py-2 border border-white/30 text-white text-sm font-medium rounded-lg hover:bg-white/10 transition-all duration-300"
              >
                Logout
              </button>
            </template>
            <!-- Not logged in: show login + get started -->
            <template v-else>
              <router-link
                to="/login"
                class="px-5 py-2 border border-white/30 text-white text-sm font-medium rounded-lg hover:bg-white/10 transition-all duration-300"
              >
                Admin Login
              </router-link>
              <router-link
                to="/search"
                class="px-6 py-2.5 bg-amber-500 text-white text-sm font-semibold rounded-lg hover:bg-amber-600 transition-all duration-300 shadow-lg shadow-amber-500/25"
              >
                Get Started
              </router-link>
            </template>
          </div>

          <!-- Mobile menu button -->
          <button
            @click="mobileMenuOpen = !mobileMenuOpen"
            class="md:hidden text-white p-2"
          >
            <svg v-if="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Mobile menu -->
        <div
          v-if="mobileMenuOpen"
          class="md:hidden mt-4 bg-[#1a1a2e]/95 backdrop-blur-lg rounded-xl p-4 border border-white/10"
        >
          <div class="flex flex-col gap-3">
            <router-link to="/" class="text-white/80 hover:text-amber-400 py-2 px-3 rounded-lg hover:bg-white/5 transition" @click="mobileMenuOpen = false">Home</router-link>
            <router-link to="/search" class="text-white/80 hover:text-amber-400 py-2 px-3 rounded-lg hover:bg-white/5 transition" @click="mobileMenuOpen = false">Find Artisans</router-link>
            <router-link v-if="isAdmin()" to="/admin" class="text-white/80 hover:text-amber-400 py-2 px-3 rounded-lg hover:bg-white/5 transition" @click="mobileMenuOpen = false">Admin Dashboard</router-link>

            <template v-if="isAuthenticated()">
              <div class="border-t border-white/10 pt-3 mt-1">
                <div class="text-amber-400 text-sm px-3 mb-2">{{ authState.user?.name }}</div>
                <button @click="handleLogout(); mobileMenuOpen = false" class="w-full text-left text-white/80 hover:text-red-400 py-2 px-3 rounded-lg hover:bg-white/5 transition">Logout</button>
              </div>
            </template>
            <template v-else>
              <div class="border-t border-white/10 pt-3 mt-1">
                <router-link to="/login" class="text-white/80 hover:text-amber-400 py-2 px-3 rounded-lg hover:bg-white/5 transition block" @click="mobileMenuOpen = false">Admin Login</router-link>
              </div>
              <router-link to="/search" class="mt-1 py-2.5 bg-amber-500 text-white text-center font-semibold rounded-lg" @click="mobileMenuOpen = false">Get Started</router-link>
            </template>
          </div>
        </div>
      </div>
    </nav>

    <!-- Main content -->
    <main>
      <router-view />
    </main>

    <!-- Footer (hidden on login page) -->
    <footer v-if="!hideLayout" class="bg-[#1a1a2e] text-white">
      <!-- Main footer -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-10">
          <!-- Brand -->
          <div>
            <div class="flex items-center gap-2 mb-4">
              <div class="w-10 h-10 bg-amber-500 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
              </div>
              <span class="text-xl font-bold">Hirfati</span>
            </div>
            <p class="text-gray-400 text-sm leading-relaxed">
              Morocco's premier artisan trust platform. Connecting you with verified professionals through our dynamic trust scoring system.
            </p>
            <div class="flex gap-3 mt-6">
              <a href="#" class="w-9 h-9 bg-white/10 rounded-full flex items-center justify-center hover:bg-amber-500 transition-colors">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg>
              </a>
              <a href="#" class="w-9 h-9 bg-white/10 rounded-full flex items-center justify-center hover:bg-amber-500 transition-colors">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm3 8h-1.35c-.538 0-.65.221-.65.778v1.222h2l-.209 2h-1.791v7h-3v-7h-2v-2h2v-2.308c0-1.769.931-2.692 3.029-2.692h1.971v3z"/></svg>
              </a>
              <a href="#" class="w-9 h-9 bg-white/10 rounded-full flex items-center justify-center hover:bg-amber-500 transition-colors">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
              </a>
            </div>
          </div>

          <!-- Quick Links -->
          <div>
            <h4 class="text-lg font-semibold mb-4">Quick Links</h4>
            <ul class="space-y-3">
              <li><router-link to="/" class="text-gray-400 hover:text-amber-400 transition-colors text-sm">Home</router-link></li>
              <li><router-link to="/search" class="text-gray-400 hover:text-amber-400 transition-colors text-sm">Find Artisans</router-link></li>
              <li><a href="#about" class="text-gray-400 hover:text-amber-400 transition-colors text-sm">About Us</a></li>
              <li><a href="#services" class="text-gray-400 hover:text-amber-400 transition-colors text-sm">Services</a></li>
            </ul>
          </div>

          <!-- Services -->
          <div>
            <h4 class="text-lg font-semibold mb-4">Services</h4>
            <ul class="space-y-3">
              <li><a href="#" class="text-gray-400 hover:text-amber-400 transition-colors text-sm">Plumbing</a></li>
              <li><a href="#" class="text-gray-400 hover:text-amber-400 transition-colors text-sm">Electrical</a></li>
              <li><a href="#" class="text-gray-400 hover:text-amber-400 transition-colors text-sm">Carpentry</a></li>
              <li><a href="#" class="text-gray-400 hover:text-amber-400 transition-colors text-sm">Painting</a></li>
              <li><a href="#" class="text-gray-400 hover:text-amber-400 transition-colors text-sm">Renovation</a></li>
            </ul>
          </div>

          <!-- Contact -->
          <div>
            <h4 class="text-lg font-semibold mb-4">Contact</h4>
            <ul class="space-y-3">
              <li class="flex items-center gap-3 text-gray-400 text-sm">
                <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Casablanca, Morocco
              </li>
              <li class="flex items-center gap-3 text-gray-400 text-sm">
                <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                +212 5XX-XXXXXX
              </li>
              <li class="flex items-center gap-3 text-gray-400 text-sm">
                <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                contact@hirfati.ma
              </li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Bottom bar -->
      <div class="border-t border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 flex flex-col sm:flex-row items-center justify-between gap-3">
          <p class="text-gray-500 text-sm">&copy; 2026 Hirfati. All rights reserved.</p>
          <div class="flex gap-6">
            <a href="#" class="text-gray-500 hover:text-amber-400 text-sm transition-colors">Privacy Policy</a>
            <a href="#" class="text-gray-500 hover:text-amber-400 text-sm transition-colors">Terms of Service</a>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuth } from './composables/useAuth.js';

const route = useRoute();
const router = useRouter();
const { state: authState, isAdmin, isAuthenticated, logout } = useAuth();

const scrolled = ref(false);
const mobileMenuOpen = ref(false);

// Hide navbar and footer on login page
const hideLayout = computed(() => route.meta?.hideLayout === true);

function handleScroll() {
  scrolled.value = window.scrollY > 50;
}

async function handleLogout() {
  await logout();
  router.push('/');
}

onMounted(() => {
  window.addEventListener('scroll', handleScroll);
  handleScroll();
});

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
});
</script>
