import axios, { AxiosInstance, AxiosResponse } from 'axios';

// Create axios instance with default configuration
export const api: AxiosInstance = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000',
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Request interceptor to add auth token
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Response interceptor for error handling
api.interceptors.response.use(
  (response: AxiosResponse) => {
    return response;
  },
  (error) => {
    if (error.response?.status === 401) {
      // Handle unauthorized access
      localStorage.removeItem('auth_token');
      window.location.href = '/login';
    }
    
    if (error.response?.status === 403) {
      // Handle forbidden access
      console.error('Access forbidden');
    }
    
    if (error.response?.status >= 500) {
      // Handle server errors
      console.error('Server error:', error.response.data);
    }
    
    return Promise.reject(error);
  }
);

// Utility functions for common API operations
export const apiUtils = {
  // Handle API errors consistently
  handleError: (error: any) => {
    if (error.response) {
      // The request was made and the server responded with a status code
      // that falls out of the range of 2xx
      return {
        message: error.response.data?.message || 'Server error',
        errors: error.response.data?.errors || {},
        status: error.response.status,
      };
    } else if (error.request) {
      // The request was made but no response was received
      return {
        message: 'Network error. Please check your connection.',
        errors: {},
        status: 0,
      };
    } else {
      // Something happened in setting up the request that triggered an Error
      return {
        message: error.message || 'An unexpected error occurred',
        errors: {},
        status: 0,
      };
    }
  },

  // Build query string from filters object
  buildQueryString: (filters: Record<string, any>): string => {
    const params = new URLSearchParams();
    
    Object.entries(filters).forEach(([key, value]) => {
      if (value !== undefined && value !== null && value !== '') {
        params.append(key, value.toString());
      }
    });
    
    return params.toString();
  },

  // Format API response data
  formatResponse: <T>(response: AxiosResponse<{ success: boolean; data: T; message?: string }>) => {
    return {
      data: response.data.data,
      message: response.data.message,
      success: response.data.success,
    };
  },
};

export default api;
