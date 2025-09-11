import axios from 'axios'

// Create axios instance
export const api = axios.create({
  baseURL: 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  timeout: 10000,
})

// Request interceptor
api.interceptors.request.use(
  (config) => {
    // Add auth token if available
    const token = localStorage.getItem('auth_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Response interceptor
api.interceptors.response.use(
  (response) => {
    return response
  },
  (error) => {
    // Handle 401 errors (unauthorized)
    if (error.response?.status === 401) {
      localStorage.removeItem('auth_token')
      window.location.href = '/login'
    }
    
    // Handle network errors
    if (!error.response) {
      console.error('Network error:', error.message)
    }
    
    return Promise.reject(error)
  }
)

// API endpoints
export const authAPI = {
  login: (credentials: { email: string; password: string }) => 
    api.post('/login', credentials),
  
  register: (data: { name: string; email: string; password: string; password_confirmation: string; phone?: string }) => 
    api.post('/register', data),
  
  logout: () => api.post('/logout'),
  
  getUser: () => api.get('/user'),
  
  updateProfile: (data: any) => api.put('/user/profile', data),
}

export const loanAPI = {
  getLoanTypes: () => api.get('/loan-types'),
  
  applyLoan: (data: FormData) => api.post('/loans', data, {
    headers: {
      'Content-Type': 'multipart/form-data',
    },
  }),
  
  getLoans: () => api.get('/loans'),
  
  getLoan: (id: number) => api.get(`/loans/${id}`),
  
  uploadDocument: (loanId: number, data: FormData) => 
    api.post(`/loans/${loanId}/documents`, data, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    }),
}

export const kycAPI = {
  getKYCStatus: () => api.get('/kyc/status'),
  
  submitKYC: (data: FormData) => api.post('/kyc/submit', data, {
    headers: {
      'Content-Type': 'multipart/form-data',
    },
  }),
  
  updateKYC: (data: FormData) => api.put('/kyc/update', data, {
    headers: {
      'Content-Type': 'multipart/form-data',
    },
  }),
}

export const dashboardAPI = {
  getStats: () => api.get('/dashboard/stats'),
  
  getRecentLoans: () => api.get('/dashboard/recent-loans'),
  
  getNotifications: () => api.get('/notifications'),
  
  markNotificationRead: (id: number) => api.put(`/notifications/${id}/read`),
}

export default api