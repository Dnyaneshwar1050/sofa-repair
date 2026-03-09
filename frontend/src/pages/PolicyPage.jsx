import React from 'react';
import { Link } from 'react-router-dom';

const PolicyPage = () => {
  return (
    <div className="min-h-screen bg-gray-50 py-16">
      <div className="max-w-4xl mx-auto bg-white rounded-xl shadow-md p-10">
        <div className="mb-8 text-center">
          <h1 className="text-3xl font-extrabold text-gray-900">Refund & Cancellation Policy</h1>
          <p className="mt-2 text-sm text-gray-600">Updated: 2025</p>
        </div>

        <section className="mb-6">
          <h2 className="text-xl font-semibold text-gray-800 mb-2">Payment Made via the Platform at the Time of Booking</h2>
          <h3 className="font-medium text-gray-700">Cancellation Terms</h3>
          <ul className="list-disc pl-6 mt-2 text-gray-600">
            <li>Cancellation more than 4 hours prior to scheduled service: Full refund</li>
            <li>Cancellation within 4 hours of scheduled service: 50% refund</li>
            <li>Cancellation after service provider is en route or at location: No refund</li>
          </ul>
          <h3 className="font-medium text-gray-700 mt-3">Refund Processing</h3>
          <p className="text-gray-600">Refunds will be initiated to the original payment method within 5–7 business days, subject to bank processing timelines.</p>
        </section>

        <section className="mb-6">
          <h2 className="text-xl font-semibold text-gray-800 mb-2">Payment Made via the Platform After Completion of Service</h2>
          <h3 className="font-medium text-gray-700">Cancellation Terms</h3>
          <p className="text-gray-600">Cancellations must be made before service commencement to avoid charges. Once the service has commenced or is completed, no cancellations or refunds are permitted.</p>
          <h3 className="font-medium text-gray-700 mt-3">Dispute Resolution</h3>
          <p className="text-gray-600">Rework by the same or alternate service provider or a partial refund may be offered at the sole discretion of the Platform based on internal review and evidence.</p>
        </section>

        <section className="mb-6">
          <h2 className="text-xl font-semibold text-gray-800 mb-2">Payment Made Outside the Platform After Completion of Service</h2>
          <p className="text-gray-600">The Platform does not assume responsibility for payments made outside its official payment channels. No refund, dispute resolution or customer support shall be extended in such cases. Customers are strictly advised to transact only via the Platform.</p>
        </section>

        <section className="mb-6">
          <h2 className="text-xl font-semibold text-gray-800 mb-2">Partial or Advance Payment via Platform Before or During the Service</h2>
          <h3 className="font-medium text-gray-700">Cancellation Before Service Start</h3>
          <ul className="list-disc pl-6 mt-2 text-gray-600">
            <li>4+ hours before service time: Full refund of the advance amount</li>
            <li>Within 4 hours of service time: 50% refund of the advance amount</li>
            <li>After service has commenced: Refund eligibility subject to Platform's assessment</li>
          </ul>
          <h3 className="font-medium text-gray-700 mt-3">Mid-Service Cancellation</h3>
          <p className="text-gray-600">Refund based on pro-rata for work completed and consideration of materials/resources already used. Refunds will be initiated within 5–7 business days, subject to assessment.</p>
        </section>

        <section className="mb-6">
          <h2 className="text-xl font-semibold text-gray-800 mb-2">Partial or Advance Payment Made Outside the Platform</h2>
          <p className="text-gray-600">The Platform bears no responsibility for such payments and no support, refund, or redressal will be offered. Customers should make all payments through official Platform channels for protection under this Policy.</p>
        </section>

        <section className="mb-6">
          <h2 className="text-xl font-semibold text-gray-800 mb-2">Valid Mode of Cancellation and Refund Request</h2>
          <p className="text-gray-600">All requests must be made through the official Home Triangle website or mobile application. Requests via direct communication with the service provider or third-party channels will not be considered valid.</p>
        </section>

        <section className="mb-6">
          <h2 className="text-xl font-semibold text-gray-800 mb-2">General Provisions</h2>
          <ul className="list-disc pl-6 mt-2 text-gray-600">
            <li><strong>Service Provider No-Show:</strong> If a booked service is cancelled by the provider or if they fail to show up, the Customer will be eligible for a full refund, including any advance payments.</li>
            <li><strong>Duplicate or Erroneous Transactions:</strong> In case of duplicate payments or incorrect charges made through the Platform, a full refund will be processed upon validation.</li>
            <li><strong>Refund Processing Timeline:</strong> All approved refunds will be initiated within 5–7 business days. The Platform is not liable for delays caused by the payment gateway or banks.</li>
            <li><strong>Dispute Escalation:</strong> Customers must contact support at info@khushihomesofarepairing.com within 24 hours of the incident or scheduled service time.</li>
          </ul>
        </section>

        <section className="mb-6">
          <h2 className="text-xl font-semibold text-gray-800 mb-2">Governing Law and Jurisdiction</h2>
          <p className="text-gray-600">This Policy is governed by the laws of the Republic of India. Disputes will be subject to the exclusive jurisdiction of the courts of Bengaluru, India.</p>
        </section>

        <section className="mb-6">
          <h2 className="text-xl font-semibold text-gray-800 mb-2">Amendments to the Policy</h2>
          <p className="text-gray-600">The Platform reserves the right to update this Policy at any time without notice. Continued use of the Platform constitutes acceptance of any changes.</p>
        </section>

        <section className="mb-6 border-t pt-4">
          <h3 className="font-semibold text-gray-800">Contact</h3>
          <p className="text-gray-600">Email: <a href="mailto:info@khushihomesofarepairing.com" className="text-orange-600 hover:underline">info@khushihomesofarepairing.com</a></p>
          <p className="text-gray-600">Phone: <a href="tel:+919689861811" className="text-orange-600 hover:underline">+919689861811</a></p>
        </section>

        <footer className="mt-8 text-sm text-gray-500 border-t pt-4">
          <p>2025 Khushi Home Sofa Repair Online Services Pvt Ltd. All rights reserved | CIN U72200KA2015PTC078917.</p>
        </footer>

        <div className="mt-6 text-center">
          <Link to="/" className="text-orange-600 hover:underline">Back to Home</Link>
        </div>
      </div>
    </div>
  );
};

export default PolicyPage;
