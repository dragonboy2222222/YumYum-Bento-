// customer-react/src/pages/aboutus.jsx

import React, { useState, useEffect } from 'react';
import Navbar from '../components/Navbar';
import Footer from '../components/Footer';
// 🎯 Import the same API function used by HomePage to get the nav data
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
            <h1 className="text-center mb-4">Welcome to Our Simple About Us Page</h1>
            <p className="lead text-center">
                This is a placeholder page created to ensure your **Navbar dropdowns** are working across all routes.
            </p>
            
            <hr className="my-5" />

            <div className="row">
                <div className="col-md-6">
                    <h2 className="fw-bold">Our Simple Mission</h2>
                    <p>
                        To be the best at what we do, which is delivering a functional, bug-free web application experience. We focus on clean code and robust state management.
                    </p>
                    <p>
                        Since this page is static, no custom CSS is loaded. If the dropdown works here, it confirms the issue on the other page was CSS or data structure, not the React-Bootstrap component itself.
                    </p>
                </div>
                <div className="col-md-6">
                    <h2 className="fw-bold">Contact Us</h2>
                    <ul className="list-unstyled">
                        <li>Email: support@example.com</li>
                        <li>Phone: (123) 456-7890</li>
                        <li><Link to="/">Go back home</Link></li>
                    </ul>
                </div>
            </div>
        </main>
    );

    return (
        <>
            {/* 🎯 Pass the fetched navData prop to the Navbar */}
            <Navbar navData={navData} />
            {renderContent()}
            <Footer />
        </>
    );
};

export default AboutUs;