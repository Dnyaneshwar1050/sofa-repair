import React, { useState } from 'react';
import { FaStar } from 'react-icons/fa';
import { toast } from 'sonner';
// FIX: Import the API call from apiService
import { createReview } from '../api/apiService'; 

// ... (StarRatingInput component remains the same) ...
const StarRatingInput = ({ rating, setRating }) => {
  return (
    <div className="flex space-x-1">
      {[...Array(5)].map((_, index) => {
        const ratingValue = index + 1;
        return (
          <label key={index}>
            <input
              type="radio"
              name="rating"
              value={ratingValue}
              onClick={() => setRating(ratingValue)}
              className="hidden"
            />
            <FaStar
              className="cursor-pointer transition-colors"
              color={ratingValue <= rating ? "#ffc107" : "#e4e5e9"}
              size={24}
            />
          </label>
        );
      })}
    </div>
  );
};


const ReviewForm = ({ serviceId, onReviewSubmitted, onCancel }) => {
  const [rating, setRating] = useState(0);
  const [reviewText, setReviewText] = useState('');
  const [loading, setLoading] = useState(false);
  const [files, setFiles] = useState([]);

  const handleFileChange = (e) => {
    const selectedFiles = Array.from(e.target.files).slice(0, 5); // Limit to 5
    if (selectedFiles.length > 5) {
      toast.error("Maximum 5 images allowed.");
    }
    setFiles(selectedFiles);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (rating === 0) {
      toast.error("Please select a star rating.");
      return;
    }
    setLoading(true);

    const formData = new FormData();
    formData.append('serviceId', serviceId);
    formData.append('rating', rating);
    formData.append('reviewText', reviewText.trim());
    
    // FIX: Change field name from 'photos' to 'images' to match backend middleware (multerUploader.js)
    files.forEach(file => {
      formData.append('images', file); 
    });

    try {
      const res = await createReview(formData); 
      
      toast.success(res.data?.message || "Review submitted successfully!");
      setRating(0);
      setReviewText('');
      setFiles([]);
      onReviewSubmitted(); // Trigger parent component refresh/close form

    } catch (error) {
      const msg = error.response?.data?.message || "Failed to submit review. Are you logged in and verified?";
      toast.error(msg);
      console.error('Review submission error:', error);
    } finally {
      setLoading(false);
    }
  };

  return (
    <form onSubmit={handleSubmit} className="p-6 border border-gray-200 rounded-lg shadow-inner bg-white space-y-4">
      <h4 className="text-xl font-bold text-gray-800">Your Feedback</h4>
      
      {/* Star Rating Input */}
      <div>
        <label className="block text-sm font-medium text-gray-700 mb-1">Rating *</label>
        <StarRatingInput rating={rating} setRating={setRating} />
      </div>

      {/* Review Text Area */}
      <div>
        <label htmlFor="reviewText" className="block text-sm font-medium text-gray-700">Review/Comment *</label>
        <textarea
          id="reviewText"
          value={reviewText}
          onChange={(e) => setReviewText(e.target.value)}
          required
          rows="3"
          maxLength="500"
          className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 resize-none"
          placeholder="Describe your experience (max 500 characters)"
        />
      </div>

      {/* Photo Upload Input */}
      <div>
        <label htmlFor="photos" className="block text-sm font-medium text-gray-700 mb-1">Upload Photos (Max 5)</label>
        <input
          type="file"
          id="photos"
          multiple
          accept="image/*"
          onChange={handleFileChange}
          className="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
        />
        {files.length > 0 && (
            <p className="text-xs text-gray-500 mt-1">{files.length} file(s) selected.</p>
        )}
      </div>


      {/* Actions */}
      <div className="flex justify-end space-x-3">
        <button
          type="button"
          onClick={onCancel}
          className="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition"
        >
          Cancel
        </button>
        <button
          type="submit"
          disabled={loading || rating === 0}
          className="px-4 py-2 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 disabled:bg-gray-400 transition"
        >
          {loading ? 'Submitting...' : 'Submit Review'}
        </button>
      </div>
    </form>
  );
};

export default ReviewForm;