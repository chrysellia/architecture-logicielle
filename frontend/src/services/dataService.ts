import { api } from './api'

export interface DashboardStats {
  products: {
    total: number
    active: number
    lowStock: number
    outOfStock: number
  }
  orders: {
    total: number
    pending: number
    confirmed: number
    processing: number
    shipped: number
    delivered: number
  }
  customers: {
    total: number
    newThisMonth: number
  }
  invoices: {
    total: number
    paid: number
    sent: number
    overdue: number
    totalRevenue: number
    totalPaid: number
    outstanding: number
  }
}

export interface Order {
  id: number
  orderNumber: string
  customer: {
    id: number
    firstName: string
    lastName: string
    email: string
    phone: string
  }
  totalAmount: number
  status: string
  orderDate: string
  items: OrderItem[]
}

export interface OrderItem {
  id: number
  product: {
    id: number
    name: string
    sku: string
  }
  quantity: number
  unitPrice: number
  totalPrice: number
}

export interface Customer {
  id: number
  firstName: string
  lastName: string
  email: string
  phone: string
  address: string
  city: string
  postalCode: string
  country: string
  createdAt: string
}

export interface Invoice {
  id: number
  invoiceNumber: string
  order: {
    id: number
    orderNumber: string
  }
  totalAmount: number
  taxAmount: number
  netAmount: number
  status: string
  issueDate: string
  dueDate: string
  paidDate: string | null
}

export interface StockMovement {
  id: number
  product: {
    id: number
    name: string
    sku: string
  }
  type: string
  quantity: number
  reason: string
  unitCost: number | null
  totalCost: number | null
  reference: string
  movementDate: string
  notes: string
}

export const dataService = {
  // Dashboard
  async getDashboardStats(): Promise<DashboardStats> {
    const response = await api.get('/api/dashboard/stats')
    return response.data.data
  },

  // Orders
  async getOrders(): Promise<Order[]> {
    const response = await api.get('/api/orders')
    return response.data.data
  },

  async getOrder(id: number): Promise<Order> {
    const response = await api.get(`/api/orders/${id}`)
    return response.data.data
  },

  // Customers
  async getCustomers(): Promise<Customer[]> {
    const response = await api.get('/api/customers')
    return response.data.data
  },

  async getCustomer(id: number): Promise<Customer> {
    const response = await api.get(`/api/customers/${id}`)
    return response.data.data
  },

  async createCustomer(customerData: any): Promise<any> {
    const response = await api.post('/api/customers', customerData)
    return response.data
  },

  async updateCustomer(id: number, customerData: any): Promise<any> {
    const response = await api.put(`/api/customers/${id}`, customerData)
    return response.data
  },

  async deleteCustomer(id: number): Promise<any> {
    const response = await api.delete(`/api/customers/${id}`)
    return response.data
  },

  async createStockMovement(movementData: any): Promise<any> {
    const response = await api.post('/api/stock-movements', movementData)
    return response.data
  },

  async updateStockMovement(id: number, movementData: any): Promise<any> {
    const response = await api.put(`/api/stock-movements/${id}`, movementData)
    return response.data
  },

  async deleteStockMovement(id: number): Promise<any> {
    const response = await api.delete(`/api/stock-movements/${id}`)
    return response.data
  },

  async createOrder(orderData: any): Promise<any> {
    const response = await api.post('/api/orders', orderData)
    return response.data
  },

  async updateOrder(id: number, orderData: any): Promise<any> {
    const response = await api.put(`/api/orders/${id}`, orderData)
    return response.data
  },

  async deleteOrder(id: number): Promise<any> {
    const response = await api.delete(`/api/orders/${id}`)
    return response.data
  },

  // Invoices
  async getInvoices(): Promise<Invoice[]> {
    const response = await api.get('/api/invoices')
    return response.data.data
  },

  async getInvoice(id: number): Promise<Invoice> {
    const response = await api.get(`/api/invoices/${id}`)
    return response.data.data
  },

  // Stock Movements
  async getStockMovements(): Promise<StockMovement[]> {
    const response = await api.get('/api/stock-movements')
    return response.data.data
  },

  async getStockMovement(id: number): Promise<StockMovement> {
    const response = await api.get(`/api/stock-movements/${id}`)
    return response.data.data
  }
}
