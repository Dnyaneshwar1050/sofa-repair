import React, { createContext, useContext, useState, useEffect } from 'react';
import axios from 'axios';
import { API_URL } from '../api/apiService';

const SettingsContext = createContext();

export const SettingsProvider = ({ children }) => {
    const [settings, setSettings] = useState({
        siteName: '',
        primaryColor: '#2563eb',
        secondaryColor: '#f59e0b',
        logoUrl: '',
        themeMode: 'light'
    });

    const fetchSettings = async () => {
        try {
            const { data } = await axios.get(`${API_URL}/site-settings`);
            setSettings(data);
            applyTheme(data);
        } catch (error) {
            console.error("Failed to load branding", error);
        }
    };

    const applyTheme = (s) => {
        document.title = s.siteName;
        const root = document.documentElement;
        root.style.setProperty('--primary-color', s.primaryColor);
        root.style.setProperty('--secondary-color', s.secondaryColor);

        if (s.themeMode === 'dark') {
            document.body.classList.add('dark-mode');
        } else {
            document.body.classList.remove('dark-mode');
        }
    };

    useEffect(() => {
        fetchSettings();
    }, []);

    return (
        <SettingsContext.Provider value={{ settings, refreshSettings: fetchSettings }}>
            {children}
        </SettingsContext.Provider>
    );
};

export const useSettings = () => useContext(SettingsContext);