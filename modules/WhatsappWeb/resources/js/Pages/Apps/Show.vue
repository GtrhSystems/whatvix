<script setup>
import { ref } from 'vue'

import UserLayout from '@/Layouts/User/UserLayout.vue'

defineOptions({ layout: UserLayout })
const props = defineProps(['app', 'authKey'])
const activeTab = ref('curl')
const activeSubTab = ref('text')

const tabLists = [
  {
    title: 'cUrl',
    value: 'curl'
  },
  {
    title: 'Php',
    value: 'php'
  },
  {
    title: 'NodeJs',
    value: 'nodejs'
  },
  {
    title: 'Python',
    value: 'python'
  }
]

const subTypes = [
  {
    title: 'Text',
    value: 'text'
  }
]

const integrations = {
  curl: {
    text: `curl --location --request POST '${route('user.whatsapp-web.api.send-message')}' \n
--form 'app_key="${props.app.key}"' \n
--form 'auth_key="${props.authKey}"' \n
--form 'to="RECEIVER_NUMBER"' \n
--form 'type="text"' \n
--form 'message="Example message"'`
  },
  php: {
    text: `$curl = curl_init();
      curl_setopt_array($curl, array(
      CURLOPT_URL => '${route('user.whatsapp-web.api.send-message')}',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => array(
      'app_key' => '${props.app.key}',
      'auth_key' => '${props.authKey}',
      'to' => 'RECEIVER_NUMBER',
      'message' => 'Example message',
      ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    echo $response;`
  },
  nodejs: {
    text: `var request = require('request');
    var options = {
      'method': 'POST',
      'url': '${route('user.whatsapp-web.api.send-message')}',
      'headers': {
      },
      formData: {
        'app_key': '${props.app.key}',
        'auth_key': '${props.authKey}',
        'to': 'RECEIVER_NUMBER',
        'message': 'Example message'
      }
    };
    request(options, function (error, response) {
      if (error) throw new Error(error);
      console.log(response.body);
    });`
  },
  python: {
    text: `import requests

    url = "${route('user.whatsapp-web.api.send-message')}"

    payload={
    'app_key': '${props.app.key}',
    'auth_key': '${props.authKey}',
    'to': 'RECEIVER_NUMBER',
    'message': 'Example message',

    }
    files=[]
    headers = {}
    response = requests.request("POST", url, headers=headers, data=payload, files=files)
    print(response.text)`
  }
}

function formattedCurlCommand(text) {
  return text.replace(/\\n/g, '\n')
}

const apiParameters = [
  {
    value: 'app_key',
    type: 'string',
    required: 'Yes',
    description: 'Used to authorize a transaction for the app'
  },
  {
    value: 'auth_key',
    type: 'string',
    required: 'Yes',
    description: 'Used to authorize a transaction for the is valid user'
  },
  {
    value: 'to',
    type: 'string',
    required: 'Yes',
    description: 'Recipient Whatsapp number should be full number with country code'
  },
  {
    value: 'message',
    type: 'string',
    required: 'Required',
    description: 'The message to be sent. The message can be in text only'
  }
]
</script>

<template>
  <div class="flex flex-col items-center justify-between gap-2 xl:flex-row">
    <div class="card max-w-max p-1">
      <button
        v-for="tab in tabLists"
        :key="tab.value"
        class="btn w-full px-14 py-2 md:w-auto"
        :class="{ 'btn-primary': activeTab === tab.value }"
        @click="activeTab = tab.value"
      >
        <span class="text-xs md:text-sm">{{ tab.title }}</span>
      </button>
    </div>

    <div class="card max-w-max p-1">
      <button
        v-for="tab in subTypes"
        :key="tab.value"
        class="btn w-full px-14 py-2 md:w-auto"
        :class="{ 'btn-primary': activeSubTab === tab.value }"
        @click="activeSubTab = tab.value"
      >
        <span class="text-xs md:text-sm">{{ tab.title }}</span>
      </button>
    </div>
  </div>

  <div class="mt-8 space-y-8">
    <div class="space-y-8" v-if="activeTab === 'curl'">
      <div class="card card-body">
        <p class="mb-5 font-semibold">{{ trans('Send Message') }}</p>
        <pre class="overflow-x-auto rounded bg-gray-100 p-2 dark:bg-dark-900">{{
          formattedCurlCommand(integrations.curl[activeSubTab])
        }}</pre>
      </div>
    </div>
    <div class="space-y-8" v-if="activeTab === 'php'">
      <div class="card card-body">
        <p class="mb-5 font-semibold">{{ trans('Send Message') }}</p>
        <pre class="overflow-x-auto rounded bg-gray-100 p-2 dark:bg-dark-900">{{
          formattedCurlCommand(integrations.php[activeSubTab])
        }}</pre>
      </div>
    </div>
    <div class="space-y-8" v-if="activeTab === 'nodejs'">
      <div class="card card-body">
        <p class="mb-5 font-semibold">{{ trans('Send Message') }}</p>
        <pre class="overflow-x-auto rounded bg-gray-100 p-2 dark:bg-dark-900">{{
          formattedCurlCommand(integrations.nodejs[activeSubTab])
        }}</pre>
      </div>
    </div>

    <div class="space-y-8" v-if="activeTab === 'python'">
      <div class="card card-body">
        <p class="mb-5 font-semibold">{{ trans('Send Message') }}</p>
        <pre class="overflow-x-auto rounded bg-gray-100 p-2 dark:bg-dark-900">{{
          formattedCurlCommand(integrations.python[activeSubTab])
        }}</pre>
      </div>
    </div>

    <div class="card card-body">
      <p class="mb-2 font-semibold">{{ trans('Successful Json Callback') }}</p>
      <pre class="rounded bg-gray-100 p-2 dark:bg-dark-900">
{
  "status": "Success",
  "data": {
    "from": "SENDER_NUMBER",
    "to": "RECEIVER_NUMBER",
    "status_code": 200
  }
}      </pre
      >
    </div>

    <div class="table-responsive mt-6 w-full">
      <table class="table">
        <thead>
          <tr>
            <th>
              {{ trans('S/N') }}
            </th>
            <th>{{ trans('VALUE') }}</th>
            <th>{{ trans('TYPE') }}</th>
            <th>
              {{ trans('REQUIRED') }}
            </th>
            <th>
              {{ trans('DESCRIPTION') }}
            </th>
          </tr>
        </thead>
        <tbody class="tbody">
          <tr v-for="(params, index) in apiParameters" :key="params.sn">
            <td>
              {{ index + 1 }}
            </td>
            <td>
              {{ params.value }}
            </td>
            <td>
              {{ params.type }}
            </td>
            <td>
              {{ params.required }}
            </td>
            <td>
              {{ params.description }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
