// customer-react/src/services/api.js
const API_BASE_URL = 'http://localhost:3000/Webpage/api'; // *** CHANGE THIS URL ***

export const fetchHomeData = async () => {
    try {
        // Assuming your PHP API is at http://your-backend-domain/api/home_data.php
        const response = await fetch(`${API_BASE_URL}/home_data.php`); 
        
        if (!response.ok) {
            // Throw an error if the response status is not 2xx
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        if (data.success) {
            return data.data; // Return the 'nav' data object
        } else {
            throw new Error(data.message || 'Failed to fetch home data from API.');
        }

    } catch (error) {
        console.error("Error fetching home data:", error);
        throw error;
    }
};


// Add this new function to your existing api.js file
export const fetchMenusData = async (lunchboxId) => {
    try {
        const url = lunchboxId ? 
            `${API_BASE_URL}/menu_data.php?lunchbox_id=${lunchboxId}` : 
            `${API_BASE_URL}/menu_data.php`;
            
        const response = await fetch(url);

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        if (data.success) {
            return data.data;
        } else {
            throw new Error(data.message || 'Failed to fetch menu data from API.');
        }

    } catch (error) {
        console.error("Error fetching menu data:", error);
        throw error;
    }
};

export const fetchLunchboxData = async (lunchboxId) => {
    // You must pass an ID now, otherwise the API defaults to ID 4
    if (!lunchboxId) {
        throw new Error("Lunchbox ID is required.");
    }

    try {
        const url = `${API_BASE_URL}/lunchbox_data.php?id=${lunchboxId}`;
            
        const response = await fetch(url);

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        if (data.success) {
            return data.data; // Returns { lunchbox, plans, isLoggedIn }
        } else {
            throw new Error(data.message || 'Failed to fetch lunchbox data from API.');
        }

    } catch (error) {
        console.error("Error fetching lunchbox data:", error);
        throw error;
    }
};

// customer-react/src/services/api.js (Add this to the end of the file)

export const fetchCartData = async () => {
    // Note: This API call needs to send session cookies. Ensure your fetch 
    // uses credentials if you are using sessions.
    
    try {
        const url = `${API_BASE_URL}/cart_data.php`; 
        
        const response = await fetch(url, {
            // Include credentials to send cookies for PHP session
            credentials: 'include' 
        });

        if (response.status === 401) {
             throw new Error("Login required to view cart.");
        }
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        if (data.success) {
            // Returns { items: [], total: 0.00 }
            return data.data; 
        } else {
            throw new Error(data.message || 'Failed to fetch cart data from API.');
        }

    } catch (error) {
        console.error("Error fetching cart data:", error);
        throw error;
    }
};
