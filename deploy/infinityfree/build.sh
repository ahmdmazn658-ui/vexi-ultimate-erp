#!/usr/bin/env bash
#
# ═══════════════════════════════════════════════════════════════
#  بناء مجلد جاهز للرفع على استضافة مشتركة (InfinityFree وغيرها)
# ═══════════════════════════════════════════════════════════════
#
#  الاستخدام:
#      ./deploy/infinityfree/build.sh
#
#  الناتج: مجلد `dist-infinityfree/` — محتواه بالظبط هو اللي بيتحط
#  جوه `htdocs` على السيرفر.
#
#  لازم يتشغّل من جهازك (مش من السيرفر) لأنه محتاج composer و npm،
#  والاتنين مش متوفرين على الاستضافة المشتركة.

set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
OUT="$ROOT/dist-infinityfree"
KIT="$ROOT/deploy/infinityfree"

echo "▶ التحقق من الأدوات المطلوبة"
for tool in php composer npm; do
    command -v "$tool" >/dev/null || { echo "✗ $tool مش متثبت."; exit 1; }
done

# ─────────────────────────────────────────────────────────────
echo "▶ بناء الواجهة"
cd "$ROOT/apps/frontend"

# الواجهة والـ API على نفس الدومين، فالمسار النسبي كفاية —
# ومفيش داعي لأي إعداد CORS.
VITE_API_BASE_URL="" npm run build

# ─────────────────────────────────────────────────────────────
echo "▶ تثبيت حزم الباك إند (إنتاج فقط)"
cd "$ROOT/apps/backend"
composer install --no-dev --optimize-autoloader --no-interaction

# ─────────────────────────────────────────────────────────────
echo "▶ تجميع المجلد"
rm -rf "$OUT"
mkdir -p "$OUT/erp-app"

# ملفات Laravel — من غير public (هتتفكك بره) ولا التطوير
for item in app bootstrap config database resources routes storage vendor artisan composer.json composer.lock; do
    [ -e "$ROOT/apps/backend/$item" ] && cp -R "$ROOT/apps/backend/$item" "$OUT/erp-app/"
done

# الكاش المولّد محليًا بيشاور على مسارات جهازك — لازم يتشال
rm -rf "$OUT/erp-app/bootstrap/cache"/*.php
find "$OUT/erp-app/storage" -type f \( -name '*.log' -o -name '*.php' \) \
    -path '*framework*' -delete 2>/dev/null || true

# مجلدات التخزين لازم تفضل موجودة وفاضية (git بيتجاهل الفاضي)
mkdir -p "$OUT/erp-app/bootstrap/cache" \
         "$OUT/erp-app/storage/app/private/documents" \
         "$OUT/erp-app/storage/app/public" \
         "$OUT/erp-app/storage/framework/cache/data" \
         "$OUT/erp-app/storage/framework/sessions" \
         "$OUT/erp-app/storage/framework/views" \
         "$OUT/erp-app/storage/logs"

# محتوى public بتاع Laravel (من غير index.php — عندنا نسخة معدّلة)
if [ -d "$ROOT/apps/backend/public" ]; then
    find "$ROOT/apps/backend/public" -mindepth 1 -maxdepth 1 \
        ! -name 'index.php' ! -name '.htaccess' \
        -exec cp -R {} "$OUT/" \;
fi

# ناتج بناء الواجهة
cp -R "$ROOT/apps/frontend/dist/." "$OUT/"

# ملفات الاستضافة المشتركة
cp "$KIT/htdocs-index.php" "$OUT/index.php"
cp "$KIT/htdocs.htaccess" "$OUT/.htaccess"
cp "$KIT/erp-app.htaccess" "$OUT/erp-app/.htaccess"
cp "$KIT/setup.php" "$OUT/setup.php"
cp "$KIT/.env.infinityfree" "$OUT/erp-app/.env.example"

# ─────────────────────────────────────────────────────────────
echo "▶ تقليل عدد الملفات (حد inode بتاع الاستضافة المجانية 30,000)"
# الاختبارات والتوثيق جوه vendor مش بتُستخدم وقت التشغيل، وبتاكل آلاف الملفات
find "$OUT/erp-app/vendor" -type d \
    \( -name 'tests' -o -name 'test' -o -name 'Tests' -o -name 'docs' -o -name '.github' \) \
    -prune -exec rm -rf {} + 2>/dev/null || true
find "$OUT/erp-app/vendor" -type f \
    \( -name '*.md' -o -name 'phpunit.xml*' -o -name '.gitignore' -o -name '.gitattributes' \) \
    -delete 2>/dev/null || true

# ─────────────────────────────────────────────────────────────
INODES=$(find "$OUT" | wc -l)
SIZE=$(du -sh "$OUT" | cut -f1)

echo
echo "═══════════════════════════════════════════════"
echo "  المجلد جاهز: dist-infinityfree/"
echo "  عدد الملفات: $INODES   (الحد المجاني 30,000)"
echo "  الحجم: $SIZE"
echo "═══════════════════════════════════════════════"

if [ "$INODES" -gt 28000 ]; then
    echo "⚠️  عدد الملفات قريب من الحد — راجع دليل النشر، قسم 'حد الملفات'."
fi

echo
echo "الخطوة اللي بعدها:"
echo "  1. جهّز erp-app/.env من erp-app/.env.example"
echo "  2. ارفع محتوى dist-infinityfree/ جوه htdocs بالـ FTP"
echo "  3. افتح /setup.php?token=...&action=migrate ثم action=seed"
echo "  4. امسح setup.php من السيرفر"
