import{A as a}from"./AdminLayout-DOrr8jPw.js";import n from"./Endpoint-C2jEXaqX.js";import{c as r,w as s,a as t,i as l,t as i,j as o,p as m,o as p}from"./app-IK5CPhZk.js";/* empty css            */const u={class:"max-w-5xl space-y-8 pb-12"},x={class:"card bg-gradient-to-l from-primary-600 to-indigo-700 text-white"},c={class:"flex items-center gap-4 mb-2"},g={class:"text-primary-100 mt-1 text-sm leading-relaxed"},b={class:"bg-black/30 px-2 py-0.5 rounded font-mono text-amber-300 mx-1",dir:"ltr"},f={class:"card p-0 overflow-hidden shadow-sm"},v={class:"p-4"},_={class:"card p-0 overflow-hidden shadow-sm"},w={class:"p-4"},h={class:"card p-0 overflow-hidden shadow-sm"},y={class:"p-4"},O={__name:"Index",setup(R){const d=m(()=>window.location.origin);return(k,e)=>(p(),r(a,{title:"توثيق الـ API الشامل"},{default:s(()=>[t("div",u,[t("div",x,[t("div",c,[e[4]||(e[4]=t("div",{class:"bg-white/20 p-3 rounded-xl flex-shrink-0"},[t("svg",{xmlns:"http://www.w3.org/2000/svg",class:"h-8 w-8 text-white",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor"},[t("path",{"stroke-linecap":"round","stroke-linejoin":"round","stroke-width":"2",d:"M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"})])],-1)),t("div",null,[e[3]||(e[3]=t("h2",{class:"text-2xl font-bold"},"التوثيق البرمجي المفصل (API Documentation)",-1)),t("p",g,[e[0]||(e[0]=l(" هذا الدليل مخصص لمطور تطبيق الموبايل (Flutter Developer). يحتوي على التفاصيل الدقيقة لكل مسار (Endpoint) مع أمثلة للطلبات (Requests) والاستجابات (Responses). ",-1)),e[1]||(e[1]=t("br",null,null,-1)),e[2]||(e[2]=l(" الرابط الأساسي لجميع المسارات: ",-1)),t("code",b,i(d.value)+"/api",1)])])])]),t("div",f,[e[9]||(e[9]=t("div",{class:"bg-slate-50 border-b border-slate-200 p-4 font-bold text-slate-800 flex items-center gap-2 text-lg"}," 🔐 1. المصادقة والدخول (Authentication) ",-1)),t("div",v,[o(n,{method:"POST",path:"/auth/send-otp",desc:"إرسال كود OTP للمستخدم (عبر واتساب أو رسالة نصية)."},{default:s(()=>[...e[5]||(e[5]=[t("div",{class:"grid md:grid-cols-2 gap-4 mt-3"},[t("div",null,[t("p",{class:"text-slate-600 text-sm mb-1 font-semibold"},"Request Body:"),t("pre",{class:"bg-slate-900 text-blue-300 p-3 rounded-lg text-xs font-mono overflow-x-auto",dir:"ltr"},`{
  "phone": "07701234567"  // رقم الهاتف العراقي
}`)]),t("div",null,[t("p",{class:"text-slate-600 text-sm mb-1 font-semibold"},"Response (200 OK):"),t("pre",{class:"bg-slate-900 text-emerald-400 p-3 rounded-lg text-xs font-mono overflow-x-auto",dir:"ltr"},`{
  "success": true,
  "message": "تم إرسال رمز التحقق",
  "channel": "whatsapp", // أو "sms"
  "data": { ... } // تفاصيل من مزود الخدمة
}`)])],-1)])]),_:1}),o(n,{method:"POST",path:"/auth/verify-otp",desc:"التحقق من الكود (ينقسم لفرعين: مستخدم جديد، أو مستخدم مسجل مسبقاً)."},{default:s(()=>[...e[6]||(e[6]=[t("div",{class:"mt-3 space-y-4"},[t("div",null,[t("p",{class:"text-slate-600 text-sm mb-1 font-semibold"},"Request Body:"),t("pre",{class:"bg-slate-900 text-blue-300 p-3 rounded-lg text-xs font-mono overflow-x-auto max-w-md",dir:"ltr"},`{
  "phone": "07701234567",
  "code": "123456"
}`)]),t("div",{class:"grid md:grid-cols-2 gap-4"},[t("div",null,[t("p",{class:"text-slate-600 text-sm mb-1 font-semibold"},"Response A (مستخدم جديد - يجب نقله لصفحة التسجيل):"),t("pre",{class:"bg-slate-900 text-amber-300 p-3 rounded-lg text-xs font-mono overflow-x-auto",dir:"ltr"},`{
  "success": true,
  "is_new": true,
  "phone": "07701234567"
}`)]),t("div",null,[t("p",{class:"text-slate-600 text-sm mb-1 font-semibold"},"Response B (مستخدم مسجل - تم الدخول بنجاح):"),t("pre",{class:"bg-slate-900 text-emerald-400 p-3 rounded-lg text-xs font-mono overflow-x-auto",dir:"ltr"},`{
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
}`)])])],-1)])]),_:1}),o(n,{method:"POST",path:"/auth/register",desc:"إكمال تسجيل مستخدم جديد (بعد التأكد من الكود)."},{default:s(()=>[...e[7]||(e[7]=[t("div",{class:"grid md:grid-cols-2 gap-4 mt-3"},[t("div",null,[t("p",{class:"text-slate-600 text-sm mb-1 font-semibold"},"Request Body:"),t("pre",{class:"bg-slate-900 text-blue-300 p-3 rounded-lg text-xs font-mono overflow-x-auto",dir:"ltr"},`{
  "phone": "07701234567",
  "first_name": "أحمد",
  "last_name": "علي",
  "gender": "male",        // "male" | "female"
  "governorate_id": 1,     // ID من الـ dropdown
  "district_id": 2,        // ID بناءً على المحافظة
  "address": "حي المعلمين قرب مدرسة النور"
}`)]),t("div",null,[t("p",{class:"text-slate-600 text-sm mb-1 font-semibold"},"Response (201 Created):"),t("pre",{class:"bg-slate-900 text-emerald-400 p-3 rounded-lg text-xs font-mono overflow-x-auto",dir:"ltr"},`{
  "success": true,
  "token": "2|xyz987...",
  "user": {
    "id": 2,
    "first_name": "أحمد",
    ...
  }
}`)])],-1)])]),_:1}),o(n,{method:"DELETE",path:"/auth/delete-account",desc:"حذف حساب المستخدم بشكل نهائي (🛡️ يتطلب Token)"},{default:s(()=>[...e[8]||(e[8]=[t("p",{class:"text-slate-500 text-xs mt-1"},[l("يجب إرسال "),t("code",null,"Authorization: Bearer {token}"),l(" في الـ Header.")],-1),t("div",{class:"mt-3"},[t("p",{class:"text-slate-600 text-sm mb-1 font-semibold"},"Response (200 OK):"),t("pre",{class:"bg-slate-900 text-emerald-400 p-3 rounded-lg text-xs font-mono overflow-x-auto max-w-md",dir:"ltr"},`{
  "success": true
}`)],-1)])]),_:1})])]),t("div",_,[e[15]||(e[15]=t("div",{class:"bg-slate-50 border-b border-slate-200 p-4 font-bold text-slate-800 flex items-center gap-2 text-lg"}," 📱 2. محتوى التطبيق العام (بدون Token) ",-1)),t("div",w,[o(n,{method:"GET",path:"/sliders",desc:"السلايدات للواجهة الرئيسية."},{default:s(()=>[...e[10]||(e[10]=[t("div",{class:"mt-3"},[t("p",{class:"text-slate-600 text-sm mb-1 font-semibold"},"Response Array:"),t("pre",{class:"bg-slate-900 text-emerald-400 p-3 rounded-lg text-xs font-mono overflow-x-auto",dir:"ltr"},`[
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
]`)],-1)])]),_:1}),o(n,{method:"GET",path:"/categories",desc:"الأقسام المتوفرة مع أصنافها الفرعية."},{default:s(()=>[...e[11]||(e[11]=[t("div",{class:"mt-3"},[t("p",{class:"text-slate-600 text-sm mb-1 font-semibold"},"Response Array:"),t("pre",{class:"bg-slate-900 text-emerald-400 p-3 rounded-lg text-xs font-mono overflow-x-auto",dir:"ltr"},`[
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
]`)],-1)])]),_:1}),o(n,{method:"GET",path:"/products",desc:"قائمة المنتجات النشطة (يدعم البحث والفلترة). المنتجات النافذة تظهر مع is_available=false."},{default:s(()=>[...e[12]||(e[12]=[t("p",{class:"text-slate-500 text-xs mt-1"},[l("مثال: "),t("code",null,"/api/products?search=ماك&category_id=1&subcategory_id=2")],-1),t("div",{class:"mt-3"},[t("p",{class:"text-slate-600 text-sm mb-1 font-semibold"},"Response Object (مع pagination):"),t("pre",{class:"bg-slate-900 text-emerald-400 p-3 rounded-lg text-xs font-mono overflow-x-auto",dir:"ltr"},`{
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
}`),t("div",{class:"mt-3 p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-800"},[t("strong",null,"⚠️ تعليمات حرجة للمطور:"),t("ul",{class:"list-disc list-inside space-y-1 mt-1"},[t("li",null,[l("إذا "),t("code",null,"is_available === false"),l(': أظهر badge "نفذ" وعطّل زر الإضافة للسلة')]),t("li",null,[l("استخدم "),t("code",null,"total_stock"),l(" كحد أقصى للكمية في السلة (لا تسمح بالتجاوز)")]),t("li",null,[l("في تفاصيل المنتج: استخدم "),t("code",null,"variants[i].stock"),l(" لكل متغير بشكل منفصل")])])])],-1)])]),_:1}),o(n,{method:"GET",path:"/districts",desc:"سحب المحافظات والأقضية المرتبطة بها (لعرضها كقوائم منسدلة)."},{default:s(()=>[...e[13]||(e[13]=[t("div",{class:"mt-3"},[t("p",{class:"text-slate-600 text-sm mb-1 font-semibold"},"Response Array (Governorates with their districts):"),t("pre",{class:"bg-slate-900 text-emerald-400 p-3 rounded-lg text-xs font-mono overflow-x-auto max-w-md",dir:"ltr"},`[
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
]`)],-1)])]),_:1}),o(n,{method:"GET",path:"/privacy-policy",desc:"نص سياسة الخصوصية."},{default:s(()=>[...e[14]||(e[14]=[t("div",{class:"mt-3"},[t("p",{class:"text-slate-600 text-sm mb-1 font-semibold"},"Response Object:"),t("pre",{class:"bg-slate-900 text-emerald-400 p-3 rounded-lg text-xs font-mono overflow-x-auto max-w-md",dir:"ltr"},`{
  "content": "نحن في أمواج ديالى نهتم بخصوصيتك...\\n\\n- الاسم\\n- العنوان"
}`)],-1)])]),_:1})])]),t("div",h,[e[22]||(e[22]=t("div",{class:"bg-slate-50 border-b border-orange-200 p-4 font-bold text-slate-800 flex items-center gap-2 text-lg"}," 🛡️ 3. مسارات المستخدم والطلبات (تتطلب Bearer Token) ",-1)),e[23]||(e[23]=t("div",{class:"p-4 bg-orange-50 border-b border-orange-100 text-orange-900 text-sm font-medium"},[l(" ⚠️ "),t("strong",null,"ملاحظة هامة جداً:"),l(" يجب إرسال الهيدر التالي في جميع مسارات هذا القسم: "),t("br"),t("code",{class:"bg-orange-200 px-3 py-1 rounded-md text-orange-900 mt-2 inline-block shadow-sm",dir:"ltr"},"Authorization: Bearer {token_here}")],-1)),t("div",y,[o(n,{method:"GET",path:"/user/profile",desc:"جلب بيانات المستخدم الحالي."},{default:s(()=>[...e[16]||(e[16]=[t("div",{class:"mt-3"},[t("p",{class:"text-slate-600 text-sm mb-1 font-semibold"},"Response Object:"),t("pre",{class:"bg-slate-900 text-emerald-400 p-3 rounded-lg text-xs font-mono overflow-x-auto",dir:"ltr"},`{
  "id": 1,
  "first_name": "أحمد",
  "last_name": "علي",
  "phone": "07701234567",
  "gender": "male",
  "address": "حي المصطفى"
}`)],-1)])]),_:1}),o(n,{method:"PUT",path:"/user/profile",desc:"تحديث بيانات الملف الشخصي."},{default:s(()=>[...e[17]||(e[17]=[t("div",{class:"grid md:grid-cols-2 gap-4 mt-3"},[t("div",null,[t("p",{class:"text-slate-600 text-sm mb-1 font-semibold"},"Request Body:"),t("pre",{class:"bg-slate-900 text-blue-300 p-3 rounded-lg text-xs font-mono overflow-x-auto",dir:"ltr"},`{
  "first_name": "أحمد",
  "last_name": "علي حسن",
  "gender": "male",
  "governorate_id": 1,
  "district_id": 2,
  "address": "حي المصطفى، قرب مدرسة النوارس"
}`)]),t("div",null,[t("p",{class:"text-slate-600 text-sm mb-1 font-semibold"},"Response:"),t("pre",{class:"bg-slate-900 text-emerald-400 p-3 rounded-lg text-xs font-mono overflow-x-auto",dir:"ltr"},`{
  "success": true,
  "user": { "id": 1, "first_name": "أحمد", ... }
}`)])],-1)])]),_:1}),o(n,{method:"POST",path:"/coupons/validate",desc:"التحقق من صحة كود الخصم (كوبون) قبل استخدامه في الطلب. يجب إرسال subtotal للحساب الدقيق."},{default:s(()=>[...e[18]||(e[18]=[t("div",{class:"grid md:grid-cols-2 gap-4 mt-3"},[t("div",null,[t("p",{class:"text-slate-600 text-sm mb-1 font-semibold"},"Request Body:"),t("pre",{class:"bg-slate-900 text-blue-300 p-3 rounded-lg text-xs font-mono overflow-x-auto",dir:"ltr"},`{
  "code": "AMWAJ10",
  "subtotal": 25000  // (مطلوب) المجموع قبل الخصم لحساب قيمة الخصم
}`)]),t("div",null,[t("p",{class:"text-slate-600 text-sm mb-1 font-semibold"},"Response (Success):"),t("pre",{class:"bg-slate-900 text-emerald-400 p-3 rounded-lg text-xs font-mono overflow-x-auto",dir:"ltr"},`{
  "success": true,
  "coupon": {
    "id": 1,
    "code": "AMWAJ10",
    "type": "percent", // "percent" | "fixed"
    "value": "10.00",
    "discount": 2500   // القيمة الفعلية المخصومة بالدينار
  }
}`)])],-1)])]),_:1}),o(n,{method:"POST",path:"/orders",desc:"إرسال طلب شراء جديد (يتطلب Bearer Token). جميع المنتجات يجب أن تحتوي على product_variant_id."},{default:s(()=>[...e[19]||(e[19]=[t("div",{class:"grid md:grid-cols-2 gap-4 mt-3"},[t("div",null,[t("p",{class:"text-slate-600 text-sm mb-1 font-semibold"},"Request Body:"),t("pre",{class:"bg-slate-900 text-amber-300 p-3 rounded-lg text-xs font-mono overflow-x-auto",dir:"ltr"},`{
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
}`)]),t("div",null,[t("p",{class:"text-slate-600 text-sm mb-1 font-semibold"},"Response (201 Created):"),t("pre",{class:"bg-slate-900 text-emerald-400 p-3 rounded-lg text-xs font-mono overflow-x-auto",dir:"ltr"},`{
  "success": true,
  "message": "تم إنشاء الطلب بنجاح",
  "data": {
    "id": 105,
    "invoice_number": "WEB-ABC123",
    "status": "pending",
    "status_label": "قيد الانتظار",
    "total_price": "25000.00"
  }
}`)])],-1)])]),_:1}),o(n,{method:"GET",path:"/orders",desc:"قائمة طلبات المستخدم السابقة والحالية (مع pagination)."},{default:s(()=>[...e[20]||(e[20]=[t("div",{class:"mt-3"},[t("p",{class:"text-slate-600 text-sm mb-1 font-semibold"},"Response (200 OK):"),t("pre",{class:"bg-slate-900 text-emerald-400 p-3 rounded-lg text-xs font-mono overflow-x-auto",dir:"ltr"},`{
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
}`)],-1)])]),_:1}),o(n,{method:"GET",path:"/orders/{id}",desc:"تفاصيل طلب محدد لغرض عرض الفاتورة الدقيقة بالتطبيق."},{default:s(()=>[...e[21]||(e[21]=[t("div",{class:"mt-3"},[t("p",{class:"text-slate-600 text-sm mb-1 font-semibold"},"Response Object:"),t("pre",{class:"bg-slate-900 text-emerald-400 p-3 rounded-lg text-xs font-mono overflow-x-auto",dir:"ltr"},`{
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
}`)],-1)])]),_:1})])])])]),_:1}))}};export{O as default};
