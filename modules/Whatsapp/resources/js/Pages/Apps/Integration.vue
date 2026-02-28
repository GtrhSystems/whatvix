<script setup>
import { ref } from 'vue'
import JsonHighlighter from '@whatsapp/Components/JsonHighlighter.vue'
import UserLayout from '@/Layouts/User/UserLayout.vue'

defineOptions({ layout: UserLayout })

const props = defineProps(['app', 'authKey'])
const activeTab = ref('curl')

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
    title: 'NodeJs - Request',
    value: 'nodejs'
  },
  {
    title: 'Python',
    value: 'python'
  }
]

const apiUrl = window.location.origin + '/api/whatsapp/message'

const integrations = {
  curl: {
    text: `curl --location --request POST '${apiUrl}' \n
        --form 'appkey="${props.app.key}"' \n
        --form 'authkey="${props.authKey}"' \n
        --form 'to="RECEIVER_NUMBER"' \n
        --form 'message="Example message"'`
  },
  php: {
    text: `$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => '${apiUrl}',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => array(
  'appkey' => '${props.app.key}',
  'authkey' => '${props.authKey}',
  'to' => 'RECEIVER_NUMBER',
  'message' => 'Example message',
  'sandbox' => 'false'
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
  'url': '${apiUrl}',
  'headers': {
  },
  formData: {
    'appkey': '${props.app.key}',
    'authkey': '${props.authKey}',
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

url = "${apiUrl}"

payload={
'appkey': '${props.app.key}',
'authkey': '${props.authKey}',
'to': 'RECEIVER_NUMBER',
'message': 'Example message',

}
files=[

]
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
    sn: 1,
    value: 'appkey',
    type: 'string',
    required: 'Yes',
    description: 'Used to authorize a transaction for the app'
  },
  {
    sn: 2,
    value: 'authkey',
    type: 'string',
    required: 'Yes',
    description: 'Used to authorize a transaction for the is valid user'
  },
  {
    sn: 3,
    value: 'to',
    type: 'string',
    required: 'Yes',
    description:
      'Who will receive the message the Whatsapp number should be full number with country code'
  },
  {
    sn: 4,
    value: 'message',
    type: 'string',
    required: 'No',
    description: 'The transactional message max:1000 words'
  }
]

const successfulResJson = {
  status: 'Success',
  data: {
    from: 'SENDER_NUMBER',
    to: 'RECEIVER_NUMBER',
    status_code: 200
  }
}
</script>

<template>
  <div class="flex items-start justify-between md:items-center">
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
  </div>

  <div class="mt-8 space-y-8">
    <div class="space-y-8" v-if="activeTab === 'curl'">
      <div class="card card-body">
        <p class="mb-5 font-semibold">{{ trans('Text Message') }}</p>
        <pre class="text-left leading-3">{{ formattedCurlCommand(integrations.curl.text) }}</pre>
      </div>
    </div>
    <div class="space-y-8" v-if="activeTab === 'php'">
      <div class="card card-body">
        <p class="mb-5 font-semibold">{{ trans('Text Message') }}</p>
        <pre class="text-left leading-5">{{ formattedCurlCommand(integrations.php.text) }}</pre>
      </div>
    </div>
    <div class="space-y-8" v-if="activeTab === 'nodejs'">
      <div class="card card-body">
        <p class="mb-5 font-semibold">{{ trans('Text Message') }}</p>
        <pre class="text-left leading-5">{{ formattedCurlCommand(integrations.nodejs.text) }}</pre>
      </div>
    </div>

    <div class="space-y-8" v-if="activeTab === 'python'">
      <div class="card card-body">
        <pre class="text-left leading-5">{{ formattedCurlCommand(integrations.python.text) }}</pre>
      </div>
    </div>

    <div class="card card-body">
      <p class="mb-5 font-semibold">{{ trans('Successful Json Callback') }}</p>
      <JsonHighlighter :code="successfulResJson" />
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
          <tr v-for="params in apiParameters" :key="params.sn">
            <td>
              {{ params.sn }}
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
