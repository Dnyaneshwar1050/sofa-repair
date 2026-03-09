import React from 'react';
import { Navigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

const ProtectedRoute = ({ children, role, requiresSuperAdmin = false }) => {
    const { user, loading } = useAuth();

    if (loading) return <div className="flex justify-center items-center h-screen">Loading...</div>;

    if (!user) {
        return <Navigate to="/auth" />;
    }

    // Check for Super Admin requirement first
    if (requiresSuperAdmin && !user.isSuperAdmin) {
        return <Navigate to="/" />;
    }

    // Check for standard role requirements (Super Admins bypass Admin role checks)
    if (role) {
        const hasAccess = user.role === role || user.isSuperAdmin === true;
        if (!hasAccess) {
            return <Navigate to="/" />;
        }
    }

    return children;
};

export default ProtectedRoute;