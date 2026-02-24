import { Package, Users, ShoppingCart, FileText, Settings, LogOut } from 'lucide-react'
import { authService } from '../../services/authService'

export function Header() {
  const handleLogout = () => {
    authService.logout()
    window.location.href = '/login'
  }

  const user = authService.getUser()

  return (
    <header className="bg-white shadow-sm border-b">
      <div className="px-6 py-4 flex items-center justify-between">
        <div className="flex items-center space-x-4">
          <h1 className="text-2xl font-bold text-gray-900">Mini ERP</h1>
        </div>
        
        <div className="flex items-center space-x-4">
          <div className="text-sm text-gray-600">
            Bonjour, {user?.name || 'Utilisateur'}
          </div>
          <button className="p-2 rounded-md hover:bg-gray-100 transition-colors">
            <Settings className="h-5 w-5 text-gray-600" />
          </button>
          <button 
            onClick={handleLogout}
            className="p-2 rounded-md hover:bg-gray-100 transition-colors"
            title="Déconnexion"
          >
            <LogOut className="h-5 w-5 text-gray-600" />
          </button>
        </div>
      </div>
    </header>
  )
}
