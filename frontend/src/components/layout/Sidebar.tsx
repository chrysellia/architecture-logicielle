import { NavLink } from 'react-router-dom'
import { Package, Users, ShoppingCart, FileText, BarChart3, Warehouse } from 'lucide-react'

const navigation = [
  { name: 'Tableau de bord', href: '/', icon: BarChart3 },
  { name: 'Produits', href: '/products', icon: Package },
  { name: 'Stock', href: '/stock', icon: Warehouse },
  { name: 'Commandes', href: '/orders', icon: ShoppingCart },
  { name: 'Clients', href: '/customers', icon: Users },
  { name: 'Factures', href: '/invoices', icon: FileText },
]

export function Sidebar() {
  return (
    <aside className="w-64 bg-white shadow-sm h-full">
      <nav className="p-4 space-y-2">
        {navigation.map((item) => (
          <NavLink
            key={item.name}
            to={item.href}
            className={({ isActive }) =>
              `flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors ${
                isActive
                  ? 'bg-blue-50 text-blue-700 border-l-4 border-blue-700'
                  : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900'
              }`
            }
          >
            <item.icon className="h-5 w-5" />
            <span className="font-medium">{item.name}</span>
          </NavLink>
        ))}
      </nav>
    </aside>
  )
}
