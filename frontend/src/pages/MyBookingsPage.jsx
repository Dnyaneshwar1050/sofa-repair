import React, { useEffect, useState } from 'react';
import { getMyBookings } from '../api/apiService';
import { useAuth } from '../context/AuthContext';
import { useNavigate } from 'react-router-dom';
import { 
  Calendar, 
  MapPin, 
  Package, 
  IndianRupee, 
  Clock, 
  FileText, 
  CheckCircle, 
  XCircle, 
  AlertCircle,
  Loader,
  Home,
  Phone
} from 'lucide-react';

const MyBookingsPage = () => {
  const [bookings, setBookings] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [filter, setFilter] = useState('all');
  const { token, loading: authLoading } = useAuth();
  const navigate = useNavigate();

  useEffect(() => {
    if (authLoading) return;

    if (!token) {
      navigate('/auth');
      return;
    }

    const fetchBookings = async () => {
      try {
        const res = await getMyBookings();
        setBookings(res.data);
      } catch (err) {
        setError('Failed to fetch your requests. Please log in again.', err);
      } finally {
        setLoading(false);
      }
    };
    fetchBookings();
  }, [token, authLoading, navigate]);

  const getStatusConfig = (status) => {
    switch(status) {
      case 'Confirmed': 
        return { 
          bg: 'bg-green-100', 
          text: 'text-green-800', 
          border: 'border-green-300',
          icon: CheckCircle,
          iconColor: 'text-green-600',
          gradient: 'from-green-50 to-emerald-50'
        };
      case 'Completed': 
        return { 
          bg: 'bg-blue-100', 
          text: 'text-blue-800', 
          border: 'border-blue-300',
          icon: CheckCircle,
          iconColor: 'text-blue-600',
          gradient: 'from-blue-50 to-indigo-50'
        };
      case 'Cancelled': 
        return { 
          bg: 'bg-red-100', 
          text: 'text-red-800', 
          border: 'border-red-300',
          icon: XCircle,
          iconColor: 'text-red-600',
          gradient: 'from-red-50 to-rose-50'
        };
      default: 
        return { 
          bg: 'bg-yellow-100', 
          text: 'text-yellow-800', 
          border: 'border-yellow-300',
          icon: AlertCircle,
          iconColor: 'text-yellow-600',
          gradient: 'from-yellow-50 to-amber-50'
        };
    }
  };

  const filterBookings = () => {
    if (filter === 'all') return bookings;
    return bookings.filter(b => b.status.toLowerCase() === filter.toLowerCase());
  };

  const filteredBookings = filterBookings();

  const stats = {
    total: bookings.length,
    pending: bookings.filter(b => b.status === 'Pending').length,
    confirmed: bookings.filter(b => b.status === 'Confirmed').length,
    completed: bookings.filter(b => b.status === 'Completed').length,
  };

  if (loading || authLoading) {
    return (
      <div className="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 flex items-center justify-center">
        <div className="text-center">
          <Loader className="w-12 h-12 text-blue-600 animate-spin mx-auto mb-4" />
          <p className="text-xl font-semibold text-gray-700">Loading your requests...</p>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 flex items-center justify-center p-4">
        <div className="bg-white rounded-xl shadow-2xl p-8 max-w-md text-center">
          <XCircle className="w-16 h-16 text-red-500 mx-auto mb-4" />
          <h2 className="text-2xl font-bold text-gray-900 mb-2">Oops!</h2>
          <p className="text-gray-600 mb-6">{error}</p>
          <button
            onClick={() => navigate('/auth')}
            className="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-all font-semibold"
          >
            Go to Login
          </button>
        </div>
      </div>
    );
  }

  if (bookings.length === 0) {
    return (
      <div className="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 flex items-center justify-center p-4">
        <div className="bg-white rounded-xl shadow-2xl p-8 max-w-md text-center">
          <div className="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <FileText className="w-12 h-12 text-gray-400" />
          </div>
          <h2 className="text-2xl font-bold text-gray-900 mb-2">No Requests Yet</h2>
          <p className="text-gray-600 mb-6">You haven't made any service requests yet. Start by browsing our services!</p>
          <button
            onClick={() => navigate('/')}
            className="bg-gradient-to-r from-blue-600 to-purple-600 text-white px-6 py-3 rounded-lg hover:from-blue-700 hover:to-purple-700 transition-all font-semibold inline-flex items-center gap-2"
          >
            <Home className="w-5 h-5" />
            Browse Services
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-white">
      {/* Hero Header - Full Width */}
      <div className="relative bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 overflow-hidden">
        <div className="absolute inset-0 bg-grid-white/10 [mask-image:linear-gradient(0deg,white,rgba(255,255,255,0.5))] -z-0"></div>
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 relative">
          <div className="mb-8">
            <h1 className="text-4xl md:text-5xl font-bold text-white mb-3 tracking-tight">My Service Requests</h1>
            <p className="text-xl text-white/90">Track and manage all your service bookings</p>
          </div>
          
          {/* Stats - Integrated into header */}
          <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div className="bg-white/10 backdrop-blur-md rounded-xl p-5 border border-white/20">
              <div className="text-white/80 text-sm mb-1 font-medium">Total Requests</div>
              <div className="text-3xl font-bold text-white">{stats.total}</div>
            </div>
            <div className="bg-white/10 backdrop-blur-md rounded-xl p-5 border border-white/20">
              <div className="text-yellow-200 text-sm mb-1 font-medium">Pending</div>
              <div className="text-3xl font-bold text-white">{stats.pending}</div>
            </div>
            <div className="bg-white/10 backdrop-blur-md rounded-xl p-5 border border-white/20">
              <div className="text-green-200 text-sm mb-1 font-medium">Confirmed</div>
              <div className="text-3xl font-bold text-white">{stats.confirmed}</div>
            </div>
            <div className="bg-white/10 backdrop-blur-md rounded-xl p-5 border border-white/20">
              <div className="text-blue-200 text-sm mb-1 font-medium">Completed</div>
              <div className="text-3xl font-bold text-white">{stats.completed}</div>
            </div>
          </div>
        </div>
      </div>

      {/* Main Content */}
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        {/* Filter Tabs */}
        <div className="mb-10 border-b border-gray-200">
          <div className="flex gap-1 overflow-x-auto pb-px">
            {[
              { key: 'all', label: 'All Requests', count: stats.total },
              { key: 'pending', label: 'Pending', count: stats.pending },
              { key: 'confirmed', label: 'Confirmed', count: stats.confirmed },
              { key: 'completed', label: 'Completed', count: stats.completed },
            ].map((tab) => (
              <button
                key={tab.key}
                onClick={() => setFilter(tab.key)}
                className={`px-6 py-4 font-semibold transition-all whitespace-nowrap border-b-4 ${
                  filter === tab.key
                    ? 'border-blue-600 text-blue-600 bg-blue-50/50'
                    : 'border-transparent text-gray-600 hover:text-gray-900 hover:border-gray-300'
                }`}
              >
                {tab.label}
                {tab.count > 0 && (
                  <span className={`ml-2 px-2.5 py-0.5 rounded-full text-xs font-bold ${
                    filter === tab.key ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'
                  }`}>
                    {tab.count}
                  </span>
                )}
              </button>
            ))}
          </div>
        </div>

        {/* Bookings List */}
        {filteredBookings.length === 0 ? (
          <div className="text-center py-20">
            <FileText className="w-20 h-20 text-gray-300 mx-auto mb-6" />
            <p className="text-2xl font-semibold text-gray-400">No {filter !== 'all' ? filter : ''} requests found</p>
          </div>
        ) : (
          <div className="space-y-6">
            {filteredBookings.map((booking) => {
              const statusConfig = getStatusConfig(booking.status);
              const StatusIcon = statusConfig.icon;
              
              return (
                <div 
                  key={booking._id} 
                  className="group relative bg-white border-l-4 hover:shadow-xl transition-all duration-300 overflow-hidden"
                  style={{ borderLeftColor: 
                    booking.status === 'Confirmed' ? '#10b981' : 
                    booking.status === 'Completed' ? '#3b82f6' : 
                    booking.status === 'Cancelled' ? '#ef4444' : '#eab308' 
                  }}
                >
                  {/* Card Content */}
                  <div className="p-8">
                    {/* Header Row */}
                    <div className="flex items-start justify-between gap-6 mb-6">
                      <div className="flex-1">
                        <h3 className="text-2xl font-bold text-gray-900 mb-2 group-hover:text-blue-600 transition-colors">
                          {booking.items[0].serviceName}
                        </h3>
                        <div className="flex flex-wrap items-center gap-4 text-sm text-gray-600">
                          <div className="flex items-center gap-2">
                            <Clock className="w-4 h-4" />
                            <span>{new Date(booking.createdAt).toLocaleDateString('en-IN', { 
                              day: 'numeric', 
                              month: 'short', 
                              year: 'numeric' 
                            })}</span>
                          </div>
                          <div className="flex items-center gap-2">
                            <Package className="w-4 h-4" />
                            <span className="font-medium">{booking.items[0].selectedOption?.name || 'Base Service'}</span>
                          </div>
                        </div>
                      </div>
                      <div className={`flex items-center gap-2 px-4 py-2 rounded-full font-bold ${statusConfig.bg} ${statusConfig.text}`}>
                        <StatusIcon className={`w-5 h-5 ${statusConfig.iconColor}`} />
                        <span>{booking.status}</span>
                      </div>
                    </div>

                    {/* Details Grid */}
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                      
                      {/* Price */}
                      <div className="flex items-center gap-4 p-4 bg-green-50 rounded-xl border border-green-200">
                        <div className="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                          <IndianRupee className="w-6 h-6 text-green-700" />
                        </div>
                        <div>
                          <p className="text-sm font-semibold text-gray-600">Total Amount</p>
                          <p className="text-2xl font-black text-green-700">₹{booking.totalAmount.toLocaleString('en-IN')}</p>
                        </div>
                      </div>

                      {/* Address */}
                      <div className="flex items-start gap-4 p-4 bg-gray-50 rounded-xl lg:col-span-2">
                        <div className="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0">
                          <MapPin className="w-5 h-5 text-red-600" />
                        </div>
                        <div className="flex-1 min-w-0">
                          <p className="text-sm font-semibold text-gray-600 mb-1">Service Address</p>
                          <p className="text-base font-medium text-gray-900">{booking.serviceAddress}</p>
                        </div>
                      </div>

                      {/* Phone */}
                      {booking.phone && (
                        <div className="flex items-center justify-between gap-4 p-4 bg-blue-50 rounded-xl border border-blue-200">
                          <div className="flex items-center gap-3">
                            <div className="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                              <Phone className="w-5 h-5 text-blue-600" />
                            </div>
                            <div>
                              <p className="text-sm font-semibold text-gray-600">Contact</p>
                              <p className="text-base font-bold text-gray-900">{booking.phone}</p>
                            </div>
                          </div>
                          <a
                            href={`tel:${booking.phone}`}
                            className="bg-green-600 text-white px-5 py-2.5 rounded-lg hover:bg-green-700 transition-all font-semibold text-sm shadow-md hover:shadow-lg"
                          >
                            Call
                          </a>
                        </div>
                      )}

                      {/* Notes */}
                      {booking.notes && booking.notes !== 'No specific notes provided.' && (
                        <div className="flex items-start gap-4 p-4 bg-purple-50 rounded-xl border border-purple-200 md:col-span-2">
                          <div className="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <FileText className="w-5 h-5 text-purple-600" />
                          </div>
                          <div className="flex-1">
                            <p className="text-sm font-semibold text-gray-600 mb-1">Special Notes</p>
                            <p className="text-base text-gray-900 italic">{booking.notes}</p>
                          </div>
                        </div>
                      )}
                    </div>

                    {/* Footer */}
                    <div className="mt-6 pt-6 border-t border-gray-200">
                      <p className="text-xs text-gray-500">
                        Booking ID: <span className="font-mono font-semibold text-gray-700">{booking._id}</span>
                      </p>
                    </div>
                  </div>
                </div>
              );
            })}
          </div>
        )}

        {/* Contact Support Footer */}
        <div className="mt-16 text-center py-12 border-t border-gray-200">
          <h3 className="text-2xl font-bold text-gray-900 mb-3">Need Help?</h3>
          <p className="text-gray-600 mb-6 text-lg">Have questions about your bookings? Our support team is here to help!</p>
          <button
            onClick={() => navigate('/contact')}
            className="inline-flex items-center gap-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white px-8 py-4 rounded-xl hover:from-blue-700 hover:to-purple-700 transition-all font-semibold text-lg shadow-lg hover:shadow-xl"
          >
            <Phone className="w-6 h-6" />
            Contact Support
          </button>
        </div>
      </div>
    </div>
  );
};

export default MyBookingsPage;