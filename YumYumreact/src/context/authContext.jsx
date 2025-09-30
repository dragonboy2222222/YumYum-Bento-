// customer-react/src/context/authContext.jsx

import React, { createContext, useContext, useState } from 'react';
import { apiProfile } from '../services/apiClient'; // Assuming apiClient handles profile GET

const AuthContext = createContext();

export const AuthProvider = ({ children }) => {
    // 1. Initialize state from localStorage
    const [user, setUser] = useState(() => {
        const storedUser = localStorage.getItem('user');
        return storedUser ? JSON.parse(storedUser) : null;
    });

    // Fetches and updates user data, including the profile picture.
    const fetchUserData = async () => {
        const result = await apiProfile.get(); // Assumes this API returns user data including 'id'
        if (result.success && result.user) {
            // Ensure result.user contains the user's ID
            const updatedUser = {
                // Keep existing user data (like tokens, if any)
                ...user, 
                // Overwrite/add new profile data (e.g., name, email, id)
                ...result.user, 
            };
            setUser(updatedUser);
            localStorage.setItem('user', JSON.stringify(updatedUser));
            return updatedUser;
        }
        return user;
    };

    // 2. Ensure user data passed to login includes the ID
    const login = (userData) => {
        // userData must contain the user's ID (e.g., {id: 123, username: 'test'})
        setUser(userData);
        localStorage.setItem('user', JSON.stringify(userData));
    };

    const logout = () => {
        setUser(null);
        localStorage.removeItem('user');
    };

    const value = {
        user,
        // Crucial: Access user.id if needed, but it's contained within 'user'
        isAuthenticated: !!user,
        login,
        logout,
        fetchUserData,
    };

    return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
};

export const useAuth = () => {
    return useContext(AuthContext);
};