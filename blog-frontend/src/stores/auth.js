import { defineStore } from 'pinia'
import api from '@/plugins/axios'
import router from '@/router'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: JSON.parse(localStorage.getItem('user')) || null,
    token: localStorage.getItem('token') || null,
    loading: false,
    error: null,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    isAdmin: (state) => {
      if (!state.user) return false
      return state.user.roles?.some((role) => role.name === 'admin') || false
    },
    userName: (state) => state.user?.name || '',
  },

  actions: {
    async login(credentials) {
      this.loading = true
      this.error = null

      try {
        const response = await api.post('/auth/login', credentials)

        this.token = response.data.access_token
        this.user = response.data.user

        localStorage.setItem('token', this.token)
        localStorage.setItem('user', JSON.stringify(this.user))

        router.push('/dashboard')
      } catch (error) {
        this.error = error.response?.data?.error || 'Error al iniciar sesión'
        throw error
      } finally {
        this.loading = false
      }
    },

    async register(userData) {
      this.loading = true
      this.error = null

      try {
        await api.post('/auth/register', userData)
        router.push('/login')
      } catch (error) {
        this.error = error.response?.data?.errors || 'Error al registrarse'
        throw error
      } finally {
        this.loading = false
      }
    },

    async logout() {
      try {
        await api.post('/auth/logout')
      } catch (error) {
        console.error('Error al cerrar sesión:', error)
      } finally {
        this.token = null
        this.user = null
        localStorage.removeItem('token')
        localStorage.removeItem('user')
        router.push('/login')
      }
    },

    async fetchUser() {
      try {
        const response = await api.get('/auth/me')
        this.user = response.data
        localStorage.setItem('user', JSON.stringify(this.user))
      } catch {
        this.logout()
      }
    },
  },
})
