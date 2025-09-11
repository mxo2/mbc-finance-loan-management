import { useState } from 'react'
import { motion } from 'framer-motion'
import { useForm } from 'react-hook-form'
import LoadingSpinner from '../components/LoadingSpinner'

interface LoanFormData {
  loan_type: string
  amount: number
  purpose_of_loan: string
  referral_code?: string
  aadhaar_card_front: FileList
  aadhaar_card_back: FileList
  pan_card: FileList
}

const LoanApplication = () => {
  const [isSubmitting, setIsSubmitting] = useState(false)
  const [currentStep, setCurrentStep] = useState(1)
  const totalSteps = 3

  const {
    register,
    handleSubmit,
    formState: { errors },
    watch,
  } = useForm<LoanFormData>()

  const onSubmit = async (data: LoanFormData) => {
    setIsSubmitting(true)
    try {
      // Create FormData for file uploads
      const formData = new FormData()
      formData.append('loan_type', data.loan_type)
      formData.append('amount', data.amount.toString())
      formData.append('purpose_of_loan', data.purpose_of_loan)
      if (data.referral_code) {
        formData.append('referral_code', data.referral_code)
      }
      
      // Append files
      if (data.aadhaar_card_front[0]) {
        formData.append('aadhaar_card_front', data.aadhaar_card_front[0])
      }
      if (data.aadhaar_card_back[0]) {
        formData.append('aadhaar_card_back', data.aadhaar_card_back[0])
      }
      if (data.pan_card[0]) {
        formData.append('pan_card', data.pan_card[0])
      }

      // TODO: Submit to API
      console.log('Submitting loan application:', formData)
      
      // Simulate API call
      await new Promise(resolve => setTimeout(resolve, 2000))
      
      alert('Loan application submitted successfully!')
    } catch (error) {
      console.error('Error submitting loan application:', error)
      alert('Error submitting application. Please try again.')
    } finally {
      setIsSubmitting(false)
    }
  }

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

  return (
    <div className="max-w-2xl mx-auto">
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.5 }}
      >
        {/* Header */}
        <div className="text-center mb-8">
          <h1 className="text-3xl font-bold text-gray-900 mb-2">Apply for Loan</h1>
          <p className="text-gray-600">Complete your loan application in simple steps</p>
        </div>

        {/* Progress Bar */}
        <div className="mb-8">
          <div className="flex items-center justify-between mb-2">
            {Array.from({ length: totalSteps }, (_, i) => (
              <div
                key={i}
                className={`flex items-center justify-center w-8 h-8 rounded-full text-sm font-medium ${
                  i + 1 <= currentStep
                    ? 'bg-primary-600 text-white'
                    : 'bg-gray-200 text-gray-600'
                }`}
              >
                {i + 1}
              </div>
            ))}
          </div>
          <div className="w-full bg-gray-200 rounded-full h-2">
            <div
              className="bg-primary-600 h-2 rounded-full transition-all duration-300"
              style={{ width: `${(currentStep / totalSteps) * 100}%` }}
            ></div>
          </div>
        </div>

        <form onSubmit={handleSubmit(onSubmit)}>
          <div className="card p-6">
            {/* Step 1: Basic Information */}
            {currentStep === 1 && (
              <motion.div
                initial={{ opacity: 0, x: 20 }}
                animate={{ opacity: 1, x: 0 }}
                transition={{ duration: 0.3 }}
                className="space-y-6"
              >
                <h2 className="text-xl font-semibold text-gray-900 mb-4">Basic Information</h2>
                
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Loan Type
                  </label>
                  <select
                    {...register('loan_type', { required: 'Please select a loan type' })}
                    className="input w-full"
                  >
                    <option value="">Select loan type</option>
                    <option value="personal">Personal Loan</option>
                    <option value="home">Home Loan</option>
                    <option value="business">Business Loan</option>
                    <option value="education">Education Loan</option>
                  </select>
                  {errors.loan_type && (
                    <p className="mt-1 text-sm text-error-600">{errors.loan_type.message}</p>
                  )}
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Loan Amount (₹)
                  </label>
                  <input
                    {...register('amount', {
                      required: 'Please enter loan amount',
                      min: { value: 1000, message: 'Minimum amount is ₹1,000' },
                      max: { value: 1000000, message: 'Maximum amount is ₹10,00,000' }
                    })}
                    type="number"
                    className="input w-full"
                    placeholder="Enter amount"
                  />
                  {errors.amount && (
                    <p className="mt-1 text-sm text-error-600">{errors.amount.message}</p>
                  )}
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Purpose of Loan
                  </label>
                  <textarea
                    {...register('purpose_of_loan', { required: 'Please describe the purpose' })}
                    className="input w-full h-24 resize-none"
                    placeholder="Describe the purpose of this loan"
                  />
                  {errors.purpose_of_loan && (
                    <p className="mt-1 text-sm text-error-600">{errors.purpose_of_loan.message}</p>
                  )}
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Referral Code (Optional)
                  </label>
                  <input
                    {...register('referral_code')}
                    type="text"
                    className="input w-full"
                    placeholder="Enter referral code if any"
                  />
                </div>
              </motion.div>
            )}

            {/* Step 2: Document Upload */}
            {currentStep === 2 && (
              <motion.div
                initial={{ opacity: 0, x: 20 }}
                animate={{ opacity: 1, x: 0 }}
                transition={{ duration: 0.3 }}
                className="space-y-6"
              >
                <h2 className="text-xl font-semibold text-gray-900 mb-4">Document Upload</h2>
                
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                      Aadhaar Card - Front Side *
                    </label>
                    <input
                      {...register('aadhaar_card_front', { required: 'Aadhaar front is required' })}
                      type="file"
                      accept=".jpg,.jpeg,.png,.pdf"
                      className="input w-full"
                    />
                    {errors.aadhaar_card_front && (
                      <p className="mt-1 text-sm text-error-600">{errors.aadhaar_card_front.message}</p>
                    )}
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                      Aadhaar Card - Back Side *
                    </label>
                    <input
                      {...register('aadhaar_card_back', { required: 'Aadhaar back is required' })}
                      type="file"
                      accept=".jpg,.jpeg,.png,.pdf"
                      className="input w-full"
                    />
                    {errors.aadhaar_card_back && (
                      <p className="mt-1 text-sm text-error-600">{errors.aadhaar_card_back.message}</p>
                    )}
                  </div>

                  <div className="md:col-span-2">
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                      PAN Card *
                    </label>
                    <input
                      {...register('pan_card', { required: 'PAN card is required' })}
                      type="file"
                      accept=".jpg,.jpeg,.png,.pdf"
                      className="input w-full"
                    />
                    {errors.pan_card && (
                      <p className="mt-1 text-sm text-error-600">{errors.pan_card.message}</p>
                    )}
                  </div>
                </div>

                <div className="bg-blue-50 border border-blue-200 rounded-lg p-4">
                  <h3 className="font-medium text-blue-900 mb-2">📋 Document Guidelines</h3>
                  <ul className="text-sm text-blue-800 space-y-1">
                    <li>• Upload clear, readable images or PDFs</li>
                    <li>• Maximum file size: 2MB per document</li>
                    <li>• Supported formats: JPG, PNG, PDF</li>
                    <li>• Ensure all text is clearly visible</li>
                  </ul>
                </div>
              </motion.div>
            )}

            {/* Step 3: Review & Submit */}
            {currentStep === 3 && (
              <motion.div
                initial={{ opacity: 0, x: 20 }}
                animate={{ opacity: 1, x: 0 }}
                transition={{ duration: 0.3 }}
                className="space-y-6"
              >
                <h2 className="text-xl font-semibold text-gray-900 mb-4">Review & Submit</h2>
                
                <div className="bg-gray-50 rounded-lg p-6 space-y-4">
                  <div className="grid grid-cols-2 gap-4">
                    <div>
                      <span className="text-sm text-gray-600">Loan Type:</span>
                      <p className="font-medium">{watch('loan_type') || 'Not selected'}</p>
                    </div>
                    <div>
                      <span className="text-sm text-gray-600">Amount:</span>
                      <p className="font-medium">₹{watch('amount')?.toLocaleString() || '0'}</p>
                    </div>
                  </div>
                  
                  <div>
                    <span className="text-sm text-gray-600">Purpose:</span>
                    <p className="font-medium">{watch('purpose_of_loan') || 'Not provided'}</p>
                  </div>
                  
                  {watch('referral_code') && (
                    <div>
                      <span className="text-sm text-gray-600">Referral Code:</span>
                      <p className="font-medium">{watch('referral_code')}</p>
                    </div>
                  )}
                </div>

                <div className="bg-green-50 border border-green-200 rounded-lg p-4">
                  <h3 className="font-medium text-green-900 mb-2">✅ What happens next?</h3>
                  <ul className="text-sm text-green-800 space-y-1">
                    <li>• Your application will be reviewed within 24 hours</li>
                    <li>• You'll receive updates via email and SMS</li>
                    <li>• Additional documents may be requested if needed</li>
                    <li>• Approved loans are disbursed within 2-3 business days</li>
                  </ul>
                </div>
              </motion.div>
            )}

            {/* Navigation Buttons */}
            <div className="flex justify-between mt-8 pt-6 border-t border-gray-200">
              <button
                type="button"
                onClick={prevStep}
                disabled={currentStep === 1}
                className="btn btn-outline btn-md disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Previous
              </button>
              
              {currentStep < totalSteps ? (
                <button
                  type="button"
                  onClick={nextStep}
                  className="btn btn-primary btn-md"
                >
                  Next
                </button>
              ) : (
                <button
                  type="submit"
                  disabled={isSubmitting}
                  className="btn btn-primary btn-md"
                >
                  {isSubmitting ? (
                    <>
                      <LoadingSpinner size="sm" className="mr-2" />
                      Submitting...
                    </>
                  ) : (
                    'Submit Application'
                  )}
                </button>
              )}
            </div>
          </div>
        </form>
      </motion.div>
    </div>
  )
}

export default LoanApplication