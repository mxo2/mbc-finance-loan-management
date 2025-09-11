import { Link } from 'react-router-dom'
import { motion } from 'framer-motion'
import { useAuth } from '../hooks/useAuth'

const Dashboard = () => {
  const { user } = useAuth()

  const quickActions = [
    {
      title: 'Apply for Loan',
      description: 'Start a new loan application',
      icon: '📝',
      href: '/apply',
      color: 'bg-primary-500',
    },
    {
      title: 'Complete KYC',
      description: 'Verify your identity',
      icon: '🆔',
      href: '/kyc',
      color: 'bg-success-500',
    },
    {
      title: 'View Loans',
      description: 'Check your loan status',
      icon: '💰',
      href: '/loans',
      color: 'bg-warning-500',
    },
    {
      title: 'Update Profile',
      description: 'Manage your account',
      icon: '👤',
      href: '/profile',
      color: 'bg-purple-500',
    },
  ]

  const stats = [
    {
      label: 'Active Loans',
      value: '2',
      icon: '💰',
      color: 'text-primary-600',
    },
    {
      label: 'Total Borrowed',
      value: '₹50,000',
      icon: '📊',
      color: 'text-success-600',
    },
    {
      label: 'KYC Status',
      value: user?.kyc_status === 'verified' ? 'Verified' : 'Pending',
      icon: '✅',
      color: user?.kyc_status === 'verified' ? 'text-success-600' : 'text-warning-600',
    },
  ]

  return (
    <div className="space-y-6">
      {/* Welcome Section */}
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.5 }}
        className="card p-6 bg-gradient-to-r from-primary-500 to-primary-600 text-white"
      >
        <h1 className="text-2xl font-bold mb-2">
          Welcome back, {user?.name}! 👋
        </h1>
        <p className="text-primary-100">
          Manage your loans and complete your applications from here.
        </p>
      </motion.div>

      {/* Stats */}
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.5, delay: 0.1 }}
        className="grid grid-cols-1 md:grid-cols-3 gap-4"
      >
        {stats.map((stat, index) => (
          <div key={index} className="card p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-600">{stat.label}</p>
                <p className={`text-2xl font-bold ${stat.color}`}>{stat.value}</p>
              </div>
              <div className="text-3xl">{stat.icon}</div>
            </div>
          </div>
        ))}
      </motion.div>

      {/* Quick Actions */}
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.5, delay: 0.2 }}
      >
        <h2 className="text-xl font-semibold text-gray-900 mb-4">Quick Actions</h2>
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          {quickActions.map((action, index) => (
            <Link
              key={index}
              to={action.href}
              className="card p-6 hover:shadow-lg transition-shadow duration-200 group"
            >
              <div className="text-center">
                <div className={`w-12 h-12 ${action.color} rounded-lg flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform duration-200`}>
                  <span className="text-2xl">{action.icon}</span>
                </div>
                <h3 className="font-semibold text-gray-900 mb-2">{action.title}</h3>
                <p className="text-sm text-gray-600">{action.description}</p>
              </div>
            </Link>
          ))}
        </div>
      </motion.div>

      {/* Recent Activity */}
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.5, delay: 0.3 }}
        className="card p-6"
      >
        <h2 className="text-xl font-semibold text-gray-900 mb-4">Recent Activity</h2>
        <div className="space-y-4">
          <div className="flex items-center space-x-4 p-4 bg-gray-50 rounded-lg">
            <div className="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center">
              <span className="text-primary-600">📝</span>
            </div>
            <div className="flex-1">
              <p className="font-medium text-gray-900">Loan Application Submitted</p>
              <p className="text-sm text-gray-600">Personal loan for ₹25,000</p>
            </div>
            <span className="text-sm text-gray-500">2 days ago</span>
          </div>
          
          <div className="flex items-center space-x-4 p-4 bg-gray-50 rounded-lg">
            <div className="w-10 h-10 bg-success-100 rounded-full flex items-center justify-center">
              <span className="text-success-600">✅</span>
            </div>
            <div className="flex-1">
              <p className="font-medium text-gray-900">KYC Verification Completed</p>
              <p className="text-sm text-gray-600">Identity verification successful</p>
            </div>
            <span className="text-sm text-gray-500">1 week ago</span>
          </div>
          
          <div className="flex items-center space-x-4 p-4 bg-gray-50 rounded-lg">
            <div className="w-10 h-10 bg-warning-100 rounded-full flex items-center justify-center">
              <span className="text-warning-600">💰</span>
            </div>
            <div className="flex-1">
              <p className="font-medium text-gray-900">Loan Approved</p>
              <p className="text-sm text-gray-600">Home loan for ₹25,000 approved</p>
            </div>
            <span className="text-sm text-gray-500">2 weeks ago</span>
          </div>
        </div>
      </motion.div>


    </div>
  )
}

export default Dashboard