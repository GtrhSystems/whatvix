<script setup>
import trans from '@/Composables/transComposable'
import { Link } from '@inertiajs/vue3'
</script>

<template>
  <li>
    <Link href="/privacy-policy">{{ trans('Privacy Policy') }} .</Link>
  </li>
  <li>
    <Link href="/terms-and-conditions">{{ trans('Terms & Conditions') }} .</Link>
  </li>
  <li>
    <Link href="/faq">{{ trans('FAQ') }}</Link>
  </li>
</template>
