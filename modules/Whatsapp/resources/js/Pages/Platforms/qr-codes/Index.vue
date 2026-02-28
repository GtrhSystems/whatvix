<script setup>
import { onMounted } from 'vue'

import { useForm } from '@inertiajs/vue3'
import Modal from '@/Components/Dashboard/Modal.vue'
import Paginate from '@/Components/Dashboard/Paginate.vue'
import NoDataFound from '@/Components/NoDataFound.vue'
import SpinnerBtn from '@/Components/Dashboard/SpinnerBtn.vue'
import sharedComposable from '@/Composables/sharedComposable.js'

import UserLayout from '@/Layouts/User/UserLayout.vue'
import { useModalStore } from '@/Store/modalStore'

const modalStore = useModalStore()

defineOptions({ layout: UserLayout })
const props = defineProps(['device', 'qrCodes'])
const { textExcerpt, deleteRow } = sharedComposable()

const form = useForm({
  prefilled_message: '',
  generate_qr_image: 'SVG'
})

const createQrCode = () => {
  form.post(route('user.whatsapp.qr-codes.store', props.device.uuid), {
    onSuccess: () => {
      form.reset()
      modalStore.close('qrCodeModal')
    }
  })
}
</script>

<template>
  <div class="table-responsive w-full">
    <table class="table">
      <thead>
        <tr>
          <th>{{ trans('Code') }}</th>
          <th>
            {{ trans('Prefilled Message') }}
          </th>
          <th>{{ trans('QR Image') }}</th>
          <th class="!text-right">
            {{ trans('Action') }}
          </th>
        </tr>
      </thead>
      <tbody v-if="qrCodes.data.length" class="tbody">
        <tr v-for="(code, index) in qrCodes.data" :key="index">
          <td>{{ code.code }}</td>
          <td>{{ textExcerpt(code.prefilled_message, 50) }}</td>
          <td>
            <a :href="code.qr_image_url" download>
              <img :src="code.qr_image_url" class="h-20" />
            </a>
          </td>
          <td>
            <div class="flex justify-end">
              <div class="dropdown" data-placement="bottom-start">
                <div class="dropdown-toggle">
                  <Icon class="h-5 text-3xl text-slate-400" icon="bx:dots-vertical-rounded" />
                </div>
                <div class="dropdown-content w-56">
                  <ul class="dropdown-list">
                    <li class="dropdown-list-item">
                      <a class="dropdown-link" target="_blank" :href="code.deep_link_url">
                        <Icon class="h-5 text-slate-400" icon="bx:link" />
                        {{ trans('Try') }}
                      </a>
                    </li>
                    <li class="dropdown-list-item">
                      <a class="dropdown-link" :href="code.qr_image_url">
                        <Icon class="h-5 text-slate-400" icon="bx:download" />
                        {{ trans('Download') }}
                      </a>
                    </li>
                    <li class="dropdown-list-item">
                      <button
                        class="dropdown-link delete-confirm"
                        type="button"
                        @click="
                          deleteRow(
                            route('user.whatsapp.qr-codes.destroy', {
                              device: device,
                              qr_code: code.id
                            })
                          )
                        "
                      >
                        <Icon class="h-5 text-slate-400" icon="bx:trash" />
                        {{ trans('Remove') }}
                      </button>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </td>
        </tr>
      </tbody>
      <NoDataFound :forTable="true" v-else />
    </table>
    <div class="w-full">
      <Paginate v-if="qrCodes.data.length" :links="qrCodes.links" />
    </div>
  </div>
  <Modal :header-state="true" header-title="Create Qr Code" state="qrCodeModal">
    <form @submit.prevent="createQrCode">
      <div class="mb-2">
        <label>{{ trans('Prefilled Message') }}</label>
        <textarea v-model="form.prefilled_message" class="input"></textarea>
      </div>
      <div class="mb-2">
        <label>{{ trans('Generate Image Format') }}</label>
        <select v-model="form.generate_qr_image" class="select">
          <option value="SVG">{{ trans('SVG') }}</option>
          <option value="PNG">{{ trans('PNG') }}</option>
        </select>
      </div>

      <div class="mt-2 text-end">
        <SpinnerBtn
          classes="btn btn-primary"
          :processing="form.processing"
          :btn-text="trans('Create QR Code')"
        />
      </div>
    </form>
  </Modal>
</template>
