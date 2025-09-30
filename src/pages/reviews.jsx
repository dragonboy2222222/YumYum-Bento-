// customer-react/src/pages/ReviewsPage.jsx

import React, { useState, useEffect } from 'react';
// Import Navbar and Footer components
import Navbar from '../components/Navbar'; 
import Footer from '../components/Footer'; 
import { fetchReviews, fetchHomeData } from '../services/api'; // fetchHomeData needed for Navbar
import { submitReview } from '../services/apiActions'; 
import { useAuth } from '../context/authContext';

// Utility for Star Rating Display (remains the same)
const StarRating = ({ rating }) => {
    // Round rating to the nearest half-star
    const roundedRating = Math.round(rating * 2) / 2;
    const fullStars = Math.floor(roundedRating);
    const hasHalfStar = roundedRating % 1 !== 0;
    const emptyStars = 5 - fullStars - (hasHalfStar ? 1 : 0);

    const stars = [];
    // Full stars
    for (let i = 0; i < fullStars; i++) {
        stars.push(<span key={`full-${i}`} className="text-warning">★</span>);
    }
    // Half star
    if (hasHalfStar) {
        stars.push(<span key="half" className="text-warning">½</span>);
    }
    // Empty stars
    for (let i = 0; i < emptyStars; i++) {
        stars.push(<span key={`empty-${i}`} className="text-muted">☆</span>);
    }

    return (
        <div className="d-inline-block">
            {stars} ({rating.toFixed(1)})
        </div>
    );
};

// Component for the Review Submission Form (remains the same)
const ReviewForm = ({ onReviewSubmitted, onFormError }) => {
    const { isAuthenticated } = useAuth();
    const [rating, setRating] = useState(5);
    const [reviewText, setReviewText] = useState('');
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [submissionMessage, setSubmissionMessage] = useState(null);

    const handleSubmit = async (e) => {
        e.preventDefault();
        setSubmissionMessage(null);
        setIsSubmitting(true);
        onFormError(null); // Clear main page error

        if (!isAuthenticated) {
            setSubmissionMessage({ type: 'danger', text: 'You must be logged in to submit a review.' });
            setIsSubmitting(false);
            return;
        }

        if (reviewText.trim().length < 10) {
            setSubmissionMessage({ type: 'warning', text: 'Review must be at least 10 characters long.' });
            setIsSubmitting(false);
            return;
        }

        try {
            await submitReview(rating, reviewText);
            setSubmissionMessage({ type: 'success', text: 'Review submitted successfully! Thank you.' });
            setReviewText(''); // Clear form
            setRating(5);
            onReviewSubmitted(); // Tell parent component to re-fetch reviews
        } catch (error) {
            // Note: The generic postAction utility handles the 401 (Login required) error and throws
            setSubmissionMessage({ type: 'danger', text: error.message || 'Failed to submit review.' });
            onFormError(error.message);
        } finally {
            setIsSubmitting(false);
        }
    };

    if (!isAuthenticated) {
        return (
            <div className="alert alert-info">
                Please log in to share your experience and submit a review.
            </div>
        );
    }

    return (
        <div className="card shadow-sm mb-4">
            <div className="card-header bg-primary text-white">Submit Your Review</div>
            <div className="card-body">
                <form onSubmit={handleSubmit}>
                    <div className="mb-3">
                        <label htmlFor="rating" className="form-label">Rating</label>
                        <select 
                            id="rating" 
                            className="form-select" 
                            value={rating} 
                            onChange={(e) => setRating(parseInt(e.target.value))}
                            disabled={isSubmitting}
                            required
                        >
                            {[5, 4, 3, 2, 1].map(r => (
                                <option key={r} value={r}>{r} Star{r > 1 ? 's' : ''}</option>
                            ))}
                        </select>
                    </div>
                    <div className="mb-3">
                        <label htmlFor="reviewText" className="form-label">Review</label>
                        <textarea
                            id="reviewText"
                            className="form-control"
                            rows="3"
                            value={reviewText}
                            onChange={(e) => setReviewText(e.target.value)}
                            disabled={isSubmitting}
                            required
                            placeholder="Tell us about your experience..."
                        ></textarea>
                    </div>
                    
                    {submissionMessage && (
                        <div className={`alert alert-${submissionMessage.type}`} role="alert">
                            {submissionMessage.text}
                        </div>
                    )}

                    <button type="submit" className="btn btn-success" disabled={isSubmitting}>
                        {isSubmitting ? 'Submitting...' : 'Post Review'}
                    </button>
                </form>
            </div>
        </div>
    );
};


// Main Component
const ReviewsPage = () => {
    const [reviews, setReviews] = useState([]);
    const [navData, setNavData] = useState(null); // State for Navbar data
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    // --- Data Fetching ---
    const loadData = async () => {
        setLoading(true);
        setError(null);
        try {
            // 1. Fetch Navbar data (or you could use a shared Context/Hook if already implemented)
            const homeData = await fetchHomeData();
            setNavData(homeData.nav);

            // 2. Fetch Reviews data
            const reviewData = await fetchReviews();
            setReviews(reviewData);

        } catch (err) {
            setError(err.message);
        } finally {
            setLoading(false);
        }
    };
    
    // Function to only reload reviews (used after submission)
    const loadReviewsOnly = async () => {
        try {
            const reviewData = await fetchReviews();
            setReviews(reviewData);
        } catch (err) {
            setError(err.message);
        }
    };


    useEffect(() => {
        loadData();
    }, []); // Runs once on mount

    const handleReviewSubmission = () => {
        // Re-fetch all reviews to include the new one without re-fetching navbar data
        loadReviewsOnly(); 
    };

    // --- Render States ---

    if (loading) {
        // Wait for both reviews and nav data to load
        return <div className="text-center py-5">Loading page content...</div>;
    }

    if (error) {
        return <div>Error: {error}</div>;
    }

    // Calculate Average Rating
    const totalRating = reviews.reduce((sum, review) => sum + review.rating, 0);
    const averageRating = reviews.length > 0 ? totalRating / reviews.length : 0;


    // --- Main Render ---

    return (
        <>
            {/* 1. Navbar using fetched data */}
            <Navbar navData={navData} />

            {/* 2. Main Content Area */}
            <main className="container my-5">
                <h2 className="mb-4 text-center">🌟 Customer Reviews ({reviews.length})</h2>
                
                <div className="row justify-content-center mb-5">
                    <div className="col-md-8">
                        <div className="card text-center bg-light border-0 shadow-sm p-3">
                            <h4 className="card-title">Overall Rating</h4>
                            <p className="card-text fs-1">
                                {averageRating.toFixed(1)} <small className="fs-6">/ 5</small>
                            </p>
                            <p className="card-text fs-4">
                                <StarRating rating={averageRating} />
                            </p>
                        </div>
                    </div>
                </div>

                <div className="row justify-content-center">
                    <div className="col-lg-8">
                        {/* Review Submission Form */}
                        <ReviewForm 
                            onReviewSubmitted={handleReviewSubmission} 
                            onFormError={setError}
                        />
                        
                        {/* List of Reviews */}
                        {reviews.length === 0 ? (
                            <div className="alert alert-warning text-center">
                                Be the first to leave a review!
                            </div>
                        ) : (
                            <div className="review-list">
                                {reviews.map((review, index) => (
                                    <div key={index} className="card mb-3 shadow-sm">
                                        <div className="card-body">
                                            <div className="d-flex align-items-center mb-2">
                                                <img 
                                                    // Ensure the image URL is correct, using the URL provided by the PHP API
                                                    src={review.profile_image_url}
                                                    alt={review.username}
                                                    className="rounded-circle me-3"
                                                    style={{ width: '50px', height: '50px', objectFit: 'cover' }}
                                                />
                                                <div>
                                                    <h5 className="mb-0">{review.username}</h5>
                                                    <StarRating rating={review.rating} />
                                                </div>
                                            </div>
                                            <p className="card-text">{review.review_text}</p>
                                            <small className="text-muted">
                                                Reviewed on: {new Date(review.created_at).toLocaleDateString()}
                                            </small>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>
                </div>
            </main>
            {/* 3. Footer */}
            <Footer />
        </>
    );
};

export default ReviewsPage;