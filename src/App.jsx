import React from 'react';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';

// Import all application pages
import HomePage from './pages/home';
import LoginPage from './pages/login';
import VerifyOtpPage from './pages/verifyotp';
import RegisterPage from './pages/register';
import ProfilePage from './pages/profile';
import MenusPage from './pages/menu';
import LunchboxPage from './pages/lunchbox'; 
import AboutUs from './pages/abouttus';
import FAQPage from './pages/faq';
import PrivacyPage from './pages/privacy';
import TermsPage from './pages/terms';




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

        {/* Menu Routes - Handles specific menu item ID or the main menu page */}
        <Route path="/menus/:id" element={<MenusPage />} /> 
        <Route path="/menu" element={<MenusPage />} /> 
        <Route path="/lunchbox/:id" element={<LunchboxPage />} />

        <Route path="/aboutus" element={<AboutUs />} />

        <Route path="/faq" element={<FAQPage />} />

        <Route path="/policy" element={<PrivacyPage />} />

        <Route path="/terms" element={<TermsPage />} />



      

      
     
      </Routes>
    </Router>
  );
};

export default App;
