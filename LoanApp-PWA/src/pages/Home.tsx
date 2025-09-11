import { Link } from 'react-router-dom'
import { motion } from 'framer-motion'
import { useState } from 'react'

const Home = () => {
  const [isMenuOpen, setIsMenuOpen] = useState(false)

  const loanProducts = [
    {
      title: 'Personal Loan',
      description: 'Quick personal loans for your immediate needs',
      rate: '10.99%',
      amount: '₹50,000 - ₹25,00,000',
      tenure: 'Up to 5 years',
      icon: '👤',
      features: ['Instant approval', 'Minimal documentation', 'Flexible repayment']
    },
    {
      title: 'Business Loan',
      description: 'Fuel your business growth with our business loans',
      rate: '12.50%',
      amount: '₹1,00,000 - ₹75,00,000',
      tenure: 'Up to 7 years',
      icon: '🏢',
      features: ['Quick disbursement', 'Competitive rates', 'Easy EMI options']
    },
    {
      title: 'Home Loan',
      description: 'Make your dream home a reality',
      rate: '8.75%',
      amount: '₹5,00,000 - ₹5,00,00,000',
      tenure: 'Up to 30 years',
      icon: '🏠',
      features: ['Low interest rates', 'Long tenure', 'Tax benefits']
    },
    {
      title: 'Vehicle Loan',
      description: 'Drive your dream car or bike today',
      rate: '9.25%',
      amount: '₹1,00,000 - ₹1,50,00,000',
      tenure: 'Up to 7 years',
      icon: '🚗',
      features: ['Fast approval', 'Attractive rates', 'Flexible tenure']
    }
  ]

  const consumerItems = [
    {
      title: 'Mobile Phones',
      description: 'Latest smartphones with easy EMI options',
      image: '📱',
      emi: 'EMI starts at ₹999/month',
      offer: 'Up to 12 months EMI'
    },
    {
      title: 'Laptops & Electronics',
      description: 'Premium laptops and electronics',
      image: '💻',
      emi: 'EMI starts at ₹2,499/month',
      offer: 'Up to 24 months EMI'
    },
    {
      title: 'Home Appliances',
      description: 'Kitchen and home appliances',
      image: '🏠',
      emi: 'EMI starts at ₹1,299/month',
      offer: 'Up to 18 months EMI'
    },
    {
      title: 'Fashion & Lifestyle',
      description: 'Clothing, accessories and lifestyle products',
      image: '👕',
      emi: 'EMI starts at ₹499/month',
      offer: 'Up to 6 months EMI'
    }
  ]

  return (
    <div className="min-h-screen bg-white">
      {/* Header */}
      <header className="bg-white shadow-sm sticky top-0 z-50">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between items-center h-16">
            {/* Logo */}
            <div className="flex items-center">
              <img src="/upload/logo/logo_mbc.png" alt="MBC Finance" className="h-14 w-auto" />
            </div>
            
            {/* Desktop Navigation */}
            <nav className="hidden md:flex items-center space-x-8">
              <Link to="#loans" className="text-gray-700 hover:text-blue-600 font-medium transition-colors">
                Loans
              </Link>
              <Link to="#consumer" className="text-gray-700 hover:text-blue-600 font-medium transition-colors">
                Consumer Products
              </Link>
              <Link to="#about" className="text-gray-700 hover:text-blue-600 font-medium transition-colors">
                About Us
              </Link>
              <Link to="#contact" className="text-gray-700 hover:text-blue-600 font-medium transition-colors">
                Contact
              </Link>
            </nav>
            
            {/* Login/Register */}
            <div className="flex items-center space-x-4">
              <Link
                to="/login"
                className="text-gray-700 hover:text-blue-600 font-medium transition-colors"
              >
                Login
              </Link>
              <Link
                to="/login"
                className="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors font-medium"
              >
                Apply Now
              </Link>
              
              {/* Mobile menu button */}
              <button
                onClick={() => setIsMenuOpen(!isMenuOpen)}
                className="md:hidden p-2 rounded-md text-gray-700 hover:text-blue-600"
              >
                <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" />
                </svg>
              </button>
            </div>
          </div>
          
          {/* Mobile Navigation */}
          {isMenuOpen && (
            <div className="md:hidden py-4 border-t border-gray-200">
              <div className="flex flex-col space-y-4">
                <Link to="#loans" className="text-gray-700 hover:text-blue-600 font-medium">
                  Loans
                </Link>
                <Link to="#consumer" className="text-gray-700 hover:text-blue-600 font-medium">
                  Consumer Products
                </Link>
                <Link to="#about" className="text-gray-700 hover:text-blue-600 font-medium">
                  About Us
                </Link>
                <Link to="#contact" className="text-gray-700 hover:text-blue-600 font-medium">
                  Contact
                </Link>
              </div>
            </div>
          )}
        </div>
      </header>

      {/* Hero Section */}
      <section className="bg-gradient-to-r from-blue-600 to-blue-800 text-white">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <motion.div
              initial={{ opacity: 0, x: -50 }}
              animate={{ opacity: 1, x: 0 }}
              transition={{ duration: 0.8 }}
            >
              <h1 className="text-4xl md:text-6xl font-bold mb-6">
                Your Financial
                <span className="text-yellow-400"> Partner</span>
                <br />for Life
              </h1>
              
              <p className="text-xl text-blue-100 mb-8 leading-relaxed">
                Get instant loans, buy consumer products on EMI, and manage your finances with ease. 
                Experience the future of digital lending.
              </p>
              
              <div className="flex flex-col sm:flex-row gap-4">
                <Link
                  to="/login"
                  className="bg-yellow-400 text-blue-900 px-8 py-4 rounded-lg font-semibold hover:bg-yellow-300 transition-colors text-center"
                >
                  Apply for Loan
                </Link>
                <Link
                  to="#consumer"
                  className="border-2 border-white text-white px-8 py-4 rounded-lg font-semibold hover:bg-white hover:text-blue-600 transition-colors text-center"
                >
                  Shop on EMI
                </Link>
              </div>
            </motion.div>
            
            <motion.div
              initial={{ opacity: 0, x: 50 }}
              animate={{ opacity: 1, x: 0 }}
              transition={{ duration: 0.8, delay: 0.2 }}
              className="hidden lg:block"
            >
              <div className="bg-white/10 backdrop-blur-sm rounded-2xl p-8">
                <h3 className="text-2xl font-bold mb-6">Quick Loan Calculator</h3>
                <div className="space-y-4">
                  <div>
                    <label className="block text-sm font-medium mb-2">Loan Amount</label>
                    <input
                      type="range"
                      min="50000"
                      max="2500000"
                      defaultValue="500000"
                      className="w-full h-2 bg-blue-200 rounded-lg appearance-none cursor-pointer"
                    />
                    <div className="flex justify-between text-sm mt-1">
                      <span>₹50K</span>
                      <span className="font-semibold">₹5L</span>
                      <span>₹25L</span>
                    </div>
                  </div>
                  <div className="grid grid-cols-2 gap-4">
                    <div>
                      <label className="block text-sm font-medium mb-2">Tenure</label>
                      <select className="w-full p-3 rounded-lg bg-white/20 border border-white/30 text-white">
                        <option value="12">12 months</option>
                        <option value="24">24 months</option>
                        <option value="36">36 months</option>
                        <option value="60">60 months</option>
                      </select>
                    </div>
                    <div>
                      <label className="block text-sm font-medium mb-2">Interest Rate</label>
                      <div className="p-3 rounded-lg bg-white/20 border border-white/30">
                        <span className="text-lg font-bold">10.99%</span>
                      </div>
                    </div>
                  </div>
                  <div className="bg-yellow-400 text-blue-900 p-4 rounded-lg">
                    <div className="text-sm font-medium">Monthly EMI</div>
                    <div className="text-2xl font-bold">₹11,374</div>
                  </div>
                </div>
              </div>
            </motion.div>
          </div>
        </div>
      </section>
          
      {/* Loan Products Section */}
      <section id="loans" className="py-20 bg-gray-50">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.6 }}
            className="text-center mb-16"
          >
            <h2 className="text-4xl font-bold text-gray-900 mb-4">Our Loan Products</h2>
            <p className="text-xl text-gray-600 max-w-3xl mx-auto">
              Choose from our wide range of loan products designed to meet your financial needs
            </p>
          </motion.div>
          
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            {loanProducts.map((loan, index) => (
              <motion.div
                key={loan.title}
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.6, delay: index * 0.1 }}
                className="bg-white rounded-2xl shadow-lg hover:shadow-xl transition-shadow p-6 border border-gray-100"
              >
                <div className="text-4xl mb-4">{loan.icon}</div>
                <h3 className="text-xl font-bold text-gray-900 mb-2">{loan.title}</h3>
                <p className="text-gray-600 mb-4">{loan.description}</p>
                
                <div className="space-y-2 mb-6">
                  <div className="flex justify-between">
                    <span className="text-sm text-gray-500">Interest Rate</span>
                    <span className="text-sm font-semibold text-blue-600">{loan.rate} p.a.</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-sm text-gray-500">Amount</span>
                    <span className="text-sm font-semibold">{loan.amount}</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-sm text-gray-500">Tenure</span>
                    <span className="text-sm font-semibold">{loan.tenure}</span>
                  </div>
                </div>
                
                <div className="space-y-2 mb-6">
                  {loan.features.map((feature, idx) => (
                    <div key={idx} className="flex items-center text-sm text-gray-600">
                      <svg className="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                      </svg>
                      {feature}
                    </div>
                  ))}
                </div>
                
                <Link
                  to="/login"
                  className="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors text-center block"
                >
                  Apply Now
                </Link>
              </motion.div>
            ))}
          </div>
        </div>
      </section>

      {/* Consumer Products Section */}
      <section id="consumer" className="py-20 bg-white">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.6 }}
            className="text-center mb-16"
          >
            <h2 className="text-4xl font-bold text-gray-900 mb-4">Shop on EMI</h2>
            <p className="text-xl text-gray-600 max-w-3xl mx-auto">
              Buy your favorite products and pay in easy monthly installments with zero down payment
            </p>
          </motion.div>
          
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            {consumerItems.map((item, index) => (
              <motion.div
                key={item.title}
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.6, delay: index * 0.1 }}
                className="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl p-6 hover:shadow-lg transition-shadow border border-blue-200"
              >
                <div className="text-5xl mb-4 text-center">{item.image}</div>
                <h3 className="text-xl font-bold text-gray-900 mb-2">{item.title}</h3>
                <p className="text-gray-600 mb-4">{item.description}</p>
                
                <div className="bg-white rounded-lg p-4 mb-4">
                  <div className="text-lg font-bold text-blue-600 mb-1">{item.emi}</div>
                  <div className="text-sm text-gray-500">{item.offer}</div>
                </div>
                
                <button className="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors">
                  Shop Now
                </button>
              </motion.div>
            ))}
          </div>
        </div>
      </section>

      {/* Features Section */}
      <section className="py-20 bg-gray-50">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
            <motion.div
              initial={{ opacity: 0, x: -50 }}
              animate={{ opacity: 1, x: 0 }}
              transition={{ duration: 0.8 }}
            >
              <h2 className="text-4xl font-bold text-gray-900 mb-6">Why Choose MBC Finance?</h2>
              <p className="text-xl text-gray-600 mb-8">
                Experience the future of digital lending with our innovative platform
              </p>
              
              <div className="space-y-6">
                <div className="flex items-start">
                  <div className="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                    <svg className="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                  </div>
                  <div>
                    <h3 className="text-xl font-semibold text-gray-900 mb-2">Instant Approval</h3>
                    <p className="text-gray-600">Get your loan approved in minutes with our AI-powered system</p>
                  </div>
                </div>
                
                <div className="flex items-start">
                  <div className="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                    <svg className="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                  </div>
                  <div>
                    <h3 className="text-xl font-semibold text-gray-900 mb-2">100% Secure</h3>
                    <p className="text-gray-600">Bank-grade security with end-to-end encryption</p>
                  </div>
                </div>
                
                <div className="flex items-start">
                  <div className="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                    <svg className="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                    </svg>
                  </div>
                  <div>
                    <h3 className="text-xl font-semibold text-gray-900 mb-2">Competitive Rates</h3>
                    <p className="text-gray-600">Best interest rates in the market starting from 8.99%</p>
                  </div>
                </div>
              </div>
            </motion.div>
            
            <motion.div
              initial={{ opacity: 0, x: 50 }}
              animate={{ opacity: 1, x: 0 }}
              transition={{ duration: 0.8, delay: 0.2 }}
              className="grid grid-cols-2 gap-6"
            >
              <div className="bg-white rounded-2xl p-6 text-center shadow-lg">
                <div className="text-3xl font-bold text-blue-600 mb-2">50K+</div>
                <div className="text-gray-600">Happy Customers</div>
              </div>
              <div className="bg-white rounded-2xl p-6 text-center shadow-lg">
                <div className="text-3xl font-bold text-blue-600 mb-2">₹500Cr+</div>
                <div className="text-gray-600">Loans Disbursed</div>
              </div>
              <div className="bg-white rounded-2xl p-6 text-center shadow-lg">
                <div className="text-3xl font-bold text-blue-600 mb-2">2 Min</div>
                <div className="text-gray-600">Approval Time</div>
              </div>
              <div className="bg-white rounded-2xl p-6 text-center shadow-lg">
                <div className="text-3xl font-bold text-blue-600 mb-2">99.9%</div>
                <div className="text-gray-600">Uptime</div>
              </div>
            </motion.div>
          </div>
        </div>
      </section>

      {/* CTA Section */}
      <section className="py-20 bg-gradient-to-r from-blue-600 to-blue-800 text-white">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.6 }}
          >
            <h2 className="text-4xl font-bold mb-4">Ready to Get Started?</h2>
            <p className="text-xl text-blue-100 mb-8 max-w-2xl mx-auto">
              Join thousands of satisfied customers who trust MBC Finance for their financial needs
            </p>
            
            <div className="flex flex-col sm:flex-row gap-4 justify-center">
              <Link
                to="/login"
                className="bg-yellow-400 text-blue-900 px-8 py-4 rounded-lg font-semibold hover:bg-yellow-300 transition-colors"
              >
                Apply for Loan Now
              </Link>
              <button className="border-2 border-white text-white px-8 py-4 rounded-lg font-semibold hover:bg-white hover:text-blue-600 transition-colors">
                Download App
              </button>
            </div>
          </motion.div>
        </div>
      </section>

      {/* Footer */}
      <footer className="bg-gray-900 text-white py-16">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div>
              <img src="/upload/logo/logo_mbc.png" alt="MBC Finance" className="h-14 w-auto mb-4" />
              <p className="text-gray-400 mb-4">
                Your trusted financial partner for all your lending and consumer finance needs.
              </p>
              <div className="flex space-x-4">
                <a href="#" className="text-gray-400 hover:text-white transition-colors">
                  <svg className="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/>
                  </svg>
                </a>
                <a href="#" className="text-gray-400 hover:text-white transition-colors">
                  <svg className="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M22.46 6c-.77.35-1.6.58-2.46.69.88-.53 1.56-1.37 1.88-2.38-.83.5-1.75.85-2.72 1.05C18.37 4.5 17.26 4 16 4c-2.35 0-4.27 1.92-4.27 4.29 0 .34.04.67.11.98C8.28 9.09 5.11 7.38 3 4.79c-.37.63-.58 1.37-.58 2.15 0 1.49.75 2.81 1.91 3.56-.71 0-1.37-.2-1.95-.5v.03c0 2.08 1.48 3.82 3.44 4.21a4.22 4.22 0 0 1-1.93.07 4.28 4.28 0 0 0 4 2.98 8.521 8.521 0 0 1-5.33 1.84c-.34 0-.68-.02-1.02-.06C3.44 20.29 5.7 21 8.12 21 16 21 20.33 14.46 20.33 8.79c0-.19 0-.37-.01-.56.84-.6 1.56-1.36 2.14-2.23z"/>
                  </svg>
                </a>
                <a href="#" className="text-gray-400 hover:text-white transition-colors">
                  <svg className="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                  </svg>
                </a>
              </div>
            </div>
            
            <div>
              <h3 className="text-lg font-semibold mb-4">Loans</h3>
              <ul className="space-y-2 text-gray-400">
                <li><a href="#" className="hover:text-white transition-colors">Personal Loan</a></li>
                <li><a href="#" className="hover:text-white transition-colors">Business Loan</a></li>
                <li><a href="#" className="hover:text-white transition-colors">Home Loan</a></li>
                <li><a href="#" className="hover:text-white transition-colors">Vehicle Loan</a></li>
              </ul>
            </div>
            
            <div>
              <h3 className="text-lg font-semibold mb-4">Consumer Products</h3>
              <ul className="space-y-2 text-gray-400">
                <li><a href="#" className="hover:text-white transition-colors">Mobile Phones</a></li>
                <li><a href="#" className="hover:text-white transition-colors">Electronics</a></li>
                <li><a href="#" className="hover:text-white transition-colors">Home Appliances</a></li>
                <li><a href="#" className="hover:text-white transition-colors">Fashion</a></li>
              </ul>
            </div>
            
            <div>
              <h3 className="text-lg font-semibold mb-4">Support</h3>
              <ul className="space-y-2 text-gray-400">
                <li><a href="#" className="hover:text-white transition-colors">Help Center</a></li>
                <li><a href="#" className="hover:text-white transition-colors">Contact Us</a></li>
                <li><a href="#" className="hover:text-white transition-colors">Privacy Policy</a></li>
                <li><a href="#" className="hover:text-white transition-colors">Terms of Service</a></li>
              </ul>
            </div>
          </div>
          
          <div className="border-t border-gray-800 mt-12 pt-8 text-center text-gray-400">
            <p>&copy; 2024 MBC Finance. All rights reserved. | Licensed by RBI</p>
          </div>
        </div>
      </footer>
    </div>
  )
}

export default Home