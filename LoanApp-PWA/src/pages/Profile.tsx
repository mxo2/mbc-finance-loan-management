import { useState } from 'react'
import { motion } from 'framer-motion'
import { useAuth } from '../hooks/useAuth'
import LoadingSpinner from '../components/LoadingSpinner'

const Profile = () => {
  const { user } = useAuth()
  const [isEditing, setIsEditing] = useState(false)
  const [isUpdating, setIsUpdating] = useState(false)
  const [formData, setFormData] = useState({
    name: user?.name || '',
    email: user?.email || '',
    phone: user?.phone || ''
  })

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    setIsUpdating(true)
    
    try {
      // TODO: Update profile via API
      await new Promise(resolve => setTimeout(resolve, 1000))
      alert('Profile updated successfully!')
      setIsEditing(false)
    } catch (error) {
      alert('Error updating profile. Please try again.')
    } finally {
      setIsUpdating(false)
    }
  }

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value
    })
  }

  return (
    <div className="max-w-2xl mx-auto space-y-6">
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.5 }}
      >
        <div className="text-center mb-8">
          <h1 className="text-3xl font-bold text-gray-900 mb-2">My Profile</h1>
          <p className="text-gray-600">Manage your account information</p>
        </div>

        {/* Profile Card */}
        <div className="card p-6">
          <div className="flex items-center justify-between mb-6">
            <h2 className="text-xl font-semibold text-gray-900">Personal Information</h2>
            <button
              onClick={() => setIsEditing(!isEditing)}
              className="btn btn-outline btn-sm"
            >
              {isEditing ? 'Cancel' : 'Edit'}
            </button>
          </div>

          <form onSubmit={handleSubmit}>
            <div className="space-y-6">
              <div className="flex justify-center mb-6">
                <div className="w-24 h-24 bg-primary-100 rounded-full flex items-center justify-center">
                  <span className="text-3xl font-bold text-primary-600">
                    {user?.name?.charAt(0).toUpperCase()}
                  </span>
                </div>
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Full Name
                </label>
                {isEditing ? (
                  <input
                    type="text"
                    name="name"
                    value={formData.name}
                    onChange={handleInputChange}
                    className="input w-full"
                    required
                  />
                ) : (
                  <p className="text-gray-900 py-2">{user?.name}</p>
                )}
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Email Address
                </label>
                {isEditing ? (
                  <input
                    type="email"
                    name="email"
                    value={formData.email}
                    onChange={handleInputChange}
                    className="input w-full"
                    required
                  />
                ) : (
                  <p className="text-gray-900 py-2">{user?.email}</p>
                )}
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Phone Number
                </label>
                {isEditing ? (
                  <input
                    type="tel"
                    name="phone"
                    value={formData.phone}
                    onChange={handleInputChange}
                    className="input w-full"
                  />
                ) : (
                  <p className="text-gray-900 py-2">{user?.phone || 'Not provided'}</p>
                )}
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Account Type
                </label>
                <p className="text-gray-900 py-2 capitalize">{user?.type}</p>
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Member Since
                </label>
                <p className="text-gray-900 py-2">
                  {user?.created_at ? new Date(user.created_at).toLocaleDateString() : 'N/A'}
                </p>
              </div>

              {isEditing && (
                <div className="pt-4 border-t border-gray-200">
                  <button
                    type="submit"
                    disabled={isUpdating}
                    className="btn btn-primary btn-lg w-full"
                  >
                    {isUpdating ? (
                      <>
                        <LoadingSpinner size="sm" className="mr-2" />
                        Updating...
                      </>
                    ) : (
                      'Save Changes'
                    )}
                  </button>
                </div>
              )}
            </div>
          </form>
        </div>

        {/* KYC Status */}
        <div className="card p-6">
          <h2 className="text-xl font-semibold text-gray-900 mb-4">Verification Status</h2>
          <div className="flex items-center justify-between">
            <div>
              <p className="font-medium text-gray-900">KYC Verification</p>
              <p className="text-sm text-gray-600">Identity verification status</p>
            </div>
            <div className={`px-3 py-1 rounded-full text-sm font-medium ${
              user?.kyc_status === 'verified' ? 'bg-success-100 text-success-800' :
              user?.kyc_status === 'rejected' ? 'bg-error-100 text-error-800' :
              'bg-warning-100 text-warning-800'
            }`}>
              {user?.kyc_status === 'verified' ? '✅ Verified' :
               user?.kyc_status === 'rejected' ? '❌ Rejected' :
               '⏳ Pending'}
            </div>
          </div>
          
          {user?.kyc_status !== 'verified' && (
            <div className="mt-4">
              <button className="btn btn-primary btn-sm">
                Complete KYC
              </button>
            </div>
          )}
        </div>

        {/* Security */}
        <div className="card p-6">
          <h2 className="text-xl font-semibold text-gray-900 mb-4">Security</h2>
          <div className="space-y-4">
            <div className="flex items-center justify-between">
              <div>
                <p className="font-medium text-gray-900">Password</p>
                <p className="text-sm text-gray-600">Last updated 30 days ago</p>
              </div>
              <button className="btn btn-outline btn-sm">
                Change Password
              </button>
            </div>
            
            <div className="flex items-center justify-between">
              <div>
                <p className="font-medium text-gray-900">Two-Factor Authentication</p>
                <p className="text-sm text-gray-600">Add an extra layer of security</p>
              </div>
              <button className="btn btn-outline btn-sm">
                Enable 2FA
              </button>
            </div>
          </div>
        </div>

        {/* App Settings */}
        <div className="card p-6">
          <h2 className="text-xl font-semibold text-gray-900 mb-4">App Settings</h2>
          <div className="space-y-4">
            <div className="flex items-center justify-between">
              <div>
                <p className="font-medium text-gray-900">Push Notifications</p>
                <p className="text-sm text-gray-600">Receive updates about your loans</p>
              </div>
              <label className="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" className="sr-only peer" defaultChecked />
                <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
              </label>
            </div>
            
            <div className="flex items-center justify-between">
              <div>
                <p className="font-medium text-gray-900">Email Notifications</p>
                <p className="text-sm text-gray-600">Receive email updates</p>
              </div>
              <label className="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" className="sr-only peer" defaultChecked />
                <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
              </label>
            </div>
          </div>
        </div>
      </motion.div>
    </div>
  )
}

export default Profile