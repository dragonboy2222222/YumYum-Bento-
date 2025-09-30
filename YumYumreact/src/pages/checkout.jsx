// customer-react/src/pages/CheckoutPage.jsx

import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../context/authContext';
// Data fetching from api.js and the action for final checkout
import { fetchCartData } from '../services/api'; 
import { finalizeCheckout } from '../services/apiActions'; 

// Utility: Currency formatter (copied from CartPage.jsx)
const formatCurrency = (amount) => {
    const numericAmount = parseFloat(amount); 
    if (isNaN(numericAmount)) return '$0.00'; 
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(numericAmount);
};

const CheckoutPage = () => {
    const { isAuthenticated, user } = useAuth();
    const navigate = useNavigate();
    
    // State for form fields
    const [formData, setFormData] = useState({
        method: 'credit_card', // Default to first option
        address: user?.address || '', // Try to pre-fill address if available in user context
    });
    
    // State for checkout process
    const [cart, setCart] = useState({ items: [], total: 0 }); 
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [success, setSuccess] = useState(null); // To display success message

    // --- Data Fetching (Reusing cart fetch for summary) ---
    const loadCartData = async () => {
        if (!isAuthenticated) {
            navigate('/login'); 
            return;
        }
        setLoading(true);
        setError(null);
        try {
            const data = await fetchCartData();
            if (data.items.length === 0) {
                // Redirect if cart becomes empty during checkout
                navigate('/cart');
                return;
            }
            setCart(data);
        } catch (err) {
            setError("Could not load cart data. " + err.message);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        if (isAuthenticated) {
            loadCartData();
        } else {
             setLoading(false);
        }
    }, [isAuthenticated]);

    // --- Form Handling ---

    const handleChange = (e) => {
        setFormData({
            ...formData,
            [e.target.name]: e.target.value,
        });
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        
        if (cart.items.length === 0) {
            setError("Your cart is empty. Please add items before checking out.");
            navigate('/cart');
            return;
        }

        setLoading(true);
        setError(null);
        setSuccess(null);

        try {
            // Call the new API function
            const result = await finalizeCheckout(formData.method, formData.address);

            // Checkout successful
            setSuccess(result.message || "Order placed successfully!");
            setCart({ items: [], total: 0 }); // Clear the local cart display
            
            // Optionally redirect to an Order Confirmation/History page
            // navigate(`/order/${result.data.checkout_id}`, { replace: true });

        } catch (err) {
            setError(err.message || 'Failed to complete checkout.');
        } finally {
            setLoading(false);
        }
    };

    // --- Render States ---

    if (loading) {
        return <div className="text-center py-5">Loading order details...</div>;
    }

    if (error) {
        return <div className="text-center py-5 text-danger">Error: {error}</div>;
    }
    
    if (success) {
        return (
            <div className="container my-5 text-center">
                <div className="alert alert-success p-5">
                    <h2>✅ Order Confirmed!</h2>
                    <p className="lead">{success}</p>
                    <p className="mt-4">
                        <button onClick={() => navigate('/profile')} className="btn btn-primary me-2">View Your Orders</button>
                        <button onClick={() => navigate('/')} className="btn btn-secondary">Continue Shopping</button>
                    </p>
                </div>
            </div>
        );
    }
    
    // Fallback if cart is somehow empty but not caught by loadCartData
    if (cart.items.length === 0) {
        return (
            <div className="container my-5 text-center">
                <div className="alert alert-info">
                    <p>Your cart is empty. Redirecting to cart page...</p>
                    <button onClick={() => navigate('/cart')} className="btn btn-primary">Go to Cart</button>
                </div>
            </div>
        );
    }

    // --- Main Render ---

    return (
        <div className="container my-5">
            <h2 className="mb-4">💳 Finalize Your Order</h2>

            <form onSubmit={handleSubmit} className="row g-5">
                
                {/* --- Order Summary (Left Column) --- */}
                <div className="col-md-5 order-md-2">
                    <h4 className="d-flex justify-content-between align-items-center mb-3">
                        <span className="text-primary">Order Summary</span>
                        <span className="badge bg-primary rounded-pill">{cart.items.length}</span>
                    </h4>
                    <ul className="list-group mb-3">
                        {cart.items.map((item, index) => (
                            <li 
                                key={index} 
                                className="list-group-item d-flex justify-content-between lh-sm"
                            >
                                <div>
                                    <h6 className="my-0">{item.name}</h6>
                                    <small className="text-muted">Quantity: {item.quantity}</small>
                                </div>
                                <span className="text-muted">{formatCurrency(item.line_total)}</span>
                            </li>
                        ))}
                        <li className="list-group-item d-flex justify-content-between">
                            <span>Total (USD)</span>
                            <strong className="text-success">{formatCurrency(cart.total)}</strong>
                        </li>
                    </ul>
                </div>

                {/* --- Checkout Details (Right Column) --- */}
                <div className="col-md-7 order-md-1">
                    
                    <h4 className="mb-3">Delivery & Payment</h4>

                    {/* Delivery Address */}
                    <div className="mb-3">
                        <label htmlFor="address" className="form-label">Delivery Address</label>
                        <textarea 
                            className="form-control" 
                            id="address" 
                            name="address" 
                            rows="3" 
                            value={formData.address}
                            onChange={handleChange}
                            required
                        />
                    </div>

                    {/* Payment Method */}
                    <div className="mb-3">
                        <label htmlFor="method" className="form-label">Payment Method</label>
                        <select 
                            id="method"
                            name="method" 
                            className="form-select" 
                            value={formData.method}
                            onChange={handleChange}
                            required
                        >
                            <option value="credit_card">Credit Card</option>
                            <option value="paypal">PayPal</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cash">Cash (Payment on Delivery)</option>
                        </select>
                    </div>
                    
                    <hr className="my-4" />

                    <button 
                        className="btn btn-primary btn-lg w-100" 
                        type="submit"
                        disabled={loading}
                    >
                        {loading ? 'Processing...' : `Confirm & Pay ${formatCurrency(cart.total)}`}
                    </button>
                </div>
            </form>
        </div>
    );
};

export default CheckoutPage;