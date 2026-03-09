import React, { useState, useEffect, useCallback } from 'react';
import { apiService } from '../api/apiService';
import { useAuth } from '../context/AuthContext';
import StarRating from './StarRating';

const ReviewList = ({ serviceId, refreshTrigger }) => {
  const { user } = useAuth();
  const [reviews, setReviews] = useState([]);
  const [stats, setStats] = useState(null);
  const [pagination, setPagination] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [sortBy, setSortBy] = useState('newest');
  const [currentPage, setCurrentPage] = useState(1);

  const fetchReviews = useCallback(async () => {
    try {
      setLoading(true);
      const response = await apiService.get(`/reviews/service/${serviceId}`, {
        params: {
          page: currentPage,
          limit: 10,
          sort: sortBy
        }
      });

      setReviews(response.data.reviews);
      setStats(response.data.stats);
      setPagination(response.data.pagination);
    } catch (error) {
      console.error('Error fetching reviews:', error);
      setError('Failed to load reviews');
    } finally {
      setLoading(false);
    }
  }, [serviceId, sortBy, currentPage]);

  useEffect(() => {
    fetchReviews();
  }, [fetchReviews, refreshTrigger]);

  const handleSortChange = (newSort) => {
    setSortBy(newSort);
    setCurrentPage(1);
  };

  const handlePageChange = (page) => {
    setCurrentPage(page);
  };

  const handleHelpfulVote = async (reviewId, isHelpful) => {
    if (!user) {
      alert('Please log in to vote');
      return;
    }

    try {
      await apiService.post(`/reviews/${reviewId}/helpful`, { isHelpful });
      // Refresh reviews to show updated vote counts
      fetchReviews();
    } catch (error) {
      console.error('Error voting on review:', error);
      alert('Failed to record vote');
    }
  };

  const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
  };

  if (loading) {
    return (
      <div className="flex justify-center items-center py-8">
        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {/* Review Statistics */}
      {stats && (
        <div className="bg-gray-50 rounded-lg p-6">
          <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
              <div className="flex items-center space-x-2">
                <StarRating rating={stats?.averageRating || 0} size="lg" readonly showNumber={false} />
                <span className="text-2xl font-bold text-gray-900">{(stats?.averageRating || 0).toFixed(1)}</span>
              </div>
              <p className="text-gray-600 mt-1">{stats?.totalReviews || 0} review{stats?.totalReviews !== 1 ? 's' : ''}</p>
            </div>

            {/* Rating Distribution */}
            <div className="mt-4 sm:mt-0 sm:ml-8">
              <div className="space-y-1">
                {[5, 4, 3, 2, 1].map((rating) => {
                  const count = stats?.ratingDistribution?.[rating] || 0;
                  const percentage = stats?.totalReviews > 0 ? (count / stats.totalReviews) * 100 : 0;

                  return (
                    <div key={rating} className="flex items-center space-x-2 text-sm">
                      <span className="w-3">{rating}</span>
                      <svg className="w-3 h-3 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                      </svg>
                      <div className="flex-1 bg-gray-200 rounded-full h-2">
                        <div
                          className="bg-yellow-400 h-2 rounded-full transition-all duration-300"
                          style={{ width: `${percentage}%` }}
                        ></div>
                      </div>
                      <span className="w-8 text-right text-gray-600">{count}</span>
                    </div>
                  );
                })}
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Sort Options */}
      <div className="flex items-center justify-between">
        <h3 className="text-lg font-semibold text-gray-900">
          Reviews ({pagination?.totalItems || 0})
        </h3>

        <div className="flex items-center space-x-2">
          <label htmlFor="sort" className="text-sm text-gray-600">Sort by:</label>
          <select
            id="sort"
            value={sortBy}
            onChange={(e) => handleSortChange(e.target.value)}
            className="text-sm border border-gray-300 rounded-md px-3 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="newest">Newest</option>
            <option value="oldest">Oldest</option>
            <option value="highest-rated">Highest Rated</option>
            <option value="lowest-rated">Lowest Rated</option>
            <option value="most-helpful">Most Helpful</option>
          </select>
        </div>
      </div>

      {/* Error Message */}
      {error && (
        <div className="bg-red-50 border border-red-200 rounded-md p-4">
          <p className="text-red-700">{error}</p>
        </div>
      )}

      {/* Reviews List */}
      {reviews.length === 0 ? (
        <div className="text-center py-8">
          <p className="text-gray-500">No reviews yet. Be the first to review this service!</p>
        </div>
      ) : (
        <div className="space-y-6">
          {reviews.map((review) => (
            <div key={review._id} className="bg-white border border-gray-200 rounded-lg p-6">
              <div className="flex items-start justify-between">
                <div className="flex items-start space-x-4">
                  {/* User Avatar */}
                  <div className="flex-shrink-0">
                    <div className="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-medium">
                      {review.user?.name?.charAt(0)?.toUpperCase() || 'A'}
                    </div>
                  </div>

                  <div className="flex-1">
                    {/* Header */}
                    <div className="flex items-center space-x-2 mb-2">
                      <h4 className="font-medium text-gray-900">
                        {review.user?.name || 'Anonymous'}
                      </h4>
                      {review.user?.isEmailVerified && (
                        <svg className="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                          <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
                        </svg>
                      )}
                      {review.isVerifiedPurchase && (
                        <span className="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">
                          Verified Purchase
                        </span>
                      )}
                    </div>

                    {/* Rating and Date */}
                    <div className="flex items-center space-x-4 mb-3">
                      <StarRating rating={review.rating} size="sm" readonly showNumber={false} />
                      <span className="text-sm text-gray-500">
                        {formatDate(review.createdAt)}
                      </span>
                    </div>

                    {/* Review Content */}
                    <h5 className="font-medium text-gray-900 mb-2">{review.title}</h5>
                    <p className="text-gray-700 mb-4">{review.comment}</p>

                    {/* Images */}
                    {review.images && review.images.length > 0 && (
                      <div className="flex space-x-2 mb-4 overflow-x-auto">
                        {review.images.map((image, index) => (
                          <img
                            key={index}
                            src={image.url}
                            alt={`Review image ${index + 1}`}
                            className="w-20 h-20 object-cover rounded-md border cursor-pointer hover:opacity-90"
                            onClick={() => window.open(image.url, '_blank')}
                          />
                        ))}
                      </div>
                    )}

                    {/* Helpful Votes */}
                    {user &&
                      (
                        <div className="flex items-center space-x-4">
                          <span className="text-sm text-gray-600">Was this helpful?</span>
                          <div className="flex items-center space-x-2">
                            <button
                              onClick={() => handleHelpfulVote(review._id, true)}
                              disabled={!user}
                              className="flex items-center space-x-1 text-sm text-gray-600 hover:text-green-600 disabled:opacity-50"
                            >
                              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5" />
                              </svg>
                              <span>
                                {review.helpful ? review.helpful.filter(vote => vote.isHelpful).length : 0}
                              </span>
                            </button>

                            <button
                              onClick={() => handleHelpfulVote(review._id, false)}
                              disabled={!user}
                              className="flex items-center space-x-1 text-sm text-gray-600 hover:text-red-600 disabled:opacity-50"
                            >
                              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 14H5.236a2 2 0 01-1.789-2.894l3.5-7A2 2 0 018.736 3h4.018c.163 0 .326.02.485.06L17 4m-7 10v5a2 2 0 002 2h.095c.5 0 .905-.405.905-.905 0-.714.211-1.412.608-2.006L17 13V4m-7 10h2m5-10H9a2 2 0 00-2 2v6a2 2 0 002 2h2.5" />
                              </svg>
                              <span>
                                {review.helpful ? review.helpful.filter(vote => !vote.isHelpful).length : 0}
                              </span>
                            </button>
                          </div>
                        </div>
                      )}
                  </div>
                </div>
              </div>
            </div>
          ))}
        </div>
      )}

      {/* Pagination */}
      {pagination && pagination.totalPages > 1 && (
        <div className="flex justify-center items-center space-x-2 mt-8">
          <button
            onClick={() => handlePageChange(currentPage - 1)}
            disabled={!pagination.hasPrevPage}
            className="px-3 py-2 text-sm text-gray-600 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Previous
          </button>

          {[...Array(pagination.totalPages)].map((_, index) => {
            const page = index + 1;
            return (
              <button
                key={page}
                onClick={() => handlePageChange(page)}
                className={`px-3 py-2 text-sm rounded-md ${currentPage === page
                    ? 'bg-blue-600 text-white'
                    : 'text-gray-600 bg-white border border-gray-300 hover:bg-gray-50'
                  }`}
              >
                {page}
              </button>
            );
          })}

          <button
            onClick={() => handlePageChange(currentPage + 1)}
            disabled={!pagination.hasNextPage}
            className="px-3 py-2 text-sm text-gray-600 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Next
          </button>
        </div>
      )}
    </div>
  );
};

export default ReviewList;