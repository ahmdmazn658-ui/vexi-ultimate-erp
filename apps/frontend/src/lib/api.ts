import axios from 'axios'
import { clearSession, getToken } from './auth'

export const api = axios.create({
  baseURL: `${import.meta.env.VITE_API_BASE_URL ?? ''}/api/v1`,
  headers: { Accept: 'application/json' },
})

api.interceptors.request.use((config) => {
  const token = getToken()
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

/**
 * الاستضافات المشتركة اللي بتستخدم تحدي JavaScript (زي InfinityFree) بتحقن
 * صفحة HTML فيها `aes.js` مكان أي رد — بما فيها ردود الـ JSON — لما كوكي
 * `__test` تنتهي (عمرها حوالي ٦ ساعات). النتيجة إن التطبيق بيفضل شغال في
 * الجلسة الأولى وبعدين كل النداءات بتفشل بشكل غامض.
 *
 * إعادة تحميل الصفحة بتخلّي المتصفح يعدّي التحدي ويجدّد الكوكي. بنعمل ده مرة
 * واحدة بس لكل جلسة عشان ما ندخلش في حلقة إعادة تحميل لو المشكلة حاجة تانية.
 */
const RELOAD_FLAG = 'erp.challengeReloaded'

function isSecurityChallenge(data: unknown): boolean {
  return (
    typeof data === 'string' &&
    data.includes('aes.js') &&
    data.includes('__test')
  )
}

api.interceptors.response.use(
  (response) => {
    if (isSecurityChallenge(response.data)) {
      if (!sessionStorage.getItem(RELOAD_FLAG)) {
        sessionStorage.setItem(RELOAD_FLAG, '1')
        window.location.reload()
      }

      return Promise.reject(
        new Error('تحقق أمني من الاستضافة قطع الاتصال. حدّث الصفحة وجرّب تاني.'),
      )
    }

    sessionStorage.removeItem(RELOAD_FLAG)
    return response
  },
  (error) => {
    // التوكن انتهى أو مرفوض — نظّف الجلسة ورجّع لصفحة الدخول
    if (error?.response?.status === 401 && getToken()) {
      clearSession()
      window.location.assign('/login')
    }
    return Promise.reject(error)
  },
)

/** يستخرج رسالة خطأ مقروءة من استجابة Laravel (بما فيها أخطاء التحقق 422). */
export function errorMessage(error: unknown): string {
  if (axios.isAxiosError(error)) {
    const data = error.response?.data as
      | { message?: string; errors?: Record<string, string[]> }
      | undefined

    const firstValidationError = data?.errors
      ? Object.values(data.errors).flat()[0]
      : undefined

    return firstValidationError ?? data?.message ?? error.message
  }

  if (error instanceof Error) return error.message

  return 'حصل خطأ غير متوقع.'
}
