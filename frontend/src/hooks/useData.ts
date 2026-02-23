import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import { dataService, DashboardStats, Order, Customer, Invoice, StockMovement } from '../services/dataService'
import { productService } from '../services/productService'

export const useDashboardStats = () => {
  return useQuery<DashboardStats>({
    queryKey: ['dashboard-stats'],
    queryFn: dataService.getDashboardStats,
    staleTime: 5 * 60 * 1000, // 5 minutes
  })
}

export const useOrders = () => {
  return useQuery<Order[]>({
    queryKey: ['orders'],
    queryFn: dataService.getOrders,
    staleTime: 5 * 60 * 1000,
  })
}

export const useOrder = (id: number) => {
  return useQuery<Order>({
    queryKey: ['order', id],
    queryFn: () => dataService.getOrder(id),
    enabled: !!id,
  })
}

export const useCustomers = () => {
  return useQuery<Customer[]>({
    queryKey: ['customers'],
    queryFn: dataService.getCustomers,
    staleTime: 5 * 60 * 1000,
  })
}

export const useCustomer = (id: number) => {
  return useQuery<Customer>({
    queryKey: ['customer', id],
    queryFn: () => dataService.getCustomer(id),
    enabled: !!id,
  })
}

export const useInvoices = () => {
  return useQuery<Invoice[]>({
    queryKey: ['invoices'],
    queryFn: dataService.getInvoices,
    staleTime: 5 * 60 * 1000,
  })
}

export const useInvoice = (id: number) => {
  return useQuery<Invoice>({
    queryKey: ['invoice', id],
    queryFn: () => dataService.getInvoice(id),
    enabled: !!id,
  })
}

export const useStockMovements = () => {
  return useQuery<StockMovement[]>({
    queryKey: ['stock-movements'],
    queryFn: dataService.getStockMovements,
    staleTime: 5 * 60 * 1000,
  })
}

export const useStockMovement = (id: number) => {
  return useQuery<StockMovement>({
    queryKey: ['stock-movement', id],
    queryFn: () => dataService.getStockMovement(id),
    enabled: !!id,
  })
}

export const useProducts = () => {
  return useQuery({
    queryKey: ['products'],
    queryFn: async () => {
      const response = await productService.getProducts()
      return response.data || response // Handle both paginated and direct array responses
    },
    staleTime: 5 * 60 * 1000,
  })
}

// CRUD mutations for products
export const createProduct = () => {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: (productData: any) => productService.createProduct(productData),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['products'] })
    },
  })
}

export const updateProduct = () => {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: ({ id, data }: { id: number; data: any }) => 
      productService.updateProduct(id, data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['products'] })
    },
  })
}

export const deleteProduct = () => {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: (id: number) => productService.deleteProduct(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['products'] })
    },
  })
}

// CRUD mutations for customers
export const createCustomer = () => {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: (customerData: any) => dataService.createCustomer(customerData),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['customers'] })
    },
  })
}

export const updateCustomer = () => {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: ({ id, data }: { id: number; data: any }) => 
      dataService.updateCustomer(id, data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['customers'] })
    },
  })
}

// CRUD mutations for stock movements
export const createStockMovement = () => {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: (movementData: any) => dataService.createStockMovement(movementData),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['stock-movements'] })
      queryClient.invalidateQueries({ queryKey: ['products'] }) // Update product stock
    },
  })
}

export const updateStockMovement = () => {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: ({ id, data }: { id: number; data: any }) => 
      dataService.updateStockMovement(id, data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['stock-movements'] })
      queryClient.invalidateQueries({ queryKey: ['products'] }) // Update product stock
    },
  })
}

export const deleteStockMovement = () => {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: (id: number) => dataService.deleteStockMovement(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['stock-movements'] })
      queryClient.invalidateQueries({ queryKey: ['products'] }) // Update product stock
    },
  })
}
