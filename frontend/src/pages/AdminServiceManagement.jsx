import React, { useEffect, useState, useMemo } from 'react';
import { fetchAdminServices, createService, updateService, deleteService, getCategories, toggleServiceStatus } from '../api/apiService';
import { toast } from 'sonner';
import { useAuth } from '../context/AuthContext';
import { Trash2, XCircle, ImageIcon } from 'lucide-react'; // Import icons

const initialServiceState = {
    name: '',
    shortDescription: '',
    basePrice: 0,
    priceUpperRange: 0,
    categoryId: '',
    // REMOVED: imageUrl: '', // Use images array instead
    images: [], // Holds existing URLs in edit mode
    options: [{ name: 'Base Option', price: 0, details: [] }],
    isDisabled: false
};

/**
 * @desc Component for managing services (Used by Admin for all services, Provider for own services)
 */
const AdminServiceManagement = ({ isProviderView = false }) => {
    const { user } = useAuth();
    const [services, setServices] = useState([]);
    const [categories, setCategories] = useState([]);
    const [loading, setLoading] = useState(true);
    const [apiError, setApiError] = useState(null);
    const [isFormOpen, setIsFormOpen] = useState(false);
    const [currentService, setCurrentService] = useState(initialServiceState);
    const [isEditing, setIsEditing] = useState(false);

    const [newFiles, setNewFiles] = useState([]);
    const [filesToKeep, setFilesToKeep] = useState([]);

    const fetchServicesAndCategories = async () => {
        try {
            const fetchFunction = fetchAdminServices;

            const [servicesRes, categoriesRes] = await Promise.all([
                fetchFunction(),
                getCategories()
            ]);
            setServices(servicesRes.data);
            setCategories(categoriesRes.data);
            setApiError(null);
        } catch (err) {
            setApiError("Failed to fetch data. Check API token/status.");
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchServicesAndCategories();
    }, [isProviderView]);

    const handleFormClose = () => {
        setIsFormOpen(false);
        setIsEditing(false);
        setCurrentService(initialServiceState);
        setNewFiles([]); // Clear new files
        setFilesToKeep([]); // Clear files to keep state
    };

    const handleEditClick = (service) => {
        setCurrentService({
            ...service,
            categoryId: service.category?._id || service.category,
            basePrice: service.basePrice,
            priceUpperRange: service.priceUpperRange || service.basePrice,
            isDisabled: service.isDisabled,
            images: service.images || [], // Load existing URLs
            options: service.options && service.options.length > 0 ? service.options : [{ name: 'Base Option', price: 0, details: [] }]
        });
        setFilesToKeep(service.images || []); // All existing files are kept by default
        setNewFiles([]);
        setIsEditing(true);
        setIsFormOpen(true);
    };

    const handleFileChange = (e) => {
        const selectedFiles = Array.from(e.target.files);
        const totalImages = filesToKeep.length + selectedFiles.length;

        if (totalImages > 5) {
            toast.error(`You can upload a maximum of 5 images. Current count: ${totalImages}.`);
            return;
        }

        setNewFiles(selectedFiles);
    };

    const handleRemoveExistingImage = (urlToRemove) => {
        // Remove from the list of files to be sent back to the server
        setFilesToKeep(prev => prev.filter(url => url !== urlToRemove));
    };

    const handleFormSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);

        const formData = new FormData();

        // 1. Append text/numeric fields
        Object.keys(currentService).forEach(key => {
            // Exclude arrays/internal fields/image data
            if (!['_id', 'category', 'images', 'options'].includes(key)) {
                // Ensure boolean status is sent as a string for FormData
                if (key === 'isDisabled') {
                    formData.append(key, currentService[key] ? 'true' : 'false');
                } else if (currentService[key] !== undefined && currentService[key] !== null) {
                    formData.append(key, currentService[key]);
                }
            }
        });

        // 2. Append options array as a JSON string
        formData.append('options', JSON.stringify(currentService.options));

        // 3. Append EXISTING image URLs to keep (only necessary for UPDATE)
        if (isEditing) {
            formData.append('existingImages', JSON.stringify(filesToKeep));
        }

        // 4. Append NEW files (field name 'images' must match backend multer config)
        newFiles.forEach(file => {
            formData.append('images', file);
        });


        try {
            if (isEditing) {
                await updateService(currentService._id, formData);
                toast.success("Service updated successfully.");
            } else {
                await createService(formData);
                toast.success("Service created successfully.");
            }
            handleFormClose();
            await fetchServicesAndCategories();
        } catch (err) {
            toast.error(err.response?.data?.message || err.message || "Failed to save service.");
        } finally {
            setLoading(false);
        }
    };

    // (Existing handleOptionChange, handleAddOption, handleRemoveOption functions go here)
    const handleOptionChange = (index, field, value) => {
        const newOptions = [...currentService.options];
        if (field === 'price') {
            newOptions[index][field] = Number(value);
        } else if (field === 'details') {
            newOptions[index][field] = Array.isArray(value) ? value : value.split(',').map(d => d.trim());
        } else {
            newOptions[index][field] = value;
        }
        setCurrentService({ ...currentService, options: newOptions });
    };

    const handleAddOption = () => {
        setCurrentService({
            ...currentService,
            options: [...currentService.options, { name: '', price: 0, details: [] }]
        });
    };

    const handleRemoveOption = (index) => {
        const newOptions = currentService.options.filter((_, i) => i !== index);
        setCurrentService({ ...currentService, options: newOptions });
    };

    const pageTitle = isProviderView ? "My Services Management" : "Service Catalog Management";

    const imagePreviewList = useMemo(() => {
        // Create URL objects for new files for previewing
        const newFilePreviews = newFiles.map(file => ({
            url: URL.createObjectURL(file),
            isNew: true
        }));
        // Combine existing URLs (only those to keep) and new file previews
        return [
            ...filesToKeep.map(url => ({ url, isNew: false })),
            ...newFilePreviews
        ];
    }, [filesToKeep, newFiles]);

    const maxImagesReached = imagePreviewList.length >= 5;


    if (loading) return <p className="text-center py-10">Loading Services...</p>;
    if (apiError) return <p className="text-center text-red-500 py-10">Error: {apiError}</p>;

    return (
        <div className='max-w-7xl mx-auto p-6'>
            <h2 className="text-3xl font-black mb-6 text-blue-900">{pageTitle}</h2>

            <div className="flex justify-between items-center mb-4">
                <button
                    onClick={() => { setIsEditing(false); setIsFormOpen(true); setCurrentService(initialServiceState); setNewFiles([]); setFilesToKeep([]); }}
                    className='bg-green-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-green-700 transition-colors'>
                    + Add New Service
                </button>
                {isProviderView && user && (
                    <p className="text-lg font-semibold text-gray-700">Logged in as: <span className="capitalize">{user.role}</span></p>
                )}
            </div>

            {isFormOpen && (
                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
                    <div className="bg-white p-8 rounded-xl shadow-2xl max-h-[90vh] overflow-y-auto w-full max-w-2xl">
                        <h3 className="text-2xl font-bold mb-6 text-gray-900 border-b pb-2">
                            {isEditing ? 'Edit Service' : 'Create New Service'}
                        </h3>
                        <form onSubmit={handleFormSubmit} className="space-y-4">

                            {/* Basic Fields */}
                            <div>
                                <label className="block text-sm font-medium text-gray-700">Name</label>
                                <input type="text" required value={currentService.name}
                                    onChange={(e) => setCurrentService({ ...currentService, name: e.target.value })}
                                    className="mt-1 p-2 border rounded-md w-full" />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700">Short Description</label>
                                <textarea required value={currentService.shortDescription}
                                    onChange={(e) => setCurrentService({ ...currentService, shortDescription: e.target.value })}
                                    className="mt-1 p-2 border rounded-md w-full" rows="2" />
                            </div>
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Base Price (₹)</label>
                                    <input type="number" required value={currentService.basePrice}
                                        onChange={(e) => setCurrentService({ ...currentService, basePrice: Number(e.target.value) })}
                                        className="mt-1 p-2 border rounded-md w-full" />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Maximum Expected Price (₹)</label>
                                    <input type="number" required value={currentService.priceUpperRange}
                                        onChange={(e) => setCurrentService({ ...currentService, priceUpperRange: Number(e.target.value) })}
                                        className="mt-1 p-2 border rounded-md w-full" />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Category</label>
                                    <select required value={currentService.categoryId}
                                        onChange={(e) => setCurrentService({ ...currentService, categoryId: e.target.value })}
                                        className="mt-1 p-2 border rounded-md w-full">
                                        <option value="">Select Category</option>
                                        {categories.map(cat => (
                                            <option key={cat._id} value={cat._id}>{cat.name}</option>
                                        ))}
                                    </select>
                                </div>
                            </div>

                            <h4 className="text-lg font-bold mt-4 pt-4 border-t">Service Images (Max 5)</h4>
                            <input
                                type="file"
                                multiple
                                accept="image/png, image/jpeg, image/gif"
                                onChange={handleFileChange}
                                className="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 disabled:opacity-50"
                                required={!isEditing && imagePreviewList.length === 0}
                                disabled={maxImagesReached}
                            />
                            {maxImagesReached && <p className='text-sm text-red-500'>Maximum 5 images reached.</p>}

                            {/* Image Preview Area */}
                            {imagePreviewList.length > 0 && (
                                <div className="mt-4 grid grid-cols-3 gap-3">
                                    {imagePreviewList.map((image, index) => (
                                        <div key={index} className="relative aspect-video rounded-lg overflow-hidden border">
                                            <img
                                                src={image.url}
                                                alt={`Service Preview ${index + 1}`}
                                                className="w-full h-full object-cover"
                                            />
                                            {/* Show remove button for existing images and new files */}
                                            <button
                                                type="button"
                                                onClick={() => image.isNew ? setNewFiles(prev => prev.filter(f => URL.createObjectURL(f) !== image.url)) : handleRemoveExistingImage(image.url)}
                                                className="absolute top-1 right-1 text-red-600 bg-white rounded-full p-1 shadow-lg hover:text-red-800 transition-colors"
                                            >
                                                <XCircle size={20} />
                                            </button>
                                            {/* Show badge for new files */}
                                            {image.isNew && (
                                                <span className="absolute bottom-1 left-1 bg-green-500 text-white text-xs px-2 py-1 rounded">
                                                    New
                                                </span>
                                            )}
                                            {!image.isNew && isEditing && (
                                                <span className="absolute bottom-1 left-1 bg-blue-500 text-white text-xs px-2 py-1 rounded">
                                                    Existing
                                                </span>
                                            )}
                                        </div>
                                    ))}
                                </div>
                            )}


                            {/* Option to toggle status in Edit form */}
                            {isEditing && (
                                <div className="flex items-center space-x-2 pt-2">
                                    <input
                                        type="checkbox"
                                        id="isDisabled"
                                        checked={currentService.isDisabled}
                                        onChange={(e) => setCurrentService({ ...currentService, isDisabled: e.target.checked })}
                                        className="h-4 w-4 text-blue-600 border-gray-300 rounded"
                                    />
                                    <label htmlFor="isDisabled" className="text-sm font-medium text-gray-700">
                                        Disable Service (Hide from public view)
                                    </label>
                                </div>
                            )}

                            {/* Options/Packages */}
                            <h4 className="text-lg font-bold mt-4 pt-4 border-t">Service Options/Packages</h4>
                            {currentService.options.map((option, index) => (
                                <div key={index} className="border p-4 rounded-md bg-gray-50 space-y-2">
                                    <div className="flex justify-between items-center">
                                        <p className="font-semibold">Option {index + 1}</p>
                                        <button type="button" onClick={() => handleRemoveOption(index)}
                                            className="text-red-500 hover:text-red-700 text-sm disabled:opacity-50"
                                            disabled={currentService.options.length === 1}>
                                            <Trash2 size={16} />
                                        </button>
                                    </div>
                                    <input type="text" placeholder="Option Name (e.g., 2 BHK, Rica Wax)" required value={option.name}
                                        onChange={(e) => handleOptionChange(index, 'name', e.target.value)}
                                        className="p-2 border rounded-md w-full" />
                                    <input type="number" placeholder="Price Difference (0 for base)" required value={option.price}
                                        onChange={(e) => handleOptionChange(index, 'price', e.target.value)}
                                        className="p-2 border rounded-md w-full" />
                                    <textarea placeholder="Details (one per line, comma-separated for simple view)" value={Array.isArray(option.details) ? option.details.join(', ') : ''}
                                        onChange={(e) => handleOptionChange(index, 'details', e.target.value)}
                                        className="p-2 border rounded-md w-full" rows="1" />
                                </div>
                            ))}
                            <button type="button" onClick={handleAddOption}
                                className="w-full bg-gray-200 text-gray-700 py-2 rounded-md hover:bg-gray-300">
                                + Add Another Option
                            </button>

                            {/* Actions */}
                            <div className="flex justify-end space-x-4 pt-4">
                                <button type="button" onClick={handleFormClose}
                                    className="px-6 py-2 border border-gray-300 rounded-md hover:bg-gray-100">
                                    Cancel
                                </button>
                                <button type="submit" disabled={loading}
                                    className="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 disabled:bg-gray-400">
                                    {loading ? 'Saving...' : (isEditing ? 'Update Service' : 'Create Service')}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* Service Table */}
            <div className="overflow-x-auto shadow-xl rounded-xl">
                <table className='min-w-full text-left bg-white text-gray-700'>
                    <thead className='bg-gray-100 text-sm uppercase text-gray-900 font-semibold'>
                        <tr>
                            <th className="py-3 px-4">Image</th>
                            <th className="py-3 px-4">Name</th>
                            <th className="py-3 px-4">Category</th>
                            <th className="py-3 px-4">Status</th>
                            <th className="py-3 px-4">Base Price</th>
                            <th className="py-3 px-4">Rating</th>
                            <th className="py-3 px-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {services.map((service) => (
                            <tr key={service._id} className="border-b border-gray-200 hover:bg-gray-50 transition-colors">
                                <td className='p-4'>
                                    {service.images && service.images.length > 0 ? (
                                        <img src={service.images[0]} alt={service.name} className="w-12 h-12 object-cover rounded-md" />
                                    ) : (
                                        <div className="w-12 h-12 bg-gray-200 rounded-md flex items-center justify-center text-gray-500"><ImageIcon size={16} /></div>
                                    )}
                                </td>
                                <td className='p-4 font-bold text-gray-900 whitespace-nowrap'>{service.name}</td>
                                <td className="p-4 font-medium text-gray-900">{service.category?.name || 'N/A'}</td>
                                {/* Status display cell */}
                                <td className="p-4">
                                    <span className={`px-3 py-1 rounded-full text-xs font-semibold 
                                        ${service.isDisabled ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'}`}>
                                        {service.isDisabled ? 'Disabled' : 'Enabled'}
                                    </span>
                                </td>
                                <td className="p-4 font-medium text-green-600">₹{service.basePrice ? service.basePrice.toLocaleString() : 4500}</td>
                                <td className="p-4 font-medium text-gray-900">{service.averageRating} ({service.reviewCount})</td>
                                <td className="p-4 flex space-x-2">
                                    <button
                                        onClick={() => handleEditClick(service)}
                                        className='bg-yellow-500 text-black font-semibold px-4 py-2 rounded-lg hover:bg-yellow-600 transition-colors'>
                                        Edit
                                    </button>
                                    {/* Enable/Disable Button */}
                                    <button
                                        onClick={() => handleStatusToggle(service._id, service.isDisabled)}
                                        className={`font-semibold px-4 py-2 rounded-lg transition-colors text-white ${service.isDisabled ? 'bg-green-600 hover:bg-green-700' : 'bg-orange-600 hover:bg-orange-700'
                                            }`}>
                                        {service.isDisabled ? 'Enable' : 'Disable'}
                                    </button>
                                    <button
                                        onClick={() => handleDelete(service._id)}
                                        className='bg-red-600 text-white font-semibold px-4 py-2 rounded-lg hover:bg-red-700 transition-colors'>
                                        Delete
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

export default AdminServiceManagement;