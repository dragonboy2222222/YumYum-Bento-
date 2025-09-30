import React, { useState, useEffect } from 'react';
import { useParams } from 'react-router-dom';
import Navbar from '../components/Navbar'; // Re-use the existing Navbar
import Footer from '../components/Footer'; // Re-use the existing Footer
import { fetchMenusData } from '../services/api';
import Chatbot from '../components/chatbot';

const MenusPage = () => {
    // Get the lunchbox_id from the URL, if it exists
    const { id } = useParams();
    const [pageData, setPageData] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        const loadMenus = async () => {
            try {
                // Pass the ID to the API function
                const data = await fetchMenusData(id);
                setPageData(data); // Set all the fetched data (nav and mainContent)
            } catch (err) {
                setError(err.message);
            } finally {
                setLoading(false);
            }
        };

        loadMenus();
    }, [id]); // Re-fetch data whenever the ID changes

    if (loading) {
        return <div>Loading menus...</div>;
    }

    if (error) {
        return <div>Error: {error}</div>;
    }

    if (!pageData || !pageData.mainContent) {
        return <div>No menus found.</div>;
    }
    
    // De-structure data for easier use
    const { nav, mainContent } = pageData;

    return (
        <>
            <Navbar navData={nav} />
            <main className="container py-5">
                <h2 className="text-center mb-5">{mainContent.lunchboxName} Menus</h2>
                <div className="row g-4">
                    {mainContent.menus.length > 0 ? (
                        mainContent.menus.map(menu => (
                            <div key={menu.id} className="col-md-4">
                                <div className="card menu-card h-100 shadow-sm">
                                    <img 
                                        src={`http://localhost:3000/Webpage/uploads/${menu.image}`} 
                                        alt={menu.name} 
                                        className="card-img-top" 
                                    />
                                    <div className="card-body d-flex flex-column">
                                        <h5 className="card-title">{menu.name}</h5>
                                        <p className="card-text text-muted">{menu.description}</p>
                                        <div className="mt-auto text-center">
                                            <a href={`/lunchbox/${menu.lunchbox_id}`} className="btn btn-subscribe">
                                                Subscribe
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        ))
                    ) : (
                        <p className="text-center">No menus found for this lunchbox.</p>
                    )}
                </div>
            </main>
             <Chatbot /> 
            <Footer />
        </>
    );
};

export default MenusPage;