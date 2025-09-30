// customer-react/src/pages/privacy.jsx

import React, { useState, useEffect } from 'react';
import Navbar from '../components/Navbar';
import Footer from '../components/Footer';
// We use fetchHomeData to ensure the Navbar has its required data (like lunchboxes)
import { fetchHomeData } from '../services/api'; 
import Chatbot from '../components/chatbot'; // 1. Import the Chatbot component


const PrivacyPage = () => {
    const [navData, setNavData] = useState({});
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    // Fetch the navigation data to make the Navbar fully functional
    useEffect(() => {
        const loadData = async () => {
            try {
                // Fetch the same data the home page uses
                const data = await fetchHomeData(); 
                setNavData(data.nav); 
            } catch (err) {
                setError(err.message);
            } finally {
                setLoading(false);
            }
        };
        loadData();
    }, []);


    // --- RENDERING LOGIC ---
    
    if (loading) {
        return <div className="text-center p-5">Loading Policy...</div>;
    }

    if (error) {
        return (
            <div className="text-center p-5 text-danger">
                <h1>Error</h1>
                <p>Could not load necessary data for the Navbar: {error}</p>
            </div>
        );
    }

    const renderPolicyContent = () => (
        <main className="container my-5 py-4">
            <h1 className="text-center mb-5 fw-bold">Privacy Policy – YumYum Bento</h1>
            <p className="lead text-center text-muted mb-4">
                Last updated: September 16, 2025
            </p>

            {/* Information We Collect */}
            <section className="mb-5">
                <h2 className="fw-bold mb-3" style={{ color: '#993333' }}>Information We Collect</h2>
                <p>Personal information provided voluntarily when registering, ordering, or contacting us.</p>
                <ul className="list-group list-group-flush">
                    <li className="list-group-item"><strong>Examples:</strong> name, email address, phone number, shipping address, payment details.</li>
                </ul>
            </section>

            {/* How We Use Your Information */}
            <section className="mb-5">
                <h2 className="fw-bold mb-3" style={{ color: '#993333' }}>How We Use Your Information</h2>
                <p>We use the collected information to:</p>
                <ul className="list-unstyled">
                    <li className="mb-2"><i className="bi bi-check-circle-fill text-success me-2"></i> Process and fulfill orders and subscriptions.</li>
                    <li className="mb-2"><i className="bi bi-check-circle-fill text-success me-2"></i> Communicate about your account, orders, and promotions.</li>
                    <li className="mb-2"><i className="bi bi-check-circle-fill text-success me-2"></i> Improve our website and services.</li>
                    <li className="mb-2"><i className="bi bi-check-circle-fill text-success me-2"></i> Process payments and prevent fraudulent transactions.</li>
                    <li className="mb-2"><i className="bi bi-check-circle-fill text-success me-2"></i> Provide customer support.</li>
                </ul>
            </section>

            {/* Data Protection */}
            <section className="mb-5">
                <h2 className="fw-bold mb-3" style={{ color: '#993333' }}>Data Protection</h2>
                <p>Security measures are implemented to protect personal information.</p>
                <ul className="list-unstyled">
                    <li className="mb-2">Data is stored in secure networks accessible only to authorized personnel.</li>
                    <li className="mb-2">Payment transactions are processed through a secure gateway and are not stored on our servers.</li>
                </ul>
            </section>

            {/* Sharing Your Information */}
            <section className="mb-5">
                <h2 className="fw-bold mb-3" style={{ color: '#993333' }}>Sharing Your Information</h2>
                <p>We do not sell, trade, or transfer personal information without consent.</p>
                <p>Trusted third parties assisting with operations may access information under confidentiality agreements.</p>
            </section>

            {/* Cookies */}
            <section className="mb-5">
                <h2 className="fw-bold mb-3" style={{ color: '#993333' }}>Cookies</h2>
                <p>Cookies are used to enhance user experience. They enable recognition of your browser and remember certain information.</p>
            </section>
            
            {/* Your Consent */}
            <section className="mb-5">
                <h2 className="fw-bold mb-3" style={{ color: '#993333' }}>Your Consent</h2>
                <p>By using the site, you consent to the privacy policy.</p>
            </section>

            {/* Contact */}
            <section>
                <h2 className="fw-bold mb-3" style={{ color: '#993333' }}>Contact</h2>
                <p>For questions about this policy, contact: <a href="mailto:support@lunchboxco.com">support@lunchboxco.com</a></p>
            </section>
        </main>
    );

    return (
        <>
            <Navbar navData={navData} />
            {renderPolicyContent()}
             <Chatbot /> 
            <Footer />
        </>
    );
};

export default PrivacyPage;