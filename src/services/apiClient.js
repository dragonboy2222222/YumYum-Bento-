// apiClient.js
import axios from 'axios';

const apiClient = axios.create({
  baseURL: 'http://localhost:3000/Webpage/api',
  headers: {
    'Content-Type': 'application/json', // This will be overridden for file uploads
  },
  withCredentials: true,
});

// ... (existing functions like apiLogin, apiVerifyOtp) ...

// New function to handle both GET and POST for the profile
export const apiProfile = {
  // Fetch a user's profile and subscriptions
  get: async () => {
    try {
      const response = await apiClient.get('/profile_data.php');
      return response.data;
    } catch (error) {
      console.error("Fetch profile API error:", error);
      return error.response ? error.response.data : { success: false, message: 'Network or server error.' };
    }
  },

  // Create or update a user's profile
  save: async (profileData) => {
    try {
      const response = await apiClient.post('/profile_data.php', profileData, {
        headers: {
          // Axios sets this automatically for FormData
          'Content-Type': 'multipart/form-data', 
        },
      });
      return response.data;
    } catch (error) {
      console.error("Save profile API error:", error);
      return error.response ? error.response.data : { success: false, message: 'Network or server error.' };
    }
  },
};