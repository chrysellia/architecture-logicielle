import { useState } from 'react'
import { Plus, Search, Filter, Edit, Trash2, Package, TrendingUp, TrendingDown, AlertTriangle } from 'lucide-react'
import { useStockMovements, useProducts } from '../../hooks/useData'

export function StockListPage() {
  const [searchTerm, setSearchTerm] = useState('')
  const { data: movements, isLoading: movementsLoading, error: movementsError } = useStockMovements()
  const { data: products, isLoading: productsLoading } = useProducts()

  if (movementsLoading || productsLoading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
      </div>
    )
  }

  if (movementsError) {
    return (
      <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
        Erreur lors du chargement des mouvements de stock
      </div>
    )
  }

  const getTypeColor = (type: string) => {
    switch (type) {
      case 'in':
        return 'bg-green-100 text-green-800'
      case 'out':
        return 'bg-red-100 text-red-800'
      case 'adjustment':
        return 'bg-yellow-100 text-yellow-800'
      default:
        return 'bg-gray-100 text-gray-800'
    }
  }

  const getTypeText = (type: string) => {
    switch (type) {
      case 'in':
        return 'Entrée'
      case 'out':
        return 'Sortie'
      case 'adjustment':
        return 'Ajustement'
      default:
        return type
    }
  }

  const getReasonText = (reason: string) => {
    switch (reason) {
      case 'purchase':
        return 'Achat'
      case 'sale':
        return 'Vente'
      case 'return':
        return 'Retour'
      case 'damage':
        return 'Dégât'
      case 'loss':
        return 'Perte'
      case 'inventory':
        return 'Inventaire'
      default:
        return reason
    }
  }

  const filteredMovements = movements?.filter(movement =>
    movement.product.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
    movement.product.sku.toLowerCase().includes(searchTerm.toLowerCase()) ||
    movement.reference?.toLowerCase().includes(searchTerm.toLowerCase()) ||
    movement.notes?.toLowerCase().includes(searchTerm.toLowerCase())
  ) || []

  // Calculer le stock actuel pour chaque produit
  const currentStock = movements?.reduce((acc, movement) => {
    const productId = movement.product.id
    if (!acc[productId]) {
      acc[productId] = {
        product: movement.product,
        quantity: 0
      }
    }
    
    if (movement.type === 'in') {
      acc[productId].quantity += movement.quantity
    } else if (movement.type === 'out') {
      acc[productId].quantity -= movement.quantity
    } else if (movement.type === 'adjustment') {
      acc[productId].quantity += movement.quantity
    }
    
    return acc
  }, {} as Record<number, { product: any, quantity: number }>) || {}

  const stockArray = Object.values(currentStock)

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-3xl font-bold text-gray-900">Stock</h1>
          <p className="text-gray-600 mt-2">Suivez vos mouvements de stock et niveaux actuels</p>
        </div>
        <button className="btn btn-primary flex items-center space-x-2">
          <Plus className="h-4 w-4" />
          <span>Nouveau mouvement</span>
        </button>
      </div>

      {/* État actuel du stock */}
      <div className="bg-white p-6 rounded-lg shadow-sm border">
        <h2 className="text-lg font-semibold text-gray-900 mb-4">État actuel du stock</h2>
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          {stockArray.map((item) => (
            <div key={item.product.id} className="border rounded-lg p-4">
              <div className="flex items-center justify-between">
                <div>
                  <h3 className="font-medium text-gray-900">{item.product.name}</h3>
                  <p className="text-sm text-gray-500">{item.product.sku}</p>
                </div>
                <Package className="h-8 w-8 text-gray-400" />
              </div>
              <div className="mt-2">
                <div className={`text-2xl font-bold ${
                  item.quantity === 0 ? 'text-red-600' :
                  item.quantity < 5 ? 'text-yellow-600' :
                  'text-green-600'
                }`}>
                  {item.quantity}
                </div>
                <div className="text-sm text-gray-500">unités</div>
              </div>
              {item.quantity === 0 && (
                <div className="mt-2 flex items-center text-red-600">
                  <AlertTriangle className="h-4 w-4 mr-1" />
                  <span className="text-xs">Rupture de stock</span>
                </div>
              )}
              {item.quantity > 0 && item.quantity < 5 && (
                <div className="mt-2 flex items-center text-yellow-600">
                  <AlertTriangle className="h-4 w-4 mr-1" />
                  <span className="text-xs">Stock faible</span>
                </div>
              )}
            </div>
          ))}
        </div>
      </div>

      {/* Mouvements de stock */}
      <div className="bg-white p-4 rounded-lg shadow-sm border">
        <div className="flex items-center space-x-4">
          <div className="flex-1 relative">
            <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
            <input
              type="text"
              placeholder="Rechercher un mouvement..."
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              className="input pl-10"
            />
          </div>
          <button className="btn btn-outline flex items-center space-x-2">
            <Filter className="h-4 w-4" />
            <span>Filtres</span>
          </button>
        </div>
      </div>

      <div className="bg-white rounded-lg shadow-sm border overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full">
            <thead className="bg-gray-50 border-b">
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Produit
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Type
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Quantité
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Motif
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Référence
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Date
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
              {filteredMovements.map((movement) => (
                <tr key={movement.id} className="hover:bg-gray-50">
                  <td className="px-6 py-4 whitespace-nowrap">
                    <div className="flex items-center">
                      <Package className="h-8 w-8 text-gray-400 mr-3" />
                      <div>
                        <div className="text-sm font-medium text-gray-900">{movement.product.name}</div>
                        <div className="text-sm text-gray-500">{movement.product.sku}</div>
                      </div>
                    </div>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <div className="flex items-center">
                      {movement.type === 'in' ? (
                        <TrendingUp className="h-4 w-4 text-green-600 mr-2" />
                      ) : movement.type === 'out' ? (
                        <TrendingDown className="h-4 w-4 text-red-600 mr-2" />
                      ) : (
                        <AlertTriangle className="h-4 w-4 text-yellow-600 mr-2" />
                      )}
                      <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getTypeColor(movement.type)}`}>
                        {getTypeText(movement.type)}
                      </span>
                    </div>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <div className={`text-sm font-medium ${
                      movement.type === 'in' ? 'text-green-600' :
                      movement.type === 'out' ? 'text-red-600' :
                      'text-yellow-600'
                    }`}>
                      {movement.type === 'out' ? '-' : movement.type === 'adjustment' && movement.quantity < 0 ? '-' : '+'}
                      {Math.abs(movement.quantity)}
                    </div>
                    {movement.unitCost && (
                      <div className="text-sm text-gray-500">
                        €{movement.unitCost.toFixed(2)}/u
                      </div>
                    )}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {getReasonText(movement.reason)}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {movement.reference || '-'}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {new Date(movement.movementDate).toLocaleDateString('fr-FR')}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div className="flex items-center space-x-2">
                      <button className="text-blue-600 hover:text-blue-900">
                        <Edit className="h-4 w-4" />
                      </button>
                      <button className="text-red-600 hover:text-red-900">
                        <Trash2 className="h-4 w-4" />
                      </button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  )
}
