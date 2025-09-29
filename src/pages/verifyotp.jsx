import React, { useState } from 'react';
import '../styles/verify.css'; 
import { apiVerifyOtp } from '../services/authApi';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../context/authContext';

const VerifyOtpPage = () => {
  const [otp, setOtp] = useState('');
  const [error, setError] = useState(null);
  const [loading, setLoading] = useState(false);
  const navigate = useNavigate();
  const { login } = useAuth();

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError(null);
    setLoading(true);

    const result = await apiVerifyOtp(otp);

    if (result.success && result.user) {
      // Store user data globally and locally
      login(result.user);

      // Role-based redirect
      if (result.user.role === 'admin') {
        // Redirect to PHP admin dashboard (XAMPP port)
        window.location.href = 'http://localhost/admin/dashboard.php';
      } else {
        // Redirect to React homepage for normal users
        navigate('/');
      }
    } else {
      setError(result.message || 'Verification failed. Invalid OTP or session expired.');
    }
    setLoading(false);
  };

  return (
    <div className="verify-container">
      <div className="verify-box">
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
          />
          <button type="submit" disabled={loading}>
            {loading ? 'Verifying...' : 'Verify'}
          </button>
        </form>

        <p className="note">Check your email for the verification code. Code expires in 5 minutes.</p>
      </div>
    </div>
  );
};

export default VerifyOtpPage;
