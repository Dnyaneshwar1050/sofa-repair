import React, { useState, useEffect } from 'react';
import { apiService } from '../api/apiService';
import { useAuth } from '../context/AuthContext';

const UserNotificationsPage = () => {
    const [notifications, setNotifications] = useState([]);
    const [loading, setLoading] = useState(true);
    const [filter, setFilter] = useState('all'); // all, unread, read
    const { user } = useAuth();

    useEffect(() => {
        if (user) {
            fetchNotifications();
        }
    }, [user]);

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

    const markAllAsRead = async () => {
        try {
            const unreadNotifications = notifications.filter(notif => !notif.isRead);
            
            // Mark all unread notifications as read
            for (const notif of unreadNotifications) {
                await apiService.put(`notifications/user/${notif._id}/read`);
            }
            
            // Update local state
            setNotifications(prev => 
                prev.map(notif => ({ ...notif, isRead: true }))
            );
        } catch (error) {
            console.error('Error marking all notifications as read:', error);
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
            return date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    };

    const getNotificationIcon = (type) => {
        switch (type) {
            case 'admin_response':
                return '💬';
            case 'booking_updated':
                return '📋';
            case 'booking_created':
                return '🆕';
            case 'booking_cancelled':
                return '❌';
            case 'system':
                return '⚙️';
            default:
                return '🔔';
        }
    };

    const getPriorityColor = (priority) => {
        switch (priority) {
            case 'urgent':
                return 'border-l-red-600 bg-red-50';
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

    const getPriorityBadge = (priority) => {
        const colors = {
            urgent: 'bg-red-600 text-white',
            high: 'bg-red-500 text-white',
            medium: 'bg-yellow-500 text-white',
            low: 'bg-green-500 text-white'
        };
        return colors[priority] || 'bg-gray-500 text-white';
    };

    if (loading) {
        return (
            <div className="min-h-screen bg-gray-50 flex justify-center items-center">
                <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-600"></div>
            </div>
        );
    }

    const filteredNotifications = getFilteredNotifications();
    const unreadCount = Array.isArray(notifications) ? notifications.filter(notif => !notif.isRead).length : 0;

    return (
        <div className="min-h-screen bg-gray-50 py-8">
            <div className="max-w-4xl mx-auto px-4">
                {/* Header */}
                <div className="bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg shadow-lg p-6 mb-6">
                    <div className="flex justify-between items-center">
                        <div>
                            <h1 className="text-3xl font-bold text-white">My Notifications</h1>
                            <p className="text-blue-100 mt-2">Stay updated with all your service communications</p>
                        </div>
                        <div className="bg-white/20 backdrop-blur-sm rounded-lg p-4">
                            <div className="text-white text-center">
                                <div className="text-2xl font-bold">{unreadCount}</div>
                                <div className="text-sm">Unread</div>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Action Bar */}
                <div className="bg-white rounded-lg shadow-sm mb-6 p-4">
                    <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        {/* Filter Tabs */}
                        <div className="flex space-x-1">
                            {[
                                { key: 'all', label: 'All Notifications', count: notifications.length },
                                { key: 'unread', label: 'Unread', count: unreadCount },
                                { key: 'read', label: 'Read', count: notifications.length - unreadCount }
                            ].map(tab => (
                                <button
                                    key={tab.key}
                                    onClick={() => setFilter(tab.key)}
                                    className={`px-4 py-2 rounded-md text-sm font-medium transition-colors ${
                                        filter === tab.key
                                            ? 'bg-blue-600 text-white'
                                            : 'text-gray-700 hover:bg-gray-100'
                                    }`}
                                >
                                    {tab.label} ({tab.count})
                                </button>
                            ))}
                        </div>

                        {/* Mark All as Read Button */}
                        {unreadCount > 0 && (
                            <button
                                onClick={markAllAsRead}
                                className="bg-green-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-green-700 transition-colors"
                            >
                                Mark All as Read
                            </button>
                        )}
                    </div>
                </div>

                {/* Notifications List */}
                <div className="space-y-4">
                    {filteredNotifications.length === 0 ? (
                        <div className="text-center py-12 bg-white rounded-lg shadow-sm">
                            <div className="text-6xl mb-4">📭</div>
                            <h3 className="text-xl font-semibold text-gray-600 mb-2">No notifications found</h3>
                            <p className="text-gray-500">
                                {filter === 'unread' ? 'All caught up! No unread notifications.' : 'No notifications to display.'}
                            </p>
                        </div>
                    ) : (
                        filteredNotifications.map(notification => (
                            <div
                                key={notification._id}
                                className={`bg-white rounded-lg shadow-sm border-l-4 p-6 transition-all hover:shadow-md ${
                                    getPriorityColor(notification.priority)
                                } ${!notification.isRead ? 'ring-2 ring-blue-200' : ''}`}
                            >
                                <div className="flex items-start space-x-4">
                                    <div className="text-3xl">
                                        {getNotificationIcon(notification.type)}
                                    </div>
                                    <div className="flex-1">
                                        <div className="flex items-start justify-between mb-3">
                                            <div className="flex-1">
                                                <div className="flex items-center space-x-3 mb-2">
                                                    <h3 className="text-lg font-semibold text-gray-900">
                                                        {notification.title}
                                                    </h3>
                                                    {!notification.isRead && (
                                                        <span className="bg-blue-500 text-white text-xs px-2 py-1 rounded-full">
                                                            New
                                                        </span>
                                                    )}
                                                    <span className={`text-xs px-2 py-1 rounded-full ${getPriorityBadge(notification.priority)}`}>
                                                        {notification.priority.charAt(0).toUpperCase() + notification.priority.slice(1)}
                                                    </span>
                                                </div>
                                                <p className="text-gray-700 mb-4 leading-relaxed">{notification.message}</p>
                                            </div>
                                        </div>
                                        
                                        {/* Enhanced Metadata Display */}
                                        {notification.metadata && (
                                            <div className="bg-gray-50 rounded-lg p-4 mb-4">
                                                <h4 className="font-medium text-gray-900 mb-2">Details:</h4>
                                                <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                                                    {notification.metadata.serviceName && (
                                                        <div>
                                                            <span className="text-gray-600">Service:</span>
                                                            <span className="ml-2 font-medium">{notification.metadata.serviceName}</span>
                                                        </div>
                                                    )}
                                                    {notification.type === 'admin_response' && notification.metadata.responseType && (
                                                        <div>
                                                            <span className="text-gray-600">Response Type:</span>
                                                            <span className="ml-2 font-medium capitalize">{notification.metadata.responseType}</span>
                                                        </div>
                                                    )}
                                                    {notification.metadata.bookingStatus && (
                                                        <div>
                                                            <span className="text-gray-600">Booking Status:</span>
                                                            <span className="ml-2 font-medium">{notification.metadata.bookingStatus}</span>
                                                        </div>
                                                    )}
                                                    {notification.metadata.scheduledDate && (
                                                        <div className="sm:col-span-2">
                                                            <span className="text-gray-600">Scheduled Date:</span>
                                                            <span className="ml-2 font-medium">
                                                                {new Date(notification.metadata.scheduledDate).toLocaleDateString()}
                                                            </span>
                                                        </div>
                                                    )}
                                                </div>
                                            </div>
                                        )}
                                        
                                        <div className="flex items-center justify-between">
                                            <span className="text-sm text-gray-500">
                                                {formatDate(notification.createdAt)}
                                            </span>
                                            <div className="flex space-x-2">
                                                {!notification.isRead && (
                                                    <button
                                                        onClick={() => markAsRead(notification._id)}
                                                        className="inline-flex items-center px-3 py-1 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700 transition-colors"
                                                    >
                                                        Mark as Read
                                                    </button>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        ))
                    )}
                </div>

                {/* Back to Dashboard Link */}
                <div className="mt-8 text-center">
                    <button
                        onClick={() => window.history.back()}
                        className="inline-flex items-center px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors"
                    >
                        ← Back to Previous Page
                    </button>
                </div>
            </div>
        </div>
    );
};

export default UserNotificationsPage;