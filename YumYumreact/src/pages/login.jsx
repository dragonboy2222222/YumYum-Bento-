import React, { useState } from 'react';
import '../styles/login.css';
// Ensure this path is correct for your project structure
import { apiLogin } from '../services/authApi'; 
import { useNavigate } from 'react-router-dom';
// Assuming useAuth is used to manage global login state (if needed later)
// import { useAuth } from '../context/authContext'; 

const LoginPage = () => {
    // const { login } = useAuth(); // Example if you are using context
    const [username, setUsername] = useState('');
    const [password, setPassword] = useState('');
    const [error, setError] = useState(null);
    const [loading, setLoading] = useState(false);
    const navigate = useNavigate();

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError(null);
        setLoading(true);

        // This function should be defined in '../services/authApi'
        const result = await apiLogin(username, password); 

        if (result.success) {
            // login(result.user); // Example to update context
            if (result.otp_required) {
                navigate('/verifyotp');
            } else {
                navigate('/'); // Navigate to home or dashboard on successful login
            }
        } else {
            setError(result.message || "Login failed. Please check your credentials.");
        }
        setLoading(false);
    };

    return (
        <div className="login-container">
            <div className="login-box">
                <h2>User Login</h2>

                {error && <div className="error">{error}</div>}

                <form onSubmit={handleSubmit}>
                    <label>Username:</label>
                    <input 
                        type="text" 
                        value={username} 
                        onChange={(e) => setUsername(e.target.value)} 
                        required 
                        disabled={loading}
                    />

                    <label>Password:</label>
                    <input 
                        type="password" 
                        value={password} 
                        onChange={(e) => setPassword(e.target.value)} 
                        required 
                        disabled={loading}
                    />

                    <button type="submit" disabled={loading}>
                        {loading ? 'Logging In...' : 'Login'}
                    </button>
                </form>

                <div className="link-container">
                    {/* CORRECTED LINK STRUCTURE for better flex alignment */}
                    <a href="/" className="back-link">
                        &larr; Back
                    </a>
                    
                    <span className="register-link">
                        Don’t have an account? <a href="/register">Register here</a>
                    </span>
                </div>
            </div>
        </div>
    );
};

export default LoginPage;