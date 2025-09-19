import React, { useState, useEffect } from 'react'
import { motion } from 'framer-motion'

interface CalculationResult {
  emi: number
  totalAmount: number
  totalInterest: number
  schedule: {
    month: number
    emi: number
    principal: number
    interest: number
    balance: number
  }[]
}

export default function EMICalculatorPage() {
  const [loanAmount, setLoanAmount] = useState(100000)
  const [interestRate, setInterestRate] = useState(12)
  const [tenure, setTenure] = useState(12)
  const [result, setResult] = useState<CalculationResult | null>(null)
  const [loading, setLoading] = useState(false)

  const calculateEMI = () => {
    setLoading(true)
    
    // EMI calculation formula: EMI = [P x R x (1+R)^N] / [(1+R)^N-1]
    const principal = loanAmount
    const monthlyRate = interestRate / (12 * 100)
    const numberOfPayments = tenure
    
    const emi = (principal * monthlyRate * Math.pow(1 + monthlyRate, numberOfPayments)) / 
                (Math.pow(1 + monthlyRate, numberOfPayments) - 1)
    
    const totalAmount = emi * numberOfPayments
    const totalInterest = totalAmount - principal
    
    // Generate amortization schedule
    const schedule = []
    let balance = principal
    
    for (let i = 1; i <= numberOfPayments; i++) {
      const interestPayment = balance * monthlyRate
      const principalPayment = emi - interestPayment
      balance = balance - principalPayment
      
      schedule.push({
        month: i,
        emi: Math.round(emi),
        principal: Math.round(principalPayment),
        interest: Math.round(interestPayment),
        balance: Math.round(Math.max(0, balance))
      })
    }
    
    setResult({
      emi: Math.round(emi),
      totalAmount: Math.round(totalAmount),
      totalInterest: Math.round(totalInterest),
      schedule
    })
    
    setTimeout(() => setLoading(false), 500)
  }

  useEffect(() => {
    calculateEMI()
  }, [loanAmount, interestRate, tenure])

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-IN', {
      style: 'currency',
      currency: 'INR',
      maximumFractionDigits: 0
    }).format(amount)
  }

  return (
    <div className="min-h-screen bg-gradient-to-br from-blue-50 to-white">
      {/* Header */}
      <div className="bg-white shadow-sm border-b">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex items-center justify-between h-16">
            <div className="flex items-center">
              <button 
                onClick={() => window.history.back()}
                className="mr-4 p-2 rounded-lg hover:bg-gray-100 transition-colors"
              >
                <svg className="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
                </svg>
              </button>
              <div>
                <h1 className="text-xl font-semibold text-gray-900">EMI Calculator</h1>
                <p className="text-sm text-gray-500">Calculate your loan EMI</p>
              </div>
            </div>
            <div className="flex items-center space-x-2">
              <img 
                src="/logo-192x192.png" 
                alt="MBC FinServ" 
                className="h-8 w-8"
                onError={(e) => { e.currentTarget.style.display = 'none' }}
              />
              <span className="text-lg font-bold text-blue-600">MBC FinServ</span>
            </div>
          </div>
        </div>
      </div>

      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
          {/* Calculator Form */}
          <motion.div 
            initial={{ opacity: 0, x: -20 }}
            animate={{ opacity: 1, x: 0 }}
            className="bg-white rounded-xl shadow-sm border border-gray-200 p-6"
          >
            <h2 className="text-xl font-semibold text-gray-900 mb-6">Loan Details</h2>
            
            <div className="space-y-6">
              {/* Loan Amount */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Loan Amount
                </label>
                <div className="relative">
                  <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span className="text-gray-500 sm:text-sm">₹</span>
                  </div>
                  <input
                    type="number"
                    value={loanAmount}
                    onChange={(e) => setLoanAmount(Number(e.target.value))}
                    className="block w-full pl-8 pr-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="100000"
                    min="1000"
                    max="10000000"
                  />
                </div>
                <input
                  type="range"
                  min="1000"
                  max="1000000"
                  value={loanAmount}
                  onChange={(e) => setLoanAmount(Number(e.target.value))}
                  className="w-full mt-2 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer slider"
                />
                <div className="flex justify-between text-xs text-gray-500 mt-1">
                  <span>₹1K</span>
                  <span>₹10L</span>
                </div>
              </div>

              {/* Interest Rate */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Interest Rate (per annum)
                </label>
                <div className="relative">
                  <input
                    type="number"
                    value={interestRate}
                    onChange={(e) => setInterestRate(Number(e.target.value))}
                    className="block w-full px-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="12"
                    min="1"
                    max="50"
                    step="0.1"
                  />
                  <div className="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <span className="text-gray-500 sm:text-sm">%</span>
                  </div>
                </div>
                <input
                  type="range"
                  min="1"
                  max="50"
                  value={interestRate}
                  onChange={(e) => setInterestRate(Number(e.target.value))}
                  className="w-full mt-2 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer slider"
                  step="0.1"
                />
                <div className="flex justify-between text-xs text-gray-500 mt-1">
                  <span>1%</span>
                  <span>50%</span>
                </div>
              </div>

              {/* Tenure */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Loan Tenure
                </label>
                <div className="relative">
                  <input
                    type="number"
                    value={tenure}
                    onChange={(e) => setTenure(Number(e.target.value))}
                    className="block w-full px-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="12"
                    min="1"
                    max="360"
                  />
                  <div className="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <span className="text-gray-500 sm:text-sm">months</span>
                  </div>
                </div>
                <input
                  type="range"
                  min="1"
                  max="360"
                  value={tenure}
                  onChange={(e) => setTenure(Number(e.target.value))}
                  className="w-full mt-2 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer slider"
                />
                <div className="flex justify-between text-xs text-gray-500 mt-1">
                  <span>1 month</span>
                  <span>30 years</span>
                </div>
              </div>
            </div>

            {/* Quick Tenure Options */}
            <div className="mt-6">
              <p className="text-sm font-medium text-gray-700 mb-3">Quick Select Tenure</p>
              <div className="grid grid-cols-4 gap-2">
                {[12, 24, 36, 60].map((months) => (
                  <button
                    key={months}
                    onClick={() => setTenure(months)}
                    className={`px-3 py-2 rounded-lg text-sm font-medium transition-colors ${
                      tenure === months
                        ? 'bg-blue-600 text-white'
                        : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                    }`}
                  >
                    {months}m
                  </button>
                ))}
              </div>
            </div>
          </motion.div>

          {/* Results */}
          <motion.div 
            initial={{ opacity: 0, x: 20 }}
            animate={{ opacity: 1, x: 0 }}
            transition={{ delay: 0.1 }}
            className="space-y-6"
          >
            {/* EMI Result Card */}
            <div className="bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl shadow-lg p-6">
              <h3 className="text-lg font-semibold mb-4">Monthly EMI</h3>
              {loading ? (
                <div className="animate-pulse">
                  <div className="h-8 bg-blue-500 rounded w-32 mb-2"></div>
                  <div className="h-4 bg-blue-500 rounded w-48"></div>
                </div>
              ) : result ? (
                <div>
                  <p className="text-3xl font-bold mb-2">{formatCurrency(result.emi)}</p>
                  <p className="text-blue-100">per month for {tenure} months</p>
                </div>
              ) : null}
            </div>

            {/* Summary Cards */}
            {result && (
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                  <div className="flex items-center">
                    <div className="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                      <svg className="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                      </svg>
                    </div>
                    <div className="ml-4">
                      <p className="text-sm font-medium text-gray-500">Principal Amount</p>
                      <p className="text-xl font-bold text-gray-900">{formatCurrency(loanAmount)}</p>
                    </div>
                  </div>
                </div>

                <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                  <div className="flex items-center">
                    <div className="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                      <svg className="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                      </svg>
                    </div>
                    <div className="ml-4">
                      <p className="text-sm font-medium text-gray-500">Total Interest</p>
                      <p className="text-xl font-bold text-gray-900">{formatCurrency(result.totalInterest)}</p>
                    </div>
                  </div>
                </div>

                <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sm:col-span-2">
                  <div className="flex items-center">
                    <div className="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                      <svg className="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                      </svg>
                    </div>
                    <div className="ml-4">
                      <p className="text-sm font-medium text-gray-500">Total Amount Payable</p>
                      <p className="text-2xl font-bold text-gray-900">{formatCurrency(result.totalAmount)}</p>
                    </div>
                  </div>
                </div>
              </div>
            )}

            {/* Apply for Loan Button */}
            <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
              <h3 className="text-lg font-semibold text-gray-900 mb-4">Ready to Apply?</h3>
              <p className="text-gray-600 mb-4">
                Get pre-approved for your loan with competitive interest rates and flexible tenure options.
              </p>
              <button
                onClick={() => window.location.href = '/pwa/loan-application'}
                className="w-full bg-blue-600 text-white py-3 px-6 rounded-lg font-medium hover:bg-blue-700 transition-colors"
              >
                Apply for Loan
              </button>
            </div>
          </motion.div>
        </div>

        {/* Amortization Schedule */}
        {result && result.schedule.length > 0 && (
          <motion.div 
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: 0.3 }}
            className="mt-8 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
          >
            <div className="px-6 py-4 border-b border-gray-200">
              <h3 className="text-lg font-semibold text-gray-900">Amortization Schedule</h3>
              <p className="text-sm text-gray-500 mt-1">Monthly breakdown of principal and interest</p>
            </div>

            <div className="overflow-x-auto max-h-96">
              <table className="w-full">
                <thead className="bg-gray-50 sticky top-0">
                  <tr>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Month</th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">EMI</th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Principal</th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Interest</th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                  </tr>
                </thead>
                <tbody className="bg-white divide-y divide-gray-200">
                  {result.schedule.map((row, index) => (
                    <tr key={index} className="hover:bg-gray-50">
                      <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {row.month}
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        ₹{row.emi.toLocaleString()}
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        ₹{row.principal.toLocaleString()}
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        ₹{row.interest.toLocaleString()}
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        ₹{row.balance.toLocaleString()}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </motion.div>
        )}
      </div>

      <style jsx>{`
        .slider::-webkit-slider-thumb {
          appearance: none;
          height: 20px;
          width: 20px;
          border-radius: 50%;
          background: #3b82f6;
          cursor: pointer;
          border: 2px solid #ffffff;
          box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .slider::-moz-range-thumb {
          height: 20px;
          width: 20px;
          border-radius: 50%;
          background: #3b82f6;
          cursor: pointer;
          border: 2px solid #ffffff;
          box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
      `}</style>
    </div>
  )
}