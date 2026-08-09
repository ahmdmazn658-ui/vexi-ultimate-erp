import { useRefOptions, type Row } from '../api/resource'
import type { Field } from '../resources/types'
import { Field as FieldWrapper, inputClass, selectClass } from './ui'

export type FormValues = Record<string, string>

/** القيم الابتدائية لنموذج: من السجل الحالي عند التعديل، ومن `defaultValue` عند الإنشاء. */
export function initialValues(fields: Field[], row?: Row): FormValues {
  const values: FormValues = {}

  for (const field of fields) {
    const existing = row?.[field.name]

    if (existing === null || existing === undefined) {
      values[field.name] = String(field.defaultValue ?? '')
      continue
    }

    // لاحظ: Laravel بيسريلايز العلاقة تحت اسم snake_case بتاعها، فعلاقة زي
    // `assignedTo` بتطلع باسم `assigned_to` وبتغطي على عمود المفتاح الأجنبي
    // اللي بنفس الاسم. فلو القيمة كائن، ناخد الـ id منه.
    values[field.name] =
      typeof existing === 'object'
        ? String((existing as { id?: number }).id ?? '')
        : String(existing)
  }

  return values
}

/** بيشيل الحقول الفاضية عشان Laravel يطبّق `nullable` بدل ما يرفض سلسلة فاضية. */
export function toPayload(fields: Field[], values: FormValues): Row {
  const payload: Row = {}

  for (const field of fields) {
    const raw = values[field.name]
    if (raw === undefined || raw === '') continue

    payload[field.name] =
      field.kind === 'number' || field.kind === 'ref' ? Number(raw) : raw
  }

  return payload
}

function RefSelect({
  field,
  value,
  onChange,
}: {
  field: Field
  value: string
  onChange: (value: string) => void
}) {
  const { data: options, isPending, isError } = useRefOptions(field.ref)

  return (
    <select
      className={selectClass}
      value={value}
      required={field.required}
      disabled={isPending}
      onChange={(event) => onChange(event.target.value)}
    >
      <option value="">
        {isPending ? 'جارٍ التحميل…' : isError ? 'تعذّر تحميل القائمة' : '— اختر —'}
      </option>
      {options?.map((option) => (
        <option key={option.value} value={option.value}>
          {option.label}
        </option>
      ))}
    </select>
  )
}

export function FieldInput({
  field,
  value,
  onChange,
}: {
  field: Field
  value: string
  onChange: (value: string) => void
}) {
  if (field.kind === 'ref') {
    return <RefSelect field={field} value={value} onChange={onChange} />
  }

  if (field.kind === 'select') {
    return (
      <select
        className={selectClass}
        value={value}
        required={field.required}
        onChange={(event) => onChange(event.target.value)}
      >
        <option value="">— اختر —</option>
        {field.options?.map((option) => (
          <option key={option.value} value={option.value}>
            {option.label}
          </option>
        ))}
      </select>
    )
  }

  if (field.kind === 'textarea') {
    return (
      <textarea
        className={inputClass}
        rows={3}
        value={value}
        required={field.required}
        placeholder={field.placeholder}
        onChange={(event) => onChange(event.target.value)}
      />
    )
  }

  return (
    <input
      className={inputClass}
      type={
        field.kind === 'number'
          ? 'number'
          : field.kind === 'date'
            ? 'date'
            : field.kind === 'email'
              ? 'email'
              : field.kind === 'password'
                ? 'password'
                : 'text'
      }
      // الأرقام والتواريخ LTR حتى داخل واجهة RTL
      dir={field.kind === 'number' || field.kind === 'date' ? 'ltr' : undefined}
      value={value}
      required={field.required}
      placeholder={field.placeholder}
      step={field.step}
      min={field.min}
      max={field.max}
      onChange={(event) => onChange(event.target.value)}
    />
  )
}

/** شبكة حقول — الحقول ذات `span: 2` بتاخد العرض كامل. */
export function FieldGrid({
  fields,
  values,
  onChange,
}: {
  fields: Field[]
  values: FormValues
  onChange: (name: string, value: string) => void
}) {
  return (
    <div className="grid gap-4 sm:grid-cols-2">
      {fields.map((field) => (
        <div key={field.name} className={field.span === 2 ? 'sm:col-span-2' : undefined}>
          <FieldWrapper label={field.required ? `${field.label} *` : field.label}>
            <FieldInput
              field={field}
              value={values[field.name] ?? ''}
              onChange={(value) => onChange(field.name, value)}
            />
          </FieldWrapper>
          {field.help && <p className="mt-1 text-xs text-ink-400">{field.help}</p>}
        </div>
      ))}
    </div>
  )
}
