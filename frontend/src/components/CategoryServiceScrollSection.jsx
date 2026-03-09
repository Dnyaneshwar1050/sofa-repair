import React, { useEffect, useState } from 'react';
import { getServices } from '../api/apiService';
import ServiceScrollList from './ServiceScrollList';
import { Link } from 'react-router-dom';

const CategoryServiceScrollSection = ({ category }) => {
    const [services, setServices] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const fetchServices = async () => {
            try {
                const res = await getServices(category._id);
                const data = Array.isArray(res.data) ? res.data : [];
                setServices(data.slice(0, 8));
            } catch (err) {
                console.error(`Error loading ${category.name} services:`, err);
            } finally {
                setLoading(false);
            }
        };
        fetchServices();
    }, [category._id]);

    if (loading || services.length === 0) return null;

    return (
        <div className="bg-white rounded-2xl p-6 shadow-sm border border-gray-200">
            <div className="flex justify-between items-center mb-4">
                <h3 className="text-2xl font-black">{category.name}</h3>
                <Link to={`/services/${category._id}`} className="text-orange-600 font-semibold hover:underline">
                    View All →
                </Link>
            </div>
            <ServiceScrollList items={services} loading={loading} type="service" />
        </div>
    );
};

export default CategoryServiceScrollSection;
