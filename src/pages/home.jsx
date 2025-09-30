// customer-react/src/pages/home.jsx
import React, { useState, useEffect } from 'react';
import Navbar from '../components/Navbar';
import Footer from '../components/Footer';
import { fetchHomeData } from '../services/api'; // Import the API function
import { Link } from 'react-router-dom';

const HomePage = () => {
    const [navData, setNavData] = useState({});
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    
    // The target URL for the Classic Lunchbox page (ID 4)
    const LUNCHBOX_CLASSIC_URL = "/lunchbox/4"; 

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

    // Placeholder content for the main sections (since they are static in home.php)
    const renderHeroSection = () => (
        <section className="row align-items-center g-0" style={{backgroundColor: '#f8f4ec'}}>
            <div className="col-md-6 image-container">
                <img src="/productImage/fam.webp" alt="Our Team" className="img-fluid object-fit-cover" />
            </div>
            <div className="col-md-6 p-5 d-flex flex-column justify-content-center">
                <h6 className="text-uppercase fw-bold mb-3" style={{color: '#993333'}}>What Makes Us Different</h6>
                <h2 className="fw-bold mb-4">Our Commitment to Craft & Culture!</h2>
                <p className="mb-4">
                    With a Lunchbox subscription, enjoy delicious meals while supporting small family-run businesses and preserving culinary traditions.
                </p>
                <Link 
                    to={LUNCHBOX_CLASSIC_URL} 
                    className="btn btn-primary btn-lg btn-animated-subscribe"
                >
                    Subscribe Now
                </Link>
            </div>
        </section>
    );
    
    const renderValueSection = () => (
        <section className="value-section">
            <div className="container">
                <h2 className="mb-5 fw-bold" style={{color: '#fff'}}>Our Values: What We Stand For</h2>
                <div className="row g-5">
                    {/* Card 1: Community Focus */}
                    <div className="col-md-4">
                        <div className="value-card">
                            {/* Assuming Font Awesome is linked via CDN or installed */}
                            <i className="fas fa-hand-holding-heart value-icon"></i> 
                            <hr className="value-line" />
                            <h5 className="fw-bold mt-4">Community Focus</h5>
                            <p>We partner with local, family-owned businesses to bring you authentic, high-quality meals while supporting our community.</p>
                        </div>
                    </div>
                    {/* Card 2: Freshness First */}
                    <div className="col-md-4">
                        <div className="value-card">
                            <i className="fas fa-seedling value-icon"></i>
                            <hr className="value-line" />
                            <h5 className="fw-bold mt-4">Freshness First</h5>
                            <p>We are dedicated to using the freshest, seasonal ingredients to ensure every meal is nutritious, delicious, and a delight to eat.</p>
                        </div>
                    </div>
                    {/* Card 3: Culinary Tradition */}
                    <div className="col-md-4">
                        <div className="value-card">
                            <i className="fas fa-utensils value-icon"></i>
                            <hr className="value-line" />
                            <h5 className="fw-bold mt-4">Culinary Tradition</h5>
                            <p>Our meals celebrate time-honored recipes and cultural traditions, offering a unique and flavorful experience with every lunchbox.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );

    const renderHowItWorks = () => (
        <section className="container py-5">
            <h2 className="text-center mb-5">How it works</h2>
            <div className="row text-center g-4">
                {/* Step 1 */}
                <div className="col-md-4">
                    <img src="/productImage/yumyum1.jpg" alt="Subscribe" className="img-fluid mb-3 rounded" />
                    <h5 className="fw-bold">SUBSCRIBE</h5>
                    <p className="text-muted">
                        Choose your preferred lunchbox category and subscription plan (30, 60, or 90 days) with just a few clicks.
                    </p>
                </div>
                {/* Step 2 */}
                <div className="col-md-4">
                    <img src="/productImage/yumyum2.jpg" alt="Receive" className="img-fluid mb-3 rounded" />
                    <h5 className="fw-bold">RECEIVE</h5>
                    <p className="text-muted">
                        Freshly prepared lunchboxes are delivered straight to your doorstep on your selected schedule.
                    </p>
                </div>
                {/* Step 3 */}
                <div className="col-md-4">
                    <img src="/productImage/yumyum3.png" alt="Enjoy" className="img-fluid mb-3 rounded" />
                    <h5 className="fw-bold">ENJOY</h5>
                    <p className="text-muted">
                        Enjoy balanced meals with dynamic menus, and manage your subscriptions easily through your profile.
                    </p>
                </div>
            </div>
        </section>
    );
    
    const renderFirstLunchbox = () => (
        <section className="container py-5">
            <div className="row align-items-center g-4">
                <div className="col-md-6 text-center">
                    <img src="/productImage/yumyum4.png" alt="Lunchbox" className="img-fluid rounded" />
                </div>
                <div className="col-md-6">
                    <h6 className="text-uppercase fw-bold mb-3" style={{color:'#993333'}}>Your First Lunchbox Includes:</h6>
                    <h2 className="fw-bold mb-4">Fresh and Delicious Meals Delivered to You!</h2>
                    <ul className="list-unstyled mb-4">
                        <li className="mb-2">🍱 <strong>Variety of Lunchboxes</strong> – Choose from six different lunchbox categories designed to fit different tastes and lifestyles.</li>
                        <li className="mb-2">🥗 <strong>Fresh Menus</strong> – Each lunchbox comes with dynamic menus that change regularly for a balanced and exciting meal plan.</li>
                        <li className="mb-2">📅 <strong>Flexible Plans</strong> – Select from 30, 60, or 90-day subscription plans to match your schedule and preferences.</li>
                        <li className="mb-2">💳 <strong>Easy Subscription</strong> – Simple checkout with multiple payment options for a smooth ordering experience.</li>
                        <li className="mb-2">🛍️ <strong>Convenient Delivery</strong> – Your lunchbox is delivered straight to your door on your chosen schedule.</li>
                    </ul>
                    <Link 
                        to={LUNCHBOX_CLASSIC_URL} 
                        className="btn btn-primary btn-lg btn-animated-subscribe"
                    >
                        SUBSCRIBE NOW
                    </Link>
                </div>
            </div>
        </section>
    );


    if (loading) return <div>Loading...</div>;
    if (error) return <div>Error: {error}</div>;

    return (
        <>
            <Navbar navData={navData} />
            <main className="container-fluid px-0">
                {renderHeroSection()}
                {renderValueSection()}
                {renderHowItWorks()}
                {renderFirstLunchbox()}
            </main>
            <Footer /> 
        </>
    );
};

export default HomePage;