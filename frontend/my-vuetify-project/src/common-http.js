const APP_URL = "http://192.168.0.180/awwFood/Backend/public/api/";
const baseURL = `${APP_URL}api`;

// Function to make API requests
const request = async (url, options = {}) => {
    // Set default headers
    const headers = {
        "Content-Type": "application/json",
        ...options.headers,
    };

    // Add Authorization header if accessToken is available
    const token = localStorage.getItem("accessToken");
    if (token) {
        headers["Authorization"] = `Bearer ${token}`;
    }

    // Prepare fetch options
    const fetchOptions = {
        method: options.method || 'GET',
        headers: headers,
        credentials: 'include', // This is equivalent to `withCredentials: true` in Axios
        body: options.body ? JSON.stringify(options.body) : undefined,
    };

    try {
        const response = await fetch(`${baseURL}${url}`, fetchOptions);

        // Check if response is ok
        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || 'Something went wrong');
        }

        // Return JSON response
        return await response.json();
    } catch (error) {
        // Handle errors
        console.error('Fetch error:', error);
        throw error;
    }
};

// Example usage of the `request` function
const getData = async () => {
    try {
        const data = await request('/endpoint');
        console.log('Data:', data);
    } catch (error) {
        console.error('Error fetching data:', error);
    }
};

const postData = async (data) => {
  
