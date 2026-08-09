.DEFAULT_GOAL := help
.PHONY: help install dev-api dev-web migrate seed fresh test verify build build-shared lint clean

BACKEND  := apps/backend
FRONTEND := apps/frontend

help: ## عرض الأوامر المتاحة
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) \
		| awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-14s\033[0m %s\n", $$1, $$2}'

install: ## تثبيت حزم الباك إند والفرونت إند
	cd $(BACKEND) && composer install
	cd $(FRONTEND) && npm install

dev-api: ## تشغيل الـ API على http://localhost:8000
	cd $(BACKEND) && php artisan serve

dev-web: ## تشغيل الواجهة على http://localhost:5173
	cd $(FRONTEND) && npm run dev

migrate: ## تشغيل المايجريشن
	cd $(BACKEND) && php artisan migrate

seed: ## تعبئة البيانات الابتدائية (شجرة الحسابات + مستخدم admin)
	cd $(BACKEND) && php artisan db:seed

fresh: ## إعادة بناء قاعدة البيانات من الصفر — بتمسح كل البيانات
	cd $(BACKEND) && php artisan migrate:fresh --seed

test: ## اختبارات PHPUnit
	cd $(BACKEND) && php artisan test

verify: ## فحوصات الدورة المالية المستقلة (SQLite، من غير Laravel)
	php scripts/verify/accounting-cycle.php
	php scripts/verify/year-end-closing.php

lint: ## فحص تنسيق الباك إند وأنواع الفرونت إند
	cd $(BACKEND) && ./vendor/bin/pint --test
	cd $(FRONTEND) && npm run lint

build: ## بناء الواجهة للإنتاج
	cd $(FRONTEND) && npm run build

build-shared: ## تجهيز مجلد رفع لاستضافة مشتركة (InfinityFree)
	./deploy/infinityfree/build.sh

clean: ## حذف ناتج البناء والحزم
	rm -rf $(FRONTEND)/dist $(FRONTEND)/node_modules $(BACKEND)/vendor dist-infinityfree
