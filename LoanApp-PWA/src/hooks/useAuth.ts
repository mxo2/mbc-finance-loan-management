import { useState, useEffect } from 'react'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import { api } from '../services/api'
import toast from 'react-hot-toast'

export interface User {
  id: number
  name: string
  email: string
  phone?: string
  type: 'customer' | 'admin'
  email_verified_at?: string
  kyc_status?: 'pending' | 'verified' | 'rejected'
  created_at: string
  updated_at: string
}

export interface LoginCredentials {
  email: string
  password: string
}

export interface RegisterData {
  name: string
  email: string
  password: string
  password_confirmation: string
  phone?: string
}

export const useAuth = () => {
  const [token, setToken] = useState<string | null>(() => {
    return localStorage.getItem('auth_token')
  })
  
  const queryClient = useQueryClient()

  // Get current user
  const { data: user, isLoading, error } = useQuery({
    queryKey: ['user'],
    queryFn: async () => {
      if (!token) return null
      try {
        const response = await api.get('/user')
        return response.data.user
      } catch (error) {
        // If token is invalid, remove it
        localStorage.removeItem('auth_token')
        setToken(null)
        throw error
      }
    },
    enabled: !!token,
    retry: false,
  })

  // Login mutation
  const loginMutation = useMutation({
    mutationFn: async (credentials: LoginCredentials) => {
      const response = await api.post('/login', credentials)
      return response.data
    },
    onSuccess: (data) => {
      const { token: authToken, user: userData } = data
      localStorage.setItem('auth_token', authToken)
      setToken(authToken)
      queryClient.setQueryData(['user'], userData)
      toast.success('Welcome back!')
    },
    onError: (error: any) => {
      const message = error.response?.data?.message || 'Login failed'
      toast.error(message)
    },
  })

  // Register mutation
  const registerMutation = useMutation({
    mutationFn: async (data: RegisterData) => {
      const response = await api.post('/register', data)
      return response.data
    },
    onSuccess: (data) => {
      const { token: authToken, user: userData } = data
      localStorage.setItem('auth_token', authToken)
      setToken(authToken)
      queryClient.setQueryData(['user'], userData)
      toast.success('Account created successfully!')
    },
    onError: (error: any) => {
      const message = error.response?.data?.message || 'Registration failed'
      toast.error(message)
    },
  })

  // Logout mutation
  const logoutMutation = useMutation({
    mutationFn: async () => {
      await api.post('/logout')
    },
    onSuccess: () => {
      localStorage.removeItem('auth_token')
      setToken(null)
      queryClient.clear()
      toast.success('Logged out successfully')
    },
    onError: () => {
      // Even if logout fails on server, clear local data
      localStorage.removeItem('auth_token')
      setToken(null)
      queryClient.clear()
    },
  })

  // Update API token when token changes
  useEffect(() => {
    if (token) {
      api.defaults.headers.common['Authorization'] = `Bearer ${token}`
    } else {
      delete api.defaults.headers.common['Authorization']
    }
  }, [token])

  return {
    user: user || null,
    isLoading,
    isAuthenticated: !!user,
    login: loginMutation.mutate,
    register: registerMutation.mutate,
    logout: logoutMutation.mutate,
    isLoginLoading: loginMutation.isPending,
    isRegisterLoading: registerMutation.isPending,
    isLogoutLoading: logoutMutation.isPending,
  }
}