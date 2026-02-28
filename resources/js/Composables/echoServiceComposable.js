import { usePage } from '@inertiajs/vue3'
import Echo from 'laravel-echo'

export default {
  connect: () => {
    const page = usePage()
    const config = page.props.broadcast_config ?? null
    if (!config) {
      throw new Error('Broadcast config not found')
    }
    try {
      return new Echo(config)
    } catch (e) {
      console.error('Error connecting to Echo:', e)
    }
  }
}
