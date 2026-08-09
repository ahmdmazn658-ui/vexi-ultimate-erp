import { lazy, Suspense, type JSX } from 'react'
import { Navigate, Route, Routes, useParams } from 'react-router'
import Layout from './components/Layout'
import { Loading } from './components/ui'
import { getToken } from './lib/auth'
import Login from './pages/Login'
import { resourceBySlug } from './resources'

// تقسيم الحزمة: صفحات الرسوم البيانية (recharts) بتتحمّل عند الحاجة بس
const Dashboard = lazy(() => import('./pages/Dashboard'))
const Invoices = lazy(() => import('./pages/Invoices'))
const Payments = lazy(() => import('./pages/Payments'))
const Periods = lazy(() => import('./pages/Periods'))
const Reports = lazy(() => import('./pages/Reports'))
const ResourcePage = lazy(() => import('./components/ResourcePage'))

function RequireAuth({ children }: { children: JSX.Element }) {
  return getToken() ? children : <Navigate to="/login" replace />
}

/**
 * كل الموديولات المتولّدة بتشترك في مسار واحد. الشاشة بتتحدد من `resources`
 * حسب أول جزء في المسار — فإضافة موديول جديد بتحتاج تعريف بس، من غير راوت.
 */
function ResourceRoute() {
  const { slug } = useParams()
  const resource = slug ? resourceBySlug.get(slug) : undefined

  if (!resource) return <Navigate to="/" replace />

  return <ResourcePage key={resource.slug} resource={resource} />
}

export default function App() {
  return (
    <Suspense fallback={<Loading />}>
      <Routes>
        <Route path="/login" element={<Login />} />

        <Route
          element={
            <RequireAuth>
              <Layout />
            </RequireAuth>
          }
        >
          <Route index element={<Dashboard />} />

          {/* شاشات مكتوبة بإيد — فيها منطق مش متكرر (رسوم، تخصيص، إقفال) */}
          <Route path="invoices" element={<Invoices />} />
          <Route path="payments" element={<Payments />} />
          <Route path="reports" element={<Reports />} />
          <Route path="periods" element={<Periods />} />

          {/* باقي الموديولات — متولّدة من التعريف */}
          <Route path=":slug" element={<ResourceRoute />} />
        </Route>

        <Route path="*" element={<Navigate to="/" replace />} />
      </Routes>
    </Suspense>
  )
}
