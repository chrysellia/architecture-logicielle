import { useState } from 'react'
import { Plus, Search, Filter, Edit, Trash2, Package } from 'lucide-react'
import { useProducts } from '../../hooks/useData'
import { ProductForm } from '../../components/forms/ProductForm'
import { createProduct, updateProduct, deleteProduct } from '../../hooks/useData'

export function ProductListPage() {
  const [searchTerm, setSearchTerm] = useState('')
  const [showFilters, setShowFilters] = useState(false)
  const [showForm, setShowForm] = useState(false)
  const [editingProduct, setEditingProduct] = useState<any>(null)
  
  const { data: products = [], isLoading: productsLoading, error, refetch } = useProducts()
  const createProductMutation = createProduct()
  const updateProductMutation = updateProduct()
  const deleteProductMutation = deleteProduct()

  const handleCreateProduct = async (formData: any) => {
    try {
      await createProductMutation.mutateAsync(formData)
      setShowForm(false)
    } catch (error) {
      console.error('Error creating product:', error)
    }
  }

  const handleUpdateProduct = async (formData: any) => {
    if (!editingProduct) return
    try {
      await updateProductMutation.mutateAsync({ id: editingProduct.id, data: formData })
      setEditingProduct(null)
      setShowForm(false)
    } catch (error) {
      console.error('Error updating product:', error)
    }
  }

  const handleDeleteProduct = async (productId: number) => {
    if (!confirm('Êtes-vous sûr de vouloir supprimer ce produit ?')) return
    try {
      await deleteProductMutation.mutateAsync(productId)
    } catch (error) {
      console.error('Error deleting product:', error)
    }
  }

  const openEditForm = (product: any) => {
    setEditingProduct(product)
    setShowForm(true)
  }

  const closeForm = () => {
    setShowForm(false)
    setEditingProduct(null)
  }

  const filteredProducts = (Array.isArray(products) ? products : []).filter((product: any) =>
    product.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
    product.sku.toLowerCase().includes(searchTerm.toLowerCase())
  )

  const getStatusColor = (stock: number) => {
    if (stock === 0) return 'bg-red-100 text-red-800'
    if (stock <= 5) return 'bg-yellow-100 text-yellow-800'
    return 'bg-green-100 text-green-800'
  }

  const getStatusText = (stock: number) => {
    if (stock === 0) return 'Rupture'
    if (stock <= 5) return 'Stock faible'
    return 'En stock'
  }

  const isLoading = productsLoading || createProductMutation.isPending || 
                   updateProductMutation.isPending || deleteProductMutation.isPending

  if (productsLoading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
      </div>
    )
  }

  if (error) {
    return (
      <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
        Erreur lors du chargement des produits
      </div>
    )
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-3xl font-bold text-gray-900">Produits</h1>
          <p className="text-gray-600 mt-2">Gérez votre catalogue de produits</p>
        </div>
        <button 
          onClick={() => setShowForm(true)}
          className="btn btn-primary flex items-center space-x-2"
        >
          <Plus className="h-4 w-4" />
          <span>Nouveau produit</span>
        </button>
      </div>

      <div className="bg-white p-4 rounded-lg shadow-sm border">
        <div className="flex items-center space-x-4">
          <div className="flex-1 relative">
            <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
            <input
              type="text"
              placeholder="Rechercher un produit..."
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              className="input pl-10"
            />
          </div>
          <button
            onClick={() => setShowFilters(!showFilters)}
            className="btn btn-outline flex items-center space-x-2"
          >
            <Filter className="h-4 w-4" />
            <span>Filtres</span>
          </button>
        </div>

        {showFilters && (
          <div className="mt-4 pt-4 border-t grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Statut</label>
              <select className="input">
                <option value="">Tous</option>
                <option value="active">En stock</option>
                <option value="low_stock">Stock faible</option>
                <option value="out_of_stock">Rupture</option>
              </select>
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Prix min</label>
              <input type="number" placeholder="0" className="input" />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Prix max</label>
              <input type="number" placeholder="9999" className="input" />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Stock</label>
              <select className="input">
                <option value="">Tous</option>
                <option value="in_stock">En stock</option>
                <option value="low_stock">Stock faible</option>
                <option value="out_of_stock">Rupture</option>
              </select>
            </div>
          </div>
        )}
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
                  Description
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Prix
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Stock
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Statut
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
              {filteredProducts.map((product: any) => (
                <tr key={product.id} className="hover:bg-gray-50">
                  <td className="px-6 py-4 whitespace-nowrap">
                    <div className="flex items-center">
                      <Package className="h-8 w-8 text-gray-400 mr-3" />
                      <div>
                        <div className="text-sm font-medium text-gray-900">{product.name}</div>
                        <div className="text-sm text-gray-500">{product.sku}</div>
                      </div>
                    </div>
                  </td>
                  <td className="px-6 py-4 text-sm text-gray-900 max-w-xs truncate">
                    {product.description}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    €{parseFloat(product.price).toFixed(2)}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {product.stock}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getStatusColor(product.stock)}`}>
                      {getStatusText(product.stock)}
                    </span>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div className="flex items-center space-x-2">
                      <button 
                        onClick={() => openEditForm(product)}
                        className="text-blue-600 hover:text-blue-900"
                        title="Modifier"
                      >
                        <Edit className="h-4 w-4" />
                      </button>
                      <button 
                        onClick={() => handleDeleteProduct(product.id)}
                        className="text-red-600 hover:text-red-900"
                        title="Supprimer"
                      >
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

      {showForm && (
        <ProductForm
          product={editingProduct}
          onSubmit={editingProduct ? handleUpdateProduct : handleCreateProduct}
          onCancel={closeForm}
          isLoading={isLoading}
        />
      )}
    </div>
  )
}
