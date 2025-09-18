import { Routes, Route, useNavigate } from 'react-router-dom'
import { useState, useEffect } from 'react'
import EnhancedLoanApplication from './pages/EnhancedLoanApplication'

// Loan Overview Component
function LoanOverview() {
  const [loans, setLoans] = useState<any[]>([])
  const [loading, setLoading] = useState(true)
  const token = localStorage.getItem('auth_token')

  useEffect(() => {
    const fetchLoans = async () => {
      try {
        const response = await fetch('/api/dashboard', {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
          }
        })
        
        // Handle response similar to other APIs
        const responseText = await response.text()
        let data
        try {
          data = JSON.parse(responseText)
        } catch (parseError) {
          const jsonMatch = responseText.match(/\{.*\}$/s)
          if (jsonMatch) {
            data = JSON.parse(jsonMatch[0])
          } else {
            throw new Error('No valid JSON found in response')
          }
        }
        
        if (response.ok && data.success) {
          // Filter to show only active loans (Approved status indicates active loans)
          const activeLoans = (data.loans || []).filter((loan: any) => 
            loan.status === 'Approved' || loan.status === 'Active'
          )
          setLoans(activeLoans)
        } else {
          setLoans([])
        }
      } catch (error) {
        console.error('Error fetching loans:', error)
        setLoans([])
      } finally {
        setLoading(false)
      }
    }
    fetchLoans()
  }, [token])

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-600"></div>
      </div>
    )
  }

  // Calculate EMI statistics for active loans
  const calculateLoanStats = (loan: any) => {
    if (!loan.repayment_schedules || loan.status !== 'Approved') {
      return null
    }
    
    const schedules = loan.repayment_schedules
    const totalEMIPaid = schedules
      .filter((schedule: any) => schedule.status === 'Paid')
      .reduce((sum: number, schedule: any) => sum + parseFloat(schedule.total_amount || 0), 0)
    
    const totalLoanValue = parseFloat(loan.amount || loan.principal || 0)
    const remainingAmount = parseFloat(loan.pending_amount || loan.outstanding || 0)
    
    return {
      totalLoanValue,
      totalEMIPaid,
      remainingAmount
    }
  }

  return (
    <div className="min-h-screen bg-blue-50 p-6">
      <div className="max-w-6xl mx-auto">
        <div className="mb-8">
          <h1 className="text-3xl font-bold text-blue-900">Loan Overview</h1>
          <p className="text-blue-700 mt-2">Manage all your loans in one place</p>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {loans.map((loan, index) => (
            <div key={loan.id || index} className="bg-white rounded-lg shadow-lg border border-blue-200 p-6">
              <div className="flex justify-between items-start mb-4">
                <div>
                  <h3 className="text-lg font-semibold text-blue-900">{loan.type || 'Personal Loan'}</h3>
                  <p className="text-sm text-blue-600">{loan.loan_id}</p>
                </div>
                <span className={`px-3 py-1 rounded-full text-xs font-medium ${
                  loan.status === 'Approved' ? 'bg-green-100 text-green-800' :
                  loan.status === 'Pending' ? 'bg-yellow-100 text-yellow-800' :
                  loan.status === 'Active' ? 'bg-blue-100 text-blue-800' :
                  'bg-red-100 text-red-800'
                }`}>
                  {loan.status || 'Active'}
                </span>
              </div>
              <div className="space-y-3">
                <div className="flex justify-between">
                  <span className="text-gray-600">Loan Amount:</span>
                  <span className="font-semibold text-blue-900">₹{(loan.amount || loan.principal || 0).toLocaleString()}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-600">Outstanding:</span>
                  <span className="font-semibold text-red-600">₹{(loan.pending_amount || loan.outstanding || 0).toLocaleString()}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-600">Loan Purpose:</span>
                  <span className="font-medium text-gray-800">{loan.purpose || 'Personal'}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-600">Start Date:</span>
                  <span className="font-medium">{loan.start_date ? new Date(loan.start_date).toLocaleDateString() : 'N/A'}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-600">Due Date:</span>
                  <span className="font-medium">{loan.due_date ? new Date(loan.due_date).toLocaleDateString() : 'N/A'}</span>
                </div>
                
                {/* EMI Statistics for Active Loans */}
                {(() => {
                  const stats = calculateLoanStats(loan)
                  if (stats) {
                    return (
                      <div className="mt-4 pt-3 border-t border-gray-100">
                        <div className="text-sm font-medium text-blue-900 mb-2">EMI Payment Progress</div>
                        <div className="space-y-2">
                          <div className="flex justify-between text-sm">
                            <span className="text-gray-600">Total Loan Value:</span>
                            <span className="font-semibold text-blue-900">₹{stats.totalLoanValue.toLocaleString()}</span>
                          </div>
                          <div className="flex justify-between text-sm">
                            <span className="text-gray-600">Total EMI Paid:</span>
                            <span className="font-semibold text-green-600">₹{stats.totalEMIPaid.toLocaleString()}</span>
                          </div>
                          <div className="flex justify-between text-sm">
                            <span className="text-gray-600">Remaining Amount:</span>
                            <span className="font-semibold text-red-600">₹{stats.remainingAmount.toLocaleString()}</span>
                          </div>
                          <div className="mt-2">
                            <div className="w-full bg-gray-200 rounded-full h-2">
                              <div 
                                className="bg-blue-600 h-2 rounded-full" 
                                style={{width: `${((stats.totalEMIPaid / stats.totalLoanValue) * 100).toFixed(1)}%`}}
                              ></div>
                            </div>
                            <div className="text-xs text-gray-500 mt-1 text-center">
                              {((stats.totalEMIPaid / stats.totalLoanValue) * 100).toFixed(1)}% paid
                            </div>
                          </div>
                        </div>
                      </div>
                    )
                  }
                  return null
                })()}
              </div>
              <div className="mt-6 pt-4 border-t border-gray-200">
                <div className="flex space-x-2">
                  <button className="flex-1 bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                    View Details
                  </button>
                  <button className="flex-1 bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700 transition-colors text-sm font-medium">
                    Pay EMI
                  </button>
                </div>
              </div>
            </div>
          ))}
        </div>

        {loans.length === 0 && (
          <div className="text-center py-12">
            <div className="w-24 h-24 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <svg className="w-12 h-12 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
            <h3 className="text-lg font-medium text-blue-900 mb-2">No loans found</h3>
            <p className="text-blue-600 mb-4">You don't have any loans yet. Apply for a new loan to get started.</p>
            <button className="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors font-medium">
              Apply for Loan
            </button>
          </div>
        )}
      </div>
    </div>
  )
}

// Repayment Schedule Component
function RepaymentSchedule() {
  const [schedule, setSchedule] = useState<any[]>([])
  const [selectedLoan, setSelectedLoan] = useState('all')
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const token = localStorage.getItem('auth_token')

  useEffect(() => {
    const fetchSchedule = async () => {
      try {
        setLoading(true)
        const response = await fetch('/api/customer/repayment-schedule', {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
          }
        })
        
        // Handle response similar to dashboard
        const responseText = await response.text()
        let data
        try {
          data = JSON.parse(responseText)
        } catch (parseError) {
          const jsonMatch = responseText.match(/\{.*\}$/s)
          if (jsonMatch) {
            data = JSON.parse(jsonMatch[0])
          } else {
            throw new Error('No valid JSON found in response')
          }
        }
        
        if (response.ok && data.success) {
          setSchedule(data.repayment_schedules || [])
          setError('')
        } else {
          setError('Failed to load repayment schedule')
        }
      } catch (error) {
        console.error('Error fetching schedule:', error)
        setError('Network error. Please try again.')
      } finally {
        setLoading(false)
      }
    }
    fetchSchedule()
  }, [token, selectedLoan])

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-600"></div>
      </div>
    )
  }

  return (
    <div className="min-h-screen bg-gray-50 p-6">
      <div className="max-w-6xl mx-auto">
        <div className="mb-8">
          <h1 className="text-3xl font-bold text-gray-900">Repayment Schedule</h1>
          <p className="text-gray-600 mt-2">Track your upcoming and past payments</p>
        </div>

        {error && (
          <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {error}
          </div>
        )}

        <div className="bg-white rounded-lg shadow-md overflow-hidden">
          <div className="p-6 border-b">
            <div className="flex justify-between items-center">
              <h2 className="text-xl font-semibold text-gray-900">Repayment Schedule</h2>
              <div className="flex gap-4">
                <select className="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                  <option value="">Select Loan</option>
                  <option value="#LON-0003">#LON-0003</option>
                  <option value="#LON-0008">#LON-0008</option>
                </select>
                <input type="date" className="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Select date" />
                <button className="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition-colors">
                  Search
                </button>
              </div>
            </div>
          </div>

          <div className="overflow-x-auto">
            <table className="w-full">
              <thead className="bg-gray-50">
                <tr>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">LOAN NO.</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PAYMENT DATE</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PRINCIPAL AMOUNT</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">INTEREST</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">STATUS</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ACTION</th>
                </tr>
              </thead>
              <tbody className="bg-white divide-y divide-gray-200">
                {schedule.length > 0 ? schedule.map((payment, index) => (
                  <tr key={index} className={payment.status === 'Overdue' ? 'bg-red-50' : ''}>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                      <button 
                        onClick={() => window.location.href = `/loan-details/${payment.loan_no.replace('#LON-', '')}`}
                        className="text-blue-600 hover:text-blue-800 hover:underline"
                      >
                        {payment.loan_no}
                      </button>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{new Date(payment.payment_date).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })}</td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₹{payment.principal_amount.toLocaleString()}</td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₹{payment.interest.toLocaleString()}</td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <span className={`px-2 py-1 rounded text-xs font-medium ${
                        payment.status === 'Paid' ? 'bg-blue-600 text-white' :
                        payment.status === 'Overdue' ? 'bg-red-100 text-red-800' :
                        'bg-yellow-100 text-yellow-800'
                      }`}>
                        {payment.status}
                      </span>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm">
                      <button 
                        onClick={() => window.location.href = `/loan-details/${payment.loan_no.replace('#LON-', '')}`}
                        className="text-blue-600 hover:text-blue-800"
                      >
                        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                      </button>
                    </td>
                  </tr>
                )) : (
                  <tr>
                    <td colSpan={6} className="px-6 py-4 text-center text-gray-500">
                      No repayment schedule found
                    </td>
                  </tr>
                )}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  )
}

function LoanPage() {
  const [selectedLoanType, setSelectedLoanType] = useState<any>(null)
  const [loanTypes, setLoanTypes] = useState<any[]>([])
  const [loading, setLoading] = useState(true)
  const [formData, setFormData] = useState({
    branch_id: '',
    amount: '',
    referral_code: '',
    purpose_of_loan: '',
    notes: ''
  })
  const [isSubmitting, setIsSubmitting] = useState(false)
  const [message, setMessage] = useState('')
  const token = localStorage.getItem('auth_token')
  
  useEffect(() => {
    const fetchLoanTypes = async () => {
      try {
        const response = await fetch('/api/loan-types', {
          headers: {
            'Content-Type': 'application/json'
          }
        })
        
        const responseText = await response.text()
        let data
        try {
          data = JSON.parse(responseText)
        } catch (parseError) {
          const jsonMatch = responseText.match(/\{.*\}$/s)
          if (jsonMatch) {
            data = JSON.parse(jsonMatch[0])
          } else {
            throw new Error('No valid JSON found in response')
          }
        }
        
        if (response.ok && data.success) {
          setLoanTypes(data.loan_types || [])
          if (data.loan_types && data.loan_types.length > 0) {
            setSelectedLoanType(data.loan_types[0])
          }
        }
      } catch (error) {
        console.error('Error fetching loan types:', error)
      } finally {
        setLoading(false)
      }
    }
    
    fetchLoanTypes()
  }, [])
  
  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    setIsSubmitting(true)
    setMessage('')

    try {
      const userData = JSON.parse(localStorage.getItem('user_data') || '{}')
      
      const applicationData = {
        name: userData.name || 'Customer',
        email: userData.email || 'customer@example.com',
        phone: userData.phone || '1234567890',
        loan_type: selectedLoanType?.id || '1',
        loan_amount: formData.amount,
        loan_purpose: formData.purpose_of_loan,
        tenure: selectedLoanType?.max_loan_term || '12',
        income: '50000',
        employment: 'salaried'
      }

      const response = await fetch('/api/apply-loan', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(applicationData)
      })
      
      const responseText = await response.text()
      let data
      try {
        data = JSON.parse(responseText)
      } catch (parseError) {
        const jsonMatch = responseText.match(/\{.*\}$/s)
        if (jsonMatch) {
          data = JSON.parse(jsonMatch[0])
        } else {
          throw new Error('No valid JSON found in response')
        }
      }
      
      if (response.ok && data.success !== false) {
        setMessage('Loan application submitted successfully! We will review your application and get back to you soon.')
        setFormData({
          branch_id: '',
          amount: '',
          referral_code: '',
          purpose_of_loan: '',
          notes: ''
        })
      } else {
        setMessage(data.message || 'Failed to submit application')
      }
    } catch (error) {
      console.error('Application error:', error)
      setMessage('Network error. Please try again.')
    } finally {
      setIsSubmitting(false)
    }
  }
  
  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-IN', {
      style: 'currency',
      currency: 'INR',
      minimumFractionDigits: 0,
      maximumFractionDigits: 0
    }).format(amount)
  }
  
  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-blue-50">
        <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-600"></div>
      </div>
    )
  }
  
  return (
    <div className="min-h-screen bg-blue-50 p-6">
      <div className="max-w-7xl mx-auto">
        {/* Breadcrumb */}
        <nav className="mb-6">
          <ol className="flex items-center space-x-2 text-sm text-blue-600">
            <li><a href="/dashboard" className="hover:text-blue-800">Dashboard</a></li>
            <li className="text-gray-400">/</li>
            <li><a href="/loan" className="hover:text-blue-800">Loans</a></li>
            <li className="text-gray-400">/</li>
            <li className="text-blue-900 font-medium">Apply</li>
          </ol>
        </nav>
        
        <div className="mb-8">
          <h1 className="text-3xl font-bold text-blue-900">Apply for Loan</h1>
          <p className="text-blue-700 mt-2">Complete your loan application with detailed information</p>
        </div>

        {message && (
          <div className={`p-4 rounded-lg mb-6 ${
            message.includes('successfully') ? 'bg-green-100 text-green-700 border border-green-200' : 'bg-red-100 text-red-700 border border-red-200'
          }`}>
            <div className="flex items-center">
              <svg className="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
              </svg>
              {message}
            </div>
          </div>
        )}
        
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
          {/* Loan Type Information Sidebar */}
          <div className="lg:col-span-1">
            <div className="bg-white rounded-2xl shadow-lg border border-blue-200 p-6 sticky top-6">
              <div className="mb-6">
                <h3 className="text-xl font-bold text-blue-900 mb-2">Loan Type Details</h3>
                <div className="w-12 h-1 bg-blue-600 rounded"></div>
              </div>
              
              {selectedLoanType ? (
                <div className="space-y-4">
                  <div>
                    <h4 className="text-lg font-semibold text-blue-800 mb-3">{selectedLoanType.name}</h4>
                  </div>
                  
                  <div className="bg-blue-50 rounded-lg p-4">
                    <div className="flex items-center justify-between mb-2">
                      <span className="text-sm font-medium text-gray-600">Loan Amount Range:</span>
                    </div>
                    <div className="text-lg font-bold text-green-600">
                      {formatCurrency(selectedLoanType.min_amount || 50000)} - {formatCurrency(selectedLoanType.max_amount || 2500000)}
                    </div>
                  </div>
                  
                  <div className="bg-blue-50 rounded-lg p-4">
                    <div className="flex items-center justify-between mb-2">
                      <span className="text-sm font-medium text-gray-600">Interest Rate:</span>
                    </div>
                    <div className="text-lg font-bold text-blue-600">
                      {selectedLoanType.interest_rate || 12}% per year
                    </div>
                  </div>
                  
                  <div className="bg-blue-50 rounded-lg p-4">
                    <div className="flex items-center justify-between mb-2">
                      <span className="text-sm font-medium text-gray-600">Interest Type:</span>
                    </div>
                    <div className="text-sm font-medium text-gray-800">
                      {selectedLoanType.interest_type || 'Fixed'}
                    </div>
                  </div>
                  
                  <div className="bg-blue-50 rounded-lg p-4">
                    <div className="flex items-center justify-between mb-2">
                      <span className="text-sm font-medium text-gray-600">Maximum Term:</span>
                    </div>
                    <div className="text-sm font-medium text-gray-800">
                      {selectedLoanType.max_loan_term || 60} {selectedLoanType.payment_frequency || 'months'}
                    </div>
                  </div>
                  
                  <div className="bg-yellow-50 rounded-lg p-4">
                    <div className="flex items-center justify-between mb-2">
                      <span className="text-sm font-medium text-gray-600">Late Payment Penalty:</span>
                    </div>
                    <div className="text-sm font-bold text-yellow-600">
                      {selectedLoanType.penalties || 2}%
                    </div>
                  </div>
                  
                  {selectedLoanType.description && (
                    <div className="bg-gray-50 rounded-lg p-4">
                      <div className="mb-2">
                        <span className="text-sm font-medium text-gray-600">Additional Information:</span>
                      </div>
                      <div className="text-sm text-gray-700">
                        {selectedLoanType.description}
                      </div>
                    </div>
                  )}
                </div>
              ) : (
                <div className="text-center py-8">
                  <div className="text-gray-400 mb-2">No loan type selected</div>
                  <div className="text-sm text-gray-500">Please select a loan type to view details</div>
                </div>
              )}
              
              {/* Loan Type Selection */}
              <div className="mt-6">
                <label className="block text-sm font-medium text-gray-700 mb-3">Select Loan Type:</label>
                <div className="space-y-2">
                  {loanTypes.map((loanType) => (
                    <button
                      key={loanType.id}
                      onClick={() => setSelectedLoanType(loanType)}
                      className={`w-full text-left p-3 rounded-lg border transition-colors ${
                        selectedLoanType?.id === loanType.id
                          ? 'bg-blue-100 border-blue-300 text-blue-900'
                          : 'bg-white border-gray-200 text-gray-700 hover:bg-gray-50'
                      }`}
                    >
                      <div className="font-medium">{loanType.name}</div>
                      <div className="text-sm text-gray-500">{loanType.interest_rate || 12}% interest</div>
                    </button>
                  ))}
                </div>
              </div>
            </div>
          </div>
          
          {/* Application Form */}
          <div className="lg:col-span-2">
            <div className="bg-white rounded-2xl shadow-lg border border-blue-200 p-8">
              <div className="mb-6">
                <h3 className="text-xl font-bold text-blue-900 mb-2">Loan Application Form</h3>
                <div className="w-12 h-1 bg-blue-600 rounded"></div>
              </div>
              
              <form onSubmit={handleSubmit} className="space-y-6">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">Branch</label>
                    <select 
                      value={formData.branch_id}
                      onChange={(e) => setFormData({...formData, branch_id: e.target.value})}
                      className="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      required
                    >
                      <option value="">Select Branch</option>
                      <option value="1">Main Branch</option>
                      <option value="2">City Center Branch</option>
                      <option value="3">Downtown Branch</option>
                    </select>
                  </div>
                  
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">Requested Amount</label>
                    <input
                      type="number"
                      value={formData.amount}
                      onChange={(e) => setFormData({...formData, amount: e.target.value})}
                      className="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      placeholder="Enter requested amount"
                      min={selectedLoanType?.min_amount || 50000}
                      max={selectedLoanType?.max_amount || 2500000}
                      required
                    />
                    {selectedLoanType && (
                      <small className="text-gray-500 mt-1 block">
                        Amount must be between {formatCurrency(selectedLoanType.min_amount || 50000)} and {formatCurrency(selectedLoanType.max_amount || 2500000)}
                      </small>
                    )}
                  </div>
                  
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">Referral Code</label>
                    <input
                      type="text"
                      value={formData.referral_code}
                      onChange={(e) => setFormData({...formData, referral_code: e.target.value})}
                      className="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      placeholder="Enter referral code (required)"
                      required
                    />
                    <small className="text-gray-500 mt-1 block">Please enter a valid referral code</small>
                  </div>
                  
                  <div>
                    <div className="bg-blue-50 border border-blue-200 rounded-lg p-4">
                      <h6 className="font-semibold text-blue-900 mb-2 flex items-center">
                        <svg className="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                          <path fillRule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clipRule="evenodd" />
                        </svg>
                        Loan Terms
                      </h6>
                      <p className="text-sm text-blue-800 mb-1">
                        <strong>Term:</strong> {selectedLoanType?.max_loan_term || 60} {selectedLoanType?.payment_frequency || 'months'}
                      </p>
                      <p className="text-sm text-blue-700">
                        <strong>Note:</strong> Loan terms are predefined for this loan type
                      </p>
                    </div>
                  </div>
                </div>
                
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">Purpose of Loan</label>
                  <textarea
                    value={formData.purpose_of_loan}
                    onChange={(e) => setFormData({...formData, purpose_of_loan: e.target.value})}
                    className="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    rows={3}
                    placeholder="Please describe the purpose of this loan"
                    required
                  />
                </div>
                
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">Additional Notes</label>
                  <textarea
                    value={formData.notes}
                    onChange={(e) => setFormData({...formData, notes: e.target.value})}
                    className="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    rows={2}
                    placeholder="Any additional information (optional)"
                  />
                </div>
                
                {/* Important Notice */}
                <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                  <div className="flex items-start">
                    <svg className="w-5 h-5 text-yellow-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                      <path fillRule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clipRule="evenodd" />
                    </svg>
                    <div>
                      <p className="text-sm text-yellow-800">
                        <strong>Important:</strong> Your loan application will be reviewed by our team. You will be notified of the decision via email and SMS.
                      </p>
                    </div>
                  </div>
                </div>
                
                <div className="flex justify-end space-x-4 pt-6">
                  <button
                    type="button"
                    className="px-8 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors font-medium"
                    onClick={() => window.history.back()}
                  >
                    Cancel
                  </button>
                  <button
                    type="submit"
                    disabled={isSubmitting}
                    className="px-8 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 font-medium flex items-center"
                  >
                    {isSubmitting ? (
                      <>
                        <svg className="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                          <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                          <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Submitting...
                      </>
                    ) : (
                      'Submit Application'
                    )}
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}

function LoanApplication() {
  const [formData, setFormData] = useState({
    loanType: '',
    amount: '2000000',
    tenure: '96',
    purpose: '',
    income: '',
    employment: '',
    documents: []
  })
  const [loanTypes, setLoanTypes] = useState<any[]>([])
  const [isSubmitting, setIsSubmitting] = useState(false)
  const [loading, setLoading] = useState(true)
  const [message, setMessage] = useState('')
  const [selectedLoanType, setSelectedLoanType] = useState<any>(null)
  const [calculatedEMI, setCalculatedEMI] = useState(0)
  const token = localStorage.getItem('auth_token')
  const navigate = useNavigate()
  
  // Note: Temporarily removed auth check for debugging
  
  // Tenure options in months (3-month intervals)
  const tenureOptions = [3, 6, 9, 12, 15, 18, 21, 24, 30, 36, 48, 60, 72, 84, 96]
  
  // Calculate EMI based on amount, tenure, and interest rate
  const calculateEMI = (principal: number, tenure: number, interestRate: number) => {
    if (!principal || !tenure || !interestRate) return 0
    
    const monthlyRate = interestRate / (12 * 100)
    const emi = (principal * monthlyRate * Math.pow(1 + monthlyRate, tenure)) / 
                (Math.pow(1 + monthlyRate, tenure) - 1)
    
    return Math.round(emi)
  }
  
  // Update EMI when amount, tenure, or loan type changes
  useEffect(() => {
    if (selectedLoanType && formData.amount && formData.tenure) {
      const emi = calculateEMI(
        parseFloat(formData.amount),
        parseInt(formData.tenure),
        selectedLoanType.interest_rate || 12
      )
      setCalculatedEMI(emi)
    }
  }, [formData.amount, formData.tenure, selectedLoanType])

  useEffect(() => {
    const fetchLoanTypes = async () => {
      try {
        const response = await fetch('/api/loan-types', {
          headers: {
            'Content-Type': 'application/json'
          }
        })
        
        // Handle response similar to other APIs
        const responseText = await response.text()
        let data
        try {
          data = JSON.parse(responseText)
        } catch (parseError) {
          const jsonMatch = responseText.match(/\{.*\}$/s)
          if (jsonMatch) {
            data = JSON.parse(jsonMatch[0])
          } else {
            throw new Error('No valid JSON found in response')
          }
        }
        
        if (response.ok && data.success) {
          setLoanTypes(data.loan_types || [])
          // Set first loan type as default if available
          if (data.loan_types && data.loan_types.length > 0) {
            const firstLoanType = data.loan_types[0]
            setFormData(prev => ({ ...prev, loanType: firstLoanType.id.toString() }))
            setSelectedLoanType(firstLoanType)
          }
        } else {
          // Fallback to default loan types if API fails
          const defaultTypes = [
            { id: 1, name: 'Personal Loan', description: 'For personal expenses', interest_rate: 12 },
            { id: 2, name: 'Home Loan', description: 'For home purchase', interest_rate: 8.5 },
            { id: 3, name: 'Car Loan', description: 'For vehicle purchase', interest_rate: 10 },
            { id: 4, name: 'Business Loan', description: 'For business needs', interest_rate: 14 }
          ]
          setLoanTypes(defaultTypes)
          setFormData(prev => ({ ...prev, loanType: '1' }))
          setSelectedLoanType(defaultTypes[0])
        }
      } catch (error) {
        console.error('Error fetching loan types:', error)
        // Fallback to default loan types
        const defaultTypes = [
          { id: 1, name: 'Personal Loan', description: 'For personal expenses', interest_rate: 12 },
          { id: 2, name: 'Home Loan', description: 'For home purchase', interest_rate: 8.5 },
          { id: 3, name: 'Car Loan', description: 'For vehicle purchase', interest_rate: 10 },
          { id: 4, name: 'Business Loan', description: 'For business needs', interest_rate: 14 }
        ]
        setLoanTypes(defaultTypes)
        setFormData(prev => ({ ...prev, loanType: '1' }))
        setSelectedLoanType(defaultTypes[0])
      } finally {
        setLoading(false)
      }
    }
    
    fetchLoanTypes()
  }, [])
  
  // Handle loan type change
  const handleLoanTypeChange = (loanTypeId: string) => {
    const loanType = loanTypes.find(lt => lt.id.toString() === loanTypeId)
    setFormData(prev => ({ ...prev, loanType: loanTypeId }))
    setSelectedLoanType(loanType)
  }
  
  // Format number with commas
  const formatNumber = (num: number) => {
    return num.toLocaleString('en-IN')
  }
  
  // Handle amount change
  const handleAmountChange = (value: string) => {
    // Remove commas and non-numeric characters except digits
    const numericValue = value.replace(/[^0-9]/g, '')
    setFormData(prev => ({ ...prev, amount: numericValue }))
  }

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    setIsSubmitting(true)
    setMessage('')

    try {
      // Get user data from localStorage
      const userData = JSON.parse(localStorage.getItem('user_data') || '{}')
      
      // Prepare data for the correct API endpoint
      const applicationData = {
        name: userData.name || 'Customer',
        email: userData.email || 'customer@example.com',
        phone: userData.phone || '1234567890',
        loan_type: formData.loanType,
        loan_amount: formData.amount,
        loan_purpose: formData.purpose,
        tenure: formData.tenure,
        income: formData.income,
        employment: formData.employment
      }

      const response = await fetch('/api/apply-loan', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(applicationData)
      })
      
      // Handle response similar to other APIs
      const responseText = await response.text()
      let data
      try {
        data = JSON.parse(responseText)
      } catch (parseError) {
        const jsonMatch = responseText.match(/\{.*\}$/s)
        if (jsonMatch) {
          data = JSON.parse(jsonMatch[0])
        } else {
          throw new Error('No valid JSON found in response')
        }
      }
      
      if (response.ok && data.success !== false) {
        setMessage('Loan application submitted successfully! We will review your application and get back to you soon.')
        setFormData({
          loanType: loanTypes.length > 0 ? loanTypes[0].id.toString() : '',
          amount: '',
          tenure: '',
          purpose: '',
          income: '',
          employment: '',
          documents: []
        })
      } else {
        setMessage(data.message || 'Failed to submit application')
      }
    } catch (error) {
      console.error('Application error:', error)
      setMessage('Network error. Please try again.')
    } finally {
      setIsSubmitting(false)
    }
  }

  // Temporarily disabled for debugging - Don't render if not authenticated
  /*
  if (!token) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-600"></div>
      </div>
    )
  }
  */

  return (
    <div className="min-h-screen bg-blue-50 p-6">
      <div className="max-w-4xl mx-auto">
        <div className="mb-8">
          <h1 className="text-3xl font-bold text-blue-900">Apply for Loan</h1>
          <p className="text-blue-700 mt-2">Calculate your EMI and apply for the perfect loan</p>
        </div>

        {message && (
          <div className={`p-4 rounded-lg mb-6 ${
            message.includes('successfully') ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'
          }`}>
            {message}
          </div>
        )}

        <div className="bg-white rounded-2xl shadow-xl p-8">
          {/* Loan Type Selection */}
          <div className="mb-8">
            <div className="flex items-center justify-between mb-4">
              <h2 className="text-2xl font-bold text-gray-900">
                {selectedLoanType?.name || 'Business Loan'}
              </h2>
              <div className="flex items-center space-x-2">
                <span className="text-yellow-500">★</span>
                <span className="font-semibold">4.8</span>
                <span className="text-gray-500">(1,629)</span>
              </div>
            </div>
            
            <p className="text-gray-600 mb-6">
              Loan from ₹2 lakh to ₹80 lakh | No Collateral | ROI starts @ {selectedLoanType?.interest_rate || 14}% p.a.
            </p>
            
            {/* Loan Type Tabs */}
            <div className="flex flex-wrap gap-2 mb-8">
              {loanTypes.map((loanType) => (
                <button
                  key={loanType.id}
                  onClick={() => handleLoanTypeChange(loanType.id.toString())}
                  className={`px-4 py-2 rounded-lg font-medium transition-colors ${
                    formData.loanType === loanType.id.toString()
                      ? 'bg-blue-600 text-white'
                      : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                  }`}
                >
                  {loanType.name}
                </button>
              ))}
            </div>
          </div>

          {/* Loan Amount */}
          <div className="mb-8">
            <label className="block text-lg font-semibold text-gray-900 mb-4">Loan Amount</label>
            <div className="bg-gray-100 rounded-lg p-4 mb-4">
              <div className="flex items-center">
                <span className="text-2xl font-bold text-gray-600 mr-2">₹</span>
                <input
                  type="text"
                  value={formatNumber(parseInt(formData.amount) || 0)}
                  onChange={(e) => handleAmountChange(e.target.value)}
                  className="text-4xl font-bold bg-transparent border-none outline-none flex-1 text-gray-900"
                  placeholder="20,00,000"
                />
              </div>
            </div>
            <p className="text-sm text-gray-600">
              Enter an amount between ₹2,00,000 & ₹75,00,000
            </p>
          </div>

          {/* Loan Tenure */}
          <div className="mb-8">
            <div className="flex items-center justify-between mb-4">
              <label className="text-lg font-semibold text-gray-900">
                Selected loan tenure: {formData.tenure} Months
              </label>
              <div className="w-6 h-6 bg-orange-100 rounded-full flex items-center justify-center">
                <span className="text-orange-600 text-sm">?</span>
              </div>
            </div>
            
            <div className="grid grid-cols-6 gap-3 mb-6">
              {tenureOptions.map((months) => (
                <button
                  key={months}
                  onClick={() => setFormData(prev => ({ ...prev, tenure: months.toString() }))}
                  className={`py-3 px-4 rounded-lg font-semibold transition-colors ${
                    formData.tenure === months.toString()
                      ? 'bg-blue-900 text-white'
                      : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                  }`}
                >
                  {months}
                </button>
              ))}
            </div>
          </div>

          {/* Loan Variant Selection */}
          <div className="mb-8">
            <div className="flex items-center justify-between mb-4">
              <label className="text-lg font-semibold text-gray-900">
                Selected loan variant: FLEXI TERM
              </label>
              <div className="w-6 h-6 bg-orange-100 rounded-full flex items-center justify-center">
                <span className="text-orange-600 text-sm">?</span>
              </div>
            </div>
            
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
              <div className="border-2 border-orange-400 rounded-lg p-4 bg-orange-50">
                <h3 className="font-bold text-gray-900 mb-2">FLEXI TERM</h3>
                <div className="text-2xl font-bold text-gray-900 mb-1">₹{formatNumber(calculatedEMI)}</div>
                <div className="text-sm text-gray-600 mb-4">Fixed EMIs</div>
              </div>
              
              <div className="border border-gray-200 rounded-lg p-4">
                <h3 className="font-bold text-gray-900 mb-2">FLEXI HYBRID TERM LOAN</h3>
                <div className="text-lg font-bold text-gray-900 mb-1">₹30,000</div>
                <div className="text-sm text-gray-600 mb-1">(24 Months)</div>
                <div className="text-sm text-gray-600 mb-1">Initial EMIs</div>
                <div className="text-lg font-bold text-gray-900 mb-1">₹45,616</div>
                <div className="text-sm text-gray-600">(72 Months)</div>
                <div className="text-sm text-gray-600">Subsequent EMIs</div>
              </div>
              
              <div className="border border-gray-200 rounded-lg p-4">
                <h3 className="font-bold text-gray-900 mb-2">TERM LOAN</h3>
                <div className="text-2xl font-bold text-gray-900 mb-1">₹{formatNumber(calculatedEMI)}</div>
                <div className="text-sm text-gray-600 mb-4">Fixed EMIs</div>
                <div className="text-sm text-gray-600">Full amount disbursed</div>
              </div>
            </div>
            
            <div className="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
              <p className="text-blue-800 font-medium">
                Fixed instalments | Multiple withdrawals | No part-prepayment charges
              </p>
            </div>
          </div>

          {/* EMI Display */}
          <div className="bg-gray-50 rounded-lg p-6 mb-8">
            <div className="text-center">
              <p className="text-lg text-gray-600 mb-2">
                Instalment for {formData.tenure} Months at {selectedLoanType?.interest_rate || 14}% p.a.
              </p>
              <div className="text-5xl font-bold text-gray-900">₹{formatNumber(calculatedEMI)}</div>
            </div>
          </div>

          {/* Additional Form Fields */}
          <form onSubmit={handleSubmit} className="space-y-6">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Monthly Income (₹)</label>
                <input
                  type="number"
                  value={formData.income}
                  onChange={(e) => setFormData({...formData, income: e.target.value})}
                  className="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  placeholder="Enter monthly income"
                  min="0"
                  required
                />
              </div>
              
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Employment Type</label>
                <select 
                  value={formData.employment}
                  onChange={(e) => setFormData({...formData, employment: e.target.value})}
                  className="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  required
                >
                  <option value="">Select employment type</option>
                  <option value="salaried">Salaried</option>
                  <option value="self-employed">Self Employed</option>
                  <option value="business">Business Owner</option>
                  <option value="freelancer">Freelancer</option>
                </select>
              </div>
            </div>
            
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">Purpose of Loan</label>
              <input
                type="text"
                value={formData.purpose}
                onChange={(e) => setFormData({...formData, purpose: e.target.value})}
                className="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Enter purpose of loan"
                required
              />
            </div>

            <div className="flex justify-end space-x-4 pt-6">
              <button
                type="button"
                className="px-8 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors font-medium"
                onClick={() => window.history.back()}
              >
                Cancel
              </button>
              <button
                type="submit"
                disabled={isSubmitting}
                className="px-8 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 font-medium"
              >
                {isSubmitting ? 'Submitting...' : 'Apply for Loan'}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  )
}

function LoanDetails() {
  const [loanDetails, setLoanDetails] = useState<any>(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const [activeTab, setActiveTab] = useState('overview')
  const token = localStorage.getItem('auth_token')
  
  // Get loan ID from URL
  const loanId = window.location.pathname.split('/').pop()

  useEffect(() => {
    const fetchLoanDetails = async () => {
      try {
        setLoading(true)
        const response = await fetch(`/api/customer/loan-details/${loanId}`, {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
          }
        })
        
        // Handle response similar to other APIs
        const responseText = await response.text()
        let data
        try {
          data = JSON.parse(responseText)
        } catch (parseError) {
          const jsonMatch = responseText.match(/\{.*\}$/s)
          if (jsonMatch) {
            data = JSON.parse(jsonMatch[0])
          } else {
            throw new Error('No valid JSON found in response')
          }
        }
        
        if (response.ok && data.success) {
          setLoanDetails(data)
          setError('')
        } else {
          setError('Failed to load loan details')
        }
      } catch (error) {
        console.error('Error fetching loan details:', error)
        setError('Network error. Please try again.')
      } finally {
        setLoading(false)
      }
    }
    
    if (loanId) {
      fetchLoanDetails()
    }
  }, [token, loanId])

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-600"></div>
      </div>
    )
  }

  if (error) {
    return (
      <div className="min-h-screen bg-gray-50 p-6">
        <div className="max-w-4xl mx-auto">
          <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            {error}
          </div>
          <button 
            onClick={() => window.history.back()}
            className="mt-4 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
          >
            Go Back
          </button>
        </div>
      </div>
    )
  }

  const loan = loanDetails?.loan_details
  const schedules = loanDetails?.repayment_schedules || []
  const transactions = loanDetails?.transactions || []
  const summary = loanDetails?.summary || {}

  return (
    <div className="min-h-screen bg-gray-50 p-6">
      <div className="max-w-6xl mx-auto">
        {/* Header */}
        <div className="mb-8">
          <div className="flex items-center justify-between">
            <div>
              <h1 className="text-3xl font-bold text-gray-900">Loan Details</h1>
              <p className="text-gray-600 mt-2">{loan?.loan_id} - {loan?.type}</p>
            </div>
            <button 
              onClick={() => window.history.back()}
              className="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700"
            >
              Back
            </button>
          </div>
        </div>

        {/* Loan Overview Cards */}
        <div className="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
          <div className="bg-white p-6 rounded-lg shadow-md">
            <h3 className="text-sm font-medium text-gray-500">Loan Amount</h3>
            <p className="text-2xl font-bold text-gray-900">₹{loan?.amount?.toLocaleString()}</p>
          </div>
          <div className="bg-white p-6 rounded-lg shadow-md">
            <h3 className="text-sm font-medium text-gray-500">Status</h3>
            <p className={`text-2xl font-bold ${
              loan?.status === 'Approved' ? 'text-green-600' : 
              loan?.status === 'Pending' ? 'text-yellow-600' : 'text-gray-900'
            }`}>{loan?.status}</p>
          </div>
          <div className="bg-white p-6 rounded-lg shadow-md">
            <h3 className="text-sm font-medium text-gray-500">Total Paid</h3>
            <p className="text-2xl font-bold text-green-600">₹{loan?.total_paid?.toLocaleString()}</p>
          </div>
          <div className="bg-white p-6 rounded-lg shadow-md">
            <h3 className="text-sm font-medium text-gray-500">Pending Amount</h3>
            <p className="text-2xl font-bold text-red-600">₹{loan?.pending_amount?.toLocaleString()}</p>
          </div>
        </div>

        {/* Tabs */}
        <div className="bg-white rounded-lg shadow-md">
          <div className="border-b border-gray-200">
            <nav className="-mb-px flex space-x-8 px-6">
              <button
                onClick={() => setActiveTab('overview')}
                className={`py-4 px-1 border-b-2 font-medium text-sm ${
                  activeTab === 'overview'
                    ? 'border-blue-500 text-blue-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                }`}
              >
                Overview
              </button>
              <button
                onClick={() => setActiveTab('schedule')}
                className={`py-4 px-1 border-b-2 font-medium text-sm ${
                  activeTab === 'schedule'
                    ? 'border-blue-500 text-blue-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                }`}
              >
                Repayment Schedule ({schedules.length})
              </button>
              <button
                onClick={() => setActiveTab('transactions')}
                className={`py-4 px-1 border-b-2 font-medium text-sm ${
                  activeTab === 'transactions'
                    ? 'border-blue-500 text-blue-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                }`}
              >
                Transactions ({transactions.length})
              </button>
            </nav>
          </div>

          <div className="p-6">
            {activeTab === 'overview' && (
              <div className="space-y-6">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <h3 className="text-lg font-medium text-gray-900 mb-4">Loan Information</h3>
                    <dl className="space-y-3">
                      <div>
                        <dt className="text-sm font-medium text-gray-500">Purpose</dt>
                        <dd className="text-sm text-gray-900">{loan?.purpose || 'Not specified'}</dd>
                      </div>
                      <div>
                        <dt className="text-sm font-medium text-gray-500">Start Date</dt>
                        <dd className="text-sm text-gray-900">{loan?.start_date || 'Not set'}</dd>
                      </div>
                      <div>
                        <dt className="text-sm font-medium text-gray-500">Due Date</dt>
                        <dd className="text-sm text-gray-900">{loan?.due_date || 'Not set'}</dd>
                      </div>
                      <div>
                        <dt className="text-sm font-medium text-gray-500">Terms</dt>
                        <dd className="text-sm text-gray-900">{loan?.terms} {loan?.term_period}</dd>
                      </div>
                    </dl>
                  </div>
                  <div>
                    <h3 className="text-lg font-medium text-gray-900 mb-4">Payment Summary</h3>
                    <dl className="space-y-3">
                      <div>
                        <dt className="text-sm font-medium text-gray-500">Total Scheduled</dt>
                        <dd className="text-sm text-gray-900">₹{loan?.total_scheduled?.toLocaleString()}</dd>
                      </div>
                      <div>
                        <dt className="text-sm font-medium text-gray-500">Paid Installments</dt>
                        <dd className="text-sm text-gray-900">{summary.paid_schedules} of {summary.total_schedules}</dd>
                      </div>
                      <div>
                        <dt className="text-sm font-medium text-gray-500">Next Payment</dt>
                        <dd className="text-sm text-gray-900">
                          {loan?.next_payment ? 
                            `₹${loan.next_payment.amount?.toLocaleString()} on ${new Date(loan.next_payment.due_date).toLocaleDateString()}` : 
                            'No pending payments'
                          }
                        </dd>
                      </div>
                    </dl>
                  </div>
                </div>
              </div>
            )}

            {activeTab === 'schedule' && (
              <div className="overflow-x-auto">
                <table className="w-full">
                  <thead className="bg-gray-50">
                    <tr>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Principal</th>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Interest</th>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                  </thead>
                  <tbody className="bg-white divide-y divide-gray-200">
                    {schedules.map((schedule: any, index: number) => (
                      <tr key={index}>
                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                          {new Date(schedule.due_date).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })}
                        </td>
                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₹{schedule.principal_amount?.toLocaleString()}</td>
                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₹{schedule.interest?.toLocaleString()}</td>
                        <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">₹{schedule.total_amount?.toLocaleString()}</td>
                        <td className="px-6 py-4 whitespace-nowrap">
                          <span className={`px-2 py-1 rounded text-xs font-medium ${
                            schedule.status === 'Paid' ? 'bg-green-100 text-green-800' :
                            'bg-yellow-100 text-yellow-800'
                          }`}>
                            {schedule.status}
                          </span>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            )}

            {activeTab === 'transactions' && (
              <div className="overflow-x-auto">
                {transactions.length > 0 ? (
                  <table className="w-full">
                    <thead className="bg-gray-50">
                      <tr>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Date</th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Principal</th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Interest</th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                      </tr>
                    </thead>
                    <tbody className="bg-white divide-y divide-gray-200">
                      {transactions.map((transaction: any, index: number) => (
                        <tr key={index}>
                          <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {new Date(transaction.payment_date).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })}
                          </td>
                          <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">₹{transaction.amount?.toLocaleString()}</td>
                          <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₹{transaction.principal?.toLocaleString()}</td>
                          <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₹{transaction.interest?.toLocaleString()}</td>
                          <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{transaction.payment_method}</td>
                          <td className="px-6 py-4 whitespace-nowrap">
                            <span className={`px-2 py-1 rounded text-xs font-medium ${
                              transaction.status === 'Paid' ? 'bg-green-100 text-green-800' :
                              'bg-yellow-100 text-yellow-800'
                            }`}>
                              {transaction.status}
                            </span>
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                ) : (
                  <div className="text-center py-8">
                    <p className="text-gray-500">No transactions found for this loan.</p>
                  </div>
                )}
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  )
}

// Upcoming Installments Component
function UpcomingInstallments() {
  const [installments, setInstallments] = useState<any[]>([])
  const [loading, setLoading] = useState(true)
  const [paymentLoading, setPaymentLoading] = useState<number | null>(null)
  const token = localStorage.getItem('auth_token')

  useEffect(() => {
    const fetchInstallments = async () => {
      try {
        const response = await fetch('/api/customer/upcoming-installments', {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
          }
        })
        
        // Handle response similar to other APIs
        const responseText = await response.text()
        let data
        try {
          data = JSON.parse(responseText)
        } catch (parseError) {
          const jsonMatch = responseText.match(/\{.*\}$/s)
          if (jsonMatch) {
            data = JSON.parse(jsonMatch[0])
          } else {
            throw new Error('No valid JSON found in response')
          }
        }
        
        if (response.ok && data.success) {
          setInstallments(data.installments || [])
        } else {
          setInstallments([])
        }
      } catch (error) {
        console.error('Error fetching installments:', error)
        setInstallments([])
      } finally {
        setLoading(false)
      }
    }
    fetchInstallments()
  }, [token])

  const handlePayment = async (installment: any) => {
    setPaymentLoading(installment.id)
    try {
      const response = await fetch('/api/customer/pay-installment', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          schedule_id: installment.id,
          amount: installment.amount,
          payment_method: 'UPI'
        })
      })
      
      const data = await response.json()
      
      if (response.ok) {
        alert(`Payment successful! Transaction ID: ${data.transaction_id}`)
        // Refresh installments
        window.location.reload()
      } else {
        alert(data.message || 'Payment failed')
      }
    } catch (error) {
      console.error('Payment error:', error)
      alert('Payment failed. Please try again.')
    } finally {
      setPaymentLoading(null)
    }
  }

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-600"></div>
      </div>
    )
  }

  return (
    <div className="min-h-screen bg-blue-50 p-6">
      <div className="max-w-4xl mx-auto">
        <div className="mb-8">
          <h1 className="text-3xl font-bold text-blue-900">Next Month's EMI Installments</h1>
          <p className="text-blue-700 mt-2">Upcoming EMI payments for next month - Pay on time to maintain a good credit score</p>
        </div>

        <div className="space-y-4">
          {installments.map((installment) => (
            <div key={installment.id} className="bg-white rounded-lg shadow-lg border border-blue-200 p-6">
              <div className="flex items-center justify-between">
                <div className="flex-1">
                  <div className="flex items-center space-x-4">
                    <div className="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                      <svg className="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v2a2 2 0 002 2z" />
                      </svg>
                    </div>
                    <div>
                      <h3 className="text-lg font-semibold text-blue-900">{installment.loan_type}</h3>
                      <p className="text-sm text-blue-600">Loan ID: {installment.loan_id}</p>
                      <p className="text-sm text-blue-600">EMI #{installment.installment_number}</p>
                    </div>
                  </div>
                </div>
                
                <div className="text-center">
                  <p className="text-2xl font-bold text-blue-900">₹{installment.amount.toLocaleString()}</p>
                  <p className="text-sm text-blue-600">Due: {new Date(installment.due_date).toLocaleDateString()}</p>
                  <p className={`text-sm font-medium ${
                    installment.days_remaining <= 7 ? 'text-red-600' :
                    installment.days_remaining <= 15 ? 'text-orange-600' :
                    'text-blue-600'
                  }`}>
                    {installment.days_remaining > 0 ? `${installment.days_remaining} days left` : 'Overdue'}
                  </p>
                </div>
                
                <div className="ml-6">
                  <button
                    onClick={() => handlePayment(installment)}
                    disabled={paymentLoading === installment.id}
                    className="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors shadow-md"
                  >
                    {paymentLoading === installment.id ? 'Processing...' : 'Pay Now'}
                  </button>
                </div>
              </div>
            </div>
          ))}
        </div>

        {installments.length === 0 && (
          <div className="text-center py-12">
            <div className="w-24 h-24 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <svg className="w-12 h-12 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <h3 className="text-lg font-medium text-blue-900 mb-2">All caught up!</h3>
            <p className="text-blue-600">You have no upcoming EMI installments for next month.</p>
          </div>
        )}
      </div>
    </div>
  )
}

// EMI Calculator Component
function EMICalculator() {
  const [formData, setFormData] = useState({
    amount: '',
    interestRate: '',
    tenure: ''
  })
  const [result, setResult] = useState<any>(null)
  const [loading, setLoading] = useState(false)

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) => {
    const { name, value } = e.target
    setFormData(prev => ({ ...prev, [name]: value }))
  }

  const calculateEMI = async (e: React.FormEvent) => {
    e.preventDefault()
    setLoading(true)
    
    try {
      const response = await fetch('/api/customer/calculate-emi', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          amount: parseFloat(formData.amount),
          interest_rate: parseFloat(formData.interestRate),
          tenure: parseInt(formData.tenure)
        })
      })
      
      const data = await response.json()
      
      if (response.ok) {
        setResult(data)
      } else {
        alert(data.message || 'Calculation failed')
      }
    } catch (error) {
      console.error('Calculation error:', error)
      alert('Calculation failed. Please try again.')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="min-h-screen bg-gray-50 p-6">
      <div className="max-w-4xl mx-auto">
        <div className="mb-8">
          <h1 className="text-3xl font-bold text-gray-900">EMI Calculator</h1>
          <p className="text-gray-600 mt-2">Calculate your monthly installments before applying for a loan</p>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
          {/* Calculator Form */}
          <div className="bg-white rounded-lg shadow-md p-6">
            <h2 className="text-xl font-semibold text-gray-900 mb-6">Loan Details</h2>
            
            <form onSubmit={calculateEMI} className="space-y-6">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Loan Amount (₹)</label>
                <input
                  type="number"
                  name="amount"
                  value={formData.amount}
                  onChange={handleInputChange}
                  className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  placeholder="Enter loan amount"
                  required
                />
              </div>
              
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Interest Rate (% per annum)</label>
                <input
                  type="number"
                  step="0.1"
                  name="interestRate"
                  value={formData.interestRate}
                  onChange={handleInputChange}
                  className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  placeholder="Enter interest rate"
                  required
                />
              </div>
              
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Loan Tenure (months)</label>
                <select
                  name="tenure"
                  value={formData.tenure}
                  onChange={handleInputChange}
                  className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  required
                >
                  <option value="">Select tenure</option>
                  <option value="12">12 months (1 year)</option>
                  <option value="24">24 months (2 years)</option>
                  <option value="36">36 months (3 years)</option>
                  <option value="48">48 months (4 years)</option>
                  <option value="60">60 months (5 years)</option>
                  <option value="84">84 months (7 years)</option>
                  <option value="120">120 months (10 years)</option>
                </select>
              </div>
              
              <button
                type="submit"
                disabled={loading}
                className="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors"
              >
                {loading ? 'Calculating...' : 'Calculate EMI'}
              </button>
            </form>
          </div>

          {/* Results */}
          <div className="bg-white rounded-lg shadow-md p-6">
            <h2 className="text-xl font-semibold text-gray-900 mb-6">Calculation Results</h2>
            
            {result ? (
              <div className="space-y-4">
                <div className="bg-blue-50 rounded-lg p-4">
                  <p className="text-sm text-blue-600 font-medium">Monthly EMI</p>
                  <p className="text-3xl font-bold text-blue-900">₹{result.emi.toLocaleString()}</p>
                </div>
                
                <div className="grid grid-cols-2 gap-4">
                  <div className="bg-gray-50 rounded-lg p-4">
                    <p className="text-sm text-gray-600">Principal Amount</p>
                    <p className="text-xl font-semibold text-gray-900">₹{result.principal.toLocaleString()}</p>
                  </div>
                  
                  <div className="bg-gray-50 rounded-lg p-4">
                    <p className="text-sm text-gray-600">Total Interest</p>
                    <p className="text-xl font-semibold text-gray-900">₹{result.total_interest.toLocaleString()}</p>
                  </div>
                  
                  <div className="bg-gray-50 rounded-lg p-4 col-span-2">
                    <p className="text-sm text-gray-600">Total Amount Payable</p>
                    <p className="text-xl font-semibold text-gray-900">₹{result.total_amount.toLocaleString()}</p>
                  </div>
                </div>
                
                <div className="mt-6">
                  <a href="/loan-application" className="w-full bg-green-600 text-white py-3 rounded-lg hover:bg-green-700 transition-colors block text-center">
                    Apply for This Loan
                  </a>
                </div>
              </div>
            ) : (
              <div className="text-center py-12">
                <div className="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                  <svg className="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                  </svg>
                </div>
                <p className="text-gray-600">Enter loan details to calculate your EMI</p>
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  )
}

// Payment Tracking Component
function PaymentTracking() {
  const [payments, setPayments] = useState<any[]>([])
  const [filters, setFilters] = useState({
    status: 'all',
    dateRange: '30',
    loanType: 'all'
  })
  const [loading, setLoading] = useState(true)
  const token = localStorage.getItem('auth_token')

  useEffect(() => {
    const fetchPayments = async () => {
      try {
        setLoading(true)
        const response = await fetch('/api/customer/repayment-schedule', {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
          }
        })
        
        // Handle response similar to other APIs
        const responseText = await response.text()
        let data
        try {
          data = JSON.parse(responseText)
        } catch (parseError) {
          const jsonMatch = responseText.match(/\{.*\}$/s)
          if (jsonMatch) {
            data = JSON.parse(jsonMatch[0])
          } else {
            throw new Error('No valid JSON found in response')
          }
        }
        
        if (response.ok && data.success) {
          // Transform repayment schedule data to payment tracking format
          const transformedPayments = data.repayment_schedules.map((schedule: any, index: number) => ({
            id: index + 1,
            date: schedule.payment_date,
            amount: schedule.total_amount,
            principal: schedule.principal_amount,
            interest: schedule.interest,
            type: 'EMI',
            status: schedule.status.toLowerCase() === 'paid' ? 'completed' : 'pending',
            method: 'Bank Transfer',
            reference: `${schedule.loan_no}-${index + 1}`,
            loan_no: schedule.loan_no,
            loan_type: schedule.loan_type || 'Personal Loan',
            loan_purpose: schedule.loan_purpose
          }))
          setPayments(transformedPayments)
        } else {
          setPayments([])
        }
      } catch (error) {
        console.error('Error fetching payments:', error)
        setPayments([])
      } finally {
        setLoading(false)
      }
    }
    fetchPayments()
  }, [token, filters])

  const handleFilterChange = (filterType: string, value: string) => {
    setFilters(prev => ({ ...prev, [filterType]: value }))
  }

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-600"></div>
      </div>
    )
  }

  return (
    <div className="min-h-screen bg-gray-50 p-6">
      <div className="max-w-6xl mx-auto">
        <div className="mb-8">
          <h1 className="text-3xl font-bold text-gray-900">Payment History</h1>
          <p className="text-gray-600 mt-2">Track all your loan payments and transactions</p>
        </div>

        {/* Filters */}
        <div className="bg-white rounded-lg shadow-md p-6 mb-6">
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">Status</label>
              <select
                value={filters.status}
                onChange={(e) => handleFilterChange('status', e.target.value)}
                className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="all">All Status</option>
                <option value="completed">Completed</option>
                <option value="pending">Pending</option>
                <option value="failed">Failed</option>
              </select>
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
              <select
                value={filters.dateRange}
                onChange={(e) => handleFilterChange('dateRange', e.target.value)}
                className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="30">Last 30 days</option>
                <option value="90">Last 3 months</option>
                <option value="180">Last 6 months</option>
                <option value="365">Last year</option>
              </select>
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">Loan Type</label>
              <select
                value={filters.loanType}
                onChange={(e) => handleFilterChange('loanType', e.target.value)}
                className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="all">All Loans</option>
                <option value="personal">Personal Loan</option>
                <option value="home">Home Loan</option>
                <option value="car">Car Loan</option>
              </select>
            </div>
          </div>
        </div>

        {/* Payment Summary Cards */}
        <div className="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
          <div className="bg-white rounded-lg shadow-md p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-gray-600">Total Paid</p>
                <p className="text-2xl font-bold text-green-600">₹{payments.filter(p => p.status === 'completed').reduce((sum, p) => sum + p.amount, 0).toLocaleString()}</p>
              </div>
              <div className="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <svg className="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                </svg>
              </div>
            </div>
          </div>
          
          <div className="bg-white rounded-lg shadow-md p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-gray-600">Successful Payments</p>
                <p className="text-2xl font-bold text-blue-600">{payments.filter(p => p.status === 'completed').length}</p>
              </div>
              <div className="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <svg className="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
              </div>
            </div>
          </div>
          
          <div className="bg-white rounded-lg shadow-md p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-gray-600">Failed Payments</p>
                <p className="text-2xl font-bold text-red-600">{payments.filter(p => p.status === 'failed').length}</p>
              </div>
              <div className="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                <svg className="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                </svg>
              </div>
            </div>
          </div>
          
          <div className="bg-white rounded-lg shadow-md p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-gray-600">Average Payment</p>
                <p className="text-2xl font-bold text-purple-600">₹{payments.length > 0 ? Math.round(payments.reduce((sum, p) => sum + p.amount, 0) / payments.length).toLocaleString() : 0}</p>
              </div>
              <div className="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                <svg className="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
              </div>
            </div>
          </div>
        </div>

        {/* Payment History Table */}
        <div className="bg-white rounded-lg shadow-md overflow-hidden">
          <div className="p-6 border-b">
            <h2 className="text-xl font-semibold text-gray-900">Payment History</h2>
          </div>
          
          <div className="overflow-x-auto">
            <table className="w-full">
              <thead className="bg-gray-50">
                <tr>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                </tr>
              </thead>
              <tbody className="bg-white divide-y divide-gray-200">
                {payments.map((payment) => (
                  <tr key={payment.id}>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{payment.date}</td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">₹{payment.amount.toLocaleString()}</td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{payment.type}</td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{payment.method}</td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <span className={`px-2 py-1 rounded-full text-xs font-medium ${
                        payment.status === 'completed' ? 'bg-green-100 text-green-800' :
                        payment.status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                        'bg-red-100 text-red-800'
                      }`}>
                        {payment.status}
                      </span>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{payment.reference}</td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm">
                      <button className="text-blue-600 hover:text-blue-800 font-medium">
                        View Receipt
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  )
}

// Simple Login Component
function Login() {
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [isLoading, setIsLoading] = useState(false)
  const [message, setMessage] = useState('')

  const handleLogin = async (e: React.FormEvent) => {
    e.preventDefault()
    setIsLoading(true)
    setMessage('')

    try {
      const response = await fetch('/api/login', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify({ email, password }),
      })

      // Get response as text first to handle mixed HTML/JSON
      const responseText = await response.text()
      
      // Extract JSON from response (handle PHP deprecation warnings)
      let data
      try {
        // Try to parse as pure JSON first
        data = JSON.parse(responseText)
      } catch (parseError) {
        // If that fails, extract JSON from mixed HTML/JSON response
        const jsonMatch = responseText.match(/\{.*\}$/s)
        if (jsonMatch) {
          data = JSON.parse(jsonMatch[0])
        } else {
          throw new Error('No valid JSON found in response')
        }
      }
      
      if (response.ok && data.success) {
        setMessage('Login successful!')
        // Store token and user data
        localStorage.setItem('auth_token', data.token)
        localStorage.setItem('user_data', JSON.stringify(data.user))
        window.location.href = '/pwa/dashboard'
      } else {
        setMessage(data.message || 'Login failed')
      }
    } catch (error) {
      console.error('Login error:', error)
      setMessage('Network error. Please try again.')
    } finally {
      setIsLoading(false)
    }
  }

  return (
    <div className="min-h-screen bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center px-4">
      <div className="bg-white p-8 rounded-xl shadow-2xl w-full max-w-md">
        <div className="text-center mb-8">
          <img src="/upload/logo/logo_mbc.png" alt="MBC Finance" className="h-20 w-auto mx-auto mb-4" />
          <h1 className="text-2xl font-bold text-gray-900">Welcome Back</h1>
          <p className="text-gray-600 mt-2">Sign in to your account</p>
        </div>

        <form onSubmit={handleLogin} className="space-y-6">
          <div>
            <label htmlFor="email" className="block text-sm font-medium text-gray-700 mb-2">
              Email Address
            </label>
            <input
              type="email"
              id="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="Enter your email"
              required
            />
          </div>

          <div>
            <label htmlFor="password" className="block text-sm font-medium text-gray-700 mb-2">
              Password
            </label>
            <input
              type="password"
              id="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="Enter your password"
              required
            />
          </div>

          {message && (
            <div className={`p-3 rounded-lg text-sm ${
              message.includes('successful') 
                ? 'bg-green-100 text-green-700 border border-green-200' 
                : 'bg-red-100 text-red-700 border border-red-200'
            }`}>
              {message}
            </div>
          )}

          <button
            type="submit"
            disabled={isLoading}
            className="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            {isLoading ? 'Signing In...' : 'Sign In'}
          </button>
        </form>

        <div className="mt-6 text-center">
          <p className="text-sm text-gray-600">
            Don't have an account?{' '}
            <a href="/register" className="text-blue-600 hover:text-blue-700 font-medium">
              Sign up
            </a>
          </p>
          <a href="/" className="text-sm text-gray-500 hover:text-gray-700 mt-2 inline-block">
            ← Back to Home
          </a>
        </div>
      </div>
    </div>
  )
}

// Enhanced Dashboard Component with Loan Management Features
function Dashboard() {
  const token = localStorage.getItem('auth_token')
  const userData = JSON.parse(localStorage.getItem('user_data') || '{}')
  const [dashboardData, setDashboardData] = useState<any>(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  
  if (!token) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gray-50">
        <div className="text-center">
          <p className="text-gray-600 mb-4">Please log in to access your dashboard</p>
          <a href="/login" className="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
            Go to Login
          </a>
        </div>
      </div>
    )
  }

  // Fetch dashboard data from API
  useEffect(() => {
    const fetchDashboardData = async () => {
      try {
        setLoading(true)
        const response = await fetch('/api/dashboard', {
          method: 'GET',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': `Bearer ${token}`,
          },
        })

        // Handle response similar to login
        const responseText = await response.text()
        let data
        try {
          data = JSON.parse(responseText)
        } catch (parseError) {
          const jsonMatch = responseText.match(/\{.*\}$/s)
          if (jsonMatch) {
            data = JSON.parse(jsonMatch[0])
          } else {
            throw new Error('No valid JSON found in response')
          }
        }

        if (response.ok && data) {
          setDashboardData(data)
        } else {
          setError('Failed to load dashboard data')
        }
      } catch (error) {
        console.error('Dashboard fetch error:', error)
        setError('Network error. Please try again.')
      } finally {
        setLoading(false)
      }
    }

    fetchDashboardData()
  }, [token])

  const handleLogout = () => {
    localStorage.removeItem('auth_token')
    localStorage.removeItem('user_data')
    window.location.href = '/'
  }

  // Use API data or fallback to mock data
  const loanSummary = (dashboardData && dashboardData.summary) ? dashboardData.summary : {
    totalLoans: 0,
    activeLoans: 0,
    totalAmount: 0,
    pendingAmount: 0,
    nextPayment: '2025-09-15',
    nextPaymentAmount: 0
  }

  const recentActivity = (dashboardData && dashboardData.recentActivity) ? dashboardData.recentActivity : []

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-100">
        <div className="text-center">
          <div className="w-16 h-16 border-4 border-blue-600 border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
          <p className="text-gray-600">Loading your dashboard...</p>
        </div>
      </div>
    )
  }

  if (error) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-100">
        <div className="text-center">
          <div className="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg className="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <p className="text-red-600 mb-4">{error}</p>
          <button 
            onClick={() => window.location.reload()} 
            className="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors"
          >
            Retry
          </button>
        </div>
      </div>
    )
  }

  return (
    <div className="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
      {/* Header */}
      <header className="bg-white shadow-sm border-b border-gray-200">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between items-center h-16">
            <div className="flex items-center">
              <img src="/upload/logo/logo_mbc.png" alt="MBC Finance" className="h-12 w-auto" />
            </div>
            <div className="flex items-center space-x-4">
              <div className="text-right">
                <p className="text-sm font-medium text-gray-900">{userData.name || 'User'}</p>
                <p className="text-xs text-gray-500">{userData.email}</p>
              </div>
              <button
                onClick={handleLogout}
                className="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors text-sm"
              >
                Logout
              </button>
            </div>
          </div>
        </div>
      </header>
      
      <main className="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        {/* Welcome Section */}
        <div className="mb-8">
          <h2 className="text-2xl font-bold text-gray-900 mb-2">Welcome back, {userData.name || 'User'}!</h2>
          <p className="text-gray-600">Manage your loans and payments with ease</p>
        </div>

        {/* Upcoming Installments Banner */}
        <div className="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 mb-8 text-white">
          <div className="flex items-center justify-between">
            <div>
              <h3 className="text-xl font-bold mb-2">⏰ Next Payment Due</h3>
              <p className="text-blue-100 mb-4">Your next EMI of ₹5,000 is due on {new Date(Date.now() + 15 * 24 * 60 * 60 * 1000).toLocaleDateString()}</p>
              <button className="inline-flex items-center px-6 py-3 bg-white text-blue-600 font-semibold rounded-lg hover:bg-blue-50 transition-colors">
                <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v2a2 2 0 002 2z" />
                </svg>
                Pay Now
              </button>
            </div>
            <div className="hidden md:block">
              <svg className="w-24 h-24 text-white opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
        </div>

        {/* Loan Summary Cards */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <div className="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-600">Total Loans</p>
                <p className="text-2xl font-bold text-gray-900">{loanSummary.totalLoans}</p>
              </div>
              <div className="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <svg className="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
              </div>
            </div>
          </div>
          
          <div className="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-600">Active Loans</p>
                <p className="text-2xl font-bold text-green-600">{loanSummary.activeLoans}</p>
              </div>
              <div className="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <svg className="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
          </div>
          
          <div className="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-600">Total Amount</p>
                <p className="text-2xl font-bold text-gray-900">₹{loanSummary.totalAmount.toLocaleString()}</p>
              </div>
              <div className="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                <svg className="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                </svg>
              </div>
            </div>
          </div>
          
          <div className="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-600">Pending Amount</p>
                <p className="text-2xl font-bold text-orange-600">₹{loanSummary.pendingAmount.toLocaleString()}</p>
              </div>
              <div className="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                <svg className="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
          </div>
        </div>

        {/* Quick Actions */}
        <div className="mb-8">
          <h3 className="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
          <div className="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <a href="/pwa/loan-application" className="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:shadow-md transition-shadow group block">
              <div className="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mx-auto mb-3 group-hover:bg-blue-200 transition-colors">
                <svg className="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
              </div>
              <p className="text-sm font-medium text-gray-900 text-center">Apply Loan</p>
            </a>
            
            <a href="/upcoming-installments" className="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:shadow-md transition-shadow group block">
              <div className="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mx-auto mb-3 group-hover:bg-green-200 transition-colors">
                <svg className="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v2a2 2 0 002 2z" />
                </svg>
              </div>
              <p className="text-sm font-medium text-gray-900 text-center">Pay EMI</p>
            </a>
            
            <a href="/pwa/loan-overview" className="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:shadow-md transition-shadow group block">
              <div className="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mx-auto mb-3 group-hover:bg-purple-200 transition-colors">
                <svg className="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
              </div>
              <p className="text-sm font-medium text-gray-900 text-center">My Loans</p>
            </a>
            
            <a href="/repayment-schedule" className="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:shadow-md transition-shadow group block">
              <div className="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center mx-auto mb-3 group-hover:bg-yellow-200 transition-colors">
                <svg className="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>
              <p className="text-sm font-medium text-gray-900 text-center">Schedule</p>
            </a>
            
            <a href="/payment-tracking" className="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:shadow-md transition-shadow group block">
              <div className="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center mx-auto mb-3 group-hover:bg-indigo-200 transition-colors">
                <svg className="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
              </div>
              <p className="text-sm font-medium text-gray-900 text-center">Transactions</p>
            </a>
            
            <a href="/emi-calculator" className="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:shadow-md transition-shadow group block">
              <div className="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mx-auto mb-3 group-hover:bg-red-200 transition-colors">
                <svg className="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
              </div>
              <p className="text-sm font-medium text-gray-900 text-center">Calculator</p>
            </a>
          </div>
        </div>

        {/* Recent Activity & Next Payment */}
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
          {/* Next Payment */}
          <div className="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <h3 className="text-lg font-semibold text-gray-900 mb-4">Next Payment Due</h3>
            <div className="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-4">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-gray-600">Due Date</p>
                  <p className="text-lg font-semibold text-gray-900">{loanSummary.nextPayment}</p>
                </div>
                <div className="text-right">
                  <p className="text-sm text-gray-600">Amount</p>
                  <p className="text-lg font-semibold text-blue-600">₹{loanSummary.nextPaymentAmount.toLocaleString()}</p>
                </div>
              </div>
              <button className="w-full mt-4 bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors">
                Pay Now
              </button>
            </div>
          </div>
          
          {/* Recent Activity */}
           <div className="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
             <h3 className="text-lg font-semibold text-gray-900 mb-4">Recent Activity</h3>
             <div className="space-y-4">
               {recentActivity.length > 0 ? (
                 recentActivity.map((activity: any, index: number) => {
                   const getActivityIcon = (type: string) => {
                     switch (type) {
                       case 'payment':
                         return (
                           <div className="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                             <svg className="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                               <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                             </svg>
                           </div>
                         )
                       case 'approval':
                         return (
                           <div className="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                             <svg className="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                               <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                             </svg>
                           </div>
                         )
                       case 'reminder':
                         return (
                           <div className="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                             <svg className="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                               <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                             </svg>
                           </div>
                         )
                       default:
                         return (
                           <div className="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                             <svg className="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                               <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                             </svg>
                           </div>
                         )
                     }
                   }
                   
                   return (
                     <div key={index} className="flex items-center space-x-3">
                       {getActivityIcon(activity.type)}
                       <div className="flex-1">
                         <p className="text-sm font-medium text-gray-900">{activity.description}</p>
                         <p className="text-xs text-gray-500">
                           {activity.amount ? `₹${activity.amount.toLocaleString()} • ` : ''}{activity.date}
                         </p>
                       </div>
                     </div>
                   )
                 })
               ) : (
                 <div className="text-center py-4">
                   <p className="text-gray-500 text-sm">No recent activity</p>
                 </div>
               )}
             </div>
           </div>
        </div>
      </main>
    </div>
  )
}

// Home Component
function Home() {
  return (
    <div className="min-h-screen bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center px-4">
      <div className="bg-white p-8 rounded-xl shadow-2xl w-full max-w-md text-center">
        <div className="mb-6">
          <img src="/upload/logo/logo_mbc.png" alt="MBC Finance" className="h-20 w-auto mx-auto" />
        </div>
        <h1 className="text-3xl font-bold text-blue-900 mb-4">MBC Finance</h1>
        <p className="text-gray-600 mb-8">Your Trusted Financial Partner</p>
        
        <div className="space-y-4">
          <a 
            href="/login" 
            className="block w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 transition-colors font-medium"
          >
            Login to Account
          </a>
          <a 
            href="/register" 
            className="block w-full bg-gray-100 text-gray-700 py-3 px-4 rounded-lg hover:bg-gray-200 transition-colors font-medium"
          >
            Create Account
          </a>
        </div>
        
        <div className="mt-8 pt-6 border-t border-gray-200">
          <p className="text-sm text-gray-500">Secure • Fast • Reliable</p>
        </div>
      </div>
    </div>
  )
}

// Main App Component with Routing
function App() {
  return (
    <Routes>
      <Route path="/" element={<Home />} />
      <Route path="/login" element={<Login />} />
      <Route path="/dashboard" element={<Dashboard />} />
      <Route path="/pwa/dashboard" element={<Dashboard />} />
      <Route path="/loan-overview" element={<LoanOverview />} />
      <Route path="/pwa/loan-overview" element={<LoanOverview />} />
      <Route path="/repayment-schedule" element={<RepaymentSchedule />} />
      <Route path="/pwa/repayment-schedule" element={<RepaymentSchedule />} />
      <Route path="/loan-application" element={<EnhancedLoanApplication />} />
      <Route path="/pwa/loan-application" element={<EnhancedLoanApplication />} />
      <Route path="/pwa/enhanced-loan-application" element={<EnhancedLoanApplication />} />
        <Route path="/loan" element={<LoanPage />} />
        <Route path="/loan/:loanTypeId" element={<LoanPage />} />
      <Route path="/loan-details/:loanId" element={<LoanDetails />} />
      <Route path="/payment-tracking" element={<PaymentTracking />} />
      <Route path="/upcoming-installments" element={<UpcomingInstallments />} />
      <Route path="/emi-calculator" element={<EMICalculator />} />
      {/* Catch-all route for any unmatched paths - now uses enhanced wizard */}
      <Route path="*" element={<EnhancedLoanApplication />} />
    </Routes>
  )
}

export default App