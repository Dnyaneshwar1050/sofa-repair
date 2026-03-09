// Google Analytics and Conversion Tracking Utilities

// Purchase conversion tracking
export const trackPurchaseConversion = (value = 1.0, transactionId = '') => {
  if (typeof window !== 'undefined' && typeof window.gtag !== 'undefined') {
    window.gtag('event', 'conversion', {
      'send_to': 'AW-17678278696/fnRmCNs=+7MEKjA1OIB',
      'value': value,
      'currency': 'INR',
      'transaction_id': transactionId
    });
    console.log('Purchase conversion tracked:', { value, transactionId });
  }
};

// General conversion tracking (for form submissions, contact, etc.)
export const trackGeneralConversion = (url = '') => {
  if (typeof window !== 'undefined' && typeof window.gtag !== 'undefined') {
    window.gtag('report_conversion', url);
    console.log('General conversion tracked:', url);
  }
};

// Service booking conversion tracking
export const trackServiceBooking = (serviceDetails) => {
  if (typeof window !== 'undefined' && typeof window.gtag !== 'undefined') {
    // Track as purchase conversion
    trackPurchaseConversion(
      serviceDetails.price || 1.0, 
      serviceDetails.bookingId || `booking_${Date.now()}`
    );
    
    // Also track as custom event
    window.gtag('event', 'service_booking', {
      'event_category': 'Booking',
      'event_label': serviceDetails.serviceName || 'Unknown Service',
      'value': serviceDetails.price || 1.0,
      'custom_parameters': {
        'service_id': serviceDetails.serviceId,
        'category': serviceDetails.category,
        'booking_id': serviceDetails.bookingId
      }
    });
    
    console.log('Service booking tracked:', serviceDetails);
  }
};

// Contact form submission tracking
export const trackContactFormSubmission = () => {
  if (typeof window !== 'undefined' && typeof window.gtag !== 'undefined') {
    trackGeneralConversion(window.location.href);
    
    window.gtag('event', 'contact_form_submit', {
      'event_category': 'Contact',
      'event_label': 'Contact Form Submission'
    });
    
    console.log('Contact form submission tracked');
  }
};

// Email verification tracking
export const trackEmailVerification = () => {
  if (typeof window !== 'undefined' && typeof window.gtag !== 'undefined') {
    window.gtag('event', 'email_verification', {
      'event_category': 'User Engagement',
      'event_label': 'Email Verified'
    });
    
    console.log('Email verification tracked');
  }
};

// Page view tracking (for SPA navigation)
export const trackPageView = (pagePath, pageTitle) => {
  if (typeof window !== 'undefined' && typeof window.gtag !== 'undefined') {
    window.gtag('config', 'AW-17678278696', {
      page_path: pagePath,
      page_title: pageTitle
    });
    
    console.log('Page view tracked:', { pagePath, pageTitle });
  }
};

// User registration tracking
export const trackUserRegistration = (userType = 'customer') => {
  if (typeof window !== 'undefined' && typeof window.gtag !== 'undefined') {
    window.gtag('event', 'sign_up', {
      'event_category': 'User',
      'event_label': `Registration - ${userType}`,
      'method': 'email'
    });
    
    console.log('User registration tracked:', userType);
  }
};

// Review submission tracking
export const trackReviewSubmission = (serviceId, rating) => {
  if (typeof window !== 'undefined' && typeof window.gtag !== 'undefined') {
    window.gtag('event', 'review_submit', {
      'event_category': 'Engagement',
      'event_label': `Review - ${rating} stars`,
      'value': rating,
      'custom_parameters': {
        'service_id': serviceId
      }
    });
    
    console.log('Review submission tracked:', { serviceId, rating });
  }
};