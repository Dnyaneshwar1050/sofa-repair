import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { apiService } from '../api/apiService';
import { useAuth } from '../context/AuthContext';

const UserNotifications = ({ isOpen, onClose }) => {
    const [notifications, setNotifications] = useState([]);
    const [loading, setLoading] = useState(true);
    const [filter, setFilter] = useState('all'); // all, unread, read
    const { user } = useAuth();

    useEffect(() => {
        if (isOpen && user) {
            fetchNotifications();
        }
    }, [isOpen, user]);

    const fetchNotifications = async () => {
        try {
            setLoading(true);
            const response = await apiService.get('notifications/user');
            console.log('User notifications response:', response);
            
            if (response && response.success && Array.isArray(response.data)) {
                setNotifications(response.data);
            } else if (Array.isArray(response)) {
                setNotifications(response);
            } else {
                setNotifications([]);
            }
        } catch (error) {
            console.error('Error fetching user notifications:', error);
            setNotifications([]);
        } finally {
            setLoading(false);
        }
    };

    const markAsRead = async (notificationId) => {
        try {
            await apiService.put(`notifications/user/${notificationId}/read`);
            setNotifications(prev => 
                prev.map(notif => 
                    notif._id === notificationId 
                        ? { ...notif, isRead: true }
                        : notif
                )
            );
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    };

    const getFilteredNotifications = () => {
        if (!Array.isArray(notifications)) return [];
        switch (filter) {
            case 'unread':
                return notifications.filter(notif => !notif.isRead);
            case 'read':
                return notifications.filter(notif => notif.isRead);
            default:
                return notifications;
        }
    };

    const formatDate = (dateString) => {
        const date = new Date(dateString);
        const now = new Date();
        const diffInHours = (now - date) / (1000 * 60 * 60);
        
        if (diffInHours < 1) {
            const diffInMinutes = Math.floor((now - date) / (1000 * 60));
            return `${diffInMinutes} minutes ago`;
        } else if (diffInHours < 24) {
            return `${Math.floor(diffInHours)} hours ago`;
        } else {
            return date.toLocaleDateString();
        }
    };

    const getNotificationIcon = (type) => {
        switch (type) {
            case 'admin_response':
                return '💬';
            case 'booking_status':
                return '📋';
            case 'system':
                return '⚙️';
            default:
                return '🔔';
        }
    };

    const getPriorityColor = (priority) => {
        switch (priority) {
            case 'high':
                return 'border-l-red-500 bg-red-50';
            case 'medium':
                return 'border-l-yellow-500 bg-yellow-50';
            case 'low':
                return 'border-l-green-500 bg-green-50';
            default:
                return 'border-l-gray-300 bg-white';
        }
    };

    if (!isOpen) return null;

    const filteredNotifications = getFilteredNotifications();
    const unreadCount = Array.isArray(notifications) ? notifications.filter(notif => !notif.isRead).length : 0;

    return (
        <div className="absolute top-full right-0 mt-2 w-80 bg-white rounded-lg shadow-2xl border border-gray-200 z-50 max-h-96 overflow-hidden">
            {/* Header */}
            <div className="bg-gradient-to-r from-blue-600 to-purple-600 p-3">
                <div className="flex justify-between items-center">
                    <div>
                        <h3 className="text-sm font-bold text-white">My Notifications</h3>
                        <p className="text-blue-100 text-xs">{unreadCount} unread</p>
                    </div>
                    <button
                        onClick={onClose}
                        className="text-white hover:bg-white/20 rounded-full p-1 transition-colors"
                    >
                        ✕
                    </button>
                </div>
            </div>

            {/* Filter Tabs */}
            <div className="bg-gray-50 p-2 border-b">
                <div className="flex space-x-1">
                    {[
                        { key: 'all', label: 'All', count: notifications.length },
                        { key: 'unread', label: 'Unread', count: unreadCount }
                    ].map(tab => (
                        <button
                            key={tab.key}
                            onClick={() => setFilter(tab.key)}
                            className={`flex-1 px-2 py-1 rounded text-xs font-medium transition-colors ${
                                filter === tab.key
                                    ? 'bg-blue-600 text-white'
                                    : 'text-gray-700 hover:bg-gray-200'
                            }`}
                        >
                            {tab.label} ({tab.count})
                        </button>
                    ))}
                </div>
            </div>

            {/* Notifications List */}
            <div className="max-h-64 overflow-y-auto">
                {loading ? (
                    <div className="flex justify-center items-center p-4">
                        <div className="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                    </div>
                ) : filteredNotifications.length === 0 ? (
                    <div className="text-center py-8 px-4">
                        <div className="text-3xl mb-2">📭</div>
                        <h4 className="text-sm font-semibold text-gray-600 mb-1">No notifications</h4>
                        <p className="text-gray-500 text-xs">
                            {filter === 'unread' ? 'All caught up!' : 'No notifications to display.'}
                        </p>
                    </div>
                ) : (
                    <div className="p-2 space-y-2">
                        {filteredNotifications.map(notification => (
                            <Link
                                key={notification._id}
                                to="/notifications"
                                onClick={() => {
                                    if (!notification.isRead) markAsRead(notification._id);
                                    onClose();
                                }}
                                className={`block border-l-3 rounded p-3 transition-all hover:shadow-sm cursor-pointer ${
                                    getPriorityColor(notification.priority)
                                } ${!notification.isRead ? 'ring-1 ring-blue-200' : ''}`}
                            >
                                <div className="flex items-start space-x-2">
                                    <div className="text-lg">
                                        {getNotificationIcon(notification.type)}
                                    </div>
                                    <div className="flex-1 min-w-0">
                                        <div className="flex items-center justify-between mb-1">
                                            <h4 className="font-medium text-gray-900 text-xs truncate">
                                                {notification.title}
                                            </h4>
                                            {!notification.isRead && (
                                                <span className="bg-blue-500 text-white text-xs px-1 py-0.5 rounded-full ml-1">
                                                    •
                                                </span>
                                            )}
                                        </div>
                                        <p className="text-gray-700 text-xs mb-2" style={{
                                            display: '-webkit-box',
                                            WebkitLineClamp: 3,
                                            WebkitBoxOrient: 'vertical',
                                            overflow: 'hidden'
                                        }}>{notification.message}</p>
                                        
                                        {/* Enhanced Metadata Display */}
                                        {notification.metadata && (
                                            <div className="bg-gray-100 rounded p-2 text-xs text-gray-600 mb-1">
                                                {notification.metadata.serviceName && (
                                                    <div className="font-medium">Service: {notification.metadata.serviceName}</div>
                                                )}
                                                {notification.type === 'admin_response' && notification.metadata.responseType && (
                                                    <div className="mt-1 text-blue-600">Type: {notification.metadata.responseType}</div>
                                                )}
                                            </div>
                                        )}
                                        
                                        <div className="flex items-center justify-between">
                                            <span className="text-xs text-gray-500">
                                                {formatDate(notification.createdAt)}
                                            </span>
                                            <span className={`text-xs px-1 py-0.5 rounded ${
                                                notification.priority === 'high' ? 'bg-red-100 text-red-700' :
                                                notification.priority === 'medium' ? 'bg-yellow-100 text-yellow-700' :
                                                'bg-green-100 text-green-700'
                                            }`}>
                                                {notification.priority}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </Link>
                        ))}
                    </div>
                )}
            </div>
            
            {/* Footer - View All Link */}
            <div className="border-t bg-gray-50 p-3 text-center">
                <Link 
                    to="/notifications"
                    onClick={onClose}
                    className="text-blue-600 hover:text-blue-800 text-sm font-medium inline-flex items-center"
                >
                    View All Notifications →
                </Link>
            </div>
        </div>
    );
};

export default UserNotifications;