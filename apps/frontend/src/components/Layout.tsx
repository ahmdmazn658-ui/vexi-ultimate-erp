import { useState } from 'react'
import { LayoutDashboard, LogOut, Menu, X } from 'lucide-react'
import { NavLink, Outlet, useNavigate } from 'react-router'
import { clearSession, getUser } from '../lib/auth'
import { navGroups } from '../resources'

function Sidebar({ onNavigate }: { onNavigate?: () => void }) {
  return (
    <nav className="space-y-6 p-3 pb-10">
      <NavLink
        to="/"
        end
        onClick={onNavigate}
        className={({ isActive }) =>
          `flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors ${
            isActive ? 'bg-brand-50 text-brand-700' : 'text-ink-600 hover:bg-slate-50'
          }`
        }
      >
        <LayoutDashboard className="size-4 shrink-0" />
        لوحة المؤشرات
      </NavLink>

      {navGroups.map((group) => (
        <div key={group.label}>
          <p className="px-3 pb-1.5 text-xs font-semibold text-ink-400">{group.label}</p>

          <div className="space-y-0.5">
            {group.items.map((item) => (
              <NavLink
                key={item.to}
                to={item.to}
                onClick={onNavigate}
                className={({ isActive }) =>
                  `block rounded-lg px-3 py-1.5 text-sm transition-colors ${
                    isActive
                      ? 'bg-brand-50 font-medium text-brand-700'
                      : 'text-ink-600 hover:bg-slate-50'
                  }`
                }
              >
                {item.label}
              </NavLink>
            ))}
          </div>
        </div>
      ))}
    </nav>
  )
}

export default function Layout() {
  const navigate = useNavigate()
  const user = getUser()
  const [menuOpen, setMenuOpen] = useState(false)

  function handleLogout() {
    clearSession()
    void navigate('/login')
  }

  return (
    <div className="flex min-h-screen">
      <aside className="hidden w-64 shrink-0 overflow-y-auto border-l border-slate-200 bg-white md:block">
        <div className="flex h-16 items-center border-b border-slate-100 px-5">
          <span className="text-lg font-bold text-brand-700">Vexi Ultimate</span>
        </div>

        <Sidebar />
      </aside>

      {/* القائمة على الموبايل — الشاشات بقت كتير، فالقائمة الجانبية لازم تفتح وتقفل */}
      {menuOpen && (
        <div className="fixed inset-0 z-40 md:hidden">
          <div
            className="absolute inset-0 bg-slate-900/40"
            onClick={() => setMenuOpen(false)}
          />
          <aside className="absolute inset-y-0 right-0 w-72 overflow-y-auto bg-white shadow-xl">
            <div className="flex h-16 items-center justify-between border-b border-slate-100 px-5">
              <span className="text-lg font-bold text-brand-700">Vexi Ultimate</span>
              <button
                onClick={() => setMenuOpen(false)}
                aria-label="إغلاق القائمة"
                className="rounded-lg p-1 text-ink-400 hover:bg-slate-50"
              >
                <X className="size-5" />
              </button>
            </div>

            <Sidebar onNavigate={() => setMenuOpen(false)} />
          </aside>
        </div>
      )}

      <div className="flex min-w-0 flex-1 flex-col">
        <header className="flex h-16 items-center justify-between border-b border-slate-200 bg-white px-6">
          <button
            onClick={() => setMenuOpen(true)}
            aria-label="فتح القائمة"
            className="rounded-lg p-2 text-ink-600 hover:bg-slate-50 md:hidden"
          >
            <Menu className="size-5" />
          </button>

          <div className="mr-auto flex items-center gap-4">
            {user && (
              <div className="text-left">
                <p className="text-sm font-medium text-ink-900">{user.name}</p>
                <p className="text-xs text-ink-400">{user.role}</p>
              </div>
            )}
            <button
              onClick={handleLogout}
              className="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-ink-600 transition-colors hover:bg-slate-50"
            >
              <LogOut className="size-4" />
              خروج
            </button>
          </div>
        </header>

        <main className="flex-1 overflow-x-hidden p-6">
          <Outlet />
        </main>
      </div>
    </div>
  )
}
