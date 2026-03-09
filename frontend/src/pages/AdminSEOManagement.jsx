import React, { useState, useEffect, useCallback } from 'react';
import { 
    getSEOSettings, 
    updateSEOSettings, 
    getFestivals, 
    setManualSEOOverride, 
    disableManualSEOOverride 
} from '../api/apiService';
import { Calendar, Settings, TrendingUp, AlertCircle, CheckCircle, Eye, Edit3 } from 'lucide-react';

const AdminSEOManagement = () => {
    const [settings, setSettings] = useState(null);
    const [festivals, setFestivals] = useState({});
    const [loading, setLoading] = useState(true);
    const [activeTab, setActiveTab] = useState('dashboard');
    const [manualOverrideForm, setManualOverrideForm] = useState({
        keywords: '',
        metaTitle: '',
        metaDescription: '',
        heroText: '',
        promoText: '',
        startDate: '',
        endDate: '',
        campaignName: ''
    });
    const [message, setMessage] = useState({ type: '', text: '' });

    useEffect(() => {
        fetchData();
    }, []);

    const fetchData = useCallback(async () => {
        try {
            setLoading(true);
            const [settingsRes, festivalsRes] = await Promise.all([
                getSEOSettings(),
                getFestivals()
            ]);
            
            if (settingsRes.data.success) {
                setSettings(settingsRes.data.settings);
            }
            
            if (festivalsRes.data.success) {
                setFestivals(festivalsRes.data.festivals);
            }
        } catch (err) {
            console.error('Failed to load SEO data:', err);
            showMessage('error', 'Failed to load SEO data');
        } finally {
            setLoading(false);
        }
    }, []);

    const showMessage = (type, text) => {
        setMessage({ type, text });
        setTimeout(() => setMessage({ type: '', text: '' }), 5000);
    };

    const handleSettingsUpdate = async (updatedSettings) => {
        try {
            const response = await updateSEOSettings(updatedSettings);
            if (response.data.success) {
                setSettings(response.data.settings);
                showMessage('success', 'SEO settings updated successfully');
            }
        } catch (err) {
            console.error('Failed to update settings:', err);
            showMessage('error', 'Failed to update settings');
        }
    };

    const handleManualOverride = async () => {
        try {
            const overrideData = {
                ...manualOverrideForm,
                keywords: manualOverrideForm.keywords.split(',').map(k => k.trim()).filter(k => k)
            };
            
            const response = await setManualSEOOverride(overrideData);
            if (response.data.success) {
                setSettings(response.data.settings);
                showMessage('success', 'Manual SEO override activated');
                setActiveTab('dashboard');
                // Reset form
                setManualOverrideForm({
                    keywords: '',
                    metaTitle: '',
                    metaDescription: '',
                    heroText: '',
                    promoText: '',
                    startDate: '',
                    endDate: '',
                    campaignName: ''
                });
            }
        } catch (err) {
            console.error('Failed to set manual override:', err);
            showMessage('error', 'Failed to set manual override');
        }
    };

    const handleDisableOverride = async () => {
        try {
            const response = await disableManualSEOOverride();
            if (response.data.success) {
                setSettings(response.data.settings);
                showMessage('success', 'Manual override disabled');
            }
        } catch (err) {
            console.error('Failed to disable override:', err);
            showMessage('error', 'Failed to disable override');
        }
    };

    if (loading) {
        return (
            <div className="flex justify-center items-center h-64">
                <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            </div>
        );
    }

    const getCurrentFestival = () => {
        const today = new Date().toISOString().split('T')[0];
        const currentYear = new Date().getFullYear();
        const yearFestivals = festivals[currentYear] || [];
        
        return yearFestivals.find(festival => {
            const endDate = new Date(festival.endDate);
            const extendedEnd = new Date(endDate.getTime() + (3 * 24 * 60 * 60 * 1000));
            return today >= festival.startDate && today <= extendedEnd.toISOString().split('T')[0];
        });
    };

    const currentFestival = getCurrentFestival();
    const activeSEO = settings?.getActiveSEOData ? settings.getActiveSEOData() : null;

    return (
        <div className="max-w-7xl mx-auto p-6">
            <div className="mb-8">
                <h1 className="text-3xl font-bold text-gray-900 mb-2">SEO Management</h1>
                <p className="text-gray-600">Manage festival-based SEO and manual overrides</p>
            </div>

            {/* Message Alert */}
            {message.text && (
                <div className={`mb-6 p-4 rounded-lg flex items-center ${
                    message.type === 'success' 
                        ? 'bg-green-50 text-green-800 border border-green-200' 
                        : 'bg-red-50 text-red-800 border border-red-200'
                }`}>
                    {message.type === 'success' ? <CheckCircle className="mr-2" /> : <AlertCircle className="mr-2" />}
                    {message.text}
                </div>
            )}

            {/* Tab Navigation */}
            <div className="border-b border-gray-200 mb-8">
                <nav className="-mb-px flex space-x-8">
                    <button
                        onClick={() => setActiveTab('dashboard')}
                        className={`py-2 px-1 border-b-2 font-medium text-sm ${
                            activeTab === 'dashboard'
                                ? 'border-blue-500 text-blue-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                        }`}
                    >
                        <TrendingUp className="inline mr-2" size={16} />
                        Dashboard
                    </button>
                    <button
                        onClick={() => setActiveTab('settings')}
                        className={`py-2 px-1 border-b-2 font-medium text-sm ${
                            activeTab === 'settings'
                                ? 'border-blue-500 text-blue-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                        }`}
                    >
                        <Settings className="inline mr-2" size={16} />
                        Settings
                    </button>
                    <button
                        onClick={() => setActiveTab('manual')}
                        className={`py-2 px-1 border-b-2 font-medium text-sm ${
                            activeTab === 'manual'
                                ? 'border-blue-500 text-blue-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                        }`}
                    >
                        <Edit3 className="inline mr-2" size={16} />
                        Manual Override
                    </button>
                    <button
                        onClick={() => setActiveTab('festivals')}
                        className={`py-2 px-1 border-b-2 font-medium text-sm ${
                            activeTab === 'festivals'
                                ? 'border-blue-500 text-blue-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                        }`}
                    >
                        <Calendar className="inline mr-2" size={16} />
                        Festivals
                    </button>
                </nav>
            </div>

            {/* Dashboard Tab */}
            {activeTab === 'dashboard' && (
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    {/* Current SEO Status */}
                    <div className="bg-white rounded-lg shadow-md p-6 border">
                        <h2 className="text-xl font-semibold mb-4 flex items-center">
                            <Eye className="mr-2" />
                            Current SEO Status
                        </h2>
                        
                        {settings?.manualOverride?.isActive ? (
                            <div className="bg-orange-50 border border-orange-200 rounded-lg p-4 mb-4">
                                <h3 className="font-semibold text-orange-800">Manual Override Active</h3>
                                <p className="text-orange-700">{settings.manualOverride.campaignName || 'Custom Campaign'}</p>
                                <button
                                    onClick={handleDisableOverride}
                                    className="mt-2 bg-orange-600 text-white px-4 py-2 rounded hover:bg-orange-700"
                                >
                                    Disable Override
                                </button>
                            </div>
                        ) : currentFestival ? (
                            <div className="bg-purple-50 border border-purple-200 rounded-lg p-4 mb-4">
                                <h3 className="font-semibold text-purple-800">Festival SEO Active</h3>
                                <p className="text-purple-700">{currentFestival.name}</p>
                            </div>
                        ) : (
                            <div className="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4">
                                <h3 className="font-semibold text-gray-800">Default SEO Active</h3>
                                <p className="text-gray-700">No festival or manual override</p>
                            </div>
                        )}

                        {activeSEO && (
                            <div className="space-y-3">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Meta Title</label>
                                    <p className="text-gray-900">{activeSEO.metaTitle}</p>
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Meta Description</label>
                                    <p className="text-gray-600 text-sm">{activeSEO.metaDescription}</p>
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Hero Text</label>
                                    <p className="text-gray-900">{activeSEO.heroText}</p>
                                </div>
                                {activeSEO.keywords && (
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700">Keywords</label>
                                        <div className="flex flex-wrap gap-1 mt-1">
                                            {activeSEO.keywords.slice(0, 5).map((keyword, index) => (
                                                <span key={index} className="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">
                                                    {keyword}
                                                </span>
                                            ))}
                                        </div>
                                    </div>
                                )}
                            </div>
                        )}
                    </div>

                    {/* Quick Actions */}
                    <div className="bg-white rounded-lg shadow-md p-6 border">
                        <h2 className="text-xl font-semibold mb-4">Quick Actions</h2>
                        <div className="space-y-3">
                            <button
                                onClick={() => setActiveTab('manual')}
                                className="w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 transition-colors"
                            >
                                Create Manual Override
                            </button>
                            <button
                                onClick={() => setActiveTab('settings')}
                                className="w-full bg-gray-600 text-white py-2 px-4 rounded hover:bg-gray-700 transition-colors"
                            >
                                Update Default SEO
                            </button>
                            <button
                                onClick={fetchData}
                                className="w-full border border-gray-300 text-gray-700 py-2 px-4 rounded hover:bg-gray-50 transition-colors"
                            >
                                Refresh Data
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {/* Settings Tab */}
            {activeTab === 'settings' && settings && (
                <div className="bg-white rounded-lg shadow-md p-6 border">
                    <h2 className="text-xl font-semibold mb-6">SEO Settings</h2>
                    
                    <form onSubmit={(e) => {
                        e.preventDefault();
                        const formData = new FormData(e.target);
                        const updatedSettings = {
                            isAutoSEOEnabled: formData.get('autoSEO') === 'on',
                            festivalSettings: {
                                enableFestivalSEO: formData.get('festivalSEO') === 'on',
                                daysBeforeFestival: parseInt(formData.get('daysBefore')) || 7,
                                daysAfterFestival: parseInt(formData.get('daysAfter')) || 3
                            },
                            defaultSEO: {
                                keywords: formData.get('keywords').split(',').map(k => k.trim()).filter(k => k),
                                metaTitle: formData.get('metaTitle'),
                                metaDescription: formData.get('metaDescription'),
                                heroText: formData.get('heroText'),
                                promoText: formData.get('promoText')
                            }
                        };
                        handleSettingsUpdate(updatedSettings);
                    }}>
                        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <div>
                                <h3 className="text-lg font-medium mb-4">Auto SEO Settings</h3>
                                <div className="space-y-4">
                                    <label className="flex items-center">
                                        <input
                                            type="checkbox"
                                            name="autoSEO"
                                            defaultChecked={settings.isAutoSEOEnabled}
                                            className="mr-2"
                                        />
                                        Enable Auto SEO
                                    </label>
                                    <label className="flex items-center">
                                        <input
                                            type="checkbox"
                                            name="festivalSEO"
                                            defaultChecked={settings.festivalSettings?.enableFestivalSEO}
                                            className="mr-2"
                                        />
                                        Enable Festival SEO
                                    </label>
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700">Days Before Festival</label>
                                        <input
                                            type="number"
                                            name="daysBefore"
                                            defaultValue={settings.festivalSettings?.daysBeforeFestival || 7}
                                            className="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2"
                                            min="1"
                                            max="30"
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700">Days After Festival</label>
                                        <input
                                            type="number"
                                            name="daysAfter"
                                            defaultValue={settings.festivalSettings?.daysAfterFestival || 3}
                                            className="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2"
                                            min="1"
                                            max="15"
                                        />
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <h3 className="text-lg font-medium mb-4">Default SEO</h3>
                                <div className="space-y-4">
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700">Keywords (comma separated)</label>
                                        <textarea
                                            name="keywords"
                                            defaultValue={settings.defaultSEO?.keywords?.join(', ') || ''}
                                            rows={2}
                                            className="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2"
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700">Meta Title</label>
                                        <input
                                            type="text"
                                            name="metaTitle"
                                            defaultValue={settings.defaultSEO?.metaTitle || ''}
                                            className="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2"
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700">Meta Description</label>
                                        <textarea
                                            name="metaDescription"
                                            defaultValue={settings.defaultSEO?.metaDescription || ''}
                                            rows={3}
                                            className="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2"
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700">Hero Text</label>
                                        <input
                                            type="text"
                                            name="heroText"
                                            defaultValue={settings.defaultSEO?.heroText || ''}
                                            className="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2"
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700">Promo Text</label>
                                        <input
                                            type="text"
                                            name="promoText"
                                            defaultValue={settings.defaultSEO?.promoText || ''}
                                            className="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div className="mt-6">
                            <button
                                type="submit"
                                className="bg-blue-600 text-white py-2 px-6 rounded hover:bg-blue-700 transition-colors"
                            >
                                Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            )}

            {/* Manual Override Tab */}
            {activeTab === 'manual' && (
                <div className="bg-white rounded-lg shadow-md p-6 border">
                    <h2 className="text-xl font-semibold mb-6">Manual SEO Override</h2>
                    <form onSubmit={(e) => {
                        e.preventDefault();
                        handleManualOverride();
                    }}>
                        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <div className="space-y-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Campaign Name</label>
                                    <input
                                        type="text"
                                        value={manualOverrideForm.campaignName}
                                        onChange={(e) => setManualOverrideForm(prev => ({ ...prev, campaignName: e.target.value }))}
                                        className="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2"
                                        placeholder="e.g., Summer Sale 2024"
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Keywords (comma separated)</label>
                                    <textarea
                                        value={manualOverrideForm.keywords}
                                        onChange={(e) => setManualOverrideForm(prev => ({ ...prev, keywords: e.target.value }))}
                                        rows={3}
                                        className="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2"
                                        placeholder="summer sofa cleaning, discount repair"
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Meta Title</label>
                                    <input
                                        type="text"
                                        value={manualOverrideForm.metaTitle}
                                        onChange={(e) => setManualOverrideForm(prev => ({ ...prev, metaTitle: e.target.value }))}
                                        className="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2"
                                        placeholder="Summer Sofa Cleaning Offers | Khushi Home"
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Meta Description</label>
                                    <textarea
                                        value={manualOverrideForm.metaDescription}
                                        onChange={(e) => setManualOverrideForm(prev => ({ ...prev, metaDescription: e.target.value }))}
                                        rows={3}
                                        className="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2"
                                        placeholder="Special summer offers on sofa cleaning..."
                                    />
                                </div>
                            </div>
                            
                            <div className="space-y-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Hero Text</label>
                                    <input
                                        type="text"
                                        value={manualOverrideForm.heroText}
                                        onChange={(e) => setManualOverrideForm(prev => ({ ...prev, heroText: e.target.value }))}
                                        className="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2"
                                        placeholder="Summer Special - Clean Sofas!"
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Promo Text</label>
                                    <input
                                        type="text"
                                        value={manualOverrideForm.promoText}
                                        onChange={(e) => setManualOverrideForm(prev => ({ ...prev, promoText: e.target.value }))}
                                        className="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2"
                                        placeholder="50% Off All Cleaning Services"
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Start Date</label>
                                    <input
                                        type="date"
                                        value={manualOverrideForm.startDate}
                                        onChange={(e) => setManualOverrideForm(prev => ({ ...prev, startDate: e.target.value }))}
                                        className="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2"
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">End Date</label>
                                    <input
                                        type="date"
                                        value={manualOverrideForm.endDate}
                                        onChange={(e) => setManualOverrideForm(prev => ({ ...prev, endDate: e.target.value }))}
                                        className="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2"
                                    />
                                </div>
                            </div>
                        </div>
                        
                        <div className="mt-6">
                            <button
                                type="submit"
                                className="bg-orange-600 text-white py-2 px-6 rounded hover:bg-orange-700 transition-colors"
                            >
                                Activate Manual Override
                            </button>
                        </div>
                    </form>
                </div>
            )}

            {/* Festivals Tab */}
            {activeTab === 'festivals' && (
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {Object.entries(festivals).map(([year, yearFestivals]) => (
                        <div key={year} className="bg-white rounded-lg shadow-md p-6 border">
                            <h3 className="text-lg font-semibold mb-4">{year} Festivals</h3>
                            <div className="space-y-3">
                                {yearFestivals.map((festival, index) => (
                                    <div key={index} className="border-l-4 border-purple-500 pl-4 py-2">
                                        <h4 className="font-medium">{festival.name}</h4>
                                        <p className="text-sm text-gray-600">
                                            {festival.startDate} to {festival.endDate}
                                        </p>
                                        <p className="text-sm text-gray-500 mt-1">
                                            {festival.keywords?.slice(0, 3).join(', ')}...
                                        </p>
                                    </div>
                                ))}
                            </div>
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
};

export default AdminSEOManagement;