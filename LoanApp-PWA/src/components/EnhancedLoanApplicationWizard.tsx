import React, { useState, useEffect } from 'react'
import { motion, AnimatePresence } from 'framer-motion'

interface LoanType {
  id: number
  name: string
  description: string
  min_loan_amount: number
  max_loan_amount: number
  interest_rate: number
  max_loan_term: number
  penalty_type: string
  penalties: number
  file_charges: number
}

interface FormData {
  loanType: LoanType | null
  amount: string
  tenure: string
  purpose: string
  referralCode: string
  documents: {
    aadhaar_front: File | null
    aadhaar_back: File | null
    pan_card: File | null
    income_proof: File | null
  }
  monthlyIncome: string
  employment: string
  customPurpose: string
}

export function EnhancedLoanApplicationWizard() {
  const [currentStep, setCurrentStep] = useState(1)
  const [loanTypes, setLoanTypes] = useState<LoanType[]>([])
  const [loading, setLoading] = useState(true)
  const [submitting, setSubmitting] = useState(false)
  const [formData, setFormData] = useState<FormData>({
    loanType: null,
    amount: '200000',
    tenure: '12',
    purpose: '',
    referralCode: '',
    documents: {
      aadhaar_front: null,
      aadhaar_back: null,
      pan_card: null,
      income_proof: null
    },
    monthlyIncome: '',
    employment: '',
    customPurpose: ''
  })

  const totalSteps = 6

  // Calculate EMI
  const calculateEMI = (principal: number, tenure: number, rate: number) => {
    const monthlyRate = rate / (12 * 100)
    const emi = (principal * monthlyRate * Math.pow(1 + monthlyRate, tenure)) / 
                (Math.pow(1 + monthlyRate, tenure) - 1)
    return Math.round(emi)
  }

  // Format number for display
  const formatNumber = (num: number) => {
    return new Intl.NumberFormat('en-IN').format(num)
  }

  // Fetch loan types
  useEffect(() => {
    const fetchLoanTypes = async () => {
      try {
        const token = localStorage.getItem('auth_token')
        const response = await fetch('/api/loan-types', {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
          }
        })
        
        if (response.ok) {
          const data = await response.json()
          setLoanTypes(data.loan_types || [])
        }
      } catch (error) {
        console.error('Error fetching loan types:', error)
      } finally {
        setLoading(false)
      }
    }

    fetchLoanTypes()
  }, [])

  // Step navigation
  const nextStep = () => {
    if (currentStep < totalSteps) {
      setCurrentStep(currentStep + 1)
    }
  }

  const prevStep = () => {
    if (currentStep > 1) {
      setCurrentStep(currentStep - 1)
    }
  }

  // Handle loan type selection
  const selectLoanType = (loanType: LoanType) => {
    setFormData(prev => ({
      ...prev,
      loanType,
      amount: loanType.min_loan_amount.toString()
    }))
    nextStep()
  }

  // Get selected loan type for calculations
  const selectedLoanType = formData.loanType

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gray-50">
        <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-600"></div>
      </div>
    )
  }

  return (
    <div className="min-h-screen bg-gradient-to-br from-blue-50 via-white to-blue-50">
      <div className="max-w-4xl mx-auto px-4 py-8">
        
        {/* Header */}
        <div className="text-center mb-8">
          <img src="/pwa/logo_mbc.png" alt="MBC Finance" className="h-12 mx-auto mb-4" />
          <h1 className="text-3xl font-bold text-gray-900 mb-2">Enhanced Loan Application</h1>
          <p className="text-gray-600">Complete your loan application in simple steps</p>
        </div>

        {/* Progress Indicator */}
        <div className="mb-8">
          <div className="flex justify-between items-center mb-4">
            {Array.from({ length: totalSteps }, (_, i) => {
              const step = i + 1
              const isActive = step === currentStep
              const isCompleted = step < currentStep
              
              return (
                <div key={step} className="flex flex-col items-center">
                  <div className={`w-10 h-10 rounded-full flex items-center justify-center text-sm font-semibold ${
                    isCompleted 
                      ? 'bg-green-600 text-white' 
                      : isActive 
                        ? 'bg-blue-600 text-white' 
                        : 'bg-gray-200 text-gray-600'
                  }`}>
                    {isCompleted ? '✓' : step}
                  </div>
                  <span className={`text-xs mt-2 ${isActive ? 'text-blue-600 font-semibold' : 'text-gray-500'}`}>
                    {step === 1 && 'Loan Type'}
                    {step === 2 && 'Amount'}
                    {step === 3 && 'Tenure'}
                    {step === 4 && 'EMI'}
                    {step === 5 && 'Details'}
                    {step === 6 && 'Review'}
                  </span>
                </div>
              )
            })}
          </div>
          <div className="w-full bg-gray-200 rounded-full h-2">
            <div 
              className="bg-blue-600 h-2 rounded-full transition-all duration-300"
              style={{ width: `${(currentStep / totalSteps) * 100}%` }}
            ></div>
          </div>
        </div>

        {/* Step Content */}
        <AnimatePresence mode="wait">
          {/* Step 1: Loan Type Selection */}
          {currentStep === 1 && (
            <motion.div
              key="step1"
              initial={{ opacity: 0, x: 50 }}
              animate={{ opacity: 1, x: 0 }}
              exit={{ opacity: 0, x: -50 }}
              className="bg-white rounded-xl shadow-lg p-8"
            >
              <h2 className="text-2xl font-bold text-gray-900 mb-6">Choose Your Loan Type</h2>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                {loanTypes.map((loanType) => (
                  <motion.div
                    key={loanType.id}
                    whileHover={{ scale: 1.02 }}
                    whileTap={{ scale: 0.98 }}
                    onClick={() => selectLoanType(loanType)}
                    className="border-2 border-gray-200 rounded-lg p-6 cursor-pointer hover:border-blue-500 hover:shadow-md transition-all"
                  >
                    <h3 className="text-xl font-semibold text-gray-900 mb-2">{loanType.name}</h3>
                    <p className="text-gray-600 mb-4">{loanType.description}</p>
                    <div className="space-y-2 text-sm">
                      <div className="flex justify-between">
                        <span>Amount Range:</span>
                        <span className="text-blue-700 font-semibold">
                          ₹{formatNumber(loanType.min_loan_amount)} - ₹{formatNumber(loanType.max_loan_amount)}
                        </span>
                      </div>
                      <div className="flex justify-between">
                        <span>Interest Rate:</span>
                        <span className="text-blue-700 font-semibold">{loanType.interest_rate}% p.a.</span>
                      </div>
                      <div className="flex justify-between">
                        <span>Max Tenure:</span>
                        <span className="text-blue-700 font-semibold">{loanType.max_loan_term} months</span>
                      </div>
                    </div>
                  </motion.div>
                ))}
              </div>
            </motion.div>
          )}

          {/* Step 2: Amount Selection */}
          {currentStep === 2 && selectedLoanType && (
            <motion.div
              key="step2"
              initial={{ opacity: 0, x: 50 }}
              animate={{ opacity: 1, x: 0 }}
              exit={{ opacity: 0, x: -50 }}
              className="bg-white rounded-xl shadow-lg p-8"
            >
              <h2 className="text-2xl font-bold text-gray-900 mb-6">Select Loan Amount</h2>
              <div className="space-y-6">
                <div className="text-center">
                  <div className="text-4xl font-bold text-blue-600 mb-2">
                    ₹{formatNumber(parseInt(formData.amount))}
                  </div>
                  <p className="text-gray-600">
                    Range: ₹{formatNumber(selectedLoanType.min_loan_amount)} - ₹{formatNumber(selectedLoanType.max_loan_amount)}
                  </p>
                </div>
                
                <div className="px-4">
                  <input
                    type="range"
                    min={selectedLoanType.min_loan_amount}
                    max={selectedLoanType.max_loan_amount}
                    step="1000"
                    value={formData.amount}
                    onChange={(e) => setFormData(prev => ({ ...prev, amount: e.target.value }))}
                    className="w-full h-3 bg-gray-200 rounded-lg appearance-none cursor-pointer slider"
                  />
                </div>
                
                <div className="grid grid-cols-3 gap-4">
                  {[
                    selectedLoanType.min_loan_amount,
                    Math.floor((selectedLoanType.min_loan_amount + selectedLoanType.max_loan_amount) / 2),
                    selectedLoanType.max_loan_amount
                  ].map((amount) => (
                    <button
                      key={amount}
                      onClick={() => setFormData(prev => ({ ...prev, amount: amount.toString() }))}
                      className="py-2 px-4 border border-gray-300 rounded-lg hover:bg-blue-50 hover:border-blue-500 transition-colors"
                    >
                      ₹{formatNumber(amount)}
                    </button>
                  ))}
                </div>
              </div>
              
              <div className="flex justify-between mt-8">
                <button
                  onClick={prevStep}
                  className="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
                >
                  Previous
                </button>
                <button
                  onClick={nextStep}
                  className="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                >
                  Continue
                </button>
              </div>
            </motion.div>
          )}

          {/* Step 3: Tenure Selection */}
          {currentStep === 3 && selectedLoanType && (
            <motion.div
              key="step3"
              initial={{ opacity: 0, x: 50 }}
              animate={{ opacity: 1, x: 0 }}
              exit={{ opacity: 0, x: -50 }}
              className="bg-white rounded-xl shadow-lg p-8"
            >
              <h2 className="text-2xl font-bold text-gray-900 mb-6">Choose Loan Tenure</h2>
              <div className="space-y-6">
                <div className="text-center">
                  <div className="text-4xl font-bold text-blue-600 mb-2">
                    {formData.tenure} months
                  </div>
                  <p className="text-gray-600">
                    Maximum allowed: {selectedLoanType.max_loan_term} months
                  </p>
                </div>
                
                <div className="px-4">
                  <input
                    type="range"
                    min="6"
                    max={selectedLoanType.max_loan_term}
                    step="6"
                    value={formData.tenure}
                    onChange={(e) => setFormData(prev => ({ ...prev, tenure: e.target.value }))}
                    className="w-full h-3 bg-gray-200 rounded-lg appearance-none cursor-pointer slider"
                  />
                </div>
                
                <div className="grid grid-cols-4 gap-4">
                  {[6, 12, 24, 36].filter(months => months <= selectedLoanType.max_loan_term).map((months) => (
                    <button
                      key={months}
                      onClick={() => setFormData(prev => ({ ...prev, tenure: months.toString() }))}
                      className="py-2 px-4 border border-gray-300 rounded-lg hover:bg-blue-50 hover:border-blue-500 transition-colors"
                    >
                      {months}m
                    </button>
                  ))}
                </div>
              </div>
              
              <div className="flex justify-between mt-8">
                <button
                  onClick={prevStep}
                  className="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
                >
                  Previous
                </button>
                <button
                  onClick={nextStep}
                  className="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                >
                  Continue
                </button>
              </div>
            </motion.div>
          )}

          {/* Step 4: EMI Calculation & Repayment */}
          {currentStep === 4 && selectedLoanType && (
            <motion.div
              key="step4"
              initial={{ opacity: 0, x: 50 }}
              animate={{ opacity: 1, x: 0 }}
              exit={{ opacity: 0, x: -50 }}
              className="bg-white rounded-xl shadow-lg p-8"
            >
              <h2 className="text-2xl font-bold text-gray-900 mb-6">EMI Calculation</h2>
              
              <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div className="space-y-4">
                  <h3 className="text-lg font-semibold text-gray-900">Loan Summary</h3>
                  <div className="space-y-3">
                    <div className="flex justify-between">
                      <span>Loan Amount:</span>
                      <span className="font-semibold">₹{formatNumber(parseInt(formData.amount))}</span>
                    </div>
                    <div className="flex justify-between">
                      <span>Interest Rate:</span>
                      <span className="font-semibold">{selectedLoanType.interest_rate}% p.a.</span>
                    </div>
                    <div className="flex justify-between">
                      <span>Tenure:</span>
                      <span className="font-semibold">{formData.tenure} months</span>
                    </div>
                    <hr />
                    <div className="flex justify-between text-lg">
                      <span className="font-semibold">Monthly EMI:</span>
                      <span className="font-bold text-blue-600">
                        ₹{formatNumber(calculateEMI(parseInt(formData.amount), parseInt(formData.tenure), selectedLoanType.interest_rate))}
                      </span>
                    </div>
                  </div>
                </div>
                
                <div className="bg-blue-50 rounded-lg p-6">
                  <h3 className="text-lg font-semibold text-gray-900 mb-4">Key Features</h3>
                  <ul className="space-y-2 text-sm">
                    <li className="flex items-center">
                      <span className="text-green-600 mr-2">✓</span>
                      Quick approval process
                    </li>
                    <li className="flex items-center">
                      <span className="text-green-600 mr-2">✓</span>
                      Minimal documentation
                    </li>
                    <li className="flex items-center">
                      <span className="text-green-600 mr-2">✓</span>
                      Competitive interest rates
                    </li>
                    <li className="flex items-center">
                      <span className="text-green-600 mr-2">✓</span>
                      Flexible repayment options
                    </li>
                  </ul>
                </div>
              </div>
              
              <div className="flex justify-between mt-8">
                <button
                  onClick={prevStep}
                  className="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
                >
                  Previous
                </button>
                <button
                  onClick={nextStep}
                  className="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                >
                  Continue
                </button>
              </div>
            </motion.div>
          )}

          {/* Step 5: Loan Details Form */}
          {currentStep === 5 && (
            <motion.div
              key="step5"
              initial={{ opacity: 0, x: 50 }}
              animate={{ opacity: 1, x: 0 }}
              exit={{ opacity: 0, x: -50 }}
              className="bg-white rounded-xl shadow-lg p-8"
            >
              <h2 className="text-2xl font-bold text-gray-900 mb-6">Loan Details</h2>
              
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Purpose of Loan *
                  </label>
                  <select
                    value={formData.purpose}
                    onChange={(e) => setFormData(prev => ({ ...prev, purpose: e.target.value }))}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required
                  >
                    <option value="">Select Purpose</option>
                    <option value="Business">Business</option>
                    <option value="Personal">Personal</option>
                    <option value="Medical">Medical</option>
                    <option value="Education">Education</option>
                    <option value="Travel">Travel</option>
                    <option value="Wedding">Wedding</option>
                    <option value="Home Renovation">Home Renovation</option>
                    <option value="Other">Other</option>
                  </select>
                </div>

                {formData.purpose === 'Other' && (
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                      Specify Purpose *
                    </label>
                    <input
                      type="text"
                      value={formData.customPurpose}
                      onChange={(e) => setFormData(prev => ({ ...prev, customPurpose: e.target.value }))}
                      className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                      placeholder="Enter custom purpose"
                      required
                    />
                  </div>
                )}

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Monthly Income *
                  </label>
                  <input
                    type="number"
                    value={formData.monthlyIncome}
                    onChange={(e) => setFormData(prev => ({ ...prev, monthlyIncome: e.target.value }))}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Enter monthly income"
                    required
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Employment Type *
                  </label>
                  <select
                    value={formData.employment}
                    onChange={(e) => setFormData(prev => ({ ...prev, employment: e.target.value }))}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required
                  >
                    <option value="">Select Employment</option>
                    <option value="Salaried">Salaried</option>
                    <option value="Self-employed">Self-employed</option>
                    <option value="Business">Business</option>
                    <option value="Freelancer">Freelancer</option>
                  </select>
                </div>

                <div className="md:col-span-2">
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Referral Code (Optional)
                  </label>
                  <input
                    type="text"
                    value={formData.referralCode}
                    onChange={(e) => setFormData(prev => ({ ...prev, referralCode: e.target.value }))}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Enter referral code if any"
                  />
                </div>
              </div>
              
              <div className="flex justify-between mt-8">
                <button
                  onClick={prevStep}
                  className="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
                >
                  Previous
                </button>
                <button
                  onClick={nextStep}
                  className="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                >
                  Review Application
                </button>
              </div>
            </motion.div>
          )}

          {/* Step 6: Review & Submit */}
          {currentStep === 6 && selectedLoanType && (
            <motion.div
              key="step6"
              initial={{ opacity: 0, x: 50 }}
              animate={{ opacity: 1, x: 0 }}
              exit={{ opacity: 0, x: -50 }}
              className="bg-white rounded-xl shadow-lg p-8"
            >
              <h2 className="text-2xl font-bold text-gray-900 mb-6">Review Your Application</h2>
              
              <div className="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                <div className="space-y-4">
                  <h3 className="text-lg font-semibold text-gray-900">Loan Details</h3>
                  <div className="space-y-2 text-sm">
                    <div className="flex justify-between">
                      <span>Loan Type:</span>
                      <span className="font-semibold">{selectedLoanType.name}</span>
                    </div>
                    <div className="flex justify-between">
                      <span>Amount:</span>
                      <span className="font-semibold">₹{formatNumber(parseInt(formData.amount))}</span>
                    </div>
                    <div className="flex justify-between">
                      <span>Tenure:</span>
                      <span className="font-semibold">{formData.tenure} months</span>
                    </div>
                    <div className="flex justify-between">
                      <span>Interest Rate:</span>
                      <span className="font-semibold">{selectedLoanType.interest_rate}% p.a.</span>
                    </div>
                    <div className="flex justify-between">
                      <span>Monthly EMI:</span>
                      <span className="font-bold text-blue-600">
                        ₹{formatNumber(calculateEMI(parseInt(formData.amount), parseInt(formData.tenure), selectedLoanType.interest_rate))}
                      </span>
                    </div>
                  </div>
                </div>
                
                <div className="space-y-4">
                  <h3 className="text-lg font-semibold text-gray-900">Personal Details</h3>
                  <div className="space-y-2 text-sm">
                    <div className="flex justify-between">
                      <span>Purpose:</span>
                      <span className="font-semibold">
                        {formData.purpose === 'Other' ? formData.customPurpose : formData.purpose}
                      </span>
                    </div>
                    <div className="flex justify-between">
                      <span>Monthly Income:</span>
                      <span className="font-semibold">₹{formatNumber(parseInt(formData.monthlyIncome || '0'))}</span>
                    </div>
                    <div className="flex justify-between">
                      <span>Employment:</span>
                      <span className="font-semibold">{formData.employment}</span>
                    </div>
                    {formData.referralCode && (
                      <div className="flex justify-between">
                        <span>Referral Code:</span>
                        <span className="font-semibold">{formData.referralCode}</span>
                      </div>
                    )}
                  </div>
                </div>
              </div>

              <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <div className="flex">
                  <div className="flex-shrink-0">
                    <svg className="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                      <path fillRule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clipRule="evenodd" />
                    </svg>
                  </div>
                  <div className="ml-3">
                    <h3 className="text-sm font-medium text-yellow-800">Important Notice</h3>
                    <div className="mt-2 text-sm text-yellow-700">
                      <p>Please review all details carefully before submitting. Once submitted, some details cannot be modified.</p>
                    </div>
                  </div>
                </div>
              </div>
              
              <div className="flex justify-between">
                <button
                  onClick={prevStep}
                  className="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
                >
                  Previous
                </button>
                <button
                  onClick={() => {
                    // Handle application submission
                    alert('Application submitted successfully!')
                    window.location.href = '/pwa/dashboard'
                  }}
                  disabled={submitting}
                  className="px-8 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50"
                >
                  {submitting ? 'Submitting...' : 'Submit Application'}
                </button>
              </div>
            </motion.div>
          )}
        </AnimatePresence>
      </div>
    </div>
  )
}