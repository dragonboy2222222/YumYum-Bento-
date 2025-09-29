import React, { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { apiRegister } from '../services/authApi'; // Import the registration function
import '../styles/register.css'; // Assuming you have a CSS file for styling

const Register = () => {
    const [formData, setFormData] = useState({
        username: '',
        email: '',
        password: '',
    });
    const [message, setMessage] = useState('');
    const [passwordStrengthMessage, setPasswordStrengthMessage] = useState('');
    const [loading, setLoading] = useState(false);
    const navigate = useNavigate();

    const handleInputChange = (e) => {
        const { name, value } = e.target;
        setFormData({ ...formData, [name]: value });
        if (name === 'password') {
            checkPasswordStrength(value);
        }
    };

    const checkPasswordStrength = (password) => {
        // This regex pattern matches at least 8 characters, one letter, one number, and one special character.
        const strongPasswordPattern = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
        if (!password) {
            setPasswordStrengthMessage('');
        } else if (!strongPasswordPattern.test(password)) {
            setPasswordStrengthMessage('Password must be at least 8 characters, contain one letter, one number, and one special character.');
        } else {
            setPasswordStrengthMessage('Strong password');
        }
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);
        setMessage('');

        const { username, email, password } = formData;

        try {
            const result = await apiRegister(username, email, password);

            if (result.success) {
                setMessage(result.message);
                setTimeout(() => {
                    navigate('/profile');
                }, 1500); // Redirect after a delay for a better user experience
            } else {
                setMessage(result.message);
            }
        } catch (err) {
            console.error("Registration failed:", err);
            setMessage('An error occurred. Please check your network connection.');
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="register-box">
            <h2>Register New User</h2>
            <form onSubmit={handleSubmit}>
                <label>Username:</label>
                <input
                    type="text"
                    name="username"
                    value={formData.username}
                    onChange={handleInputChange}
                    required
                />
                <label>Email:</label>
                <input
                    type="email"
                    name="email"
                    value={formData.email}
                    onChange={handleInputChange}
                    required
                />
                <label>Password:</label>
                <input
                    type="password"
                    name="password"
                    value={formData.password}
                    onChange={handleInputChange}
                    required
                />
                <div id="password-strength-message" className="password-strength">{passwordStrengthMessage}</div>
                <button type="submit" disabled={loading}>
                    {loading ? 'Registering...' : 'Register'}
                </button>
            </form>

            {message && <div className="message">{message}</div>}

            <div className="link-container">
                <div className="back-link">
                    <Link to="/">← Back</Link>
                </div>
                <div className="login-link">
                    Already have an account? <Link to="/login">Login here</Link>
                </div>
            </div>
        </div>
    );
};

export default Register;