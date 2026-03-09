import React, { useState } from 'react';
import { Phone, Mail, MapPin, Send, CheckCircle, AlertCircle } from 'lucide-react';
import { submitContactForm } from '../api/apiService';
import { trackContactFormSubmission } from '../utils/analytics';

const ContactPage = () => {
    const [formData, setFormData] = useState({
        name: '',
        email: '',
        phone: '',
        message: ''
    });
    const [submitted, setSubmitted] = useState(false);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState('');

    const handleChange = (e) => {
        setFormData({
            ...formData,
            [e.target.name]: e.target.value
        });
        setError(''); // Clear error on input change
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);
        setError('');
        
        try {
            const response = await submitContactForm(formData);
            
            if (response.data.success) {
                setSubmitted(true);
                setFormData({ name: '', email: '', phone: '', message: '' });
                
                // Track contact form conversion
                trackContactFormSubmission();
                
                setTimeout(() => {
                    setSubmitted(false);
                }, 5000);
            }
            } catch (err) {
            console.error('Contact form error:', err);
            setError(
                err.response?.data?.error || 
                'Failed to send message. Please try again or call us at +919689861811'
            );
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="bg-gray-100 min-h-screen py-12">
            <div className="max-w-6xl mx-auto px-4">
                <div className="text-center mb-12">
                    <h1 className="text-4xl font-black text-gray-900 mb-4">Contact Us</h1>
                    <p className="text-xl text-gray-600">
                        We'd love to hear from you. Get in touch with us today!
                    </p>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    {/* Contact Information */}
                    <div className="bg-white rounded-xl shadow-md p-8">
                        <h2 className="text-2xl font-bold text-gray-900 mb-6">Get In Touch</h2>
                        
                        <div className="space-y-6">
                            <div className="flex items-start gap-4">
                                <div className="bg-orange-100 p-3 rounded-full">
                                    <Phone className="w-6 h-6 text-orange-600" />
                                </div>
                                <div>
                                    <h3 className="font-semibold text-gray-900 mb-1">Phone</h3>
                                    <div className="space-y-1">
                                        <a 
                                            href="tel:+919689861811" 
                                            className="text-orange-600 hover:text-orange-700 font-medium text-lg block"
                                        >
                                            +919689861811
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div className="flex items-start gap-4">
                                <div className="bg-orange-100 p-3 rounded-full">
                                    <Mail className="w-6 h-6 text-orange-600" />
                                </div>
                                <div>
                                    <h3 className="font-semibold text-gray-900 mb-1">Email</h3>
                                    <a 
                                        href="mailto:info@khushihomesofarepairing.com" 
                                        className="text-orange-600 hover:text-orange-700"
                                    >
                                        info@khushihomesofarepairing.com
                                    </a>
                                </div>
                            </div>

                            <div className="flex items-start gap-4">
                                <div className="bg-orange-100 p-3 rounded-full">
                                    <MapPin className="w-6 h-6 text-orange-600" />
                                </div>
                                <div>
                                    <h3 className="font-semibold text-gray-900 mb-2">Our Locations</h3>
                                    <div className="space-y-3">
                                        <p className="text-gray-600 text-sm">
                                            <span className="font-medium text-gray-700">Main Office:</span><br />
                                            Digvijay Heights, Back Gate Swami Narayan Temple<br />
                                            Narhe, Pune, Maharashtra, India 411041
                                        </p>
                                        <p className="text-gray-600 text-sm">
                                            <span className="font-medium text-gray-700">Branch Office:</span><br />
                                            Office No. 15, Ground Floor, Orchid Plaza<br />
                                            In Front of Fashion College, Narhe<br />
                                            Pune 411041
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="mt-8 pt-8 border-t border-gray-200">
                            <h3 className="font-semibold text-gray-900 mb-3">Business Hours</h3>
                            <div className="space-y-2 text-gray-600">
                                <p>Monday - Saturday: 9:00 AM - 8:00 PM</p>
                                <p>Sunday: 10:00 AM - 6:00 PM</p>
                            </div>
                        </div>
                    </div>

                    {/* Contact Form */}
                    <div className="bg-white rounded-xl shadow-md p-8">
                        <h2 className="text-2xl font-bold text-gray-900 mb-6">Send us a Message</h2>
                        
                        {submitted && (
                            <div className="mb-6 p-4 bg-green-100 border border-green-400 rounded-lg flex items-center gap-2">
                                <CheckCircle className="w-5 h-5 text-green-600" />
                                <p className="text-green-700 font-medium">
                                    Thank you! We'll get back to you soon.
                                </p>
                            </div>
                        )}

                        {error && (
                            <div className="mb-6 p-4 bg-red-100 border border-red-400 rounded-lg flex items-center gap-2">
                                <AlertCircle className="w-5 h-5 text-red-600" />
                                <p className="text-red-700 font-medium">{error}</p>
                            </div>
                        )}

                        <form onSubmit={handleSubmit} className="space-y-4">
                            <div>
                                <label htmlFor="name" className="block text-sm font-medium text-gray-700 mb-1">
                                    Your Name *
                                </label>
                                <input
                                    type="text"
                                    id="name"
                                    name="name"
                                    value={formData.name}
                                    onChange={handleChange}
                                    required
                                    className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                    placeholder="Enter your name"
                                />
                            </div>

                            <div>
                                <label htmlFor="email" className="block text-sm font-medium text-gray-700 mb-1">
                                    Email Address *
                                </label>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    value={formData.email}
                                    onChange={handleChange}
                                    required
                                    className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                    placeholder="your@email.com"
                                />
                            </div>

                            <div>
                                <label htmlFor="phone" className="block text-sm font-medium text-gray-700 mb-1">
                                    Phone Number
                                </label>
                                <input
                                    type="tel"
                                    id="phone"
                                    name="phone"
                                    value={formData.phone}
                                    onChange={handleChange}
                                    className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                    placeholder="+91 XXXXXXXXXX"
                                />
                            </div>

                            <div>
                                <label htmlFor="message" className="block text-sm font-medium text-gray-700 mb-1">
                                    Message *
                                </label>
                                <textarea
                                    id="message"
                                    name="message"
                                    value={formData.message}
                                    onChange={handleChange}
                                    required
                                    rows="5"
                                    className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent resize-none"
                                    placeholder="How can we help you?"
                                />
                            </div>

                            <button
                                type="submit"
                                disabled={loading}
                                className="w-full bg-orange-600 text-white py-3 rounded-lg font-semibold hover:bg-orange-700 transition-colors flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                {loading ? (
                                    <span>Sending...</span>
                                ) : (
                                    <>
                                        <Send className="w-5 h-5" />
                                        Send Message
                                    </>
                                )}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default ContactPage;
