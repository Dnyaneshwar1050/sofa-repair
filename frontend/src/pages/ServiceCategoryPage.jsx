import React, { useEffect, useState } from 'react';
import { useParams, useSearchParams } from 'react-router-dom'; 
import { getServices, getCategories } from '../api/apiService';
import ServiceCard from '../components/ServiceCard';
import { FaChevronLeft } from 'react-icons/fa'; 

const ServiceCategoryPage = () => {
    const { categoryId } = useParams(); 
    const [searchParams] = useSearchParams(); 
    
    const [services, setServices] = useState([]);
    const [pageTitle, setPageTitle] = useState('Services');
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        const fetchServicesData = async () => {
            setLoading(true);
            setError(null);

            // 1. Determine the filtering method and parameters
            const searchQuery = searchParams.get('q'); 
            let params = {};
            let title = "All Services";

            if (searchQuery) {
                params.search = searchQuery;
                title = `Results for: "${searchQuery}"`;
            } else if (categoryId && categoryId !== 'search') {
                params.categoryId = categoryId;
                
                try {
                    const categoriesResponse = await getCategories();
                    // Ensure categories data is an array
                    const categoriesData = Array.isArray(categoriesResponse.data) ? categoriesResponse.data : [];
                    const currentCategory = categoriesData.find(cat => cat._id === categoryId);
                    title = currentCategory ? currentCategory.name : 'Category Services';
                } catch (e) {
                    console.error("Error fetching category name:", e);
                }
            }

            setPageTitle(title);

            // 2. Fetch services using the determined parameters
            try {
                const servicesResponse = await getServices(params); 
                // Ensure services is always an array
                const servicesData = Array.isArray(servicesResponse.data) ? servicesResponse.data : [];
                setServices(servicesData);
            } catch (err) {
                console.error("Error fetching services:", err);
                setError("Failed to fetch services. Check API endpoint or parameters.");
                setServices([]); // Set empty array on error
            } finally {
                setLoading(false);
            }
        };

        fetchServicesData();
    }, [categoryId, searchParams]); 


    if (loading) return <div className="text-center mt-10">Loading services...</div>;
    if (error) return <div className="text-center mt-10 text-red-500">{error}</div>;

    return (
        <div className="max-w-6xl mx-auto p-4 ">
            <div className="pt-6 pb-4">
                <button onClick={() => window.history.back()} className="text-blue-600 font-medium hover:underline flex items-center">
                    <FaChevronLeft className="mr-2 text-sm" /> Back 
                </button>
            </div>

            <h1 className="text-3xl font-black mb-8 text-gray-900 border-b pb-4">
                {pageTitle}
            </h1>

            {services.length === 0 ? (
                <p className="text-center text-xl text-gray-600 mt-10">
                    No services found matching your criteria.
                </p>
            ) : (
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    {services.map((service) => (
                        <ServiceCard key={service._id} service={service} />
                    ))}
                </div>
            )}
        </div>
    );
};

export default ServiceCategoryPage;