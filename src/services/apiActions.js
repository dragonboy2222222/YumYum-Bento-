// customer-react/src/services/apiActions.js

// Ensure this path matches the one in api.js
const API_BASE_URL = 'http://localhost:3000/Webpage/api'; 
const CART_ACTIONS_URL = `${API_BASE_URL}/cart_actions.php`;
const CHECKOUT_PROCESS_URL = `${API_BASE_URL}/checkout_process.php`; // New Checkout API

// Utility function to handle POST requests, now accepting an optional URL
const postAction = async (payload, url = CART_ACTIONS_URL) => {
    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(payload),
            credentials: 'include' // Important for PHP sessions/cookies
        });

        const data = await response.json();

        if (response.status === 401) {
            throw new Error("Login required for this action.");
        }
        
        // Handle API errors based on HTTP status or the 'success' flag in the payload
        if (!response.ok || !data.success) {
            // Use the message from the API if available, otherwise a generic error
            throw new Error(data.message || `API Error: ${response.status}`);
        }

        return data; // Returns the success response
        
    } catch (error) {
        console.error("Cart action failed:", error);
        throw error;
    }
};

/**
 * 1. Used by LunchboxPage.jsx when clicking 'Subscribe'
 */
export const addItemToCart = (lunchboxId, planId, image) => {
    return postAction({
        action: 'add',
        lunchbox_id: lunchboxId,
        plan_id: planId,
        image: image,
    });
};

/**
 * 2. Used by CartPage.jsx for + and - buttons
 */
export const updateCartItemQuantity = (index, operation) => {
    const change = operation === 'plus' ? 1 : -1;
    return postAction({
        action: 'update_quantity',
        index: index,
        change: change,
    });
};

/**
 * 3. Used by CartPage.jsx for the 'Remove' button
 */
export const removeItem = (index) => {
    return postAction({
        action: 'remove',
        index: index,
    });
};

/**
 * 4. Used by CartPage.jsx for the 'Clear Cart' button
 */
export const clearCart = () => {
    return postAction({
        action: 'clear',
    });
};

/**
 * 5. Used by CheckoutPage.jsx to finalize the order.
 * Calls the new checkout_process.php API.
 */
export const finalizeCheckout = (method, address) => {
    return postAction({
        method: method, // Payment method
        address: address, // Delivery address
    }, CHECKOUT_PROCESS_URL); // Pass the specific Checkout URL
};