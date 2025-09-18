import axios from 'axios'

// Get the API URL from environment or fallback to local
const apiUrl = typeof import.meta.env.VITE_API_URL === 'string' 
  ? import.meta.env.VITE_API_URL 
  : 'https://fix.mbcfinserv.com';

// Create axios instance
export const api = axios.create({
  baseURL: apiUrl,
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
      window.location.href = '/pwa/login'
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
    api.post('/pwa-login', credentials),
  
  register: (data: { name: string; email: string; password: string; password_confirmation: string; phone?: string }) => 
    api.post('/register', data),
  
  logout: () => api.post('/pwa-logout'),
  
  getUser: () => api.get('/pwa/user'),
  
  updateProfile: (data: any) => api.put('/user/profile', data),
}

export const loanAPI = {
  getLoanTypes: () => api.get('/pwa/loan-types'),
  
  applyLoan: (data: FormData) => api.post('/loans', data, {
    headers: {
      'Content-Type': 'multipart/form-data',
    },
  }),
  
  getLoans: () => api.get('/pwa/loans'),
  
  getLoan: (id: number) => api.get(`/pwa/loans/${id}`),
  
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
  getStats: () => api.get('/pwa/dashboard'),
  
  getRecentLoans: () => api.get('/pwa/loans'),
  
  getRepaymentSchedule: () => api.get('/pwa/repayment-schedule'),
  
  getNotifications: () => api.get('/notifications'),
  
  markNotificationRead: (id: number) => api.put(`/notifications/${id}/read`),
  
  calculateEMI: (data: { amount: number; interest_rate: number; tenure: number }) =>
    api.post('/pwa/calculate-emi', data),
}

export default api