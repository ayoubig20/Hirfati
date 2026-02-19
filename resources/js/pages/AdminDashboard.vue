<template>
  <div class="max-w-7xl mx-auto px-4 pt-24 pb-8">
    <div class="flex items-center justify-between mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Admin Dashboard</h1>
      <span class="px-3 py-1 bg-amber-100 text-amber-700 text-sm font-semibold rounded-full">Admin Panel</span>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
      <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm text-gray-500">Total Artisans</div>
        <div class="text-3xl font-bold text-gray-900 mt-1">{{ stats.totalArtisans }}</div>
      </div>
      <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm text-gray-500">Avg Trust Score</div>
        <div class="text-3xl font-bold text-blue-600 mt-1">{{ (stats.avgTrust * 100).toFixed(1) }}%</div>
      </div>
      <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm text-gray-500">Gold Tier</div>
        <div class="text-3xl font-bold text-yellow-500 mt-1">{{ stats.goldCount }}</div>
      </div>
      <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm text-gray-500">Fraud Alerts</div>
        <div class="text-3xl font-bold text-red-600 mt-1">{{ fraudAlerts.length }}</div>
      </div>
    </div>

    <!-- Artisan Management -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold text-gray-900">Artisan Management</h2>
        <button
          @click="openAddModal()"
          class="px-4 py-2 bg-amber-500 text-white text-sm font-semibold rounded-lg hover:bg-amber-600 transition-colors flex items-center gap-2"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Add Artisan
        </button>
      </div>

      <!-- Search & Filter -->
      <div class="flex flex-col sm:flex-row gap-3 mb-4">
        <input
          v-model="artisanSearch"
          type="text"
          placeholder="Search by name, email, or category..."
          class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none"
        />
        <select
          v-model="statusFilter"
          class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none"
        >
          <option value="">All Statuses</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
          <option value="suspended">Suspended</option>
        </select>
      </div>

      <!-- Artisan Table -->
      <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
          <thead class="text-xs text-gray-500 uppercase bg-gray-50">
            <tr>
              <th class="px-4 py-3">Name</th>
              <th class="px-4 py-3">Email</th>
              <th class="px-4 py-3">Category</th>
              <th class="px-4 py-3">Location</th>
              <th class="px-4 py-3">Trust Score</th>
              <th class="px-4 py-3">Status</th>
              <th class="px-4 py-3 text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading" class="border-b">
              <td colspan="7" class="px-4 py-8 text-center text-gray-500">Loading artisans...</td>
            </tr>
            <tr v-else-if="filteredArtisans.length === 0" class="border-b">
              <td colspan="7" class="px-4 py-8 text-center text-gray-500">No artisans found.</td>
            </tr>
            <tr v-for="artisan in paginatedArtisans" :key="artisan.id" class="border-b hover:bg-gray-50">
              <td class="px-4 py-3 font-medium">
                <router-link :to="`/artisan/${artisan.id}`" class="text-blue-600 hover:underline">
                  {{ artisan.name }}
                </router-link>
              </td>
              <td class="px-4 py-3 text-gray-600">{{ artisan.email }}</td>
              <td class="px-4 py-3">{{ artisan.service_category }}</td>
              <td class="px-4 py-3">{{ artisan.location }}</td>
              <td class="px-4 py-3">
                <span
                  :class="trustScoreClass(artisan.trust_score)"
                  class="px-2 py-1 rounded text-xs font-semibold"
                >
                  {{ (parseFloat(artisan.trust_score) * 100).toFixed(1) }}%
                </span>
              </td>
              <td class="px-4 py-3">
                <span
                  :class="statusClass(artisan.status)"
                  class="px-2 py-1 rounded-full text-xs font-semibold"
                >
                  {{ artisan.status }}
                </span>
              </td>
              <td class="px-4 py-3 text-right">
                <div class="flex items-center justify-end gap-2">
                  <button
                    @click="openEditModal(artisan)"
                    class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                    title="Edit"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>
                  <button
                    @click="confirmDelete(artisan)"
                    class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                    title="Delete"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="totalPages > 1" class="flex items-center justify-between mt-4 pt-4 border-t">
        <div class="text-sm text-gray-500">
          Showing {{ (currentPage - 1) * perPage + 1 }}-{{ Math.min(currentPage * perPage, filteredArtisans.length) }} of {{ filteredArtisans.length }}
        </div>
        <div class="flex gap-1">
          <button
            @click="currentPage = Math.max(1, currentPage - 1)"
            :disabled="currentPage === 1"
            class="px-3 py-1 text-sm border rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Previous
          </button>
          <button
            v-for="page in visiblePages"
            :key="page"
            @click="currentPage = page"
            :class="[
              'px-3 py-1 text-sm border rounded-lg',
              page === currentPage ? 'bg-amber-500 text-white border-amber-500' : 'hover:bg-gray-50'
            ]"
          >
            {{ page }}
          </button>
          <button
            @click="currentPage = Math.min(totalPages, currentPage + 1)"
            :disabled="currentPage === totalPages"
            class="px-3 py-1 text-sm border rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Next
          </button>
        </div>
      </div>
    </div>

    <!-- Trust Distribution -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
      <h2 class="text-xl font-bold text-gray-900 mb-4">Trust Score Distribution</h2>
      <div class="flex items-end gap-1 h-40">
        <div
          v-for="(count, bucket) in distribution"
          :key="bucket"
          class="flex-1 bg-blue-500 rounded-t transition-all hover:bg-blue-600"
          :style="{ height: (count / maxDistribution * 100) + '%' }"
          :title="`${bucket}: ${count} artisans`"
        ></div>
      </div>
      <div class="flex gap-1 mt-1">
        <div v-for="(_, bucket) in distribution" :key="bucket" class="flex-1 text-xs text-gray-500 text-center">
          {{ bucket }}
        </div>
      </div>
    </div>

    <!-- Fraud Alerts -->
    <div class="bg-white rounded-lg shadow p-6">
      <h2 class="text-xl font-bold text-gray-900 mb-4">Fraud Detection Alerts</h2>
      <div v-if="fraudAlerts.length === 0" class="text-gray-500">No fraud alerts.</div>
      <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
          <thead class="text-xs text-gray-500 uppercase bg-gray-50">
            <tr>
              <th class="px-4 py-3">Artisan</th>
              <th class="px-4 py-3">Category</th>
              <th class="px-4 py-3">Rating Component</th>
              <th class="px-4 py-3">Completion Component</th>
              <th class="px-4 py-3">Penalty</th>
              <th class="px-4 py-3">Trust Score</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="alert in fraudAlerts" :key="alert.id" class="border-b hover:bg-gray-50">
              <td class="px-4 py-3 font-medium">
                <router-link :to="`/artisan/${alert.id}`" class="text-blue-600 hover:underline">
                  {{ alert.name }}
                </router-link>
              </td>
              <td class="px-4 py-3">{{ alert.service_category }}</td>
              <td class="px-4 py-3">{{ (parseFloat(alert.rating_component) * 100).toFixed(1) }}%</td>
              <td class="px-4 py-3">{{ (parseFloat(alert.completion_component) * 100).toFixed(1) }}%</td>
              <td class="px-4 py-3">
                <span class="px-2 py-1 bg-red-100 text-red-800 rounded text-xs font-semibold">
                  {{ (parseFloat(alert.penalty) * 100).toFixed(1) }}%
                </span>
              </td>
              <td class="px-4 py-3">{{ (parseFloat(alert.trust_score) * 100).toFixed(1) }}%</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center">
      <div class="absolute inset-0 bg-black/50" @click="closeModal()"></div>
      <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between p-6 border-b">
          <h3 class="text-lg font-bold text-gray-900">
            {{ isEditing ? 'Edit Artisan' : 'Add New Artisan' }}
          </h3>
          <button @click="closeModal()" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <form @submit.prevent="submitForm()" class="p-6 space-y-4">
          <div v-if="formError" class="p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
            {{ formError }}
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
            <input
              v-model="form.name"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none"
              placeholder="Full name"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
            <input
              v-model="form.email"
              type="email"
              :required="!isEditing"
              :disabled="isEditing"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none disabled:bg-gray-100 disabled:text-gray-500"
              placeholder="email@example.com"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
            <input
              v-model="form.phone"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none"
              placeholder="+212 6XX-XXXXXX"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Service Category *</label>
            <input
              v-model="form.service_category"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none"
              placeholder="e.g. Plumbing, Electrical, Carpentry"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Specialty</label>
            <input
              v-model="form.specialty"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none"
              placeholder="e.g. Kitchen renovation, AC installation"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Location *</label>
            <input
              v-model="form.location"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none"
              placeholder="e.g. Casablanca, Rabat, Marrakech"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Hourly Rate (MAD)</label>
            <input
              v-model="form.hourly_rate"
              type="number"
              step="0.01"
              min="0"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none"
              placeholder="0.00"
            />
          </div>

          <div v-if="isEditing">
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select
              v-model="form.status"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none"
            >
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
              <option value="suspended">Suspended</option>
            </select>
          </div>

          <div class="flex justify-end gap-3 pt-4 border-t">
            <button
              type="button"
              @click="closeModal()"
              class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="submitting"
              class="px-6 py-2 text-sm font-semibold text-white bg-amber-500 rounded-lg hover:bg-amber-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ submitting ? 'Saving...' : (isEditing ? 'Update Artisan' : 'Add Artisan') }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center">
      <div class="absolute inset-0 bg-black/50" @click="showDeleteModal = false"></div>
      <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-md mx-4">
        <div class="p-6">
          <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0">
              <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
              </svg>
            </div>
            <div>
              <h3 class="text-lg font-bold text-gray-900">Delete Artisan</h3>
              <p class="text-sm text-gray-500">This action cannot be undone.</p>
            </div>
          </div>
          <p class="text-gray-700 mb-6">
            Are you sure you want to delete <strong>{{ artisanToDelete?.name }}</strong>? All associated data (orders, reviews, trust scores) may be affected.
          </p>
          <div class="flex justify-end gap-3">
            <button
              @click="showDeleteModal = false"
              class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
            >
              Cancel
            </button>
            <button
              @click="deleteArtisan()"
              :disabled="deleting"
              class="px-4 py-2 text-sm font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors disabled:opacity-50"
            >
              {{ deleting ? 'Deleting...' : 'Delete' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Success Toast -->
    <div
      v-if="toast.show"
      class="fixed bottom-6 right-6 z-50 flex items-center gap-3 px-5 py-3 rounded-lg shadow-lg text-white text-sm font-medium transition-all duration-300"
      :class="toast.type === 'success' ? 'bg-green-600' : 'bg-red-600'"
    >
      <svg v-if="toast.type === 'success'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
      </svg>
      <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
      </svg>
      {{ toast.message }}
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import api from '../api.js';

const fraudAlerts = ref([]);
const allArtisans = ref([]);
const stats = ref({ totalArtisans: 0, avgTrust: 0, goldCount: 0 });
const loading = ref(true);

// Artisan management state
const artisanSearch = ref('');
const statusFilter = ref('');
const currentPage = ref(1);
const perPage = 10;

// Modal state
const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const submitting = ref(false);
const formError = ref('');

// Delete state
const showDeleteModal = ref(false);
const artisanToDelete = ref(null);
const deleting = ref(false);

// Toast
const toast = ref({ show: false, message: '', type: 'success' });

// Form
const defaultForm = {
  name: '',
  email: '',
  phone: '',
  service_category: '',
  specialty: '',
  location: '',
  hourly_rate: '',
  status: 'active',
};
const form = ref({ ...defaultForm });

const distribution = ref({
  '0-10': 0, '10-20': 0, '20-30': 0, '30-40': 0, '40-50': 0,
  '50-60': 0, '60-70': 0, '70-80': 0, '80-90': 0, '90-100': 0,
});

const maxDistribution = computed(() => Math.max(1, ...Object.values(distribution.value)));

// Filtered artisans
const filteredArtisans = computed(() => {
  let list = allArtisans.value;

  if (artisanSearch.value) {
    const q = artisanSearch.value.toLowerCase();
    list = list.filter(a =>
      a.name.toLowerCase().includes(q) ||
      a.email.toLowerCase().includes(q) ||
      a.service_category.toLowerCase().includes(q) ||
      a.location.toLowerCase().includes(q)
    );
  }

  if (statusFilter.value) {
    list = list.filter(a => a.status === statusFilter.value);
  }

  return list;
});

const totalPages = computed(() => Math.ceil(filteredArtisans.value.length / perPage));

const paginatedArtisans = computed(() => {
  const start = (currentPage.value - 1) * perPage;
  return filteredArtisans.value.slice(start, start + perPage);
});

const visiblePages = computed(() => {
  const pages = [];
  const start = Math.max(1, currentPage.value - 2);
  const end = Math.min(totalPages.value, start + 4);
  for (let i = start; i <= end; i++) pages.push(i);
  return pages;
});

function trustScoreClass(score) {
  const pct = parseFloat(score) * 100;
  if (pct >= 85) return 'bg-yellow-100 text-yellow-800';
  if (pct >= 70) return 'bg-gray-200 text-gray-800';
  if (pct >= 50) return 'bg-orange-100 text-orange-800';
  return 'bg-red-100 text-red-800';
}

function statusClass(status) {
  if (status === 'active') return 'bg-green-100 text-green-800';
  if (status === 'inactive') return 'bg-gray-100 text-gray-800';
  return 'bg-red-100 text-red-800';
}

function showToast(message, type = 'success') {
  toast.value = { show: true, message, type };
  setTimeout(() => { toast.value.show = false; }, 3000);
}

function openAddModal() {
  isEditing.value = false;
  editingId.value = null;
  form.value = { ...defaultForm };
  formError.value = '';
  showModal.value = true;
}

function openEditModal(artisan) {
  isEditing.value = true;
  editingId.value = artisan.id;
  form.value = {
    name: artisan.name,
    email: artisan.email,
    phone: artisan.phone || '',
    service_category: artisan.service_category,
    specialty: artisan.specialty || '',
    location: artisan.location,
    hourly_rate: artisan.hourly_rate || '',
    status: artisan.status,
  };
  formError.value = '';
  showModal.value = true;
}

function closeModal() {
  showModal.value = false;
  formError.value = '';
}

async function submitForm() {
  submitting.value = true;
  formError.value = '';

  try {
    const data = { ...form.value };
    if (data.hourly_rate === '') data.hourly_rate = null;
    if (data.phone === '') data.phone = null;
    if (data.specialty === '') data.specialty = null;

    if (isEditing.value) {
      // Remove email from update payload
      delete data.email;
      await api.updateArtisan(editingId.value, data);
      showToast('Artisan updated successfully');
    } else {
      await api.createArtisan(data);
      showToast('Artisan added successfully');
    }

    closeModal();
    await loadArtisans();
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

function confirmDelete(artisan) {
  artisanToDelete.value = artisan;
  showDeleteModal.value = true;
}

async function deleteArtisan() {
  deleting.value = true;
  try {
    await api.deleteArtisan(artisanToDelete.value.id);
    showToast('Artisan deleted successfully');
    showDeleteModal.value = false;
    artisanToDelete.value = null;
    await loadArtisans();
  } catch (e) {
    showToast(e.response?.data?.message || 'Failed to delete artisan', 'error');
  } finally {
    deleting.value = false;
  }
}

async function loadArtisans() {
  try {
    const res = await api.getArtisans({ per_page: 100 });
    allArtisans.value = res.data.data;
    stats.value.totalArtisans = res.data.total;

    if (allArtisans.value.length) {
      stats.value.avgTrust = allArtisans.value.reduce((sum, a) => sum + parseFloat(a.trust_score), 0) / allArtisans.value.length;
      stats.value.goldCount = allArtisans.value.filter(a => parseFloat(a.trust_score) >= 0.85).length;
    }

    // Build distribution
    const dist = { '0-10': 0, '10-20': 0, '20-30': 0, '30-40': 0, '40-50': 0, '50-60': 0, '60-70': 0, '70-80': 0, '80-90': 0, '90-100': 0 };
    allArtisans.value.forEach(a => {
      const pct = parseFloat(a.trust_score) * 100;
      const bucket = Math.min(Math.floor(pct / 10), 9);
      const keys = Object.keys(dist);
      dist[keys[bucket]]++;
    });
    distribution.value = dist;

    // Reset to page 1 if current page out of range
    if (currentPage.value > totalPages.value && totalPages.value > 0) {
      currentPage.value = totalPages.value;
    }
  } catch (e) {
    console.error('Failed to load artisans', e);
  }
}

onMounted(async () => {
  loading.value = true;
  try {
    const [, alertsRes] = await Promise.all([
      loadArtisans(),
      api.getFraudAlerts(),
    ]);
    fraudAlerts.value = alertsRes.data;
  } catch (e) {
    console.error('Failed to load admin data', e);
  } finally {
    loading.value = false;
  }
});
</script>
