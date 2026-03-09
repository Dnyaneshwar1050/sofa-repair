// service-platform/frontend/src/pages/AdminCategoryManagement.jsx
import React, { useEffect, useState } from 'react';
import { fetchAdminCategories, updateCategoryStatus } from '../api/apiService';
import { toast } from 'sonner';
import { useAuth } from '../context/AuthContext';

const AdminCategoryManagement = () => {
    const { user } = useAuth();
    const [categories, setCategories] = useState([]);
    const [loading, setLoading] = useState(true);
    const [apiError, setApiError] = useState(null);

    const fetchCategoriesData = async () => {
        try {
            // Fetch all categories (including disabled ones) for management view
            const res = await fetchAdminCategories();
            setCategories(res.data);
            setApiError(null);
        } catch (err) {
            setApiError("Failed to fetch categories. Check API token/status.");
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchCategoriesData();
    }, []);

    const handleStatusToggle = async (categoryId, currentStatus) => {
        const newStatus = !currentStatus;
        const action = newStatus ? 'Disable' : 'Enable';
        
        if (!window.confirm(`Are you sure you want to ${action} this category? This will hide all associated services from the public site.`)) {
            return;
        }

        setLoading(true);
        try {
            await updateCategoryStatus(categoryId, newStatus);
            toast.success(`Category successfully ${newStatus ? 'disabled' : 'enabled'}.`);
            await fetchCategoriesData(); // Refresh list
        } catch (err) {
            toast.error(err.response?.data?.message || "Failed to update category status.");
        } finally {
            setLoading(false);
        }
    };

    if (loading) return <p className="text-center py-10">Loading Categories...</p>;
    if (apiError) return <p className="text-center text-red-500 py-10">Error: {apiError}</p>;

    return (
        <div className='max-w-7xl mx-auto p-6'>
            <h2 className="text-3xl font-black mb-6 text-blue-900">
                {user.role === 'admin' ? 'Category Management' : 'Category Status'}
            </h2>
            <p className='text-gray-600 mb-8'>
                Use the buttons to control the public visibility of categories.
            </p>

            <div className="overflow-x-auto shadow-xl rounded-xl">
                <table className='min-w-full text-left bg-white text-gray-700'>
                    <thead className='bg-gray-100 text-sm uppercase text-gray-900 font-semibold'>
                        <tr>
                            <th className="py-3 px-4">Name</th>
                            <th className="py-3 px-4">Slug</th>
                            <th className="py-3 px-4">Status</th>
                            <th className="py-3 px-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        {categories.map((category) => (
                            <tr key={category._id} className="border-b border-gray-200 hover:bg-gray-50 transition-colors">
                                <td className='p-4 font-bold text-gray-900 whitespace-nowrap'>{category.name}</td>
                                <td className="p-4 font-medium text-gray-600">{category.slug}</td> 
                                <td className="p-4">
                                    <span className={`px-3 py-1 rounded-full text-sm font-semibold 
                                        ${category.isDisabled ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'}`}>
                                        {category.isDisabled ? 'Disabled' : 'Enabled'}
                                    </span>
                                </td>
                                <td className="p-4">
                                    <button
                                        onClick={() => handleStatusToggle(category._id, category.isDisabled)} 
                                        disabled={loading}
                                        className={`font-semibold px-4 py-2 rounded-lg transition-colors text-white ${
                                            category.isDisabled ? 'bg-green-600 hover:bg-green-700' : 'bg-yellow-600 hover:bg-yellow-700'
                                        }`}>
                                        {category.isDisabled ? 'Enable' : 'Disable'}
                                    </button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </div>
    );
};

export default AdminCategoryManagement;