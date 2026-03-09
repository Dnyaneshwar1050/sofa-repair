import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Link } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { login as apiLogin, register as apiRegister } from '../api/apiService';
import { trackUserRegistration } from '../utils/analytics';

const AuthPage = () => {
  const [isLogin, setIsLogin] = useState(true);
  const [formData, setFormData] = useState({ name: '', email: '', phone: '', password: '' });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  
  const { login } = useAuth();
  const navigate = useNavigate();

  const handleChange = (e) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError(null);

    const dataToSend = isLogin 
        ? { email: formData.email, password: formData.password }
        : formData;

    try {
      const apiCall = isLogin ? apiLogin : apiRegister;
      const res = await apiCall(dataToSend);
      
      // Track user registration conversion (only for new registrations)
      if (!isLogin) {
        trackUserRegistration(res.data.user?.role || 'customer');
      }
      
      // The backend returns { token, user: { ... } }
      login(res.data.token, res.data.user);
      navigate('/');
    } catch (err) {
      const errorMessage = err.response?.data?.message || `An error occurred during ${isLogin ? 'login' : 'registration'}.`;
      setError(errorMessage);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="max-w-md mx-auto mt-10 p-6 bg-white shadow-xl rounded-lg">
      <div className="flex justify-center mb-4 py-5">
          <img 
              src="/logo-dark.png" 
              alt="Khushi Home Sofa Repairing Logo" 
              className="h-30 w-auto scale-150"
          />
      </div>
      <h1 className="text-3xl font-bold text-center mb-6">
        {isLogin ? 'Customer Login' : 'Customer Registration'}
      </h1>
      <form onSubmit={handleSubmit} className="space-y-4">
        {!isLogin && (
          <>
            <input type="text" name="name" placeholder="Full Name" required value={formData.name} onChange={handleChange}
              className="w-full p-3 border border-gray-300 rounded" />
            <input type="tel" name="phone" placeholder="Phone Number" required value={formData.phone} onChange={handleChange}
              className="w-full p-3 border border-gray-300 rounded" />
          </>
        )}
        <input type="email" name="email" placeholder="Email Address" required value={formData.email} onChange={handleChange}
          className="w-full p-3 border border-gray-300 rounded" />
        <input type="password" name="password" placeholder="Password" required value={formData.password} onChange={handleChange}
          className="w-full p-3 border border-gray-300 rounded" />
        
        {error && <p className="text-red-500 text-sm">{error}</p>}

        <button type="submit" disabled={loading}
          className="w-full bg-blue-600 text-white p-3 rounded font-semibold hover:bg-blue-700 disabled:bg-gray-400">
          {loading ? 'Processing...' : (isLogin ? 'Login' : 'Register')}
        </button>
      </form>
      
      <p className="text-center mt-4">
        {isLogin ? "Don't have an account? " : "Already have an account? "}
        <button onClick={() => setIsLogin(!isLogin)} className="text-blue-600 font-medium hover:underline">
          {isLogin ? 'Register here' : 'Login here'}
        </button>
      </p>
      <p className="text-center text-xs text-gray-600 mt-3">
        By continuing you agree to our
        {' '}<Link to="/policy" className="text-blue-600 underline">Refund & Cancellation Policy</Link>,
        {' '}<Link to="/privacy" className="text-blue-600 underline">Privacy Policy</Link> and
        {' '}<Link to="/terms" className="text-blue-600 underline">Terms & Conditions</Link>.
      </p>
    </div>
  );
};

export default AuthPage;