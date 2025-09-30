// customer-react/src/pages/CartPage.jsx

import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
// Imports for fetching data and performing actions
import { fetchCartData } from '../services/api'; 
import { updateCartItemQuantity, clearCart, removeItem } from '../services/apiActions'; 
import { useAuth } from '../context/authContext';

// Utility: Currency formatter (can be moved to a separate utils file)
const formatCurrency = (amount) => {
    // Ensure amount is treated as a number
    const numericAmount = parseFloat(amount); 
    // Handle cases where amount might be invalid
    if (isNaN(numericAmount)) return '$0.00'; 
    
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(numericAmount);
};

const CartPage = () => {
    const { isAuthenticated } = useAuth(); // Use the isAuthenticated status
    const navigate = useNavigate();
    
    const [cart, setCart] = useState({ items: [], total: 0 }); 
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    // --- Data Fetching ---
    const loadCartData = async () => {
        if (!isAuthenticated) {
            // If not logged in, redirect them immediately
            navigate('/login'); 
            return;
        }
        setLoading(true);
        setError(null);
        try {
            // Uses the fetchCartData service function
            const data = await fetchCartData();
            setCart(data);
        } catch (err) {
            // Error could be from network, or a 401 (Login Required) handled in api.js
            setError(err.message);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        // Only attempt to load if authenticated status is true
        if (isAuthenticated) {
            loadCartData();
        } else if (isAuthenticated === false) {
             // Handle initial state where we confirm lack of auth
             setLoading(false);
             // Redirection already handled in loadCartData if called
        }
    }, [isAuthenticated]); // Rerun if auth status changes

    // --- Action Handlers ---
    
    const handleUpdateQuantity = async (index, action) => {
        setLoading(true); // Show loading while updating
        try {
            // Calls the apiActions function
            await updateCartItemQuantity(index, action); 
            await loadCartData(); // Re-fetch the updated cart
        } catch (err) {
            setError(err.message);
            // Don't set loading to false here, loadCartData will handle it, 
            // but if re-fetch fails, we need to show error.
        } finally {
            // Ensure loading stops even if loadCartData throws an error
            setLoading(false);
        }
    };

    const handleRemoveItem = async (index) => {
        setLoading(true);
        try {
            // Calls the apiActions function
            await removeItem(index); 
            await loadCartData();
        } catch (err) {
            setError(err.message);
        } finally {
            setLoading(false);
        }
    };

    const handleClearCart = async () => {
        setLoading(true);
        try {
            // Calls the apiActions function
            await clearCart(); 
            await loadCartData();
        } catch (err) {
            setError(err.message);
        } finally {
            setLoading(false);
        }
    };
    
    // --- Render States ---

    if (loading) {
        return <div className="text-center py-5">Loading cart...</div>;
    }

    if (error) {
        // Display generic error. Login redirect is handled earlier.
        return <div className="text-center py-5 text-danger">Error: {error}</div>;
    }

    // --- Main Render ---

    return (
        <div className="container my-5">
            <h2 className="mb-4">🛒 Your Cart</h2>

            {cart.items.length === 0 ? (
                <div className="alert alert-info">
                    <p>Your cart is empty.</p>
                    <button onClick={() => navigate('/')} className="btn btn-secondary">Continue Shopping</button>
                </div>
            ) : (
                <>
                    <table className="table table-bordered bg-white shadow-sm">
                        <thead className="table-dark">
                            <tr>
                                <th>Image</th>
                                <th>Item</th>
                                <th>Price (Per Unit)</th>
                                <th>Quantity</th>
                                <th>Line Total</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            {cart.items.map(item => (
                                // Use index for key as lunchbox_id/plan_id might not be fully unique 
                                // if the cart uses the PHP array index as the primary reference.
                                <tr key={item.index}> 
                                    <td>
                                        <img 
                                            // Ensure this path matches the actual image location
                                            src={`http://localhost:3000/Webpage/uploads/${item.image}`} 
                                            alt={item.name} 
                                            width="80"
                                        />
                                    </td>
                                    <td>{item.name}</td>
                                    <td>{formatCurrency(item.price_per_unit)}</td>
                                    <td>
                                        <div className="d-flex align-items-center">
                                            <button 
                                                className="btn btn-sm btn-outline-danger me-2"
                                                onClick={() => handleUpdateQuantity(item.index, 'minus')}
                                                disabled={loading || item.quantity <= 1} // Disable if quantity is 1
                                            >-</button>
                                            {item.quantity}
                                            <button 
                                                className="btn btn-sm btn-outline-success ms-2"
                                                onClick={() => handleUpdateQuantity(item.index, 'plus')}
                                                disabled={loading}
                                            >+</button>
                                        </div>
                                    </td>
                                    <td>
                                        <span className="fw-bold">{formatCurrency(item.line_total)}</span>
                                    </td>
                                    <td>
                                        <button 
                                            className="btn btn-sm btn-danger"
                                            onClick={() => handleRemoveItem(item.index)}
                                            disabled={loading}
                                        >Remove</button>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>

                    <h4 className="text-end">
                        Total: <span className="text-success">{formatCurrency(cart.total)}</span>
                    </h4>

                    <div className="d-flex justify-content-between mt-4">
                        <button onClick={handleClearCart} className="btn btn-danger" disabled={loading}>Clear Cart</button>
                        <button onClick={() => navigate('/')} className="btn btn-secondary" disabled={loading}>Continue Shopping</button>
                        {/* Navigate to checkout page */}
                        <button onClick={() => navigate('/checkout')} className="btn btn-primary" disabled={loading}>Proceed to Checkout</button>
                    </div>
                </>
            )}
        </div>
    );
};

export default CartPage;