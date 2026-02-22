import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { productService } from '../services/productService';
import { Product, ProductFormData, ProductFilters, ProductStatistics } from '../types/product';

// Hook for fetching products with filters
export const useProducts = (filters?: ProductFilters, page = 1, limit = 20) => {
  return useQuery({
    queryKey: ['products', filters, page, limit],
    queryFn: () => productService.getProducts(filters, page, limit),
    staleTime: 5 * 60 * 1000, // 5 minutes
    gcTime: 10 * 60 * 1000, // 10 minutes
  });
};

// Hook for fetching a single product
export const useProduct = (id: number, enabled = true) => {
  return useQuery({
    queryKey: ['product', id],
    queryFn: () => productService.getProduct(id),
    enabled: enabled && !!id,
    staleTime: 5 * 60 * 1000, // 5 minutes
  });
};

// Hook for creating a product
export const useCreateProduct = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (productData: ProductFormData) => productService.createProduct(productData),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['products'] });
      queryClient.invalidateQueries({ queryKey: ['product-statistics'] });
    },
  });
};

// Hook for updating a product
export const useUpdateProduct = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({ id, productData }: { id: number; productData: Partial<ProductFormData> }) =>
      productService.updateProduct(id, productData),
    onSuccess: (data, variables) => {
      queryClient.invalidateQueries({ queryKey: ['products'] });
      queryClient.invalidateQueries({ queryKey: ['product', variables.id] });
      queryClient.invalidateQueries({ queryKey: ['product-statistics'] });
    },
  });
};

// Hook for deleting a product
export const useDeleteProduct = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (id: number) => productService.deleteProduct(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['products'] });
      queryClient.invalidateQueries({ queryKey: ['product-statistics'] });
    },
  });
};

// Hook for updating product stock
export const useUpdateStock = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({ id, quantity, reason }: { id: number; quantity: number; reason?: string }) =>
      productService.updateStock(id, quantity, reason),
    onSuccess: (data, variables) => {
      queryClient.invalidateQueries({ queryKey: ['products'] });
      queryClient.invalidateQueries({ queryKey: ['product', variables.id] });
      queryClient.invalidateQueries({ queryKey: ['product-statistics'] });
    },
  });
};

// Hook for updating product price
export const useUpdatePrice = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({ id, price }: { id: number; price: number }) =>
      productService.updatePrice(id, price),
    onSuccess: (data, variables) => {
      queryClient.invalidateQueries({ queryKey: ['products'] });
      queryClient.invalidateQueries({ queryKey: ['product', variables.id] });
    },
  });
};

// Hook for duplicating a product
export const useDuplicateProduct = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({ id, sku, name }: { id: number; sku: string; name: string }) =>
      productService.duplicateProduct(id, sku, name),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['products'] });
      queryClient.invalidateQueries({ queryKey: ['product-statistics'] });
    },
  });
};

// Hook for getting product statistics
export const useProductStatistics = () => {
  return useQuery({
    queryKey: ['product-statistics'],
    queryFn: () => productService.getStatistics(),
    staleTime: 2 * 60 * 1000, // 2 minutes
  });
};

// Hook for getting active products
export const useActiveProducts = () => {
  return useQuery({
    queryKey: ['active-products'],
    queryFn: () => productService.getActiveProducts(),
    staleTime: 5 * 60 * 1000, // 5 minutes
  });
};

// Hook for getting available products
export const useAvailableProducts = () => {
  return useQuery({
    queryKey: ['available-products'],
    queryFn: () => productService.getAvailableProducts(),
    staleTime: 5 * 60 * 1000, // 5 minutes
  });
};

// Hook for getting low stock products
export const useLowStockProducts = () => {
  return useQuery({
    queryKey: ['low-stock-products'],
    queryFn: () => productService.getLowStockProducts(),
    staleTime: 2 * 60 * 1000, // 2 minutes
  });
};

// Hook for searching products
export const useSearchProducts = (query: string, filters?: ProductFilters) => {
  return useQuery({
    queryKey: ['search-products', query, filters],
    queryFn: () => productService.searchProducts(query, filters),
    enabled: !!query && query.length >= 2,
    staleTime: 5 * 60 * 1000, // 5 minutes
  });
};
