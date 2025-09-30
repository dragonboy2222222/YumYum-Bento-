// customer-react/src/pages/terms.jsx

import React, { useState, useEffect } from 'react';
import Navbar from '../components/Navbar';
import Footer from '../components/Footer';
import { fetchHomeData } from '../services/api'; 
import Chatbot from '../components/chatbot'; // 1. Import the Chatbot component


const TermsPage = () => {
    const [navData, setNavData] = useState({});
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    // Fetch the navigation data to make the Navbar fully functional
    useEffect(() => {
        const loadData = async () => {
            try {
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
        return <div className="text-center p-5">Loading Terms and Conditions...</div>;
    }

    if (error) {
        return (
            <div className="text-center p-5 text-danger">
                <h1>Error</h1>
                <p>Could not load necessary data for the Navbar: {error}</p>
            </div>
        );
    }

    const renderTermsContent = () => (
        <main className="container my-5 py-4">
            <h1 className="text-center mb-5 fw-bold">Terms and Conditions</h1>
            
            {/* 1. Acceptance of Terms */}
            <section className="mb-4">
                <h2 className="fw-bold mb-3" style={{ color: '#993333' }}>1. Acceptance of Terms</h2>
                <p>By using YumYum Bento services, you agree to be bound by these **Terms and Conditions**. If you do not agree, do not use the services.</p>
            </section>

            {/* 2. Subscription and Payments */}
            <section className="mb-4">
                <h2 className="fw-bold mb-3" style={{ color: '#993333' }}>2. Subscription and Payments</h2>
                
                <h5 className="fw-semibold">2.1. Subscription Plans</h5>
                <p>Various meal delivery subscription plans are offered. Fees are recurring based on the chosen plan.</p>
                
                <h5 className="fw-semibold">2.2. No Cancellations or Refunds</h5>
                <p>Orders **cannot be cancelled**, and payments are **final** due to fresh meal preparation.</p>
                
                <h5 className="fw-semibold">2.3. Payment Information</h5>
                <p>Users must provide accurate payment details and authorize charges for subscription fees and taxes.</p>
            </section>

            {/* 3. Delivery Policy */}
            <section className="mb-4">
                <h2 className="fw-bold mb-3" style={{ color: '#993333' }}>3. Delivery Policy</h2>
                
                <h5 className="fw-semibold">3.1. Delivery Schedule</h5>
                <p>Lunchboxes are delivered on scheduled days per your subscription plan. Timing may vary.</p>
                
                <h5 className="fw-semibold">3.2. Missed Delivery</h5>
                <p>If delivery fails due to company issues, one extra day will be added to the subscription period.</p>
                
                <h5 className="fw-semibold">3.3. Customer Responsibility</h5>
                <p>You must ensure your delivery address is accurate and that someone is available to receive the order. The Company is not responsible for unattended or spoiled deliveries.</p>
            </section>

            {/* 4. Termination and Refund Policy */}
            <section className="mb-4">
                <h2 className="fw-bold mb-3" style={{ color: '#993333' }}>4. Termination and Refund Policy</h2>
                
                <h5 className="fw-semibold">4.1. Termination by User</h5>
                <p>Users can choose **not to renew** their subscription; however, there are no refunds for unused portions of an active plan.</p>
                
                <h5 className="fw-semibold">4.2. Termination by Company</h5>
                <p>The Company may terminate or suspend subscriptions at its discretion.</p>
                
                <h5 className="fw-semibold">4.3. Company Inability to Deliver</h5>
                <p>Full or prorated refunds are only offered if the company **cannot continue service** (e.g., permanent closure or ceasing operations in your area).</p>
            </section>

            {/* 5. User Conduct */}
            <section className="mb-4">
                <h2 className="fw-bold mb-3" style={{ color: '#993333' }}>5. User Conduct</h2>
                <p>Use services lawfully and avoid actions that could damage, disable, or impair the website or services. Providing false or misleading information is prohibited.</p>
            </section>

            {/* 6. Limitation of Liability */}
            <section className="mb-4">
                <h2 className="fw-bold mb-3" style={{ color: '#993333' }}>6. Limitation of Liability</h2>
                <p>YumYum Bento is not liable for indirect, incidental, or consequential damages. Total liability is limited to the amount paid for the subscription in the last month.</p>
            </section>

            {/* 7. Intellectual Property */}
            <section className="mb-4">
                <h2 className="fw-bold mb-3" style={{ color: '#993333' }}>7. Intellectual Property</h2>
                <p>All website content (text, graphics, logos, images) is the property of YumYum Bento or its content suppliers. Unauthorized use, reproduction, or distribution is prohibited.</p>
            </section>

            {/* 8. Governing Law */}
            <section className="mb-4">
                <h2 className="fw-bold mb-3" style={{ color: '#993333' }}>8. Governing Law</h2>
                <p>These Terms are governed by the laws of the country in which YumYum Bento operates. Disputes are resolved in the jurisdiction’s courts.</p>
            </section>

            {/* 9. Changes to Terms */}
            <section className="mb-5">
                <h2 className="fw-bold mb-3" style={{ color: '#993333' }}>9. Changes to Terms</h2>
                <p>These Terms may be modified at any time. Continued use after posting changes constitutes **acceptance of the updated terms**.</p>
            </section>

            {/* Contact */}
            <section>
                <h2 className="fw-bold mb-3" style={{ color: '#993333' }}>Contact</h2>
                <p>Questions about these terms can be sent to <a href="mailto:support@lunchboxco.com">support@lunchboxco.com</a></p>
            </section>
        </main>
    );

    return (
        <>
            <Navbar navData={navData} />
            {renderTermsContent()}
             <Chatbot /> 
            <Footer />
        </>
    );
};

export default TermsPage;