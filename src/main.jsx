import React from 'react';
import ReactDOM from 'react-dom/client';
import App from './App';
import { AuthProvider } from './context/authContext';
// 1. Core Bootstrap CSS (You already have this)
import "bootstrap/dist/css/bootstrap.min.css"; 

// 2. Bootstrap JavaScript (Needed for dropdowns, toggles, etc.)
//    This line is CRUCIAL for making interactive elements work!

import './index.css';

ReactDOM.createRoot(document.getElementById('root')).render(
  <React.StrictMode>
    <AuthProvider>
      <App />
    </AuthProvider>
  </React.StrictMode>,
);
