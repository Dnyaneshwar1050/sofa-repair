import axios from 'axios';
// Backend URL is pulled from the environment variables (e.g., .env)
// Use relative path in production (when env var not set), absolute in development
const determineApiUrl = () => {
    if (import.meta.env?.VITE_API_URL) return import.meta.env.VITE_API_URL;
    if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
        const pathSegments = window.location.pathname.split('/');
        if (pathSegments.length > 1 && pathSegments[1] && pathSegments[1] !== 'api' && !pathSegments[1].includes('.')) {
            // Dynamic check: Assume the first path segment is the project folder if accessed via localhost
            return `/${pathSegments[1]}/api`;
        }
    }
    return '/api';
};

export const API_URL = determineApiUrl();

// Helper function to manage JWT token in headers
const getToken = () => localStorage.getItem('token');
const getAuthHeaders = (isFormData = false) => ({
    headers: {
        Authorization: `Bearer ${getToken()}`,
        // When using FormData (for file uploads), the browser MUST set Content-Type
        ...(isFormData ? {} : { 'Content-Type': 'application/json' })
    }
});

// ===================================
// 1. PUBLIC ENDPOINTS
// ===================================

export const getCategories = async () => {
    try {
        const response = await axios.get(`${API_URL}/services/categories`);
        // Ensure we always return an array
        return {
            ...response,
            data: Array.isArray(response.data) ? response.data : []
        };
    } catch (error) {
        console.error('Error fetching categories:', error);
        return { data: [] };
    }
};

export const getServiceDetails = (id) => axios.get(`${API_URL}/services/${id}`);

// Search endpoint that handles category filtering or general search queries
export const getServices = async (params = {}) => {
    try {
        const query = new URLSearchParams(typeof params === 'object' ? params : { categoryId: params });
        const queryString = query.toString();

        const url = `${API_URL}/services?${queryString}`;
        const response = await axios.get(url);

        // Ensure we always return an array
        return {
            ...response,
            data: Array.isArray(response.data) ? response.data : []
        };
    } catch (error) {
        console.error('Error fetching services:', error);
        return { data: [] };
    }
};

// ===================================
// 2. AUTHENTICATION
// ===================================

export const register = (userData) => axios.post(`${API_URL}/auth/register`, userData);
export const login = (userData) => axios.post(`${API_URL}/auth/login`, userData);
export const completeRegistration = (phone, password) =>
    axios.put(`${API_URL}/auth/complete-registration`, { phone, password });

// ===================================
// 3. CUSTOMER ENDPOINTS (Protected)
// ===================================

export const createBooking = (bookingData) => axios.post(`${API_URL}/bookings`, bookingData, getAuthHeaders());
export const getMyBookings = () => axios.get(`${API_URL}/bookings/my`, getAuthHeaders());
export const createReview = (formData) => axios.post(
    `${API_URL}/reviews`,
    formData,
    getAuthHeaders(true) // Pass true to correctly send file data
);

// ===================================
// 4. ADMIN & PROVIDER MANAGEMENT ENDPOINTS
// ===================================

// --- User Management (Admin only) ---
export const fetchAllUsers = () => axios.get(`${API_URL}/admin/users`, getAuthHeaders());
export const updateUserRole = (id, userData) => axios.put(`${API_URL}/admin/users/${id}`, userData, getAuthHeaders());
export const deleteUser = (id) => axios.delete(`${API_URL}/admin/users/${id}`, getAuthHeaders());

// --- Service Management (Admin/Provider) ---
export const fetchAdminServices = () => axios.get(`${API_URL}/admin/services`, getAuthHeaders());

// Change to accept FormData and pass appropriate headers
export const createService = (formData) => axios.post(
    `${API_URL}/admin/services`,
    formData,
    getAuthHeaders(true) // Pass true for FormData headers
);

// Change to accept FormData and pass appropriate headers
export const updateService = (id, formData) => axios.put(
    `${API_URL}/admin/services/${id}`,
    formData,
    getAuthHeaders(true) // Pass true for FormData headers
);

export const deleteService = (id) => axios.delete(`${API_URL}/admin/services/${id}`, getAuthHeaders());
// Endpoint used by both Admin/Provider to toggle visibility
export const toggleServiceStatus = (id, isDisabled) => axios.put(`${API_URL}/admin/services/${id}/status`, { isDisabled }, getAuthHeaders());

// Alias to reuse fetchAdminServices on provider dashboard (backend filters by provider ID)
export const fetchProviderServices = fetchAdminServices;

// --- Category Management (Admin/Provider) ---
export const fetchAdminCategories = () => axios.get(`${API_URL}/admin/categories`, getAuthHeaders());
export const updateCategoryStatus = (id, isDisabled) => axios.put(`${API_URL}/admin/categories/${id}/status`, { isDisabled }, getAuthHeaders());

// --- Booking Management (Admin only) ---
export const fetchAllBookings = () => axios.get(`${API_URL}/admin/bookings`, getAuthHeaders());
export const updateBookingStatus = (id, status) => axios.put(`${API_URL}/admin/bookings/${id}`, { status }, getAuthHeaders());
export const deleteBooking = (id) => axios.delete(`${API_URL}/admin/bookings/${id}`, getAuthHeaders());

// --- Provider Jobs (Provider only) ---
export const fetchProviderBookings = () => axios.get(`${API_URL}/provider/bookings`, getAuthHeaders());
export const updateProviderBookingStatus = (id, status) => axios.put(`${API_URL}/provider/bookings/${id}`, { status }, getAuthHeaders());

// ===================================
// 5. BLOG ENDPOINTS
// ===================================

// Public blog endpoints
export const getAllBlogs = (params = {}) => {
    const query = new URLSearchParams(params);
    return axios.get(`${API_URL}/blogs?${query}`);
};

export const getBlogBySlug = (slug) => axios.get(`${API_URL}/blogs/${slug}`);

// Admin blog endpoints
export const getAdminBlogs = () => axios.get(`${API_URL}/blogs/admin/all`, getAuthHeaders());
export const createBlog = (formData) => axios.post(`${API_URL}/blogs/admin/create`, formData, {
    ...getAuthHeaders(),
    headers: {
        ...getAuthHeaders().headers,
        'Content-Type': 'multipart/form-data'
    }
});
export const updateBlog = (id, formData) => axios.put(`${API_URL}/blogs/admin/${id}`, formData, {
    ...getAuthHeaders(),
    headers: {
        ...getAuthHeaders().headers,
        'Content-Type': 'multipart/form-data'
    }
});
export const deleteBlog = (id) => axios.delete(`${API_URL}/blogs/admin/${id}`, getAuthHeaders());

// ===================================
// 6. SEO ENDPOINTS
// ===================================

// Public SEO endpoints
export const getCurrentSEO = () => axios.get(`${API_URL}/seo/current`);

// Admin SEO endpoints
export const getSEOSettings = () => axios.get(`${API_URL}/seo/settings`, getAuthHeaders());
export const updateSEOSettings = (settings) => axios.put(`${API_URL}/seo/settings`, settings, getAuthHeaders());
export const getFestivals = () => axios.get(`${API_URL}/seo/festivals`, getAuthHeaders());
export const setManualSEOOverride = (overrideData) => axios.post(`${API_URL}/seo/manual-override`, overrideData, getAuthHeaders());
export const disableManualSEOOverride = () => axios.post(`${API_URL}/seo/disable-override`, {}, getAuthHeaders());

// ===================================
// 7. CHATBOT ENDPOINTS
// ===================================

export const sendChatMessage = (message, sessionId) => {
    const token = getToken();
    return axios.post(
        `${API_URL}/chatbot/message`,
        { message, sessionId },
        { headers: token ? { Authorization: `Bearer ${token}` } : {} }
    );
};

export const getChatContext = () => axios.get(`${API_URL}/chatbot/context`, getAuthHeaders());
export const getChatHistory = (sessionId) => axios.get(`${API_URL}/chatbot/conversation/${sessionId}`, getAuthHeaders());
export const rateChatConversation = (sessionId, rating, feedback) =>
    axios.post(`${API_URL}/chatbot/conversation/${sessionId}/rate`, { rating, feedback }, getAuthHeaders());
export const endChatConversation = (sessionId) => axios.delete(`${API_URL}/chatbot/conversation/${sessionId}`, getAuthHeaders());

// ===================================
// 8. CONTACT ENDPOINTS
// ===================================

export const submitContactForm = (contactData) => axios.post(`${API_URL}/contact/submit`, contactData);
export const getContactMessages = () => axios.get(`${API_URL}/contact/messages`, getAuthHeaders());
export const getContactStats = () => axios.get(`${API_URL}/contact/stats`, getAuthHeaders());
export const updateContactStatus = (id, status) => axios.patch(`${API_URL}/contact/messages/${id}/status`, { status }, getAuthHeaders());
export const deleteContactMessage = (id) => axios.delete(`${API_URL}/contact/messages/${id}`, getAuthHeaders());

// ===================================
// 9. GENERIC API SERVICE OBJECT
// ===================================

// Generic API service object for common HTTP operations
export const apiService = {
    get: async (url) => {
        const token = getToken();
        const config = token ? { headers: { Authorization: `Bearer ${token}` } } : {};
        // Remove leading slash from url to avoid double slashes
        const cleanUrl = url.startsWith('/') ? url.slice(1) : url;
        const response = await axios.get(`${API_URL}/${cleanUrl}`, config);
        return response.data;
    },
    post: async (url, data) => {
        const token = getToken();
        const config = token ? { headers: { Authorization: `Bearer ${token}` } } : {};
        const cleanUrl = url.startsWith('/') ? url.slice(1) : url;
        const response = await axios.post(`${API_URL}/${cleanUrl}`, data, config);
        return response.data;
    },
    put: async (url, data) => {
        const token = getToken();
        const config = token ? { headers: { Authorization: `Bearer ${token}` } } : {};
        const cleanUrl = url.startsWith('/') ? url.slice(1) : url;
        const response = await axios.put(`${API_URL}/${cleanUrl}`, data, config);
        return response.data;
    },
    delete: async (url) => {
        const token = getToken();
        const config = token ? { headers: { Authorization: `Bearer ${token}` } } : {};
        const cleanUrl = url.startsWith('/') ? url.slice(1) : url;
        const response = await axios.delete(`${API_URL}/${cleanUrl}`, config);
        return response.data;
    }
};