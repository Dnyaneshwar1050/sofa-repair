import React, { useEffect, useState } from 'react';
import { getCategories, getAllBlogs } from '../api/apiService'; 
import HeroSearch from '../components/HeroSearch';
import CategoryServiceScrollSection from '../components/CategoryServiceScrollSection';
import { Link } from 'react-router-dom';
import { BookOpen, ArrowRight } from 'lucide-react';
import { useSEO } from '../hooks/useSEO'; 

const HomePage = () => {
    const [categories, setCategories] = useState([]); 
    const [blogs, setBlogs] = useState([]);
    const [loading, setLoading] = useState(true);
    const [blogsLoading, setBlogsLoading] = useState(true);
    const [error, setError] = useState(null);

    useSEO();

    useEffect(() => {
        const fetchData = async () => {
            try {
                const [catRes, blogRes] = await Promise.all([
                    getCategories(),
                    getAllBlogs({ limit: 3 })
                ]);
                setCategories(Array.isArray(catRes.data) ? catRes.data : []);
                setBlogs(Array.isArray(blogRes.data.blogs) ? blogRes.data.blogs : []);
            } catch (err) {
                console.error("Error loading homepage data:", err);
                setError("Something went wrong while loading content.");
            } finally {
                setLoading(false);
                setBlogsLoading(false);
            }
        };
        fetchData();
    }, []);

    return (
        <div className="bg-gray-50 min-h-screen text-gray-900 ">
            <HeroSearch />

            {/* Categories Section */}
            <section className="max-w-6xl mx-auto px-4 py-12">
                <div className="flex justify-between items-center mb-8">
                    <h2 className="text-3xl font-black">Featured Services</h2>
                    <div className="h-1 w-24 bg-orange-500 rounded-full"></div>
                </div>

                {loading ? (
                    <p className="text-center text-gray-600">Loading featured services...</p>
                ) : error ? (
                    <p className="text-center text-red-500">{error}</p>
                ) : (
                    <div className="space-y-12">
                        {categories.map(category => (
                            <CategoryServiceScrollSection key={category._id} category={category} />
                        ))}
                    </div>
                )}
            </section>

            {/* Blogs Section */}
            <section className="bg-white py-16 border-t border-gray-200">
                <div className="max-w-6xl mx-auto px-4">
                    <div className="text-center mb-10">
                        <h2 className="text-3xl font-black flex justify-center items-center gap-2">
                            <BookOpen className="text-orange-600" /> Latest Articles
                        </h2>
                        <p className="text-gray-600 text-lg">Insights and tips from our experts</p>
                    </div>

                    {blogsLoading ? (
                        <p className="text-center text-gray-600">Loading blogs...</p>
                    ) : blogs.length > 0 ? (
                        <div className="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                            {blogs.map(blog => (
                                <article key={blog._id} className="bg-gray-50 border border-gray-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-all">
                                    {blog.featuredImage?.url && (
                                        <Link to={`/blog/${blog.slug}`}>
                                            <img
                                                src={blog.featuredImage.url}
                                                alt={blog.title}
                                                className="w-full h-48 object-cover"
                                            />
                                        </Link>
                                    )}
                                    <div className="p-6">
                                        <h3 className="text-lg font-bold mb-2 hover:text-orange-600 transition-colors">
                                            <Link to={`/blog/${blog.slug}`}>{blog.title}</Link>
                                        </h3>
                                        <p className="text-gray-600 text-sm line-clamp-3 mb-4">{blog.excerpt}</p>
                                        <Link 
                                            to={`/blog/${blog.slug}`}
                                            className="inline-flex items-center text-orange-600 font-semibold text-sm"
                                        >
                                            Read More <ArrowRight className="w-4 h-4 ml-1" />
                                        </Link>
                                    </div>
                                </article>
                            ))}
                        </div>
                    ) : (
                        <p className="text-center text-gray-600">No blogs found.</p>
                    )}
                </div>
            </section>

            {/* Contact Section */}
            <section className="bg-linear-to-r from-orange-600 to-orange-500 text-white py-12">
                <div className="max-w-5xl mx-auto px-4 text-center">
                    <h2 className="text-3xl font-black mb-3">Need Assistance?</h2>
                    <p className="text-lg mb-6">Our experts are always ready to help.</p>
                    <div className="space-y-4">
                        <a href="tel:+919689861811" className="block text-lg hover:text-orange-100">
                            📞 +919689861811
                        </a>
                        <a href="mailto:info@khushihomesofarepairing.com" className="block text-lg hover:text-orange-100">
                            📧 info@khushihomesofarepairing.com
                        </a>
                    </div>
                    <Link
                        to="/contact"
                        className="inline-block mt-6 bg-white text-orange-600 font-semibold px-6 py-2 rounded-lg hover:bg-orange-50 transition-colors"
                    >
                        Contact Us
                    </Link>
                </div>
            </section>
        </div>
    );
};

export default HomePage;
