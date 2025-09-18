import { motion } from 'framer-motion'
import { useQuery } from '@tanstack/react-query'
import { useState } from 'react'
import { loanAPI, dashboardAPI } from '../services/api'
import LoadingSpinner from '../components/LoadingSpinner'

interface Loan {
  id: number
  loan_id: string
  loan_number: number
  amount: number
  status: string
  pending_amount: number
  paid_amount: number
  type: string
  purpose: string
  start_date: string
  due_date: string
  terms: number
  term_period: string
  interest_rate: number
  monthly_emi: number
  next_emi_date: string
  created_at: string
  updated_at: string
}

interface RepaymentSchedule {
  id: number
  loan_no: string
  payment_date: string
  principal_amount: number
  interest: number
  penalty: number
  total_amount: number
  status: string
  paid_amount: number
  loan_amount: number
  loan_status: string
  loan_type: string
  loan_purpose: string
  days_remaining: number
}

const LoanStatus = () => {
  const [selectedLoanId, setSelectedLoanId] = useState<string | null>(null)

  // Fetch loans data
  const { data: loansData, isLoading: loansLoading, error: loansError } = useQuery({
    queryKey: ['loans'],
    queryFn: async () => {
      const response = await loanAPI.getLoans()
      return response.data
    },
    refetchOnWindowFocus: false,
  })

  // Fetch repayment schedule
  const { data: repaymentData, isLoading: repaymentLoading } = useQuery({
    queryKey: ['repayment-schedule'],
    queryFn: async () => {
      const response = await dashboardAPI.getRepaymentSchedule()
      return response.data
    },
    refetchOnWindowFocus: false,
  })

  if (loansLoading) {
    return (
      <div className="flex items-center justify-center h-64">
        <LoadingSpinner size="lg" />
      </div>
    )
  }

  if (loansError) {
    return (
      <div className="text-center text-red-600 p-6">
        <p>Failed to load loan data. Please try refreshing the page.</p>
      </div>
    )
  }

  const loans: Loan[] = loansData?.loans || []
  const repaymentSchedules: RepaymentSchedule[] = repaymentData?.repayment_schedules || []

  const getStatusColor = (status: string) => {
    switch (status.toLowerCase()) {
      case 'approved':
      case 'disbursed':
        return 'bg-success-100 text-success-800'
      case 'rejected':
        return 'bg-error-100 text-error-800'
      case 'pending':
        return 'bg-warning-100 text-warning-800'
      case 'closed':
        return 'bg-gray-100 text-gray-800'
      default:
        return 'bg-blue-100 text-blue-800'
    }
  }

  const getStatusIcon = (status: string) => {
    switch (status.toLowerCase()) {
      case 'approved':
        return '✅'
      case 'disbursed':
        return '💰'
      case 'rejected':
        return '❌'
      case 'pending':
        return '⏳'
      case 'closed':
        return '🔒'
      default:
        return '📄'
    }
  }

  const getNextPayment = (loanId: string) => {
    return repaymentSchedules.find(schedule => 
      schedule.loan_no === loanId && 
      schedule.status.toLowerCase() === 'pending'
    )
  }

  const getLoanRepayments = (loanId: string) => {
    return repaymentSchedules.filter(schedule => schedule.loan_no === loanId)
  }

  return (
    <div className="space-y-6">
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.5 }}
      >
        <div className="text-center mb-8">
          <h1 className="text-3xl font-bold text-gray-900 mb-2">My Loans</h1>
          <p className="text-gray-600">Track the status of your loan applications and repayments</p>
        </div>

        {/* Summary Cards */}
        <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
          <div className="card p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-600">Total Loans</p>
                <p className="text-2xl font-bold text-gray-900">{loans.length}</p>
              </div>
              <div className="text-3xl">📋</div>
            </div>
          </div>
          
          <div className="card p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-600">Active Loans</p>
                <p className="text-2xl font-bold text-success-600">
                  {loans.filter(loan => ['approved', 'disbursed'].includes(loan.status.toLowerCase())).length}
                </p>
              </div>
              <div className="text-3xl">✅</div>
            </div>
          </div>
          
          <div className="card p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-600">Total Amount</p>
                <p className="text-2xl font-bold text-primary-600">
                  ₹{loans.reduce((sum, loan) => sum + loan.amount, 0).toLocaleString('en-IN')}
                </p>
              </div>
              <div className="text-3xl">💰</div>
            </div>
          </div>

          <div className="card p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-600">Pending Amount</p>
                <p className="text-2xl font-bold text-orange-600">
                  ₹{loans.reduce((sum, loan) => sum + loan.pending_amount, 0).toLocaleString('en-IN')}
                </p>
              </div>
              <div className="text-3xl">⏰</div>
            </div>
          </div>
        </div>

        {/* Loans List */}
        <div className="space-y-4">
          {loans.map((loan, index) => {
            const nextPayment = getNextPayment(loan.loan_id)
            const loanRepayments = getLoanRepayments(loan.loan_id)
            const isExpanded = selectedLoanId === loan.loan_id

            return (
              <motion.div
                key={loan.id}
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.5, delay: index * 0.1 }}
                className="card p-6"
              >
                <div className="flex items-center justify-between mb-4">
                  <div className="flex items-center space-x-3">
                    <div className="text-2xl">{getStatusIcon(loan.status)}</div>
                    <div>
                      <h3 className="text-lg font-semibold text-gray-900">{loan.type}</h3>
                      <p className="text-sm text-gray-600">{loan.loan_id}</p>
                    </div>
                  </div>
                  <div className={`px-3 py-1 rounded-full text-sm font-medium ${getStatusColor(loan.status)}`}>
                    {loan.status.charAt(0).toUpperCase() + loan.status.slice(1)}
                  </div>
                </div>
                
                <div className="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm mb-4">
                  <div>
                    <span className="text-gray-600">Loan Amount:</span>
                    <p className="font-medium">₹{loan.amount.toLocaleString('en-IN')}</p>
                  </div>
                  <div>
                    <span className="text-gray-600">Pending Amount:</span>
                    <p className="font-medium text-orange-600">₹{loan.pending_amount.toLocaleString('en-IN')}</p>
                  </div>
                  <div>
                    <span className="text-gray-600">Monthly EMI:</span>
                    <p className="font-medium">₹{loan.monthly_emi.toLocaleString('en-IN')}</p>
                  </div>
                  <div>
                    <span className="text-gray-600">Interest Rate:</span>
                    <p className="font-medium">{loan.interest_rate}% p.a.</p>
                  </div>
                </div>

                {/* Next Payment Info */}
                {nextPayment && (
                  <div className="mb-4 p-3 bg-orange-50 rounded-lg border-l-4 border-orange-400">
                    <div className="flex items-center justify-between">
                      <div>
                        <p className="text-sm font-medium text-orange-800">Next EMI Due</p>
                        <p className="text-lg font-bold text-orange-900">
                          ₹{nextPayment.total_amount.toLocaleString('en-IN')} on {new Date(nextPayment.payment_date).toLocaleDateString('en-IN')}
                        </p>
                        <p className="text-xs text-orange-700">
                          {nextPayment.days_remaining > 0 ? `${nextPayment.days_remaining} days remaining` : 'Overdue'}
                        </p>
                      </div>
                      <button className="btn btn-sm btn-primary">
                        Pay Now
                      </button>
                    </div>
                  </div>
                )}

                <div className="flex items-center justify-between">
                  <div className="text-sm text-gray-600">
                    <span>Purpose: {loan.purpose}</span> • <span>Term: {loan.terms} {loan.term_period}</span>
                  </div>
                  <button 
                    onClick={() => setSelectedLoanId(isExpanded ? null : loan.loan_id)}
                    className="text-primary-600 hover:text-primary-700 font-medium text-sm"
                  >
                    {isExpanded ? 'Hide Details ↑' : 'View Details →'}
                  </button>
                </div>

                {/* Expanded Details */}
                {isExpanded && (
                  <motion.div
                    initial={{ opacity: 0, height: 0 }}
                    animate={{ opacity: 1, height: 'auto' }}
                    exit={{ opacity: 0, height: 0 }}
                    className="mt-4 pt-4 border-t border-gray-200"
                  >
                    <h4 className="font-semibold text-gray-900 mb-3">Repayment Schedule</h4>
                    {repaymentLoading ? (
                      <div className="flex items-center justify-center py-4">
                        <LoadingSpinner size="sm" />
                      </div>
                    ) : (
                      <div className="overflow-x-auto">
                        <table className="min-w-full text-sm">
                          <thead>
                            <tr className="bg-gray-50">
                              <th className="text-left p-2">Due Date</th>
                              <th className="text-left p-2">Principal</th>
                              <th className="text-left p-2">Interest</th>
                              <th className="text-left p-2">Total</th>
                              <th className="text-left p-2">Status</th>
                            </tr>
                          </thead>
                          <tbody>
                            {loanRepayments.slice(0, 5).map((schedule, idx) => (
                              <tr key={idx} className="border-b">
                                <td className="p-2">{new Date(schedule.payment_date).toLocaleDateString('en-IN')}</td>
                                <td className="p-2">₹{schedule.principal_amount.toLocaleString('en-IN')}</td>
                                <td className="p-2">₹{schedule.interest.toLocaleString('en-IN')}</td>
                                <td className="p-2 font-medium">₹{schedule.total_amount.toLocaleString('en-IN')}</td>
                                <td className="p-2">
                                  <span className={`px-2 py-1 rounded-full text-xs ${getStatusColor(schedule.status)}`}>
                                    {schedule.status}
                                  </span>
                                </td>
                              </tr>
                            ))}
                          </tbody>
                        </table>
                        {loanRepayments.length > 5 && (
                          <p className="text-center text-sm text-gray-500 mt-2">
                            Showing 5 of {loanRepayments.length} payments
                          </p>
                        )}
                      </div>
                    )}
                  </motion.div>
                )}
              </motion.div>
            )
          })}
        </div>

        {/* Empty State */}
        {loans.length === 0 && (
          <div className="card p-12 text-center">
            <div className="text-6xl mb-4">📋</div>
            <h3 className="text-xl font-semibold text-gray-900 mb-2">No Loan Applications</h3>
            <p className="text-gray-600 mb-6">You haven't applied for any loans yet.</p>
            <button className="btn btn-primary btn-lg">
              Apply for Loan
            </button>
          </div>
        )}
      </motion.div>
    </div>
  )
}

export default LoanStatus