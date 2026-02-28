import axios from 'axios'
import { defineStore } from 'pinia'

import { router, useForm } from '@inertiajs/vue3'
import { useModalStore } from '@/Store/modalStore'

export const useAssetStore = defineStore('asset', {
  state: () => ({
    allAssets: {
      data: [],
      currentPage: 1,
      loadMore: false,
      loading: false,
      newest: null
    },
    assetUploadProgress: 0,
    assetForm: useForm({
      file: []
    }),
    files: [],
    selectedAssets: [],
    events: {
      load: 'image',
      button: undefined,
      caption: undefined,
      multiple: false,
      selected: [],
      onOpen: () => { },
      onSelect: (asset: []) => { },
      onSubmit: (assets: []) => { }
    }
  }),

  actions: {

    openModal(events: Events) {
      this.events = {
        ...this.events,
        ...events
      }
      this.selectedAssets = events.selected ?? []
      this.events.onOpen()
      this.loadAssets(this.events.load)
      useModalStore().open('assetModal')
    },

    closeModal() {
      useModalStore().close('assetModal')
    },

    toggleSelect(asset: { id: any }) {

      if (!this.events.multiple) {
        this.selectedAssets = [asset]
        this.events.onSelect(asset)
        return
      }

      if (this.isSelected(asset)) {
        this.selectedAssets = this.selectedAssets.filter((item: { id: any }) => item.id !== asset.id)
      } else {
        this.selectedAssets.push(asset)
        this.events.onSelect(asset)
      }

    },

    onSubmit() {
      this.events.onSubmit(this.selectedAssets, this.events)
      this.closeModal()
    },

    loadAssets(file_type = null) {
      this.allAssets.loading = true

      if (file_type === 'voice') {
        file_type = 'audio'
      }

      axios
        .get(route('api-assets-all', { file_type, page: 1 }))
        .then((res) => {
          this.allAssets.data = res.data.data
          if (res.data.data?.length > 0) {
            this.allAssets.newest = res.data.data[0].id
          }
          if (res.data.current_page !== res.data.last_page) {
            this.allAssets.loadMore = true
          }
          if (!res.data.next_page_url) {
            this.allAssets.loadMore = false
          }
        })
        .finally(() => {
          this.allAssets.loading = false
          this.events.load = file_type
        })
    },

    loadMoreAssets() {
      if (!this.allAssets.loadMore) return
      this.allAssets.currentPage++
      this.allAssets.loadMore = true
      this.allAssets.loading = true
      axios
        .get(
          route('api-assets-all', {
            page: this.allAssets.currentPage, file_type: this.events.load
          })
        )
        .then((res) => {
          this.allAssets.data.push(...res.data.data)
          this.allAssets.currentPage = res.data.current_page
          if (!res.data.next_page_url) {
            this.allAssets.loadMore = false
          }
        })
        .finally(() => {
          this.allAssets.loading = false
        })
    },
    setFileType(file: File) {
      if (file.type.startsWith('image/')) {
        return 'image'
      } else if (file.type.startsWith('audio/')) {
        return 'audio'
      } else if (file.type.startsWith('video/')) {
        return 'video'
      } else {
        return 'document'
      }
    },
    assetSubmit() {
      const modifiedFiles = this.files.map((file: any) => {
        return {
          file: file,
          type: this.setFileType(file)
        }
      })

      // return
      router.post(
        route('user.assets.store'),
        {
          files: modifiedFiles
        },
        {
          onProgress: (progress) => {
            this.assetUploadProgress = progress.percentage
          },
          onFinish: () => {
            setTimeout(() => (this.assetUploadProgress = 0), 1000)
          },
          onSuccess: () => {
            this.files = []
            axios.get(route('api-assets-all', { newest: this.allAssets.newest, file_type: this.events.load })).then((res) => {
              if (res.data?.length > 0) {
                this.allAssets.newest = res.data[0].id
                this.allAssets.data.unshift(...res.data)
              }
            })
          }
        }
      )
    },

    isSelected(asset: { path: any }) {
      return this.selectedAssets.map((a: { path: any }) => a.path).includes(asset.path)
    }
  }
})


interface Events {
  load: string
  button: {
    text: String
  }
  caption: undefined | String
  multiple: boolean
  selected: any
  onOpen: () => void
  onSelect: (asset: any) => void
  onSubmit: (assets: any, state: any) => void
}