import React from 'react';
import "./index.css";
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import ProtectedRoute from './components/ProtectedRoute';
import AdminLayout from './components/Admin/AdminLayout';
import AdminHomePage from './pages/AdminHomePage';
import AdminUserManagement from './pages/AdminUserManagement';
import AdminServiceManagement from './pages/AdminServiceManagement';
import AdminBookingManagement from './pages/AdminBookingManagement';
import AdminContactManagement from './pages/AdminContactManagement';
import AdminNotifications from './pages/AdminNotifications';
import AdminRecentRequests from './pages/AdminRecentRequests';
import AdminSettings from './pages/AdminSettings';
import HomePage from './pages/HomePage';
import ServiceDetailsPage from './pages/ServiceDetailsPage';
import AuthPage from './pages/AuthPage';
import MyBookingsPage from './/pages/MyBookingsPage';
import Navbar from './components/Navbar';
import ServiceCategoryPage from './pages/ServiceCategoryPage';
import ProfilePage from './pages/ProfilePage';
import ProviderJobsPage from './pages/ProviderJobsPage';
import AdminCategoryManagement from './pages/AdminCategoryManagement';
import ErrorBoundary from './components/ErrorBoundary';

// Import Blog components
import Blog from './pages/Blogs/Blog';
import BlogDetails from './pages/Blogs/Blogdetails';
import BlogForm from './pages/Blogs/BlogForm';
import BlogManagement from './pages/Blogs/BlogManagement';
import AdminSEOManagement from './pages/AdminSEOManagement';
import ContactPage from './pages/ContactPage';
import UserNotificationsPage from './pages/UserNotificationsPage';
import Chatbot from './components/Chatbot';
import Footer from './components/Footer';
import PolicyPage from './pages/PolicyPage';
import PrivacyPolicy from './pages/PrivacyPolicy';
import TermsPage from './pages/TermsPage';
import CompleteRegistrationPage from './pages/CompleteRegistrationPage';
import SuperAdminSettings from './pages/SuperAdminSettings';
import ServiceRequestTrail from './pages/ServiceRequestTrail';

function App() {
  return (
    <ErrorBoundary>
      <Router>
        <Navbar />
        <main className="">
          <Routes>
            <Route path="/" element={<HomePage />} />
            <Route path="/services/:categoryId" element={<ServiceCategoryPage />} />
            <Route path="/services/search" element={<ServiceCategoryPage />} />
            <Route path="/service/:id" element={<ServiceDetailsPage />} />
            <Route path="/auth" element={<AuthPage />} />
            <Route path="/requests" element={<MyBookingsPage />} />
            <Route path="/notifications" element={<UserNotificationsPage />} />
            <Route path="/profile" element={<ProfilePage />} />
            <Route path="/contact" element={<ContactPage />} />
            <Route path="/policy" element={<PolicyPage />} />
            <Route path="/privacy" element={<PrivacyPolicy />} />
            <Route path="/terms" element={<TermsPage />} />

            <Route path="/complete-registration" element={<CompleteRegistrationPage />} />
            
            {/* Standalone Service Request Trail */}
            <Route path="/service-request" element={<ServiceRequestTrail />} />

            {/* Blog Routes */}
            <Route path="/blog" element={<Blog />} />
            <Route path="/blog/:slug" element={<BlogDetails />} />

            <Route path="/my-jobs" element={
              <ProtectedRoute role="provider">
                <ProviderJobsPage />
              </ProtectedRoute>
            } />

            <Route path="/provider-services" element={
              <ProtectedRoute role="provider">
                <AdminServiceManagement isProviderView={true} />
              </ProtectedRoute>
            } />

            <Route path="/super-admin/settings" element={
              <ProtectedRoute role="superadmin">
                <SuperAdminSettings />
              </ProtectedRoute>
            } />
            
            <Route path="/admin" element={
              <ProtectedRoute role="admin">
                <AdminLayout />
              </ProtectedRoute>
            }>
              <Route index element={<AdminHomePage />} />
              <Route path="users" element={<AdminUserManagement />} />
              <Route path="categories" element={<AdminCategoryManagement />} />
              <Route path="services" element={<AdminServiceManagement />} />
              <Route path="bookings" element={<AdminBookingManagement />} />
              <Route path="notifications" element={<AdminNotifications />} />
              <Route path="recent-requests" element={<AdminRecentRequests />} />
              <Route path="settings" element={<AdminSettings />} />
              <Route path="contact-messages" element={<AdminContactManagement />} />
              <Route path="blogs" element={<BlogManagement />} />
              <Route path="blogs/create" element={<BlogForm />} />
              <Route path="blogs/edit/:id" element={<BlogForm />} />
              <Route path="seo" element={<AdminSEOManagement />} />
            </Route>



          </Routes>
        </main>
        <Chatbot />
        <Footer />
      </Router>
    </ErrorBoundary>
  );
}

export default App;