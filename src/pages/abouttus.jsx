// customer-react/src/pages/aboutus.jsx

import React, { useState, useEffect } from 'react';
import Navbar from '../components/Navbar';
import Footer from '../components/Footer';
import Chatbot from '../components/chatbot'; // 1. Import the Chatbot component
import { fetchHomeData } from '../services/api'; 
import { Link } from 'react-router-dom';

const AboutUs = () => {
    // We only need navData for the Navbar, but we fetch all home data
    const [navData, setNavData] = useState({});
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    // This is the core logic that fetches the data for the Navbar
    useEffect(() => {
        const loadData = async () => {
            try {
                // Fetch the data just like the home page does
                const data = await fetchHomeData(); 
                
                // Assuming data.nav contains { lunchboxes: [...], cartCount: 0, ... }
                setNavData(data.nav); 
            } catch (err) {
                // If this fails, the page will show the error, not crash blankly
                setError(err.message);
            } finally {
                setLoading(false);
            }
        };

        loadData();
    }, []);

    // --- RENDERING LOGIC ---
    
    if (loading) {
        return <div className="text-center p-5">Loading About Us page...</div>;
    }

    if (error) {
        return (
            <div className="text-center p-5 text-danger">
                <h1>Error Loading Page</h1>
                <p>Could not fetch navigation data: {error}</p>
            </div>
        );
    }

    // Static content function
    const renderContent = () => (
        <main className="container my-5">
            <h1 className="text-center mb-4">About YumYum Bento</h1>
            <p className="lead text-center">
                At YumYum Bento, we believe food should be fresh, balanced, and convenient. 
                We bring authentic Japanese-inspired bento meals right to your doorstep, 
                crafted daily with care and love.
            </p>
            
            <hr className="my-5" />

            <div className="row">
                <div className="col-md-6">
                    <h2 className="fw-bold">Our Mission</h2>
                    <p>
                        Our mission is simple: to make healthy, delicious, and beautifully packed 
                        bento meals accessible to everyone. Whether you're a busy professional, 
                        a student, or a family on the go, we deliver meals that are nutritious and 
                        satisfying — without compromising on taste.
                    </p>
                    <p>
                        Every bento is prepared with seasonal ingredients, balanced portions, 
                        and a touch of creativity, ensuring you get both variety and quality 
                        in every box.
                    </p>
                </div>
                <div className="col-md-6">
                    <h2 className="fw-bold">Get in Touch</h2>
                    <ul className="list-unstyled">
                        <li>Email: support@yumyumbento.com</li>
                        <li>Phone: +95 9 123 456 789</li>
                        <li>Location: Yangon, Myanmar</li>
                        <li><Link to="/">Back to Home</Link></li>
                    </ul>
                </div>
            </div>
        </main>
    );

    return (
        <>
            {/* Pass the fetched navData prop to the Navbar */}
            <Navbar navData={navData} />
            {renderContent()}
            
            {/* 2. Render the Chatbot component here */}
            <Chatbot /> 

            <Footer />
        </>
    );
};

export default AboutUs;