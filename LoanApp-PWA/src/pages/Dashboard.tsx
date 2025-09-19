import { Link } from 'react-router-dom'
import { motion } from 'framer-motion'
import { useAuth } from '../hooks/useAuth'
import { useQuery } from '@tanstack/react-query'
import { dashboardAPI } from '../services/api'
import LoadingSpinner from '../components/LoadingSpinner'

const Dashboard = () => {
  const { user } = useAuth()

  // Fetch dashboard data from API
  const { data: dashboardData, isLoading, error } = useQuery({
    queryKey: ['dashboard'],
    queryFn: async () => {
      const response = await dashboardAPI.getStats()
      return response.data
    },
    enabled: !!user,
    refetchOnWindowFocus: false,
  })

  if (isLoading) {
    return (
      <div className="flex items-center justify-center h-64">
        <LoadingSpinner size="lg" />
      </div>
    )
  }

  if (error) {
    return (
      <div className="text-center text-red-600 p-6">
        <p>Failed to load dashboard data. Please try refreshing the page.</p>
      </div>
    )
  }

  const quickActions = [
    {
      title: 'Apply for Loan',
      description: 'Start a new loan application',
      icon: '📝',
      href: '/apply',
      color: 'bg-primary-500',
    },
    {
      title: 'Pay EMI',
      description: 'Pay your monthly EMI',
      icon: '💳',
      href: '/pwa/pay-emi',
      color: 'bg-red-500',
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

  // Use real data from API if available, otherwise fallback to dummy data
  const stats = dashboardData?.stats || [
    {
      label: 'Active Loans',
      value: dashboardData?.summary?.activeLoans?.toString() || '0',
      icon: '💰',
      color: 'text-primary-600',
    },
    {
      label: 'Total Borrowed',
      value: `₹${dashboardData?.summary?.totalAmount ? new Intl.NumberFormat('en-IN').format(dashboardData.summary.totalAmount) : '0'}`,
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

  // Use real recent activity data if available
  const recentActivity = dashboardData?.recentActivity || [
    {
      title: 'Loan Application Submitted',
      description: 'Personal loan application under review',
      icon: '📝',
      date: '2 days ago'
    },
    {
      title: 'KYC Verification Completed',
      description: 'Identity verification successful',
      icon: '✅',
      date: '1 week ago'
    },
    {
      title: 'Payment Received',
      description: 'EMI payment processed successfully',
      icon: '💰',
      date: '2 weeks ago'
    }
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
        
        {/* Next EMI Alert */}
        {dashboardData?.nextEmi && (
          <div className="mt-4 p-3 bg-white/10 rounded-lg backdrop-blur">
            <p className="text-sm text-primary-100">Next EMI Due</p>
            <p className="font-semibold">
              ₹{new Intl.NumberFormat('en-IN').format(dashboardData.nextEmi.amount)} on {new Date(dashboardData.nextEmi.due_date).toLocaleDateString('en-IN')}
            </p>
            <p className="text-xs text-primary-200">
              {dashboardData.nextEmi.days_remaining} days remaining • {dashboardData.nextEmi.loan_id}
            </p>
          </div>
        )}
      </motion.div>

      {/* Stats */}
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.5, delay: 0.1 }}
        className="grid grid-cols-1 md:grid-cols-3 gap-4"
      >
        {stats.map((stat: any, index: number) => (
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
          {recentActivity.map((activity: any, index: number) => (
            <div key={index} className="flex items-center space-x-4 p-4 bg-gray-50 rounded-lg">
              <div className="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center">
                <span className="text-primary-600">{activity.icon}</span>
              </div>
              <div className="flex-1">
                <p className="font-medium text-gray-900">{activity.title}</p>
                <p className="text-sm text-gray-600">{activity.description}</p>
              </div>
              <span className="text-sm text-gray-500">{activity.date}</span>
            </div>
          ))}
        </div>
      </motion.div>

      {/* Pending Amount Alert */}
      {dashboardData?.summary?.pendingAmount > 0 && (
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.5, delay: 0.4 }}
          className="card p-6 bg-orange-50 border-l-4 border-orange-400"
        >
          <div className="flex items-center">
            <div className="flex-shrink-0">
              <span className="text-2xl">⚠️</span>
            </div>
            <div className="ml-3">
              <h3 className="text-lg font-medium text-orange-800">
                Outstanding Amount: ₹{new Intl.NumberFormat('en-IN').format(dashboardData.summary.pendingAmount)}
              </h3>
              <p className="text-sm text-orange-700">
                You have pending loan repayments. Please make your payments on time to maintain a good credit score.
              </p>
              <Link 
                to="/loans" 
                className="text-sm text-orange-600 hover:text-orange-500 font-medium mt-2 inline-block"
              >
                View repayment schedule →
              </Link>
            </div>
          </div>
        </motion.div>
      )}

    </div>
  )
}

export default Dashboard