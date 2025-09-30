// customer-react/src/components/Footer.jsx
import React from 'react';
import { Link } from 'react-router-dom';

const Footer = () => {
    return (
        <footer className="text-center text-lg-start mt-5" style={{ backgroundColor: '#993333', color: '#fff' }}>
            <div className="container-fluid p-4">
                <div className="row">
                    
                    {/* Company Info */}
                    <div className="col-lg-6 col-md-12 mb-4">
                        <h5 className="text-uppercase">YumYum Bento</h5>
                        <p style={{ color: '#fff' }}>
                            Delivering healthy meals straight to your doorstep. Contact us for more info!
                        </p>
                    </div>

                    {/* Quick Links */}
                    <div className="col-lg-3 col-md-6 mb-4">
                        <h6 className="text-uppercase">Links</h6>
                        <ul className="list-unstyled mb-0">
                            <li><Link to="/terms" className="text-white">Terms and Conditions</Link></li>
                            <li><Link to="/policy" className="text-white">Privacy Policy</Link></li>
                            <li><Link to="/aboutus" className="text-white">About Us</Link></li>
                            <li><Link to="/faq" className="text-white">FAQ</Link></li>
                        </ul>
                    </div>

                    {/* Follow Us */}
                    <div className="col-lg-3 col-md-6 mb-4">
                        <h6 className="text-uppercase">Follow Us</h6>
                        <ul className="list-unstyled mb-0">
                            <li><a href="https://www.facebook.com/yumyumbentos/" className="text-white" target="_blank" rel="noopener noreferrer">Facebook</a></li>
                            <li><a href="https://www.instagram.com/explore/locations/104070141120529/yumyum-bento/recent/" className="text-white" target="_blank" rel="noopener noreferrer">Instagram</a></li>
                            <li><a href="https://twitter.com/yumyumbento/status/781263054356885504" className="text-white" target="_blank" rel="noopener noreferrer">Twitter</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            
            {/* Copyright */}
            <div className="text-center p-3" style={{ backgroundColor: '#922b21' }}>
                &copy; 2025 Lunchbox Co. All rights reserved.
            </div>
        </footer>
    );
};

export default Footer;