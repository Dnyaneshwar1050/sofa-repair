import React, { useState, useEffect } from 'react';
import { apiService } from '../api/apiService';

const AdminSettings = () => {
    const [settings, setSettings] = useState({});
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState({});

    useEffect(() => {
        fetchSettings();
    }, []);

    const fetchSettings = async () => {
        try {
            const response = await apiService.get('settings');
            // Handle the response structure { success: true, data: [settings array] }
            const settingsArray = response.success ? response.data : response;
            
            // Convert array to object for easier access
            const settingsObject = {};
            if (Array.isArray(settingsArray)) {
                settingsArray.forEach(setting => {
                    settingsObject[setting.settingKey] = {
                        value: setting.settingValue,
                        type: setting.settingType,
                        description: setting.description,
                        category: setting.category,
                        lastModified: setting.updatedAt
                    };
                });
            }
            
            setSettings(settingsObject);
        } catch (error) {
            console.error('Error fetching settings:', error);
        } finally {
            setLoading(false);
        }
    };

    const updateSetting = async (key, value) => {
        setSaving(prev => ({ ...prev, [key]: true }));
        try {
            const response = await apiService.put(`settings/${key}`, { 
                value,
                description: settingsConfig.find(s => s.key === key)?.description,
                category: settingsConfig.find(s => s.key === key)?.category || 'system'
            });
            
            // Handle the response structure { success: true, data: setting }
            const updatedSetting = response.success ? response.data : response;
            
            setSettings(prev => ({
                ...prev,
                [key]: {
                    value: updatedSetting.settingValue || value,
                    type: updatedSetting.settingType,
                    description: updatedSetting.description,
                    category: updatedSetting.category,
                    lastModified: updatedSetting.updatedAt
                }
            }));
            
            // Show success feedback
            const element = document.getElementById(`setting-${key}`);
            if (element) {
                element.classList.add('ring-2', 'ring-green-500');
                setTimeout(() => {
                    element.classList.remove('ring-2', 'ring-green-500');
                }, 1500);
            }
        } catch (error) {
            console.error('Error updating setting:', error);
            alert('Error updating setting');
        } finally {
            setSaving(prev => ({ ...prev, [key]: false }));
        }
    };

    const settingsConfig = [
        {
            key: 'show_service_prices',
            title: 'Show Service Prices',
            description: 'Display service prices to customers on the website',
            type: 'boolean',
            category: 'Website Display',
            icon: '💰'
        },
        {
            key: 'notification_retention_days',
            title: 'Notification Retention (Days)',
            description: 'How many days to keep notifications before automatic cleanup',
            type: 'number',
            category: 'Notifications',
            icon: '🔔',
            min: 1,
            max: 365
        },
        {
            key: 'recent_bookings_hours',
            title: 'Recent Bookings Window (Hours)',
            description: 'Time window for considering bookings as "recent" in the dashboard',
            type: 'number',
            category: 'Dashboard',
            icon: '⏰',
            min: 1,
            max: 72
        },
        {
            key: 'auto_confirm_bookings',
            title: 'Auto-Confirm Bookings',
            description: 'Automatically confirm bookings without admin approval',
            type: 'boolean',
            category: 'Booking Management',
            icon: '✅'
        },
        {
            key: 'require_phone_verification',
            title: 'Require Phone Verification',
            description: 'Require customers to verify phone numbers before booking',
            type: 'boolean',
            category: 'Security',
            icon: '📱'
        },
        {
            key: 'max_bookings_per_user_per_day',
            title: 'Max Bookings Per User Per Day',
            description: 'Maximum number of bookings a user can make in a single day',
            type: 'number',
            category: 'Booking Management',
            icon: '📋',
            min: 1,
            max: 50
        },
        {
            key: 'enable_email_notifications',
            title: 'Email Notifications',
            description: 'Send email notifications for booking updates',
            type: 'boolean',
            category: 'Notifications',
            icon: '📧'
        },
        {
            key: 'maintenance_mode',
            title: 'Maintenance Mode',
            description: 'Put the website in maintenance mode (only admins can access)',
            type: 'boolean',
            category: 'System',
            icon: '🔧'
        }
    ];

    const groupedSettings = settingsConfig.reduce((groups, setting) => {
        const category = setting.category || 'General';
        if (!groups[category]) {
            groups[category] = [];
        }
        groups[category].push(setting);
        return groups;
    }, {});

    const renderSettingControl = (setting) => {
        const currentSetting = settings[setting.key];
        const isLoading = saving[setting.key];
        
        if (setting.type === 'boolean') {
            return (
                <div className="flex items-center space-x-3">
                    <div className="relative">
                        <input
                            type="checkbox"
                            id={`setting-${setting.key}`}
                            checked={currentSetting?.value || false}
                            onChange={(e) => updateSetting(setting.key, e.target.checked)}
                            disabled={isLoading}
                            className="sr-only"
                        />
                        <div
                            className={`block w-14 h-8 rounded-full transition-colors cursor-pointer ${
                                currentSetting?.value ? 'bg-blue-600' : 'bg-gray-300'
                            } ${isLoading ? 'opacity-50 cursor-not-allowed' : ''}`}
                            onClick={() => !isLoading && updateSetting(setting.key, !currentSetting?.value)}
                        >
                            <div
                                className={`dot absolute left-1 top-1 bg-white w-6 h-6 rounded-full transition-transform ${
                                    currentSetting?.value ? 'transform translate-x-6' : ''
                                }`}
                            ></div>
                        </div>
                    </div>
                    <span className={`text-sm ${currentSetting?.value ? 'text-green-600 font-medium' : 'text-gray-500'}`}>
                        {currentSetting?.value ? 'Enabled' : 'Disabled'}
                    </span>
                    {isLoading && (
                        <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600"></div>
                    )}
                </div>
            );
        }
        
        if (setting.type === 'number') {
            return (
                <div className="flex items-center space-x-3">
                    <input
                        type="number"
                        id={`setting-${setting.key}`}
                        value={currentSetting?.value || ''}
                        onChange={(e) => updateSetting(setting.key, parseInt(e.target.value))}
                        disabled={isLoading}
                        min={setting.min}
                        max={setting.max}
                        className="w-24 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:bg-gray-100"
                    />
                    {isLoading && (
                        <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600"></div>
                    )}
                </div>
            );
        }
        
        return null;
    };

    if (loading) {
        return (
            <div className="flex justify-center items-center min-h-screen">
                <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-600"></div>
            </div>
        );
    }

    return (
        <div className="min-h-screen bg-gray-50 p-6">
            <div className="max-w-4xl mx-auto">
                {/* Header */}
                <div className="bg-gradient-to-r from-purple-600 to-blue-600 rounded-lg shadow-lg p-6 mb-6">
                    <h1 className="text-3xl font-bold text-white">System Settings</h1>
                    <p className="text-purple-100 mt-2">Configure system behavior and feature toggles</p>
                </div>

                {/* Settings Categories */}
                <div className="space-y-6">
                    {Object.entries(groupedSettings).map(([category, categorySettings]) => (
                        <div key={category} className="bg-white rounded-lg shadow-sm p-6">
                            <h2 className="text-xl font-semibold text-gray-900 mb-4 flex items-center">
                                <span className="bg-gray-100 rounded-lg p-2 mr-3">
                                    {categorySettings[0]?.icon || '⚙️'}
                                </span>
                                {category}
                            </h2>
                            
                            <div className="space-y-6">
                                {categorySettings.map(setting => (
                                    <div key={setting.key} className="border-b border-gray-200 pb-6 last:border-b-0 last:pb-0">
                                        <div className="flex items-center justify-between">
                                            <div className="flex-1">
                                                <div className="flex items-center space-x-3 mb-1">
                                                    <span className="text-lg">{setting.icon}</span>
                                                    <h3 className="text-lg font-medium text-gray-900">
                                                        {setting.title}
                                                    </h3>
                                                </div>
                                                <p className="text-gray-600 text-sm ml-8">
                                                    {setting.description}
                                                </p>
                                                
                                                {/* Current Value Display */}
                                                <div className="mt-2 ml-8">
                                                    <span className="text-xs text-gray-500">
                                                        Current: {
                                                            setting.type === 'boolean' 
                                                                ? (settings[setting.key]?.value ? 'Enabled' : 'Disabled')
                                                                : (settings[setting.key]?.value || 'Not set')
                                                        }
                                                    </span>
                                                    {settings[setting.key]?.lastModified && (
                                                        <span className="text-xs text-gray-400 ml-2">
                                                            (Updated: {new Date(settings[setting.key].lastModified).toLocaleDateString()})
                                                        </span>
                                                    )}
                                                </div>
                                            </div>
                                            
                                            <div className="ml-6">
                                                {renderSettingControl(setting)}
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    ))}
                </div>

                {/* System Info */}
                <div className="bg-white rounded-lg shadow-sm p-6 mt-6">
                    <h2 className="text-xl font-semibold text-gray-900 mb-4">System Information</h2>
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div className="bg-gray-50 rounded-lg p-4">
                            <h3 className="font-medium text-gray-900 mb-1">Total Settings</h3>
                            <p className="text-2xl font-bold text-blue-600">{Object.keys(settings).length}</p>
                        </div>
                        <div className="bg-gray-50 rounded-lg p-4">
                            <h3 className="font-medium text-gray-900 mb-1">Categories</h3>
                            <p className="text-2xl font-bold text-green-600">{Object.keys(groupedSettings).length}</p>
                        </div>
                        <div className="bg-gray-50 rounded-lg p-4">
                            <h3 className="font-medium text-gray-900 mb-1">Last Updated</h3>
                            <p className="text-sm text-gray-600">
                                {Object.values(settings).some(s => s.lastModified) 
                                    ? new Date(Math.max(...Object.values(settings)
                                        .filter(s => s.lastModified)
                                        .map(s => new Date(s.lastModified)))).toLocaleString()
                                    : 'Never'
                                }
                            </p>
                        </div>
                    </div>
                </div>

                {/* Warning Notice */}
                <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mt-6">
                    <div className="flex items-start">
                        <div className="text-yellow-600 text-xl mr-3">⚠️</div>
                        <div>
                            <h3 className="text-sm font-medium text-yellow-800">Important Notice</h3>
                            <p className="text-sm text-yellow-700 mt-1">
                                Changes to these settings affect the entire system. Some changes may require users to refresh their browsers to take effect.
                                The price visibility setting will immediately affect how services are displayed to customers.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default AdminSettings;