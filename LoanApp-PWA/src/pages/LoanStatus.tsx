import { motion } from 'framer-motion'

interface Loan {
  id: number
  type: string
  amount: number
  status: 'pending' | 'approved' | 'rejected' | 'disbursed'
  appliedDate: string
  dueDate?: string
}

const LoanStatus = () => {
  // Mock data - replace with API call
  const loans: Loan[] = [
    {
      id: 1,
      type: 'Personal Loan',
      amount: 25000,
      status: 'approved',
      appliedDate: '2024-01-15',
      dueDate: '2025-01-15'
    },
    {
      id: 2,
      type: 'Home Loan',
      amount: 500000,
      status: 'pending',
      appliedDate: '2024-01-20'
    },
    {
      id: 3,
      type: 'Business Loan',
      amount: 100000,
      status: 'rejected',
      appliedDate: '2024-01-10'
    }
  ]

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'approved':
      case 'disbursed':
        return 'bg-success-100 text-success-800'
      case 'rejected':
        return 'bg-error-100 text-error-800'
      case 'pending':
        return 'bg-warning-100 text-warning-800'
      default:
        return 'bg-gray-100 text-gray-800'
    }
  }

  const getStatusIcon = (status: string) => {
    switch (status) {
      case 'approved':
        return '✅'
      case 'disbursed':
        return '💰'
      case 'rejected':
        return '❌'
      case 'pending':
        return '⏳'
      default:
        return '📄'
    }
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
          <p className="text-gray-600">Track the status of your loan applications</p>
        </div>

        {/* Summary Cards */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
          <div className="card p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-600">Total Applications</p>
                <p className="text-2xl font-bold text-gray-900">{loans.length}</p>
              </div>
              <div className="text-3xl">📋</div>
            </div>
          </div>
          
          <div className="card p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-600">Approved Loans</p>
                <p className="text-2xl font-bold text-success-600">
                  {loans.filter(loan => loan.status === 'approved' || loan.status === 'disbursed').length}
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
                  ₹{loans.reduce((sum, loan) => sum + loan.amount, 0).toLocaleString()}
                </p>
              </div>
              <div className="text-3xl">💰</div>
            </div>
          </div>
        </div>

        {/* Loans List */}
        <div className="space-y-4">
          {loans.map((loan, index) => (
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
                    <p className="text-sm text-gray-600">Application #{loan.id}</p>
                  </div>
                </div>
                <div className={`px-3 py-1 rounded-full text-sm font-medium ${getStatusColor(loan.status)}`}>
                  {loan.status.charAt(0).toUpperCase() + loan.status.slice(1)}
                </div>
              </div>
              
              <div className="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div>
                  <span className="text-gray-600">Amount:</span>
                  <p className="font-medium">₹{loan.amount.toLocaleString()}</p>
                </div>
                <div>
                  <span className="text-gray-600">Applied Date:</span>
                  <p className="font-medium">{new Date(loan.appliedDate).toLocaleDateString()}</p>
                </div>
                {loan.dueDate && (
                  <div>
                    <span className="text-gray-600">Due Date:</span>
                    <p className="font-medium">{new Date(loan.dueDate).toLocaleDateString()}</p>
                  </div>
                )}
                <div>
                  <button className="text-primary-600 hover:text-primary-700 font-medium">
                    View Details →
                  </button>
                </div>
              </div>
              
              {/* Progress for pending loans */}
              {loan.status === 'pending' && (
                <div className="mt-4 pt-4 border-t border-gray-200">
                  <div className="flex items-center justify-between text-sm mb-2">
                    <span className="text-gray-600">Application Progress</span>
                    <span className="text-gray-600">60%</span>
                  </div>
                  <div className="w-full bg-gray-200 rounded-full h-2">
                    <div className="bg-primary-600 h-2 rounded-full" style={{ width: '60%' }}></div>
                  </div>
                  <p className="text-xs text-gray-500 mt-2">Under review by our team</p>
                </div>
              )}
            </motion.div>
          ))}
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