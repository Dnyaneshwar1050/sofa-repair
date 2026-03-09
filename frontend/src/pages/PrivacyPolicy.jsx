import React from 'react';
import { Link } from 'react-router-dom';

const PrivacyPolicy = () => {
  return (
    <div className="min-h-screen bg-gray-50 py-16">
      <div className="max-w-5xl mx-auto bg-white rounded-xl shadow-md p-10">
        <div className="mb-6 text-center">
          <h1 className="text-3xl font-extrabold text-gray-900">Privacy Policy</h1>
          <p className="mt-2 text-sm text-gray-600">Last updated: 2025</p>
        </div>

        <section className="mb-6">
          <h2 className="text-xl font-semibold text-gray-800 mb-2">Information We Collect</h2>
          <p className="text-gray-600">We collect personal information (name, email, phone), usage data (pages visited, services searched), payment and booking records, user generated content (reviews, photos), and device information (IP address, browser type).</p>
        </section>

        <section className="mb-6">
          <h2 className="text-xl font-semibold text-gray-800 mb-2">How We Use Your Information</h2>
          <ul className="list-disc pl-6 text-gray-600">
            <li>To process bookings and payments.</li>
            <li>To deliver services and communicate updates.</li>
            <li>To personalize content and improve service quality.</li>
            <li>To detect and prevent fraud and abuse.</li>
          </ul>
        </section>

        <section className="mb-6">
          <h2 className="text-xl font-semibold text-gray-800 mb-2">Data Retention & International Transfers</h2>
          <p className="text-gray-600">We retain account and transaction data for legal and operational purposes. Information may be stored or processed in locations outside India where our service providers operate, with legal safeguards.</p>
        </section>

        <section className="mb-6">
          <h2 className="text-xl font-semibold text-gray-800 mb-2">User Rights</h2>
          <p className="text-gray-600">Users may access, correct, or delete their account data. For privacy requests contact privacy@khushihomesofarepairing.com. We are not liable for actions taken by third parties or for content posted publicly by users.</p>
        </section>

        <section className="mb-6">
          <h2 className="text-xl font-semibold text-gray-800 mb-2">Cookies and Tracking</h2>
          <p className="text-gray-600">We use cookies and similar technologies for authentication, analytics, and personalization. Third-party tools like Google Analytics may be used to collect anonymous usage stats.</p>
        </section>

        <section className="mb-6">
          <h2 className="text-xl font-semibold text-gray-800 mb-2">Policy Changes</h2>
          <p className="text-gray-600">We may update our Privacy Policy periodically. We will post a notice on the site and update the policy effective date.</p>
        </section>

        <section className="mb-4 border-t pt-4">
          <h3 className="font-semibold text-gray-800">Contact</h3>
          <p className="text-gray-600">Email: <a href="mailto:privacy@khushihomesofarepairing.com" className="text-orange-600 hover:underline">privacy@khushihomesofarepairing.com</a></p>
        </section>

        <div className="mt-6 text-center">
          <Link to="/" className="text-orange-600 hover:underline">Back to Home</Link>
        </div>
      </div>
    </div>
  );
};

export default PrivacyPolicy;
