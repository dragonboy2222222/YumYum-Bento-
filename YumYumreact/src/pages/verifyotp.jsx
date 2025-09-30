// src/pages/VerifyOtpPage.jsx

import React, { useState } from 'react';
import '../styles/verify.css'; // Keep this for now, though it will be minimal
import '../styles/login.css'; // Import login.css to share general styles!
import { apiVerifyOtp } from '../services/authApi';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../context/authContext';

const VerifyOtpPage = () => {
    const [otp, setOtp] = useState('');
    const [error, setError] = useState(null);
    const [loading, setLoading] = useState(false);
    const navigate = useNavigate();
    const { login } = useAuth(); // Assuming useAuth provides a login function

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError(null);
        setLoading(true);

        const result = await apiVerifyOtp(otp);

        if (result.success && result.user) {
            login(result.user); // Store user data globally and locally

            if (result.user.role === 'admin') {
                window.location.href = 'http://localhost:3000/Webpage/admin/dashboard.php';
            } else {
                navigate('/'); // Redirect to React homepage for normal users
            }
        } else {
            setError(result.message || 'Verification failed. Invalid OTP or session expired.');
        }
        setLoading(false);
    };

    return (
        // 🌟 Use login-container and login-box classes 🌟
        <div className="login-container"> 
            <div className="login-box"> 
                <h2>Verify OTP</h2>

                {error && <div className="error">{error}</div>}

                <form onSubmit={handleSubmit}>
                    <label>Enter 6-digit Code:</label>
                    <input
                        type="text"
                        value={otp}
                        onChange={(e) => setOtp(e.target.value)}
                        maxLength="6"
                        required
                        disabled={loading}
                        // 🌟 You might want to add a specific class here if needed, 
                        //    but form input already has shared styles.
                        // className="otp-input" 
                    />
                    <button type="submit" disabled={loading} className='verify-button'>
                        {loading ? 'Verifying...' : 'Verify'}
                    </button>
                </form>

                <p className="note">Check your email for the verification code. Code expires in 5 minutes.</p>
            </div>
        </div>
    );
};

export default VerifyOtpPage;