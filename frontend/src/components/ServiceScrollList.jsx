import React from 'react';
import { Link } from 'react-router-dom';
import { FaStar } from 'react-icons/fa';

const ServiceScrollList = ({ items, loading, type }) => {
    if (loading) return <p className="text-center text-gray-600">Loading...</p>;
    if (!Array.isArray(items) || items.length === 0) return <p className="text-center text-gray-600">No items found.</p>;

    if (type === 'category') {
        return (
            <div className="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-6 gap-4">
                {items.map(cat => (
                    <Link to={`/services/${cat._id}`} key={cat._id}>
                        <div className="bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md p-4 flex flex-col items-center text-center transition-all">
                            <img src={cat.iconUrl || '/default.png'} alt={cat.name} className="w-12 h-12 mb-2 object-contain" />
                            <p className="text-sm font-medium text-gray-800">{cat.name}</p>
                        </div>
                    </Link>
                ))}
            </div>
        );
    }

    return (
        <div className="overflow-x-auto hide-scrollbar">
            <div className="flex space-x-4 pb-3">
                {items.map(service => (
                    <Link to={`/service/${service._id}`} key={service._id} className="min-w-[220px] flex-shrink-0">
                        <div className="bg-gray-50 rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-all">
                            <img src={service.imageUrl || '/default-service.jpg'} alt={service.name} className="w-full h-32 object-cover rounded-t-xl" />
                            <div className="p-4">
                                <h4 className="font-bold text-gray-900 mb-1 line-clamp-1">{service.name}</h4>
                                <div className="flex items-center text-xs text-gray-500">
                                    <FaStar className="text-yellow-500 mr-1" /> {service.averageRating.toFixed(1) || 4.5}
                                </div>
                                {/* <p className="text-sm text-green-600 font-semibold mt-1">
                                    ₹{service.basePrice} - ₹{service.priceUpperRange}
                                </p> */}
                                 <p className="text-sm font-semibold text-blue-600 mt-1">
                                    Contact for Pricing
                                </p>
                            </div>
                        </div>
                    </Link>
                ))}
            </div>
        </div>
    );
};

export default ServiceScrollList;
