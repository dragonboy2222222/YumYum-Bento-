// customer-react/src/pages/faq.jsx

import React, { useState, useEffect } from 'react';
import Navbar from '../components/Navbar';
import Footer from '../components/Footer';
import { fetchHomeData } from '../services/api'; 
// 🎯 NEW: Import the Accordion component from react-bootstrap
import { Accordion } from 'react-bootstrap';
import Chatbot from '../components/chatbot'; // 1. Import the Chatbot component


// --- STATIC FAQ CONTENT ---
const faqContent = [
    {
        question: "What is YumYum Lunchbox?",
        answer: "YumYum Lunchbox is a subscription service that delivers delicious, freshly prepared meals straight to your door. Our mission is to provide convenient and healthy lunch options while supporting local, family-run businesses.",
        id: "faq-1"
    },
    // ... (rest of your faqContent items remain the same)
    {
        question: "How does the subscription work?",
        answer: "You can choose a subscription plan that fits your needs (e.g., weekly, bi-weekly, monthly). We then deliver a curated box of meals to your specified address on a regular schedule. You can manage your subscription, skip a delivery, or cancel anytime from your profile page.",
        id: "faq-2"
    },
    {
        question: "Can I choose my meals?",
        answer: "Our menus are curated by our team and rotate weekly to ensure variety and seasonality. You can view the upcoming menus on our 'Menus' page to see what's being offered in your next box.",
        id: "faq-3"
    },
    {
        question: "How are the lunchboxes packaged?",
        answer: "Our lunchboxes are carefully packaged in insulated containers with ice packs to ensure they remain fresh and at the correct temperature during transit. We also use eco-friendly and sustainable packaging whenever possible.",
        id: "faq-4"
    },
    {
        question: "What if I have allergies or dietary restrictions?",
        answer: "We do our best to accommodate dietary needs. Each meal on our menu page lists its ingredients and any potential allergens. We recommend checking the menu each week and contacting our support team if you have a severe allergy.",
        id: "faq-5"
    },
    {
        question: "Can I cancel my subscription?",
        answer: "Due to the nature of our fresh meal preparation and delivery schedules, we are unable to accommodate subscription cancellations. All sales are final. Please review our available plans carefully before subscribing.",
        id: "faq-6"
    },
    {
        question: "Do you offer any promotions or discounts?",
        answer: "Yes! We regularly offer special promotions and discounts. Please check our homepage for any current offers or subscribe to our newsletter to be the first to know about upcoming deals.",
        id: "faq-7"
    },
];
// --- END STATIC FAQ CONTENT ---


const FAQPage = () => {
    // ... (State and useEffect logic remains the same for fetching navData)
    const [navData, setNavData] = useState({});
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        const loadData = async () => {
            try {
                const data = await fetchHomeData(); 
                setNavData(data.nav); 
            } catch (err) {
                setError(err.message);
            } finally {
                setLoading(false);
            }
        };
        loadData();
    }, []);

    if (loading) return <div className="text-center p-5">Loading FAQs...</div>;
    if (error) return (
        <div className="text-center p-5 text-danger">
            <h1>Error</h1>
            <p>Could not load necessary data for the Navbar: {error}</p>
        </div>
    );
    // ------------------------------------------------------------------

    const renderFAQContent = () => (
        <main className="container my-5">
            <h1 className="text-center mb-5 fw-bold">Frequently Asked Questions</h1>
            
            {/* 🎯 FIX: Using React-Bootstrap Accordion component */}
            <Accordion defaultActiveKey="0">
                {faqContent.map((item, index) => (
                    // 1. Accordion.Item replaces div.accordion-item
                    <Accordion.Item eventKey={String(index)} key={item.id}>
                        
                        {/* 2. Accordion.Header replaces h2.accordion-header and button */}
                        <Accordion.Header>
                            {item.question}
                        </Accordion.Header>
                        
                        {/* 3. Accordion.Body replaces div.accordion-collapse and div.accordion-body */}
                        <Accordion.Body>
                            {item.answer}
                        </Accordion.Body>
                        
                    </Accordion.Item>
                ))}
            </Accordion>
        </main>
    );

    return (
        <>
            <Navbar navData={navData} />
            {renderFAQContent()}
             <Chatbot /> 
            <Footer />
        </>
    );
};

export default FAQPage;