import React, { createContext, useContext, useState } from 'react';
import { apiProfile } from '../services/apiClient';

const AuthContext = createContext();

export const AuthProvider = ({ children }) => {
    const [user, setUser] = useState(() => {
        const storedUser = localStorage.getItem('user');
        return storedUser ? JSON.parse(storedUser) : null;
    });

    // Fetches and updates user data, including the profile picture.
    const fetchUserData = async () => {
        const result = await apiProfile.get();
        if (result.success && result.user) {
            // Merge the new profile data with the existing user data
            const updatedUser = {
                ...user,
                ...result.user,
            };
            setUser(updatedUser);
            localStorage.setItem('user', JSON.stringify(updatedUser));
            return updatedUser;
        }
        return user;
    };

    const login = (userData) => {
        setUser(userData);
        localStorage.setItem('user', JSON.stringify(userData));
    };

    const logout = () => {
        setUser(null);
        localStorage.removeItem('user');
    };

    const value = {
        user,
        isAuthenticated: !!user,
        login,
        logout,
        // ✅ Add the new function to the context value
        fetchUserData,
    };

    return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
};

export const useAuth = () => {
    return useContext(AuthContext);
};