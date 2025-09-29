import React, { useEffect, useState } from 'react';
import { useAuth } from '../context/authContext';
import { useNavigate } from 'react-router-dom';
import { apiProfile } from '../services/apiClient';
import '../styles/Profile.css';

const ProfilePage = () => {
    // Get the new fetchUserData function from the context
    const { isAuthenticated, user, fetchUserData } = useAuth();
    const navigate = useNavigate();
    const [profileData, setProfileData] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const [isEditing, setIsEditing] = useState(false);
    const [formInput, setFormInput] = useState({
        full_name: '',
        phone: '',
        address: ''
    });
    const [profileImageFile, setProfileImageFile] = useState(null);

    const fetchProfileData = async () => {
        setLoading(true);
        const result = await apiProfile.get();
        if (result.success && result.user) {
            setProfileData(result.user);
            setFormInput({
                full_name: result.user.full_name || '',
                phone: result.user.phone || '',
                address: result.user.address || ''
            });
            if (!result.user.full_name) {
                setIsEditing(true);
            }
        } else {
            setError(result.message);
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
            // ✅ This is the new part: Update the auth context after a successful save
            await fetchUserData();
            await fetchProfileData(); // Re-fetch local component data to show the updated profile
            setIsEditing(false); // Switch back to view mode
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

    if (loading) {
        return <div className="profile-container text-center">Loading profile...</div>;
    }

    return (
        <div className="profile-container">
            <div className="profile-card">
                <div className="profile-header">
                    <h2 className="profile-title">My Profile</h2>
                </div>
                <div className="profile-body">
                    {isEditing ? (
                        // Create/Edit Profile Form
                        <form onSubmit={handleFormSubmit}>
                            <h3 className="section-title">Complete Your Profile</h3>
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
                            {profileData && (
                                <button type="button" className="btn btn-secondary ms-2" onClick={() => setIsEditing(false)}>
                                    Cancel
                                </button>
                            )}
                            {error && <div className="text-danger mt-3">{error}</div>}
                        </form>
                    ) : (
                        // Display Profile View
                        <>
                            <div className="profile-info">
                                <div className="profile-image-container">
                                    <img 
                                        src={profileData?.profile_image ? `http://localhost:3000/Webpage/${profileData.profile_image}` : "https://via.placeholder.com/150"} 
                                        alt="Profile" 
                                        className="profile-image" 
                                    />
                                </div>
                                <div className="profile-details">
                                    <h3 className="details-title">User Information</h3>
                                    <p><strong>Username:</strong> {profileData?.username}</p>
                                    <p><strong>Email:</strong> {profileData?.email}</p>
                                    <p><strong>Full Name:</strong> {profileData?.full_name || 'Not provided'}</p>
                                    <p><strong>Phone:</strong> {profileData?.phone || 'Not provided'}</p>
                                    <p><strong>Address:</strong> {profileData?.address || 'Not provided'}</p>
                                    <button onClick={() => setIsEditing(true)} className="btn btn-warning mt-2">Edit Profile</button>
                                </div>
                            </div>
                            {/* You would also render the subscriptions here */}
                            {profileData?.subscriptions && profileData.subscriptions.length > 0 && (
                                <div className="card card-subscriptions mt-4">
                                    <div className="card-header">My Subscriptions</div>
                                    <ul className="list-group list-group-flush">
                                        {profileData.subscriptions.map((sub, index) => (
                                            <li key={index} className="list-group-item">
                                                {/* Subscription details */}
                                            </li>
                                        ))}
                                    </ul>
                                </div>
                            )}
                            {profileData?.subscriptions && profileData.subscriptions.length === 0 && (
                                <div className="card card-subscriptions mt-4">
                                    <div className="card-header">My Subscriptions</div>
                                    <ul className="list-group list-group-flush">
                                        <li className="list-group-item text-center text-muted">No subscriptions found.</li>
                                    </ul>
                                </div>
                            )}
                        </>
                    )}
                </div>
            </div>
        </div>
    );
};

export default ProfilePage;