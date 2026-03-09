import React, { useEffect, useState } from 'react';
import { Link } from "react-router-dom";
import { fetchAllUsers, fetchAdminServices, fetchAllBookings } from '../api/apiService';

const AdminHomePage = () => {
    const [stats, setStats] = useState({
        totalUsers: 0,
        totalServices: 0,
        totalBookings: 0,
        recentBookings: []
    });
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        const fetchStats = async () => {
            try {
                const [usersRes, servicesRes, bookingsRes] = await Promise.all([
                    fetchAllUsers(),
                    fetchAdminServices(),
                    fetchAllBookings()
                ]);

                setStats({
                    totalUsers: usersRes.data.length,
                    totalServices: servicesRes.data.length,
                    totalBookings: bookingsRes.data.length,
                    recentBookings: bookingsRes.data.slice(0, 5) 
                });
            } catch (err) {
                setError("Failed to load admin dashboard data.");
                console.error(err);
            } finally {
                setLoading(false);
            }
        };
        fetchStats();
    }, []);

    if (loading) return <p className="text-center py-10">Loading Dashboard...</p>;
    if (error) return <p className="text-center text-red-500 py-10">Error: {error}</p>;

    return (
        <div className="max-w-7xl mx-auto p-6">
            <h1 className="text-3xl font-black mb-8 text-blue-900">Admin Dashboard</h1>
            
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                {/* Total Bookings */}
                <div className="p-6 bg-white shadow-xl rounded-xl border-t-4 border-blue-500">
                    <h2 className="text-xl font-semibold mb-2 text-gray-900">Total Bookings</h2>
                    <p className="text-3xl font-bold text-blue-500">{stats.totalBookings}</p>
                    <Link to="/admin/bookings" className="text-sm text-blue-600 hover:underline">Manage Bookings</Link>
                </div>
                {/* Total Services */}
                <div className="p-6 bg-white shadow-xl rounded-xl border-t-4 border-yellow-500">
                    <h2 className="text-xl font-semibold mb-2 text-gray-900">Active Services</h2>
                    <p className="text-3xl font-bold text-yellow-600">{stats.totalServices}</p>
                    <Link to="/admin/services" className="text-sm text-blue-600 hover:underline">Manage Services</Link>
                </div>
                {/* Total Users */}
                <div className="p-6 bg-white shadow-xl rounded-xl border-t-4 border-green-500">
                    <h2 className="text-xl font-semibold mb-2 text-gray-900">Total Users</h2>
                    <p className="text-3xl font-bold text-green-600">{stats.totalUsers}</p>
                    <Link to="/admin/users" className="text-sm text-blue-600 hover:underline">Manage Users</Link>
                </div>
            </div>

            <div className="mt-12">
                <h2 className="text-2xl font-bold mb-6 text-gray-900">Recent Bookings</h2>
                <div className="overflow-x-auto shadow-xl rounded-xl">
                    <table className="min-w-full text-left bg-white text-gray-700">
                        <thead className="bg-gray-100 text-sm uppercase text-gray-900 font-semibold">
                            <tr>
                                <th className="py-3 px-4">Booking ID</th>
                                <th className="py-3 px-4">Customer</th>
                                <th className="py-3 px-4">Service</th>
                                <th className="py-3 px-4">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            {stats.recentBookings.length > 0 ? (
                                stats.recentBookings.map((booking) => (
                                    <tr key={booking._id} className="border-b border-gray-200 hover:bg-gray-50 transition-colors">
                                        <td className="p-4 font-medium text-gray-900">#{booking._id.slice(-6)}</td>
                                        <td className="p-4 font-medium text-gray-900">{booking.user?.name || 'N/A'}</td> 
                                        <td className="p-4 font-medium text-gray-900">{booking.items[0]?.serviceName || 'Service N/A'}</td>
                                        <td className="p-4">
                                            <span className='bg-yellow-100 text-yellow-800 text-sm font-semibold px-2 py-1 rounded-full'>
                                                {booking.status}
                                            </span>
                                        </td>
                                    </tr>
                                ))
                            ) : (
                                <tr>
                                    <td colSpan={4} className="p-4 text-center text-gray-700">No recent bookings.</td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    );
}

export default AdminHomePage;