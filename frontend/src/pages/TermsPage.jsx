import React from 'react';
import { Link } from 'react-router-dom';

const TermsPage = () => {
  return (
    <div className="min-h-screen bg-gray-50 py-16">
      <div className="max-w-5xl mx-auto bg-white rounded-xl shadow-md p-10">
        <div className="mb-6 text-center">
          <h1 className="text-3xl font-extrabold text-gray-900">Terms & Conditions</h1>
          <p className="mt-2 text-sm text-gray-600">Effective Date: 2025</p>
        </div>

        <section className="mb-6">
          <h2 className="text-xl font-semibold text-gray-800 mb-2">Acceptance of Terms</h2>
          <p className="text-gray-600">By accessing or using our platform, you agree to these Terms and our Privacy Policy. Use of the platform is conditioned on compliance with these terms.</p>
        </section>

        <section className="mb-6">
          <h2 className="text-xl font-semibold text-gray-800 mb-2">User Accounts</h2>
          <p className="text-gray-600">Users must provide accurate details. Accounts are non-transferable. We may suspend or terminate access for violations.</p>
        </section>

        <section className="mb-6">
          <h2 className="text-xl font-semibold text-gray-800 mb-2">Bookings & Payments</h2>
          <p className="text-gray-600">Bookings are offers to purchase services subject to provider acceptance. Payment is due at time of booking or as per the chosen option. Refund policies are set out in the Refund & Cancellation Policy.</p>
        </section>

        <section className="mb-6">
          <h2 className="text-xl font-semibold text-gray-800 mb-2">User Conduct</h2>
          <p className="text-gray-600">Users must not use the platform for illegal activity. Harassment, abuse, or attempts to compromise system integrity will lead to account suspension.</p>
        </section>

        <section className="mb-6">
          <h2 className="text-xl font-semibold text-gray-800 mb-2">Intellectual Property</h2>
          <p className="text-gray-600">All content, design and trademarks are property of the Platform or its licensors. You may not use them without permission.</p>
        </section>

        <section className="mb-6">
          <h2 className="text-xl font-semibold text-gray-800 mb-2">Limitation of Liability</h2>
          <p className="text-gray-600">To the maximum extent permitted by law, the platform is not responsible for indirect or consequential damages. Our liability is limited to the value of the transaction.</p>
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

export default TermsPage;
