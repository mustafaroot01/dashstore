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

          <Endpoint method="GET" path="/categories" desc="الأقسام المتوفرة.">
            <div class="mt-3">
              <p class="text-slate-600 text-sm mb-1 font-semibold">Response Array:</p>
              <pre class="bg-slate-900 text-emerald-400 p-3 rounded-lg text-xs font-mono overflow-x-auto" dir="ltr">[
  {
    "id": 1,
    "name": "قناني مياه",
    "image": "https://domain.com/storage/categories/xxx.jpg"
  }
]</pre>
            </div>
          </Endpoint>

          <Endpoint method="GET" path="/products" desc="قائمة المنتجات (يدعم الفلترة).">
            <p class="text-slate-500 text-xs mt-1">مثال لفلترة قسم معين: <code>/api/products?category_id=1</code></p>
            <div class="mt-3">
              <p class="text-slate-600 text-sm mb-1 font-semibold">Response Array:</p>
              <pre class="bg-slate-900 text-emerald-400 p-3 rounded-lg text-xs font-mono overflow-x-auto" dir="ltr">[
  {
    "id": 1,
    "category_id": 1,
    "name": "كارتون ماء 330 مل",
    "description": "كارتون يحتوي على 20 قنينة",
    "size": "20 x 330ml",
    "price": "3500.00",
    "sale_price": "3000.00",        // إذا كان مخفضاً
    "is_on_sale": 1,                // 1 يعني أظهر sale_price
    "is_available": 1,              // 1 = متوفر , 0 = نفذت الكمية
    "images": [                     // Array of image objects
      { "id": 1, "url": "https://domain.com/storage/products/1.jpg" }
    ]
  }
]</pre>
            </div>
          </Endpoint>

          <Endpoint method="GET" path="/districts" desc="قائمة الأقضية (تُعرض كـ Dropdown عند إنشاء طلب).">
            <div class="mt-3">
              <p class="text-slate-600 text-sm mb-1 font-semibold">Response Array:</p>
              <pre class="bg-slate-900 text-emerald-400 p-3 rounded-lg text-xs font-mono overflow-x-auto max-w-md" dir="ltr">[
  { "id": 1, "name": "بعقوبة" },
  { "id": 2, "name": "الخالص" },
  { "id": 3, "name": "المقدادية" }
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
  "address": "حي المصطفى، قرب مدرسة النوارس"
}</pre>
              </div>
              <div>
                <p class="text-slate-600 text-sm mb-1 font-semibold">Response:</p>
                <pre class="bg-slate-900 text-emerald-400 p-3 rounded-lg text-xs font-mono overflow-x-auto" dir="ltr">{
  "success": true,
  "user": { ... updated user object ... }
}</pre>
              </div>
            </div>
          </Endpoint>
          
          <Endpoint method="POST" path="/coupons/validate" desc="التحقق من صحة كود الخصم (كوبون) قبل استخدامه في الطلب.">
            <div class="grid md:grid-cols-2 gap-4 mt-3">
              <div>
                <p class="text-slate-600 text-sm mb-1 font-semibold">Request Body:</p>
                <pre class="bg-slate-900 text-blue-300 p-3 rounded-lg text-xs font-mono overflow-x-auto" dir="ltr">{
  "code": "AMWAJ10"
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
    "value": "10.00"   // 10% خصم أو 10 آلاف عراقي خصم ثابت
  }
}</pre>
              </div>
            </div>
          </Endpoint>
          
          <Endpoint method="POST" path="/orders" desc="إرسال طلب شراء جديد.">
            <div class="grid md:grid-cols-2 gap-4 mt-3">
              <div>
                <p class="text-slate-600 text-sm mb-1 font-semibold">Request Body:</p>
                <pre class="bg-slate-900 text-amber-300 p-3 rounded-lg text-xs font-mono overflow-x-auto" dir="ltr">{
  "district_id": 1,
  "delivery_point": "قرب مدرسة الأندلس",
  "phone": "07701234567",
  "coupon_code": "AMWAJ10", // (اختياري) أرسله إذا تم التحقق منه مسبقاً
  "notes": "الرجاء الاتصال قبل الوصول",
  "items": [
    { "product_id": 5, "quantity": 2 },
    { "product_id": 1, "quantity": 5 }
  ]
}</pre>
              </div>
              <div>
                <p class="text-slate-600 text-sm mb-1 font-semibold">Response (Created):</p>
                <pre class="bg-slate-900 text-emerald-400 p-3 rounded-lg text-xs font-mono overflow-x-auto" dir="ltr">{
  "success": true,
  "order": {
    "id": 105,
    "invoice_number": "AW-10005",
    "status": "pending", // pending | received | preparing | delivering | delivered | rejected
    "total_price": "25000.00",
    "discount_amount": "2500.00",
    ...
  }
}</pre>
              </div>
            </div>
          </Endpoint>

          <Endpoint method="GET" path="/orders" desc="قائمة طلبات المستخدم السابقة والحالية.">
            <div class="mt-3">
              <p class="text-slate-600 text-sm mb-1 font-semibold">Response Array:</p>
              <pre class="bg-slate-900 text-emerald-400 p-3 rounded-lg text-xs font-mono overflow-x-auto" dir="ltr">[
  {
    "id": 105,
    "invoice_number": "AW-10005",
    "status": "pending", // pending | received | preparing | delivering | delivered | rejected
    "total_price": "22500.00",
    "created_at": "2026-02-25T14:30:00.000000Z",
    "items_count": 2 
  },
  ...
]</pre>
            </div>
          </Endpoint>

          <Endpoint method="GET" path="/orders/{id}" desc="تفاصيل طلب محدد لغرض عرض الفاتورة الدقيقة بالتطبيق.">
            <div class="mt-3">
              <p class="text-slate-600 text-sm mb-1 font-semibold">Response Object:</p>
              <pre class="bg-slate-900 text-emerald-400 p-3 rounded-lg text-xs font-mono overflow-x-auto" dir="ltr">{
  "id": 105,
  "invoice_number": "AW-10005",
  "status": "pending",           // pending | received | preparing | delivering | delivered | rejected
  "district": { "id": 1, "name": "بعقوبة" },
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
      "quantity": 2,
      "price": "5000.00",        // السعر وقت الطلب
      "product": { "name": "كارتون ماء...", "image_url": "..." }
    },
    ...
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
