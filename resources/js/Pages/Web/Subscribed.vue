<script setup>
import trans from '@/Composables/transComposable'
import DefaultLayout from '@/Layouts/Default/DefaultLayout.vue'
import { Link } from '@inertiajs/vue3'
defineOptions({ layout: DefaultLayout })
</script>

<template>
  <!-- Main container card -->
  <div class="container">
    <div class="card">
      <!-- Confirmation icon (inline SVG) -->
      <div class="icon-wrapper">
        <svg class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M5 13l4 4L19 7"
          />
        </svg>
      </div>

      <!-- Headline and message -->
      <h1 class="headline">{{ trans('Thank You for Subscribing!') }}</h1>
      <p class="message">
        {{
          trans(`You're now on the list to receive our latest updates, news, and special offers. Check your
        inbox soon for your first email.`)
        }}
      </p>

      <!-- Call-to-action button -->
      <Link href="/" class="button">{{ trans(' Go to Home Page') }} </Link>
    </div>
  </div>
</template>

<style scoped>
/* Importing the font from Google Fonts */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');

/* Main container and body styles */
.container {
  margin-top: 10rem;
  background-color: #f3f4f6; /* Equivalent to bg-gray-100 */
  display: flex;
  border-radius: 1rem;
  justify-content: center;
  align-items: center;
  align-items: center;
  justify-content: center;
  min-height: 400px;
  padding: 5rem; /* Equivalent to p-4 */
  font-family: 'Inter', sans-serif;
}

/* Card styles */
.card {
  background-color: #fff;
  border-radius: 1rem; /* Equivalent to rounded-2xl */
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); /* Equivalent to shadow-xl */
  padding: 2rem; /* Equivalent to p-8 */
  max-width: 32rem; /* Equivalent to max-w-lg */
  width: 100%; /* Equivalent to w-full */
  text-align: center;
}

/* Icon styles */
.icon-wrapper {
  margin: 0 auto;
  display: flex;
  align-items: center;
  justify-content: center;
  height: 6rem; /* Equivalent to h-24 */
  width: 6rem; /* Equivalent to w-24 */
  border-radius: 9999px; /* Equivalent to rounded-full */
  background-color: #d1fae5; /* Equivalent to bg-green-100 */
  margin-bottom: 1.5rem; /* Equivalent to mb-6 */
}

.icon {
  height: 4rem; /* Equivalent to h-16 */
  width: 4rem; /* Equivalent to w-16 */
  color: #10b981; /* Equivalent to text-green-600 */
}

/* Text styles */
.headline {
  font-size: 1.875rem; /* Equivalent to text-3xl */
  font-weight: 700; /* Equivalent to font-bold */
  color: #1f2937; /* Equivalent to text-gray-800 */
  margin-bottom: 0.5rem; /* Equivalent to mb-2 */
}

.message {
  color: #4b5563; /* Equivalent to text-gray-600 */
  margin-bottom: 2rem; /* Equivalent to mb-8 */
}

/* Button styles */
.button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.75rem 1.5rem; /* Equivalent to px-6 py-3 */
  border: 1px solid transparent; /* Equivalent to border-transparent */
  font-size: 1rem; /* Equivalent to text-base */
  font-weight: 500; /* Equivalent to font-medium */
  border-radius: 9999px; /* Equivalent to rounded-full */
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); /* Equivalent to shadow-sm */
  color: #fff;
  background-color: #10b981; /* Equivalent to bg-green-600 */
  transition-property: background-color, border-color, color, fill, stroke, opacity, box-shadow,
    transform;
  transition-duration: 200ms; /* Equivalent to transition-colors duration-200 */
}

.button:hover {
  background-color: #059669; /* Equivalent to hover:bg-green-700 */
}

.button:focus {
  outline: 2px solid transparent; /* Equivalent to focus:outline-none */
  outline-offset: 2px; /* Equivalent to focus:ring-offset-2 */
  box-shadow: 0 0 0 2px #fff, 0 0 0 4px #34d399; /* Equivalent to focus:ring-2 focus:ring-green-500 */
}
</style>
