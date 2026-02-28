<script setup>
const props = defineProps(['message'])
const messageBody = props.message.body
const messageType = props.message.type
</script>

<template>
  <div class="rounded-lg bg-white p-4 shadow-md dark:bg-gray-800">
    <h4 class="mb-2 text-lg font-semibold text-gray-900 dark:text-gray-100">
      {{ trans('Contact Info') }}
    </h4>
    <div class="grid grid-cols-2 gap-4">
      <!-- address -->
      <div v-if="messageBody.addresses.length">
        <h5 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
          {{ trans('Address') }}
        </h5>
        <ul class="list-disc pl-5 text-gray-700 dark:text-gray-300">
          <li v-for="(address, index) in messageBody.addresses" :key="index">
            {{ address.street }}, {{ address.city }}, {{ address.state }}, {{ address.zip }},
            {{ address.country }} ({{ address.type }})
          </li>
        </ul>
      </div>
      <!-- birthday -->
      <div v-if="messageBody.birthday">
        <h5 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
          {{ trans('Birthday') }}
        </h5>
        <p class="text-gray-700 dark:text-gray-300">{{ messageBody.birthday }}</p>
      </div>
      <!-- emails -->
      <div v-if="messageBody.emails.length">
        <h5 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
          {{ trans('Emails') }}
        </h5>
        <ul class="list-disc pl-5 text-gray-700 dark:text-gray-300">
          <li v-for="(email, index) in messageBody.emails" :key="index">
            {{ email.email }} ({{ email.type }})
          </li>
        </ul>
      </div>
      <!-- name -->
      <div v-if="messageBody.name">
        <h5 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ trans('Name') }}</h5>
        <p class="text-gray-700 dark:text-gray-300">{{ messageBody.name.formatted_name }}</p>
        <p class="text-gray-700 dark:text-gray-300">
          {{ trans('First Name') }}: {{ messageBody.name.first_name }}
        </p>
        <p class="text-gray-700 dark:text-gray-300">
          {{ trans('Last Name') }}: {{ messageBody.name.last_name }}
        </p>
        <p class="text-gray-700 dark:text-gray-300">
          {{ trans('Middle Name') }}: {{ messageBody.name.middle_name }}
        </p>
        <p class="text-gray-700 dark:text-gray-300">
          {{ trans('Suffix') }}: {{ messageBody.name.suffix }}
        </p>
        <p class="text-gray-700 dark:text-gray-300">
          {{ trans('Prefix') }}: {{ messageBody.name.prefix }}
        </p>
      </div>
      <!-- org -->
      <div v-if="messageBody.org">
        <h5 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
          {{ trans('Organization') }}
        </h5>
        <p class="text-gray-700 dark:text-gray-300">
          {{ trans('Company') }}: {{ messageBody.org.company }}
        </p>
        <p class="text-gray-700 dark:text-gray-300">
          {{ trans('Department') }}: {{ messageBody.org.department }}
        </p>
        <p class="text-gray-700 dark:text-gray-300">
          {{ trans('Title') }}: {{ messageBody.org.title }}
        </p>
      </div>
      <!-- phones -->
      <div v-if="messageBody.phones.length">
        <h5 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
          {{ trans('Phones') }}
        </h5>
        <ul class="list-disc pl-5 text-gray-700 dark:text-gray-300">
          <li v-for="(phone, index) in messageBody.phones" :key="index">
            {{ phone.phone }} ({{ phone.type }})
          </li>
        </ul>
      </div>
      <!-- urls -->
      <div v-if="messageBody.urls.length">
        <h5 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ trans('URLs') }}</h5>
        <ul class="list-disc pl-5 text-gray-700 dark:text-gray-300">
          <li v-for="(url, index) in messageBody.urls" :key="index">
            {{ url.url }} ({{ url.type }})
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<style scoped>
.grid-cols-2 > * {
  @apply break-words;
}
</style>
