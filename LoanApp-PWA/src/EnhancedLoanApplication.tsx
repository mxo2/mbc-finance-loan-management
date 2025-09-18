import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';

// Enhanced Loan Application Component with Multi-Step Wizard
export function EnhancedLoanApplication() {
  // Step management
  const [currentStep, setCurrentStep] = useState(1);
  const totalSteps = 6;
  
  // Form data state
  const [formData, setFormData] = useState({
    loanType: '',
    amount: '2000000',
    tenure: '12',
    purpose: '',
    purposeOther: '',
    income: '',
    employment: '',
    documents: [],
    referralCode: '',
  });
  
  // Application state
  const [loanTypes, setLoanTypes] = useState<any[]>([]);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [loading, setLoading] = useState(true);
  const [message, setMessage] = useState('');
  const [selectedLoanType, setSelectedLoanType] = useState<any>(null);
  const [calculatedEMI, setCalculatedEMI] = useState(0);
  const [totalInterest, setTotalInterest] = useState(0);
  const [totalRepayment, setTotalRepayment] = useState(0);
  
  // Uploaded documents
  const [uploadedDocuments, setUploadedDocuments] = useState<{name: string, size: string, type: string}[]>([]);
  
  const token = localStorage.getItem('auth_token');
  const navigate = useNavigate();
  
  // Tenure options in months
  const tenureOptions = [3, 6, 9, 12, 18, 24, 36, 48, 60, 72, 84, 96];
  
  // Purpose options
  const purposeOptions = [
    'Home Renovation',
    'Education',
    'Medical Expenses',
    'Debt Consolidation',
    'Wedding',
    'Travel',
    'Vehicle Purchase',
    'Business Expansion',
    'Other'
  ];
  
  // Format number with commas (Indian format)
  const formatNumber = (num: number) => {
    return num.toLocaleString('en-IN');
  };
  
  // Calculate EMI based on amount, tenure, and interest rate
  const calculateEMI = (principal: number, tenure: number, interestRate: number) => {
    if (!principal || !tenure || !interestRate) return 0;
    
    const monthlyRate = interestRate / (12 * 100);
    const emi = (principal * monthlyRate * Math.pow(1 + monthlyRate, tenure)) / 
                (Math.pow(1 + monthlyRate, tenure) - 1);
    
    return Math.round(emi);
  };
  
  // Calculate total interest and repayment
  const calculateTotals = (emi: number, tenure: number, principal: number) => {
    const totalAmount = emi * tenure;
    const interest = totalAmount - principal;
    
    setTotalInterest(interest);
    setTotalRepayment(totalAmount);
  };
  
  // Update EMI when amount, tenure, or loan type changes
  useEffect(() => {
    if (selectedLoanType && formData.amount && formData.tenure) {
      const amount = parseFloat(formData.amount);
      const tenure = parseInt(formData.tenure);
      const interestRate = selectedLoanType.interest_rate || 12;
      
      const emi = calculateEMI(amount, tenure, interestRate);
      setCalculatedEMI(emi);
      
      calculateTotals(emi, tenure, amount);
    }
  }, [formData.amount, formData.tenure, selectedLoanType]);
  
  // Fetch loan types on component mount
  useEffect(() => {
    const fetchLoanTypes = async () => {
      try {
        const response = await fetch('/api/loan-types', {
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
          }
        });
        
        const responseText = await response.text();
        let data;
        try {
          data = JSON.parse(responseText);
        } catch (parseError) {
          const jsonMatch = responseText.match(/\{.*\}$/s);
          if (jsonMatch) {
            data = JSON.parse(jsonMatch[0]);
          } else {
            throw new Error('No valid JSON found in response');
          }
        }
        
        if (response.ok && data.success) {
          setLoanTypes(data.loan_types || []);
          // Set first loan type as default if available
          if (data.loan_types && data.loan_types.length > 0) {
            const firstLoanType = data.loan_types[0];
            setFormData(prev => ({ ...prev, loanType: firstLoanType.id.toString() }));
            setSelectedLoanType(firstLoanType);
          }
        } else {
          // Fallback to default loan types if API fails
          const defaultTypes = [
            { id: 1, name: 'Personal Loan', description: 'For personal expenses', interest_rate: 12, max_amount: 7500000, min_amount: 200000 },
            { id: 2, name: 'Home Loan', description: 'For home purchase', interest_rate: 8.5, max_amount: 10000000, min_amount: 500000 },
            { id: 3, name: 'Car Loan', description: 'For vehicle purchase', interest_rate: 10, max_amount: 5000000, min_amount: 200000 },
            { id: 4, name: 'Business Loan', description: 'For business needs', interest_rate: 14, max_amount: 7500000, min_amount: 300000 }
          ];
          setLoanTypes(defaultTypes);
          setFormData(prev => ({ ...prev, loanType: '1' }));
          setSelectedLoanType(defaultTypes[0]);
        }
      } catch (error) {
        console.error('Error fetching loan types:', error);
        // Fallback to default loan types
        const defaultTypes = [
          { id: 1, name: 'Personal Loan', description: 'For personal expenses', interest_rate: 12, max_amount: 7500000, min_amount: 200000 },
          { id: 2, name: 'Home Loan', description: 'For home purchase', interest_rate: 8.5, max_amount: 10000000, min_amount: 500000 },
          { id: 3, name: 'Car Loan', description: 'For vehicle purchase', interest_rate: 10, max_amount: 5000000, min_amount: 200000 },
          { id: 4, name: 'Business Loan', description: 'For business needs', interest_rate: 14, max_amount: 7500000, min_amount: 300000 }
        ];
        setLoanTypes(defaultTypes);
        setFormData(prev => ({ ...prev, loanType: '1' }));
        setSelectedLoanType(defaultTypes[0]);
      } finally {
        setLoading(false);
      }
    };
    
    fetchLoanTypes();
  }, [token]);
  
  // Handle loan type selection
  const handleLoanTypeChange = (id: string) => {
    const selected = loanTypes.find(type => type.id.toString() === id);
    
    setFormData(prev => ({ 
      ...prev, 
      loanType: id,
      // Reset amount to min/max bounds if needed
      amount: selected && prev.amount ? 
        Math.min(
          Math.max(parseInt(prev.amount), selected.min_amount || 200000), 
          selected.max_amount || 7500000
        ).toString() : prev.amount
    }));
    
    setSelectedLoanType(selected);
  };
  
  // Handle amount change
  const handleAmountChange = (value: string) => {
    // Remove all non-numeric characters
    const numericValue = value.replace(/[^0-9]/g, '');
    
    if (numericValue && selectedLoanType) {
      const numValue = parseInt(numericValue);
      const minAmount = selectedLoanType.min_amount || 200000;
      const maxAmount = selectedLoanType.max_amount || 7500000;
      
      // Ensure amount is within valid range
      const boundedValue = Math.min(Math.max(numValue, minAmount), maxAmount);
      
      setFormData(prev => ({ ...prev, amount: boundedValue.toString() }));
    } else {
      setFormData(prev => ({ ...prev, amount: numericValue }));
    }
  };
  
  // Handle tenure selection
  const handleTenureChange = (months: number) => {
    setFormData(prev => ({ ...prev, tenure: months.toString() }));
  };
  
  // Handle form input changes
  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement>) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
  };
  
  // Handle file upload
  const handleFileUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
    const files = e.target.files;
    if (files && files.length > 0) {
      const newFiles = Array.from(files).map(file => ({
        name: file.name,
        size: (file.size / 1024 < 1024) 
          ? `${(file.size / 1024).toFixed(1)} KB` 
          : `${(file.size / 1024 / 1024).toFixed(1)} MB`,
        type: file.type
      }));
      
      setUploadedDocuments(prev => [...prev, ...newFiles]);
    }
  };
  
  // Navigate to previous step
  const handlePrevStep = () => {
    if (currentStep > 1) {
      setCurrentStep(prev => prev - 1);
    }
  };
  
  // Navigate to next step
  const handleNextStep = () => {
    // Add validation logic here
    if (currentStep < totalSteps) {
      setCurrentStep(prev => prev + 1);
    }
  };
  
  // Handle form submission
  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (isSubmitting) return;
    
    setIsSubmitting(true);
    setMessage('');
    
    try {
      // Here you would send the form data to your backend API
      const response = await fetch('/api/apply-loan', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify({
          loan_type_id: formData.loanType,
          amount: formData.amount,
          tenure: formData.tenure,
          purpose_of_loan: formData.purpose === 'Other' ? 'Other' : formData.purpose,
          custom_purpose: formData.purpose === 'Other' ? formData.purposeOther : '',
          income: formData.income,
          employment_type: formData.employment,
          referral_code: formData.referralCode || null
          // For a real implementation, you would upload documents separately
        })
      });
      
      const responseText = await response.text();
      let data;
      try {
        data = JSON.parse(responseText);
      } catch (parseError) {
        const jsonMatch = responseText.match(/\{.*\}$/s);
        if (jsonMatch) {
          data = JSON.parse(jsonMatch[0]);
        } else {
          throw new Error('No valid JSON found in response');
        }
      }
      
      if (response.ok && data.success) {
        setMessage('Loan application submitted successfully!');
        // Redirect to dashboard or loan status page
        setTimeout(() => {
          navigate('/pwa/dashboard');
        }, 2000);
      } else {
        setMessage(data.message || 'Failed to submit loan application. Please try again.');
      }
    } catch (error) {
      console.error('Error submitting loan application:', error);
      setMessage('Network error. Please check your connection and try again.');
    } finally {
      setIsSubmitting(false);
    }
  };
  
  // Render loading state
  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-600"></div>
      </div>
    );
  }
  
  // Render progress indicator
  const renderProgressBar = () => {
    const steps = [
      { step: 1, name: "Loan Type" },
      { step: 2, name: "Amount" },
      { step: 3, name: "Term" },
      { step: 4, name: "EMI" },
      { step: 5, name: "Details" },
      { step: 6, name: "Review" }
    ];
    
    return (
      <div className="mb-8">
        <div className="flex justify-between mb-2">
          {steps.map((step) => (
            <div 
              key={step.step}
              className={`relative flex flex-col items-center ${
                step.step < currentStep ? 'text-green-600' : 
                step.step === currentStep ? 'text-blue-600' : 'text-gray-400'
              }`}
            >
              <div className={`w-10 h-10 rounded-full flex items-center justify-center border-2 ${
                step.step < currentStep ? 'bg-green-100 border-green-600' : 
                step.step === currentStep ? 'bg-blue-100 border-blue-600' : 'bg-gray-100 border-gray-400'
              }`}>
                {step.step < currentStep ? (
                  <svg className="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                  </svg>
                ) : (
                  <span className="text-sm font-medium">{step.step}</span>
                )}
              </div>
              <span className="text-xs mt-1 font-medium">{step.name}</span>
            </div>
          ))}
        </div>
        
        <div className="relative w-full bg-gray-200 h-2 rounded-full mt-2">
          <div 
            className="absolute top-0 left-0 h-2 bg-blue-600 rounded-full"
            style={{ width: `${((currentStep - 1) / (totalSteps - 1)) * 100}%` }}
          ></div>
        </div>
      </div>
    );
  };
  
  // Render step 1: Loan Type Selection
  const renderLoanTypeStep = () => (
    <div className="bg-white rounded-lg shadow-md p-8">
      <h2 className="text-2xl font-bold text-gray-900 mb-6">Select Loan Type</h2>
      
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        {loanTypes.map((loanType) => (
          <div 
            key={loanType.id}
            onClick={() => handleLoanTypeChange(loanType.id.toString())}
            className={`cursor-pointer border-2 rounded-lg p-6 transition-all transform hover:scale-105 ${
              formData.loanType === loanType.id.toString() 
                ? 'border-blue-600 bg-blue-50' 
                : 'border-gray-200 hover:border-blue-300'
            }`}
          >
            <div className="flex flex-col h-full">
              <h3 className="text-xl font-bold text-gray-900 mb-2">{loanType.name}</h3>
              <p className="text-gray-600 mb-4 flex-grow">{loanType.description}</p>
              <div className="space-y-2">
                <p className="text-sm flex items-center justify-between">
                  <span className="text-gray-500">Interest Rate</span>
                  <span className="font-semibold text-blue-700">{loanType.interest_rate}% p.a.</span>
                </p>
                <p className="text-sm flex items-center justify-between">
                  <span className="text-gray-500">Max Amount</span>
                  <span className="font-semibold text-blue-700">₹{formatNumber(loanType.max_amount || 7500000)}</span>
                </p>
              </div>
            </div>
          </div>
        ))}
      </div>
      
      <div className="flex justify-end mt-8">
        <button
          type="button"
          onClick={handleNextStep}
          disabled={!formData.loanType}
          className={`px-6 py-3 rounded-lg font-medium ${
            formData.loanType 
              ? 'bg-blue-600 text-white hover:bg-blue-700' 
              : 'bg-gray-300 text-gray-500 cursor-not-allowed'
          }`}
        >
          Continue
        </button>
      </div>
    </div>
  );
  
  // Render step 2: Loan Amount Selection
  const renderAmountStep = () => {
    const minAmount = selectedLoanType?.min_amount || 200000;
    const maxAmount = selectedLoanType?.max_amount || 7500000;
    const amount = parseInt(formData.amount) || minAmount;
    
    return (
      <div className="bg-white rounded-lg shadow-md p-8">
        <h2 className="text-2xl font-bold text-gray-900 mb-6">Select Loan Amount</h2>
        
        <div className="bg-blue-50 rounded-lg p-6 mb-8">
          <div className="flex items-center mb-4">
            <span className="text-3xl font-bold text-gray-600 mr-2">₹</span>
            <input
              type="text"
              value={formatNumber(amount)}
              onChange={(e) => handleAmountChange(e.target.value)}
              className="text-5xl font-bold bg-transparent border-none outline-none flex-1 text-gray-900"
              placeholder="20,00,000"
            />
          </div>
          
          <input
            type="range"
            min={minAmount}
            max={maxAmount}
            step={10000}
            value={amount}
            onChange={(e) => handleAmountChange(e.target.value)}
            className="w-full h-4 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-blue-600"
          />
          
          <div className="flex justify-between mt-2 text-sm text-gray-600">
            <span>₹{formatNumber(minAmount)}</span>
            <span>₹{formatNumber(maxAmount)}</span>
          </div>
        </div>
        
        <p className="text-gray-600 mb-8">
          You selected <span className="font-semibold text-blue-700">₹{formatNumber(amount)}</span> for your {selectedLoanType?.name}
        </p>
        
        <div className="flex justify-between mt-8">
          <button
            type="button"
            onClick={handlePrevStep}
            className="px-6 py-3 rounded-lg font-medium bg-gray-200 text-gray-800 hover:bg-gray-300"
          >
            Back
          </button>
          <button
            type="button"
            onClick={handleNextStep}
            className="px-6 py-3 rounded-lg font-medium bg-blue-600 text-white hover:bg-blue-700"
          >
            Continue
          </button>
        </div>
      </div>
    );
  };
  
  // Render step 3: Loan Tenure Selection
  const renderTenureStep = () => {
    const selectedTenure = parseInt(formData.tenure);
    
    return (
      <div className="bg-white rounded-lg shadow-md p-8">
        <h2 className="text-2xl font-bold text-gray-900 mb-6">Select Loan Tenure</h2>
        
        <p className="text-gray-600 mb-6">
          Choose how long you need to repay your loan
        </p>
        
        <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 mb-8">
          {tenureOptions.map((months) => (
            <button
              key={months}
              type="button"
              onClick={() => handleTenureChange(months)}
              className={`py-4 px-2 rounded-lg border-2 text-center ${
                selectedTenure === months
                  ? 'border-blue-600 bg-blue-50 text-blue-700'
                  : 'border-gray-200 text-gray-700 hover:border-blue-300'
              }`}
            >
              <div className="text-lg font-bold">{months}</div>
              <div className="text-sm">Months</div>
            </button>
          ))}
        </div>
        
        <p className="text-gray-600 mb-8">
          You selected <span className="font-semibold text-blue-700">{selectedTenure} months</span> tenure for your loan
        </p>
        
        <div className="flex justify-between mt-8">
          <button
            type="button"
            onClick={handlePrevStep}
            className="px-6 py-3 rounded-lg font-medium bg-gray-200 text-gray-800 hover:bg-gray-300"
          >
            Back
          </button>
          <button
            type="button"
            onClick={handleNextStep}
            className="px-6 py-3 rounded-lg font-medium bg-blue-600 text-white hover:bg-blue-700"
          >
            Continue
          </button>
        </div>
      </div>
    );
  };
  
  // Render step 4: EMI Calculation
  const renderEMIStep = () => {
    return (
      <div className="bg-white rounded-lg shadow-md p-8">
        <h2 className="text-2xl font-bold text-gray-900 mb-6">Your Loan Summary</h2>
        
        <div className="bg-blue-50 rounded-lg p-6 mb-8">
          <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div className="bg-white rounded-lg p-4 shadow-sm">
              <div className="text-gray-500 text-sm mb-1">Monthly EMI</div>
              <div className="text-3xl font-bold text-blue-700">₹{formatNumber(calculatedEMI)}</div>
            </div>
            
            <div className="bg-white rounded-lg p-4 shadow-sm">
              <div className="text-gray-500 text-sm mb-1">Total Interest</div>
              <div className="text-3xl font-bold text-blue-700">₹{formatNumber(totalInterest)}</div>
            </div>
            
            <div className="bg-white rounded-lg p-4 shadow-sm">
              <div className="text-gray-500 text-sm mb-1">Total Repayment</div>
              <div className="text-3xl font-bold text-blue-700">₹{formatNumber(totalRepayment)}</div>
            </div>
          </div>
        </div>
        
        <div className="bg-gray-100 rounded-lg p-6">
          <h3 className="text-lg font-semibold mb-4">Loan Details</h3>
          
          <div className="space-y-3">
            <div className="flex justify-between">
              <span className="text-gray-600">Loan Type</span>
              <span className="font-medium">{selectedLoanType?.name}</span>
            </div>
            
            <div className="flex justify-between">
              <span className="text-gray-600">Loan Amount</span>
              <span className="font-medium">₹{formatNumber(parseInt(formData.amount))}</span>
            </div>
            
            <div className="flex justify-between">
              <span className="text-gray-600">Tenure</span>
              <span className="font-medium">{formData.tenure} Months</span>
            </div>
            
            <div className="flex justify-between">
              <span className="text-gray-600">Interest Rate</span>
              <span className="font-medium">{selectedLoanType?.interest_rate}% p.a.</span>
            </div>
          </div>
        </div>
        
        <div className="flex justify-between mt-8">
          <button
            type="button"
            onClick={handlePrevStep}
            className="px-6 py-3 rounded-lg font-medium bg-gray-200 text-gray-800 hover:bg-gray-300"
          >
            Back
          </button>
          <button
            type="button"
            onClick={handleNextStep}
            className="px-6 py-3 rounded-lg font-medium bg-blue-600 text-white hover:bg-blue-700"
          >
            Continue
          </button>
        </div>
      </div>
    );
  };
  
  // Render step 5: Loan Details Form
  const renderDetailsStep = () => {
    return (
      <div className="bg-white rounded-lg shadow-md p-8">
        <h2 className="text-2xl font-bold text-gray-900 mb-6">Loan Details</h2>
        
        <div className="space-y-6">
          {/* Purpose of Loan */}
          <div>
            <label htmlFor="purpose" className="block text-gray-700 font-medium mb-2">
              Purpose of Loan <span className="text-red-500">*</span>
            </label>
            <select
              id="purpose"
              name="purpose"
              value={formData.purpose}
              onChange={handleInputChange}
              className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
              required
            >
              <option value="">Select Purpose</option>
              {purposeOptions.map((purpose) => (
                <option key={purpose} value={purpose}>{purpose}</option>
              ))}
            </select>
          </div>
          
          {/* Custom Purpose (if "Other" is selected) */}
          {formData.purpose === 'Other' && (
            <div>
              <label htmlFor="purposeOther" className="block text-gray-700 font-medium mb-2">
                Specify Purpose <span className="text-red-500">*</span>
              </label>
              <input
                type="text"
                id="purposeOther"
                name="purposeOther"
                value={formData.purposeOther}
                onChange={handleInputChange}
                className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                placeholder="Please specify your loan purpose"
                required
              />
            </div>
          )}
          
          {/* Monthly Income */}
          <div>
            <label htmlFor="income" className="block text-gray-700 font-medium mb-2">
              Monthly Income (₹) <span className="text-red-500">*</span>
            </label>
            <input
              type="number"
              id="income"
              name="income"
              value={formData.income}
              onChange={handleInputChange}
              className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
              placeholder="Enter your monthly income"
              required
            />
          </div>
          
          {/* Employment Type */}
          <div>
            <label htmlFor="employment" className="block text-gray-700 font-medium mb-2">
              Employment Type <span className="text-red-500">*</span>
            </label>
            <select
              id="employment"
              name="employment"
              value={formData.employment}
              onChange={handleInputChange}
              className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
              required
            >
              <option value="">Select Employment Type</option>
              <option value="Salaried">Salaried</option>
              <option value="Self-Employed">Self-Employed</option>
              <option value="Business Owner">Business Owner</option>
              <option value="Freelancer">Freelancer</option>
              <option value="Retired">Retired</option>
            </select>
          </div>
          
          {/* Document Upload */}
          <div>
            <label className="block text-gray-700 font-medium mb-2">
              Upload Documents <span className="text-red-500">*</span>
            </label>
            <div className="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
              <input
                type="file"
                id="documents"
                onChange={handleFileUpload}
                className="hidden"
                multiple
              />
              <label htmlFor="documents" className="cursor-pointer">
                <div className="flex flex-col items-center">
                  <svg className="w-12 h-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                  </svg>
                  <p className="text-gray-700 font-medium">Drag & drop files or click to browse</p>
                  <p className="text-gray-500 text-sm mt-1">Upload ID proof, address proof, income proof</p>
                </div>
              </label>
            </div>
          </div>
          
          {/* Display uploaded files */}
          {uploadedDocuments.length > 0 && (
            <div className="bg-gray-100 rounded-lg p-4">
              <h3 className="font-medium text-gray-700 mb-2">Uploaded Documents</h3>
              <ul className="space-y-2">
                {uploadedDocuments.map((doc, index) => (
                  <li key={index} className="flex items-center justify-between bg-white p-3 rounded-md">
                    <div className="flex items-center">
                      <svg className="w-5 h-5 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fillRule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clipRule="evenodd" />
                      </svg>
                      <span className="text-gray-700">{doc.name}</span>
                    </div>
                    <span className="text-gray-500 text-sm">{doc.size}</span>
                  </li>
                ))}
              </ul>
            </div>
          )}
          
          {/* Referral Code */}
          <div>
            <label htmlFor="referralCode" className="block text-gray-700 font-medium mb-2">
              Referral Code (Optional)
            </label>
            <input
              type="text"
              id="referralCode"
              name="referralCode"
              value={formData.referralCode}
              onChange={handleInputChange}
              className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
              placeholder="Enter referral code if you have one"
            />
          </div>
        </div>
        
        <div className="flex justify-between mt-8">
          <button
            type="button"
            onClick={handlePrevStep}
            className="px-6 py-3 rounded-lg font-medium bg-gray-200 text-gray-800 hover:bg-gray-300"
          >
            Back
          </button>
          <button
            type="button"
            onClick={handleNextStep}
            disabled={!formData.purpose || (formData.purpose === 'Other' && !formData.purposeOther) || !formData.income || !formData.employment || uploadedDocuments.length === 0}
            className={`px-6 py-3 rounded-lg font-medium ${
              formData.purpose && (formData.purpose !== 'Other' || formData.purposeOther) && formData.income && formData.employment && uploadedDocuments.length > 0
                ? 'bg-blue-600 text-white hover:bg-blue-700' 
                : 'bg-gray-300 text-gray-500 cursor-not-allowed'
            }`}
          >
            Continue
          </button>
        </div>
      </div>
    );
  };
  
  // Render step 6: Review & Apply
  const renderReviewStep = () => {
    return (
      <div className="bg-white rounded-lg shadow-md p-8">
        <h2 className="text-2xl font-bold text-gray-900 mb-6">Review & Submit Application</h2>
        
        <div className="bg-blue-50 rounded-lg p-6 mb-8">
          <h3 className="text-lg font-semibold text-gray-900 mb-4">Loan Summary</h3>
          
          <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div className="bg-white rounded-lg p-4 shadow-sm">
              <div className="text-gray-500 text-sm mb-1">Loan Amount</div>
              <div className="text-2xl font-bold text-gray-900">₹{formatNumber(parseInt(formData.amount))}</div>
            </div>
            
            <div className="bg-white rounded-lg p-4 shadow-sm">
              <div className="text-gray-500 text-sm mb-1">Tenure</div>
              <div className="text-2xl font-bold text-gray-900">{formData.tenure} Months</div>
            </div>
            
            <div className="bg-white rounded-lg p-4 shadow-sm">
              <div className="text-gray-500 text-sm mb-1">Monthly EMI</div>
              <div className="text-2xl font-bold text-blue-700">₹{formatNumber(calculatedEMI)}</div>
            </div>
          </div>
          
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div className="space-y-3">
              <div>
                <span className="text-gray-500">Loan Type:</span>
                <span className="ml-2 text-gray-900 font-medium">{selectedLoanType?.name}</span>
              </div>
              
              <div>
                <span className="text-gray-500">Interest Rate:</span>
                <span className="ml-2 text-gray-900 font-medium">{selectedLoanType?.interest_rate}% p.a.</span>
              </div>
              
              <div>
                <span className="text-gray-500">Total Interest:</span>
                <span className="ml-2 text-gray-900 font-medium">₹{formatNumber(totalInterest)}</span>
              </div>
            </div>
            
            <div className="space-y-3">
              <div>
                <span className="text-gray-500">Purpose:</span>
                <span className="ml-2 text-gray-900 font-medium">
                  {formData.purpose === 'Other' ? formData.purposeOther : formData.purpose}
                </span>
              </div>
              
              <div>
                <span className="text-gray-500">Employment:</span>
                <span className="ml-2 text-gray-900 font-medium">{formData.employment}</span>
              </div>
              
              <div>
                <span className="text-gray-500">Documents:</span>
                <span className="ml-2 text-gray-900 font-medium">{uploadedDocuments.length} files uploaded</span>
              </div>
            </div>
          </div>
        </div>
        
        <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-8">
          <div className="flex">
            <svg className="w-6 h-6 text-yellow-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
              <p className="text-gray-700">
                By submitting this application, you confirm that all the information provided is accurate and complete. You authorize MBC Finance to verify the details as necessary.
              </p>
            </div>
          </div>
        </div>
        
        {message && (
          <div className={`p-4 rounded-lg mb-6 ${message.includes('success') ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}`}>
            {message}
          </div>
        )}
        
        <div className="flex justify-between mt-8">
          <button
            type="button"
            onClick={handlePrevStep}
            className="px-6 py-3 rounded-lg font-medium bg-gray-200 text-gray-800 hover:bg-gray-300"
            disabled={isSubmitting}
          >
            Back
          </button>
          <button
            type="submit"
            onClick={handleSubmit}
            disabled={isSubmitting}
            className={`px-6 py-3 rounded-lg font-medium ${
              isSubmitting 
                ? 'bg-gray-400 text-white cursor-not-allowed' 
                : 'bg-blue-600 text-white hover:bg-blue-700'
            }`}
          >
            {isSubmitting ? (
              <div className="flex items-center">
                <svg className="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                  <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Processing...
              </div>
            ) : (
              'Submit Application'
            )}
          </button>
        </div>
      </div>
    );
  };
  
  // Render current step content
  const renderStepContent = () => {
    switch (currentStep) {
      case 1:
        return renderLoanTypeStep();
      case 2:
        return renderAmountStep();
      case 3:
        return renderTenureStep();
      case 4:
        return renderEMIStep();
      case 5:
        return renderDetailsStep();
      case 6:
        return renderReviewStep();
      default:
        return null;
    }
  };
  
  return (
    <div className="min-h-screen bg-gray-50 p-4 md:p-6">
      <div className="max-w-5xl mx-auto">
        <div className="mb-6">
          <h1 className="text-3xl font-bold text-gray-900">Loan Application</h1>
          <p className="text-gray-600 mt-2">Complete the steps below to apply for your loan</p>
        </div>
        
        {renderProgressBar()}
        
        <form>
          {renderStepContent()}
        </form>
      </div>
    </div>
  );
}