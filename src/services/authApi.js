import axios from 'axios';

// Create an Axios instance with base configuration
const apiClient = axios.create({
  baseURL: 'http://localhost:3000/Webpage/api', // ✅ make sure this matches your PHP API folder
  headers: {
    'Content-Type': 'application/json',
  },
  withCredentials: true, // ✅ keep for PHP session
});

// --- STEP 1: LOGIN
export const apiLogin = async (username, password) => {
  try {
    const response = await apiClient.post('/login_data.php', { username, password });
    return response.data;
  } catch (error) {
    console.error("Login API error:", error);
    return error.response ? error.response.data : { success: false, message: 'Network or server error.' };
  }
};

// --- STEP 2: VERIFY OTP
export const apiVerifyOtp = async (otp) => {
  try {
    const response = await apiClient.post('/verify_otp.php', { otp });
    return response.data;
  } catch (error) {
    console.error("Verify OTP API error:", error);
    return error.response ? error.response.data : { success: false, message: 'Network or server error.' };
  }
};

// --- STEP 3: REGISTER
export const apiRegister = async (username, email, password) => {
  try {
    const response = await apiClient.post('/register.php', { username, email, password });
    return response.data;
  } catch (error) {
    console.error("Registration API error:", error);
    return error.response ? error.response.data : { success: false, message: 'Network or server error.' };
  }
};

// --- STEP 4: LOGOUT (Optional but Recommended)
export const apiLogout = async () => {
    try {
        const response = await apiClient.post('/logout.php');
        return response.data;
    } catch (error) {
        console.error("Logout API error:", error);
        return error.response ? error.response.data : { success: false, message: 'Network or server error.' };
    }
};

export default apiClient; // Export the instance itself for other uses