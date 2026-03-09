import React, { useState, useEffect, useRef } from 'react';
import { Link, NavLink } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { FaUserCircle, FaBars, FaTimes, FaBell } from 'react-icons/fa';
import UserNotifications from './UserNotifications';
import { apiService } from '../api/apiService';
import { useSettings } from '../context/SettingsContext';

const getLinkClasses = ({ isActive }) =>
  `text-lg font-medium transition-colors p-2 ${isActive
    ? "text-orange-500 font-bold border-b-2 border-orange-500" // Highlight style
    : "text-gray-700 hover:text-orange-500"
  }`;

const getMobileLinkClasses = ({ isActive }) =>
  `block text-lg font-medium transition-all p-3 rounded-lg ${isActive
    ? "bg-orange-500 text-white font-bold shadow-md"
    : "text-gray-700 hover:bg-gray-100 active:bg-gray-200"
  }`;

const Navbar = () => {
  const { user, logout } = useAuth();
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
  const [isNotificationsOpen, setIsNotificationsOpen] = useState(false);
  const [unreadCount, setUnreadCount] = useState(0);
  const notificationRef = useRef(null);
  const { settings } = useSettings();

  // --- START: New logic for detecting browser dark mode and switching logo ---
  const [isDarkMode, setIsDarkMode] = useState(
    window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches
  );

  useEffect(() => {
    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');

    const handleChange = (e) => {
      setIsDarkMode(e.matches);
    };

    mediaQuery.addEventListener('change', handleChange);

    return () => {
      mediaQuery.removeEventListener('change', handleChange);
    };
  }, []);
  // --- END: New logic for detecting browser dark mode and switching logo ---

  // Fetch unread notification count for logged in users
  useEffect(() => {
    if (user && user.role !== 'admin') {
      fetchUnreadCount();
      // Poll for new notifications every 30 seconds
      const interval = setInterval(fetchUnreadCount, 30000);
      return () => clearInterval(interval);
    }
  }, [user]);

  // Close notification dropdown when clicking outside
  useEffect(() => {
    const handleClickOutside = (event) => {
      if (notificationRef.current && !notificationRef.current.contains(event.target)) {
        setIsNotificationsOpen(false);
      }
    };

    if (isNotificationsOpen) {
      document.addEventListener('mousedown', handleClickOutside);
      return () => document.removeEventListener('mousedown', handleClickOutside);
    }
  }, [isNotificationsOpen]);

  const fetchUnreadCount = async () => {
    try {
      const response = await apiService.get('notifications/user?isRead=false');
      if (response && response.success) {
        setUnreadCount(response.unreadCount || 0);
      } else if (Array.isArray(response)) {
        setUnreadCount(response.length);
      }
    } catch (error) {
      console.error('Error fetching unread count:', error);
    }
  };

  const toggleMobileMenu = () => {
    setIsMobileMenuOpen(!isMobileMenuOpen);
  };

  const closeMobileMenu = () => {
    setIsMobileMenuOpen(false);
  };

  const handleLogout = () => {
    logout();
    closeMobileMenu();
  };

  return (
    <nav className="bg-white text-gray-900 shadow-lg border-b-2 border-gray-100 sticky top-0 z-50">

      <div className="container mx-auto px-4 py-2">
        <div className=" flex justify-between items-center">
          <Link
            to="/"
            onClick={closeMobileMenu}
            className="flex items-center"
          >
            <img
              src={isDarkMode ? `${import.meta.env.BASE_URL}logo-dark.png` : `${import.meta.env.BASE_URL}logo-light.png`}
              alt="Khushi Home Sofa Repairing"
              className="h-12 w-auto scale-150 "
            />
          </Link>
          {/* Logo - Text replaced with Image Logo and Theme Switching */}
          {/* Desktop Navigation Links */}
          <div className="hidden lg:flex space-x-4 items-center">
            <NavLink to="/" className={getLinkClasses} end>
              Home
            </NavLink>

            <NavLink to="/blog" className={getLinkClasses}>
              Blog
            </NavLink>

            <NavLink to="/contact" className={getLinkClasses}>
              Contact
            </NavLink>

            {user && user.role === 'provider' && (
              <>
                <NavLink to="/provider-services" className={getLinkClasses}>
                  My Services
                </NavLink>
                <NavLink to="/my-jobs" className={getLinkClasses}>
                  My Jobs
                </NavLink>
              </>
            )}

            {user && (user.role === 'admin' || user.isSuperAdmin) && (
              <NavLink to="/admin" className={getLinkClasses}>
                Admin
              </NavLink>
            )}

            {user && user.isSuperAdmin && (
              <NavLink to="/super-admin/settings" className={getLinkClasses}>
                Site Branding
              </NavLink>
            )}

            {user ? (
              <div className="flex items-center space-x-3">
                {/* Notification Button - only for non-admin users */}
                {user.role !== 'admin' && (
                  <div className="relative" ref={notificationRef}>
                    <button
                      onClick={() => setIsNotificationsOpen(!isNotificationsOpen)}
                      className="relative p-2 text-gray-700 hover:text-orange-500 transition-colors"
                    >
                      <FaBell className="text-lg" />
                      {unreadCount > 0 && (
                        <span className="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                          {unreadCount > 9 ? '9+' : unreadCount}
                        </span>
                      )}
                    </button>

                    {/* Notification Dropdown */}
                    <UserNotifications
                      isOpen={isNotificationsOpen}
                      onClose={() => {
                        setIsNotificationsOpen(false);
                        fetchUnreadCount(); // Refresh unread count when closing
                      }}
                    />
                  </div>
                )}

                <NavLink to="/profile" className={getLinkClasses}>
                  <FaUserCircle className="inline mr-1" />
                  Profile
                </NavLink>

                <NavLink to="/requests" className={getLinkClasses}>
                  My Requests
                </NavLink>

                <button
                  onClick={logout}
                  className="bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-red-700 active:scale-95 transition-all shadow-md"
                >
                  Logout
                </button>
              </div>
            ) : (
              <Link
                to="/auth"
                className="bg-blue-600 text-white px-6 py-2 rounded-lg text-sm font-semibold hover:bg-orange-500 active:scale-95 transition-all shadow-md"
              >
                Login / Register
              </Link>
            )}
          </div>

          {/* Mobile Menu Button */}
          <button
            onClick={toggleMobileMenu}
            className="lg:hidden p-2 rounded-lg hover:bg-gray-100 active:bg-gray-200 transition-colors"
            aria-label="Toggle menu"
          >
            {isMobileMenuOpen ? (
              <FaTimes className="h-6 w-6 text-gray-700" />
            ) : (
              <FaBars className="h-6 w-6 text-gray-700" />
            )}
          </button>
        </div>
        {/* Mobile Menu */}
        {isMobileMenuOpen && (
          <div className="lg:hidden mt-4 pb-4 space-y-2 border-t pt-4 animate-slideDown">
            <NavLink
              to="/"
              className={getMobileLinkClasses}
              end
              onClick={closeMobileMenu}
            >
              🏠 Home
            </NavLink>

            <NavLink
              to="/blog"
              className={getMobileLinkClasses}
              onClick={closeMobileMenu}
            >
              📝 Blog
            </NavLink>

            <NavLink
              to="/contact"
              className={getMobileLinkClasses}
              onClick={closeMobileMenu}
            >
              📞 Contact
            </NavLink>

            {user && user.role === 'provider' && (
              <>
                <NavLink
                  to="/provider-services"
                  className={getMobileLinkClasses}
                  onClick={closeMobileMenu}
                >
                  🛠️ My Services
                </NavLink>
                <NavLink
                  to="/my-jobs"
                  className={getMobileLinkClasses}
                  onClick={closeMobileMenu}
                >
                  💼 My Jobs
                </NavLink>
              </>
            )}

            {user && (user.role === 'admin' || user.isSuperAdmin) && (
              <NavLink
                to="/admin"
                className={getMobileLinkClasses}
                onClick={closeMobileMenu}
              >
                ⚙️ Admin Panel
              </NavLink>
            )}
            {user && user.isSuperAdmin && (
              <NavLink to="/super-admin/settings" className={getLinkClasses}>
                Site Branding
              </NavLink>
            )}

            {user ? (
              <>
                {/* Notification Button for Mobile - only for non-admin users */}
                {user.role !== 'admin' && (
                  <button
                    onClick={() => {
                      setIsNotificationsOpen(true);
                      closeMobileMenu();
                    }}
                    className={`w-full flex items-center justify-between p-3 rounded-lg text-gray-700 hover:bg-gray-100 active:bg-gray-200 transition-all ${unreadCount > 0 ? 'bg-blue-50 border border-blue-200' : ''
                      }`}
                  >
                    <span className="flex items-center">
                      <FaBell className="inline mr-2" />
                      Notifications
                    </span>
                    {unreadCount > 0 && (
                      <span className="bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                        {unreadCount > 9 ? '9+' : unreadCount}
                      </span>
                    )}
                  </button>
                )}

                <NavLink
                  to="/notifications"
                  className={getMobileLinkClasses}
                  onClick={closeMobileMenu}
                >
                  🔔 Notifications
                  {unreadCount > 0 && (
                    <span className="bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center ml-auto">
                      {unreadCount > 9 ? '9+' : unreadCount}
                    </span>
                  )}
                </NavLink>

                <NavLink
                  to="/profile"
                  className={getMobileLinkClasses}
                  onClick={closeMobileMenu}
                >
                  <FaUserCircle className="inline mr-2" />
                  Profile
                </NavLink>

                <NavLink
                  to="/requests"
                  className={getMobileLinkClasses}
                  onClick={closeMobileMenu}
                >
                  📋 My Requests
                </NavLink>

                <button
                  onClick={handleLogout}
                  className="w-full bg-red-600 text-white px-4 py-3 rounded-lg text-base font-semibold hover:bg-red-700 active:scale-95 transition-all shadow-md"
                >
                  🚪 Logout
                </button>
              </>
            ) : (
              <Link
                to="/auth"
                className="block bg-blue-600 text-white px-4 py-3 rounded-lg text-center text-base font-semibold hover:bg-orange-500 active:scale-95 transition-all shadow-md"
                onClick={closeMobileMenu}
              >
                🔐 Login / Register
              </Link>
            )}
          </div>
        )}
      </div>
    </nav>
  );
};

export default Navbar;