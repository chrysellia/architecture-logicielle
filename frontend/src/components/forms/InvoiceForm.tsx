import { useState } from 'react'
import { X, Plus, Trash2 } from 'lucide-react'

interface InvoiceItem {
  description: string
  quantity: number
  unitPrice: number
  totalPrice: number
}

interface InvoiceFormData {
  orderId: number
  invoiceNumber?: string
  issueDate: string
  dueDate: string
  taxAmount: number | string
  items: InvoiceItem[]
  notes?: string
  netAmount?: number
  totalAmount?: number
}

interface InvoiceFormProps {
  invoice?: any
  orders: any[]
  onSubmit: (data: InvoiceFormData) => void
  onCancel: () => void
  isLoading?: boolean
}

export function InvoiceForm({ invoice, orders, onSubmit, onCancel, isLoading = false }: InvoiceFormProps) {
  const [formData, setFormData] = useState<InvoiceFormData>({
    orderId: invoice?.order?.id || 0,
    invoiceNumber: invoice?.invoiceNumber || '',
    issueDate: invoice?.issueDate || new Date().toISOString().split('T')[0],
    dueDate: invoice?.dueDate || new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
    taxAmount: invoice?.taxAmount || 0,
    items: invoice?.items?.map((item: any) => ({
      description: item.description,
      quantity: item.quantity,
      unitPrice: item.unitPrice,
      totalPrice: item.totalPrice
    })) || [{ description: '', quantity: 1, unitPrice: 0, totalPrice: 0 }],
    notes: invoice?.notes || ''
  })

  const handleAddItem = () => {
    setFormData(prev => ({
      ...prev,
      items: [...prev.items, { description: '', quantity: 1, unitPrice: 0, totalPrice: 0 }]
    }))
  }

  const handleRemoveItem = (index: number) => {
    setFormData(prev => ({
      ...prev,
      items: prev.items.filter((_, i) => i !== index)
    }))
  }

  const handleItemChange = (index: number, field: keyof InvoiceItem, value: string | number) => {
    setFormData(prev => {
      const newItems = [...prev.items]
      const item = { ...newItems[index] }
      
      if (field === 'description') {
        item.description = value as string
      } else if (field === 'quantity') {
        item.quantity = Number(value)
      } else if (field === 'unitPrice') {
        item.unitPrice = Number(value)
      }
      
      item.totalPrice = item.quantity * item.unitPrice
      
      newItems[index] = item
      return {
        ...prev,
        items: newItems
      }
    })
  }

  const calculateTotals = () => {
    const netAmount = formData.items.reduce((total, item) => total + item.totalPrice, 0)
    const taxAmount = typeof formData.taxAmount === 'string' ? parseFloat(formData.taxAmount) : (formData.taxAmount || 0)
    const totalAmount = netAmount + taxAmount
    return { netAmount, totalAmount }
  }

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault()
    const { netAmount, totalAmount } = calculateTotals()
    onSubmit({
      ...formData,
      netAmount,
      totalAmount
    })
  }

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement>) => {
    const { name, value } = e.target
    setFormData(prev => ({
      ...prev,
      [name]: value
    }))
  }

  const selectedOrder = orders.find(o => o.id === formData.orderId)

  return (
    <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div className="bg-white rounded-lg shadow-xl w-full max-w-4xl mx-4 max-h-[90vh] overflow-y-auto">
        <div className="flex items-center justify-between p-6 border-b">
          <h2 className="text-xl font-semibold text-gray-900">
            {invoice ? 'Modifier la facture' : 'Nouvelle facture'}
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
                Commande associée *
              </label>
              <select
                name="orderId"
                value={formData.orderId}
                onChange={handleChange}
                required
                className="input"
              >
                <option value="">Sélectionner une commande</option>
                {orders.map((order) => (
                  <option key={order.id} value={order.id}>
                    {order.orderNumber} - {order.customer.firstName} {order.customer.lastName} - €{order.totalAmount.toFixed(2)}
                  </option>
                ))}
              </select>
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Numéro de facture
              </label>
              <input
                type="text"
                name="invoiceNumber"
                value={formData.invoiceNumber}
                onChange={handleChange}
                className="input"
                placeholder="FAC-2024-001"
              />
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Date d'émission *
              </label>
              <input
                type="date"
                name="issueDate"
                value={formData.issueDate}
                onChange={handleChange}
                required
                className="input"
              />
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Date d'échéance *
              </label>
              <input
                type="date"
                name="dueDate"
                value={formData.dueDate}
                onChange={handleChange}
                required
                className="input"
              />
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Montant TVA (€)
              </label>
              <input
                type="number"
                name="taxAmount"
                value={formData.taxAmount}
                onChange={handleChange}
                min="0"
                step="0.01"
                className="input"
                placeholder="0.00"
              />
            </div>
          </div>

          <div>
            <div className="flex items-center justify-between mb-4">
              <h3 className="text-lg font-medium text-gray-900">Articles de la facture</h3>
              <button
                type="button"
                onClick={handleAddItem}
                className="btn btn-outline flex items-center space-x-1"
              >
                <Plus className="h-4 w-4" />
                <span>Ajouter un article</span>
              </button>
            </div>

            <div className="space-y-4">
              {formData.items.map((item, index) => (
                <div key={index} className="border rounded-lg p-4">
                  <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div className="md:col-span-2">
                      <label className="block text-sm font-medium text-gray-700 mb-2">
                        Description *
                      </label>
                      <input
                        type="text"
                        value={item.description}
                        onChange={(e) => handleItemChange(index, 'description', e.target.value)}
                        required
                        className="input"
                        placeholder="Description de l'article"
                      />
                    </div>

                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-2">
                        Quantité *
                      </label>
                      <input
                        type="number"
                        value={item.quantity}
                        onChange={(e) => handleItemChange(index, 'quantity', e.target.value)}
                        required
                        min="1"
                        className="input"
                      />
                    </div>

                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-2">
                        Prix unitaire (€) *
                      </label>
                      <input
                        type="number"
                        value={item.unitPrice}
                        onChange={(e) => handleItemChange(index, 'unitPrice', e.target.value)}
                        required
                        min="0"
                        step="0.01"
                        className="input"
                      />
                    </div>

                    <div className="flex items-end">
                      <div className="flex-1">
                        <div className="text-sm text-gray-500">TVA: €{typeof formData.taxAmount === 'number' ? formData.taxAmount.toFixed(2) : parseFloat(formData.taxAmount).toFixed(2)}</div>
                        <div className="text-lg font-semibold text-gray-900">
                          €{item.totalPrice.toFixed(2)}
                        </div>
                      </div>
                      {formData.items.length > 1 && (
                        <button
                          type="button"
                          onClick={() => handleRemoveItem(index)}
                          className="ml-2 text-red-600 hover:text-red-900"
                        >
                          <Trash2 className="h-4 w-4" />
                        </button>
                      )}
                    </div>
                  </div>
                </div>
              ))}
            </div>
          </div>

          <div className="border-t pt-4">
            <div className="flex justify-end">
              <div className="text-right">
                <div className="space-y-2">
                  <div className="flex justify-between items-center">
                    <span className="text-sm text-gray-600">Montant HT:</span>
                    <span className="text-lg font-medium text-gray-900">
                      €{calculateTotals().netAmount.toFixed(2)}
                    </span>
                  </div>
                  <div className="flex justify-between items-center">
                    <span className="text-sm text-gray-600">TVA:</span>
                    <span className="text-lg font-medium text-gray-900">
                      €{formData.taxAmount.toFixed(2)}
                    </span>
                  </div>
                  <div className="flex justify-between items-center pt-2 border-t">
                    <span className="text-lg font-bold text-gray-900">Total TTC:</span>
                    <span className="text-2xl font-bold text-blue-600">
                      €{calculateTotals().totalAmount.toFixed(2)}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          {selectedOrder && (
            <div className="bg-blue-50 border border-blue-200 rounded-lg p-4">
              <h4 className="font-medium text-blue-900 mb-2">Informations de la commande</h4>
              <div className="grid grid-cols-2 gap-4 text-sm">
                <div>
                  <span className="font-medium">Numéro:</span> {selectedOrder.orderNumber}
                </div>
                <div>
                  <span className="font-medium">Client:</span> {selectedOrder.customer.firstName} {selectedOrder.customer.lastName}
                </div>
                <div>
                  <span className="font-medium">Email:</span> {selectedOrder.customer.email}
                </div>
                <div>
                  <span className="font-medium">Total commande:</span> €{selectedOrder.totalAmount.toFixed(2)}
                </div>
              </div>
            </div>
          )}

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
              placeholder="Notes détaillées sur la facture..."
            />
          </div>

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
              {isLoading ? 'Enregistrement...' : (invoice ? 'Mettre à jour' : 'Créer')}
            </button>
          </div>
        </form>
      </div>
    </div>
  )
}
