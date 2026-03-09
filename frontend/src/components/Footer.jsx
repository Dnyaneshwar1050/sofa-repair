// khushi-home-sofa-repair/frontend/src/components/Footer.jsx
import React from 'react';
import { Link } from 'react-router-dom';
import { MapPin, Phone, Mail, Download, Map } from 'lucide-react';
import { useSettings } from '../context/SettingsContext';

const Footer = () => {
    // Official Address and Contact Info
    const ADDRESS1 = "Paradise Heights, Back Gate Swami Narayan Temple, Narhe, Pune, Maharashtra, India 411041";
    const ADDRESS2 = "Office Number 15 Ground Floor, Orchid Plaza In Front Of Fashion College, Narhe, Pune 411041";
    const PHONE1 = "+919689861811";
    // const PHONE2 = "+919689861811";
    const EMAIL = "info@khushihomesofarepairing.com";
    const APP_LINK = "https://play.google.com/store/apps/details?id=com.lsoysapps.khushihomesofarepairing";
    // const MAP_LINK = "https://share.google/2ghJKkyz8KvlajRwD";
    const MAP_LINK = "https://maps.app.goo.gl/3K1f4ShNFCMR84Ge7";
    const { settings } = useSettings();
    const currentYear = new Date().getFullYear();

    return (
        <footer className="bg-gray-900 text-white p-4 pt-16 pb-8 border-t border-orange-500">
            <div className="container grid grid-cols-2 md:grid-cols-4 gap-8">
                {/* Logo and About */}
                <div className="flex flex-col items-center col-span-2 md:col-span-1">
                    <Link to="/" className="inline-block mb-4">
                        <img
                            src={`${import.meta.env.BASE_URL}logo-light.png`}
                            alt="Khushi Home Sofa Repairing Logo"
                            className="h-30 w-auto"
                        />
                    </Link>
                    <p className="mt-4 text-gray-400 text-sm">
                        Restore the comfort and beauty of your furniture with expert repair and upholstery services right in Pune.
                    </p>
                </div>

                {/* Quick Links */}
                <div>
                    <h4 className="text-lg font-bold mb-4 text-orange-500">Quick Links</h4>
                    <ul className="space-y-2">
                        <li><Link to="/" className="text-gray-400 hover:text-white transition-colors">Home</Link></li>
                        <li><Link to="/requests" className="text-gray-400 hover:text-white transition-colors">My Requests</Link></li>
                        <li><Link to="/blog" className="text-gray-400 hover:text-white transition-colors">Blog</Link></li>
                        <li><Link to="/contact" className="text-gray-400 hover:text-white transition-colors">Contact Us</Link></li>
                        <li><Link to="/policy" className="text-gray-400 hover:text-white transition-colors">Refund & Cancellation Policy</Link></li>
                        <li><Link to="/privacy" className="text-gray-400 hover:text-white transition-colors">Privacy Policy</Link></li>
                        <li><Link to="/terms" className="text-gray-400 hover:text-white transition-colors">Terms & Conditions</Link></li>
                    </ul>
                </div>

                {/* Services */}
                <div>
                    <h4 className="text-lg font-bold mb-4 text-orange-500">Our Services</h4>
                    <ul className="space-y-2 text-gray-400 text-sm">
                        <li>Sofa Repair</li>
                        <li>Sofa Reupholstery</li>
                        <li>Sofa Cleaning</li>
                        <li>Sofa Polishing</li>
                    </ul>
                </div>

                {/* Contact Information */}
                <div className="col-span-2 md:col-span-1">
                    <h4 className="text-lg font-bold mb-4 text-orange-500">Contact & Location</h4>
                    <address className="space-y-4 not-italic">
                        <div className="flex items-start gap-3">
                            <MapPin className="w-5 h-5 text-orange-500 mt-1 shrink-0" />
                            <div>
                                <p className="text-gray-400 text-sm mb-3">
                                    {ADDRESS1}
                                </p>
                                <p className="text-gray-400 text-sm">
                                    {ADDRESS2}
                                </p>
                            </div>
                        </div>
                        <div className="flex items-center gap-3">
                            <Map className="w-5 h-5 text-orange-500 shrink-0" />
                            <a
                                href={MAP_LINK}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="text-gray-400 hover:text-white transition-colors text-sm"
                            >
                                View on Map
                            </a>
                        </div>
                        <div className="flex items-center gap-3">
                            <Phone className="w-5 h-5 text-orange-500 shrink-0" />
                            <div className="flex flex-col gap-1">
                                <a href={`tel:${PHONE1.replace(/\s/g, '')}`} className="text-gray-400 hover:text-white transition-colors text-sm">{PHONE1}</a>
                                {/* <a href={`tel:${PHONE2.replace(/\s/g, '')}`} className="text-gray-400 hover:text-white transition-colors text-sm">{PHONE2}</a> */}
                            </div>
                        </div>
                        <div className="flex items-center gap-3">
                            <Mail className="w-5 h-5 text-orange-500 shrink-0" />
                            <a href={`mailto:${EMAIL}`} className="text-gray-400 hover:text-white transition-colors text-sm wrap-break-words">{EMAIL}</a>
                        </div>

                        {/* App Download CTA Button */}
                        <div className="flex items-center gap-3 pt-3">
                            <Download className="w-5 h-5 text-orange-500 shrink-0" />
                            <a
                                href={APP_LINK}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="bg-orange-500 text-black px-3 py-2 rounded-md font-semibold hover:bg-orange-600 transition"
                            >
                                Download App
                            </a>
                        </div>
                    </address>
                </div>
            </div>

            {/* Copyright */}
            <div className="container mt-12 pt-8 border-t border-gray-700 text-center">
                <p className="text-lg font-semibold mb-2">
                    {settings.siteName}
                </p>
                <p className="text-gray-400 text-sm">
                    &copy; {currentYear} {settings.siteName}. All rights reserved.
                </p>
            </div>
        </footer>
    );
};

export default Footer;