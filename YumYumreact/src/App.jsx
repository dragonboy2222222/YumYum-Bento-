import React from 'react';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';

// Import all application pages
import HomePage from './pages/home';
import LoginPage from './pages/login';
import VerifyOtpPage from './pages/verifyotp';
import RegisterPage from './pages/register';
import ProfilePage from './pages/profile';
import MenusPage from './pages/menu'; // Assumed to be the main menu list or specific menu
import LunchboxPage from './pages/lunchbox'; // Assumed to be the subscription/detail page for a lunchbox
import AboutUs from './pages/abouttus';
import FAQPage from './pages/faq';
import PrivacyPage from './pages/privacy';
import TermsPage from './pages/terms';
import CartPage from './pages/cartpage';
import CheckoutPage from './pages/checkout';
import ReviewsPage from './pages/reviews';
// IMPORTANT: Use the correct import path for the reusable component




const App = () => {
    return (
        <Router>
            <Routes>
                {/* Home Route */}
                <Route path="/" element={<HomePage />} />

                {/* Authentication Routes */}
                <Route path="/login" element={<LoginPage />} />
                <Route path="/verifyotp" element={<VerifyOtpPage />} />
                <Route path="/register" element={<RegisterPage />} />

                {/* User Profile Route */}
                <Route path="/profile" element={<ProfilePage />} />

                {/* Product/Menu Routes */}
                <Route path="/menu" element={<MenusPage />} /> 
                <Route path="/menus/:id" element={<MenusPage />} /> 
                <Route path="/lunchbox/:id" element={<LunchboxPage />} />
                <Route path="/reviews" element={<ReviewsPage />} />

                {/* Cart & Checkout Routes */}
                <Route path="/cart" element={<CartPage />} />
                <Route path="/checkout" element={<CheckoutPage />} />

                {/* Information Pages */}
                <Route path="/aboutus" element={<AboutUs />} />
                <Route path="/faq" element={<FAQPage />} />
                <Route path="/policy" element={<PrivacyPage />} />
                <Route path="/terms" element={<TermsPage />} />

            </Routes>

            
    
        </Router>
    );
};

export default App;