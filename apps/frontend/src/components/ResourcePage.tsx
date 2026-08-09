import { useState } from 'react'
import { Pencil, Trash2 } from 'lucide-react'
import {
  useResourceAction,
  useResourceCreate,
  useResourceDelete,
  useResourceList,
  useResourceUpdate,
  type Row,
} from '../api/resource'
import { errorMessage } from '../lib/api'
import { date as formatDate, money, num } from '../lib/format'
import type { Column, Field, Resource, RowAction } from '../resources/types'
import { FieldGrid, initialValues, toPayload, type FormValues } from './FormFields'
import { emptyItem, LineItems, type ItemRow } from './LineItems'
import {
  Badge,
  Button,
  Card,
  ConfirmDialog,
  EmptyState,
  ErrorState,
  inputClass,
  Loading,
  Modal,
  Pagination,
  selectClass,
  Table,
} from './ui'

/** بيقرا مسار متداخل زي `customer.name` من السجل. */
function readPath(row: Row, path: string): unknown {
  return path
    .split('.')
    .reduce<unknown>(
      (value, key) =>
        value && typeof value === 'object'
          ? (value as Record<string, unknown>)[key]
          : undefined,
      row,
    )
}

function renderCell(row: Row, column: Column) {
  const value = readPath(row, column.key)

  if (value === null || value === undefined || value === '') return <span>—</span>

  switch (column.format) {
    case 'money':
      return <span className="num">{money(value as string)}</span>
    case 'number':
      return <span className="num">{num(value as string)}</span>
    case 'percent':
      return <span className="num">{num(value as string)}%</span>
    case 'date':
      return <span className="text-ink-600">{formatDate(value as string)}</span>
    case 'badge':
      return <Badge value={String(value)} />
    case 'bool':
      return <Badge value={value ? 'active' : 'closed'} />
    case 'code':
      return <span className="num font-medium">{String(value)}</span>
    default:
      return <span>{String(value)}</span>
  }
}

function visibleFields(fields: Field[], mode: 'create' | 'edit'): Field[] {
  return fields.filter((field) => !field.only || field.only === mode)
}

// ── نموذج الإنشاء / التعديل ───────────────────────────────────
function ResourceForm({
  resource,
  row,
  onClose,
}: {
  resource: Resource
  row?: Row
  onClose: () => void
}) {
  const mode = row ? 'edit' : 'create'
  const fields = visibleFields(resource.fields ?? [], mode)

  const [values, setValues] = useState<FormValues>(() => initialValues(fields, row))
  const [files, setFiles] = useState<Record<string, File>>({})
  const [items, setItems] = useState<ItemRow[]>(() =>
    resource.items ? [emptyItem(resource.items.fields)] : [],
  )

  const create = useResourceCreate(resource.slug, resource.endpoint, resource.refreshes)
  const update = useResourceUpdate(resource.slug, resource.endpoint, resource.refreshes)
  const mutation = row ? update : create

  function handleSubmit() {
    const payload = toPayload(fields, values)

    if (resource.items && !row) {
      payload[resource.items.name] = items.map((item) =>
        toPayload(resource.items!.fields, item),
      )
    }

    const done = { onSuccess: () => onClose() }

    if (row) {
      update.mutate({ id: row.id as number, payload }, done)
      return
    }

    if (resource.multipart) {
      const form = new FormData()
      for (const [key, value] of Object.entries(payload)) {
        form.append(key, String(value))
      }
      for (const [key, file] of Object.entries(files)) {
        form.append(key, file)
      }
      create.mutate(form, done)
      return
    }

    create.mutate(payload, done)
  }

  return (
    <Modal
      title={row ? `تعديل — ${resource.title}` : `إضافة — ${resource.title}`}
      onClose={onClose}
      wide={Boolean(resource.items)}
    >
      <div className="space-y-5">
        <FieldGrid
          fields={fields.filter((field) => field.kind !== 'file')}
          values={values}
          onChange={(name, value) => setValues((current) => ({ ...current, [name]: value }))}
        />

        {fields
          .filter((field) => field.kind === 'file')
          .map((field) => (
            <label key={field.name} className="block">
              <span className="mb-1 block text-xs font-medium text-ink-600">
                {field.label}
              </span>
              <input
                type="file"
                className={inputClass}
                onChange={(event) => {
                  const file = event.target.files?.[0]
                  if (file) setFiles((current) => ({ ...current, [field.name]: file }))
                }}
              />
              {field.help && <p className="mt-1 text-xs text-ink-400">{field.help}</p>}
            </label>
          ))}

        {resource.items && !row && (
          <LineItems config={resource.items} rows={items} onChange={setItems} />
        )}

        {mutation.isError && <ErrorState message={errorMessage(mutation.error)} />}

        <div className="flex gap-2">
          <Button onClick={handleSubmit} disabled={mutation.isPending}>
            {mutation.isPending ? 'جارٍ الحفظ…' : 'حفظ'}
          </Button>
          <Button variant="ghost" onClick={onClose} disabled={mutation.isPending}>
            إلغاء
          </Button>
        </div>
      </div>
    </Modal>
  )
}

// ── نموذج عملية تحتاج مدخلات (تأكيد طلب بيع، تعيين متقدّم…) ────
function ActionForm({
  action,
  row,
  slug,
  refreshes,
  onClose,
}: {
  action: RowAction
  row: Row
  slug: string
  refreshes?: string[]
  onClose: () => void
}) {
  const fields = action.fields ?? []
  const [values, setValues] = useState<FormValues>(() => initialValues(fields))
  const mutation = useResourceAction(slug, refreshes)

  return (
    <Modal title={action.label} onClose={onClose}>
      <div className="space-y-5">
        <FieldGrid
          fields={fields}
          values={values}
          onChange={(name, value) => setValues((current) => ({ ...current, [name]: value }))}
        />

        {mutation.isError && <ErrorState message={errorMessage(mutation.error)} />}

        <div className="flex gap-2">
          <Button
            disabled={mutation.isPending}
            onClick={() =>
              mutation.mutate(
                { path: action.path(row), payload: toPayload(fields, values) },
                { onSuccess: () => onClose() },
              )
            }
          >
            {mutation.isPending ? 'جارٍ التنفيذ…' : action.label}
          </Button>
          <Button variant="ghost" onClick={onClose} disabled={mutation.isPending}>
            إلغاء
          </Button>
        </div>
      </div>
    </Modal>
  )
}

// ── الشاشة ────────────────────────────────────────────────────
export default function ResourcePage({ resource }: { resource: Resource }) {
  const [page, setPage] = useState(1)
  const [search, setSearch] = useState('')
  const [filters, setFilters] = useState<Record<string, string>>({})
  const [editing, setEditing] = useState<{ open: boolean; row?: Row } | null>(null)
  const [deleting, setDeleting] = useState<Row | null>(null)
  const [acting, setActing] = useState<{ action: RowAction; row: Row } | null>(null)

  const params: Record<string, unknown> = { page, ...filters }
  if (search) params.search = search

  const { data, isPending, isError, error } = useResourceList(
    resource.slug,
    resource.endpoint,
    params,
  )

  const remove = useResourceDelete(resource.slug, resource.endpoint, resource.refreshes)
  const runAction = useResourceAction(resource.slug, resource.refreshes)

  const actions = resource.actions ?? []
  const hasRowControls = actions.length > 0 || resource.edit || resource.remove

  function setFilter(name: string, value: string) {
    setPage(1)
    setFilters((current) => {
      const next = { ...current }
      if (value) next[name] = value
      else delete next[name]
      return next
    })
  }

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 className="text-xl font-bold">{resource.title}</h1>
          {resource.subtitle && (
            <p className="mt-1 text-sm text-ink-400">{resource.subtitle}</p>
          )}
        </div>

        {resource.create && (
          <Button onClick={() => setEditing({ open: true })}>إضافة جديد</Button>
        )}
      </div>

      {(resource.searchable || resource.filters?.length) && (
        <div className="flex flex-wrap items-end gap-3">
          {resource.searchable && (
            <input
              className={`${inputClass} sm:w-64`}
              placeholder="بحث بالاسم أو الكود…"
              value={search}
              onChange={(event) => {
                setPage(1)
                setSearch(event.target.value)
              }}
            />
          )}

          {resource.filters?.map((filter) => (
            <select
              key={filter.name}
              className={`${selectClass} sm:w-48`}
              value={filters[filter.name] ?? ''}
              onChange={(event) => setFilter(filter.name, event.target.value)}
            >
              <option value="">{filter.label}: الكل</option>
              {filter.options.map((option) => (
                <option key={option.value} value={option.value}>
                  {option.label}
                </option>
              ))}
            </select>
          ))}
        </div>
      )}

      {runAction.isError && <ErrorState message={errorMessage(runAction.error)} />}
      {remove.isError && <ErrorState message={errorMessage(remove.error)} />}

      <Card>
        {isPending ? (
          <Loading />
        ) : isError ? (
          <ErrorState message={errorMessage(error)} />
        ) : data.data.length === 0 ? (
          <EmptyState message="مفيش سجلات هنا لسه — ابدأ بإضافة واحد." />
        ) : (
          <>
            <Table
              headers={[
                ...resource.columns.map((column) => column.label),
                ...(hasRowControls ? [''] : []),
              ]}
            >
              {data.data.map((row) => (
                <tr key={row.id as number} className="hover:bg-slate-50">
                  {resource.columns.map((column) => (
                    <td key={column.key} className="px-3 py-2 align-middle">
                      {renderCell(row, column)}
                    </td>
                  ))}

                  {hasRowControls && (
                    <td className="px-3 py-2">
                      <div className="flex items-center justify-end gap-1.5">
                        {actions
                          .filter((action) => !action.when || action.when(row))
                          .map((action) => (
                            <Button
                              key={action.label}
                              size="sm"
                              variant={action.variant ?? 'primary'}
                              disabled={runAction.isPending}
                              onClick={() => {
                                if (action.fields?.length) {
                                  setActing({ action, row })
                                  return
                                }
                                if (action.confirm && !window.confirm(action.confirm)) return
                                runAction.mutate({ path: action.path(row) })
                              }}
                            >
                              {action.label}
                            </Button>
                          ))}

                        {resource.edit && (
                          <button
                            aria-label="تعديل"
                            onClick={() => setEditing({ open: true, row })}
                            className="rounded-lg p-1.5 text-ink-400 transition-colors hover:bg-slate-100 hover:text-ink-600"
                          >
                            <Pencil className="size-4" />
                          </button>
                        )}

                        {resource.remove && (
                          <button
                            aria-label="حذف"
                            onClick={() => setDeleting(row)}
                            className="rounded-lg p-1.5 text-ink-400 transition-colors hover:bg-rose-50 hover:text-rose-600"
                          >
                            <Trash2 className="size-4" />
                          </button>
                        )}
                      </div>
                    </td>
                  )}
                </tr>
              ))}
            </Table>

            <Pagination
              page={data.current_page}
              lastPage={data.last_page}
              total={data.total}
              onChange={setPage}
            />
          </>
        )}
      </Card>

      {resource.note && <p className="text-xs text-ink-400">{resource.note}</p>}

      {editing?.open && (
        <ResourceForm
          resource={resource}
          row={editing.row}
          onClose={() => setEditing(null)}
        />
      )}

      {acting && (
        <ActionForm
          action={acting.action}
          row={acting.row}
          slug={resource.slug}
          refreshes={resource.refreshes}
          onClose={() => setActing(null)}
        />
      )}

      {deleting && (
        <ConfirmDialog
          message="الحذف نهائي ومش هينفع ترجع فيه. تأكيد؟"
          confirmLabel="احذف"
          busy={remove.isPending}
          onCancel={() => setDeleting(null)}
          onConfirm={() =>
            remove.mutate(deleting.id as number, { onSuccess: () => setDeleting(null) })
          }
        />
      )}
    </div>
  )
}
