import { Routes, Route } from 'react-router-dom'
import { Layout } from './components/layout/Layout'
import { DashboardPage } from './pages/Dashboard/DashboardPage'
import { ProductListPage } from './pages/Products/ProductListPage'
import { OrderListPage } from './pages/Orders/OrderListPage'
import { CustomerListPage } from './pages/Customers/CustomerListPage'
import { InvoiceListPage } from './pages/Invoices/InvoiceListPage'
import { StockListPage } from './pages/Stock/StockListPage'
import { LoginPage } from './pages/Auth/LoginPage'
import { ForgotPasswordPage } from './pages/Auth/ForgotPasswordPage'
import { ResetPasswordPage } from './pages/Auth/ResetPasswordPage'
import { RegisterPage } from './pages/Auth/RegisterPage'
import { AuthGuard } from './components/Auth/AuthGuard'

function App() {
  return (
    <Routes>
      <Route path="/login" element={<LoginPage />} />
      <Route path="/register" element={<RegisterPage />} />
      <Route path="/forgot-password" element={<ForgotPasswordPage />} />
      <Route path="/reset-password" element={<ResetPasswordPage />} />
      <Route path="/" element={
        <AuthGuard>
          <Layout />
        </AuthGuard>
      }>
        <Route index element={<DashboardPage />} />
        <Route path="products" element={<ProductListPage />} />
        <Route path="products/:id" element={<div>Product Detail</div>} />
        <Route path="orders" element={<OrderListPage />} />
        <Route path="customers" element={<CustomerListPage />} />
        <Route path="invoices" element={<InvoiceListPage />} />
        <Route path="stock" element={<StockListPage />} />
        <Route path="settings" element={<div>Settings</div>} />
      </Route>
    </Routes>
  )
}

export default App
