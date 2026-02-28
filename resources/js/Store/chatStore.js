import { computed, ref, nextTick } from 'vue'
import echoService from '@/Composables/echoServiceComposable'
import axios from 'axios'
import { defineStore } from 'pinia'

import toast from '@/Composables/toastComposable'
import { useModalStore } from '@/Store/modalStore'

export const useChatStore = defineStore('chatStore', () => {
  const modalStore = useModalStore()

  const features = {
    voice_message: true
  }

  const platform = ref(null)
  const leftSidebar = ref({
    isOpen: true
  })

  // states
  const searchedConversationList = ref([])
  const filteredConversations = computed(() => {
    if (searchForm.value.customer_name.length > 0 || searchForm.value.badge_id) {
      return searchedConversationList.value
    }
    return conversations.value
  })
  const conversations = ref([])
  const chatTemplates = ref([])
  const quickReplies = ref([])
  const aiTemplates = ref([])
  const badges = ref([])

  const activeConversationId = ref(null)

  const rightSidebar = ref({
    isOpen: false
  })

  const assetPopup = ref(false)

  const messageInputFieldRef = ref()

  const chatSearchInput = ref('')
  const chatSearchInputRef = ref(null)
  const chatSearchedItems = ref([])

  const getChatSearchedItems = async () => {
    if (chatSearchInput.value.length === 0) {
      chatSearchedItems.value = []
      return
    }
    try {
      loading.value.message_searching = true
      const res = await axios.get(route('user.conversations.api', 'messages'), {
        params: {
          module: platform.value?.module,
          conversation_id: activeConversationId.value,
          search: chatSearchInput.value
        }
      })
      chatSearchedItems.value = res.data?.data ?? []
    } catch (err) {
      console.error(err)
    } finally {
      loading.value.message_searching = false
    }
  }

  const replying = ref({
    message_id: null,
    message: ''
  })

  const searchForm = ref({
    badge_id: null,
    customer_name: ''
  })

  const loading = ref({
    sendingMessage: false,
    messages: false,
    conversations: false,
    searching: false,
    message_searching: false
  })

  const inputMessage = ref({
    type: 'text',
    message: '',
    caption: '',
    attachments: [],
    template: []
  })

  // getters
  const hasConversations = computed(() => conversations.value.length > 0)
  const hasActiveConversation = computed(() => activeConversationId.value !== null)
  const activeConversation = computed(
    () => conversations.value.find((c) => c.id === activeConversationId.value) ?? {}
  )

  const activeConversationMessages = computed(() => {
    const messages = activeConversation.value.messages ?? []
    return messages.length ? messages.slice().reverse() : []
  })

  const isReplying = computed(() => replying.value.message_id?.length ?? false)

  const getActiveModuleName = computed(() => {
    return (
      (activeConversation.value?.module ?? '').charAt(0).toUpperCase() +
      (activeConversation.value?.module ?? '').slice(1)
    )
  })

  const getBadge = (badge_id) => badges.value.find((b) => b.id === badge_id) ?? null

  const quickReplyFilteredItems = computed(() => {
    let activeModule = activeConversation.value?.module ?? ''
    let list = quickReplies.value
    if (quickReplySearchInput.value.length > 0) {
      list = list.filter((item) =>
        item.message_template.toLowerCase().includes(quickReplySearchInput.value.toLowerCase())
      )
    }
    return list.filter((item) => item.module === activeModule).map((item) => item.message_template)
  })

  const quickReplySuggestionItems = computed(() => {
    let activeModule = activeConversation.value?.module ?? ''
    let list = []
    if (inputMessage.value.message?.length > 0) {
      list = quickReplies.value.filter((item) =>
        item.message_template.toLowerCase().includes(inputMessage.value.message?.toLowerCase())
      )
    }
    return list.filter((item) => item.module === activeModule).map((item) => item.message_template)
  })

  const getActiveModuleTemplates = computed(() =>
    chatTemplates.value.filter((item) => item.module == activeConversation.value.module)
  )

  // mutations
  const setActiveConversation = (id) => {
    activeConversationId.value = id
    activeConversation.value.unread_count = 0
    // scrollToLastMessage()
  }

  const toggleRightSidebar = () => {
    rightSidebar.value.isOpen = !rightSidebar.value.isOpen
  }

  const scrollToLastMessage = (behavior = 'smooth') => {
    setTimeout(() => {
      let container = document.querySelector('#scrollContainerRef')
      if (container) container.scrollIntoView({ block: 'end', behavior })
    }, 300)
  }

  const setReplying = (message) => {
    replying.value = {
      message_id: message.uuid,
      message: message.body?.body ?? message.type ?? 'Unknown type'
    }
  }

  const unsetReplying = () => {
    replying.value = {
      message_id: null,
      message: ''
    }
  }

  const touchConversation = (conversation) => {
    conversation.updated_at = new Date().toISOString()
  }

  const shortConversations = () => {
    conversations.value.sort((a, b) => new Date(b.updated_at) - new Date(a.updated_at))
  }

  const loadMoreMessages = (conversationId) => {
    console.log('loading more messages')
    if (loading.value.messages) return

    loading.value.messages = true
    axios
      .get(route('user.conversations.api', 'load_more_messages'), {
        params: {
          module: platform.value?.module,
          conversation_id: conversationId ?? activeConversationId.value,
          limit: 10,
          last_message_id: conversationId ? 0 : activeConversationMessages.value[0].id
        }
      })
      .then(function (res) {
        let no_more_messages = false
        if (res.data.length === 0) {
          no_more_messages = true
          if (no_more_messages) {
            let conversation = conversations.value.find((c) => c.id == activeConversationId.value)
            if (conversation) {
              conversation.no_more_messages = true
            }
          }
          return
        }
        conversations.value = conversations.value.map((conversation) => {
          if (conversation.id == activeConversationId.value) {
            res.data.forEach((message, index) => {
              conversation.messages.push(message)
            })
          }
          return conversation
        })
      })
      .catch((err) => console.error(err))
      .finally(() => {
        loading.value.messages = false
      })
  }

  const loadMoreConversations = () => {
    console.log('load more conversations')

    if (loading.value.conversations) return
    loading.value.conversations = true
    axios
      .get(route('user.conversations.api', 'load_more_conversations'), {
        params: {
          module: platform.value?.module,
          limit: 10,
          last_conversation_id: conversations.value.length > 0 ? conversations.value[0].id : null
        }
      })
      .then(function (res) {
        if (res.data.length === 0) {
          console.log('no more conversations')
          return
        }

        // push unique conversations
        let uniqueConversations = []
        res.data.forEach((conversation) => {
          if (!uniqueConversations.find((c) => c.id == conversation.id)) {
            uniqueConversations.push(conversation)
          }
        })
      })
      .catch((err) => console.error(err))
      .finally(() => (loading.value.conversations = false))
  }
  const loadBadges = async () => {
    try {
      const res = await axios.get(route('user.conversations.api', 'badges'))
      badges.value = res.data
    } catch (err) {
      console.error(err)
    }
  }

  const submitMessage = async () => {
    let messageData = {
      ...inputMessage.value,
      conversation_id: activeConversationId.value
    }

    if (replying.value.message_id) {
      messageData.context = {
        id: replying.value.message_id,
        title: replying.value.message
      }
    }

    try {
      loading.value.sendingMessage = true

      const res = await axios.post(route('user.messages.store'), messageData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })

      conversations.value = conversations.value.map((conversation) => {
        if (conversation.id == activeConversationId.value) {
          conversation.messages.unshift(res.data)
        }
        return conversation
      })

      // reset to default
      inputMessage.value = {
        type: 'text',
        message: '',
        caption: '',
        attachments: [],
        template: []
      }

      modalStore.close(`${getActiveModuleName.value.toLowerCase()}TemplateModal`)
      messageInputFieldRef.value?.focus()
      unsetReplying()

      touchConversation(activeConversation.value)
      shortConversations()
    } catch (err) {
      toast.danger(err.response?.data?.message || err.message || 'Something went wrong!')
      console.error(err)
    } finally {
      loading.value.sendingMessage = false
      assetPopup.value = false
      scrollToLastMessage()
    }
  }

  // start quick reply functions
  const arrowCounter = ref(0)
  const quickReplySearchInput = ref('')
  const quickReplySuggestionItemsShow = ref(false)
  const quickReplyModalIsOpen = ref(false)
  const quickReplyModalSearchInput = ref()

  const onArrowDown = (evt) => {
    if (
      quickReplyModalIsOpen.value &&
      arrowCounter.value < quickReplyFilteredItems.value.length - 1
    ) {
      arrowCounter.value = arrowCounter.value + 1
    } else if (arrowCounter.value < quickReplyFilteredItems.value.length - 1) {
      arrowCounter.value = arrowCounter.value + 1
    }
  }

  const onArrowUp = (evt) => {
    if (arrowCounter.value > 0) {
      arrowCounter.value = arrowCounter.value - 1
    }
  }

  const addQuickReplyToMessageInput = async (text) => {
    const input = document.getElementById('inputMessageField')
    input.blur()
    quickReplyModalClose()
    inputMessage.value.message = replaceTextWithShortCodes(text) + ' '
    await nextTick()
    input.focus() // Ensure focus remains
  }

  const replaceTextWithShortCodes = (text) => {
    let activeChat = {
      // whatsapp
      name: activeConversation.value.customer?.name,
      phone: activeConversation.value.customer?.uuid,
      // telegram
      username: activeConversation.value.customer?.meta?.username
    }

    return (
      text?.replace(/\{([a-z_]+)\}/g, (match, key) => {
        return activeChat.hasOwnProperty(key) ? activeChat[key] : match
      }) ?? text
    )
  }

  const quickReplyModalOpen = () => {
    modalStore.open('quickReplyModal')
    quickReplyModalIsOpen.value = true
  }
  const quickReplyModalClose = () => {
    modalStore.close('quickReplyModal')
    quickReplyModalIsOpen.value = false
  }

  const selectQuickReply = (evt) => {
    let text = ''
    if (quickReplyModalIsOpen.value) {
      text = quickReplyFilteredItems.value[arrowCounter.value]
    } else {
      text = quickReplyFilteredItems.value[arrowCounter.value]
    }
    addQuickReplyToMessageInput(text)
    arrowCounter.value = -1
  }
  // end quick reply functions

  // handle webhook
  const connectWebSocket = (channelName) => {
    echoService
      .connect()
      ?.private(channelName)
      .subscribed(() => console.log('Live chat activated successfully'))
      .listen('IncomingNewMessageEvent', handleIncomingMessages)
      .listen('IncomingMessageUpdateEvent', handleIncomingMessageUpdates)
      .listen('IncomingNewConversationEvent', handleIncomingConversations)
      .error((err) => console.error(err))
  }

  const disconnectWebSocket = () => {}

  const handleIncomingMessages = (newMessage) => {
    console.log('new message received')
    conversations.value.forEach((chat) => {
      if (chat.id === newMessage?.conversation_id) {
        // check message if already exists
        const existingMessage = chat.messages.find((message) => message.id === newMessage.id)
        if (existingMessage) return

        console.log('pushing new message')
        chat.messages.unshift(newMessage)
        touchConversation(chat)
        shortConversations()
        chat.unread_count += 1
        playSound()
      }
    })
    scrollToLastMessage()
  }

  const handleIncomingMessageUpdates = (updatedMessage) => {
    console.log('message update')
    conversations.value.forEach((chat) => {
      if (chat.id == updatedMessage.conversation_id) {
        chat.messages.forEach((message) => {
          if (message.id == updatedMessage.id) {
            message.status = updatedMessage.status
          }
        })
      }
    })
  }

  const handleIncomingConversations = (newConversation) => {
    console.log('new conversation received')
    conversations.value.value = conversations.value.unshift(newConversation)
    loadMoreMessages(newConversation.id)
    newConversation.unread_count = newConversation.messages?.length ?? 1
  }

  const playSound = () => {
    let audio = new Audio('/assets/incoming-message-beep.mp3')
    audio.play()
  }

  const toggleLeftSidebar = () => {
    leftSidebar.value.isOpen = !leftSidebar.value.isOpen
  }

  return {
    features,
    platform,
    leftSidebar,

    //-> list
    searchedConversationList,
    conversations,
    chatTemplates,
    quickReplies,
    aiTemplates,
    badges,

    // -> single
    activeConversationId,
    rightSidebar,
    replying,
    searchForm,
    loading,
    assetPopup,
    messageInputFieldRef,
    chatSearchInput,
    chatSearchInputRef,
    chatSearchedItems,
    getChatSearchedItems,
    inputMessage,
    quickReplySearchInput,
    quickReplySuggestionItemsShow,
    arrowCounter,

    // -------- computed ----------
    hasConversations,
    hasActiveConversation,
    activeConversation,
    activeConversationMessages,
    isReplying,
    getActiveModuleName,
    getBadge,
    quickReplyFilteredItems,
    quickReplySuggestionItems,
    getActiveModuleTemplates,
    filteredConversations,

    // -------- mutations ----------
    setActiveConversation,
    toggleRightSidebar,
    scrollToLastMessage,
    setReplying,
    unsetReplying,
    loadMoreMessages,
    loadMoreConversations,
    addQuickReplyToMessageInput,
    loadBadges,
    submitMessage,
    touchConversation,
    shortConversations,

    // -------- actions ----------
    quickReplyModalOpen,
    quickReplyModalClose,
    selectQuickReply,
    onArrowDown,
    onArrowUp,
    replaceTextWithShortCodes,
    quickReplyModalSearchInput,
    connectWebSocket,
    disconnectWebSocket,
    toggleLeftSidebar
  }
})
