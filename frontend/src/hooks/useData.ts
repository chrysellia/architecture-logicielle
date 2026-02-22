import { useQuery } from '@tanstack/react-query'
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
    queryFn: () => productService.getProducts(),
    staleTime: 5 * 60 * 1000,
  })
}
