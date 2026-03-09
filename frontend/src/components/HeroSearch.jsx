import React, { useEffect, useState } from 'react';
import { getCategories } from '../api/apiService';
import { useNavigate } from 'react-router-dom';
import { FaSearch } from 'react-icons/fa';
import ServiceScrollList from './ServiceScrollList';

const HeroSearch = () => {
    const [categories, setCategories] = useState([]);
    const [searchTerm, setSearchTerm] = useState('');
    const [loading, setLoading] = useState(true);
    const navigate = useNavigate();

    useEffect(() => {
        const fetchCategories = async () => {
            try {
                const res = await getCategories();
                setCategories(res.data.slice(0, 9));
            } catch (err) {
                console.error('Error loading categories:', err);
            } finally {
                setLoading(false);
            }
        };
        fetchCategories();
    }, []);

    const handleSearch = (e) => {
        e.preventDefault();
        if (searchTerm.trim()) {
            navigate(`/services/search?q=${encodeURIComponent(searchTerm)}`);
        }
    };

    return (
        <section className="bg-linear-to-br from-orange-50 to-white px-1  pt-10 pb-16 lg:px-3 text-center">
            <h1 className="text-4xl md:text-5xl font-black text-gray-900 mb-3">Your Home Service Hub</h1>
            <p className="text-lg text-gray-600 mb-8">Find trusted local professionals near you</p>

            <form onSubmit={handleSearch} className="flex flex-col md:flex-row items-center justify-center max-w-2xl mx-auto bg-white border border-gray-200 rounded-xl shadow-lg overflow-hidden">
                
                <input
                    type="text"
                    value={searchTerm}
                    onChange={(e) => setSearchTerm(e.target.value)}
                    placeholder="Search for services..."
                    className="grow p-3 text-gray-800 focus:outline-none"
                />
                <button type="submit" className="bg-orange-600 hover:bg-orange-700 text-white px-6 py-3 transition-colors font-semibold">
                    <FaSearch />
                </button>
            </form>

            <div className="mt-10">
                <h3 className="text-xl font-bold mb-4 text-gray-900">Browse Categories</h3>
                <ServiceScrollList items={categories} loading={loading} type="category" />
            </div>
        </section>
    );
};

export default HeroSearch;
