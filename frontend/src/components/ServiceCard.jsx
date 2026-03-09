import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { FaStar } from 'react-icons/fa';
import { apiService } from '../api/apiService';

const ServiceCard = ({ service }) => {
  const [showPrices, setShowPrices] = useState(true);
  const [callNumbers, setCallNumbers] = useState([]); // State for multiple numbers
  const [showCallOptions, setShowCallOptions] = useState(false); // State to toggle call options

  const upperPrice = service.priceUpperRange || service.basePrice;

  // const extractNumber = (numberString) => {
  //   // Regex to match a number starting with optional + and containing digits/spaces/hyphens
  //   const match = numberString.match(/(\+?\d[\d\s-]*\d)/);
  //   // Remove spaces and hyphens from the matched number
  //   return match ? match[0].replace(/[\s-]/g, '') : numberString;
  // };

  // Fetch price visibility and call numbers setting
  const fetchPriceVisibility = async () => {
    try {
      const response = await apiService.get('settings/public');
      const settingsData = response.success ? response.data : response;

      setShowPrices(settingsData.show_service_prices !== false);

      const numbers = settingsData.call_numbers || '+919689861811';

      // Ensure it's an array of strings
      setCallNumbers(Array.isArray(numbers) ? numbers : (numbers ? [numbers] : []));

    } catch (error) {
      console.error('Error fetching settings:', error);
      setShowPrices(true);
      // Use fallback numbers on error (keeping user's latest format)
      setCallNumbers('+919689861811');
    }
  };

  useEffect(() => {
    fetchPriceVisibility();

    // Set up polling to check for settings changes every 30 seconds
    const interval = setInterval(fetchPriceVisibility, 30000);

    return () => clearInterval(interval);
  }, []);

  // Check if the service name includes 'Sofa' (case-insensitive)
  const isSofaService = service.name && service.name.toLowerCase().includes('sofa');

  return (
    <div className="bg-white p-4 shadow-md rounded-xl border border-gray-100 hover:shadow-lg transition-shadow">

      {/* Service Image */}
      <img
        src={service.imageUrl || 'https://via.placeholder.com/300x160?text=Service+Image'}
        alt={service.name}
        className="w-full h-40 object-cover rounded-lg mb-3"
      />

      {/* Service Name and Rating */}
      <h3 className="text-xl font-bold mb-1 text-gray-900">{service.name}</h3>
      <div className="flex items-center text-sm text-gray-600 mb-3">
        <FaStar className="text-orange-500 mr-1 text-xs" />
        <span>{service.averageRating} ({service.reviewCount} reviews)</span>
      </div>

      {/* Price Range Display - Conditionally shown */}
      {/* {showPrices ? (
        <p className="text-2xl font-black text-green-600 mb-3">
          ₹{service.basePrice.toLocaleString()} - ₹{upperPrice.toLocaleString()}
        </p>
      ) : (
      )} */}
      <p className="text-lg font-semibold text-blue-600 mb-3">
        Contact for Pricing
      </p>

      {/* Description and Action Button */}
      <p className="text-gray-600 text-sm mb-4 line-clamp-2">{service.shortDescription}</p>

      <Link
        to={`/service/${service._id}`}
        className="block text-center bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-orange-500 transition duration-150 mb-3"
      >
        {/* {showPrices ? 'Book Service' : 'Get Quote'} */}
        Get Quote
      </Link>

      {/* Call Now Button for Sofa Services */}
      {/* Hidden dropdown call now */}
      {isSofaService && (
        <div className="mb-3 hidden">
          <button
            onClick={() => setShowCallOptions(!showCallOptions)}
            className={`w-full text-center ${showCallOptions ? 'bg-green-500' : 'bg-green-600'} text-white px-4 py-2 rounded-lg font-semibold hover:bg-green-700 transition duration-150 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50`}
          >
            Call Now  (24 H)
          </button>

          {/* Call Options Dropdown */}
          {showCallOptions && callNumbers.length > 0 && (
            <div className="mt-2 p-3 bg-gray-50 border border-gray-200 rounded-lg shadow-inner">
              <p className="text-sm font-medium text-gray-700 mb-2">
                Choose a number to call:
              </p>
              <div className="space-y-2">
                {callNumbers.map((numberLabel, index) => (
                  <a
                    key={index}
                    // Using the number label directly in the tel: link as requested
                    href={`tel:${numberLabel}`}
                    className={`block w-full text-center bg-green-100 text-green-800 px-3 py-2 rounded-md text-sm font-medium hover:bg-green-200 transition duration-150`}
                    onClick={() => setShowCallOptions(false)}
                  >
                    {numberLabel}
                  </a>
                ))}
              </div>
            </div>
          )}
          {showCallOptions && callNumbers.length === 0 && (
            <p className="text-sm text-red-500 mt-2">No contact numbers available.</p>
          )}
        </div>
      )}

      {isSofaService && (
        <a
          href="tel:+919689861811"
          className="block text-center bg-green-600 text-white px-4 py-2 mt-2 rounded-lg font-semibold hover:bg-green-700 transition duration-150 mb-3"
        >
          Call Now (24 H)
        </a>
      )}

    </div>
  );
};

export default ServiceCard;