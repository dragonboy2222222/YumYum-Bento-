// import React, { useState, useEffect } from 'react';
// import { Link } from 'react-router-dom';
// import { useAuth } from '../context/authContext';
// import { fetchCartData, updateCartItemQuantity } from '../services/api';

// const CartPage = () => {
//     // State to hold the cart data: { items: [], total: 0 }
//     const [cartData, setCartData] = useState({ items: [], total: 0 });
//     const [loading, setLoading] = useState(true);
//     const [error, setError] = useState(null);
    
//     // Check authentication status (login is required as per cart.php)
//     const { isAuthenticated } = useAuth(); 

//     // Helper to format currency
//     const formatCurrency = (amount) => {
//         return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(amount);
//     };

//     // --- Data Fetching Function ---
//     const loadCart = async () => {
//         setLoading(true);
//         setError(null);
//         try {
//             const data = await fetchCartData();
//             setCartData(data);
//         } catch (err) {
//             setError(err.message);
//         } finally {
//             setLoading(false);
//         }
//     };

//     // Initial load
//     useEffect(() => {
//         if (isAuthenticated) {
//             loadCart();
//         } else {
//             // Handle case where user lands on cart page but is logged out
//             setLoading(false);
//             // You might redirect to login here instead of just setting an error
//             setError("You must be logged in to view your cart."); 
//         }
//     }, [isAuthenticated]); // Re-run if auth status changes

//     // --- Action Handlers (Update, Remove, Clear) ---
//     const handleUpdateQuantity = async (action, index, remove = false) => {
//         setLoading(true);
//         setError(null);
//         try {
//             const data = await updateCartItemQuantity(action, index, remove);
//             setCartData(data);
//         } catch (err) {
//             setError(err.message);
//         } finally {
//             setLoading(false);
//         }
//     };

//     const handleClearCart = async () => {
//         setLoading(true);
//         setError(null);
//         try {
//             const data = await updateCartItemQuantity('clear');
//             setCartData(data);
//         } catch (err) {
//             setError(err.message);
//         } finally {
//             setLoading(false);
//         }
//     };

//     // --- Render Logic ---
//     if (!isAuthenticated) {
//         return (
//             <div className="container py-5">
//                 <p className="alert alert-warning text-center">
//                     {error || "Please log in to view your cart."}
//                 </p>
//                 <div className="text-center">
//                     <Link to="/login" className="btn btn-primary">Go to Login</Link>
//                 </div>
//             </div>
//         );
//     }

//     if (loading) {
//         return <div className="text-center py-5">Loading cart...</div>;
//     }

//     if (error) {
//         return <div className="text-center py-5 text-danger">Error: {error}</div>;
//     }

//     const { items, total } = cartData;

//     return (
//         <div className="container py-5">
//             <h2 className="mb-4">🛒 Your Cart</h2>

//             {items.length > 0 ? (
//                 <>
//                     <table className="table table-bordered bg-white shadow-sm">
//                         <thead className="table-dark">
//                             <tr>
//                                 <th>Item</th>
//                                 <th>Plan</th>
//                                 <th>Unit Price</th>
//                                 <th>Quantity</th>
//                                 <th>Line Total</th>
//                                 <th>Action</th>
//                             </tr>
//                         </thead>
//                         <tbody>
//                             {items.map(item => (
//                                 <tr key={`${item.lunchbox_id}-${item.plan_id}`}>
//                                     <td>
//                                         <img 
//                                             src={item.image} 
//                                             width="80" 
//                                             alt={item.lunchbox_name} 
//                                             className="me-3" 
//                                         />
//                                         <span className="fw-bold">{item.lunchbox_name}</span>
//                                     </td>
//                                     <td>{item.plan_name}</td>
//                                     <td>
//                                         {/* Original Price Strikethrough */}
//                                         {item.basePriceUnit !== item.discountedPriceUnit && (
//                                             <s className="text-muted">{formatCurrency(item.basePriceUnit)}</s>
//                                         )}
//                                         <br />
//                                         {/* Discounted Price */}
//                                         <span className="text-success fw-bold">
//                                             {formatCurrency(item.discountedPriceUnit)}
//                                         </span>
//                                     </td>
//                                     <td>
//                                         <button 
//                                             className="btn btn-sm btn-outline-danger me-2"
//                                             onClick={() => handleUpdateQuantity('minus', item.index)}
//                                             disabled={loading}
//                                         >
//                                             -
//                                         </button>
//                                         {item.quantity}
//                                         <button 
//                                             className="btn btn-sm btn-outline-success ms-2"
//                                             onClick={() => handleUpdateQuantity('plus', item.index)}
//                                             disabled={loading}
//                                         >
//                                             +
//                                         </button>
//                                     </td>
//                                     <td>
//                                         {/* Original Line Total Strikethrough */}
//                                         {item.lineBaseTotal !== item.lineDiscountTotal && (
//                                             <s className="text-muted">{formatCurrency(item.lineBaseTotal)}</s>
//                                         )}
//                                         <br />
//                                         {/* Discounted Line Total */}
//                                         <span className="text-success fw-bold">
//                                             {formatCurrency(item.lineDiscountTotal)}
//                                         </span>
//                                     </td>
//                                     <td>
//                                         <button 
//                                             className="btn btn-sm btn-danger"
//                                             // Pass true to force removal regardless of quantity
//                                             onClick={() => handleUpdateQuantity('minus', item.index, true)}
//                                             disabled={loading}
//                                         >
//                                             Remove
//                                         </button>
//                                     </td>
//                                 </tr>
//                             ))}
//                         </tbody>
//                     </table>

//                     <h4 className="text-end">Total: <span className="text-success">{formatCurrency(total)}</span></h4>

//                     <div className="d-flex justify-content-between mt-4">
//                         <button 
//                             className="btn btn-danger"
//                             onClick={handleClearCart}
//                             disabled={loading}
//                         >
//                             Clear Cart
//                         </button>
//                         <Link to="/menus" className="btn btn-secondary">
//                             Continue Shopping
//                         </Link>
//                         <Link to="/checkout" className="btn btn-primary">
//                             Proceed to Checkout
//                         </Link>
//                     </div>
//                 </>
//             ) : (
//                 <div className="text-center">
//                     <p className="alert alert-info">Your cart is empty.</p>
//                     <Link to="/menus" className="btn btn-secondary">Continue Shopping</Link>
//                 </div>
//             )}
//         </div>
//     );
// };

// export default CartPage;