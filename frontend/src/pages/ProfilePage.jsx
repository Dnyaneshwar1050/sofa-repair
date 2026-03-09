// service-platform/frontend/src/pages/ProfilePage.jsx (NEW FILE)
import React, { useEffect } from 'react';
import { useAuth } from '../context/AuthContext';
import { useNavigate } from 'react-router-dom';
import { User, Mail, Shield, Calendar, LogOut, FileText, Settings, Phone, MapPin } from 'lucide-react';
import EmailVerification from '../components/EmailVerification';

const ProfilePage = () => {
    const { user, logout, loading } = useAuth();
    const navigate = useNavigate();

    useEffect(() => {
        if (!loading && !user) {
            navigate('/auth');
        }
    }, [user, loading, navigate]);

    const handleLogout = () => {
        logout();
        navigate('/auth');
    };
    
    if (loading) {
        return (
            <div className="min-h-screen bg-white flex items-center justify-center">
                <div className="text-center">
                    <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
                    <p className="text-gray-600">Loading...</p>
                </div>
            </div>
        );
    }
    
    if (!user) {
        return null;
    }

    const getRoleBadgeColor = (role) => {
        switch(role?.toLowerCase()) {
            case 'admin': return 'bg-purple-100 text-purple-800 border-purple-300';
            case 'provider': return 'bg-blue-100 text-blue-800 border-blue-300';
            case 'customer': return 'bg-green-100 text-green-800 border-green-300';
            default: return 'bg-gray-100 text-gray-800 border-gray-300';
        }
    };

    const profileStats = [
        { label: 'Account Type', value: user.role || 'Customer', icon: Shield },
        { label: 'Member Since', value: new Date(user.createdAt || Date.now()).toLocaleDateString('en-IN', { month: 'short', year: 'numeric' }), icon: Calendar },
    ];

    return (
        <div className="min-h-screen bg-white">
            {/* Hero Header - Full Width */}
            <div className="relative bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 overflow-hidden">
                <div className="absolute inset-0 bg-grid-white/10 [mask-image:linear-gradient(0deg,white,rgba(255,255,255,0.5))] -z-0"></div>
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 relative">
                    <div className="flex flex-col md:flex-row items-center gap-8">
                        {/* Avatar */}
                        <div className="relative group">
                            <div className="w-32 h-32 bg-white/10 backdrop-blur-xl rounded-full flex items-center justify-center border-4 border-white/20 shadow-2xl transition-transform group-hover:scale-105">
                                <User className="w-16 h-16 text-white" strokeWidth={1.5} />
                            </div>
                            <div className="absolute -bottom-2 -right-2 bg-green-500 w-10 h-10 rounded-full border-4 border-white flex items-center justify-center shadow-lg">
                                <span className="text-sm font-bold text-white">✓</span>
                            </div>
                        </div>

                        {/* User Info */}
                        <div className="flex-1 text-center md:text-left">
                            <h1 className="text-4xl md:text-5xl font-bold text-white mb-4 tracking-tight">{user.name}</h1>
                            <div className="flex flex-wrap items-center justify-center md:justify-start gap-4 mb-4">
                                <div className="flex items-center gap-2 text-white/90">
                                    <Mail className="w-5 h-5" />
                                    <span className="text-base font-medium">{user.email}</span>
                                </div>
                                {user.phone && (
                                    <div className="flex items-center gap-2 text-white/90">
                                        <Phone className="w-5 h-5" />
                                        <span className="text-base font-medium">{user.phone}</span>
                                    </div>
                                )}
                            </div>
                            <div className={`inline-flex items-center gap-2 px-5 py-2.5 rounded-full font-semibold shadow-lg ${getRoleBadgeColor(user.role)}`}>
                                <Shield className="w-5 h-5" />
                                <span className="capitalize">{user.role || 'Customer'} Account</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {/* Main Content */}
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    {/* Left Column - Profile Details */}
                    <div className="lg:col-span-2 space-y-8">
                        
                        {/* Email Verification Section */}
                        <div>
                            <h2 className="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-3">
                                <div className="w-1 h-8 bg-gradient-to-b from-blue-600 to-purple-600 rounded-full"></div>
                                Email Verification
                            </h2>
                            <EmailVerification />
                        </div>

                        {/* Account Information */}
                        <div>
                            <h2 className="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-3">
                                <div className="w-1 h-8 bg-gradient-to-b from-blue-600 to-purple-600 rounded-full"></div>
                                Account Information
                            </h2>
                            
                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div className="group">
                                    <label className="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-2 block">Full Name</label>
                                    <div className="flex items-center gap-3 py-3 border-b-2 border-gray-200 group-hover:border-blue-500 transition-colors">
                                        <User className="w-5 h-5 text-gray-400" />
                                        <p className="text-lg font-semibold text-gray-900">{user.name}</p>
                                    </div>
                                </div>
                                
                                <div className="group">
                                    <label className="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-2 block">Email Address</label>
                                    <div className="flex items-center gap-3 py-3 border-b-2 border-gray-200 group-hover:border-blue-500 transition-colors">
                                        <Mail className="w-5 h-5 text-blue-600" />
                                        <p className="text-lg font-semibold text-gray-900 truncate">{user.email}</p>
                                    </div>
                                </div>
                                
                                {user.phone && (
                                    <div className="group">
                                        <label className="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-2 block">Phone Number</label>
                                        <div className="flex items-center gap-3 py-3 border-b-2 border-gray-200 group-hover:border-green-500 transition-colors">
                                            <Phone className="w-5 h-5 text-green-600" />
                                            <p className="text-lg font-semibold text-gray-900">{user.phone}</p>
                                        </div>
                                    </div>
                                )}
                                
                                <div className="group">
                                    <label className="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-2 block">Account Type</label>
                                    <div className="flex items-center gap-3 py-3 border-b-2 border-gray-200 group-hover:border-purple-500 transition-colors">
                                        <Shield className="w-5 h-5 text-purple-600" />
                                        <p className="text-lg font-semibold text-gray-900 capitalize">{user.role || 'Customer'}</p>
                                    </div>
                                </div>

                                {user.address && (
                                    <div className="group sm:col-span-2">
                                        <label className="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-2 block">Address</label>
                                        <div className="flex items-start gap-3 py-3 border-b-2 border-gray-200 group-hover:border-red-500 transition-colors">
                                            <MapPin className="w-5 h-5 text-red-600 mt-1" />
                                            <p className="text-lg font-semibold text-gray-900">{user.address}</p>
                                        </div>
                                    </div>
                                )}
                            </div>
                        </div>

                        {/* Quick Actions */}
                        <div>
                            <h2 className="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-3">
                                <div className="w-1 h-8 bg-gradient-to-b from-blue-600 to-purple-600 rounded-full"></div>
                                Quick Actions
                            </h2>

                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <button
                                    onClick={() => navigate('/requests')}
                                    className="group relative overflow-hidden bg-gradient-to-br from-blue-500 to-blue-600 text-white p-6 rounded-2xl hover:shadow-xl transition-all duration-300 hover:-translate-y-1"
                                >
                                    <div className="absolute inset-0 bg-blue-700 transform translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
                                    <div className="relative flex items-start gap-4">
                                        <div className="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                                            <FileText className="w-6 h-6" />
                                        </div>
                                        <div className="text-left">
                                            <p className="font-bold text-xl mb-1">My Requests</p>
                                            <p className="text-sm text-blue-100">View service history</p>
                                        </div>
                                    </div>
                                </button>

                                {user.role === 'admin' && (
                                    <button
                                        onClick={() => navigate('/admin')}
                                        className="group relative overflow-hidden bg-gradient-to-br from-purple-500 to-purple-600 text-white p-6 rounded-2xl hover:shadow-xl transition-all duration-300 hover:-translate-y-1"
                                    >
                                        <div className="absolute inset-0 bg-purple-700 transform translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
                                        <div className="relative flex items-start gap-4">
                                            <div className="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                                                <Shield className="w-6 h-6" />
                                            </div>
                                            <div className="text-left">
                                                <p className="font-bold text-xl mb-1">Admin Panel</p>
                                                <p className="text-sm text-purple-100">Manage platform</p>
                                            </div>
                                        </div>
                                    </button>
                                )}

                                {user.role === 'provider' && (
                                    <button
                                        onClick={() => navigate('/provider/jobs')}
                                        className="group relative overflow-hidden bg-gradient-to-br from-green-500 to-green-600 text-white p-6 rounded-2xl hover:shadow-xl transition-all duration-300 hover:-translate-y-1"
                                    >
                                        <div className="absolute inset-0 bg-green-700 transform translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
                                        <div className="relative flex items-start gap-4">
                                            <div className="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                                                <FileText className="w-6 h-6" />
                                            </div>
                                            <div className="text-left">
                                                <p className="font-bold text-xl mb-1">My Jobs</p>
                                                <p className="text-sm text-green-100">View assignments</p>
                                            </div>
                                        </div>
                                    </button>
                                )}

                                <button
                                    onClick={handleLogout}
                                    className="group relative overflow-hidden bg-gradient-to-br from-red-500 to-red-600 text-white p-6 rounded-2xl hover:shadow-xl transition-all duration-300 hover:-translate-y-1"
                                >
                                    <div className="absolute inset-0 bg-red-700 transform translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
                                    <div className="relative flex items-start gap-4">
                                        <div className="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                                            <LogOut className="w-6 h-6" />
                                        </div>
                                        <div className="text-left">
                                            <p className="font-bold text-xl mb-1">Logout</p>
                                            <p className="text-sm text-red-100">Sign out securely</p>
                                        </div>
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>

                    {/* Right Column - Stats & Info */}
                    <div className="space-y-6">
                        
                        {/* Account Stats */}
                        {profileStats.map((stat, index) => (
                            <div key={index} className="group">
                                <div className="flex items-center gap-4 py-4 border-b-2 border-gray-200 group-hover:border-blue-500 transition-colors">
                                    <stat.icon className="w-8 h-8 text-blue-600" />
                                    <div className="flex-1">
                                        <p className="text-sm font-semibold text-gray-500 uppercase tracking-wide">{stat.label}</p>
                                        <p className="text-xl font-bold text-gray-900">{stat.value}</p>
                                    </div>
                                </div>
                            </div>
                        ))}

                        {/* Account Status - Only show when email is verified */}
                        {user.isEmailVerified && (
                            <div className="relative overflow-hidden py-8 px-6 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl">
                                <div className="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16"></div>
                                <div className="absolute bottom-0 left-0 w-24 h-24 bg-white/10 rounded-full -ml-12 -mb-12"></div>
                                <div className="relative">
                                    <div className="w-14 h-14 bg-white rounded-full flex items-center justify-center mb-4 shadow-lg">
                                        <svg className="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <h3 className="text-xl font-bold text-white mb-2">Account Status</h3>
                                    <p className="text-white/90 font-medium mb-1">Active & Verified</p>
                                    <p className="text-sm text-white/80">Your account is in good standing</p>
                                </div>
                            </div>
                        )}

                        {/* Help Card */}
                        <div className="relative overflow-hidden py-8 px-6 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl">
                            <div className="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16"></div>
                            <div className="absolute bottom-0 left-0 w-24 h-24 bg-white/10 rounded-full -ml-12 -mb-12"></div>
                            <div className="relative">
                                <h3 className="text-xl font-bold text-white mb-3">Need Help?</h3>
                                <p className="text-sm text-white/90 mb-6">Contact our support team for assistance</p>
                                <button 
                                    onClick={() => navigate('/contact')}
                                    className="w-full bg-white text-blue-600 font-semibold px-6 py-3 rounded-xl hover:bg-blue-50 transition-all shadow-lg hover:shadow-xl"
                                >
                                    Contact Support
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default ProfilePage;