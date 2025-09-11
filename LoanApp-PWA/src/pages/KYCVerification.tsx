import { useState } from 'react'
import { motion } from 'framer-motion'
import LoadingSpinner from '../components/LoadingSpinner'

const KYCVerification = () => {
  const [isSubmitting, setIsSubmitting] = useState(false)
  const [kycStatus] = useState<'pending' | 'verified' | 'rejected'>('pending')

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    setIsSubmitting(true)
    
    try {
      // TODO: Submit KYC data to API
      await new Promise(resolve => setTimeout(resolve, 2000))
      alert('KYC documents submitted successfully!')
    } catch (error) {
      alert('Error submitting KYC. Please try again.')
    } finally {
      setIsSubmitting(false)
    }
  }

  return (
    <div className="max-w-2xl mx-auto">
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.5 }}
      >
        <div className="text-center mb-8">
          <h1 className="text-3xl font-bold text-gray-900 mb-2">KYC Verification</h1>
          <p className="text-gray-600">Complete your identity verification to proceed</p>
        </div>

        {/* KYC Status */}
        <div className="card p-6 mb-6">
          <div className="flex items-center justify-between">
            <div>
              <h2 className="text-lg font-semibold text-gray-900">Verification Status</h2>
              <p className="text-gray-600">Current status of your KYC verification</p>
            </div>
            <div className={`px-4 py-2 rounded-full text-sm font-medium ${
              kycStatus === 'verified' ? 'bg-success-100 text-success-800' :
              kycStatus === 'rejected' ? 'bg-error-100 text-error-800' :
              'bg-warning-100 text-warning-800'
            }`}>
              {kycStatus === 'verified' ? '✅ Verified' :
               kycStatus === 'rejected' ? '❌ Rejected' :
               '⏳ Pending'}
            </div>
          </div>
        </div>

        {/* KYC Form */}
        <form onSubmit={handleSubmit}>
          <div className="card p-6">
            <h2 className="text-xl font-semibold text-gray-900 mb-6">Identity Documents</h2>
            
            <div className="space-y-6">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Profile Photo *
                </label>
                <input
                  type="file"
                  accept="image/*"
                  className="input w-full"
                  required
                />
                <p className="mt-1 text-sm text-gray-500">
                  Upload a clear photo of yourself
                </p>
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Address Proof *
                </label>
                <input
                  type="file"
                  accept=".jpg,.jpeg,.png,.pdf"
                  className="input w-full"
                  required
                />
                <p className="mt-1 text-sm text-gray-500">
                  Utility bill, bank statement, or rental agreement
                </p>
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Income Proof *
                </label>
                <input
                  type="file"
                  accept=".jpg,.jpeg,.png,.pdf"
                  className="input w-full"
                  required
                />
                <p className="mt-1 text-sm text-gray-500">
                  Salary slip, bank statement, or ITR
                </p>
              </div>
            </div>

            <div className="mt-8 pt-6 border-t border-gray-200">
              <button
                type="submit"
                disabled={isSubmitting}
                className="btn btn-primary btn-lg w-full"
              >
                {isSubmitting ? (
                  <>
                    <LoadingSpinner size="sm" className="mr-2" />
                    Submitting...
                  </>
                ) : (
                  'Submit KYC Documents'
                )}
              </button>
            </div>
          </div>
        </form>

        {/* Guidelines */}
        <div className="card p-6 mt-6 bg-blue-50 border-blue-200">
          <h3 className="font-semibold text-blue-900 mb-3">📋 KYC Guidelines</h3>
          <ul className="text-sm text-blue-800 space-y-2">
            <li>• All documents should be clear and readable</li>
            <li>• Maximum file size: 5MB per document</li>
            <li>• Supported formats: JPG, PNG, PDF</li>
            <li>• Documents should be recent (within 3 months)</li>
            <li>• Verification typically takes 24-48 hours</li>
          </ul>
        </div>
      </motion.div>
    </div>
  )
}

export default KYCVerification