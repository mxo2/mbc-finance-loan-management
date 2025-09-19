import React, { useState, useEffect } from 'react'
import { motion, AnimatePresence } from 'framer-motion'

interface LoanType {
  id: number
  name: string
  description: string
  min_amount: number
  max_amount: number
  interest_rate: number
  max_tenure: number
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
    bank_statement: File | null
    itr_document: File | null
    other_documents: File | null
  }
  monthlyIncome: string
  employment: string
  customPurpose: string
}

const EnhancedLoanApplication = () => {
  const [currentStep, setCurrentStep] = useState(1)
  const [loanTypes, setLoanTypes] = useState<LoanType[]>([])
  const [loading, setLoading] = useState(true)
  const [submitting, setSubmitting] = useState(false)
  const [dragActive, setDragActive] = useState<string | null>(null)
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
      income_proof: null,
      bank_statement: null,
      itr_document: null,
      other_documents: null
    },
    monthlyIncome: '',
    employment: '',
    customPurpose: ''
  })

  const totalSteps = 6

  // Generate dynamic title based on loan type and step
  const getDynamicTitle = () => {
    if (!formData.loanType) {
      return 'Loan Application'
    }
    return `${formData.loanType.name} Application`
  }

  // Calculate EMI (without file charges)
  const calculateEMI = (principal: number, tenure: number, rate: number) => {
    const monthlyRate = rate / (12 * 100)
    const emi = (principal * monthlyRate * Math.pow(1 + monthlyRate, tenure)) / 
                (Math.pow(1 + monthlyRate, tenure) - 1)
    return Math.round(emi)
  }

  // File charges are fixed amounts, not percentages
  const getFileCharges = (fileCharges: number) => {
    return fileCharges
  }

  // Generate tenure options based on max tenure
  const generateTenureOptions = (maxTenure: number) => {
    const options = [3, 6, 9, 12, 18, 24, 36]
    
    // Add 40, 44, 48 if max tenure allows
    if (maxTenure >= 40) options.push(40)
    if (maxTenure >= 44) options.push(44)
    if (maxTenure >= 48) options.push(48)
    
    // Add every month from 49 to max tenure
    if (maxTenure > 48) {
      for (let i = 49; i <= maxTenure; i++) {
        options.push(i)
      }
    }
    
    return options.filter(option => option <= maxTenure)
  }

  // Format number for display
  const formatNumber = (num: number) => {
    return new Intl.NumberFormat('en-IN').format(num)
  }

  // Fetch loan types
  useEffect(() => {
    const fetchLoanTypes = async () => {
      try {
        console.log('Fetching loan types...')
        const response = await fetch('/api/loan-types', {
          headers: {
            'Content-Type': 'application/json'
          }
        })
        
        console.log('Response status:', response.status)
        
        if (response.ok) {
          const data = await response.json()
          console.log('Fetched loan types:', data)
          setLoanTypes(data.loan_types || [])
        } else {
          console.error('Failed to fetch loan types:', response.status, response.statusText)
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
    console.log('Selected loan type:', loanType)
    setFormData({
      ...formData,
      loanType,
      amount: loanType.min_amount.toString()
    })
    // Automatically advance to step 2
    console.log('Advancing to step 2')
    setCurrentStep(2)
  }

  // Get selected loan type for calculations
  const selectedLoanType = formData.loanType
  
  // Debug logging
  useEffect(() => {
    console.log('Current step:', currentStep)
    console.log('Selected loan type:', selectedLoanType)
    console.log('Form data:', formData)
  }, [currentStep, selectedLoanType, formData])

  // Update document title based on selected loan type
  useEffect(() => {
    const title = getDynamicTitle()
    document.title = `${title} - MBC Finance`
    
    // Cleanup: restore default title when component unmounts
    return () => {
      document.title = 'MBC Finance'
    }
  }, [formData.loanType])

  // File upload handlers
  const handleFileUpload = (file: File | null, documentType: keyof typeof formData.documents) => {
    setFormData(prev => ({
      ...prev,
      documents: { ...prev.documents, [documentType]: file }
    }))
  }

  const handleDrag = (e: React.DragEvent, documentType: string) => {
    e.preventDefault()
    e.stopPropagation()
    if (e.type === "dragenter" || e.type === "dragover") {
      setDragActive(documentType)
    } else if (e.type === "dragleave") {
      setDragActive(null)
    }
  }

  const handleDrop = (e: React.DragEvent, documentType: keyof typeof formData.documents) => {
    e.preventDefault()
    e.stopPropagation()
    setDragActive(null)
    
    if (e.dataTransfer.files && e.dataTransfer.files[0]) {
      handleFileUpload(e.dataTransfer.files[0], documentType)
    }
  }

  // Modern file upload component
  const FileUploadBox = ({ 
    documentType, 
    label, 
    required = false, 
    description 
  }: { 
    documentType: keyof typeof formData.documents
    label: string
    required?: boolean
    description?: string
  }) => {
    const file = formData.documents[documentType]
    const isActive = dragActive === documentType

    return (
      <div className="space-y-2">
        <label className="block text-sm font-medium text-gray-700">
          {label} {required && <span className="text-red-500">*</span>}
        </label>
        
        <div
          className={`relative border-2 border-dashed rounded-lg p-6 transition-colors cursor-pointer
            ${isActive ? 'border-blue-500 bg-blue-50' : 'border-gray-300 hover:border-gray-400'}
            ${file ? 'bg-green-50 border-green-300' : ''}
          `}
          onDragEnter={(e) => handleDrag(e, documentType)}
          onDragLeave={(e) => handleDrag(e, documentType)}
          onDragOver={(e) => handleDrag(e, documentType)}
          onDrop={(e) => handleDrop(e, documentType)}
          onClick={() => document.getElementById(`file-${documentType}`)?.click()}
        >
          <input
            id={`file-${documentType}`}
            type="file"
            accept=".jpg,.jpeg,.png,.pdf"
            onChange={(e) => handleFileUpload(e.target.files?.[0] || null, documentType)}
            className="hidden"
            required={required}
          />
          
          <div className="text-center">
            {file ? (
              <div className="space-y-2">
                <svg className="mx-auto h-12 w-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div className="text-sm font-medium text-green-700">{file.name}</div>
                <div className="text-xs text-green-600">File uploaded successfully</div>
                <button
                  type="button"
                  onClick={(e) => {
                    e.stopPropagation()
                    handleFileUpload(null, documentType)
                  }}
                  className="text-xs text-red-600 hover:text-red-800 underline"
                >
                  Remove file
                </button>
              </div>
            ) : (
              <div className="space-y-2">
                <svg className="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
                <div className="text-sm text-gray-600">
                  <span className="font-medium text-blue-600">Click to upload</span> or drag and drop
                </div>
                <div className="text-xs text-gray-500">PNG, JPG, PDF up to 5MB</div>
              </div>
            )}
          </div>
        </div>
        
        {description && (
          <p className="text-xs text-gray-500">{description}</p>
        )}
      </div>
    )
  }

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
          <h1 className="text-3xl font-bold text-gray-900 mb-2">{getDynamicTitle()}</h1>
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
                          ₹{formatNumber(loanType.min_amount)} - ₹{formatNumber(loanType.max_amount)}
                        </span>
                      </div>
                      <div className="flex justify-between">
                        <span>Interest Rate:</span>
                        <span className="text-blue-700 font-semibold">{loanType.interest_rate}% p.a.</span>
                      </div>
                      <div className="flex justify-between">
                        <span>Max Tenure:</span>
                        <span className="text-blue-700 font-semibold">{loanType.max_tenure} months</span>
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
                    Range: ₹{formatNumber(selectedLoanType.min_amount)} - ₹{formatNumber(selectedLoanType.max_amount)}
                  </p>
                </div>
                
                <div className="px-4">
                  <input
                    type="range"
                    min={selectedLoanType.min_amount}
                    max={selectedLoanType.max_amount}
                    step="1000"
                    value={formData.amount}
                    onChange={(e) => setFormData(prev => ({ ...prev, amount: e.target.value }))}
                    className="w-full h-3 bg-gray-200 rounded-lg appearance-none cursor-pointer slider"
                  />
                </div>
                
                <div className="grid grid-cols-3 gap-4">
                  {[
                    selectedLoanType.min_amount,
                    Math.floor((selectedLoanType.min_amount + selectedLoanType.max_amount) / 2),
                    selectedLoanType.max_amount
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
                    Maximum allowed: {selectedLoanType.max_tenure} months
                  </p>
                </div>
                
                <div className="px-4">
                  <input
                    type="range"
                    min="6"
                    max={selectedLoanType.max_tenure}
                    step="6"
                    value={formData.tenure}
                    onChange={(e) => setFormData(prev => ({ ...prev, tenure: e.target.value }))}
                    className="w-full h-3 bg-gray-200 rounded-lg appearance-none cursor-pointer slider"
                  />
                </div>
                
                <div className="grid grid-cols-4 gap-4">
                  {generateTenureOptions(selectedLoanType.max_tenure).slice(0, 8).map((months) => (
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
                    {selectedLoanType.file_charges > 0 && (
                      <div className="flex justify-between border-t pt-2">
                        <span>One-time File Charges:</span>
                        <span className="font-semibold text-orange-600">
                          ₹{formatNumber(getFileCharges(selectedLoanType.file_charges))}
                        </span>
                      </div>
                    )}
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

              {/* Document Upload Section */}
              <div className="mt-8">
                <h3 className="text-lg font-semibold text-gray-900 mb-6">Required Documents</h3>
                
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <FileUploadBox
                    documentType="aadhaar_front"
                    label="Aadhaar Card (Front)"
                    required={true}
                  />

                  <FileUploadBox
                    documentType="aadhaar_back"
                    label="Aadhaar Card (Back)"
                    required={true}
                  />

                  <FileUploadBox
                    documentType="pan_card"
                    label="PAN Card"
                    required={true}
                  />

                  <FileUploadBox
                    documentType="income_proof"
                    label="Income Proof (Optional)"
                    required={false}
                    description="Salary slip, bank statement, or ITR"
                  />
                </div>

                {/* Additional Documents Section */}
                <div className="space-y-4 mt-6">
                  <h3 className="text-lg font-semibold text-gray-900 border-b pb-2">Additional Documents (Optional)</h3>
                  <p className="text-gray-600 text-sm">Upload any additional supporting documents that may strengthen your application</p>
                  
                  <FileUploadBox
                    documentType="bank_statement"
                    label="Bank Statement"
                    required={false}
                    description="Last 3-6 months bank statement"
                  />

                  <FileUploadBox
                    documentType="itr_document"
                    label="ITR Documents"
                    required={false}
                    description="Income Tax Return for last 2 years"
                  />

                  <FileUploadBox
                    documentType="other_documents"
                    label="Other Supporting Documents"
                    required={false}
                    description="Any other relevant financial documents"
                  />
                </div>

                <div className="mt-4 p-4 bg-blue-50 rounded-lg">
                  <h4 className="text-sm font-medium text-blue-900 mb-2">Document Guidelines:</h4>
                  <ul className="text-xs text-blue-800 space-y-1">
                    <li>• Upload clear, readable images or PDFs</li>
                    <li>• File size should not exceed 5MB</li>
                    <li>• Supported formats: JPG, PNG, PDF</li>
                    <li>• Ensure all details are visible</li>
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

export default EnhancedLoanApplication