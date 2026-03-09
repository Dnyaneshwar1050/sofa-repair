import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { useSettings } from '../context/SettingsContext';
import { API_URL } from '../api/apiService';

const SuperAdminSettings = () => {
    const { settings, refreshSettings } = useSettings();
    const [formData, setFormData] = useState({
        siteName: '',
        primaryColor: '#000000',
        secondaryColor: '#000000',
        logoUrl: '',
        themeMode: 'light'
    });
    const [loading, setLoading] = useState(false);
    const [message, setMessage] = useState('');

    useEffect(() => {
        if (settings) {
            setFormData({
                siteName: settings.siteName || '',
                primaryColor: settings.primaryColor || '#2563eb',
                secondaryColor: settings.secondaryColor || '#f59e0b',
                logoUrl: settings.logoUrl || '',
                themeMode: settings.themeMode || 'light'
            });
        }
    }, [settings]);

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);
        try {
            await axios.put(`${API_URL}/site-settings`, formData);
            setMessage('Branding updated successfully! Updating live site...');
            await refreshSettings(); // Trigger live update across the app
        } catch (error) {
            setMessage('Error updating settings: ' + error.message);
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="max-w-2xl mx-auto p-6 bg-white shadow-lg rounded-xl mt-10">
            <h1 className="text-2xl font-bold mb-6">White-Label Site Settings</h1>
            {message && <div className="p-3 mb-4 bg-blue-100 text-blue-700 rounded">{message}</div>}

            <form onSubmit={handleSubmit} className="space-y-4">
                <div>
                    <label className="block text-sm font-medium">Website Name</label>
                    <input
                        type="text"
                        className="w-full border p-2 rounded"
                        value={formData.siteName}
                        placeholder='Site Name'
                        onChange={(e) => setFormData({ ...formData, siteName: e.target.value })}
                    />
                </div>

                <div className="grid grid-cols-2 gap-4">
                    <div>
                        <label className="block text-sm font-medium">Primary Theme Color</label>
                        <input
                            type="color"
                            className="w-full h-10 border p-1 rounded"
                            value={formData.primaryColor}
                            onChange={(e) => setFormData({ ...formData, primaryColor: e.target.value })}
                        />
                    </div>
                    <div>
                        <label className="block text-sm font-medium">Secondary Color</label>
                        <input
                            type="color"
                            className="w-full h-10 border p-1 rounded"
                            value={formData.secondaryColor}
                            onChange={(e) => setFormData({ ...formData, secondaryColor: e.target.value })}
                        />
                    </div>
                </div>

                <div>
                    <label className="block text-sm font-medium">Logo URL</label>
                    <input
                        type="text"
                        placeholder="https://your-image-link.com/logo.png"
                        className="w-full border p-2 rounded"
                        value={formData.logoUrl}
                        onChange={(e) => setFormData({ ...formData, logoUrl: e.target.value })}
                    />
                </div>

                <div>
                    <label className="block text-sm font-medium">Default Theme Mode</label>
                    <select
                        className="w-full border p-2 rounded"
                        value={formData.themeMode}
                        onChange={(e) => setFormData({ ...formData, themeMode: e.target.value })}
                    >
                        <option value="light">Light Mode</option>
                        <option value="dark">Dark Mode</option>
                    </select>
                </div>

                <button
                    disabled={loading}
                    className="w-full bg-primary text-white py-3 rounded-lg font-bold hover:opacity-90 transition-opacity"
                >
                    {loading ? 'Saving...' : 'Update Live Website'}
                </button>
            </form>
        </div>
    );
};

export default SuperAdminSettings;