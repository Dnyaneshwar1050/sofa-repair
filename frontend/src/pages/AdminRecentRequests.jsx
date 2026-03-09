import React, { useState, useEffect } from 'react';
import { apiService, API_URL } from '../api/apiService';

const AdminRecentRequests = () => {
    const [bookings, setBookings] = useState([]);
    const [loading, setLoading] = useState(true);
    const [filter, setFilter] = useState('all'); // all, pending, confirmed, completed, cancelled

    useEffect(() => {
        fetchRecentBookings();
        // Refresh every 5 minutes
        const interval = setInterval(fetchRecentBookings, 300000);
        return () => clearInterval(interval);
    }, []);

    const fetchRecentBookings = async () => {
        try {
            const data = await apiService.get('admin/bookings/recent');
            setBookings(Array.isArray(data) ? data : []);
        } catch (error) {
            console.error('Error fetching recent bookings:', error);
            setBookings([]); // Set empty array on error
        } finally {
            setLoading(false);
        }
    };

    const updateBookingStatus = async (bookingId, newStatus) => {
        try {
            await apiService.put(`admin/bookings/${bookingId}/status`, { status: newStatus });
            setBookings(prev => 
                prev.map(booking => 
                    booking._id === bookingId 
                        ? { ...booking, status: newStatus }
                        : booking
                )
            );
            
            // Send notification to customer about status change
            await apiService.post('notifications/admin/respond', {
                bookingId,
                responseText: `Your booking status has been updated to: ${newStatus}`,
                responseType: 'update'
            });
            
            alert(`Booking status updated to ${newStatus} and customer has been notified!`);
        } catch (error) {
            console.error('Error updating booking status:', error);
            alert('Error updating booking status');
        }
    };

    const getFilteredBookings = () => {
        if (!Array.isArray(bookings)) return [];
        if (filter === 'all') return bookings;
        return bookings.filter(booking => booking.status.toLowerCase() === filter);
    };

    const getStatusColor = (status) => {
        switch (status.toLowerCase()) {
            case 'pending':
                return 'bg-yellow-100 text-yellow-800 border-yellow-300';
            case 'confirmed':
                return 'bg-blue-100 text-blue-800 border-blue-300';
            case 'completed':
                return 'bg-green-100 text-green-800 border-green-300';
            case 'cancelled':
                return 'bg-red-100 text-red-800 border-red-300';
            default:
                return 'bg-gray-100 text-gray-800 border-gray-300';
        }
    };

    const getStatusIcon = (status) => {
        switch (status.toLowerCase()) {
            case 'pending':
                return '⏳';
            case 'confirmed':
                return '✅';
            case 'completed':
                return '🎉';
            case 'cancelled':
                return '❌';
            default:
                return '📋';
        }
    };

    const formatTimeAgo = (dateString) => {
        const date = new Date(dateString);
        const now = new Date();
        const diffInMinutes = Math.floor((now - date) / (1000 * 60));
        
        if (diffInMinutes < 60) {
            return `${diffInMinutes} minutes ago`;
        } else {
            const diffInHours = Math.floor(diffInMinutes / 60);
            return `${diffInHours} hours ago`;
        }
    };

    const getUrgencyLevel = (createdAt) => {
        const hoursOld = (new Date() - new Date(createdAt)) / (1000 * 60 * 60);
        if (hoursOld < 2) return { level: 'urgent', color: 'text-red-600', label: 'Urgent' };
        if (hoursOld < 6) return { level: 'high', color: 'text-orange-600', label: 'High Priority' };
        if (hoursOld < 12) return { level: 'medium', color: 'text-yellow-600', label: 'Medium Priority' };
        return { level: 'normal', color: 'text-green-600', label: 'Normal' };
    };

    if (loading) {
        return (
            <div className="flex justify-center items-center min-h-screen">
                <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-600"></div>
            </div>
        );
    }

    const filteredBookings = getFilteredBookings();
    const statusCounts = {
        all: Array.isArray(bookings) ? bookings.length : 0,
        pending: Array.isArray(bookings) ? bookings.filter(b => b.status.toLowerCase() === 'pending').length : 0,
        confirmed: Array.isArray(bookings) ? bookings.filter(b => b.status.toLowerCase() === 'confirmed').length : 0,
        completed: Array.isArray(bookings) ? bookings.filter(b => b.status.toLowerCase() === 'completed').length : 0,
        cancelled: Array.isArray(bookings) ? bookings.filter(b => b.status.toLowerCase() === 'cancelled').length : 0,
    };

    return (
        <div className="min-h-screen bg-gray-50 p-6">
            <div className="max-w-7xl mx-auto">
                {/* Header */}
                <div className="bg-gradient-to-r from-orange-500 to-red-500 rounded-lg shadow-lg p-6 mb-6">
                    <div className="flex justify-between items-center">
                        <div>
                            <h1 className="text-3xl font-bold text-white">Recent Requests (24 Hours)</h1>
                            <p className="text-orange-100 mt-2">Manage bookings received in the last 24 hours</p>
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div className="bg-white/20 backdrop-blur-sm rounded-lg p-3 text-center">
                                <div className="text-white text-xl font-bold">{statusCounts.pending}</div>
                                <div className="text-orange-100 text-sm">Pending</div>
                            </div>
                            <div className="bg-white/20 backdrop-blur-sm rounded-lg p-3 text-center">
                                <div className="text-white text-xl font-bold">{statusCounts.all}</div>
                                <div className="text-orange-100 text-sm">Total</div>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Status Filter Tabs */}
                <div className="bg-white rounded-lg shadow-sm mb-6 p-1">
                    <div className="flex space-x-1">
                        {[
                            { key: 'all', label: 'All Requests', icon: '📋' },
                            { key: 'pending', label: 'Pending', icon: '⏳' },
                            { key: 'confirmed', label: 'Confirmed', icon: '✅' },
                            { key: 'completed', label: 'Completed', icon: '🎉' },
                            { key: 'cancelled', label: 'Cancelled', icon: '❌' }
                        ].map(tab => (
                            <button
                                key={tab.key}
                                onClick={() => setFilter(tab.key)}
                                className={`flex-1 px-4 py-3 rounded-md text-sm font-medium transition-colors flex items-center justify-center space-x-2 ${
                                    filter === tab.key
                                        ? 'bg-orange-500 text-white'
                                        : 'text-gray-700 hover:bg-gray-100'
                                }`}
                            >
                                <span>{tab.icon}</span>
                                <span>{tab.label}</span>
                                <span className="bg-white/20 rounded-full px-2 py-1 text-xs">
                                    {statusCounts[tab.key]}
                                </span>
                            </button>
                        ))}
                    </div>
                </div>

                {/* Bookings Grid */}
                <div className="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                    {filteredBookings.length === 0 ? (
                        <div className="col-span-full text-center py-12 bg-white rounded-lg shadow-sm">
                            <div className="text-6xl mb-4">📭</div>
                            <h3 className="text-xl font-semibold text-gray-600 mb-2">No requests found</h3>
                            <p className="text-gray-500">
                                {filter === 'all' 
                                    ? 'No bookings received in the last 24 hours.' 
                                    : `No ${filter} bookings in the last 24 hours.`}
                            </p>
                        </div>
                    ) : (
                        filteredBookings.map(booking => {
                            const urgency = getUrgencyLevel(booking.createdAt);
                            return (
                                <div
                                    key={booking._id}
                                    className="bg-white rounded-lg shadow-sm border hover:shadow-md transition-shadow p-6"
                                >
                                    {/* Header */}
                                    <div className="flex items-center justify-between mb-4">
                                        <div className="flex items-center space-x-2">
                                            <span className="text-2xl">{getStatusIcon(booking.status)}</span>
                                            <span className={`px-3 py-1 rounded-full text-sm font-medium border ${getStatusColor(booking.status)}`}>
                                                {booking.status}
                                            </span>
                                        </div>
                                        <span className={`text-sm font-medium ${urgency.color}`}>
                                            {urgency.label}
                                        </span>
                                    </div>

                                    {/* Customer Info */}
                                    <div className="bg-gray-50 rounded-lg p-4 mb-4">
                                        <h3 className="font-semibold text-gray-900 mb-2">Customer Details</h3>
                                        <div className="space-y-2 text-sm">
                                            <div className="flex justify-between">
                                                <span className="text-gray-600">Name:</span>
                                                <span className="font-medium">{booking.user?.name || 'N/A'}</span>
                                            </div>
                                            <div className="flex justify-between">
                                                <span className="text-gray-600">Phone:</span>
                                                <span className="font-medium">{booking.phone || booking.user?.phone || 'N/A'}</span>
                                            </div>
                                            <div className="flex justify-between">
                                                <span className="text-gray-600">Email:</span>
                                                <span className="font-medium text-xs">{booking.user?.email || 'N/A'}</span>
                                            </div>
                                        </div>
                                    </div>

                                    {/* Service Details */}
                                    <div className="mb-4">
                                        <h4 className="font-medium text-gray-900 mb-2">Service Details</h4>
                                        {booking.items?.map((item, index) => (
                                            <div key={index} className="bg-blue-50 rounded-lg p-3 mb-2">
                                                <div className="flex justify-between items-start">
                                                    <div>
                                                        <span className="font-medium text-blue-900">{item.serviceName}</span>
                                                        {item.selectedOption?.name && (
                                                            <div className="text-sm text-blue-700">
                                                                Option: {item.selectedOption.name}
                                                            </div>
                                                        )}
                                                    </div>
                                                    <span className="font-bold text-blue-900">
                                                        ₹{item.selectedOption?.price || 0}
                                                    </span>
                                                </div>
                                            </div>
                                        ))}
                                        <div className="flex justify-between items-center mt-3 pt-3 border-t border-gray-200">
                                            <span className="font-semibold">Total Amount:</span>
                                            <span className="font-bold text-lg text-green-600">₹{booking.totalAmount}</span>
                                        </div>
                                    </div>

                                    {/* Additional Info */}
                                    <div className="space-y-2 text-sm text-gray-600 mb-4">
                                        {booking.preferredDate && (
                                            <div>
                                                <span className="font-medium">Preferred Date:</span>
                                                <span className="ml-2">{new Date(booking.preferredDate).toLocaleDateString()}</span>
                                            </div>
                                        )}
                                        <div>
                                            <span className="font-medium">Address:</span>
                                            <div className="mt-1 text-xs bg-gray-100 p-2 rounded">
                                                {booking.serviceAddress}
                                            </div>
                                        </div>
                                        {booking.notes && (
                                            <div>
                                                <span className="font-medium">Notes:</span>
                                                <div className="mt-1 text-xs bg-yellow-50 p-2 rounded">
                                                    {booking.notes}
                                                </div>
                                            </div>
                                        )}
                                        <div className="text-xs text-gray-500">
                                            Received: {formatTimeAgo(booking.createdAt)}
                                        </div>
                                    </div>

                                    {/* Photos (show only if there are any) */}
                                    {(() => {
                                        // Collect and filter only valid string image URLs
                                        const images = (booking.items || []).flatMap(item => Array.isArray(item.images) ? item.images : []);
                                        const validImages = images.filter(Boolean).filter(img => typeof img === 'string');

                                        if (validImages.length === 0) return null;

                                        return (
                                            <div className="mb-4">
                                                <h4 className="font-medium text-gray-900 mb-2">Photos</h4>
                                                <div className="flex space-x-3 overflow-x-auto py-2">
                                                    {validImages.map((imgUrl, i) => {
                                                        if (typeof imgUrl !== 'string') return null;

                                                        // Normalize Windows backslashes to forward slashes
                                                        let cleaned = imgUrl.replace(/\\/g, '/').trim();

                                                        // If it's already an absolute URL, use it
                                                        if (/^https?:\/\//i.test(cleaned)) {
                                                            // nothing
                                                        } else {
                                                            // Remove any leading slashes
                                                            cleaned = cleaned.replace(/^\/+/, '');

                                                            // Build base url from API_URL but strip trailing '/api' if present
                                                            const apiRoot = API_URL.replace(/\/api\/?$/i, '').replace(/\/$/, '');

                                                            // If cleaned already contains 'uploads' or 'bookings' assume it's served from root
                                                            cleaned = `${apiRoot}/${cleaned}`;
                                                        }

                                                        const src = cleaned;

                                                        return (
                                                            <a key={i} href={src} target="_blank" rel="noreferrer" title={src}>
                                                                <img
                                                                    src={src}
                                                                    alt={`booking-photo-${i}`}
                                                                    onError={(e) => { e.currentTarget.onerror = null; e.currentTarget.src = 'https://via.placeholder.com/300x200?text=Image+not+found'; }}
                                                                    className="h-28 w-36 object-cover rounded-md border border-gray-200 shadow-sm"
                                                                />
                                                            </a>
                                                        );
                                                    })}
                                                </div>
                                            </div>
                                        );
                                    })()}

                                    {/* Action Buttons */}
                                    <div className="space-y-2">
                                        {/* Quick Call Button */}
                                        {(booking.phone || booking.user?.phone) && (
                                            <a
                                                href={`tel:${booking.phone || booking.user?.phone}`}
                                                className="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors"
                                            >
                                                📞 Call Customer
                                            </a>
                                        )}
                                        
                                        {/* Status Update Buttons */}
                                        <div className="grid grid-cols-2 gap-2">
                                            {booking.status === 'Pending' && (
                                                <>
                                                    <button
                                                        onClick={() => updateBookingStatus(booking._id, 'Confirmed')}
                                                        className="px-3 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700 transition-colors"
                                                    >
                                                        ✅ Confirm
                                                    </button>
                                                    <button
                                                        onClick={() => updateBookingStatus(booking._id, 'Cancelled')}
                                                        className="px-3 py-2 bg-red-600 text-white text-sm rounded-md hover:bg-red-700 transition-colors"
                                                    >
                                                        ❌ Cancel
                                                    </button>
                                                </>
                                            )}
                                            
                                            {booking.status === 'Confirmed' && (
                                                <button
                                                    onClick={() => updateBookingStatus(booking._id, 'Completed')}
                                                    className="col-span-2 px-3 py-2 bg-green-600 text-white text-sm rounded-md hover:bg-green-700 transition-colors"
                                                >
                                                    🎉 Mark Complete
                                                </button>
                                            )}
                                        </div>
                                    </div>
                                </div>
                            );
                        })
                    )}
                </div>
            </div>
        </div>
    );
};

export default AdminRecentRequests;