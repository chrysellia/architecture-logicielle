import { Package, ShoppingCart, Users, TrendingUp, AlertTriangle } from 'lucide-react'
import { useDashboardStats, useOrders, useCustomers, useProducts } from '../../hooks/useData'

export function DashboardPage() {
  const { data: stats, isLoading: statsLoading, error: statsError } = useDashboardStats()
  const { data: orders, isLoading: ordersLoading } = useOrders()
  const { data: customers, isLoading: customersLoading } = useCustomers()
  const { data: products, isLoading: productsLoading } = useProducts()

  if (statsLoading || ordersLoading || customersLoading || productsLoading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
      </div>
    )
  }

  if (statsError) {
    return (
      <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
        Erreur lors du chargement des données du tableau de bord
      </div>
    )
  }

  // Produits avec stock faible (<= 5 unités)
  const lowStockProducts = (products && Array.isArray(products) ? products : [])?.filter((product: any) => 
    (product.stock !== undefined || product.stockQuantity !== undefined) && 
    (product.stock <= 5 || product.stockQuantity <= 5) && 
    (product.active !== false || product.isActive !== false)
  ).map((product: any) => ({
    name: product.name,
    stock: product.stock || product.stockQuantity
  })) || []

  // Commandes récentes (triées par date, plus récentes en premier)
  const recentOrders = (orders && Array.isArray(orders) ? orders : [])?.sort((a, b) => 
    new Date(b.orderDate).getTime() - new Date(a.orderDate).getTime()
  ).slice(0, 3) || []

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-bold text-gray-900">Tableau de bord</h1>
        <p className="text-gray-600 mt-2">Vue d'ensemble de votre activité commerciale</p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div className="bg-white p-6 rounded-lg shadow-sm border">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-600">Produits</p>
              <p className="text-2xl font-bold text-gray-900">{stats?.products.total || 0}</p>
            </div>
            <Package className="h-8 w-8 text-blue-600" />
          </div>
        </div>

        <div className="bg-white p-6 rounded-lg shadow-sm border">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-600">Commandes</p>
              <p className="text-2xl font-bold text-gray-900">{stats?.orders.total || 0}</p>
            </div>
            <ShoppingCart className="h-8 w-8 text-green-600" />
          </div>
        </div>

        <div className="bg-white p-6 rounded-lg shadow-sm border">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-600">Clients</p>
              <p className="text-2xl font-bold text-gray-900">{stats?.customers.total || 0}</p>
            </div>
            <Users className="h-8 w-8 text-purple-600" />
          </div>
        </div>

        <div className="bg-white p-6 rounded-lg shadow-sm border">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-600">Revenus</p>
              <p className="text-2xl font-bold text-gray-900">
                €{stats?.invoices.totalRevenue?.toFixed(2) || '0.00'}
              </p>
            </div>
            <TrendingUp className="h-8 w-8 text-orange-600" />
          </div>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div className="bg-white p-6 rounded-lg shadow-sm border">
          <h2 className="text-lg font-semibold text-gray-900 mb-4">Stock faible</h2>
          <div className="space-y-3">
            {lowStockProducts.map((product, index) => (
              <div key={index} className="flex items-center justify-between p-3 bg-yellow-50 rounded-lg">
                <div className="flex items-center space-x-3">
                  <AlertTriangle className="h-5 w-5 text-yellow-600" />
                  <span className="font-medium">{product.name}</span>
                </div>
                <span className="text-sm text-gray-600">{product.stock} unités</span>
              </div>
            ))}
          </div>
        </div>

        <div className="bg-white p-6 rounded-lg shadow-sm border">
          <h2 className="text-lg font-semibold text-gray-900 mb-4">Commandes récentes</h2>
          <div className="space-y-3">
            {recentOrders.map((order) => (
              <div key={order.id} className="flex items-center justify-between p-3 border rounded-lg">
                <div>
                  <p className="font-medium">{order.orderNumber}</p>
                  <p className="text-sm text-gray-600">
                    {order.customer.firstName} {order.customer.lastName}
                  </p>
                </div>
                <span className={`text-sm font-medium ${
                  order.status === 'confirmed' ? 'text-green-600' :
                  order.status === 'processing' ? 'text-blue-600' :
                  order.status === 'shipped' ? 'text-purple-600' :
                  'text-gray-600'
                }`}>
                  {order.status === 'confirmed' ? 'Confirmée' :
                   order.status === 'processing' ? 'En cours' :
                   order.status === 'shipped' ? 'Expédiée' :
                   order.status}
                </span>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  )
}
