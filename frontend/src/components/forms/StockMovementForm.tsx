import { useState } from 'react'
import { X } from 'lucide-react'

interface StockMovementFormData {
  productId: number
  type: 'in' | 'out' | 'adjustment'
  quantity: number
  reason: 'purchase' | 'sale' | 'return' | 'damage' | 'loss' | 'inventory' | 'manual'
  reference: string
  notes: string
  unitCost?: number
}

interface StockMovementFormProps {
  movement?: any
  products: any[]
  onSubmit: (data: StockMovementFormData) => void
  onCancel: () => void
  isLoading?: boolean
}

export function StockMovementForm({ movement, products, onSubmit, onCancel, isLoading = false }: StockMovementFormProps) {
  const [formData, setFormData] = useState<StockMovementFormData>({
    productId: movement?.product?.id || 0,
    type: movement?.type || 'in',
    quantity: movement?.quantity || 0,
    reason: movement?.reason || 'manual',
    reference: movement?.reference || '',
    notes: movement?.notes || '',
    unitCost: movement?.unitCost || undefined
  })

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault()
    onSubmit(formData)
  }

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement>) => {
    const { name, value, type } = e.target
    setFormData(prev => ({
      ...prev,
      [name]: type === 'number' ? parseFloat(value) || 0 : 
             type === 'select-one' ? value : value
    }))
  }

  const selectedProduct = products.find(p => p.id === formData.productId)

  return (
    <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div className="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
        <div className="flex items-center justify-between p-6 border-b">
          <h2 className="text-xl font-semibold text-gray-900">
            {movement ? 'Modifier le mouvement' : 'Nouveau mouvement de stock'}
          </h2>
          <button
            onClick={onCancel}
            className="text-gray-400 hover:text-gray-600"
          >
            <X className="h-6 w-6" />
          </button>
        </div>

        <form onSubmit={handleSubmit} className="p-6 space-y-6">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Produit *
              </label>
              <select
                name="productId"
                value={formData.productId}
                onChange={handleChange}
                required
                className="input"
              >
                <option value="">Sélectionner un produit</option>
                {products.map((product) => (
                  <option key={product.id} value={product.id}>
                    {product.name} ({product.sku})
                  </option>
                ))}
              </select>
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Type de mouvement *
              </label>
              <select
                name="type"
                value={formData.type}
                onChange={handleChange}
                required
                className="input"
              >
                <option value="in">Entrée</option>
                <option value="out">Sortie</option>
                <option value="adjustment">Ajustement</option>
              </select>
            </div>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Quantité *
              </label>
              <input
                type="number"
                name="quantity"
                value={formData.quantity}
                onChange={handleChange}
                required
                min="0.01"
                step="0.01"
                className="input"
                placeholder="0.00"
              />
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Motif *
              </label>
              <select
                name="reason"
                value={formData.reason}
                onChange={handleChange}
                required
                className="input"
              >
                <option value="purchase">Achat</option>
                <option value="sale">Vente</option>
                <option value="return">Retour</option>
                <option value="damage">Dégât</option>
                <option value="loss">Perte</option>
                <option value="inventory">Inventaire</option>
                <option value="manual">Manuel</option>
              </select>
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Coût unitaire (€)
              </label>
              <input
                type="number"
                name="unitCost"
                value={formData.unitCost || ''}
                onChange={handleChange}
                min="0"
                step="0.01"
                className="input"
                placeholder="0.00"
              />
            </div>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Référence
              </label>
              <input
                type="text"
                name="reference"
                value={formData.reference}
                onChange={handleChange}
                className="input"
                placeholder="Ex: FACT-2024-001"
              />
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Coût total (€)
              </label>
              <input
                type="text"
                value={formData.unitCost && formData.quantity ? 
                  (formData.unitCost * formData.quantity).toFixed(2) : 
                  '0.00'
                }
                readOnly
                className="input bg-gray-50"
                placeholder="0.00"
              />
            </div>
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Notes
            </label>
            <textarea
              name="notes"
              value={formData.notes}
              onChange={handleChange}
              rows={3}
              className="input"
              placeholder="Notes détaillées sur le mouvement..."
            />
          </div>

          {selectedProduct && (
            <div className="bg-blue-50 border border-blue-200 rounded-lg p-4">
              <h4 className="font-medium text-blue-900 mb-2">Informations du produit</h4>
              <div className="grid grid-cols-2 gap-4 text-sm">
                <div>
                  <span className="font-medium">Nom:</span> {selectedProduct.name}
                </div>
                <div>
                  <span className="font-medium">SKU:</span> {selectedProduct.sku}
                </div>
                <div>
                  <span className="font-medium">Stock actuel:</span> {selectedProduct.stock} unités
                </div>
                <div>
                  <span className="font-medium">Prix:</span> €{parseFloat(selectedProduct.price).toFixed(2)}
                </div>
              </div>
            </div>
          )}

          <div className="flex items-center justify-end space-x-4 pt-6 border-t">
            <button
              type="button"
              onClick={onCancel}
              className="btn btn-outline"
              disabled={isLoading}
            >
              Annuler
            </button>
            <button
              type="submit"
              className="btn btn-primary"
              disabled={isLoading}
            >
              {isLoading ? 'Enregistrement...' : (movement ? 'Mettre à jour' : 'Créer')}
            </button>
          </div>
        </form>
      </div>
    </div>
  )
}
