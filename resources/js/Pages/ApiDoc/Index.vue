<template>
  <AdminLayout title="توثيق الـ API الشامل">
    <div class="max-w-5xl space-y-8 pb-12">
      <!-- Intro -->
      <div class="card bg-gradient-to-l from-primary-600 to-indigo-700 text-white">
        <div class="flex items-center gap-4 mb-2">
          <div class="bg-white/20 p-3 rounded-xl flex-shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
            </svg>
          </div>
          <div>
            <h2 class="text-2xl font-bold">التوثيق البرمجي المفصل (API Documentation)</h2>
            <p class="text-primary-100 mt-1 text-sm leading-relaxed">
              هذا الدليل مخصص لمطور تطبيق الموبايل (Flutter Developer). يحتوي على التفاصيل الدقيقة لكل مسار (Endpoint) مع أمثلة للطلبات (Requests) والاستجابات (Responses). <br>
              الرابط الأساسي لجميع المسارات: 
              <code class="bg-black/30 px-2 py-0.5 rounded font-mono text-amber-300 mx-1" dir="ltr">{{ baseUrl }}/api</code>
            </p>
          </div>
        </div>
      </div>

      <!-- 1. Auth Group -->
      <div class="card p-0 overflow-hidden shadow-sm">
        <div class="bg-slate-50 border-b border-slate-200 p-4 font-bold text-slate-800 flex items-center gap-2 text-lg">
          🔐 1. المصادقة والدخول (Authentication)
        </div>
        <div class="p-4">
          
          <Endpoint method="POST" path="/auth/send-otp" desc="إرسال كود OTP للمستخدم (عبر واتساب أو رسالة نصية).">
            <div class="grid md:grid-cols-2 gap-4 mt-3">
              <div>
                <p class="text-slate-600 text-sm mb-1 font-semibold">Request Body:</p>
                <pre class="bg-slate-900 text-blue-300 p-3 rounded-lg text-xs font-mono overflow-x-auto" dir="ltr">{
  "phone": "07701234567"  // رقم الهاتف العراقي
}</pre>
              </div>
              <div>
                <p class="text-slate-600 text-sm mb-1 font-semibold">Response (200 OK):</p>
                <pre class="bg-slate-900 text-emerald-400 p-3 rounded-lg text-xs font-mono overflow-x-auto" dir="ltr">{
  "success": true,
  "message": "تم إرسال رمز التحقق",
  "channel": "whatsapp", // أو "sms"
  "data": { ... } // تفاصيل من مزود الخدمة
}</pre>
              </div>
            </div>
          </Endpoint>

          <Endpoint method="POST" path="/auth/verify-otp" desc="التحقق من الكود (ينقسم لفرعين: مستخدم جديد، أو مستخدم مسجل مسبقاً).">
            <div class="mt-3 space-y-4">
              <div>
                <p class="text-slate-600 text-sm mb-1 font-semibold">Request Body:</p>
                <pre class="bg-slate-900 text-blue-300 p-3 rounded-lg text-xs font-mono overflow-x-auto max-w-md" dir="ltr">{
  "phone": "07701234567",
  "code": "123456"
}</pre>
              </div>
              <div class="grid md:grid-cols-2 gap-4">
                <div>
                  <p class="text-slate-600 text-sm mb-1 font-semibold">Response A (مستخدم جديد - يجب نقله لصفحة التسجيل):</p>
                  <pre class="bg-slate-900 text-amber-300 p-3 rounded-lg text-xs font-mono overflow-x-auto" dir="ltr">{
  "success": true,
  "is_new": true,
  "phone": "07701234567"
}</pre>
                </div>
                <div>
                  <p class="text-slate-600 text-sm mb-1 font-semibold">Response B (مستخدم مسجل - تم الدخول بنجاح):</p>
                  <pre class="bg-slate-900 text-emerald-400 p-3 rounded-lg text-xs font-mono overflow-x-auto" dir="ltr">{
  "success": true,
  "is_new": false,
  "token": "1|abcdef1234...",
  "user": {
    "id": 1,
    "first_name": "أحمد",
    "last_name": "علي",
    "phone": "07701234567",
    "gender": "male",
    "address": "حي المصطفى"
  }
}</pre>
                </div>
              </div>
            </div>
          </Endpoint>

          <Endpoint method="POST" path="/auth/register" desc="إكمال تسجيل مستخدم جديد (بعد التأكد من الكود).">
            <div class="grid md:grid-cols-2 gap-4 mt-3">
              <div>
                <p class="text-slate-600 text-sm mb-1 font-semibold">Request Body:</p>
                <pre class="bg-slate-900 text-blue-300 p-3 rounded-lg text-xs font-mono overflow-x-auto" dir="ltr">{
  "phone": "07701234567",
  "first_name": "أحمد",
  "last_name": "علي",
  "gender": "male",        // "male" | "female"
  "governorate_id": 1,     // ID من الـ dropdown
  "district_id": 2,        // ID بناءً على المحافظة
  "address": "حي المعلمين قرب مدرسة النور"
}</pre>
              </div>
              <div>
                <p class="text-slate-600 text-sm mb-1 font-semibold">Response (201 Created):</p>
                <pre class="bg-slate-900 text-emerald-400 p-3 rounded-lg text-xs font-mono overflow-x-auto" dir="ltr">{
  "success": true,
  "token": "2|xyz987...",
  "user": {
    "id": 2,
    "first_name": "أحمد",
    ...
  }
}</pre>
              </div>
            </div>
          </Endpoint>

          <Endpoint method="DELETE" path="/auth/delete-account" desc="حذف حساب المستخدم بشكل نهائي (🛡️ يتطلب Token)">
            <p class="text-slate-500 text-xs mt-1">يجب إرسال <code>Authorization: Bearer {token}</code> في الـ Header.</p>
            <div class="mt-3">
              <p class="text-slate-600 text-sm mb-1 font-semibold">Response (200 OK):</p>
              <pre class="bg-slate-900 text-emerald-400 p-3 rounded-lg text-xs font-mono overflow-x-auto max-w-md" dir="ltr">{
  "success": true
}</pre>
            </div>
          </Endpoint>
        </div>
      </div>

      <!-- 2. General Content Group -->
      <div class="card p-0 overflow-hidden shadow-sm">
        <div class="bg-slate-50 border-b border-slate-200 p-4 font-bold text-slate-800 flex items-center gap-2 text-lg">
          📱 2. محتوى التطبيق العام (بدون Token)
        </div>
        <div class="p-4">
          
          <Endpoint method="GET" path="/sliders" desc="السلايدات للواجهة الرئيسية.">
            <div class="mt-3">
              <p class="text-slate-600 text-sm mb-1 font-semibold">Response Array:</p>
              <pre class="bg-slate-900 text-emerald-400 p-3 rounded-lg text-xs font-mono overflow-x-auto" dir="ltr">[
  {
    "id": 1,
    "title": "عرض خاص",
    "image": "https://domain.com/storage/sliders/xxx.jpg",
    "link_type": "product", // null | "category" | "product" | "external"
    "link": null,           // for explicit URLs
    "category_id": null,
    "product_id": 5
  },
  ...
]</pre>
            </div>
          </Endpoint>

          <Endpoint method="GET" path="/categories" desc="الأقسام المتوفرة مع أصنافها الفرعية.">
            <div class="mt-3">
              <p class="text-slate-600 text-sm mb-1 font-semibold">Response Array:</p>
              <pre class="bg-slate-900 text-emerald-400 p-3 rounded-lg text-xs font-mono overflow-x-auto" dir="ltr">[
  {
    "id": 1,
    "name": "قناني مياه",
    "image": "https://domain.com/storage/categories/xxx.jpg",
    "subcategories": [
      {
        "id": 1,
        "name": "مياه معبأة",
        "image": "https://domain.com/storage/subcategories/yyy.jpg"
      }
    ]
  }
]</pre>
            </div>
          </Endpoint>

          <Endpoint method="GET" path="/products" desc="قائمة المنتجات النشطة (يدعم البحث والفلترة). المنتجات النافذة تظهر مع is_available=false.">
            <p class="text-slate-500 text-xs mt-1">مثال: <code>/api/products?search=ماك&amp;category_id=1&amp;subcategory_id=2</code></p>
            <div class="mt-3">
              <p class="text-slate-600 text-sm mb-1 font-semibold">Response Object (مع pagination):</p>
              <pre class="bg-slate-900 text-emerald-400 p-3 rounded-lg text-xs font-mono overflow-x-auto" dir="ltr">{
  "success": true,
  "data": [
    {
      "id": 1,
      "category_id": 1,
      "subcategory_id": 2,
      "name": "كارتون ماء 330 مل",
      "sku": "AW-50012",
      "price": "3500.00",
      "sale_price": "3000.00",
      "is_on_sale": true,
      "effective_price": 3000.0,    // السعر الفعلي (بعد الخصم أو بدونه)
      "is_available": true,         // ⚠️ false = نفذت الكمية, يجب إظهار badge وتعطيل السلة
      "total_stock": 45,            // ⚠️ مجموع المخزون من جميع المتغيرات — استخدمه كحد أقصى للكمية
      "thumbnail": "https://domain.com/storage/products/xxx.jpg"
    }
  ],
  "meta": { "current_page": 1, "last_page": 3, "total": 60 }
}</pre>
              <div class="mt-3 p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-800">
                <strong>⚠️ تعليمات حرجة للمطور:</strong>
                <ul class="list-disc list-inside space-y-1 mt-1">
                  <li>إذا <code>is_available === false</code>: أظهر badge "نفذ" وعطّل زر الإضافة للسلة</li>
                  <li>استخدم <code>total_stock</code> كحد أقصى للكمية في السلة (لا تسمح بالتجاوز)</li>
                  <li>في تفاصيل المنتج: استخدم <code>variants[i].stock</code> لكل متغير بشكل منفصل</li>
                </ul>
              </div>
            </div>
          </Endpoint>

          <Endpoint method="GET" path="/districts" desc="سحب المحافظات والأقضية المرتبطة بها (لعرضها كقوائم منسدلة).">
            <div class="mt-3">
              <p class="text-slate-600 text-sm mb-1 font-semibold">Response Array (Governorates with their districts):</p>
              <pre class="bg-slate-900 text-emerald-400 p-3 rounded-lg text-xs font-mono overflow-x-auto max-w-md" dir="ltr">[
  { 
    "id": 1, 
    "name": "ديالى",
    "districts": [
      { "id": 1, "name": "بعقوبة" },
      { "id": 2, "name": "الخالص" }
    ]
  },
  { 
    "id": 2, 
    "name": "بغداد",
    "districts": [ ... ]
  }
]</pre>
            </div>
          </Endpoint>

          <Endpoint method="GET" path="/privacy-policy" desc="نص سياسة الخصوصية.">
            <div class="mt-3">
              <p class="text-slate-600 text-sm mb-1 font-semibold">Response Object:</p>
              <pre class="bg-slate-900 text-emerald-400 p-3 rounded-lg text-xs font-mono overflow-x-auto max-w-md" dir="ltr">{
  "content": "نحن في أمواج ديالى نهتم بخصوصيتك...\n\n- الاسم\n- العنوان"
}</pre>
            </div>
          </Endpoint>

        </div>
      </div>

      <!-- 3. Protected User Group -->
      <div class="card p-0 overflow-hidden shadow-sm">
        <div class="bg-slate-50 border-b border-orange-200 p-4 font-bold text-slate-800 flex items-center gap-2 text-lg">
          🛡️ 3. مسارات المستخدم والطلبات (تتطلب Bearer Token)
        </div>
        <div class="p-4 bg-orange-50 border-b border-orange-100 text-orange-900 text-sm font-medium">
          ⚠️ <strong>ملاحظة هامة جداً:</strong> يجب إرسال الهيدر التالي في جميع مسارات هذا القسم: <br>
          <code class="bg-orange-200 px-3 py-1 rounded-md text-orange-900 mt-2 inline-block shadow-sm" dir="ltr">Authorization: Bearer {token_here}</code>
        </div>
        <div class="p-4">
          
          <Endpoint method="GET" path="/user/profile" desc="جلب بيانات المستخدم الحالي.">
            <div class="mt-3">
              <p class="text-slate-600 text-sm mb-1 font-semibold">Response Object:</p>
              <pre class="bg-slate-900 text-emerald-400 p-3 rounded-lg text-xs font-mono overflow-x-auto" dir="ltr">{
  "id": 1,
  "first_name": "أحمد",
  "last_name": "علي",
  "phone": "07701234567",
  "gender": "male",
  "address": "حي المصطفى"
}</pre>
            </div>
          </Endpoint>

          <Endpoint method="PUT" path="/user/profile" desc="تحديث بيانات الملف الشخصي.">
            <div class="grid md:grid-cols-2 gap-4 mt-3">
              <div>
                <p class="text-slate-600 text-sm mb-1 font-semibold">Request Body:</p>
                <pre class="bg-slate-900 text-blue-300 p-3 rounded-lg text-xs font-mono overflow-x-auto" dir="ltr">{
  "first_name": "أحمد",
  "last_name": "علي حسن",
  "gender": "male",
  "governorate_id": 1,
  "district_id": 2,
  "address": "حي المصطفى، قرب مدرسة النوارس"
}</pre>
              </div>
              <div>
                <p class="text-slate-600 text-sm mb-1 font-semibold">Response:</p>
                <pre class="bg-slate-900 text-emerald-400 p-3 rounded-lg text-xs font-mono overflow-x-auto" dir="ltr">{
  "success": true,
  "user": { "id": 1, "first_name": "أحمد", ... }
}</pre>
              </div>
            </div>
          </Endpoint>
          
          <Endpoint method="POST" path="/coupons/validate" desc="التحقق من صحة كود الخصم (كوبون) قبل استخدامه في الطلب. يجب إرسال subtotal للحساب الدقيق.">
            <div class="grid md:grid-cols-2 gap-4 mt-3">
              <div>
                <p class="text-slate-600 text-sm mb-1 font-semibold">Request Body:</p>
                <pre class="bg-slate-900 text-blue-300 p-3 rounded-lg text-xs font-mono overflow-x-auto" dir="ltr">{
  "code": "AMWAJ10",
  "subtotal": 25000  // (مطلوب) المجموع قبل الخصم لحساب قيمة الخصم
}</pre>
              </div>
              <div>
                <p class="text-slate-600 text-sm mb-1 font-semibold">Response (Success):</p>
                <pre class="bg-slate-900 text-emerald-400 p-3 rounded-lg text-xs font-mono overflow-x-auto" dir="ltr">{
  "success": true,
  "coupon": {
    "id": 1,
    "code": "AMWAJ10",
    "type": "percent", // "percent" | "fixed"
    "value": "10.00",
    "discount": 2500   // القيمة الفعلية المخصومة بالدينار
  }
}</pre>
              </div>
            </div>
          </Endpoint>
          
          <Endpoint method="POST" path="/orders" desc="إرسال طلب شراء جديد (يتطلب Bearer Token). جميع المنتجات يجب أن تحتوي على product_variant_id.">
            <div class="grid md:grid-cols-2 gap-4 mt-3">
              <div>
                <p class="text-slate-600 text-sm mb-1 font-semibold">Request Body:</p>
                <pre class="bg-slate-900 text-amber-300 p-3 rounded-lg text-xs font-mono overflow-x-auto" dir="ltr">{
  "governorate_id": 1,    // (مطلوب) ID المحافظة
  "district_id": 2,       // (اختياري) ID القضاء
  "delivery_point": "قرب مدرسة الأندلس",
  "phone": "07701234567",
  "coupon_id": 3,         // (اختياري) ID الكوبون بعد التحقق منه
  "notes": "الرجاء الاتصال قبل الوصول",
  "items": [
    {
      "product_id": 5,
      "product_variant_id": 10, // (مطلوب)
      "quantity": 2
    }
  ]
}</pre>
              </div>
              <div>
                <p class="text-slate-600 text-sm mb-1 font-semibold">Response (201 Created):</p>
                <pre class="bg-slate-900 text-emerald-400 p-3 rounded-lg text-xs font-mono overflow-x-auto" dir="ltr">{
  "success": true,
  "message": "تم إنشاء الطلب بنجاح",
  "data": {
    "id": 105,
    "invoice_number": "WEB-ABC123",
    "status": "pending",
    "status_label": "قيد الانتظار",
    "total_price": "25000.00"
  }
}</pre>
              </div>
            </div>
          </Endpoint>

          <Endpoint method="GET" path="/orders" desc="قائمة طلبات المستخدم السابقة والحالية (مع pagination).">
            <div class="mt-3">
              <p class="text-slate-600 text-sm mb-1 font-semibold">Response (200 OK):</p>
              <pre class="bg-slate-900 text-emerald-400 p-3 rounded-lg text-xs font-mono overflow-x-auto" dir="ltr">{
  "success": true,
  "data": [
    {
      "id": 105,
      "invoice_number": "WEB-ABC123",
      "status": "pending",
      "status_label": "قيد الانتظار",
      "total_price": "22500.00",
      "discount_amount": "2500.00",
      "delivery_point": "قرب مدرسة الأندلس",
      "phone": "07701234567",
      "governorate": "ديالى",
      "district": "بعقوبة",
      "created_at": "2026-02-25T14:30:00.000Z",
      "items_count": 2,
      "items": [
        {
          "id": 210,
          "quantity": 2,
          "price": "5000.00",
          "name": "كارتون ماء 330 مل",
          "thumbnail": "https://domain.com/storage/products/xxx.jpg",
          "variant": "أحمر L" // null إذا لا يوجد متغير
        }
      ]
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 3,
    "total": 55
  }
}</pre>
            </div>
          </Endpoint>

          <Endpoint method="GET" path="/orders/{id}" desc="تفاصيل طلب محدد لغرض عرض الفاتورة الدقيقة بالتطبيق.">
            <div class="mt-3">
              <p class="text-slate-600 text-sm mb-1 font-semibold">Response Object:</p>
              <pre class="bg-slate-900 text-emerald-400 p-3 rounded-lg text-xs font-mono overflow-x-auto" dir="ltr">{
  "id": 105,
  "invoice_number": "AW-10005",
  "status": "pending",           // pending | received | preparing | delivering | delivered | rejected
  "district": { 
    "id": 1, 
    "name": "بعقوبة", 
    "governorate": { "id": 1, "name": "ديالى" }
  },
  "delivery_point": "قرب مدرسة الأندلس",
  "phone": "07701234567",
  "total_price": "22500.00",     // السعر النهائي بعد الخصم
  "discount_amount": "2500.00",  // قيمة الخصم لو وجد كوبون
  "notes": "الرجاء الاتصال...",
  "created_at": "2026-02-25T14:30:00.000000Z",
  "items": [
    {
      "id": 210,
      "product_id": 5,
      "product_variant_id": 10,
      "quantity": 2,
      "price": "5000.00",        // السعر وقت الطلب
      "product": { "name": "كارتون ماء...", "sku": "SKU-xyz", "image_url": "..." },
      "variant": { "color": "أحمر", "size": "L" }
    }
  ]
}</pre>
            </div>
          </Endpoint>
        </div>
      </div>

    </div>
  </AdminLayout>
</template>

<script setup>
import { computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Endpoint from './Endpoint.vue';

const baseUrl = computed(() => window.location.origin);
</script>
