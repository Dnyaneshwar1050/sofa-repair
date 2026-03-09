import React, { useState } from 'react';

const StarRating = ({ 
  rating = 0, 
  onRatingChange = null, 
  size = 'md', 
  readonly = false,
  showNumber = true 
}) => {
  const [hoverRating, setHoverRating] = useState(0);

  const sizes = {
    sm: 'w-4 h-4',
    md: 'w-5 h-5',
    lg: 'w-6 h-6',
    xl: 'w-8 h-8'
  };

  const handleClick = (selectedRating) => {
    if (!readonly && onRatingChange) {
      onRatingChange(selectedRating);
    }
  };

  const handleMouseEnter = (selectedRating) => {
    if (!readonly) {
      setHoverRating(selectedRating);
    }
  };

  const handleMouseLeave = () => {
    if (!readonly) {
      setHoverRating(0);
    }
  };

  const displayRating = hoverRating || rating;

  return (
    <div className="flex items-center space-x-1">
      <div className="flex">
        {[1, 2, 3, 4, 5].map((star) => (
          <button
            key={star}
            type="button"
            onClick={() => handleClick(star)}
            onMouseEnter={() => handleMouseEnter(star)}
            onMouseLeave={handleMouseLeave}
            disabled={readonly}
            className={`
              ${readonly ? 'cursor-default' : 'cursor-pointer hover:scale-110'} 
              transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-yellow-400 rounded
              ${readonly ? '' : 'hover:drop-shadow-sm'}
            `}
          >
            <svg
              className={`
                ${sizes[size]} 
                ${star <= displayRating ? 'text-yellow-400' : 'text-gray-300'}
                ${readonly ? '' : 'hover:text-yellow-500'}
                transition-colors duration-200
              `}
              fill="currentColor"
              viewBox="0 0 20 20"
            >
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
            </svg>
          </button>
        ))}
      </div>
      
      {showNumber && (
        <span className="text-sm text-gray-600 ml-2">
          {rating > 0 ? `${rating.toFixed(1)}` : 'No rating'}
        </span>
      )}
      
      {!readonly && hoverRating > 0 && (
        <span className="text-sm text-gray-500 ml-2">
          {hoverRating} star{hoverRating !== 1 ? 's' : ''}
        </span>
      )}
    </div>
  );
};

// Star rating display with count
export const StarRatingDisplay = ({ rating = 0, count = 0, size = 'md' }) => {
  return (
    <div className="flex items-center space-x-2">
      <StarRating rating={rating} size={size} readonly showNumber={false} />
      <span className="text-sm text-gray-600">
        {rating.toFixed(1)} ({count} review{count !== 1 ? 's' : ''})
      </span>
    </div>
  );
};

// Compact star rating for cards
export const CompactStarRating = ({ rating = 0, count = 0 }) => {
  return (
    <div className="flex items-center space-x-1">
      <StarRating rating={rating} size="sm" readonly showNumber={false} />
      <span className="text-xs text-gray-500">
        {rating.toFixed(1)} ({count})
      </span>
    </div>
  );
};

export default StarRating;