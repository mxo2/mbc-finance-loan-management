import React, { useState, useEffect } from 'react'
import { motion } from 'framer-motion'
import { 
  CalendarIcon, 
  CurrencyRupeeIcon, 
  ClockIcon, 
  CheckCircleIcon,
  ExclamationTriangleIcon,
  CreditCardIcon,
  BanknotesIcon,
  DocumentTextIcon
} from '@heroicons/react/24/outline'

interface RepaymentSchedule {
  id: number
  loan_id: number
  due_date: string
  installment_amount: number
  interest: number
  penality: number
  total_amount: number
  status: string
  payment_type?: string
  receipt?: string
}

interface Loan {
  id: number
  loan_id: string
  amount: number
  status: string
  loan_start_date: string
  loan_due_date: string
  loanType: {
    id: number
    name: string
    interest_rate: number
  }
}

const PayEMI: React.FC = () => {
  const [loans, setLoans] = useState<Loan[]>([])
  const [selectedLoan, setSelectedLoan] = useState<Loan | null>(null)
  const [repaymentSchedule, setRepaymentSchedule] = useState<RepaymentSchedule[]>([])
  const [upcomingEMI, setUpcomingEMI] = useState<RepaymentSchedule | null>(null)
  const [loading, setLoading] = useState(true)
  const [paymentMethod, setPaymentMethod] = useState<'upi' | 'card' | 'netbanking' | null>(null)
  const [showPaymentModal, setShowPaymentModal] = useState(false)

  useEffect(() => {
    // Check if user is authenticated
    const token = localStorage.getItem('auth_token')
    if (!token) {
      window.location.href = '/login'
      return
    }
    
    fetchUserLoans()
  }, [])

  useEffect(() => {
    if (selectedLoan) {
      fetchRepaymentSchedule(selectedLoan.id)
    }
  }, [selectedLoan])

  const fetchUserLoans = async () => {
    try {
      const response = await fetch('/api/pwa/loans', {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
          'Content-Type': 'application/json',
        },
      })

      if (response.ok) {
        const data = await response.json()
        setLoans(data.loans || [])
        if (data.loans && data.loans.length > 0) {
          setSelectedLoan(data.loans[0])
        }
      } else if (response.status === 401) {
        // Redirect to login if unauthorized
        window.location.href = '/login'
      }
    } catch (error) {
      console.error('Error fetching loans:', error)
    } finally {
      setLoading(false)
    }
  }

  const fetchRepaymentSchedule = async (loanId: number) => {
    try {
      const response = await fetch(`/api/pwa/loans/${loanId}/repayment-schedule`, {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
          'Content-Type': 'application/json',
        },
      })

      if (response.ok) {
        const data = await response.json()
        setRepaymentSchedule(data.schedule || [])
        
        // Find the next pending EMI
        const nextPendingEmi = data.schedule?.find((emi: any) => emi.status === 'Pending')
        setUpcomingEMI(nextPendingEmi || null)
      } else if (response.status === 401) {
        // Redirect to login if unauthorized
        window.location.href = '/login'
      }
    } catch (error) {
      console.error('Error fetching repayment schedule:', error)
    }
  }

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-IN', {
      day: 'numeric',
      month: 'long',
      year: 'numeric'
    })
  }

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-IN', {
      style: 'currency',
      currency: 'INR',
      minimumFractionDigits: 0
    }).format(amount)
  }

  const isEMIDue = (dueDate: string) => {
    const today = new Date()
    const due = new Date(dueDate)
    const diffTime = due.getTime() - today.getTime()
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
    return diffDays <= 0
  }

  const getDaysUntilDue = (dueDate: string) => {
    const today = new Date()
    const due = new Date(dueDate)
    const diffTime = due.getTime() - today.getTime()
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
    return diffDays
  }

  const handlePayNow = () => {
    setShowPaymentModal(true)
  }

  const processPayment = async () => {
    if (!upcomingEMI || !paymentMethod) return

    try {
      const response = await fetch('/api/pwa/emi/pay', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          schedule_id: upcomingEMI.id,
          payment_method: paymentMethod,
          amount: upcomingEMI.total_amount
        })
      })

      if (response.ok) {
        alert('Payment successful!')
        setShowPaymentModal(false)
        if (selectedLoan) {
          fetchRepaymentSchedule(selectedLoan.id)
        }
      } else if (response.status === 401) {
        window.location.href = '/login'
      } else {
        alert('Payment failed. Please try again.')
      }
    } catch (error) {
      console.error('Payment error:', error)
      alert('Payment failed. Please try again.')
    }
  }

  if (loading) {
    return (
      <div className="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center">
        <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-600"></div>
      </div>
    )
  }

  if (loans.length === 0) {
    return (
      <div className="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
        {/* Header with Logo */}
        <div className="bg-white shadow-sm border-b">
          <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div className="flex items-center justify-between h-16">
              <div className="flex items-center">
                <img 
                  src="/pwa/logo_mbc.png" 
                  alt="MBC Finance" 
                  className="h-10 w-auto"
                />
                <span className="ml-3 text-xl font-bold text-gray-900">MBC Finance</span>
              </div>
              <h1 className="text-lg font-semibold text-gray-900">Pay EMI</h1>
            </div>
          </div>
        </div>

        <div className="flex items-center justify-center min-h-[80vh]">
          <div className="text-center">
            <DocumentTextIcon className="mx-auto h-16 w-16 text-gray-400" />
            <h2 className="mt-4 text-xl font-semibold text-gray-900">No Active Loans</h2>
            <p className="mt-2 text-gray-600">You don't have any active loans at the moment.</p>
            <button 
              onClick={() => window.location.href = '/pwa/enhanced-loan-application'}
              className="mt-4 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
            >
              Apply for Loan
            </button>
          </div>
        </div>
      </div>
    )
  }

  return (
    <div className="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
      {/* Header with Logo */}
      <div className="bg-white shadow-sm border-b">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex items-center justify-between h-16">
            <div className="flex items-center">
              <img 
                src="/pwa/logo_mbc.png" 
                alt="MBC Finance" 
                className="h-10 w-auto"
              />
              <span className="ml-3 text-xl font-bold text-gray-900">MBC Finance</span>
            </div>
            <h1 className="text-lg font-semibold text-gray-900">Pay EMI</h1>
          </div>
        </div>
      </div>

      <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {/* Loan Selection */}
        {loans.length > 1 && (
          <div className="mb-6">
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Select Loan
            </label>
            <select 
              value={selectedLoan?.id || ''} 
              onChange={(e) => {
                const loan = loans.find(l => l.id === parseInt(e.target.value))
                setSelectedLoan(loan || null)
              }}
              className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            >
              {loans.map(loan => (
                <option key={loan.id} value={loan.id}>
                  Loan #{loan.loan_id} - {formatCurrency(loan.amount)}
                </option>
              ))}
            </select>
          </div>
        )}

        {selectedLoan && (
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            className="space-y-6"
          >
            {/* Loan Overview Card */}
            <div className="bg-white rounded-xl shadow-lg p-6">
              <h2 className="text-xl font-bold text-gray-900 mb-4">Loan Overview</h2>
              <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div className="text-center p-4 bg-blue-50 rounded-lg">
                  <CurrencyRupeeIcon className="h-8 w-8 text-blue-600 mx-auto mb-2" />
                  <p className="text-sm text-gray-600">Loan Amount</p>
                  <p className="text-lg font-bold text-gray-900">{formatCurrency(selectedLoan.amount)}</p>
                </div>
                <div className="text-center p-4 bg-green-50 rounded-lg">
                  <CheckCircleIcon className="h-8 w-8 text-green-600 mx-auto mb-2" />
                  <p className="text-sm text-gray-600">Status</p>
                  <p className="text-lg font-bold text-green-600 capitalize">{selectedLoan.status}</p>
                </div>
                <div className="text-center p-4 bg-purple-50 rounded-lg">
                  <CalendarIcon className="h-8 w-8 text-purple-600 mx-auto mb-2" />
                  <p className="text-sm text-gray-600">Start Date</p>
                  <p className="text-lg font-bold text-gray-900">{formatDate(selectedLoan.loan_start_date)}</p>
                </div>
              </div>
            </div>

            {/* Upcoming EMI Card */}
            {upcomingEMI ? (
              <div className={`bg-white rounded-xl shadow-lg p-6 border-l-4 ${
                isEMIDue(upcomingEMI.due_date) ? 'border-red-500' : 'border-yellow-500'
              }`}>
                <div className="flex items-center justify-between mb-4">
                  <h2 className="text-xl font-bold text-gray-900">Upcoming EMI</h2>
                  <div className={`flex items-center px-3 py-1 rounded-full text-sm font-medium ${
                    isEMIDue(upcomingEMI.due_date) 
                      ? 'bg-red-100 text-red-800' 
                      : 'bg-yellow-100 text-yellow-800'
                  }`}>
                    {isEMIDue(upcomingEMI.due_date) ? (
                      <>
                        <ExclamationTriangleIcon className="h-4 w-4 mr-1" />
                        EMI Due
                      </>
                    ) : (
                      <>
                        <ClockIcon className="h-4 w-4 mr-1" />
                        {getDaysUntilDue(upcomingEMI.due_date)} days left
                      </>
                    )}
                  </div>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div className="space-y-3">
                    <div className="flex justify-between">
                      <span className="text-gray-600">Due Date:</span>
                      <span className="font-semibold">{formatDate(upcomingEMI.due_date)}</span>
                    </div>
                    <div className="flex justify-between">
                      <span className="text-gray-600">Principal Amount:</span>
                      <span className="font-semibold">{formatCurrency(upcomingEMI.installment_amount)}</span>
                    </div>
                    <div className="flex justify-between">
                      <span className="text-gray-600">Interest:</span>
                      <span className="font-semibold">{formatCurrency(upcomingEMI.interest)}</span>
                    </div>
                    {upcomingEMI.penality > 0 && (
                      <div className="flex justify-between">
                        <span className="text-red-600">Penalty:</span>
                        <span className="font-semibold text-red-600">{formatCurrency(upcomingEMI.penality)}</span>
                      </div>
                    )}
                    <hr />
                    <div className="flex justify-between text-lg">
                      <span className="font-bold">Total Amount:</span>
                      <span className="font-bold text-blue-600">{formatCurrency(upcomingEMI.total_amount)}</span>
                    </div>
                  </div>
                  
                  <div className="flex items-center justify-center">
                    <button
                      onClick={handlePayNow}
                      className={`px-8 py-4 rounded-lg text-white font-semibold text-lg transition-colors ${
                        isEMIDue(upcomingEMI.due_date)
                          ? 'bg-red-600 hover:bg-red-700'
                          : 'bg-blue-600 hover:bg-blue-700'
                      }`}
                    >
                      Pay Now
                    </button>
                  </div>
                </div>
              </div>
            ) : (
              <div className="bg-white rounded-xl shadow-lg p-8 text-center">
                <CheckCircleIcon className="h-16 w-16 text-green-500 mx-auto mb-4" />
                <h2 className="text-xl font-bold text-gray-900 mb-2">No EMI Due</h2>
                <p className="text-gray-600">All your EMIs are up to date. Next EMI will be available when due.</p>
              </div>
            )}

            {/* EMI Schedule */}
            {repaymentSchedule.length > 0 && (
              <div className="bg-white rounded-xl shadow-lg p-6">
                <h2 className="text-xl font-bold text-gray-900 mb-4">EMI Schedule</h2>
                <div className="overflow-x-auto">
                  <table className="w-full">
                    <thead>
                      <tr className="border-b">
                        <th className="text-left py-2">Due Date</th>
                        <th className="text-right py-2">Amount</th>
                        <th className="text-center py-2">Status</th>
                      </tr>
                    </thead>
                    <tbody>
                      {repaymentSchedule.map((emi) => (
                        <tr key={emi.id} className="border-b">
                          <td className="py-3">{formatDate(emi.due_date)}</td>
                          <td className="text-right py-3">{formatCurrency(emi.total_amount)}</td>
                          <td className="text-center py-3">
                            <span className={`px-2 py-1 rounded-full text-sm ${
                              emi.status === 'Paid' 
                                ? 'bg-green-100 text-green-800' 
                                : 'bg-yellow-100 text-yellow-800'
                            }`}>
                              {emi.status}
                            </span>
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              </div>
            )}
          </motion.div>
        )}
      </div>

      {/* Payment Modal */}
      {showPaymentModal && upcomingEMI && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
          <div className="bg-white rounded-xl p-8 max-w-md w-full mx-4">
            <h3 className="text-xl font-bold text-gray-900 mb-4">Select Payment Method</h3>
            <p className="text-gray-600 mb-6">
              Amount to pay: <span className="font-bold text-blue-600">{formatCurrency(upcomingEMI.total_amount)}</span>
            </p>
            
            <div className="space-y-3 mb-6">
              <button
                onClick={() => setPaymentMethod('upi')}
                className={`w-full p-4 border rounded-lg flex items-center ${
                  paymentMethod === 'upi' ? 'border-blue-500 bg-blue-50' : 'border-gray-300'
                }`}
              >
                <CreditCardIcon className="h-6 w-6 mr-3" />
                UPI Payment
              </button>
              
              <button
                onClick={() => setPaymentMethod('card')}
                className={`w-full p-4 border rounded-lg flex items-center ${
                  paymentMethod === 'card' ? 'border-blue-500 bg-blue-50' : 'border-gray-300'
                }`}
              >
                <CreditCardIcon className="h-6 w-6 mr-3" />
                Credit/Debit Card
              </button>
              
              <button
                onClick={() => setPaymentMethod('netbanking')}
                className={`w-full p-4 border rounded-lg flex items-center ${
                  paymentMethod === 'netbanking' ? 'border-blue-500 bg-blue-50' : 'border-gray-300'
                }`}
              >
                <BanknotesIcon className="h-6 w-6 mr-3" />
                Net Banking
              </button>
            </div>
            
            <div className="flex gap-3">
              <button
                onClick={() => setShowPaymentModal(false)}
                className="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
              >
                Cancel
              </button>
              <button
                onClick={processPayment}
                disabled={!paymentMethod}
                className="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
              >
                Pay Now
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}

export default PayEMI