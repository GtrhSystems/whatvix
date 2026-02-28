import { computed } from 'vue'

import { router, usePage } from '@inertiajs/vue3'
import { modal } from '@/Composables/actionModalComposable'
import toast from '@/Composables/toastComposable'
import trans from '@/Composables/transComposable'
import DOMPurify from 'isomorphic-dompurify'

export default () => {
  const textExcerpt = (text, length = 30, append = '...') => {
    if (text) {
      return text?.length > length ? text?.substring(0, length) + append : text
    }
    return text || ''
  }
  const currentRoute = (route) => {
    return usePage().component === route
  }
  const currentRouteGroup = (route) => {
    const componentLocation = usePage().component.split('/').slice(0, 2).join('/')
    return componentLocation === route
  }

  const authUser = computed(() => {
    return usePage().props.authUser
  })

  const logout = () => {
    router.post('/logout')
  }

  const formatNumber = (num, precision = 1) => {
    const map = [
      { suffix: 'T', threshold: 1e12 },
      { suffix: 'B', threshold: 1e9 },
      { suffix: 'M', threshold: 1e6 },
      { suffix: 'K', threshold: 1e3 },
      { suffix: '', threshold: 1 }
    ]

    const found = map.find((x) => Math.abs(num) >= x.threshold)
    if (found) {
      const formatted = (num / found.threshold).toFixed(precision) + found.suffix
      return formatted
    }

    return num
  }

  const deleteRow = (actionUrl, alertMgs = null) => {
    modal.init(actionUrl, {
      method: 'delete',
      options: {
        message: trans('You would not be revert it back!'),
        confirm_text: trans('Are you sure?'),
        accept_btn_text: trans('Yes, Sure!'),
        reject_btn_text: trans('No, Cancel')
      },
      callback: () => {
        if (alertMgs) {
          toast.success(alertMgs)
        }
      }
    })
  }
  const formatCurrency = (amount = 0, iconType = 'name', toFixedIn = 2) => {
    let formattedCurrency = ''
    if (!(typeof amount === 'number')) {
      return ''
    }
    const currency = usePage().props.currency
    if (iconType === 'name') {
      formattedCurrency =
        currency.position === 'right'
          ? currency.name + ' ' + amount.toFixed(toFixedIn)
          : currency.icon + ' ' + amount.toFixed(toFixedIn)
    } else if (iconType === 'both') {
      formattedCurrency = currency.icon + amount.toFixed(toFixedIn) + ' ' + currency.name
    } else {
      formattedCurrency =
        currency.position === 'right'
          ? amount.toFixed(toFixedIn) + currency.icon
          : currency.icon + amount.toFixed(toFixedIn)
    }

    return formattedCurrency
  }
  const pickBy = (obj) => {
    const result = {}

    for (const key in obj) {
      const value = obj[key]

      if (value !== undefined && value !== null && value !== '') {
        if (Array.isArray(value) && value.length === 0) {
          continue // Skip empty arrays
        } else if (typeof value === 'object' && Object.keys(value).length === 0) {
          continue // Skip empty objects
        }

        result[key] = value
      }
    }

    return result
  }

  const getQueryParams = (key = null, defaultValue = null) => {
    const obj = {}
    const para = new URLSearchParams(window.location.search)

    for (const [key, value] of para) {
      if (obj.hasOwnProperty(key)) {
        if (Array.isArray(obj[key])) {
          obj[key].push(value)
        } else {
          obj[key] = [obj[key], value]
        }
      } else {
        obj[key] = value
      }
    }

    if (key) {
      return obj[key] ?? defaultValue
    }

    return obj
  }
  //copy text
  function copyToClipboard(textValue) {
    if (!textValue) {
      return toast.danger('value is cannot be empty')
    }
    navigator.clipboard
      .writeText(textValue)
      .then(() => {
        toast.success('Copied to clipboard')
      })
      .catch((err) => {
        toast.error('Failed to copy')
      })
  }
  function trim(text) {
    return text.replace(/[_-]/g, ' ')
  }

  function socialShare(media, url = null) {
    let shareableLinks = {
      facebook: 'https://www.facebook.com/sharer/sharer.php?u=',
      twitter: 'https://twitter.com/intent/tweet?url=',
      pinterest: 'https://pinterest.com/pin/create/button/?url=',
      linkedin: 'https://www.linkedin.com/sharing/share-offsite/?url=',
      instagram: 'https://www.instagram.com/?url='
    }
    if (shareableLinks.hasOwnProperty(media)) {
      return sanitizeUrl(shareableLinks[media] + (url ?? window.location.href))
    }
    return 'invalidMediaError'
  }
  function uiAvatar(name = null, avatar = null) {
    return avatar ? avatar : `https://ui-avatars.com/api/?name=${name ? name : 'user'}`
  }
  function badgeClass(status) {
    if (status == null) return 'badge badge-info'

    const normalizedStatus = String(status).toLowerCase().trim()

    const statuses = {
      success: [
        'active',
        'checked',
        'approved',
        'reversed',
        'success',
        'yes',
        'completed',
        'resolved',
        1,
        'done',
        'confirmed'
      ],
      warning: ['pending', 'in progress', 'review', 'awaiting', 'processing', 2, 'partial'],
      danger: [
        'inactive',
        'closed',
        'failed',
        'rejected',
        'no',
        0,
        'error',
        'cancelled',
        'declined'
      ]
    }

    for (const [category, values] of Object.entries(statuses)) {
      if (values.includes(normalizedStatus)) {
        return `capitalize badge badge-${category}`
      }
    }
    return 'badge badge-info'
  }

  function debounce(func, wait) {
    let timeout

    return function (...args) {
      const context = this
      clearTimeout(timeout)

      timeout = setTimeout(() => {
        func.apply(context, args)
      }, wait)
    }
  }
  function sanitizeUrl(url = '') {
    if (typeof url !== 'string') return ''

    let trimmedUrl = url.trim()
    let possibleXssVulnerabilities = ['javascript:']

    return possibleXssVulnerabilities.some((vulnerability) => trimmedUrl.includes(vulnerability))
      ? ''
      : trimmedUrl
  }
  function sanitizeHtml(html) {
    if (typeof html !== 'string') return ''
    return DOMPurify.sanitize(html, {
      ALLOWED_ATTR: ['class']
    })
  }
  function moduleLabel(name) {
    const modules = new Map([
      ['whatsapp', 'WaCloud'],
      ['Whatsapp', 'WaCloud'],
      ['whatsappweb', 'WaWeb'],
      ['Whatsappweb', 'WaWeb'],
      ['whatsapp-web', 'WaWeb'],
      ['whatsapp web', 'WaWeb'],
      ['Whatsapp Web', 'WaWeb']
    ])

    return modules.get(name) || name
  }
  return {
    authUser,
    textExcerpt,
    currentRoute,
    currentRouteGroup,
    deleteRow,
    logout,
    formatCurrency,
    pickBy,
    formatNumber,
    getQueryParams,
    copyToClipboard,
    socialShare,
    trim,
    uiAvatar,
    debounce,
    badgeClass,
    sanitizeUrl,
    sanitizeHtml,
    moduleLabel
  }
}
