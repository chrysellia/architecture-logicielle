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
    queryFn: async () => {
      const response = await fetch('http://localhost:8000/api/orders');
      const data = await response.json();
      return data.data;
    },
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
    queryFn: async () => {
      const response = await fetch('http://localhost:8000/api/customers');
      const data = await response.json();
      return data.data;
    },
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
    queryFn: async () => {
      const response = await fetch('http://localhost:8000/api/invoices');
      const data = await response.json();
      return data.data;
    },
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

export const downloadInvoice = () => {
  return useMutation({
    mutationFn: async (id: number) => {
      const response = await fetch(`http://localhost:8000/api/invoices/${id}/download`);
      const data = await response.json();
      return data;
    },
  })
}

export const useStockMovements = () => {
  return useQuery<StockMovement[]>({
    queryKey: ['stock-movements'],
    queryFn: async () => {
      const response = await fetch('http://localhost:8000/api/stock-movements');
      const data = await response.json();
      return data.data;
    },
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
    mutationFn: async (customerData: any) => {
      const response = await fetch('http://localhost:8000/api/customers', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(customerData),
      });
      const data = await response.json();
      return data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['customers'] })
    },
  })
}

export const updateCustomer = () => {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: async ({ id, data }: { id: number; data: any }) => {
      const response = await fetch(`http://localhost:8000/api/customers/${id}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(data),
      });
      const result = await response.json();
      return result;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['customers'] })
    },
  })
}

export const deleteCustomer = () => {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: async (id: number) => {
      const response = await fetch(`http://localhost:8000/api/customers/${id}`, {
        method: 'DELETE',
      });
      const data = await response.json();
      return data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['customers'] })
    },
  })
}

// CRUD mutations for stock movements
export const createStockMovement = () => {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: async (movementData: any) => {
      const response = await fetch('http://localhost:8000/api/stock-movements', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(movementData),
      });
      const data = await response.json();
      return data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['stock-movements'] })
      queryClient.invalidateQueries({ queryKey: ['products'] }) // Update product stock
    },
  })
}

export const updateStockMovement = () => {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: async ({ id, data }: { id: number; data: any }) => {
      const response = await fetch(`http://localhost:8000/api/stock-movements/${id}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(data),
      });
      const result = await response.json();
      return result;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['stock-movements'] })
      queryClient.invalidateQueries({ queryKey: ['products'] }) // Update product stock
    },
  })
}

export const deleteStockMovement = () => {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: async (id: number) => {
      const response = await fetch(`http://localhost:8000/api/stock-movements/${id}`, {
        method: 'DELETE',
      });
      const data = await response.json();
      return data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['stock-movements'] })
      queryClient.invalidateQueries({ queryKey: ['products'] }) // Update product stock
    },
  })
}

// CRUD mutations for orders
export const createOrder = () => {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: async (orderData: any) => {
      const response = await fetch('http://localhost:8000/api/orders', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(orderData),
      });
      const data = await response.json();
      return data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['orders'] })
    },
  })
}

export const updateOrder = () => {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: async ({ id, data }: { id: number; data: any }) => {
      const response = await fetch(`http://localhost:8000/api/orders/${id}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(data),
      });
      const result = await response.json();
      return result;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['orders'] })
    },
  })
}

export const deleteOrder = () => {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: async (id: number) => {
      const response = await fetch(`http://localhost:8000/api/orders/${id}`, {
        method: 'DELETE',
      });
      const data = await response.json();
      return data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['orders'] })
    },
  })
}

// CRUD mutations for invoices
export const createInvoice = () => {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: async (invoiceData: any) => {
      const response = await fetch('http://localhost:8000/api/invoices', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(invoiceData),
      });
      const data = await response.json();
      return data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['invoices'] })
    },
  })
}

export const updateInvoice = () => {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: async ({ id, data }: { id: number; data: any }) => {
      const response = await fetch(`http://localhost:8000/api/invoices/${id}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(data),
      });
      const result = await response.json();
      return result;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['invoices'] })
    },
  })
}

export const deleteInvoice = () => {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: async (id: number) => {
      const response = await fetch(`http://localhost:8000/api/invoices/${id}`, {
        method: 'DELETE',
      });
      const data = await response.json();
      return data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['invoices'] })
    },
  })
}
