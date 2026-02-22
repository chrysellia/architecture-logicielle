export interface Product {
  id: number;
  name: string;
  description?: string;
  sku: string;
  category: Category;
  price: Money;
  stockQuantity: number;
  minStockLevel: number;
  isActive: boolean;
  isAvailable: boolean;
  isLowStock: boolean;
  createdAt: string;
  updatedAt?: string;
}

export interface Category {
  id: number;
  name: string;
  description?: string;
  slug: string;
  parent?: Category;
  children?: Category[];
  position: number;
  isActive: boolean;
  isRoot: boolean;
  isLeaf: boolean;
  level: number;
  fullPath: string;
  activeProductCount: number;
  totalProductCount: number;
  createdAt: string;
  updatedAt?: string;
}

export interface Money {
  amount: number;
  formattedAmount: string;
  currency: string;
  amountInCents: number;
  display: string;
}

export interface ProductFormData {
  name: string;
  description?: string;
  sku: string;
  categoryId: number;
  price: number;
  stockQuantity: number;
  minStockLevel: number;
  isActive: boolean;
}

export interface ProductFilters {
  query?: string;
  categoryId?: number;
  minPrice?: number;
  maxPrice?: number;
  isActive?: boolean;
  inStock?: boolean;
}

export interface ProductStatistics {
  total: number;
  active: number;
  lowStock: number;
  outOfStock: number;
}

export interface PaginatedProductsResponse {
  success: boolean;
  data: Product[];
  meta: {
    page: number;
    limit: number;
    total: number;
  };
}

export interface ApiResponse<T> {
  success: boolean;
  data?: T;
  error?: string;
  message?: string;
  errors?: Record<string, string>;
}
