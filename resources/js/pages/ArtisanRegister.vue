<template>
  <div class="min-h-screen">
    <!-- Hero Banner -->
    <section class="relative pt-32 pb-16 overflow-hidden">
      <div class="absolute inset-0 bg-[#1a1a2e]">
        <img
          src="https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=1920&q=80"
          alt="Artisan at work"
          class="w-full h-full object-cover opacity-20"
        />
        <div class="absolute inset-0 bg-gradient-to-b from-[#1a1a2e]/80 to-[#1a1a2e]"></div>
      </div>
      <div class="relative z-10 max-w-3xl mx-auto px-4 text-center">
        <span class="inline-block px-4 py-1.5 bg-amber-500/20 text-amber-400 text-sm font-semibold rounded-full mb-4 border border-amber-500/30">
          Join Our Network
        </span>
        <h1 class="text-3xl sm:text-4xl font-bold text-white">Become a Hirfati Artisan</h1>
        <p class="text-gray-400 mt-4 max-w-xl mx-auto">
          Create your professional profile and start receiving job requests from clients across Morocco.
        </p>
      </div>
    </section>

    <!-- Registration Form -->
    <section class="max-w-3xl mx-auto px-4 -mt-8 pb-16 relative z-20">
      <!-- Success State -->
      <div v-if="success" class="bg-white rounded-2xl shadow-xl p-8 text-center">
        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
          <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
        </div>
        <h2 class="text-2xl font-bold text-gray-900">Profile Created Successfully!</h2>
        <p class="text-gray-500 mt-3">Your artisan profile is now live. Clients can find you through our search.</p>
        <div class="flex justify-center gap-4 mt-8">
          <router-link
            :to="`/artisan/${createdArtisanId}`"
            class="px-6 py-2.5 bg-amber-500 text-white font-semibold rounded-lg hover:bg-amber-600 transition-colors"
          >
            View My Profile
          </router-link>
          <router-link
            to="/"
            class="px-6 py-2.5 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition-colors"
          >
            Back to Home
          </router-link>
        </div>
      </div>

      <!-- Form -->
      <form v-else @submit.prevent="submitRegistration" class="bg-white rounded-2xl shadow-xl overflow-hidden">
        <!-- Step Indicators -->
        <div class="flex border-b">
          <button
            v-for="(stepInfo, idx) in steps"
            :key="idx"
            type="button"
            @click="goToStep(idx)"
            :class="[
              'flex-1 py-4 text-sm font-medium text-center transition-colors relative',
              step === idx ? 'text-amber-600 bg-amber-50' : (idx < step ? 'text-green-600' : 'text-gray-400')
            ]"
          >
            <div class="flex items-center justify-center gap-2">
              <span
                :class="[
                  'w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold',
                  step === idx ? 'bg-amber-500 text-white' : (idx < step ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-500')
                ]"
              >
                <svg v-if="idx < step" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                </svg>
                <span v-else>{{ idx + 1 }}</span>
              </span>
              <span class="hidden sm:inline">{{ stepInfo.label }}</span>
            </div>
            <div
              v-if="step === idx"
              class="absolute bottom-0 left-0 right-0 h-0.5 bg-amber-500"
            ></div>
          </button>
        </div>

        <!-- Error message -->
        <div v-if="formError" class="mx-6 mt-6 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
          {{ formError }}
        </div>

        <!-- Step 1: Personal Information -->
        <div v-show="step === 0" class="p-6 sm:p-8 space-y-5">
          <h2 class="text-xl font-bold text-gray-900 mb-1">Personal Information</h2>
          <p class="text-sm text-gray-500 mb-4">Tell us about yourself so clients can get to know you.</p>

          <div class="grid sm:grid-cols-2 gap-5">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
              <input
                v-model="form.name"
                type="text"
                required
                class="input-field"
                placeholder="e.g. Mohammed Alaoui"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
              <input
                v-model="form.email"
                type="email"
                required
                class="input-field"
                placeholder="your.email@example.com"
              />
            </div>
          </div>

          <div class="grid sm:grid-cols-2 gap-5">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number *</label>
              <input
                v-model="form.phone"
                type="tel"
                required
                class="input-field"
                placeholder="+212 6XX-XXXXXX"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">City / Location *</label>
              <input
                v-model="form.location"
                type="text"
                required
                class="input-field"
                placeholder="e.g. Casablanca, Rabat"
              />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">About You</label>
            <textarea
              v-model="form.bio"
              rows="3"
              class="input-field resize-none"
              placeholder="Tell clients about yourself, your experience, and what makes you stand out..."
            ></textarea>
          </div>
        </div>

        <!-- Step 2: Professional Details -->
        <div v-show="step === 1" class="p-6 sm:p-8 space-y-5">
          <h2 class="text-xl font-bold text-gray-900 mb-1">Professional Details</h2>
          <p class="text-sm text-gray-500 mb-4">Share your expertise and skills.</p>

          <div class="grid sm:grid-cols-2 gap-5">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Service Category *</label>
              <select v-model="form.service_category" required class="input-field">
                <option value="">Select a category</option>
                <option value="Plumbing">Plumbing</option>
                <option value="Electrical">Electrical</option>
                <option value="Carpentry">Carpentry</option>
                <option value="Painting">Painting</option>
                <option value="Renovation">Renovation</option>
                <option value="HVAC">HVAC</option>
                <option value="Masonry">Masonry</option>
                <option value="Welding">Welding</option>
                <option value="Tiling">Tiling</option>
                <option value="Gardening">Gardening</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Specialty</label>
              <input
                v-model="form.specialty"
                type="text"
                class="input-field"
                placeholder="e.g. Kitchen renovation, AC repair"
              />
            </div>
          </div>

          <div class="grid sm:grid-cols-2 gap-5">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Years of Experience</label>
              <input
                v-model="form.experience_years"
                type="number"
                min="0"
                max="50"
                class="input-field"
                placeholder="e.g. 5"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Hourly Rate (MAD)</label>
              <input
                v-model="form.hourly_rate"
                type="number"
                step="0.01"
                min="0"
                class="input-field"
                placeholder="e.g. 150.00"
              />
            </div>
          </div>
        </div>

        <!-- Step 3: Services Offered -->
        <div v-show="step === 2" class="p-6 sm:p-8 space-y-5">
          <div class="flex items-center justify-between mb-1">
            <div>
              <h2 class="text-xl font-bold text-gray-900">Services You Offer</h2>
              <p class="text-sm text-gray-500 mt-1">Add specific services you provide (optional, up to 5).</p>
            </div>
            <button
              v-if="form.services.length < 5"
              type="button"
              @click="addService()"
              class="px-3 py-1.5 bg-amber-500 text-white text-sm font-semibold rounded-lg hover:bg-amber-600 transition-colors flex items-center gap-1"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              Add Service
            </button>
          </div>

          <div v-if="form.services.length === 0" class="text-center py-10 bg-gray-50 rounded-xl border-2 border-dashed border-gray-200">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
            <p class="text-gray-500 text-sm">No services added yet.</p>
            <p class="text-gray-400 text-xs mt-1">Click "Add Service" to list your offerings.</p>
          </div>

          <div v-for="(service, idx) in form.services" :key="idx" class="bg-gray-50 rounded-xl p-5 border border-gray-200 relative">
            <button
              type="button"
              @click="removeService(idx)"
              class="absolute top-3 right-3 p-1 text-gray-400 hover:text-red-500 transition-colors"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>

            <div class="grid sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Service Name *</label>
                <input
                  v-model="service.name"
                  type="text"
                  required
                  class="input-field"
                  placeholder="e.g. Pipe repair"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Base Price (MAD)</label>
                <input
                  v-model="service.base_price"
                  type="number"
                  step="0.01"
                  min="0"
                  class="input-field"
                  placeholder="e.g. 200.00"
                />
              </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4 mt-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <input
                  v-model="service.description"
                  type="text"
                  class="input-field"
                  placeholder="Brief description"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Duration (minutes)</label>
                <input
                  v-model="service.duration_estimate"
                  type="number"
                  min="0"
                  class="input-field"
                  placeholder="e.g. 60"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Navigation buttons -->
        <div class="flex items-center justify-between p-6 sm:p-8 pt-0 border-t mt-2">
          <button
            v-if="step > 0"
            type="button"
            @click="step--"
            class="px-5 py-2.5 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
          >
            Previous
          </button>
          <div v-else></div>

          <button
            v-if="step < 2"
            type="button"
            @click="nextStep()"
            class="px-6 py-2.5 bg-amber-500 text-white text-sm font-semibold rounded-lg hover:bg-amber-600 transition-colors"
          >
            Next Step
          </button>
          <button
            v-else
            type="submit"
            :disabled="submitting"
            class="px-8 py-2.5 bg-amber-500 text-white text-sm font-semibold rounded-lg hover:bg-amber-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
          >
            <svg v-if="submitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
            </svg>
            {{ submitting ? 'Creating Profile...' : 'Create My Profile' }}
          </button>
        </div>
      </form>

      <!-- Trust info -->
      <div class="mt-8 grid sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow p-5 text-center">
          <div class="w-10 h-10 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center mx-auto mb-3">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
          </div>
          <h4 class="font-semibold text-gray-900 text-sm">Trust Score</h4>
          <p class="text-xs text-gray-500 mt-1">Your trust score grows as you complete jobs and earn great reviews.</p>
        </div>
        <div class="bg-white rounded-xl shadow p-5 text-center">
          <div class="w-10 h-10 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center mx-auto mb-3">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
          </div>
          <h4 class="font-semibold text-gray-900 text-sm">Get Discovered</h4>
          <p class="text-xs text-gray-500 mt-1">Your profile is visible to thousands of clients searching for artisans.</p>
        </div>
        <div class="bg-white rounded-xl shadow p-5 text-center">
          <div class="w-10 h-10 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center mx-auto mb-3">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <h4 class="font-semibold text-gray-900 text-sm">Free to Join</h4>
          <p class="text-xs text-gray-500 mt-1">Creating your profile is completely free. Start receiving jobs today.</p>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import api from '../api.js';

const step = ref(0);
const submitting = ref(false);
const formError = ref('');
const success = ref(false);
const createdArtisanId = ref(null);

const steps = [
  { label: 'Personal Info' },
  { label: 'Professional' },
  { label: 'Services' },
];

const form = reactive({
  name: '',
  email: '',
  phone: '',
  location: '',
  bio: '',
  service_category: '',
  specialty: '',
  experience_years: '',
  hourly_rate: '',
  services: [],
});

function goToStep(idx) {
  if (idx < step.value) {
    step.value = idx;
  }
}

function nextStep() {
  // Validate current step before proceeding
  if (step.value === 0) {
    if (!form.name || !form.email || !form.phone || !form.location) {
      formError.value = 'Please fill in all required fields.';
      return;
    }
  }
  if (step.value === 1) {
    if (!form.service_category) {
      formError.value = 'Please select a service category.';
      return;
    }
  }
  formError.value = '';
  step.value++;
}

function addService() {
  if (form.services.length < 5) {
    form.services.push({ name: '', description: '', base_price: '', duration_estimate: '' });
  }
}

function removeService(idx) {
  form.services.splice(idx, 1);
}

async function submitRegistration() {
  submitting.value = true;
  formError.value = '';

  try {
    const data = { ...form };

    // Clean up empty optional fields
    if (!data.hourly_rate) data.hourly_rate = null;
    if (!data.specialty) data.specialty = null;
    if (!data.experience_years) data.experience_years = null;
    if (!data.bio) data.bio = null;

    // Clean up services
    data.services = data.services
      .filter(s => s.name)
      .map(s => ({
        name: s.name,
        description: s.description || null,
        base_price: s.base_price || null,
        duration_estimate: s.duration_estimate || null,
      }));

    if (data.services.length === 0) delete data.services;

    const res = await api.registerArtisan(data);
    createdArtisanId.value = res.data.artisan.id;
    success.value = true;
    window.scrollTo({ top: 0, behavior: 'smooth' });
  } catch (e) {
    const msg = e.response?.data?.message || e.response?.data?.errors;
    if (msg && typeof msg === 'object') {
      formError.value = Object.values(msg).flat().join(' ');
    } else {
      formError.value = msg || 'An error occurred. Please try again.';
    }
  } finally {
    submitting.value = false;
  }
}
</script>

<style scoped>
.input-field {
  width: 100%;
  padding: 0.625rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  line-height: 1.25rem;
  outline: none;
  transition: border-color 0.15s, box-shadow 0.15s;
}
.input-field:focus {
  border-color: #f59e0b;
  box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.3);
}
</style>
