import React, { useState, useEffect } from "react";
// Remove sonner if not used or ensure it's installed
import { toast } from "sonner"; 
import { apiService } from "../api/apiService";
import { Loader2, Upload, CheckCircle, AlertCircle } from "lucide-react";

// Simple UI Components for this page to keep it standalone
const Label = ({ children, htmlFor }) => (
  <label htmlFor={htmlFor} className="block text-sm font-medium text-gray-700 mb-1">
    {children}
  </label>
);

const Input = React.forwardRef(({ className, ...props }, ref) => (
  <input
    ref={ref}
    className={`w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all ${className}`}
    {...props}
  />
));
Input.displayName = "Input";

const TextArea = React.forwardRef(({ className, ...props }, ref) => (
  <textarea
    ref={ref}
    className={`w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all min-h-[100px] ${className}`}
    {...props}
  />
));
TextArea.displayName = "TextArea";

const Button = ({ children, isLoading, className, ...props }) => (
  <button
    disabled={isLoading}
    className={`w-full flex justify-center items-center px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-200 transition-all disabled:opacity-70 disabled:cursor-not-allowed ${className}`}
    {...props}
  >
    {isLoading && <Loader2 className="w-5 h-5 mr-2 animate-spin" />}
    {children}
  </button>
);

const ServiceRequestTrail = () => {
  const [services, setServices] = useState([]);
  const [loading, setLoading] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [success, setSuccess] = useState(false);
  const [error, setError] = useState("");

  const [formData, setFormData] = useState({
    name: "", // Added name field
    customServiceName: "",
    houseNo: "",
    area: "",
    city: "",
    pincode: "",
    phone: "",
    notes: "",
    email: "", // Added email field
  });

  const [selectedFile, setSelectedFile] = useState(null);
  const [previewUrl, setPreviewUrl] = useState(null);

  // No need to fetch services as select is removed


  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));
  };

  const handleFileChange = (e) => {
    const file = e.target.files[0];
    if (file) {
      if (!file.type.startsWith("image/")) {
        toast.error("Please upload an image file.");
        return;
      }
      if (file.size > 5 * 1024 * 1024) {
        toast.error("File size must be less than 5MB.");
        return;
      }
      setSelectedFile(file);
      const url = URL.createObjectURL(file);
      setPreviewUrl(url);
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError("");
    setSuccess(false);

    if (!formData.name.trim()) {
      toast.error("Please enter your name.");
      return;
    }

    if (!formData.customServiceName.trim()) {
      toast.error("Please enter the service you need.");
      return;
    }

    setSubmitting(true);

    try {
      const data = new FormData();
      data.append("customServiceName", formData.customServiceName);
      
      // Construct address object for backend
      const addressObj = {
        houseNo: formData.houseNo,
        area: formData.area,
        city: formData.city,
        pincode: formData.pincode,
      };
      
      data.append("address[houseNo]", formData.houseNo);
      data.append("address[area]", formData.area);
      data.append("address[city]", formData.city);
      data.append("address[pincode]", formData.pincode);
      
      data.append("phone", formData.phone);
      data.append("name", formData.name);
      if (formData.notes) data.append("notes", formData.notes);
      if (formData.email) data.append("email", formData.email);

      if (selectedFile) {
        data.append("images", selectedFile);
      }

      const response = await apiService.post("bookings", data);
      
      if (response) {
        setSuccess(true);
        toast.success("Service request submitted successfully!");
        setFormData({
          name: "",
          customServiceName: "",
          houseNo: "",
          area: "",
          city: "",
          pincode: "",
          phone: "",
          notes: "",
          email: "",
        });
        setSelectedFile(null);
        setPreviewUrl(null);
      }
    } catch (err) {
      console.error("Submission failed", err);
      const msg = err.response?.data?.message || err.message || "Failed to submit request.";
      setError(msg);
      toast.error(msg);
    } finally {
      setSubmitting(false);
    }
  };



  if (success) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gray-50 p-4">
        <div className="max-w-md w-full bg-white rounded-2xl shadow-xl p-8 text-center">
          <div className="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <CheckCircle className="w-8 h-8 text-green-600" />
          </div>
          <h2 className="text-2xl font-bold text-gray-900 mb-2">Request Received!</h2>
          <p className="text-gray-600 mb-8">
            Thank you for your request. Our team will review the details and contact you shortly at <strong>{formData.phone}</strong>.
          </p>
          <button
            onClick={() => setSuccess(false)}
            className="text-blue-600 hover:text-blue-800 font-medium"
          >
            Submit another request
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
      <div className="max-w-2xl mx-auto">
        <div className="text-center mb-10">
          <h1 className="text-3xl font-extrabold text-gray-900 sm:text-4xl">
            Book a Service
          </h1>
          <p className="mt-4 text-lg text-gray-600">
            Tell us what you need and we'll get it fixed. 
            Upload a photo for a better estimate.
          </p>
        </div>

        <div className="bg-white rounded-2xl shadow-xl overflow-hidden">
          <div className="p-8">
            {error && (
              <div className="mb-6 p-4 bg-red-50 border border-red-100 rounded-lg flex items-start">
                <AlertCircle className="w-5 h-5 text-red-500 mr-2 mt-0.5" />
                <p className="text-red-700 text-sm">{error}</p>
              </div>
            )}

            <div className="mb-8 p-4 bg-green-50 border border-green-100 rounded-xl flex items-center justify-between">
              <div>
                <p className="text-green-800 font-semibold text-lg">Quick Booking?</p>
                <p className="text-green-600 text-sm">Call us directly for immediate help</p>
              </div>
              <a 
                href="tel:+919689861811" 
                className="bg-green-600 text-white px-5 py-2.5 rounded-lg font-bold hover:bg-green-700 transition-all flex items-center gap-2 shadow-md"
              >
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                Call Now
              </a>
            </div>

            <form onSubmit={handleSubmit} className="space-y-6">
              {/* Name field - REQUIRED FIRST */}
              <div>
                <Label htmlFor="name">Your Name <span className="text-red-500">*</span></Label>
                <Input
                  id="name"
                  name="name"
                  required
                  placeholder="Enter your full name"
                  value={formData.name}
                  onChange={handleInputChange}
                />
              </div>

              {/* Contact Details - Phone & Email */}
              <div className="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <Label htmlFor="phone">Phone Number <span className="text-red-500">*</span></Label>
                    <Input
                    id="phone"
                    name="phone"
                    type="tel"
                    required
                    placeholder="+91 98765 43210"
                    value={formData.phone}
                    onChange={handleInputChange}
                    />
                </div>
                <div>
                     <Label htmlFor="email">Email (Optional)</Label>
                     <Input
                     id="email"
                     name="email"
                     type="email"
                     placeholder="you@example.com"
                     value={formData.email}
                     onChange={handleInputChange}
                     />
                </div>
              </div>

              {/* Service Selection */}
              <div>
                <Label htmlFor="customServiceName">Service Required <span className="text-red-500">*</span></Label>
                <Input
                  id="customServiceName"
                  name="customServiceName"
                  required
                  placeholder="e.g. Sofa Repair, Chair Fix, Cushion Replacement"
                  value={formData.customServiceName}
                  onChange={handleInputChange}
                />
              </div>

              {/* Photo Upload */}
              <div>
                <Label>Upload Photo (Optional)</Label>
                <div 
                  className={`mt-2 flex justify-center px-6 pt-5 pb-6 border-2 border-dashed rounded-lg transition-all ${
                    previewUrl ? 'border-blue-500 bg-blue-50' : 'border-gray-300 hover:border-gray-400'
                  }`}
                >
                  <div className="space-y-1 text-center">
                    {previewUrl ? (
                      <div className="relative">
                        <img 
                          src={previewUrl} 
                          alt="Preview" 
                          className="h-48 object-contain mx-auto rounded-lg"
                        />
                        <button
                          type="button"
                          onClick={() => {
                            setSelectedFile(null);
                            setPreviewUrl(null);
                          }}
                          className="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600 shadow-md"
                        >
                          <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                      </div>
                    ) : (
                      <>
                        <Upload className="mx-auto h-12 w-12 text-gray-400" />
                        <div className="flex text-sm text-gray-600 justify-center">
                          <label
                            htmlFor="file-upload"
                            className="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none"
                          >
                            <span>Upload a file</span>
                            <input
                              id="file-upload"
                              name="file-upload"
                              type="file"
                              className="sr-only"
                              accept="image/*"
                              onChange={handleFileChange}
                            />
                          </label>
                          <p className="pl-1">or drag and drop</p>
                        </div>
                        <p className="text-xs text-gray-500">PNG, JPG, GIF up to 5MB</p>
                      </>
                    )}
                  </div>
                </div>
              </div>

              {/* Address Section */}
              <div className="bg-gray-50 p-4 rounded-lg border border-gray-200">
                <h3 className="text-md font-semibold text-gray-900 mb-4">Service Address</h3>
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                  <div className="col-span-1 sm:col-span-2">
                    <Label htmlFor="houseNo">House No / Building <span className="text-red-500">*</span></Label>
                    <Input
                      id="houseNo"
                      name="houseNo"
                      required
                      placeholder="Flat 101, Galaxy Apts"
                      value={formData.houseNo}
                      onChange={handleInputChange}
                    />
                  </div>
                  <div>
                    <Label htmlFor="area">Area / Colony <span className="text-red-500">*</span></Label>
                    <Input
                      id="area"
                      name="area"
                      required
                      placeholder="Kothrud"
                      value={formData.area}
                      onChange={handleInputChange}
                    />
                  </div>
                  <div>
                    <Label htmlFor="city">City <span className="text-red-500">*</span></Label>
                    <Input
                      id="city"
                      name="city"
                      required
                      placeholder="Pune"
                      value={formData.city}
                      onChange={handleInputChange}
                    />
                  </div>
                  <div>
                    <Label htmlFor="pincode">Pincode <span className="text-red-500">*</span></Label>
                    <Input
                      id="pincode"
                      name="pincode"
                      required
                      placeholder="411038"
                      value={formData.pincode}
                      onChange={handleInputChange}
                    />
                  </div>
                </div>
              </div>

              {/* Notes */}
              <div>
                <Label htmlFor="notes">Additional Notes</Label>
                <TextArea
                  id="notes"
                  name="notes"
                  placeholder="Describe your issue in detail..."
                  value={formData.notes}
                  onChange={handleInputChange}
                />
              </div>

              <div className="pt-4">
                <Button type="submit" isLoading={submitting}>
                  Submit Request
                </Button>
              </div>

            </form>
          </div>
        </div>
      </div>
    </div>
  );
};

export default ServiceRequestTrail;
