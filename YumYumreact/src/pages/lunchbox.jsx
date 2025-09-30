// customer-react/src/pages/LunchboxPage.jsx

import React, { useState, useEffect } from 'react';
import { useParams, Link, useNavigate } from 'react-router-dom'; // <-- ADDED useNavigate
import { fetchLunchboxData } from '../services/api'; 
import { useAuth } from '../context/authContext'; 
import { addItemToCart } from '../services/apiActions'; // <-- NEW Import

const LunchboxPage = () => {
    // Get the lunchbox_id from the URL (from the route /lunchbox/:id)
    const { id } = useParams();
    // Added isAdding state to manage button disabling during POST request
    const [data, setData] = useState(null); 
    const [loading, setLoading] = useState(true);
    const [isAdding, setIsAdding] = useState(false); // <-- NEW STATE for cart action
    const [error, setError] = useState(null);
    
    // Access the authentication state from the context
    const { isAuthenticated } = useAuth(); 
    const navigate = useNavigate(); // <-- Initialize useNavigate for redirect

    // --- Helper function for currency formatting ---
    const formatCurrency = (amount) => {
        return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(amount);
    };
    
    // --- New Handler for Adding to Cart ---
    const handleSubscribe = async (plan) => {
        if (!isAuthenticated) {
            // Should be caught by the button, but good to check
            return; 
        }

        setIsAdding(true); // Disable all subscribe buttons
        setError(null);    // Clear any previous error
        
        try {
            // Call the POST API action
            await addItemToCart(
                lunchbox.id, 
                plan.id, 
                lunchbox.image // Pass image name to PHP
            );
            
            // On success, navigate to the cart page
            navigate('/cart'); 
            
        } catch (err) {
            setError(err.message || "Failed to add item to cart.");
        } finally {
            setIsAdding(false); // Re-enable buttons
        }
    };


    // --- Data Fetching Effect ---
    useEffect(() => {
        const loadData = async () => {
            if (!id) {
                setError("No lunchbox ID provided in the URL.");
                setLoading(false);
                return;
            }
            try {
                // Fetch data using the ID from the URL
                const fetchedData = await fetchLunchboxData(id);
                setData(fetchedData); 
            } catch (err) {
                setError(err.message);
            } finally {
                setLoading(false);
            }
        };

        loadData();
    }, [id]); 

    // --- Loading and Error States ---
    if (loading) {
        return <div className="text-center py-5">Loading lunchbox details...</div>;
    }

    if (error) {
        return <div className="text-center py-5 text-danger">Error: {error}</div>;
    }
    
    // Check if data is missing *after* loading is complete
    if (!data || !data.lunchbox) {
        return <div className="text-center py-5">No lunchbox found.</div>;
    }
    
    const { lunchbox, plans } = data; // Destructure the fetched data

    return (
        <div className="container my-5">
            {/* Back Button */}
            <div className="d-flex justify-content-end mb-4">
                <Link to="/" className="text-decoration-none" style={{ color: '#993333', fontWeight: '600' }}>
                    ← Back to Menus
                </Link>
            </div>

            {/* Lunchbox Details Card (Styled based on your original PHP CSS) */}
            <div className="card lunchbox-card shadow-sm" style={{ background: 'var(--white)', padding: '25px', borderRadius: '12px', boxShadow: '0 5px 15px rgba(0,0,0,0.1)' }}>
                <div className="row g-0 align-items-center">
                    <div className="col-md-6 text-center p-3">
                        <img 
                            // Note: Adjust the image path if it's different in your React setup
                            src={`http://localhost:3000/Webpage/uploads/${lunchbox.image}`} 
                            alt={lunchbox.name} 
                            className="img-fluid rounded shadow-sm"
                        />
                    </div>
                    <div className="col-md-6 p-4">
                        <h2 className="fw-bold" style={{ color:'#993333' }}>{lunchbox.name}</h2>
                        <p className="text-muted" style={{ whiteSpace: 'pre-wrap' }}>{lunchbox.description}</p>
                        <h4 className="fw-bold mb-4">
                            {formatCurrency(lunchbox.price)}
                            {lunchbox.discount_value > 0 && (
                                <span className="badge bg-success ms-2">
                                    {lunchbox.discount_type === 'percent' 
                                        ? `${lunchbox.discount_value}% OFF` 
                                        : `${formatCurrency(lunchbox.discount_value)} OFF`}
                                </span>
                            )}
                        </h4>
                    </div>
                </div>
            </div>

            {/* Subscription Plans Section */}
            <h3 className="mt-5 text-center fw-bold" style={{ color:'#993333' }}>Choose a Subscription Plan</h3>
            <div className="row mt-4">
                {plans.map(plan => (
                    <div key={plan.id} className="col-md-4 mb-4">
                        <div 
                            className="plan-card h-100 d-flex flex-column justify-content-between"
                            style={{ border: '2px solid #eeeeee', borderRadius: '15px', padding: '25px', textAlign: 'center', background: 'white', boxShadow: '0 5px 12px rgba(0,0,0,0.05)' }}
                        >
                            <div>
                                <h4 style={{ color: '#993333', fontWeight: '700' }}>{plan.name}</h4>
                                <p className="text-muted">{plan.duration_days} Days</p>
                            </div>
                            <div>
                                <h5 className="price-text" style={{ color: '#cc3300', fontWeight: '700', fontSize: '1.5rem' }}>
                                    {formatCurrency(plan.final_price)}
                                </h5>
                                {plan.discount_value > 0 && (
                                    <p className="text-success fw-bold mb-2">
                                        {plan.discount_type === 'percent' 
                                            ? `${plan.discount_value}% OFF (Plan)` 
                                            : `${formatCurrency(plan.discount_value)} OFF (Plan)`}
                                    </p>
                                )}
                            </div>
                            
                            {/* Conditional Subscribe Button based on Auth Context */}
                            {isAuthenticated ? (
                                // LOGGED IN: Changed from Link to Button to call API
                                <button 
                                    type="button"
                                    onClick={() => handleSubscribe(plan)} // <-- NEW HANDLER
                                    className="btn btn-subscribe mt-3" 
                                    style={{ backgroundColor: '#cc3300', color: 'white', border: 'none', padding: '10px 18px', borderRadius: '8px', fontWeight: '600' }}
                                    disabled={isAdding} // <-- DISABLE BUTTON
                                >
                                    {isAdding ? 'Adding...' : 'Subscribe'}
                                </button>
                            ) : (
                                // LOGGED OUT: Button to prompt login
                                <button 
                                    type="button" 
                                    className="btn btn-subscribe mt-3" 
                                    onClick={() => window.location.href = '/login'} 
                                    style={{ backgroundColor: '#cc3300', color: 'white', border: 'none', padding: '10px 18px', borderRadius: '8px', fontWeight: '600' }}
                                    data-bs-toggle="modal" 
                                    data-bs-target="#loginModal" 
                                >
                                    Subscribe (Login Required)
                                </button>
                            )}
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
};

export default LunchboxPage;