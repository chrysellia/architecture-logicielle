import { Package, Users, ShoppingCart, FileText, Settings, LogOut } from 'lucide-react'

export function Header() {
  return (
    <header className="bg-white shadow-sm border-b">
      <div className="px-6 py-4 flex items-center justify-between">
        <div className="flex items-center space-x-4">
          <h1 className="text-2xl font-bold text-gray-900">Mini ERP</h1>
        </div>
        
        <div className="flex items-center space-x-4">
          <button className="p-2 rounded-md hover:bg-gray-100 transition-colors">
            <Settings className="h-5 w-5 text-gray-600" />
          </button>
          <button className="p-2 rounded-md hover:bg-gray-100 transition-colors">
            <LogOut className="h-5 w-5 text-gray-600" />
          </button>
        </div>
      </div>
    </header>
  )
}
