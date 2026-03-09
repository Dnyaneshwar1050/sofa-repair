import React, { useEffect, useState } from 'react';
import { fetchAllBookings, updateBookingStatus, deleteBooking } from '../api/apiService';
import { toast } from 'sonner';
import { Phone, MapPin } from 'lucide-react';

const AdminBookingManagement = () => {
    const [bookings, setBookings] = useState([]);
    const [loading, setLoading] = useState(true);
    const [apiError, setApiError] = useState(null);

    const fetchAllBookingsData = async () => {
        try {
            const res = await fetchAllBookings();
            setBookings(res.data);
            setApiError(null);
        } catch (err) {
            setApiError("Failed to fetch bookings. Check API token.");
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchAllBookingsData();
    }, []);

    const handleStatusChange = async (bookingId, status) => {
        try {
            setLoading(true);
            await updateBookingStatus(bookingId, status);
            toast.success(`Booking status updated to ${status}.`);
            await fetchAllBookingsData();
        } catch (err) {
            toast.error(err.response?.data?.message || "Failed to update booking status.");
        } finally {
            setLoading(false);
        }
    };

    const handleDelete = async (bookingId) => {
        if (window.confirm("Are you sure you want to delete this booking request? This action is irreversible.")) {
            try {
                setLoading(true);
                await deleteBooking(bookingId);
                toast.success("Booking deleted successfully.");
                await fetchAllBookingsData();
            } catch (err) {
                toast.error(err.response?.data?.message || "Failed to delete booking.");
            } finally {
                setLoading(false);
            }
        }
    };

    // Helper function 
    const formatAddress = (address) => {
        if (!address || typeof address !== "object") return null;

        return [
            address.houseNo,
            address.street,
            address.landmark,
            address.area,
            address.city,
            address.pincode
        ]
            .filter(Boolean)       // remove undefined/null
            .join(", ");
    };
    const getGoogleMapsUrl = (address) => {
        const fullAddress = formatAddress(address);
        if (!fullAddress) return "#";
        return `https://maps.google.com/?q=${encodeURIComponent(fullAddress)}`;
    };


    if (loading) return <p className="text-center py-10">Loading Bookings...</p>;
    if (apiError) return <p className="text-center text-red-500 py-10">Error: {apiError}</p>;

    return (
        <div className='max-w-7xl mx-auto p-6'>
            <h2 className="text-3xl font-black mb-6 text-blue-900">Booking Requests Management</h2>
            <div className="overflow-x-auto shadow-xl rounded-xl">
                <table className="min-w-full text-left bg-white text-gray-700">
                    <thead className="bg-gray-100 text-sm uppercase text-gray-900 font-semibold">
                        <tr>
                            <th className="py-3 px-4">Booking ID</th>
                            <th className="py-3 px-4">Date & Time</th> {/* NEW: Date & Time column header */}
                            <th className="py-3 px-4">Customer</th>
                            <th className="py-3 px-4">Phone</th>
                            <th className="py-3 px-4">Address</th>
                            <th className="py-3 px-4">Service Name</th>
                            <th className="py-3 px-4">Price</th>
                            <th className="py-3 px-4">Current Status</th>
                            <th className="py-3 px-4">Change Status To</th>
                            <th className="py-3 px-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {bookings.length > 0 ? (
                            bookings.map((booking) => (
                                <tr key={booking._id} className="border-b border-gray-200 hover:bg-gray-50 transition-colors">
                                    <td className="py-4 px-4 font-bold text-gray-900 whitespace-nowrap">#{booking._id.slice(-6)}</td>
                                    {/* NEW: Date & Time cell */}
                                    <td className="p-4 text-sm text-gray-600 whitespace-nowrap">
                                        {new Date(booking.createdAt).toLocaleString()}
                                    </td>
                                    <td className="p-4 font-medium text-gray-900">{booking.user?.name || 'N/A'}</td>
                                    <td className="p-4">
                                        {booking.phone ? (
                                            <a
                                                href={`tel:${booking.phone}`}
                                                className="inline-flex items-center gap-2 bg-green-600 text-white font-semibold px-4 py-2 rounded-lg hover:bg-green-700 transition-colors"
                                            >
                                                <Phone className="w-4 h-4" />
                                                {booking.phone}
                                            </a>
                                        ) : (
                                            <span className="text-gray-500">No phone</span>
                                        )}
                                    </td>
                                    <td className="p-4">
                                        {(booking.address || booking.user?.address) ? (
                                            <div className='flex flex-col items-start space-y-1'>
                                                <span className="text-sm text-gray-700 whitespace-pre-wrap">
                                                    {formatAddress(booking.address || booking.user.address)}
                                                </span>
                                                <a
                                                    href={getGoogleMapsUrl(booking.address || booking.user.address)}
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    className="inline-flex items-center gap-1 text-xs font-medium text-blue-600 hover:text-blue-800 transition-colors"
                                                >
                                                    <MapPin className="w-3 h-3" />
                                                    Directions
                                                </a>
                                            </div>
                                        ) : (
                                            <span className="text-gray-500">No Address</span>
                                        )}
                                    </td>
                                    <td className="p-4 font-medium text-gray-900">{booking.items[0]?.serviceName || 'N/A'}</td>
                                    <td className="p-4 font-bold text-green-600">₹{booking.totalAmount.toLocaleString()}</td>
                                    <td className="p-4">
                                        <span className={`px-3 py-1 rounded-full text-sm font-semibold 
                                            ${booking.status === 'Completed' ? 'bg-green-100 text-green-800' :
                                                booking.status === 'Confirmed' ? 'bg-blue-100 text-blue-800' :
                                                    'bg-yellow-100 text-yellow-800'}`}>
                                            {booking.status}
                                        </span>
                                    </td>
                                    <td className="p-4">
                                        <select
                                            value={booking.status}
                                            onChange={(e) => handleStatusChange(booking._id, e.target.value)}
                                            className='bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-2 focus:ring-yellow-500 block p-2.5 transition-colors'>
                                            <option value="Pending">Pending</option>
                                            <option value="Confirmed">Confirmed</option>
                                            <option value="Completed">Completed</option>
                                            <option value="Cancelled">Cancelled</option>
                                        </select>
                                    </td>
                                    <td className="p-4">
                                        <button
                                            onClick={() => handleDelete(booking._id)}
                                            disabled={loading}
                                            className='bg-red-600 text-white font-semibold px-4 py-2 rounded-lg hover:bg-red-700 transition-colors disabled:opacity-50'>
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            ))
                        ) : (
                            <tr>
                                <td colSpan={10} className="p-4 text-center text-gray-700">No booking requests found.</td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>
        </div>
    )
}

export default AdminBookingManagement;