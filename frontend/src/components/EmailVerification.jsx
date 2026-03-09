import React, { useState, useEffect } from 'react';
import { apiService } from '../api/apiService';
import { useAuth } from '../context/AuthContext';
import { trackEmailVerification } from '../utils/analytics';

const EmailVerification = () => {
  const { user, updateUser } = useAuth();
  const [verificationStatus, setVerificationStatus] = useState(null);
  const [otp, setOtp] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const [isSending, setIsSending] = useState(false);
  const [message, setMessage] = useState('');
  const [error, setError] = useState('');
  const [countdown, setCountdown] = useState(0);

  useEffect(() => {
    fetchVerificationStatus();
  }, []);

  useEffect(() => {
    if (countdown > 0) {
      const timer = setTimeout(() => setCountdown(countdown - 1), 1000);
      return () => clearTimeout(timer);
    }
  }, [countdown]);

  const fetchVerificationStatus = async () => {
    try {
      const response = await apiService.get('/email-verification/status');
      setVerificationStatus(response.data);
    } catch (error) {
      console.error('Error fetching verification status:', error);
    }
  };

  const sendVerificationEmail = async () => {
    setIsSending(true);
    setError('');
    setMessage('');

    try {
      const response = await apiService.post('/email-verification/send');
      setMessage(response.message || 'Verification email sent successfully!');
      setCountdown(60); // 1 minute countdown
      
      // Refresh verification status to show OTP input field
      await fetchVerificationStatus();
    } catch (error) {
      setError(error.response?.data?.message || 'Failed to send verification email');
    } finally {
      setIsSending(false);
    }
  };

  const resendVerificationEmail = async () => {
    setIsSending(true);
    setError('');
    setMessage('');

    try {
      const response = await apiService.post('/email-verification/resend');
      setMessage(response.message || 'Verification email resent successfully!');
      setCountdown(60);
      
      // Refresh verification status to show OTP input field
      await fetchVerificationStatus();
    } catch (error) {
      setError(error.response?.data?.message || 'Failed to resend verification email');
    } finally {
      setIsSending(false);
    }
  };

  const verifyOTP = async () => {
    if (!otp.trim()) {
      setError('Please enter the OTP');
      return;
    }

    setIsLoading(true);
    setError('');
    setMessage('');

    try {
      const response = await apiService.post('/email-verification/verify', { otp });
      setMessage(response.message || 'Email verified successfully!');
      setOtp('');
      
      // Track email verification conversion
      trackEmailVerification();
      
      // Update user context with verified status
      updateUser({ ...user, isEmailVerified: true });
      
      // Immediately update verification status to hide OTP input
      setVerificationStatus(prev => ({
        ...prev,
        isEmailVerified: true,
        hasPendingVerification: false,
        emailVerifiedAt: response.emailVerifiedAt
      }));
      
      // Refresh verification status in background
      fetchVerificationStatus();
    } catch (error) {
      setError(error.response?.data?.message || 'Invalid or expired OTP');
    } finally {
      setIsLoading(false);
    }
  };

  if (user?.isEmailVerified) {
    return (
      <div className="bg-green-50 border border-green-200 rounded-lg p-6">
        <div className="flex items-center">
          <div className="flex-shrink-0">
            <svg className="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
              <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
            </svg>
          </div>
          <div className="ml-3">
            <h3 className="text-sm font-medium text-green-800">Email Verified</h3>
            <p className="text-sm text-green-700 mt-1">
              Your email has been successfully verified.
              {verificationStatus?.emailVerifiedAt && (
                <span className="block text-xs text-green-600 mt-1">
                  Verified on: {new Date(verificationStatus.emailVerifiedAt).toLocaleDateString()}
                </span>
              )}
            </p>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
      <div className="flex items-start">
        <div className="flex-shrink-0">
          <svg className="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
            <path fillRule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clipRule="evenodd" />
          </svg>
        </div>
        <div className="ml-3 flex-1">
          <h3 className="text-sm font-medium text-yellow-800">Email Not Verified</h3>
          <p className="text-sm text-yellow-700 mt-1">
            Please verify your email to access all features, including submitting reviews.
          </p>

          {/* Send/Resend Email Button */}
          <div className="mt-4">
            <button
              onClick={verificationStatus?.hasPendingVerification ? resendVerificationEmail : sendVerificationEmail}
              disabled={isSending || countdown > 0}
              className="bg-yellow-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-yellow-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {isSending ? (
                <>
                  <svg className="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" fill="none" viewBox="0 0 24 24">
                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                    <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Sending...
                </>
              ) : countdown > 0 ? (
                `Resend in ${countdown}s`
              ) : (
                verificationStatus?.hasPendingVerification ? 'Resend Email' : 'Send Verification Email'
              )}
            </button>
          </div>

          {/* OTP Input Section */}
          {verificationStatus?.hasPendingVerification && (
            <div className="mt-4 p-4 bg-white rounded-md border">
              <label htmlFor="otp" className="block text-sm font-medium text-gray-700">
                Enter Verification Code
              </label>
              <div className="mt-1 flex">
                <input
                  type="text"
                  id="otp"
                  value={otp}
                  onChange={(e) => setOtp(e.target.value.replace(/\D/g, '').slice(0, 6))}
                  placeholder="Enter 6-digit code"
                  maxLength={6}
                  className="flex-1 px-3 py-2 border border-gray-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                />
                <button
                  onClick={verifyOTP}
                  disabled={isLoading || !otp.trim() || otp.length !== 6}
                  className="px-4 py-2 bg-yellow-600 text-white rounded-r-md hover:bg-yellow-700 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  {isLoading ? (
                    <svg className="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                      <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                      <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                  ) : (
                    'Verify'
                  )}
                </button>
              </div>
              <p className="mt-2 text-xs text-gray-500">
                Check your email for the verification code. It expires in 10 minutes.
              </p>
            </div>
          )}

          {/* Messages */}
          {message && (
            <div className="mt-3 p-3 bg-green-50 border border-green-200 rounded-md">
              <p className="text-sm text-green-700">{message}</p>
            </div>
          )}

          {error && (
            <div className="mt-3 p-3 bg-red-50 border border-red-200 rounded-md">
              <p className="text-sm text-red-700">{error}</p>
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default EmailVerification;