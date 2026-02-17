<template>
  <div class="homepage">
    <!-- ==================== HERO SECTION ==================== -->
    <section class="relative min-h-screen flex items-center overflow-hidden">
      <!-- Background with overlay -->
      <div class="absolute inset-0 bg-[#1a1a2e]">
        <img
          src="https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=1920&q=80"
          alt="Handyman at work"
          class="w-full h-full object-cover opacity-30"
        />
        <div class="absolute inset-0 bg-gradient-to-r from-[#1a1a2e]/90 to-[#1a1a2e]/60"></div>
      </div>

      <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 w-full">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
          <!-- Left content -->
          <div>
            <div class="animate-hero-title">
              <span class="inline-block px-4 py-1.5 bg-amber-500/20 text-amber-400 text-sm font-semibold rounded-full mb-6 border border-amber-500/30">
                #1 Artisan Trust Platform in Morocco
              </span>
              <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-white leading-tight">
                Find Trusted
                <span class="text-amber-500">Artisans</span>
                For Every Job
              </h1>
            </div>
            <p class="animate-hero-subtitle text-lg text-gray-300 mt-6 leading-relaxed max-w-xl">
              Hirfati connects you with verified professionals ranked by our dynamic trust scoring system. Quality service, guaranteed satisfaction.
            </p>
            <div class="animate-hero-buttons flex flex-wrap gap-4 mt-8">
              <router-link
                to="/search"
                class="px-8 py-3.5 bg-amber-500 text-white font-semibold rounded-lg hover:bg-amber-600 transition-all duration-300 shadow-lg shadow-amber-500/25 hover:shadow-amber-500/40 hover:-translate-y-0.5"
              >
                Find an Artisan
              </router-link>
              <a
                href="#about"
                class="px-8 py-3.5 border-2 border-white/30 text-white font-semibold rounded-lg hover:bg-white/10 transition-all duration-300 hover:-translate-y-0.5"
              >
                Learn More
              </a>
            </div>
          </div>

          <!-- Right - Hero search card -->
          <div class="animate-hero-search hidden lg:block">
            <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-8 border border-white/20">
              <h3 class="text-xl font-bold text-white mb-6">Quick Search</h3>
              <div class="space-y-4">
                <select
                  v-model="searchCategory"
                  class="quote-input w-full"
                >
                  <option value="" class="text-gray-900">All Categories</option>
                  <option v-for="cat in categories" :key="cat.service_category" :value="cat.service_category" class="text-gray-900">
                    {{ cat.service_category }}
                  </option>
                </select>
                <input
                  v-model="searchLocation"
                  type="text"
                  placeholder="Enter your city..."
                  class="quote-input w-full"
                />
                <button
                  @click="doSearch"
                  class="w-full py-3.5 bg-amber-500 text-white font-semibold rounded-lg hover:bg-amber-600 transition-all duration-300 shadow-lg"
                >
                  Search Artisans
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Scroll indicator -->
      <div class="absolute bottom-8 left-1/2 -translate-x-1/2 animate-float">
        <div class="w-6 h-10 border-2 border-white/40 rounded-full flex justify-center pt-2">
          <div class="w-1.5 h-3 bg-amber-500 rounded-full animate-bounce"></div>
        </div>
      </div>
    </section>

    <!-- ==================== FEATURE CARDS ==================== -->
    <section class="relative -mt-16 z-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid md:grid-cols-3 gap-6 stagger-children">
        <div
          v-for="(feature, i) in featureCards"
          :key="i"
          class="fade-up bg-white rounded-xl shadow-xl p-8 text-center hover:shadow-2xl transition-shadow duration-300"
        >
          <div class="w-16 h-16 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center mx-auto mb-4">
            <component :is="feature.icon" class="w-8 h-8" />
          </div>
          <h3 class="text-xl font-bold text-gray-900">{{ feature.title }}</h3>
          <p class="text-gray-500 mt-2">{{ feature.desc }}</p>
        </div>
      </div>
    </section>

    <!-- ==================== ABOUT SECTION ==================== -->
    <section id="about" class="py-20 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-16 items-center">
          <!-- Left image -->
          <div class="fade-left relative">
            <img
              src="https://images.unsplash.com/photo-1621905251189-08b45d6a269e?w=600&q=80"
              alt="Professional artisan"
              class="rounded-2xl shadow-2xl w-full object-cover h-[500px]"
            />
            <!-- Experience badge -->
            <div class="absolute -bottom-6 -right-6 bg-amber-500 text-white rounded-2xl p-6 shadow-xl animate-float">
              <div class="text-4xl font-bold">10+</div>
              <div class="text-sm font-medium">Years of<br/>Excellence</div>
            </div>
          </div>

          <!-- Right content -->
          <div class="fade-right">
            <span class="text-amber-500 font-semibold text-sm uppercase tracking-wider">About Hirfati</span>
            <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mt-3">
              We Connect You With Morocco's Best Artisans
            </h2>
            <p class="text-gray-600 mt-6 leading-relaxed">
              Hirfati is Morocco's premier artisan trust platform. We use a sophisticated dynamic trust scoring algorithm to rank professionals based on customer reviews, completion rates, and consistency — ensuring you always get the best service.
            </p>
            <div class="grid grid-cols-2 gap-6 mt-8">
              <div class="flex items-start gap-3">
                <div class="w-10 h-10 bg-amber-100 text-amber-600 rounded-lg flex items-center justify-center flex-shrink-0">
                  <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                </div>
                <div>
                  <h4 class="font-semibold text-gray-900">Verified Professionals</h4>
                  <p class="text-sm text-gray-500 mt-1">Every artisan is vetted and verified</p>
                </div>
              </div>
              <div class="flex items-start gap-3">
                <div class="w-10 h-10 bg-amber-100 text-amber-600 rounded-lg flex items-center justify-center flex-shrink-0">
                  <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                </div>
                <div>
                  <h4 class="font-semibold text-gray-900">Trust Scoring</h4>
                  <p class="text-sm text-gray-500 mt-1">AI-powered trust ranking system</p>
                </div>
              </div>
              <div class="flex items-start gap-3">
                <div class="w-10 h-10 bg-amber-100 text-amber-600 rounded-lg flex items-center justify-center flex-shrink-0">
                  <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                </div>
                <div>
                  <h4 class="font-semibold text-gray-900">Secure Payments</h4>
                  <p class="text-sm text-gray-500 mt-1">Safe and transparent transactions</p>
                </div>
              </div>
              <div class="flex items-start gap-3">
                <div class="w-10 h-10 bg-amber-100 text-amber-600 rounded-lg flex items-center justify-center flex-shrink-0">
                  <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                </div>
                <div>
                  <h4 class="font-semibold text-gray-900">24/7 Support</h4>
                  <p class="text-sm text-gray-500 mt-1">Customer support around the clock</p>
                </div>
              </div>
            </div>
            <router-link
              to="/search"
              class="inline-block mt-8 px-8 py-3.5 bg-amber-500 text-white font-semibold rounded-lg hover:bg-amber-600 transition-all duration-300 shadow-lg shadow-amber-500/25"
            >
              Explore Services
            </router-link>
          </div>
        </div>
      </div>
    </section>

    <!-- ==================== STATS COUNTER ==================== -->
    <section class="py-16 bg-[#1a1a2e] relative overflow-hidden">
      <div class="absolute inset-0 opacity-10">
        <div class="absolute top-0 left-0 w-72 h-72 bg-amber-500 rounded-full -translate-x-1/2 -translate-y-1/2"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-amber-500 rounded-full translate-x-1/3 translate-y-1/3"></div>
      </div>
      <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 stagger-children">
          <div v-for="(stat, i) in stats" :key="i" class="fade-up text-center">
            <div class="text-4xl sm:text-5xl font-bold text-amber-500">
              <span ref="counterRefs">{{ stat.animated ? animatedValues[i] : 0 }}</span>{{ stat.suffix }}
            </div>
            <div class="text-gray-400 mt-2 text-sm uppercase tracking-wider font-medium">{{ stat.label }}</div>
          </div>
        </div>
      </div>
    </section>

    <!-- ==================== SERVICES SECTION ==================== -->
    <section id="services" class="py-20 bg-gray-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14 fade-up">
          <span class="text-amber-500 font-semibold text-sm uppercase tracking-wider">What We Offer</span>
          <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mt-3">Our Professional Services</h2>
          <p class="text-gray-500 mt-4 max-w-2xl mx-auto">
            From plumbing to electrical work, our trusted artisans cover every home service need with excellence and reliability.
          </p>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8 stagger-children">
          <div
            v-for="(service, i) in services"
            :key="i"
            class="fade-up service-card bg-white rounded-xl shadow-lg p-8 text-center cursor-pointer"
            @click="searchByCategory(service.category)"
          >
            <div class="service-icon w-16 h-16 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center mx-auto mb-5">
              <span class="text-3xl">{{ service.icon }}</span>
            </div>
            <h3 class="text-xl font-bold text-gray-900">{{ service.name }}</h3>
            <p class="text-gray-500 mt-3 text-sm leading-relaxed">{{ service.desc }}</p>
            <div class="mt-5 text-amber-500 font-semibold text-sm flex items-center justify-center gap-1 group">
              Find Artisans
              <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ==================== SKILLS & PROGRESS ==================== -->
    <section class="py-20 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-16 items-center">
          <!-- Progress bars -->
          <div class="fade-left">
            <span class="text-amber-500 font-semibold text-sm uppercase tracking-wider">Our Expertise</span>
            <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mt-3">Professional Skills</h2>
            <p class="text-gray-500 mt-4">Our artisans are highly skilled across multiple disciplines, ensuring top-quality workmanship on every project.</p>

            <div class="space-y-6 mt-8">
              <div v-for="(skill, i) in skills" :key="i">
                <div class="flex justify-between mb-2">
                  <span class="font-semibold text-gray-700">{{ skill.name }}</span>
                  <span class="font-semibold text-amber-500">{{ skill.animated ? skill.value : 0 }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                  <div
                    class="progress-bar-fill bg-amber-500 h-3 rounded-full"
                    :style="{ width: skill.animated ? skill.value + '%' : '0%' }"
                  ></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Circular progress -->
          <div class="fade-right">
            <div class="grid grid-cols-3 gap-6">
              <div v-for="(circle, i) in circularStats" :key="i" class="text-center">
                <div class="relative inline-block">
                  <svg class="circular-progress w-28 h-28 sm:w-32 sm:h-32" viewBox="0 0 120 120">
                    <circle cx="60" cy="60" r="52" fill="none" stroke="#e5e7eb" stroke-width="8" />
                    <circle
                      class="progress-ring"
                      cx="60" cy="60" r="52"
                      fill="none"
                      stroke="#f59e0b"
                      stroke-width="8"
                      stroke-linecap="round"
                      :stroke-dasharray="circumference"
                      :stroke-dashoffset="circle.animated ? circumference - (circle.value / 100) * circumference : circumference"
                      transform="rotate(-90 60 60)"
                    />
                  </svg>
                  <div class="absolute inset-0 flex items-center justify-center">
                    <span class="text-xl font-bold text-gray-900">{{ circle.animated ? circle.value : 0 }}%</span>
                  </div>
                </div>
                <p class="mt-3 text-sm font-semibold text-gray-700">{{ circle.label }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ==================== CATEGORIES FROM API ==================== -->
    <section v-if="categories.length" class="py-20 bg-gray-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14 fade-up">
          <span class="text-amber-500 font-semibold text-sm uppercase tracking-wider">Browse Categories</span>
          <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mt-3">Service Categories</h2>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 stagger-children">
          <button
            v-for="cat in categories"
            :key="cat.service_category"
            @click="searchByCategory(cat.service_category)"
            class="fade-up bg-white rounded-xl shadow-md p-6 text-left hover:shadow-xl transition-all duration-300 border border-gray-100 hover:-translate-y-1"
          >
            <div class="text-lg font-bold text-gray-900">{{ cat.service_category }}</div>
            <div class="text-sm text-gray-500 mt-1">{{ cat.artisan_count }} artisans</div>
            <div class="mt-3 flex items-center gap-2">
              <div class="flex-1 bg-gray-200 rounded-full h-2">
                <div
                  class="bg-amber-500 h-2 rounded-full transition-all duration-700"
                  :style="{ width: (parseFloat(cat.avg_trust) * 100) + '%' }"
                ></div>
              </div>
              <span class="text-xs font-semibold text-amber-600">{{ (parseFloat(cat.avg_trust) * 100).toFixed(0) }}%</span>
            </div>
          </button>
        </div>
      </div>
    </section>

    <!-- ==================== FEATURED ARTISANS ==================== -->
    <section v-if="featured.length" class="py-20 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14 fade-up">
          <span class="text-amber-500 font-semibold text-sm uppercase tracking-wider">Top Rated</span>
          <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mt-3">Featured Artisans</h2>
          <p class="text-gray-500 mt-4 max-w-2xl mx-auto">
            These artisans have earned the highest trust scores through consistent quality work and customer satisfaction.
          </p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 stagger-children">
          <div v-for="artisan in featured" :key="artisan.id" class="fade-up">
            <ArtisanCard :artisan="artisan" />
          </div>
        </div>
        <div class="text-center mt-10 fade-up">
          <router-link
            to="/search"
            class="inline-block px-8 py-3.5 bg-amber-500 text-white font-semibold rounded-lg hover:bg-amber-600 transition-all duration-300 shadow-lg shadow-amber-500/25"
          >
            View All Artisans
          </router-link>
        </div>
      </div>
    </section>

    <!-- ==================== HOW IT WORKS ==================== -->
    <section class="py-20 bg-[#1a1a2e] relative overflow-hidden">
      <div class="absolute inset-0 opacity-5">
        <div class="absolute top-1/2 left-1/4 w-80 h-80 bg-amber-500 rounded-full"></div>
        <div class="absolute top-1/4 right-1/4 w-60 h-60 bg-amber-500 rounded-full"></div>
      </div>
      <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14 fade-up">
          <span class="text-amber-400 font-semibold text-sm uppercase tracking-wider">Simple Process</span>
          <h2 class="text-3xl sm:text-4xl font-bold text-white mt-3">How It Works</h2>
        </div>
        <div class="grid md:grid-cols-3 gap-8 stagger-children">
          <div v-for="(step, i) in howItWorks" :key="i" class="fade-up text-center">
            <div class="w-20 h-20 bg-amber-500/20 border-2 border-amber-500 text-amber-500 rounded-full flex items-center justify-center mx-auto text-2xl font-bold mb-6">
              {{ String(i + 1).padStart(2, '0') }}
            </div>
            <h3 class="text-xl font-bold text-white">{{ step.title }}</h3>
            <p class="text-gray-400 mt-3">{{ step.desc }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- ==================== TESTIMONIALS ==================== -->
    <section class="py-20 bg-gray-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14 fade-up">
          <span class="text-amber-500 font-semibold text-sm uppercase tracking-wider">Testimonials</span>
          <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mt-3">What Our Clients Say</h2>
        </div>
        <div class="grid md:grid-cols-3 gap-8 stagger-children">
          <div
            v-for="(testimonial, i) in testimonials"
            :key="i"
            class="fade-up bg-white rounded-xl shadow-lg p-8 relative"
          >
            <div class="text-amber-500 text-5xl absolute -top-3 left-6 font-serif">"</div>
            <p class="text-gray-600 mt-4 leading-relaxed italic">{{ testimonial.text }}</p>
            <div class="flex items-center gap-4 mt-6 pt-6 border-t border-gray-100">
              <div class="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center text-amber-600 font-bold text-lg">
                {{ testimonial.name.charAt(0) }}
              </div>
              <div>
                <div class="font-semibold text-gray-900">{{ testimonial.name }}</div>
                <div class="text-sm text-gray-500">{{ testimonial.role }}</div>
              </div>
              <div class="ml-auto flex gap-0.5">
                <svg v-for="s in 5" :key="s" class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ==================== GET A QUOTE / CTA ==================== -->
    <section class="py-20 bg-[#1a1a2e] relative overflow-hidden">
      <div class="absolute inset-0">
        <img
          src="https://images.unsplash.com/photo-1581578731548-c64695cc6952?w=1920&q=80"
          alt="Construction background"
          class="w-full h-full object-cover opacity-10"
        />
      </div>
      <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-16 items-center">
          <div class="fade-left">
            <span class="text-amber-400 font-semibold text-sm uppercase tracking-wider">Get Started</span>
            <h2 class="text-3xl sm:text-4xl font-bold text-white mt-3">Request a Free Quote</h2>
            <p class="text-gray-400 mt-4 leading-relaxed">
              Tell us about your project and we'll connect you with the best-matched artisans in your area. No commitment required.
            </p>
            <div class="flex items-center gap-6 mt-8">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-amber-500/20 rounded-full flex items-center justify-center">
                  <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                </div>
                <div>
                  <div class="text-gray-400 text-xs">Call Us</div>
                  <div class="text-white font-semibold">+212 5XX-XXXXXX</div>
                </div>
              </div>
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-amber-500/20 rounded-full flex items-center justify-center">
                  <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                <div>
                  <div class="text-gray-400 text-xs">Email Us</div>
                  <div class="text-white font-semibold">contact@hirfati.ma</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Quote form -->
          <div class="fade-right">
            <form @submit.prevent class="bg-white/10 backdrop-blur-lg rounded-2xl p-8 border border-white/20 space-y-4">
              <div class="grid sm:grid-cols-2 gap-4">
                <input type="text" placeholder="Your Name" class="quote-input w-full" />
                <input type="email" placeholder="Email Address" class="quote-input w-full" />
              </div>
              <div class="grid sm:grid-cols-2 gap-4">
                <input type="tel" placeholder="Phone Number" class="quote-input w-full" />
                <select class="quote-input w-full">
                  <option value="" class="text-gray-900">Select Service</option>
                  <option v-for="s in services" :key="s.name" :value="s.category" class="text-gray-900">{{ s.name }}</option>
                </select>
              </div>
              <textarea placeholder="Describe your project..." rows="4" class="quote-input w-full resize-none"></textarea>
              <button
                type="submit"
                class="w-full py-3.5 bg-amber-500 text-white font-semibold rounded-lg hover:bg-amber-600 transition-all duration-300 shadow-lg"
              >
                Send Request
              </button>
            </form>
          </div>
        </div>
      </div>
    </section>

    <!-- ==================== NEWSLETTER ==================== -->
    <section class="py-16 bg-amber-500">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row items-center justify-between gap-8">
          <div class="fade-left">
            <h3 class="text-2xl font-bold text-white">Stay Updated</h3>
            <p class="text-amber-100 mt-2">Subscribe to our newsletter for tips and special offers.</p>
          </div>
          <div class="fade-right flex w-full md:w-auto">
            <input
              type="email"
              placeholder="Enter your email..."
              class="flex-1 md:w-80 px-5 py-3.5 rounded-l-lg border-0 focus:outline-none focus:ring-2 focus:ring-amber-300 text-gray-900"
            />
            <button class="px-8 py-3.5 bg-[#1a1a2e] text-white font-semibold rounded-r-lg hover:bg-[#16213e] transition-colors duration-300">
              Subscribe
            </button>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, onUnmounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import api from '../api.js';
import ArtisanCard from '../components/ArtisanCard.vue';

const router = useRouter();
const categories = ref([]);
const featured = ref([]);
const searchCategory = ref('');
const searchLocation = ref('');

// Circumference for circular progress (r=52)
const circumference = 2 * Math.PI * 52;

// ===== Feature Cards =====
const ShieldIcon = {
  template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-8 h-8"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>`
};
const StarIcon = {
  template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-8 h-8"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>`
};
const ClockIcon = {
  template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-8 h-8"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`
};

const featureCards = [
  { icon: ShieldIcon, title: 'Verified & Trusted', desc: 'Every artisan passes our rigorous verification and trust scoring process.' },
  { icon: StarIcon, title: 'Top Quality Work', desc: 'Gold-tier artisans deliver exceptional results backed by real reviews.' },
  { icon: ClockIcon, title: 'Fast & Reliable', desc: 'Quick response times and on-time project completion guaranteed.' },
];

// ===== Stats =====
const stats = reactive([
  { value: 10, suffix: '+', label: 'Years Experience', animated: false },
  { value: 500, suffix: '+', label: 'Pro Artisans', animated: false },
  { value: 2500, suffix: '+', label: 'Projects Done', animated: false },
  { value: 1800, suffix: '+', label: 'Happy Clients', animated: false },
]);
const animatedValues = reactive([0, 0, 0, 0]);

// ===== Services =====
const services = [
  { icon: '🔧', name: 'Plumbing', category: 'Plumbing', desc: 'Expert plumbing repairs, installations, and maintenance for your home or business.' },
  { icon: '⚡', name: 'Electrical', category: 'Electrical', desc: 'Safe and certified electrical work including wiring, lighting, and panel upgrades.' },
  { icon: '🪚', name: 'Carpentry', category: 'Carpentry', desc: 'Custom woodwork, furniture repair, and structural carpentry by skilled craftsmen.' },
  { icon: '🎨', name: 'Painting', category: 'Painting', desc: 'Interior and exterior painting services with premium finishes and attention to detail.' },
  { icon: '🏠', name: 'Renovation', category: 'Renovation', desc: 'Full home renovations and remodeling to transform your living spaces.' },
  { icon: '❄️', name: 'HVAC', category: 'HVAC', desc: 'Heating, ventilation, and air conditioning installation, repair, and maintenance.' },
];

// ===== Skills =====
const skills = reactive([
  { name: 'Plumbing', value: 95, animated: false },
  { name: 'Electrical Work', value: 90, animated: false },
  { name: 'Carpentry', value: 88, animated: false },
  { name: 'Painting & Decoration', value: 92, animated: false },
  { name: 'Home Renovation', value: 85, animated: false },
]);

// ===== Circular Stats =====
const circularStats = reactive([
  { label: 'Completion Rate', value: 97, animated: false },
  { label: 'Client Satisfaction', value: 95, animated: false },
  { label: 'Trust Score', value: 92, animated: false },
]);

// ===== How It Works =====
const howItWorks = [
  { title: 'Search & Browse', desc: 'Find artisans by category, location, or trust score. Filter and compare professionals easily.' },
  { title: 'Book & Connect', desc: 'Contact your chosen artisan directly. Discuss your project details and get a fair quote.' },
  { title: 'Get It Done', desc: 'Your artisan completes the work. Leave a review to help the community and build trust.' },
];

// ===== Testimonials =====
const testimonials = [
  { name: 'Ahmed B.', role: 'Homeowner, Casablanca', text: 'Hirfati made finding a reliable plumber so easy. The trust scores gave me confidence, and the work was excellent!' },
  { name: 'Fatima Z.', role: 'Business Owner, Rabat', text: 'We used Hirfati for our office renovation. The Gold-tier carpenter we hired exceeded all expectations.' },
  { name: 'Youssef M.', role: 'Apartment Owner, Marrakech', text: 'The trust scoring system is brilliant. I found a verified electrician within minutes. Highly recommend!' },
];

// ===== Intersection Observer for animations =====
let observer = null;

function setupObserver() {
  observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');

          // Trigger counter animation for stats
          if (entry.target.closest('.stagger-children')?.parentElement?.parentElement?.classList.contains('relative')) {
            animateCounters();
          }
        }
      });
    },
    { threshold: 0.15, rootMargin: '0px 0px -50px 0px' }
  );

  document.querySelectorAll('.fade-up, .fade-left, .fade-right, .scale-in').forEach((el) => {
    observer.observe(el);
  });
}

// ===== Counter animation =====
let countersAnimated = false;
function animateCounters() {
  if (countersAnimated) return;
  countersAnimated = true;

  stats.forEach((stat, i) => {
    const duration = 2000;
    const steps = 60;
    const increment = stat.value / steps;
    let current = 0;
    const interval = setInterval(() => {
      current += increment;
      if (current >= stat.value) {
        current = stat.value;
        clearInterval(interval);
        stat.animated = true;
      }
      animatedValues[i] = Math.round(current);
    }, duration / steps);
  });
}

// ===== Skills & circular animation via observer =====
let skillsAnimated = false;
let circlesAnimated = false;

function setupSkillsObserver() {
  const skillsObs = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting && !skillsAnimated) {
          skillsAnimated = true;
          skills.forEach((s, i) => {
            setTimeout(() => { s.animated = true; }, i * 200);
          });
        }
      });
    },
    { threshold: 0.3 }
  );

  const circlesObs = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting && !circlesAnimated) {
          circlesAnimated = true;
          circularStats.forEach((c, i) => {
            setTimeout(() => { c.animated = true; }, i * 300);
          });
        }
      });
    },
    { threshold: 0.3 }
  );

  // Observe the skills section
  const progressBars = document.querySelector('.progress-bar-fill')?.closest('div[class*="fade-left"]');
  if (progressBars) skillsObs.observe(progressBars);

  const circularSection = document.querySelector('.circular-progress')?.closest('div[class*="fade-right"]');
  if (circularSection) circlesObs.observe(circularSection);
}

// ===== Stats section observer =====
function setupStatsObserver() {
  const statsObs = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          animateCounters();
        }
      });
    },
    { threshold: 0.3 }
  );

  const statsSection = document.querySelector('.counter-section');
  if (statsSection) statsObs.observe(statsSection);
}

// ===== Lifecycle =====
onMounted(async () => {
  // Load API data
  try {
    const [catRes, featRes] = await Promise.all([
      api.getCategories(),
      api.getFeaturedArtisans(),
    ]);
    categories.value = catRes.data;
    featured.value = featRes.data;
  } catch (e) {
    console.error('Failed to load homepage data', e);
  }

  // Setup animations after DOM render
  setTimeout(() => {
    setupObserver();
    setupSkillsObserver();
  }, 100);
});

onUnmounted(() => {
  if (observer) observer.disconnect();
});

// ===== Navigation =====
function doSearch() {
  const query = {};
  if (searchCategory.value) query.category = searchCategory.value;
  if (searchLocation.value) query.location = searchLocation.value;
  router.push({ name: 'search', query });
}

function searchByCategory(category) {
  router.push({ name: 'search', query: { category } });
}
</script>
