// customer-react/src/pages/ProfilePage.jsx

import React, { useEffect, useState } from 'react';
import { useAuth } from '../context/authContext';
import { useNavigate } from 'react-router-dom';
import { apiProfile } from '../services/apiClient'; // Assumed to call the updated PHP API
import '../styles/Profile.css'; // Assuming you have corresponding CSS

// Helper function to format the date (Date format: Month Day, Year)
const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const ProfilePage = () => {
    const { isAuthenticated, fetchUserData } = useAuth();
    const navigate = useNavigate();
    
    const [profileData, setProfileData] = useState({ 
        // Initialize with default structure
        username: '',
        email: '',
        full_name: '',
        phone: '',
        address: '',
        profile_image: '',
        subscriptions: [] 
    });
    
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const [isEditing, setIsEditing] = useState(false);
    
    const [formInput, setFormInput] = useState({
        full_name: '',
        phone: '',
        address: ''
    });
    const [profileImageFile, setProfileImageFile] = useState(null);

    // --- Data Fetching ---
    const fetchProfileData = async () => {
        setLoading(true);
        setError('');
        
        const result = await apiProfile.get(); 
        
        if (result.success && result.user) {
            const fullData = {
                ...result.user, 
                subscriptions: result.subscriptions || [] 
            };
            
            setProfileData(fullData);
            
            setFormInput({
                full_name: fullData.full_name || '',
                phone: fullData.phone || '',
                address: fullData.address || ''
            });
            
            if (!fullData.full_name) {
                setIsEditing(true);
            }
        } else {
            setError(result.message || 'Failed to fetch profile data.');
            setIsEditing(true); 
        }
        setLoading(false);
    };

    useEffect(() => {
        if (!isAuthenticated) {
            navigate('/login');
            return;
        }
        fetchProfileData();
    }, [isAuthenticated, navigate]);

    // --- Form Handling ---

    const handleFormSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);
        setError('');

        const formData = new FormData();
        formData.append('full_name', formInput.full_name);
        formData.append('phone', formInput.phone);
        formData.append('address', formInput.address);
        if (profileImageFile) {
            formData.append('profile_image', profileImageFile);
        }

        const result = await apiProfile.save(formData);

        if (result.success) {
            await fetchUserData();
            await fetchProfileData();
            setIsEditing(false);
        } else {
            setError(result.message);
        }
        setLoading(false);
    };

    const handleInputChange = (e) => {
        const { name, value } = e.target;
        setFormInput({ ...formInput, [name]: value });
    };

    const handleImageChange = (e) => {
        setProfileImageFile(e.target.files[0]);
    };

    // --- Render States ---

    if (loading) {
        return <div className="profile-container container text-center py-5">Loading profile...</div>;
    }

    const profileImageUrl = profileData.profile_image 
        ? `http://localhost:3000/Webpage/${profileData.profile_image}` 
        : "https://via.placeholder.com/150";

    const isProfileComplete = !!profileData.full_name;

    // --- Main Render ---

    return (
        <div className="profile-container container my-5">
            
            {/* --- NEW BUTTON ADDED HERE --- */}
            <div className="mb-3">
                <button 
                    onClick={() => navigate('/')} 
                    className="btn btn-secondary" 
                    // You might need to adjust the class name to match your styling 
                    // (e.g., btn-back if you defined it in Profile.css)
                >
                    <i className="fas fa-arrow-left me-2"></i> Back to Home
                </button>
            </div>
            {/* ----------------------------- */}

            <div className="profile-card card shadow">
                <div className="profile-header text-center">
                    <h2 className="profile-title mb-0">My Profile</h2>
                </div>
                
                <div className="profile-body card-body p-4">
                    {error && <div className="alert alert-danger">{error}</div>}

                    {/* --- Profile Creation/Edit Form --- */}
                    {isEditing || !isProfileComplete ? (
                        <form onSubmit={handleFormSubmit}>
                            <h3 className="section-title">
                                {isProfileComplete ? 'Edit Profile' : 'Complete Your Profile'}
                            </h3>
                            {/* Form fields for full_name, phone, address, profile_image */}
                            <div className="form-group mb-3">
                                <label className="form-label">Full Name</label>
                                <input type="text" name="full_name" className="form-control" value={formInput.full_name} onChange={handleInputChange} required />
                            </div>
                            <div className="form-group mb-3">
                                <label className="form-label">Phone</label>
                                <input type="text" name="phone" className="form-control" value={formInput.phone} onChange={handleInputChange} required />
                            </div>
                            <div className="form-group mb-3">
                                <label className="form-label">Address</label>
                                <textarea name="address" className="form-control" rows="3" value={formInput.address} onChange={handleInputChange} required></textarea>
                            </div>
                            <div className="form-group mb-3">
                                <label className="form-label">Profile Image</label>
                                <input type="file" name="profile_image" className="form-control" onChange={handleImageChange} />
                            </div>

                            <button type="submit" className="btn btn-primary" disabled={loading}>
                                {loading ? 'Saving...' : 'Save Profile'}
                            </button>
                            {isProfileComplete && (
                                <button type="button" className="btn btn-secondary ms-2" onClick={() => setIsEditing(false)}>
                                    Cancel
                                </button>
                            )}
                        </form>
                    ) : (
                        // --- Profile Display View ---
                        <>
                            <div className="row align-items-center mb-4">
                                <div className="col-md-4 text-center">
                                    <img 
                                        src={profileImageUrl} 
                                        alt="Profile" 
                                        className="rounded-circle mb-3 profile-image" 
                                    />
                                    <h4 className="fw-bold">{profileData.full_name}</h4>
                                    <button onClick={() => setIsEditing(true)} className="btn btn-warning mt-2">
                                        Edit Profile
                                    </button>
                                </div>
                                <div className="col-md-8 profile-details">
                                    <h3 className="section-title">Contact Information</h3>
                                    <p><strong>Username:</strong> {profileData.username}</p>
                                    <p><strong>Email:</strong> {profileData.email}</p>
                                    <p><strong>Phone:</strong> {profileData.phone}</p>
                                    <p><strong>Address:</strong> {profileData.address}</p>
                                </div>
                            </div>
                            
                            <hr className="my-4" />

                            {/* --- Subscriptions Section --- */}
                            <div className="card card-subscriptions mt-4">
                                <div className="card-header">
                                    My Subscriptions
                                </div>
                                <ul className="list-group list-group-flush">
                                    {profileData.subscriptions.length > 0 ? (
                                        profileData.subscriptions.map((sub, index) => (
                                            <li key={index} className="list-group-item">
                                                <h5 className="fw-bold">{sub.lunchbox_name}</h5>
                                                <p className="mb-1"><strong>Price:</strong> ${sub.lunchbox_price}</p>
                                                <p className="mb-1">
                                                    <strong>Status:</strong> 
                                                    <span className={`badge ${sub.status === 'active' ? 'bg-success' : 'bg-secondary'} ms-2`}>
                                                        {sub.status}
                                                    </span>
                                                </p>
                                                <p className="mb-1"><strong>Subscribed Date:</strong> {formatDate(sub.start_date)}</p>
                                            </li>
                                        ))
                                    ) : (
                                        <li className="list-group-item text-center text-muted">
                                            No subscriptions found.
                                        </li>
                                    )}
                                </ul>
                            </div>
                        </>
                    )}
                    
                    {/* The existing button at the bottom has been removed as the new one covers it. 
                    If you prefer to keep both, uncomment the following div: */}
                    {/*
                    <div className="text-center mt-4">
                        <button onClick={() => navigate('/')} className="btn btn-back">
                            Back to Home
                        </button>
                    </div>
                    */}
                </div>
            </div>
        </div>
    );
};

export default ProfilePage;