import React, { useEffect, useState } from 'react';
import { getContactMessages, updateContactStatus, deleteContactMessage } from '../api/apiService';
import { toast } from 'sonner';
import { Phone, Mail, Clock, User, MessageSquare, Trash2 } from 'lucide-react';

const AdminContactManagement = () => {
    const [messages, setMessages] = useState([]);
    const [loading, setLoading] = useState(true);
    const [apiError, setApiError] = useState(null);
    const [filter, setFilter] = useState('all'); // all, new, read, responded, archived

    const fetchMessages = async () => {
        try {
            const res = await getContactMessages();
            // Backend returns { success: true, data: { messages: [...], pagination: {...} } }
            setMessages(res.data.data?.messages || res.data.messages || []);
            setApiError(null);
        } catch (err) {
            console.error('Failed to fetch contact messages:', err);
            setApiError("Failed to fetch contact messages. Check API token.");
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchMessages();
    }, []);

    const handleStatusChange = async (messageId, status) => {
        try {
            await updateContactStatus(messageId, status);
            toast.success(`Message status updated to ${status}.`);
            await fetchMessages();
        } catch (err) {
            console.error('Status update error:', err);
            toast.error(err.response?.data?.error || "Failed to update message status.");
        }
    };

    const handleDelete = async (messageId) => {
        if (window.confirm("Are you sure you want to delete this message? This action is irreversible.")) {
            try {
                await deleteContactMessage(messageId);
                toast.success("Message deleted successfully.");
                await fetchMessages();
            } catch (err) {
                console.error('Delete error:', err);
                toast.error(err.response?.data?.error || "Failed to delete message.");
            }
        }
    };

    const formatDate = (dateString) => {
        return new Date(dateString).toLocaleString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    };

    const filteredMessages = messages.filter(msg => {
        if (filter === 'all') return true;
        return msg.status === filter;
    });

    const statusCounts = {
        all: messages.length,
        new: messages.filter(m => m.status === 'new').length,
        read: messages.filter(m => m.status === 'read').length,
        responded: messages.filter(m => m.status === 'responded').length,
        archived: messages.filter(m => m.status === 'archived').length,
    };

    if (loading) return <p className="text-center py-10">Loading Messages...</p>;
    if (apiError) return <p className="text-center text-red-500 py-10">Error: {apiError}</p>;

    return (
        <div className='max-w-7xl mx-auto p-4 md:p-6'>
            {/* Enhanced Header */}
            <div className="bg-gradient-to-r from-blue-600 to-blue-800 rounded-xl shadow-lg p-6 mb-6">
                <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h2 className="text-2xl md:text-3xl font-black text-white mb-2">
                            📧 Contact Messages Management
                        </h2>
                        <p className="text-blue-100 text-sm md:text-base">
                            Manage and respond to customer inquiries
                        </p>
                    </div>
                    <div className="flex flex-col sm:flex-row gap-3 sm:items-center">
                        <div className="bg-white/10 backdrop-blur-sm rounded-lg px-4 py-3 border border-white/20">
                            <div className="text-blue-100 text-xs mb-1">Total Messages</div>
                            <div className="text-2xl font-bold text-white">{messages.length}</div>
                        </div>
                        <div className="bg-white/10 backdrop-blur-sm rounded-lg px-4 py-3 border border-white/20">
                            <div className="text-blue-100 text-xs mb-1">New Messages</div>
                            <div className="text-2xl font-bold text-yellow-300">{statusCounts.new}</div>
                        </div>
                    </div>
                </div>
            </div>

            {/* Filter Tabs - Mobile Friendly */}
            <div className="flex gap-2 mb-6 overflow-x-auto pb-2 scrollbar-thin">
                {['all', 'new', 'read', 'responded', 'archived'].map(status => (
                    <button
                        key={status}
                        onClick={() => setFilter(status)}
                        className={`px-3 md:px-4 py-2 rounded-lg font-semibold transition-all whitespace-nowrap text-sm md:text-base ${
                            filter === status
                                ? 'bg-blue-600 text-white shadow-lg scale-105'
                                : 'bg-gray-100 text-gray-700 hover:bg-gray-200 active:scale-95'
                        }`}
                    >
                        <span className="capitalize">{status}</span>
                        <span className="ml-1 inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 text-xs font-bold rounded-full bg-white/20">
                            {statusCounts[status]}
                        </span>
                    </button>
                ))}
            </div>

            {/* Messages Grid */}
            <div className="grid gap-4">
                {filteredMessages.length > 0 ? (
                    filteredMessages.map((message) => (
                        <div 
                            key={message._id} 
                            className={`bg-white rounded-xl shadow-lg p-4 md:p-6 border-l-4 transition-all hover:shadow-xl ${
                                message.status === 'new' ? 'border-blue-500 bg-blue-50/30' :
                                message.status === 'read' ? 'border-yellow-500' :
                                message.status === 'responded' ? 'border-green-500' :
                                'border-gray-300'
                            }`}
                        >
                            {/* Header */}
                            <div className="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-3 mb-4">
                                <div className="flex items-center gap-3">
                                    <div className="bg-blue-100 p-2 md:p-3 rounded-full flex-shrink-0">
                                        <User className="w-5 h-5 md:w-6 md:h-6 text-blue-600" />
                                    </div>
                                    <div>
                                        <h3 className="text-lg md:text-xl font-bold text-gray-900">{message.name}</h3>
                                        <div className="flex items-center gap-2 text-xs md:text-sm text-gray-500 mt-1">
                                            <Clock className="w-3 h-3 md:w-4 md:h-4" />
                                            {formatDate(message.createdAt)}
                                        </div>
                                    </div>
                                </div>
                                <span className={`px-3 py-1 rounded-full text-xs md:text-sm font-semibold self-start ${
                                    message.status === 'new' ? 'bg-blue-100 text-blue-800' :
                                    message.status === 'read' ? 'bg-yellow-100 text-yellow-800' :
                                    message.status === 'responded' ? 'bg-green-100 text-green-800' :
                                    'bg-gray-100 text-gray-800'
                                }`}>
                                    {message.status.charAt(0).toUpperCase() + message.status.slice(1)}
                                </span>
                            </div>

                            {/* Contact Info - Mobile Optimized */}
                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                                <div className="flex items-center gap-2 bg-gray-50 p-3 rounded-lg">
                                    <Mail className="w-4 h-4 md:w-5 md:h-5 text-gray-400 flex-shrink-0" />
                                    <a 
                                        href={`mailto:${message.email}`}
                                        className="text-blue-600 hover:underline text-sm md:text-base truncate"
                                    >
                                        {message.email}
                                    </a>
                                </div>
                                {message.phone && (
                                    <a 
                                        href={`tel:${message.phone}`}
                                        className="flex items-center justify-center gap-2 bg-green-600 text-white font-semibold px-4 py-3 rounded-lg hover:bg-green-700 active:scale-95 transition-all shadow-md text-sm md:text-base"
                                    >
                                        <Phone className="w-4 h-4 md:w-5 md:h-5" />
                                        <span className="hidden sm:inline">Call: </span>
                                        {message.phone}
                                    </a>
                                )}
                            </div>

                            {/* Message Content */}
                            <div className="bg-gray-50 p-4 rounded-lg mb-4">
                                <div className="flex items-start gap-2 mb-2">
                                    <MessageSquare className="w-5 h-5 text-gray-400 mt-1" />
                                    <p className="text-gray-700 leading-relaxed">{message.message}</p>
                                </div>
                            </div>

                            {/* Source Tag */}
                            <div className="mb-4">
                                <span className="inline-flex items-center gap-1 text-xs bg-purple-100 text-purple-700 px-2 py-1 rounded">
                                    Source: {message.source || 'contact-form'}
                                </span>
                            </div>

                            {/* Actions */}
                            <div className="flex flex-wrap gap-3 pt-4 border-t border-gray-200">
                                <select
                                    value={message.status}
                                    onChange={(e) => handleStatusChange(message._id, e.target.value)}
                                    className='bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 px-4 py-2 transition-colors'
                                >
                                    <option value="new">New</option>
                                    <option value="read">Read</option>
                                    <option value="responded">Responded</option>
                                    <option value="archived">Archived</option>
                                </select>

                                <button
                                    onClick={() => handleDelete(message._id)}
                                    className='inline-flex items-center gap-2 bg-red-600 text-white font-semibold px-4 py-2 rounded-lg hover:bg-red-700 transition-colors'
                                >
                                    <Trash2 className="w-4 h-4" />
                                    Delete
                                </button>

                                {/* Quick Call Button */}
                                {message.phone && (
                                    <a
                                        href={`tel:${message.phone}`}
                                        className='inline-flex items-center gap-2 bg-green-600 text-white font-semibold px-4 py-2 rounded-lg hover:bg-green-700 transition-colors ml-auto'
                                    >
                                        <Phone className="w-4 h-4" />
                                        Quick Call
                                    </a>
                                )}
                            </div>
                        </div>
                    ))
                ) : (
                    <div className="text-center py-12">
                        <MessageSquare className="w-16 h-16 text-gray-300 mx-auto mb-4" />
                        <p className="text-gray-500 text-lg">No {filter !== 'all' ? filter : ''} messages found.</p>
                    </div>
                )}
            </div>
        </div>
    );
};

export default AdminContactManagement;
