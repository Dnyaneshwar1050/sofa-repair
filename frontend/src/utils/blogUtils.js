// Blog interaction tracking utilities

// Track blog view for analytics
export const trackBlogView = async (blogSlug, userId = null) => {
    try {
        // Store in localStorage for non-authenticated users
        const viewedBlogs = JSON.parse(localStorage.getItem('viewedBlogs') || '[]');
        const viewData = {
            slug: blogSlug,
            viewedAt: new Date().toISOString(),
            userId: userId
        };
        
        // Add to viewed blogs if not already viewed today
        const today = new Date().toDateString();
        const alreadyViewedToday = viewedBlogs.some(
            view => view.slug === blogSlug && 
            new Date(view.viewedAt).toDateString() === today
        );
        
        if (!alreadyViewedToday) {
            viewedBlogs.unshift(viewData);
            // Keep only last 50 views
            viewedBlogs.splice(50);
            localStorage.setItem('viewedBlogs', JSON.stringify(viewedBlogs));
        }
    } catch (error) {
        console.error('Error tracking blog view:', error);
    }
};

// Get user's reading history
export const getReadingHistory = () => {
    try {
        return JSON.parse(localStorage.getItem('viewedBlogs') || '[]');
    } catch (error) {
        console.error('Error getting reading history:', error);
        return [];
    }
};

// Get reading preferences based on history
export const getReadingPreferences = () => {
    try {
        const history = getReadingHistory();
        
        return {
            totalViews: history.length,
            recentViews: history.filter(view => 
                new Date() - new Date(view.viewedAt) < 7 * 24 * 60 * 60 * 1000
            ).length,
            favoriteCategories: []
        };
    } catch (error) {
        console.error('Error getting reading preferences:', error);
        return { totalViews: 0, recentViews: 0, favoriteCategories: [] };
    }
};

// Recommend related blogs based on reading history
export const getRecommendedBlogs = (currentBlogCategory, allBlogs) => {
    try {
        // Simple recommendation: prefer same category
        return allBlogs
            .filter(blog => blog.category === currentBlogCategory)
            .slice(0, 3);
    } catch (error) {
        console.error('Error getting recommended blogs:', error);
        return [];
    }
};

// Share blog utility
export const shareBlog = async (blog) => {
    const shareData = {
        title: blog.title,
        text: blog.excerpt,
        url: window.location.href
    };

    try {
        if (navigator.share && navigator.canShare && navigator.canShare(shareData)) {
            await navigator.share(shareData);
            return true;
        } else {
            // Fallback to clipboard
            await navigator.clipboard.writeText(window.location.href);
            return 'copied';
        }
    } catch (error) {
        console.error('Error sharing blog:', error);
        // Manual fallback
        const textArea = document.createElement('textarea');
        textArea.value = window.location.href;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        return 'copied';
    }
};