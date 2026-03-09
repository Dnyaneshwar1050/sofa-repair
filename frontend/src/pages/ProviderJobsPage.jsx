import React, { useEffect, useState, useCallback } from 'react'; // ADDED useCallback
import { fetchProviderBookings, updateProviderBookingStatus } from '../api/apiService';
import { useAuth } from '../context/AuthContext';
import { toast } from 'sonner';

const ProviderJobsPage = () => {
    const [jobs, setJobs] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const { user } = useAuth(); 

    // Define fetchJobs using useCallback for memoization (good practice for functions used in effects)
    const fetchJobs = useCallback(async () => {
        try {
            const res = await fetchProviderBookings();
            setJobs(res.data);
            setError(null);
        } catch (err) {
            setError('Failed to fetch assigned jobs. Check API connection.');
        } finally {
            setLoading(false);
        }
    }, []); 

    useEffect(() => {
        setLoading(true);
        fetchJobs();
    }, [fetchJobs]); 

    if (loading) return <p className="text-center mt-10">Loading Assigned Jobs...</p>;
    if (error) return <p className="text-center mt-10 text-red-500">Error: {error}</p>;
    if (jobs.length === 0) return <p className="text-center mt-10 text-gray-500">You currently have no assigned jobs (bookings).</p>;

    const handleStatusUpdate = async (bookingId, newStatus) => {
        if (!window.confirm(`Are you sure you want to change status to ${newStatus}?`)) {
            return;
        }

        setLoading(true);
        try {
            await updateProviderBookingStatus(bookingId, newStatus);
            toast.success(`Job status updated to ${newStatus}.`);
            await fetchJobs(); 
        } catch (err) {
            toast.error(err.response?.data?.message || "Failed to update job status.");
        } finally {
            setLoading(false);
        }
    };
    
    const getStatusColor = (status) => {
        switch (status) {
            case 'Confirmed': return 'bg-blue-100 text-blue-800';
            case 'Completed': return 'bg-green-100 text-green-800';
            case 'Cancelled': return 'bg-red-100 text-red-800';
            default: return 'bg-yellow-100 text-yellow-800';
        }
    };

    return (
        <div className="max-w-7xl mx-auto p-6">
            <h1 className="text-3xl font-black mb-8 text-blue-900">My Assigned Jobs</h1>

            <div className="overflow-x-auto shadow-xl rounded-xl">
                <table className="min-w-full text-left bg-white text-gray-700">
                    <thead className="bg-gray-100 text-sm uppercase text-gray-900 font-semibold">
                        <tr>
                            <th className="py-3 px-4">Booking ID</th>
                            <th className="py-3 px-4">Service</th>
                            <th className="py-3 px-4">Customer</th>
                            <th className="py-3 px-4">Address</th>
                            <th className="py-3 px-4">Amount</th>
                            <th className="py-3 px-4">Status</th>
                            <th className="py-3 px-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {jobs.map((job) => (
                            <tr key={job._id} className="border-b border-gray-200 hover:bg-gray-50 transition-colors">
                                <td className="py-4 px-4 font-bold text-gray-900 whitespace-nowrap">#{job._id.slice(-6)}</td>
                                <td className="p-4 font-medium text-gray-900">
                                    {job.items[0]?.serviceName || 'N/A'} - {job.items[0]?.selectedOption?.name || 'Base'}
                                </td>
                                <td className="p-4 font-medium text-gray-900">
                                    {job.user?.name || 'N/A'} <br />
                                    <span className='text-sm text-gray-500'>{job.user?.phone}</span>
                                </td>
                                <td className="p-4 text-sm max-w-xs">{job.serviceAddress}</td>
                                <td className="p-4 font-bold text-green-600">₹{job.totalAmount.toLocaleString()}</td>
                                <td className="p-4">
                                    <span className={`px-3 py-1 rounded-full text-sm font-semibold ${getStatusColor(job.status)}`}>
                                        {job.status}
                                    </span>
                                </td>
                                <td className="p-4">
                                    <select
                                        value={job.status}
                                        onChange={(e) => handleStatusUpdate(job._id, e.target.value)}
                                        className='bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-2 focus:ring-yellow-500 block p-2.5 transition-colors'>
                                        <option value="Pending" disabled={job.status !== 'Pending'}>Pending</option>
                                        <option value="Confirmed">Confirmed</option>
                                        <option value="Completed">Completed</option>
                                        <option value="Cancelled">Cancelled</option>
                                    </select>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </div>
    );
};

export default ProviderJobsPage;