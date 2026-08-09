import { useState } from 'react'
import { useNavigate } from 'react-router'
import { useLogin } from '../api/hooks'
import { errorMessage } from '../lib/api'
import { Button, ErrorState, Field, inputClass } from '../components/ui'

export default function Login() {
  const navigate = useNavigate()
  const login = useLogin()
  const [email, setEmail] = useState('admin@erp.local')
  const [password, setPassword] = useState('')

  function handleSubmit(event: React.FormEvent) {
    event.preventDefault()
    login.mutate(
      { email, password },
      { onSuccess: () => void navigate('/', { replace: true }) },
    )
  }

  return (
    <div className="flex min-h-screen items-center justify-center bg-slate-50 p-4">
      <div className="w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
        <h1 className="text-xl font-bold text-brand-700">Vexi Ultimate</h1>
        <p className="mt-1 mb-6 text-sm text-ink-400">سجّل الدخول للمتابعة</p>

        <form onSubmit={handleSubmit} className="space-y-4">
          <Field label="البريد الإلكتروني">
            <input
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              className={inputClass}
              dir="ltr"
              required
            />
          </Field>

          <Field label="كلمة المرور">
            <input
              type="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              className={inputClass}
              dir="ltr"
              required
            />
          </Field>

          {login.isError && <ErrorState message={errorMessage(login.error)} />}

          <div className="pt-2">
            <Button type="submit" disabled={login.isPending}>
              {login.isPending ? 'جارٍ الدخول…' : 'دخول'}
            </Button>
          </div>
        </form>
      </div>
    </div>
  )
}
