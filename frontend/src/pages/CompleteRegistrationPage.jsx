// project-copy/frontend/src/pages/CompleteRegistrationPage.jsx
import React, { useState, useContext, useEffect } from 'react';
import { useSearchParams, useNavigate } from 'react-router-dom';
import { completeRegistration } from '../api/apiService';
import { useAuth } from '../context/AuthContext';

const CompleteRegistrationPage = () => {
    const [searchParams] = useSearchParams();
    const navigate = useNavigate();
    const { login } = useAuth(); // Assuming useAuth provides the login function
    
    // Extract data from URL query params
    const phone = searchParams.get('phone');
    const email = searchParams.get('email');
    
    const [password, setPassword] = useState('');
    const [confirmPassword, setConfirmPassword] = useState('');
    const [loading, setLoading] = useState(false);
    const [success, setSuccess] = useState(false);
    const [error, setError] = useState(null);

    // Redirect if required info is missing
    useEffect(() => {
        if (!phone) {
            setError('Missing required phone number. Please start a new booking.');
        }
    }, [phone, navigate]);

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError(null);
        
        if (password.length < 6) {
            setError('Password must be at least 6 characters long.');
            return;
        }

        if (password !== confirmPassword) {
            setError('Passwords do not match.');
            return;
        }

        if (!phone) {
            setError('Cannot complete registration: Missing phone number.');
            return;
        }

        setLoading(true);
        try {
            // Call the new API function
            const res = await completeRegistration(phone, password);
            
            // Assuming successful call returns token/user data in res.data
            login(res.data.token, res.data.user); 
            
            setSuccess(true);
            setTimeout(() => navigate('/requests'), 1500); // Redirect to bookings page

        } catch (err) {
            console.error(err);
            setError(err.response?.data?.message || 'Failed to set password. Please try again.');
        } finally {
            setLoading(false);
        }
    };
    
    return (
        <div className="container mx-auto p-4 my-10">
            <div className="max-w-md mx-auto bg-white p-8 rounded-xl shadow-2xl border-t-4 border-red-500">
                <h2 className="text-3xl font-black text-center mb-4 text-gray-900">Complete Your Account</h2>
                
                {success ? (
                    <div className="text-center p-4 bg-green-100 text-green-700 rounded-lg">
                        <p className="font-bold">Success! 🎉</p>
                        <p>Your account is now secured. Redirecting to your bookings...</p>
                    </div>
                ) : (
                    <>
                        <p className="text-center text-gray-600 mb-6">
                            Secure your booking **({phone})** and unlock full features by setting a password.
                            {email && ` (Email: ${email})`}
                        </p>
                        
                        <form onSubmit={handleSubmit} className="space-y-4">
                            
                            <div>
                                <label className="block text-sm font-medium text-gray-700">New Password</label>
                                <input
                                    type="password"
                                    value={password}
                                    onChange={(e) => setPassword(e.target.value)}
                                    required
                                    minLength="6"
                                    className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-3 focus:ring-red-500 focus:border-red-500"
                                />
                            </div>
                            
                            <div>
                                <label className="block text-sm font-medium text-gray-700">Confirm Password</label>
                                <input
                                    type="password"
                                    value={confirmPassword}
                                    onChange={(e) => setConfirmPassword(e.target.value)}
                                    required
                                    minLength="6"
                                    className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-3 focus:ring-red-500 focus:border-red-500"
                                />
                            </div>

                            {error && <p className="text-red-500 text-sm">{error}</p>}
                            
                            <button
                                type="submit"
                                disabled={loading}
                                className="w-full bg-red-600 text-white py-3 rounded-lg font-bold hover:bg-red-700 transition disabled:bg-gray-400"
                            >
                                {loading ? 'Securing Account...' : 'Set Password and Login'}
                            </button>
                        </form>
                    </>
                )}
            </div>
        </div>
    );
};

export default CompleteRegistrationPage;