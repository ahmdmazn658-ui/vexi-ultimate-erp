import React, { useEffect, useState } from 'react';
import { moduleSettingGroups } from '../resources/settings';

interface SettingField {
  type: string;
  label_ar: string;
  label_en: string;
  options?: string[];
  default: any;
  value: any;
}

type ModuleSettings = Record<string, Record<string, SettingField>>;

export default function SettingsPage() {
  const [activeModule, setActiveModule] = useState<string>('accounting');
  const [activeGroup, setActiveGroup] = useState<string>('general');
  const [settings, setSettings] = useState<ModuleSettings>({});
  const [loading, setLoading] = useState(false);
  const [saving, setSaving] = useState(false);

  const modules = Object.entries(moduleSettingGroups);

  useEffect(() => {
    loadModuleSettings(activeModule);
  }, [activeModule]);

  async function loadModuleSettings(module: string) {
    setLoading(true);
    try {
      const res = await fetch(`/api/v1/settings/${module}`, {
        headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
      });
      const data = await res.json();
      setSettings(data.settings || {});
      const groups = moduleSettingGroups[module as keyof typeof moduleSettingGroups]?.groups;
      if (groups?.length) setActiveGroup(groups[0].key);
    } catch (e) {
      console.error(e);
    } finally {
      setLoading(false);
    }
  }

  async function saveSettings() {
    setSaving(true);
    try {
      const payload: Record<string, Record<string, any>> = {};
      for (const [group, fields] of Object.entries(settings)) {
        payload[group] = {};
        for (const [key, field] of Object.entries(fields)) {
          payload[group][key] = field.value;
        }
      }

      await fetch(`/api/v1/settings/${activeModule}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${localStorage.getItem('token')}`,
        },
        body: JSON.stringify({ settings: payload }),
      });
    } catch (e) {
      console.error(e);
    } finally {
      setSaving(false);
    }
  }

  function updateField(group: string, key: string, value: any) {
    setSettings((prev) => ({
      ...prev,
      [group]: {
        ...prev[group],
        [key]: { ...prev[group][key], value },
      },
    }));
  }

  function renderField(group: string, key: string, field: SettingField) {
    switch (field.type) {
      case 'boolean':
        return (
          <label key={key} className="flex items-center justify-between py-3 border-b">
            <span className="text-sm">{field.label_ar}</span>
            <input
              type="checkbox"
              checked={!!field.value}
              onChange={(e) => updateField(group, key, e.target.checked)}
              className="w-5 h-5 text-blue-600"
            />
          </label>
        );
      case 'string':
        if (field.options) {
          return (
            <div key={key} className="py-3 border-b">
              <label className="text-sm block mb-1">{field.label_ar}</label>
              <select
                value={field.value || ''}
                onChange={(e) => updateField(group, key, e.target.value)}
                className="w-full border rounded p-2"
              >
                {field.options.map((opt) => (
                  <option key={opt} value={opt}>{opt}</option>
                ))}
              </select>
            </div>
          );
        }
        return (
          <div key={key} className="py-3 border-b">
            <label className="text-sm block mb-1">{field.label_ar}</label>
            <input
              type="text"
              value={field.value || ''}
              onChange={(e) => updateField(group, key, e.target.value)}
              className="w-full border rounded p-2"
              placeholder={field.label_en}
            />
          </div>
        );
      case 'integer':
      case 'float':
        return (
          <div key={key} className="py-3 border-b">
            <label className="text-sm block mb-1">{field.label_ar}</label>
            <input
              type="number"
              value={field.value ?? ''}
              step={field.type === 'float' ? '0.01' : '1'}
              onChange={(e) => updateField(group, key, Number(e.target.value))}
              className="w-full border rounded p-2"
            />
          </div>
        );
      default:
        return (
          <div key={key} className="py-3 border-b">
            <label className="text-sm block mb-1">{field.label_ar}</label>
            <textarea
              value={typeof field.value === 'string' ? field.value : JSON.stringify(field.value)}
              onChange={(e) => updateField(group, key, e.target.value)}
              className="w-full border rounded p-2"
              rows={2}
            />
          </div>
        );
    }
  }

  const currentModuleConfig = moduleSettingGroups[activeModule as keyof typeof moduleSettingGroups];
  const currentGroupFields = settings[activeGroup] || {};

  return (
    <div className="flex h-full" dir="rtl">
      {/* Sidebar - Module List */}
      <div className="w-64 border-l bg-gray-50 overflow-y-auto">
        <h2 className="p-4 font-bold text-lg">⚙️ الإعدادات</h2>
        {modules.map(([key, config]) => (
          <button
            key={key}
            onClick={() => setActiveModule(key)}
            className={`w-full text-right p-3 hover:bg-gray-100 flex items-center gap-2 ${
              activeModule === key ? 'bg-blue-50 border-l-4 border-blue-500 font-bold' : ''
            }`}
          >
            <span>{config.icon}</span>
            <span className="text-sm">{config.labelAr}</span>
          </button>
        ))}
      </div>

      {/* Main Content */}
      <div className="flex-1 overflow-y-auto">
        <div className="p-6">
          <div className="flex items-center justify-between mb-6">
            <h1 className="text-2xl font-bold">
              {currentModuleConfig?.icon} {currentModuleConfig?.labelAr}
            </h1>
            <button
              onClick={saveSettings}
              disabled={saving}
              className="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 disabled:opacity-50"
            >
              {saving ? 'جاري الحفظ...' : '💾 حفظ الإعدادات'}
            </button>
          </div>

          {/* Group Tabs */}
          <div className="flex gap-2 mb-6 border-b pb-2">
            {currentModuleConfig?.groups.map((g) => (
              <button
                key={g.key}
                onClick={() => setActiveGroup(g.key)}
                className={`px-4 py-2 rounded-t-lg text-sm ${
                  activeGroup === g.key
                    ? 'bg-blue-600 text-white'
                    : 'bg-gray-100 hover:bg-gray-200'
                }`}
              >
                {g.labelAr}
              </button>
            ))}
          </div>

          {/* Settings Fields */}
          {loading ? (
            <div className="text-center py-10">جاري التحميل...</div>
          ) : (
            <div className="max-w-2xl">
              {Object.entries(currentGroupFields).map(([key, field]) =>
                renderField(activeGroup, key, field)
              )}
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
