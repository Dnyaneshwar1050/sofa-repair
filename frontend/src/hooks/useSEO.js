import { useState, useEffect, useCallback } from 'react';
import { getCurrentSEO } from '../api/apiService';

export const useSEO = () => {
    const [seoData, setSeoData] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    const fetchSEO = useCallback(async () => {
        try {
            setLoading(true);
            setError(null);
            const response = await getCurrentSEO();
            
            if (response.data.success) {
                setSeoData(response.data.seo);
            } else {
                setError('Failed to load SEO data');
            }
        } catch (err) {
            console.error('SEO fetch error:', err);
            setError('Failed to load SEO data');
        } finally {
            setLoading(false);
        }
    }, []);

    // Update page title and meta tags
    const updatePageSEO = useCallback((customTitle = null, customDescription = null) => {
        if (!seoData && !customTitle && !customDescription) return;

        // Update title
        const title = customTitle || seoData?.metaTitle || 'Khushi Home Sofa Repair - Professional Sofa Cleaning & Repair Services';
        document.title = title;

        // Update meta description
        const description = customDescription || seoData?.metaDescription || 'Professional sofa repair, cleaning and upholstery services. Expert craftsmen, quality materials, doorstep service across the city.';
        
        let metaDescription = document.querySelector('meta[name="description"]');
        if (!metaDescription) {
            metaDescription = document.createElement('meta');
            metaDescription.name = 'description';
            document.head.appendChild(metaDescription);
        }
        metaDescription.content = description;

        // Update keywords if available
        if (seoData?.keywords?.length) {
            let metaKeywords = document.querySelector('meta[name="keywords"]');
            if (!metaKeywords) {
                metaKeywords = document.createElement('meta');
                metaKeywords.name = 'keywords';
                document.head.appendChild(metaKeywords);
            }
            metaKeywords.content = seoData.keywords.join(', ');
        }

        // Update Open Graph tags
        updateOGTag('og:title', title);
        updateOGTag('og:description', description);
        updateOGTag('og:type', 'website');
        updateOGTag('og:url', window.location.href);
    }, [seoData]);

    const updateOGTag = (property, content) => {
        let tag = document.querySelector(`meta[property="${property}"]`);
        if (!tag) {
            tag = document.createElement('meta');
            tag.property = property;
            document.head.appendChild(tag);
        }
        tag.content = content;
    };

    // Fetch SEO data on mount
    useEffect(() => {
        fetchSEO();
    }, [fetchSEO]);

    // Auto-update page SEO when data changes
    useEffect(() => {
        if (seoData) {
            updatePageSEO();
        }
    }, [seoData, updatePageSEO]);

    return {
        seoData,
        loading,
        error,
        refetch: fetchSEO,
        updatePageSEO
    };
};