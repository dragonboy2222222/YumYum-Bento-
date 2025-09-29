// customer-react/src/components/Navbar.jsx
import React from 'react';
import { Link, NavLink } from 'react-router-dom';
// ❌ REMOVE: import 'bootstrap/js/dist/dropdown'; 
//    (This is the vanilla JS that causes conflicts)

// ✅ NEW: Import components from react-bootstrap
import { Navbar as RBNavbar, Nav, NavDropdown } from 'react-bootstrap';
import { useAuth } from '../context/authContext';
// You might also need this import in your main.jsx if you haven't already:
// import 'bootstrap/dist/css/bootstrap.min.css'; 


const Navbar = ({ navData = {} }) => {
    const { user, isAuthenticated, logout } = useAuth();
    const { cartCount = 0, lunchboxes = [] } = navData;
    
    const defaultProfilePic = 'http://localhost:3000/Webpage/productImage/profilesample.jpg';
    const profileImageUrl = user?.profilePic || defaultProfilePic;

    return (
        // 1. Use RBNavbar for stability and accessibility
        <RBNavbar expand="lg" className="shadow-sm">
            <div className="container-fluid">
                <div className="d-flex w-100 justify-content-between align-items-center">
                    
                    {/* Left side */}
                    <Nav className="me-auto d-flex flex-row align-items-center">
                        {/* Logo Link */}
                        <Link className="navbar-brand me-5" to="/">
                            <img src="/productImage/loogo.png" alt="Logo" width="280" />
                        </Link>
                        
                        {/* 2. Dropdowns using NavDropdown from React-Bootstrap */}
                        
                        {/* LunchBoxes Dropdown */}
                        <NavDropdown title="LunchBoxes" id="lunchboxDropdown" className="d-none d-lg-block me-3">
                            {lunchboxes.map(lb => (
                                <NavDropdown.Item key={lb.id} as={Link} to={`/lunchbox/${lb.id}`}>
                                    {lb.name}
                                </NavDropdown.Item>
                            ))}
                        </NavDropdown>

                        {/* Menus Dropdown */}
                        <NavDropdown title="Menus" id="menuDropdown" className="d-none d-lg-block me-3">
                            {/* Assuming the Menus dropdown also uses the lunchboxes data for demonstration */}
                            {lunchboxes.map(lb => (
                                <NavDropdown.Item key={lb.id} as={Link} to={`/menus/${lb.id}`}>
                                    {lb.name}
                                </NavDropdown.Item>
                            ))}
                        </NavDropdown>

                        {/* Regular NavLinks */}
                        <Nav.Link as={NavLink} to="/aboutus" className="d-none d-lg-block">About Us</Nav.Link>
                        <Nav.Link as={NavLink} to="/reviews" className="d-none d-lg-block">Reviews</Nav.Link>
                        <Nav.Link as={NavLink} to="/faq" className="d-none d-lg-block">FAQ</Nav.Link>
                    </Nav>

                    {/* Right side */}
                    {/* We can't use collapse navbar-collapse with Link/Nav/NavDropdown here easily,
                        so we simplify the right side into a standard Nav */}
                    <Nav className="ms-auto d-flex align-items-center">
                        {/* Cart */}
                        <Nav.Item>
                            <Link className="nav-link position-relative" to="/cart">
                                <i className="fas fa-shopping-cart"></i>
                                {cartCount > 0 && (
                                    <span className="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                        {cartCount}
                                    </span>
                                )}
                            </Link>
                        </Nav.Item>

                        {/* Auth Buttons */}
                        {isAuthenticated ? (
                            <>
                                <Nav.Item className="ms-3">
                                    <Link to="/profile">
                                        <img src={profileImageUrl} alt="Profile" className="profile-img" />
                                    </Link>
                                </Nav.Item>
                                <Nav.Item className="ms-3">
                                    <button onClick={logout} className="btn btn-outline-danger">Logout</button>
                                </Nav.Item>
                            </>
                        ) : (
                            <Nav.Item className="ms-3">
                                <Link to="/login" className="btn btn-outline-primary">Login</Link>
                            </Nav.Item>
                        )}
                    </Nav>

                    {/* This toggler is for mobile view but isn't complete in the original code, leaving it out for now */}
                    {/* <RBNavbar.Toggle aria-controls="responsive-navbar-nav" /> */}

                </div>
            </div>
        </RBNavbar>
    );
};

export default Navbar;