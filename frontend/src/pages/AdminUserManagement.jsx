import React, { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import { fetchAllUsers, updateUserRole, deleteUser } from '../api/apiService';
import { useAuth } from "../context/AuthContext";
import { toast } from 'sonner';
import UserRoleUpdater from '../components/Admin/UserRoleUpdater';
import { Phone, Mail, Trash2 } from 'lucide-react';

const AdminUserManagement = () => {


    const navigate = useNavigate();
    const { user: currentUser } = useAuth();
    const [users, setUsers] = useState([]);
    const [loading, setLoading] = useState(true);
    const [apiError, setApiError] = useState(null);

    const [formData, setFormData] = useState({
        name: "",
        email: "",
        password: "",
        role: "customer",
    });

    const fetchAllUsersData = async () => {
        try {
            const res = await fetchAllUsers();
            setUsers(res.data);
            setApiError(null);
        } catch (err) {
            setApiError(err.response?.data?.message || "Failed to fetch users.");
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        if (currentUser && currentUser.role === "admin") {
            fetchAllUsersData();
        } else if (!currentUser) {
            navigate("/auth");
        }
    }, [currentUser, navigate]);


    const handleChange = (e) => {
        setFormData({
            ...formData,
            [e.target.name]: e.target.value,
        })
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        alert("Add User feature is pending implementation with client-side API call in this MVP.");
    };

    const handleRoleChange = async (userId, newRole) => {
        try {
            setLoading(true);

            await updateUserRole(userId, { role: newRole });

            toast.success(`Role updated to ${newRole}.`);

            await fetchAllUsersData();
        } catch (err) {
            const message = err.response?.data?.message || "Failed to update role.";
            setApiError(message);
            toast.error(message);
        } finally {
            setLoading(false);
        }
    };

    const handleDeleteUser = async (userId) => {
        if (window.confirm("Are you sure you want to delete this user?")) {
            try {
                setLoading(true);
                await deleteUser(userId);
                await fetchAllUsersData();
            } catch (err) {
                setApiError(err.response?.data?.message || "Failed to delete user.");
            } finally {
                setLoading(false);
            }
        }
    }

    return (
        <div className='max-w-7xl mx-auto p-4 md:p-6'>
            {/* Enhanced Header */}
            <div className="bg-gradient-to-r from-blue-600 to-blue-800 rounded-xl shadow-lg p-6 mb-6">
                <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h2 className="text-2xl md:text-3xl font-black text-white mb-2">
                            👥 User Management
                        </h2>
                        <p className="text-blue-100 text-sm md:text-base">
                            Manage user accounts, roles, and permissions
                        </p>
                    </div>
                    <div className="bg-white/10 backdrop-blur-sm rounded-lg px-4 py-3 border border-white/20">
                        <div className="text-blue-100 text-xs mb-1">Total Users</div>
                        <div className="text-2xl font-bold text-white">{users.length}</div>
                    </div>
                </div>
            </div>

            {loading && <p className="text-center py-10">Loading ...</p>}
            {apiError && <p className="text-red-500 text-center py-4 bg-red-50 rounded-lg border border-red-200">{apiError}</p>}

            <div className="p-8 rounded-xl shadow-xl bg-white mb-8">
                <h3 className="text-xl font-bold mb-4 text-gray-900">Add New User (Placeholder)</h3>
                <p className="text-gray-600">The quickest way to add users is through the registration page on the client side.</p>
            </div>

            <div className="overflow-x-auto shadow-xl rounded-xl">
                <table className="min-w-full text-left bg-white text-gray-700">
                    <thead className="bg-gray-100 text-sm uppercase text-gray-900 font-semibold">
                        <tr>
                            <th className="py-3 px-4">Name</th>
                            <th className="py-3 px-4">Email</th>
                            <th className="py-3 px-4">Verification</th>
                            <th className="py-3 px-4">Phone</th>
                            <th className="py-3 px-4">Role</th>
                            <th className="py-3 px-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {users.map((user) => (
                            <tr key={user._id} className="border-b border-gray-200 hover:bg-gray-50 transition-colors">
                                <td className="p-4 font-bold text-gray-900 whitespace-nowrap">
                                    {user.name}
                                </td>
                                <td className="p-4">
                                    {user.email ? (
                                        <a
                                            href={`mailto:${user.email}`}
                                            className="inline-flex items-center gap-2 text-blue-600 hover:text-blue-800 font-medium"
                                        >
                                            <Mail className="w-4 h-4" />
                                            <span className="hidden md:inline">{user.email}</span>
                                            <span className="md:hidden">{user.email && user.email.split('@')[0]}</span>
                                        </a>
                                    ) : (
                                        <a
                                            className="inline-flex items-center gap-2 text-blue-400 hover:text-blue-600 font-medium"
                                        >
                                            <Mail className="w-4 h-4" />
                                            <span className="">No email{user.email}</span>
                                        </a>
                                    )}
                                </td>
                                <td className="p-4">
                                    {user.isEmailVerified ? (
                                        <div className="flex items-center gap-2">
                                            <div className="w-2 h-2 bg-green-500 rounded-full"></div>
                                            <span className="text-green-700 font-semibold text-sm">Verified</span>
                                            {user.emailVerifiedAt && (
                                                <span className="text-xs text-gray-500">
                                                    {new Date(user.emailVerifiedAt).toLocaleDateString()}
                                                </span>
                                            )}
                                        </div>
                                    ) : (
                                        <div className="flex items-center gap-2">
                                            <div className="w-2 h-2 bg-red-500 rounded-full"></div>
                                            <span className="text-red-700 font-semibold text-sm">Not Verified</span>
                                        </div>
                                    )}
                                </td>
                                <td className="p-4">
                                    {user.phone ? (
                                        <a
                                            href={`tel:${user.phone}`}
                                            className="inline-flex items-center gap-2 bg-green-600 text-white font-semibold px-3 md:px-4 py-2 rounded-lg hover:bg-green-700 active:scale-95 transition-all text-sm"
                                        >
                                            <Phone className="w-4 h-4" />
                                            <span className="hidden sm:inline">{user.phone}</span>
                                            <span className="sm:hidden">Call</span>
                                        </a>
                                    ) : (
                                        <span className="text-gray-400 text-sm">No phone</span>
                                    )}
                                </td>
                                <td className="p-4 font-medium text-gray-900 whitespace-nowrap">
                                    <UserRoleUpdater user={user} refreshUsersList={fetchAllUsersData} />
                                </td>
                                <td className="p-4">
                                    <button
                                        onClick={() => handleDeleteUser(user._id)}
                                        disabled={user._id === currentUser?._id}
                                        className={`inline-flex items-center gap-2 font-semibold px-3 md:px-4 py-2 rounded-lg transition-all text-sm ${user._id === currentUser?._id
                                            ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
                                            : 'bg-red-600 text-white hover:bg-red-700 active:scale-95'
                                            }`}
                                    >
                                        <Trash2 className="w-4 h-4" />
                                        <span className="hidden sm:inline">Delete</span>
                                    </button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </div>
    )
}

export default AdminUserManagement;