import React, { useState, useEffect } from 'react';
import { apiService } from '../api/apiService';

const AdminNotifications = () => {
    const [notifications, setNotifications] = useState([]);
    const [loading, setLoading] = useState(true);
    const [filter, setFilter] = useState('all'); // all, unread, read
    const [responseText, setResponseText] = useState({});
    const [respondingTo, setRespondingTo] = useState(null);

    useEffect(() => {
        fetchNotifications();
        // Poll for new notifications every 30 seconds
        const interval = setInterval(fetchNotifications, 30000);
        return () => clearInterval(interval);
    }, []);

    const fetchNotifications = async () => {
        try {
            const response = await apiService.get('notifications/admin');
            console.log('Notifications API response:', response); // Debug log
            
            // Handle the {success: true, data: [...]} response structure
            if (response && response.success && Array.isArray(response.data)) {
                setNotifications(response.data);
            } else if (Array.isArray(response)) {
                // Fallback for direct array response
                setNotifications(response);
            } else {
                console.warn('Unexpected response structure:', response);
                setNotifications([]);
            }
        } catch (error) {
            console.error('Error fetching notifications:', error);
            setNotifications([]); // Set empty array on error
        } finally {
            setLoading(false);
        }
    };

    const markAsRead = async (notificationId) => {
        try {
            const response = await apiService.put(`notifications/admin/${notificationId}/read`);
            console.log('Mark as read response:', response); // Debug log
            
            setNotifications(prev => 
                Array.isArray(prev) ? prev.map(notif => 
                    notif._id === notificationId 
                        ? { ...notif, isRead: true }
                        : notif
                ) : []
            );
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    };

    const sendResponse = async (notificationId) => {
        const text = responseText[notificationId];
        if (!text || !text.trim()) return;

        try {
            // Find the notification to get booking and user IDs
            const notification = notifications.find(n => n._id === notificationId);
            if (!notification) {
                alert('Notification not found');
                return;
            }

            await apiService.post('notifications/admin/respond', {
                bookingId: notification.booking?._id || notification.booking,
                userId: notification.user?._id || notification.user,
                responseText: text,
                responseType: 'update'
            });
            
            setResponseText(prev => ({ ...prev, [notificationId]: '' }));
            setRespondingTo(null);
            fetchNotifications(); // Refresh to get updated status
            
            alert('Response sent successfully!');
        } catch (error) {
            console.error('Error sending response:', error);
            alert('Error sending response: ' + (error.response?.data?.message || error.message));
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
            case 'booking_created':
                return '📋';
            case 'admin_response':
                return '💬';
            default:
                return '🔔';
        }
    };

    const getPriorityColor = (priority) => {
        switch (priority) {
            case 'high':
                return 'border-red-500 bg-red-50';
            case 'medium':
                return 'border-yellow-500 bg-yellow-50';
            case 'low':
                return 'border-green-500 bg-green-50';
            default:
                return 'border-gray-300 bg-white';
        }
    };

    if (loading) {
        return (
            <div className="flex justify-center items-center min-h-screen">
                <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-600"></div>
            </div>
        );
    }

    const filteredNotifications = getFilteredNotifications();
    const unreadCount = Array.isArray(notifications) ? notifications.filter(notif => !notif.isRead).length : 0;

    return (
        <div className="min-h-screen bg-gray-50 p-6">
            <div className="max-w-6xl mx-auto">
                {/* Header */}
                <div className="bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg shadow-lg p-6 mb-6">
                    <div className="flex justify-between items-center">
                        <div>
                            <h1 className="text-3xl font-bold text-white">Admin Notifications</h1>
                            <p className="text-blue-100 mt-2">Manage booking notifications and customer communications</p>
                        </div>
                        <div className="bg-white/20 backdrop-blur-sm rounded-lg p-4">
                            <div className="text-white text-center">
                                <div className="text-2xl font-bold">{unreadCount}</div>
                                <div className="text-sm">Unread</div>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Filter Tabs */}
                <div className="bg-white rounded-lg shadow-sm mb-6 p-1">
                    <div className="flex space-x-1">
                        {[
                            { key: 'all', label: 'All Notifications', count: Array.isArray(notifications) ? notifications.length : 0 },
                            { key: 'unread', label: 'Unread', count: unreadCount },
                            { key: 'read', label: 'Read', count: Array.isArray(notifications) ? notifications.length - unreadCount : 0 }
                        ].map(tab => (
                            <button
                                key={tab.key}
                                onClick={() => setFilter(tab.key)}
                                className={`flex-1 px-4 py-3 rounded-md text-sm font-medium transition-colors ${
                                    filter === tab.key
                                        ? 'bg-blue-600 text-white'
                                        : 'text-gray-700 hover:bg-gray-100'
                                }`}
                            >
                                {tab.label} ({tab.count})
                            </button>
                        ))}
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
                                <div className="flex items-start justify-between">
                                    <div className="flex items-start space-x-4 flex-1">
                                        <div className="text-2xl">
                                            {getNotificationIcon(notification.type)}
                                        </div>
                                        <div className="flex-1">
                                            <div className="flex items-center space-x-2 mb-2">
                                                <h3 className="text-lg font-semibold text-gray-900">
                                                    {notification.title}
                                                </h3>
                                                {!notification.isRead && (
                                                    <span className="bg-blue-500 text-white text-xs px-2 py-1 rounded-full">
                                                        New
                                                    </span>
                                                )}
                                                <span className={`text-xs px-2 py-1 rounded-full ${
                                                    notification.priority === 'high' ? 'bg-red-100 text-red-700' :
                                                    notification.priority === 'medium' ? 'bg-yellow-100 text-yellow-700' :
                                                    'bg-green-100 text-green-700'
                                                }`}>
                                                    {notification.priority} priority
                                                </span>
                                            </div>
                                            <p className="text-gray-700 mb-3">{notification.message}</p>
                                            
                                            {/* Booking Details */}
                                            {notification.metadata && (
                                                <div className="bg-gray-50 rounded-lg p-4 mb-4">
                                                    <h4 className="font-medium text-gray-900 mb-2">Booking Details:</h4>
                                                    <div className="grid grid-cols-2 gap-4 text-sm">
                                                        <div>
                                                            <span className="text-gray-600">Service:</span>
                                                            <span className="ml-2 font-medium">{notification.metadata.serviceName}</span>
                                                        </div>
                                                        <div>
                                                            <span className="text-gray-600">Amount:</span>
                                                            <span className="ml-2 font-medium">₹{notification.metadata.totalAmount}</span>
                                                        </div>
                                                        <div>
                                                            <span className="text-gray-600">Customer:</span>
                                                            <span className="ml-2 font-medium">{notification.metadata.customerName}</span>
                                                        </div>
                                                        <div>
                                                            <span className="text-gray-600">Phone:</span>
                                                            <span className="ml-2 font-medium">{notification.metadata.customerPhone}</span>
                                                        </div>
                                                        {notification.metadata.preferredDate && (
                                                            <div className="col-span-2">
                                                                <span className="text-gray-600">Preferred Date:</span>
                                                                <span className="ml-2 font-medium">
                                                                    {new Date(notification.metadata.preferredDate).toLocaleDateString()}
                                                                </span>
                                                            </div>
                                                        )}
                                                        {notification.metadata.serviceAddress && (
                                                            <div className="col-span-2">
                                                                <span className="text-gray-600">Address:</span>
                                                                <span className="ml-2 font-medium">{notification.metadata.serviceAddress}</span>
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
                                                    {/* Quick Call Button */}
                                                    {notification.metadata?.customerPhone && (
                                                        <a
                                                            href={`tel:${notification.metadata.customerPhone}`}
                                                            className="inline-flex items-center px-3 py-1 bg-green-600 text-white text-sm rounded-md hover:bg-green-700 transition-colors"
                                                        >
                                                            📞 Call
                                                        </a>
                                                    )}
                                                    
                                                    {!notification.isRead && (
                                                        <button
                                                            onClick={() => markAsRead(notification._id)}
                                                            className="inline-flex items-center px-3 py-1 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700 transition-colors"
                                                        >
                                                            Mark Read
                                                        </button>
                                                    )}
                                                    
                                                    <button
                                                        onClick={() => setRespondingTo(
                                                            respondingTo === notification._id ? null : notification._id
                                                        )}
                                                        className="inline-flex items-center px-3 py-1 bg-purple-600 text-white text-sm rounded-md hover:bg-purple-700 transition-colors"
                                                    >
                                                        💬 Respond
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                {/* Response Form */}
                                {respondingTo === notification._id && (
                                    <div className="mt-4 pt-4 border-t border-gray-200">
                                        <h4 className="font-medium text-gray-900 mb-2">Send Response to Customer:</h4>
                                        <div className="flex space-x-2">
                                            <textarea
                                                value={responseText[notification._id] || ''}
                                                onChange={(e) => setResponseText(prev => ({
                                                    ...prev,
                                                    [notification._id]: e.target.value
                                                }))}
                                                placeholder="Type your response to the customer..."
                                                className="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                                                rows="3"
                                            />
                                            <div className="flex flex-col space-y-2">
                                                <button
                                                    onClick={() => sendResponse(notification._id)}
                                                    disabled={!responseText[notification._id]?.trim()}
                                                    className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors"
                                                >
                                                    Send
                                                </button>
                                                <button
                                                    onClick={() => setRespondingTo(null)}
                                                    className="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors"
                                                >
                                                    Cancel
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                )}
                            </div>
                        ))
                    )}
                </div>
            </div>
        </div>
    );
};

export default AdminNotifications;