import axios from 'axios'
import { defineStore } from 'pinia'
import { useForm } from '@inertiajs/vue3'
import sharedComposable from '@/Composables/sharedComposable'
import toast from '@/Composables/toastComposable'

const { pickBy } = sharedComposable()

export const useAiToolsStore = defineStore('aiToolsStore', {
  state: () => ({
    props: {},
    generatedText: '',
    documentName: '',
    fieldErrors: {},
    errors: {},
    hasError: false,
    isProcessing: false,
    form: useForm({
      prompt: '',
      language: 'english',
      tone: '',
      max_token: 200,
      qty: 1,
      creativity: '',
      seconds: 0,
      init_audio: null,
      model: '',
      template_id: '',
      fields: ''
    }),

    tones: ['Professional', 'Funny', 'Casual', 'Excited', 'Bold', 'Dramatic'],
    creativityLevels: [
      {
        label: 'Optimal',
        value: 0.5
      },
      {
        label: 'Low',
        value: 0.8
      },
      {
        label: 'Medium',
        value: 0.9
      },
      {
        label: 'High',
        value: 1
      }
    ]
  }),
  actions: {
    setInitialState(form) {
      this.form = form
    },
    transformPrompt() {
      const fields = []
      this.form.fields.forEach((element) => {
        if (element.hasOwnProperty('value')) {
          fields[element.name] = element.value
          this.hasError = false
        } else {
          this.hasError = true
          this.fieldErrors[element.name] = `${element.name} is required`
          return
        }
      })
      this.form.prompt = this.props.template.prompt.replace(
        /\[(.*?)\]/g,
        (match, key) => fields[key]
      )
    },
    generateText() {
      axios
        .post(route('api-ai-generate-text', pickBy(this.form)))
        .then(async (res) => {
          for (const text of res.data) {
            if (text) {
              this.generatedText += ' ' + text
            }
            await new Promise((resolve) => setTimeout(resolve, 100))
          }
        })
        .catch((error) => {
          this.errors = error.response.data.errors
          if (error.response.status == 400) {
            toast.danger("You don't have enough credits please purchase some credits")
          }
          toast.danger(
            error.response.data.message ?? 'Error generating text please try again later'
          )
        })
        .finally(() => {
          this.isProcessing = false
          this.fieldErrors = {}
        })
    },

    submit() {
      const totalCharge = this.props.template.credit_charge * this.form.qty
      if (totalCharge > this.props.credits) {
        toast.danger("You don't have enough credits please purchase some credits")
        return
      }
      this.transformPrompt()
      if (this.hasError) return

      this.isProcessing = true

      this.generateText()
    },
    downloadHTMLFile(textContent, name) {
      const blob = new Blob([textContent], { type: 'text/html' })
      const a = document.createElement('a')
      a.href = URL.createObjectURL(blob)
      a.download = name ? name.replace(' ', '-') : 'untitled.html'

      a.click()
      URL.revokeObjectURL(a.href)
    }
  }
})
