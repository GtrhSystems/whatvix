<script setup>
const props = defineProps({
  contact: Object
})
</script>

<template>
  <div class="mb-2 flex justify-between">
    <h4>{{ trans('Address') }}</h4>
    <button type="button" @click="contact.addresses.push({})" class="btn btn-primary">
      {{ trans('Add New Address') }}
    </button>
  </div>

  <div class="mb-6 flex flex-col gap-6">
    <div class="grid grid-cols-2 gap-2" v-for="(address, index) in contact.addresses" :key="index">
      <input type="text" v-model="address.type" class="input" placeholder="Type: Home" />
      <input type="text" v-model="address.street" class="input" placeholder="Street" />
      <input type="text" v-model="address.city" class="input" placeholder="City" />
      <input type="text" v-model="address.state" class="input" placeholder="State" />
      <input type="text" v-model="address.zip" class="input" placeholder="Zip" />
      <input type="text" v-model="address.country" class="input" placeholder="Country" />
      <input type="text" v-model="address.country_code" class="input" placeholder="Country Code" />
      <div class="">
        <button
          type="button"
          @click="contact.addresses.splice(index, 1)"
          class="btn btn-danger btn-xs"
        >
          <Icon icon="bx:x" />
        </button>
      </div>
    </div>
  </div>

  <div class="my-2">
    <h4>{{ trans('Birthday') }}</h4>
    <input type="text" v-model="contact.birthday" class="input" placeholder="Birthday" />
  </div>

  <div class="my-2 flex justify-between">
    <h4>{{ trans('Emails') }}</h4>
    <button type="button" @click="contact.emails.push({})" class="btn btn-primary">
      {{ trans('Add New Email') }}
    </button>
  </div>

  <div class="mb-6 flex flex-col gap-2" v-for="(email, index) in contact.emails" :key="index">
    <div>
      <button type="button" @click="contact.emails.splice(index, 1)" class="btn btn-danger btn-xs">
        <Icon icon="bx:x" />
      </button>
    </div>
    <input type="text" v-model="email.email" class="input" placeholder="Email" />
    <input type="text" v-model="email.type" class="input" placeholder="Type" />
  </div>

  <div class="mb-2">
    <h4>{{ trans('Name') }}</h4>
  </div>
  <div class="mb-6 flex flex-col gap-2">
    <input
      type="text"
      v-model="contact.name.formatted_name"
      class="input"
      placeholder="Formatted Name"
    />
    <input type="text" v-model="contact.name.first_name" class="input" placeholder="First Name" />
    <input type="text" v-model="contact.name.last_name" class="input" placeholder="Last Name" />
    <input type="text" v-model="contact.name.middle_name" class="input" placeholder="Middle Name" />
    <input type="text" v-model="contact.name.prefix" class="input" placeholder="Prefix" />
    <input type="text" v-model="contact.name.suffix" class="input" placeholder="Suffix" />
  </div>

  <div class="mb-2">
    <h4>{{ trans('Organization') }}</h4>
  </div>
  <div class="mb-6 flex flex-col gap-2">
    <input type="text" v-model="contact.org.company" class="input" placeholder="Company" />
    <input type="text" v-model="contact.org.department" class="input" placeholder="Department" />
    <input type="text" v-model="contact.org.title" class="input" placeholder="Title" />
  </div>

  <div class="mb-2 flex justify-between">
    <h4>{{ trans('Phones') }}</h4>
    <button type="button" @click="contact.phones.push({})" class="btn btn-primary">
      {{ trans('Add New Phone') }}
    </button>
  </div>
  <div class="mb-6 flex flex-col gap-2" v-for="(phone, index) in contact.phones" :key="index">
    <div>
      <button type="button" @click="contact.phones.splice(index, 1)" class="btn btn-danger btn-xs">
        <Icon icon="bx:x" />
      </button>
    </div>
    <input type="text" v-model="phone.phone" class="input" placeholder="Phone" />
    <input type="text" v-model="phone.type" class="input" placeholder="Type: Home" />
    <!-- wa_id -->
    <input type="text" v-model="phone.wa_id" class="input" placeholder="WA ID" />
  </div>

  <!-- urls -->
  <div class="mb-2 flex justify-between">
    <h4>{{ trans('Urls') }}</h4>
    <button type="button" @click="contact.urls.push({})" class="btn btn-primary">
      {{ trans('Add New Url') }}
    </button>
  </div>
  <div class="mb-6 flex flex-col gap-2" v-for="(url, index) in contact.urls" :key="index">
    <div>
      <button type="button" @click="contact.urls.splice(index, 1)" class="btn btn-danger btn-xs">
        <Icon icon="bx:x" />
      </button>
    </div>
    <input type="text" v-model="url.url" class="input" placeholder="Url" />
    <input type="text" v-model="url.type" class="input" placeholder="Type" />
  </div>
</template>
