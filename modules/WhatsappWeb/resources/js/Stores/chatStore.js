import { computed, ref } from 'vue'

import axios from 'axios'
import { defineStore } from 'pinia'

import toast from '@/Composables/toastComposable'
import echoService from '@/Composables/echoServiceComposable'
import { useModalStore } from '@/Store/modalStore'

export const useChatStore = defineStore('chatStore', () => {
  const features = {
    voice_message: true
  }

  const disconnected = ref(false)
  const modalStore = useModalStore()
  const baseApiUrl = '/api/whatsapp-web/v1'
  const apiUrl = (endpoint) => `${baseApiUrl}/${endpoint}`

  // chat Platform
  const platforms = ref([])
  const mediaMessages = ref({})

  const getActivePlatform = () => {
    return platforms.value?.find((platform) => platform.id === activeConversation.value.sessionId)
  }
  //------------------ conversations
  const conversationPagination = ref({
    limit: 10
  })

  const conversations = ref([])
  const activeConversation = ref(null)
  const fetchConversations = (platform) => {
    let sessionId = platform?.uuid
    if (!sessionId) {
      loading.value.conversations = false
      return
    }

    loading.value.conversations = true
    axios
      .get(apiUrl(`${sessionId}/chats`), {
        params: {
          limit: conversationPagination.value.limit
        }
      })
      .then((res) => {
        platform.cursor = res.data.cursor
        conversations.value = [...conversations.value, ...res.data.data]
      })
      .catch((err) => {
        console.error(err)
        disconnected.value = true
      })
      .finally(() => (loading.value.conversations = false))
  }

  const loadMoreConversations = () => {
    if (loading.value.conversations) return
    loading.value.conversations = true
    platforms.value.forEach((platform) => {
      let sessionId = platform.uuid

      if (!platform.cursor) return

      axios
        .get(apiUrl(`${sessionId}/chats`), {
          params: {
            cursor: platform.cursor,
            limit: conversationPagination.value.limit
          }
        })
        .then((res) => {
          conversations.value = [...conversations.value, ...res.data.data]
          platform.cursor = res.data.cursor
        })
        .catch((err) => console.error(err))
        .finally(() => (loading.value.conversations = false))
    })
    loading.value.conversations = false
  }

  const setActiveConversation = (chat) => {
    activeConversation.value = chat
    markAsRead(chat)
    activeConversation.value.messages = []
    loadMoreMessages(chat)
    if (getConversationType(chat.id) === 'group') {
      loadGroupMetadata(chat)
    }
  }



  const markAsRead = (chat) => {
    // check if chat has unread messages
    if (chat.unreadCount <= 0) return

    let sessionId = chat.sessionId
    let jid = chat.id

    if (!jid || !sessionId) return

    axios
      .post(apiUrl(`${sessionId}/chats/${jid}/read`))
      .then((res) => {
        chat.unreadCount = 0
      })
      .catch((err) => console.error(err))
  }
  const getConversationProfilePic = (chat) => {
    if (!chat) chat = activeConversation.value

    if (chat.picture) return chat.picture

    return `https://ui-avatars.com/api/?name=${chat?.name}`
  }

  const getConversationType = (conversationId = '') => {
    if (!conversationId) {
      conversationId = activeConversation.value?.id
    }

    if (conversationId.includes('@g.us')) {
      return 'group'
    } else if (conversationId.includes('@s.whatsapp.net')) {
      return 'number'
    }

    return 'unknown'
  }

  //------------------ end conversations

  // ------------------- messages

  const messagesPagination = ref({
    limit: 10
  })

  const loadMoreMessages = (chat) => {
    if (loading.value.messages) return
    if (!chat && !activeConversation.value) return

    if (!chat) {
      chat = activeConversation.value
    }

    let sessionId = chat.sessionId
    let conversationId = chat.id

    if (!sessionId || !conversationId) {
      return
    }

    if (Object(chat).hasOwnProperty('cursor')) {
      if (chat.cursor === null) {
        console.log('no more messages')
        return
      }
    }

    loading.value.messages = true
    axios
      .get(apiUrl(`${sessionId}/chats/${conversationId}`), {
        params: {
          limit: messagesPagination.value.limit,
          cursor: chat.cursor
        }
      })
      .then(function (res) {
        let messages = res.data.data
        if (messages.length > 0) {
          let allMessages = [...messages.reverse(), ...activeConversation.value.messages]

          let filteredMessages = allMessages.filter((message, index, self) => {
            // Filter out duplicate messages based on message ID
            return index === self.findIndex((m) => m.key.id === message.key.id)
          })

          activeConversation.value.messages = filteredMessages
        }
        
        chat.cursor = res.data.cursor
      })
      .catch((err) => console.error(err))
      .finally(() => {
        loading.value.messages = false
      })
  }

  const submitMessage = async () => {
    const messagePayload = {
      jid: activeConversation.value.id,
      type: getConversationType(activeConversation.value.id),
      messageType: inputMessage.value.type,
      message: {}
    }

    switch (inputMessage.value.type) {
      case 'text':
        messagePayload.message = {
          text: inputMessage.value.message
        }
        break
      case 'image':
        messagePayload.message = {
          image: inputMessage.value.file,
          caption: inputMessage.value.caption ?? null
        }
        break

      case 'audio':
        messagePayload.message = {
          audio: inputMessage.value.file,
          ptt: false
        }
        break

      case 'voice':
        messagePayload.messageType = 'voice'
        messagePayload.message = {
          voice: inputMessage.value.file
        }
        break

      case 'video':
        messagePayload.message = {
          video: inputMessage.value.file,
          caption: inputMessage.value.caption ?? null,
          gifPlayback: false,
          ptv: false
        }
        break
      case 'location':
        messagePayload.message = inputMessage.value.message
        break
      case 'document':
        messagePayload.message = {
          document: inputMessage.value.file
        }
        break

      case 'template':
        messagePayload.messageType = inputMessage.value.template.type
        messagePayload.message = inputMessage.value.template.meta
        break
      default:
        console.error('Invalid message type: ' + inputMessage.value.type)
        return
    }

    if (isReplying.value) {
      messagePayload.options = {
        quoted: replying.value
      }
    }

    loading.value.sendingMessage = true
    axios
      .post(apiUrl(`${activeConversation.value.sessionId}/messages/send`), messagePayload, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
      .then((res) => {
        modalStore.close()
       

        if (['audio', 'video', 'image'].includes(inputMessage.value.type)) {
          let mediaMessageId = res.data.key.id
          mediaMessages.value[mediaMessageId] = inputMessage.value.file
        }

        inputMessage.value.type = 'text'
        inputMessage.value.message = ''
        messageInputFieldRef.value?.focus()
        unsetReplying()
        
      })
      .finally(() => {
        loading.value.sendingMessage = false
        assetPopup.value = false
        scrollToLastMessage()
      })
      .catch((err) => {
        toast.danger(err.response?.data?.message || err.message || 'Something went wrong!')
        console.error(err)
      })
  }

  const getMedia = async (message) => {
    if (mediaMessages.value[message.key.id]) {
      return mediaMessages.value[message.key.id]
    }

    if (!message.sessionId) {
      throw new Error('Session ID not found: ' + message)
    }

    try {
      const response = await axios.post(apiUrl(`${message.sessionId}/messages/download`), message, {
        responseType: 'blob'
      })

      // Convert blob to object URL
      const blob = new Blob([response.data], { type: response.headers['content-type'] })
      mediaMessages.value[message.id] = URL.createObjectURL(blob)
      return mediaMessages.value[message.id]
    } catch (error) {
      console.error('Error downloading file:', error)
      return null
    }
  }

  // ------------------- end messages

  // groups-------------------

  const loadGroupMetadata = (chat) => {
    if (chat.groupMetadata) return
    loading.value.groupMetadata = true
    axios
      .get(apiUrl(`${chat.sessionId}/groups/${chat.id}`))
      .then(function (res) {
        activeConversation.value.groupMetadata = res.data
      })
      .catch((err) => console.error(err))
      .finally(() => {
        loading.value.groupMetadata = false
      })
  }

  // states
  const searchedConversationList = ref([])
  const filteredConversations = computed(() => {
    if (searchForm.value.customer_name.length > 0 || searchForm.value.badge_id) {
      searchedConversationList.value = conversations.value.filter((conversation) => {
        if (searchForm.value.badge_id) {
          return conversation.badge_id === searchForm.value.badge_id
        }
        if (searchForm.value.customer_name.length > 0) {
          return conversation.name
            ?.toLowerCase()
            .includes(searchForm.value.customer_name.toLowerCase())
        }
        return true
      })
      return searchedConversationList.value
    }
    return conversations.value
  })

  const chatTemplates = ref([])
  const quickReplies = ref([])
  const aiTemplates = ref([])
  const languages = ref([])
  const badges = ref([])

  const rightSidebar = ref({
    isOpen: false
  })
  const leftSidebar = ref({
    isOpen: false
  })

  const assetPopup = ref(false)

  const messageInputFieldRef = ref()

  const replying = ref(null)

  const searchForm = ref({
    badge_id: null,
    customer_name: ''
  })

  const loading = ref({
    sendingMessage: false,
    messages: false,
    conversations: false,
    searching: false,
    groupMetadata: false
  })

  const inputMessage = ref({
    type: 'text',
    message: '',
    file: null,
    template: null,
    location: null
  })

  // getters
  const hasConversations = computed(() => conversations.value.length > 0)
  const hasActiveConversation = computed(() => activeConversation.value !== null)
  const isReplying = computed(() => replying.value ?? false)

  const quickReplyFilteredItems = computed(() => {
    let list = quickReplies.value

    if (quickReplySearchInput.value.length > 0) {
      list = list.filter((item) =>
        item.message_template.toLowerCase().includes(quickReplySearchInput.value.toLowerCase())
      )
    }

    return list.map((item) => item.message_template)
  })
  const quickReplySuggestionItems = computed(() => {
   
    let list = []
    if (inputMessage.value.message?.length > 0) {
      list = quickReplies.value.filter((item) =>
        item.message_template.toLowerCase().includes(inputMessage.value.message?.toLowerCase())
      )
    }
    return (
      list
      
        .map((item) => item.message_template)
    )
  })
  const getActiveModuleTemplates = computed(
    () => chatTemplates.value
   
  )

  // mutations

  const toggleRightSidebar = () => {
    rightSidebar.value.isOpen = !rightSidebar.value.isOpen
  }
  const toggleLeftSidebar = () => {
    leftSidebar.value.isOpen = !leftSidebar.value.isOpen
  }
  const scrollToLastMessage = (behavior = 'smooth') => {
    setTimeout(() => {
      let scrollContainerRef = document.querySelector('#scrollContainerRef')
      if (scrollContainerRef) scrollContainerRef.scrollIntoView({ block: 'end', behavior })
    }, 500)
  }
  const setReplying = (message) => {
    replying.value = message
  }
  const unsetReplying = () => {
    replying.value = null
  }

  const shortConversations = () => {
    conversations.value = conversations.value.sort(
      (a, b) => b.conversationTimestamp - a.conversationTimestamp
    )
  }

  const touchConversation = (chat) => {
    chat.conversationTimestamp = Math.floor(Date.now() / 1000)
    shortConversations()
  }

  const getBadge = (badge_id) => badges.value.find((b) => b.id === badge_id) ?? null

  const loadBadges = async () => {
    try {
      const res = await axios.get(route('user.conversations.api', 'badges'))
      badges.value = res.data
    } catch (err) {
      console.error(err)
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

  const addQuickReplyToMessageInput = (text) => {
    quickReplyModalClose()
    inputMessage.value.message = replaceTextWithShortCodes(text)
  }

  const replaceTextWithShortCodes = (text) => {
    let activeChat = {
      name: activeConversation.value.name,
      phone: activeConversation.value.id
    }

    return (
      text?.replace(/\{([a-z_]+)\}/g, (match, key) => {
        return (activeChat.hasOwnProperty(key) ? activeChat[key] : match) ?? match
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
    console.log('Activating live chat...')
    echoService
      .connect()
      .private(channelName)
      .subscribed(() => console.log('Live chat activated successfully'))
      .listen('LiveChatNotifyEvent', handleLiveChatNotifyEvent)
      .error((err) => console.error(err))
  }

  const handleLiveChatNotifyEvent = (payload) => {
   

    if (payload.event === 'messages.upsert') {
      handleMessagesUpsertEvent(payload)
    } else if (payload.event === 'messages.update') {
      handleMessagesUpdateEvent(payload)
    } else if (payload.event === 'chats.upsert') {
      handleChatsUpsertEvent(payload)
    } else if (payload.event === 'chats.update') {
      handleChatsUpdateEvent(payload)
    }
  }

  const handleMessagesUpsertEvent = (payload) => {
   

    let sessionId = payload.sessionId
    let message = payload.data?.messages || []
    if (!message.sessionId) {
      message.sessionId = sessionId
    }
    let remoteJid = message.key?.remoteJid

    conversations.value.forEach((chat) => {
      if (chat.id == remoteJid && chat.sessionId == sessionId) {
        // Check if message already exists in the chat
        const messageExists = chat.messages.some((msg) => msg.key?.id === message.key?.id)
        if (!messageExists) {
          // console.log('message upserted', message)
          chat.messages.push(message)
          if (!message.key.fromMe) playSound()

          if (activeConversation.value?.id === chat.id) scrollToLastMessage()
        }
      }
    })
  }

  const handleMessagesUpdateEvent = (payload) => {
    let sessionId = payload.sessionId
    let message = payload.data?.messages || []
    let remoteJid = message.key?.remoteJid

    conversations.value.forEach((chat) => {
      if (chat.id == remoteJid && chat.sessionId == sessionId) {
        chat.messages = chat.messages.map((msg) => {
          if (msg.key?.id == message.id) {
            return {
              ...msg,
              ...message
            }
          }
          return msg
        })
      }
    })
  }

  const handleChatsUpsertEvent = (payload) => {
    console.log('chats upsert event received', payload)
    let newChats = payload.data?.chats ?? []
    newChats.forEach((newChat) => {
      if (conversations.value.find((chat) => chat.id == newChat.id)) {
        conversations.value = conversations.value.map((chat) => {
          if (chat.id == newChat.id) {
            return {
              ...chat,
              ...newChat,
              sessionId: payload.sessionId
            }
          }
          return chat
        })
      } else {
        conversations.value.unshift({
          ...newChat,
          sessionId: payload.sessionId
        })
      }
    })
  }

  const handleChatsUpdateEvent = (payload) => {
   
    let chat = payload.data?.chats ?? null
    if (!chat) return
    conversations.value = conversations.value.map((c) => {
      if (c.id == chat.id) {
        c.conversationTimestamp = chat.conversationTimestamp
      }
      return c
    })

    shortConversations()
  }

  const getChatProfilePic = () => {
    let chat = activeConversation.value
    if (!chat) return

    let sessionId = chat.sessionId
    let jid = chat.id

    axios
      .get(apiUrl(`${sessionId}/chats/${jid}/photo`))
      .then((response) => {
        chat.picture = response.data.url
        toast.success('Profile picture updated')
      })
      .catch((error) => {
        console.error('Error fetching profile:', error)
      })
  }

  const playSound = () => {
    let audio = new Audio('/assets/incoming-message-online-whatsapp.mp3')
    audio.play()
  }

  return {
    features,
    disconnected,
    baseApiUrl,

    // platform device
    platforms,
    mediaMessages,
    getActivePlatform,

    // chat
    activeConversation,
    conversations,
    fetchConversations,
    setActiveConversation,
    getConversationProfilePic,
    getConversationType,
    getMedia,

    //-> list
    searchedConversationList,
    chatTemplates,
    quickReplies,
    aiTemplates,
    languages,
    badges,

    // -> single
    rightSidebar,
    leftSidebar,
    replying,
    searchForm,
    loading,
    assetPopup,
    messageInputFieldRef,
    inputMessage,
    quickReplySearchInput,
    quickReplySuggestionItemsShow,
    arrowCounter,

    // -------- computed ----------
    hasConversations,
    hasActiveConversation,
    isReplying,
    quickReplyFilteredItems,
    quickReplySuggestionItems,
    getActiveModuleTemplates,
    filteredConversations,

    // -------- mutations ----------
    toggleRightSidebar,
    toggleLeftSidebar,
    scrollToLastMessage,
    setReplying,
    unsetReplying,
    loadMoreMessages,
    loadMoreConversations,
    addQuickReplyToMessageInput,
    loadBadges,
    getBadge,
    submitMessage,

    // -------- actions ----------
    quickReplyModalOpen,
    quickReplyModalClose,
    selectQuickReply,
    onArrowDown,
    onArrowUp,
    replaceTextWithShortCodes,
    quickReplyModalSearchInput,
    connectWebSocket,
    getChatProfilePic
  }
})
