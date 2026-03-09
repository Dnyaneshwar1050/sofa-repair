import React, { useEffect, useState } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import { getServiceDetails, createBooking } from '../api/apiService';
import { apiService } from '../api/apiService';
import { useAuth } from '../context/AuthContext';
import { FaChevronLeft, FaStar } from 'react-icons/fa';
import ReviewForm from '../components/ReviewForm';
import ReviewList from '../components/ReviewList';
import { StarRatingDisplay } from '../components/StarRating';
import { trackServiceBooking } from '../utils/analytics';


const ServiceDetailsPage = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const { user, token } = useAuth();

  const initialAddressState = {
    houseNo: user?.address?.houseNo || '',
    street: user?.address?.street || '',
    landmark: user?.address?.landmark || '',
    area: user?.address?.area || '',
    city: user?.address?.city || '',
    pincode: user?.address?.pincode || '',
  };

  const [service, setService] = useState(null);
  const [selectedOption, setSelectedOption] = useState(null);

  const [detailedAddress, setDetailedAddress] = useState(initialAddressState);

  const [guestPhone, setGuestPhone] = useState(user?.phone || '');
  const [guestEmail, setGuestEmail] = useState(user?.email || '');

  const [budget, setBudget] = useState();
  const [notes, setNotes] = useState('');
  const [error, setError] = useState(null);
  const [loading, setLoading] = useState(true);
  const [bookingLoading, setBookingLoading] = useState(false);
  const [showPrices, setShowPrices] = useState(true);
  const [showReviewForm, setShowReviewForm] = useState(false);
  const [reviewRefreshTrigger, setReviewRefreshTrigger] = useState(0);

  const [bookingSuccessData, setBookingSuccessData] = useState(null);
  const [showRegistrationPrompt, setShowRegistrationPrompt] = useState(false);

  const calculateTotalPrice = () => {
    if (!service) return 0;
    return service.basePrice + (selectedOption ? selectedOption.price : 0);
  }

  // Effect to set initial address and phone based on user data
  useEffect(() => {
    if (user) {
      // Use logged in user's address and contact
      if (user.address) {
        setDetailedAddress(prev => ({
          ...prev,
          houseNo: user.address.houseNo || '',
          street: user.address.street || '',
          landmark: user.address.landmark || '',
          area: user.address.area || '',
          city: user.address.city || '',
          pincode: user.address.pincode || '',
        }));
      }
      setGuestPhone(user.phone || '');
      setGuestEmail(user.email || '');
    }
  }, [user]); // Run when user object loads

  // Fetch price visibility setting
  const fetchPriceVisibility = async () => {
    try {
      const response = await apiService.get('settings/public');
      const settingsData = response.success ? response.data : response;
      setShowPrices(settingsData.show_service_prices !== false);
    } catch (error) {
      console.error('Error fetching price visibility:', error);
      setShowPrices(true); // Default to showing prices on error
    }
  };

  useEffect(() => {
    fetchPriceVisibility();
    // Poll for settings changes every 30 seconds
    const interval = setInterval(fetchPriceVisibility, 30000);
    return () => clearInterval(interval);
  }, []);

  useEffect(() => {
    const fetchService = async () => {
      try {
        const res = await getServiceDetails(id);
        setService(res.data);
        if (res.data.options && res.data.options.length > 0) {
          setSelectedOption(res.data.options[0]);
        }
      } catch {
        setError('Service not found or an error occurred.');
      } finally {
        setLoading(false);
      }
    };
    fetchService();
  }, [id]);

  const handleBooking = async (e) => {
    e.preventDefault();
    setBookingSuccessData(null);
    setShowRegistrationPrompt(false);

    const phoneToSend = user?.phone || guestPhone;

    if (!phoneToSend || phoneToSend.length !== 10) {
      setError("A valid 10-digit mobile number is required for booking.");
      return;
    }

    if (!selectedOption) {
      setError("Please select a service option.");
      return;
    }

    const { houseNo, area, city, pincode } = detailedAddress;
    if (!houseNo || !area || !city || !pincode) {
      setError("Please fill in all required address fields.");
      return;
    }

    setBookingLoading(true);
    setError(null);

    const bookingData = {
      serviceId: service._id,
      option: selectedOption,
      address: detailedAddress,
      budget: Number(budget),
      notes: notes,
      phone: phoneToSend,
      email: guestEmail || undefined,
    };

    try {
      const response = await createBooking(bookingData);
      const data = response.data;

      // Track conversion in Google Analytics
      const totalPrice = calculateTotalPrice();
      const bookingDetails = {
        serviceId: service._id,
        serviceName: service.name,
        category: service.category?.name || 'Unknown',
        price: totalPrice,
        bookingId: data?._id || `booking_${Date.now()}`,
        option: selectedOption.name
      };
      trackServiceBooking(bookingDetails);

      setBookingSuccessData(data);
      if (data.isGuestBookingPrompt) {
        setShowRegistrationPrompt(true);
      } else if (user) {
        // Logged-in user booking successfully
        navigate('/requests');
      }
      // If guest and no prompt (e.g., they already registered as a guest and are just booking again), they stay on the success screen and can navigate home.

    } catch (err) {
      setError(err.response?.data?.message || 'Booking failed. An error occurred.');
    } finally {
      setBookingLoading(false);
    }
  };

  // Handler for address fields
  const handleAddressChange = (field, value) => {
    setDetailedAddress(prev => ({ ...prev, [field]: value }));
  };


  if (loading) return <div className="text-center mt-10">Loading service details...</div>;
  if (error && !service) return <div className="text-center mt-10 text-red-500">{error}</div>;
  if (!service) return <div className="text-center mt-10">Service not found.</div>;

  if (bookingSuccessData) {
    const { _id: bookingId, phone, email } = bookingSuccessData;
    const registrationParams = new URLSearchParams({
      phone: phone,
      email: email || ''
    }).toString();

    return (
      <div className="container mx-auto p-4 max-w-lg text-center">
        <h2 className="text-3xl font-black text-green-600 mb-4">Booking Confirmed! 🎉</h2>
        <p className="mb-2">Your booking request has been successfully placed. We will contact you shortly.</p>
        <p className="mb-8 font-semibold">Booking ID: {bookingId}</p>

        {showRegistrationPrompt && (
          <div className="p-6 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-800 mb-6 rounded-lg shadow-md">
            <h3 className="text-xl font-bold mb-3">Complete Your Registration!</h3>
            <p className="mb-4 text-left">
              To manage your booking history, track status updates, and leave a review,
              please secure your account by setting a password now.
            </p>
            <button
              onClick={() => navigate(`/complete-registration?${registrationParams}`)}
              className="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg transition duration-300 w-full"
            >
              Set Password Now
            </button>
          </div>
        )}

        <button
          onClick={() => navigate('/')}
          className="mt-4 bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg"
        >
          Go to Homepage
        </button>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-100">
      <div className="max-w-6xl mx-auto pt-6 px-4">
        <Link to={`/services/${service.category._id}`} className="text-orange-600 hover:underline flex items-center">
          <FaChevronLeft className="mr-2 text-sm" /> Back to {service.category.name}
        </Link>
      </div>

      <div className="max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-8 p-4">
        <div className="lg:col-span-2 bg-white p-6 rounded-xl shadow-lg space-y-8">
          {/* Header */}
          <div className='border-b pb-4'>
            <h1 className="text-3xl font-black mb-1 text-gray-900">{service.name}</h1>
            <p className="text-lg text-blue-600 mb-2">{service.category.name}</p>
            <StarRatingDisplay
              rating={service.averageRating || 0}
              count={service.reviewCount || 0}
            />
          </div>

          {/* Image */}
          <img src={service.imageUrl || 'https://via.placeholder.com/800x400'} alt={service.name} className="w-full h-80 object-cover rounded-lg shadow-md" />

          {/* Description */}
          <p className="text-lg text-gray-700">{service.shortDescription}</p>

          {/* Options/Packages */}
          <h3 className="text-2xl font-black mb-3 pt-4 border-t">Select a Package</h3>
          <div className="space-y-4">
            {service.options.map((option, index) => (
              <div
                key={index}
                className={`p-4 border rounded-lg cursor-pointer transition flex justify-between items-center ${selectedOption?.name === option.name ? 'border-blue-600 bg-blue-50 shadow-md' : 'border-gray-300 hover:bg-gray-100'}`}
                onClick={() => setSelectedOption(option)}
              >
                <div>
                  <p className="font-bold text-lg text-gray-900">{option.name}</p>
                  <ul className="list-disc ml-5 text-sm text-gray-600">
                    {option.details && option.details.map((detail, i) => <li key={i}>{detail}</li>)}
                  </ul>
                </div>
                {showPrices ? (
                  <p className="font-black text-green-600">
                    {option.price >= 0 ? `+ ₹${option.price}` : `₹${calculateTotalPrice()}`}
                  </p>
                ) : (
                  <p className="font-semibold text-blue-600">
                    Contact for Pricing
                  </p>
                )}
              </div>
            ))}
          </div>

          <div className="mt-8 lg:hidden bg-white p-6 rounded-xl shadow-lg border-t-4 border-orange-500 space-y-4">
            {selectedOption ? (
              <>
                <h3 className="text-2xl font-black text-orange-500 border-b pb-3">
                  Booking Summary
                </h3>
                {showPrices ? (
                  <>
                    <p className="text-xl font-bold">
                      Starting Price: <span className="text-red-500">₹{service.basePrice.toLocaleString()}</span>
                      {/* Display estimated max range */}
                      {service.priceUpperRange && service.priceUpperRange > service.basePrice && (
                        <span className="text-gray-500 text-base font-normal"> (Up to ₹{service.priceUpperRange.toLocaleString()})</span>
                      )}
                    </p>
                    <p className="text-lg font-semibold">Package: {selectedOption.name}</p>
                    <p className="text-3xl font-black text-green-600 pt-2 border-t mt-4">
                      Estimated Booking Price: ₹{calculateTotalPrice().toLocaleString()}
                    </p>

                    {/* Disclaimer about price range */}
                    <p className="text-xs text-red-500 italic">
                      Note: The final price may vary from the estimated booking price depending on the complexity of the job found during the on-site visit.
                    </p>
                  </>
                ) : (
                  <>
                    <p className="text-xl font-bold text-blue-600">
                      Contact for Pricing
                    </p>
                    <p className="text-lg font-semibold">Package: {selectedOption.name}</p>
                    <p className="text-lg text-gray-600 pt-2 border-t mt-4">
                      Get a custom quote for your specific needs
                    </p>
                    <p className="text-sm text-blue-500 italic">
                      Our team will provide you with a detailed quote after understanding your requirements.
                    </p>
                  </>
                )}

                {/* Booking Form */}
                <form onSubmit={handleBooking} className="space-y-4 pt-4">
                  <h4 className="text-xl font-bold text-gray-900">Confirm Details</h4>

                  {/* Phone Number Input - For both guests and logged-in users */}
                  <div className='pb-2 border-b border-gray-200'>
                    <label className="block text-sm font-medium text-gray-700">Mobile Number {user ? '(Override if different)' : '*'}</label>
                    <div className="mt-1 flex rounded-md shadow-sm">
                      <span className="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm font-semibold">
                        +91
                      </span>
                      <input
                        type="tel"
                        value={guestPhone}
                        onChange={(e) => setGuestPhone(e.target.value)}
                        required={!user}
                        pattern="[0-9]{10}"
                        maxLength="10"
                        className="block w-full border border-gray-300 rounded-r-md p-2 text-lg font-bold placeholder:text-gray-400 focus:ring-orange-500 focus:border-orange-500"
                        placeholder={user ? `Use ${user.phone} or enter different number` : "10 digit phone number"}
                      />
                    </div>
                    <p className='text-xs text-gray-500 mt-1'>
                      {user ? 'You can use a different phone number for this booking if needed.' : 'We\'ll use this number to create your booking and link it to your profile.'}
                    </p>
                  </div>

                  {!user && (
                    <div className='pb-2 border-b border-gray-200'>
                      <label className="block text-sm font-medium text-gray-700">Email (Optional)</label>
                      <input
                        type="email"
                        value={guestEmail}
                        onChange={(e) => setGuestEmail(e.target.value)}
                        className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 text-sm"
                        placeholder="Enter your email"
                      />
                      <p className='text-xs text-gray-500 mt-1'>We recommend providing an email for better communication and registration.</p>
                    </div>
                  )}

                  <div className="space-y-3 p-3 border rounded-lg bg-gray-50">
                    <p className="font-semibold text-sm">Service Address</p>
                    <div className='grid grid-cols-2 gap-3'>
                      <input
                        type="text"
                        value={detailedAddress.houseNo}
                        onChange={(e) => handleAddressChange('houseNo', e.target.value)}
                        required
                        className="block w-full border border-gray-300 rounded-md shadow-sm p-2 text-sm"
                        placeholder="House/Flat No. *"
                      />
                      <input
                        type="text"
                        value={detailedAddress.street}
                        onChange={(e) => handleAddressChange('street', e.target.value)}
                        className="block w-full border border-gray-300 rounded-md shadow-sm p-2 text-sm"
                        placeholder="Street / Road"
                      />
                    </div>
                    <input
                      type="text"
                      value={detailedAddress.landmark}
                      onChange={(e) => handleAddressChange('landmark', e.target.value)}
                      className="block w-full border border-gray-300 rounded-md shadow-sm p-2 text-sm"
                      placeholder="Landmark (Optional)"
                    />
                    <input
                      type="text"
                      value={detailedAddress.area}
                      onChange={(e) => handleAddressChange('area', e.target.value)}
                      required
                      className="block w-full border border-gray-300 rounded-md shadow-sm p-2 text-sm"
                      placeholder="Area / Locality *"
                    />
                    <div className='grid grid-cols-2 gap-3'>
                      <input
                        type="text"
                        value={detailedAddress.city}
                        onChange={(e) => handleAddressChange('city', e.target.value)}
                        required
                        className="block w-full border border-gray-300 rounded-md shadow-sm p-2 text-sm"
                        placeholder="City *"
                      />
                      <input
                        type="text"
                        value={detailedAddress.pincode}
                        onChange={(e) => handleAddressChange('pincode', e.target.value)}
                        required
                        pattern="\d{6}"
                        maxLength="6"
                        className="block w-full border border-gray-300 rounded-md shadow-sm p-2 text-sm"
                        placeholder="Pincode *"
                      />
                    </div>
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-700">Your Estimated Budget (₹)</label>
                    <input
                      type="number"
                      value={budget}
                      onChange={(e) => setBudget(Number(e.target.value))}
                      required
                      // min="500"
                      className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 text-lg font-bold"
                      placeholder="Min 500"
                    />
                  </div>

                  <textarea
                    value={notes}
                    onChange={(e) => setNotes(e.target.value)}
                    rows="2"
                    className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"
                    placeholder="Notes (e.g., preferred time, entry instructions)"
                  />

                  {error && <p className="text-red-500 text-sm">{error}</p>}

                  <button
                    type="submit"
                    disabled={bookingLoading || !selectedOption || !detailedAddress.houseNo || !detailedAddress.pincode || (guestPhone.length !== 10)}
                    className="w-full bg-orange-600 text-white py-3 rounded-lg font-semibold hover:bg-orange-700 disabled:bg-gray-400 transition shadow-md"
                  >
                    {bookingLoading ? 'Processing...' : (showPrices ? 'Confirm Booking' : 'Request Quote & Schedule Pickup')}
                  </button>

                  {!user && (
                    <p className="text-sm text-gray-600 mt-2 text-center">
                      <Link to="/auth" className='text-orange-600 font-semibold hover:underline'>Log in or Register</Link> for full account management features.
                    </p>
                  )}
                </form>
              </>
            ) : (
              <p className='text-gray-600'>Please select a service option to see the summary.</p>
            )}
          </div>

          {/* Reviews Section */}
          <div className="pt-8 border-t">
            <div className="flex items-center justify-between mb-6">
              <h3 className="text-2xl font-black text-gray-900">Reviews & Ratings</h3>
              {user && (
                <button
                  onClick={() => setShowReviewForm(!showReviewForm)}
                  className="bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-blue-700 transition-colors"
                >
                  {showReviewForm ? 'Cancel' : 'Write a Review'}
                </button>
              )}
            </div>

            {showReviewForm && (
              <div className="mb-8">
                <ReviewForm
                  serviceId={service._id}
                  onReviewSubmitted={() => {
                    setShowReviewForm(false);
                    setReviewRefreshTrigger(prev => prev + 1);
                  }}
                  onCancel={() => setShowReviewForm(false)}
                />
              </div>
            )}

            <ReviewList
              serviceId={service._id}
              refreshTrigger={reviewRefreshTrigger}
            />
          </div>
        </div>

        {/* RIGHT COLUMN: Fixed Booking Summary (1/3 width) */}
        <div className="lg:col-span-1 hidden lg:block">
          <div className="sticky top-6 bg-white p-6 rounded-xl shadow-2xl border-t-4 border-orange-500 space-y-4 max-h-[calc(100vh-24px)] overflow-y-auto">
            <h3 className="text-2xl font-black text-orange-500 border-b pb-3">
              Booking Summary
            </h3>

            {selectedOption ? (
              <>
                {showPrices ? (
                  <>
                    <p className="text-xl font-bold">
                      Starting Price: <span className="text-red-500">₹{service.basePrice.toLocaleString()}</span>
                      {/* Display estimated max range */}
                      {service.priceUpperRange && service.priceUpperRange > service.basePrice && (
                        <span className="text-gray-500 text-base font-normal"> (Up to ₹{service.priceUpperRange.toLocaleString()})</span>
                      )}
                    </p>
                    <p className="text-lg font-semibold">Package: {selectedOption.name}</p>
                    <p className="text-3xl font-black text-green-600 pt-2 border-t mt-4">
                      Estimated Booking Price: ₹{calculateTotalPrice().toLocaleString()}
                    </p>

                    {/* Disclaimer about price range */}
                    <p className="text-xs text-red-500 italic">
                      Note: The final price may vary from the estimated booking price depending on the complexity of the job found during the on-site visit.
                    </p>
                  </>
                ) : (
                  <>
                    <p className="text-xl font-bold text-blue-600">
                      Contact for Pricing
                    </p>
                    <p className="text-lg font-semibold">Package: {selectedOption.name}</p>
                    <p className="text-lg text-gray-600 pt-2 border-t mt-4">
                      Get a custom quote for your specific needs
                    </p>
                    <p className="text-sm text-blue-500 italic">
                      Our team will provide you with a detailed quote after understanding your requirements.
                    </p>
                  </>
                )}

                {/* Booking Form */}
                <form onSubmit={handleBooking} className="space-y-4 pt-4">
                  <h4 className="text-xl font-bold text-gray-900">Confirm Details</h4>

                  {/* Phone Number Input - For both guests and logged-in users */}
                  <div className='pb-2 border-b border-gray-200'>
                    <label className="block text-sm font-medium text-gray-700">Mobile Number {user ? '(Override if different)' : '*'}</label>
                    <div className="mt-1 flex rounded-md shadow-sm">
                      <span className="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm font-semibold">
                        +91
                      </span>
                      <input
                        type="tel"
                        value={guestPhone}
                        onChange={(e) => setGuestPhone(e.target.value)}
                        required={!user}
                        pattern="[0-9]{10}"
                        maxLength="10"
                        className="block w-full border border-gray-300 rounded-r-md p-2 text-lg font-bold placeholder:text-gray-400 focus:ring-orange-500 focus:border-orange-500"
                        placeholder={user ? `Use ${user.phone} or enter different number` : "10 digit phone number"}
                      />
                    </div>
                    <p className='text-xs text-gray-500 mt-1'>
                      {user ? 'You can use a different phone number for this booking if needed.' : 'We\'ll use this number to create your booking and link it to your profile.'}
                    </p>
                  </div>

                  {!user && (
                    <div className='pb-2 border-b border-gray-200'>
                      <label className="block text-sm font-medium text-gray-700">Email (Optional)</label>
                      <input
                        type="email"
                        value={guestEmail}
                        onChange={(e) => setGuestEmail(e.target.value)}
                        className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 text-sm"
                        placeholder="Enter your email"
                      />
                      <p className='text-xs text-gray-500 mt-1'>We recommend providing an email for better communication and registration.</p>
                    </div>
                  )}

                  <div className="space-y-3 p-3 border rounded-lg bg-gray-50">
                    <p className="font-semibold text-sm">Service Address</p>
                    <div className='grid grid-cols-2 gap-3'>
                      <input
                        type="text"
                        value={detailedAddress.houseNo}
                        onChange={(e) => handleAddressChange('houseNo', e.target.value)}
                        required
                        className="block w-full border border-gray-300 rounded-md shadow-sm p-2 text-sm"
                        placeholder="House/Flat No. *"
                      />
                      <input
                        type="text"
                        value={detailedAddress.street}
                        onChange={(e) => handleAddressChange('street', e.target.value)}
                        className="block w-full border border-gray-300 rounded-md shadow-sm p-2 text-sm"
                        placeholder="Street / Road"
                      />
                    </div>
                    <input
                      type="text"
                      value={detailedAddress.landmark}
                      onChange={(e) => handleAddressChange('landmark', e.target.value)}
                      className="block w-full border border-gray-300 rounded-md shadow-sm p-2 text-sm"
                      placeholder="Landmark (Optional)"
                    />
                    <input
                      type="text"
                      value={detailedAddress.area}
                      onChange={(e) => handleAddressChange('area', e.target.value)}
                      required
                      className="block w-full border border-gray-300 rounded-md shadow-sm p-2 text-sm"
                      placeholder="Area / Locality *"
                    />
                    <div className='grid grid-cols-2 gap-3'>
                      <input
                        type="text"
                        value={detailedAddress.city}
                        onChange={(e) => handleAddressChange('city', e.target.value)}
                        required
                        className="block w-full border border-gray-300 rounded-md shadow-sm p-2 text-sm"
                        placeholder="City *"
                      />
                      <input
                        type="text"
                        value={detailedAddress.pincode}
                        onChange={(e) => handleAddressChange('pincode', e.target.value)}
                        required
                        pattern="\d{6}"
                        maxLength="6"
                        className="block w-full border border-gray-300 rounded-md shadow-sm p-2 text-sm"
                        placeholder="Pincode *"
                      />
                    </div>
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-700">Your Estimated Budget (₹)</label>
                    <input
                      type="number"
                      value={budget}
                      onChange={(e) => setBudget(Number(e.target.value))}
                      required
                      // min="500"
                      className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 text-lg font-bold"
                      placeholder={`Min ${service.basePrice.toLocaleString() || "4500"}`}
                    />
                  </div>

                  <textarea
                    value={notes}
                    onChange={(e) => setNotes(e.target.value)}
                    rows="2"
                    className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"
                    placeholder="Notes (e.g., preferred time, entry instructions)"
                  />

                  {error && <p className="text-red-500 text-sm">{error}</p>}

                  <button
                    type="submit"
                    disabled={bookingLoading || !selectedOption || !detailedAddress.houseNo || !detailedAddress.pincode || (guestPhone.length !== 10)}
                    className="w-full bg-orange-600 text-white py-3 rounded-lg font-semibold hover:bg-orange-700 disabled:bg-gray-400 transition shadow-md"
                  >
                    {bookingLoading ? 'Processing...' : (showPrices ? 'Confirm Booking' : 'Request Quote & Schedule Pickup')}
                  </button>

                  {!user && (
                    <p className="text-sm text-gray-600 mt-2 text-center">
                      <Link to="/auth" className='text-orange-600 font-semibold hover:underline'>Log in or Register</Link> for full account management features.
                    </p>
                  )}
                </form>
              </>
            ) : (
              <p className='text-gray-600'>Please select a service option to see the summary.</p>
            )}
          </div>
        </div>
      </div>
    </div>
  );
};

export default ServiceDetailsPage;