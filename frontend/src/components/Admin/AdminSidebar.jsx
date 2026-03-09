import React, { useState, useEffect } from 'react';
import { FaBoxOpen, FaClipboardList, FaSignOutAlt, FaStore, FaUser, FaTags, FaBlog, FaSearch, FaEnvelope, FaBell, FaClock, FaCog } from 'react-icons/fa'
import { Link, NavLink, useNavigate } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext';
import { apiService } from '../../api/apiService';

const AdminSidebar = () => {
    const navigate = useNavigate();
    const { logout } = useAuth();
    const [unreadNotifications, setUnreadNotifications] = useState(0);

    useEffect(() => {
        fetchUnreadCount();
        // Poll for unread notifications every 30 seconds
        const interval = setInterval(fetchUnreadCount, 30000);
        return () => clearInterval(interval);
    }, []);

    const fetchUnreadCount = async () => {
        try {
            const response = await apiService.get('notifications/admin');
            // Handle the response structure { success: true, data: [notifications] }
            const notifications = response.success ? response.data : response;
            const unreadCount = Array.isArray(notifications) ? notifications.filter(notif => !notif.isRead).length : 0;
            setUnreadNotifications(unreadCount);
        } catch (error) {
            console.error('Error fetching unread count:', error);
            setUnreadNotifications(0);
        }
    };

    const handleLogout = () => {
        logout();
        navigate('/');
    }

    return (
        <div className='p-6'>
            <div className="mb-8 text-center">
                <Link to={`/admin`} className='text-3xl font-black text-yellow-500'>
                    Khushi Home Sofa Repairing
                </Link>
            </div>
            <h2 className='text-xl font-bold mb-8 text-center text-gray-200'>Management</h2>

            <nav className='flex flex-col space-y-3'>
                <NavLink
                    to={`/admin/users`}
                    className={({ isActive }) => isActive ? "bg-yellow-500 text-black py-3 px-4 rounded-lg flex items-center space-x-3 transition-colors" : "text-gray-100 hover:bg-yellow-500 hover:text-black py-3 px-4 rounded-lg flex items-center space-x-3 transition-colors"} >
                    <FaUser />
                    <span className="font-semibold">User Accounts</span>
                </NavLink> 
                
                <NavLink
                    to={`/admin/categories`}
                    className={({ isActive }) => isActive ? "bg-yellow-500 text-black py-3 px-4 rounded-lg flex items-center space-x-3 transition-colors" : "text-gray-100 hover:bg-yellow-500 hover:text-black py-3 px-4 rounded-lg flex items-center space-x-3 transition-colors"} >
                    <FaTags />
                    <span className="font-semibold">Categories</span>
                </NavLink>
                
                <NavLink
                    to={`/admin/services`}
                    className={({ isActive }) => isActive ? "bg-yellow-500 text-black py-3 px-4 rounded-lg flex items-center space-x-3 transition-colors" : "text-gray-100 hover:bg-yellow-500 hover:text-black py-3 px-4 rounded-lg flex items-center space-x-3 transition-colors"} >
                    <FaBoxOpen />
                    <span className="font-semibold">Service Catalog</span>
                </NavLink>
                
                <NavLink
                    to={`/admin/bookings`}
                    className={({ isActive }) => isActive ? "bg-yellow-500 text-black py-3 px-4 rounded-lg flex items-center space-x-3 transition-colors" : "text-gray-100 hover:bg-yellow-500 hover:text-black py-3 px-4 rounded-lg flex items-center space-x-3 transition-colors"} >
                    <FaClipboardList />
                    <span className="font-semibold">Bookings/Requests</span>
                </NavLink>

                {/* New Notification & Recent Requests Section */}
                <div className="border-t border-gray-600 pt-3 mt-3">
                    <NavLink
                        to={`/admin/notifications`}
                        className={({ isActive }) => isActive ? "bg-yellow-500 text-black py-3 px-4 rounded-lg flex items-center justify-between transition-colors" : "text-gray-100 hover:bg-yellow-500 hover:text-black py-3 px-4 rounded-lg flex items-center justify-between transition-colors"} >
                        <div className="flex items-center space-x-3">
                            <FaBell />
                            <span className="font-semibold">Notifications</span>
                        </div>
                        {unreadNotifications > 0 && (
                            <span className="bg-red-500 text-white text-xs rounded-full px-2 py-1 min-w-[20px] text-center">
                                {unreadNotifications > 99 ? '99+' : unreadNotifications}
                            </span>
                        )}
                    </NavLink>

                    <NavLink
                        to={`/admin/recent-requests`}
                        className={({ isActive }) => isActive ? "bg-yellow-500 text-black py-3 px-4 rounded-lg flex items-center space-x-3 transition-colors" : "text-gray-100 hover:bg-yellow-500 hover:text-black py-3 px-4 rounded-lg flex items-center space-x-3 transition-colors"} >
                        <FaClock />
                        <span className="font-semibold">Recent Requests (24h)</span>
                    </NavLink>
                </div>

                <NavLink
                    to={`/admin/contact-messages`}
                    className={({ isActive }) => isActive ? "bg-yellow-500 text-black py-3 px-4 rounded-lg flex items-center space-x-3 transition-colors" : "text-gray-100 hover:bg-yellow-500 hover:text-black py-3 px-4 rounded-lg flex items-center space-x-3 transition-colors"} >
                    <FaEnvelope />
                    <span className="font-semibold">Contact Messages</span>
                </NavLink>

                <NavLink
                    to={`/admin/blogs`}
                    className={({ isActive }) => isActive ? "bg-yellow-500 text-black py-3 px-4 rounded-lg flex items-center space-x-3 transition-colors" : "text-gray-100 hover:bg-yellow-500 hover:text-black py-3 px-4 rounded-lg flex items-center space-x-3 transition-colors"} >
                    <FaBlog />
                    <span className="font-semibold">Blog Management</span>
                </NavLink>

                <NavLink
                    to={`/admin/seo`}
                    className={({ isActive }) => isActive ? "bg-yellow-500 text-black py-3 px-4 rounded-lg flex items-center space-x-3 transition-colors" : "text-gray-100 hover:bg-yellow-500 hover:text-black py-3 px-4 rounded-lg flex items-center space-x-3 transition-colors"} >
                    <FaSearch />
                    <span className="font-semibold">SEO Management</span>
                </NavLink>

                <NavLink
                    to={`/admin/settings`}
                    className={({ isActive }) => isActive ? "bg-yellow-500 text-black py-3 px-4 rounded-lg flex items-center space-x-3 transition-colors" : "text-gray-100 hover:bg-yellow-500 hover:text-black py-3 px-4 rounded-lg flex items-center space-x-3 transition-colors"} >
                    <FaCog />
                    <span className="font-semibold">System Settings</span>
                </NavLink>

                <NavLink
                    to={`/`}
                    className="text-gray-100 hover:bg-red-600 hover:text-white py-3 px-4 rounded-lg flex items-center space-x-3 transition-colors">
                    <FaStore />
                    <span className="font-semibold">Back to Client Site</span>
                </NavLink>
            </nav>

            <div className='mt-8'>
                <button
                    onClick={handleLogout}
                    className='w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-lg flex items-center justify-center space-x-2 transition-colors'>
                    <FaSignOutAlt />
                    <span>Logout</span>
                </button>
            </div>
        </div>
    )
}

export default AdminSidebar