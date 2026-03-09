import React, { createContext, useState, useEffect, useContext } from 'react';
import axios from 'axios';

// Backend URL is pulled from the environment variables (e.g., .env)
// Use relative path in production (when env var not set), absolute in development
import { API_URL } from '../api/apiService';

const AuthContext = createContext();

export const AuthProvider = ({ children }) => {
    const [user, setUser] = useState(null);
    const [token, setToken] = useState(localStorage.getItem('token'));
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        if (token) {
            axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
            fetchUserProfile();
        } else {
            delete axios.defaults.headers.common['Authorization'];
            setLoading(false);
        }
    }, [token]);
    
    const fetchUserProfile = async () => {
        try {
            // First try to get fresh data from backend
            const response = await axios.get(`${API_URL}/auth/profile`);
            const userData = response.data;
            
            // Update localStorage with fresh data
            localStorage.setItem('user', JSON.stringify(userData));
            setUser(userData);
        } catch (error) {
            console.error('Failed to fetch user profile from backend:', error);
            
            // Fallback to localStorage if backend call fails
            try {
                const userData = JSON.parse(localStorage.getItem('user'));
                setUser(userData);
            } catch (localStorageError) {
                console.error('Failed to fetch user profile from localStorage:', localStorageError);
                localStorage.removeItem('token');
                localStorage.removeItem('user');
                setToken(null);
                setUser(null);
            }
        } finally {
            setLoading(false);
        }
    };

    const login = (token, user) => {
        localStorage.setItem('token', token);
        localStorage.setItem('user', JSON.stringify(user));
        setToken(token);
        setUser(user);
    };
    
    const updateUser = (updatedUser) => {
        localStorage.setItem('user', JSON.stringify(updatedUser));
        setUser(updatedUser);
    };
    
    const logout = () => {
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        setToken(null);
        setUser(null);
    };
    
    
        // Effect to set up a global axios interceptor for automatic logout on token expiry
        useEffect(() => {
            const interceptor = axios.interceptors.response.use(
                (response) => response, // Pass through successful responses
                (error) => {
                    // Check if the response exists and the status is 401 (Unauthorized) or 403 (Forbidden)
                    // These are common HTTP codes for expired or invalid JWT tokens.
                    if (error.response && (error.response.status === 401 || error.response.status === 403)) {
                        console.warn('Authentication token expired or unauthorized. Logging out automatically.');
                        // Call the logout function to clear state and local storage
                        logout();
                    }
                    return Promise.reject(error);
                }
            );
    
            // Cleanup function to remove the interceptor when the component unmounts
            return () => {
                axios.interceptors.response.eject(interceptor);
            };
        }, []); 
        
    return (
        <AuthContext.Provider value={{ user, token, loading, login, logout, updateUser }}>
            {children}
        </AuthContext.Provider>
    );
};

export const useAuth = () => useContext(AuthContext);