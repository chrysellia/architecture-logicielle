import { 
  Product, 
  ProductFormData, 
  ProductFilters, 
  ProductStatistics,
  PaginatedProductsResponse,
  ApiResponse 
} from '../types/product';

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000';

export const productService = {
  // Get all products with optional filters
  async getProducts(filters?: ProductFilters, page = 1, limit = 20): Promise<PaginatedProductsResponse> {
    const params = new URLSearchParams();
    
    if (page > 1) params.append('page', page.toString());
    if (limit !== 20) params.append('limit', limit.toString());
    
    if (filters) {
      if (filters.query) params.append('query', filters.query);
      if (filters.categoryId) params.append('categoryId', filters.categoryId.toString());
      if (filters.minPrice) params.append('minPrice', filters.minPrice.toString());
      if (filters.maxPrice) params.append('maxPrice', filters.maxPrice.toString());
      if (filters.isActive !== undefined) params.append('isActive', filters.isActive.toString());
      if (filters.inStock !== undefined) params.append('inStock', filters.inStock.toString());
    }

    const response = await fetch(`${API_BASE_URL}/api/products${params.toString() ? `?${params.toString()}` : ''}`);
    return response.json();
  },

  // Get single product by ID
  async getProduct(id: number): Promise<ApiResponse<Product>> {
    const response = await fetch(`${API_BASE_URL}/api/products/${id}`);
    return response.json();
  },

  // Create new product
  async createProduct(productData: ProductFormData): Promise<ApiResponse<Product>> {
    const response = await fetch(`${API_BASE_URL}/api/products`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(productData),
    });
    return response.json();
  },

  // Update existing product
  async updateProduct(id: number, productData: Partial<ProductFormData>): Promise<ApiResponse<Product>> {
    const response = await fetch(`${API_BASE_URL}/api/products/${id}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(productData),
    });
    return response.json();
  },

  // Delete product
  async deleteProduct(id: number): Promise<ApiResponse<void>> {
    const response = await fetch(`${API_BASE_URL}/api/products/${id}`, {
      method: 'DELETE',
    });
    return response.json();
  },

  // Update product stock
  async updateStock(id: number, quantity: number, reason = 'Manual adjustment'): Promise<ApiResponse<Product>> {
    const response = await api.patch(`/api/products/${id}/stock`, { quantity, reason });
    return response.data;
  },

  // Update product price
  async updatePrice(id: number, price: number): Promise<ApiResponse<Product>> {
    const response = await api.patch(`/api/products/${id}/price`, { price });
    return response.data;
  },

  // Duplicate product
  async duplicateProduct(id: number, sku: string, name: string): Promise<ApiResponse<Product>> {
    const response = await api.post(`/api/products/${id}/duplicate`, { sku, name });
    return response.data;
  },

  // Get product statistics
  async getStatistics(): Promise<ApiResponse<ProductStatistics>> {
    const response = await api.get('/api/products/statistics');
    return response.data;
  },

  // Get active products only
  async getActiveProducts(): Promise<Product[]> {
    const response = await api.get('/api/products?isActive=true');
    return response.data.data;
  },

  // Get available products (active and in stock)
  async getAvailableProducts(): Promise<Product[]> {
    const response = await api.get('/api/products?isActive=true&inStock=true');
    return response.data.data;
  },

  // Get low stock products
  async getLowStockProducts(): Promise<Product[]> {
    const response = await api.get('/api/products?lowStock=true');
    return response.data.data;
  },

  // Search products
  async searchProducts(query: string, filters?: ProductFilters): Promise<Product[]> {
    const searchFilters = { ...filters, query };
    const response = await this.getProducts(searchFilters);
    return response.data;
  }
};
