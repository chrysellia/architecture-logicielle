import { Routes, Route } from 'react-router-dom'
import { Layout } from './components/layout/Layout'
import { DashboardPage } from './pages/Dashboard/DashboardPage'
import { ProductListPage } from './pages/Products/ProductListPage'
import { LoginPage } from './pages/Auth/LoginPage'

function App() {
  return (
    <Routes>
      <Route path="/login" element={<LoginPage />} />
      <Route path="/" element={<Layout />}>
        <Route index element={<DashboardPage />} />
        <Route path="products" element={<ProductListPage />} />
        <Route path="products/:id" element={<div>Product Detail</div>} />
        <Route path="orders" element={<div>Orders</div>} />
        <Route path="customers" element={<div>Customers</div>} />
        <Route path="invoices" element={<div>Invoices</div>} />
        <Route path="stock" element={<div>Stock</div>} />
        <Route path="settings" element={<div>Settings</div>} />
      </Route>
    </Routes>
  )
}

export default App
