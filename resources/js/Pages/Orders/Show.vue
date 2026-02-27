<template>
  <AdminLayout :title="'طلب #' + order.invoice_number">
    <!-- Topbar actions -->
    <div class="flex items-center gap-3 mb-6 no-print">
      <Link :href="route('admin.orders.index')" class="bg-white border border-slate-200 text-slate-600 px-4 py-2 rounded-xl text-sm font-bold hover:bg-slate-50 transition-all flex items-center gap-2 shadow-sm">
        ← الطلبات
      </Link>
      <button @click="printInvoice" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-sm font-bold flex items-center gap-2 transition-all shadow-lg shadow-blue-500/20 active:scale-95">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
        طباعة الفاتورة
      </button>
      <button @click="openEditModal" class="bg-indigo-50 border border-indigo-100 text-indigo-700 px-5 py-2.5 rounded-xl text-sm font-bold flex items-center gap-2 hover:bg-indigo-100 transition-all active:scale-95">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
        تعديل الطلب
      </button>
      <select :value="order.status" @change="updateStatus" class="form-select w-52 mr-auto">
        <option v-for="(s, key) in statuses" :key="key" :value="key">{{ s.label || s }}</option>
      </select>
    </div>

    <!-- ══════════════════════ INVOICE ═══════════════════════ -->
    <div id="invoice" class="bg-white rounded-xl shadow-[0_0_20px_rgba(0,0,0,0.02)] border border-slate-100 max-w-[800px] mx-auto p-4 md:p-10 relative overflow-hidden print:w-full print:shadow-none print:border-none print:p-0">
      <!-- Decorative Top Line for Print -->
      <div class="absolute top-0 left-0 w-full h-1.5 bg-primary-600 no-print"></div>
      <div class="hidden print:block absolute top-0 left-0 w-full h-2 bg-[#0ea5e9]"></div>

      <!-- Header Section -->
      <div class="flex justify-between items-end pb-6 border-b-2 border-slate-100 mb-8 mt-2">
        <div class="flex items-center gap-5">
          <div v-if="settings.invoice_logo" class="w-20 h-20 bg-slate-50 rounded-xl flex items-center justify-center p-2 border border-slate-100">
            <img :src="'/storage/' + settings.invoice_logo" class="w-full h-full object-contain" />
          </div>
          <div>
            <h1 class="font-extrabold text-2xl md:text-3xl text-slate-800 tracking-tight mb-1">{{ settings.invoice_name || 'أمواج ديالى' }}</h1>
            <div class="text-slate-500 font-medium text-sm flex flex-col gap-0.5">
              <p class="flex items-center gap-1.5"><svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg> <span dir="ltr">{{ settings.invoice_phone }}</span></p>
              <p class="flex items-center gap-1.5"><svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg> {{ settings.invoice_address }}</p>
            </div>
          </div>
        </div>
        
        <div class="text-left bg-slate-50/80 px-5 py-3 rounded-lg border border-slate-100">
          <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1.5 text-right">INVOICE NO.</p>
          <p class="font-bold text-3xl text-primary-700 font-mono tracking-tighter">{{ order.invoice_number }}</p>
          <p class="text-slate-500 text-sm mt-1 mb-2 font-medium print:text-[11px]">{{ new Date(order.created_at).toLocaleString('ar-IQ', {
              year: 'numeric', month: 'numeric', day: 'numeric', hour: 'numeric', minute: 'numeric'
          }) }}</p>
          <StatusBadge :status="order.status" :label="order.status_label" class="!text-[10px] print:hidden w-full text-center block" />
        </div>
      </div>

      <!-- Customer & Delivery Sub-header -->
      <div class="flex flex-col md:flex-row gap-6 mb-8 mt-2">
        <div class="flex-1 bg-blue-50/40 p-5 rounded-2xl border border-blue-50/60 relative overflow-hidden">
          <div class="absolute left-0 top-0 bottom-0 w-1 bg-blue-400 rounded-l-full print:bg-[#60a5fa] print:w-[3px]"></div>
          <p class="text-blue-400 text-xs font-bold uppercase tracking-wider mb-2 flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
            معلومات الزبون
          </p>
          <p class="font-extrabold text-slate-800 text-xl {{ order.user?.first_name.length > 20 ? 'text-lg' : '' }}">{{ order.user?.first_name }} {{ order.user?.last_name }}</p>
          <p class="text-slate-600 font-medium font-mono mt-1 text-sm tracking-widest" dir="ltr">{{ order.phone }}</p>
        </div>

        <div class="flex-1 bg-amber-50/40 p-5 rounded-2xl border border-amber-50/60 relative overflow-hidden">
          <div class="absolute left-0 top-0 bottom-0 w-1 bg-amber-400 rounded-l-full print:bg-[#fbbf24] print:w-[3px]"></div>
          <p class="text-amber-500 text-xs font-bold uppercase tracking-wider mb-2 flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            موقع التوصيل
          </p>
          <div class="flex flex-wrap items-center gap-1.5 mb-1.5">
            <span class="font-bold text-slate-800 text-lg">{{ order.governorate?.name || order.district?.governorate?.name }}</span>
            <span v-if="order.district_id" class="text-slate-400 font-bold">—</span>
            <span v-if="order.district_id" class="font-bold text-slate-800 text-lg">{{ order.district?.name }}</span>
          </div>
          <p class="text-slate-500 text-sm leading-relaxed max-w-[90%]">{{ order.delivery_point }}</p>
        </div>
      </div>

      <!-- Items Table -->
      <div class="rounded-xl overflow-hidden border border-slate-200 mb-8 ring-1 ring-slate-100 ring-offset-1 print:border-slate-300 print:shadow-none print:ring-0">
        <table class="w-full text-right bg-white">
          <thead class="bg-slate-100 border-b-2 border-slate-200 text-slate-600 print:bg-slate-100 print:table-header-group">
            <tr>
              <th class="px-5 py-3.5 print:py-2 print:px-2 text-sm print:text-xs font-bold w-12 text-center text-slate-400">#</th>
              <th class="px-5 py-3.5 print:py-2 print:px-2 text-sm print:text-xs font-bold">المنتج التفصيلي</th>
              <th class="px-5 py-3.5 print:py-2 print:px-2 text-sm print:text-xs font-bold text-center w-28 print:w-20">الكمية</th>
              <th class="px-5 py-3.5 print:py-2 print:px-2 text-sm print:text-xs font-bold tracking-tight text-left w-36 print:w-28">سعر الوحدة</th>
              <th class="px-5 py-3.5 print:py-2 print:px-2 text-sm print:text-xs font-bold tracking-tight text-left w-40 print:w-32 bg-slate-50/50 print:bg-transparent">المجموع</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 print:divide-slate-200">
            <tr v-for="(item, index) in order.items" :key="item.id" class="hover:bg-slate-50/40 transition-colors group print:break-inside-avoid">
              <td class="px-5 py-4 print:py-2 print:px-2 text-center text-slate-400 font-mono text-xs">{{ index + 1 }}</td>
              <td class="px-5 py-4 print:py-2 print:px-2">
                <div class="flex items-center gap-3 print:gap-2 w-max max-w-full">
                  <div v-if="item.product?.first_image" class="w-9 h-9 min-w-9 print:w-6 print:h-6 print:min-w-6 rounded-md border border-slate-200 bg-white p-0.5 shadow-sm group-hover:scale-105 transition-transform">
                    <img :src="item.product.first_image.url" class="w-full h-full object-cover rounded-sm" />
                  </div>
                  <div class="flex flex-col">
                    <span class="font-bold text-slate-800 text-[15px] print:text-[13px] leading-tight flex flex-wrap items-center gap-1.5">
                      {{ item.product?.name }}
                      <span v-if="item.product?.subcategory" class="text-[10px] bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded-md font-normal whitespace-nowrap border border-slate-200 print:bg-transparent print:border-none print:px-0">
                        ({{ item.product.subcategory.name }})
                      </span>
                    </span>
                    <span v-if="item.product?.sku" class="text-[10px] text-slate-400 font-mono mt-0.5 print:text-[9px]">
                      {{ item.product.sku }}
                    </span>
                    <span v-if="item.variant?.color || item.variant?.size" class="text-[11px] print:text-[10px] text-slate-500 font-medium mt-0.5 text-right w-full">
                      <template v-if="item.variant?.color">اللون: {{ item.variant.color }}</template>
                      <template v-if="item.variant?.color && item.variant?.size"> | </template>
                      <template v-if="item.variant?.size">المقاس: {{ item.variant.size }}</template>
                    </span>
                  </div>
                </div>
              </td>
              <td class="px-5 py-4 print:py-2 print:px-2">
                <div class="mx-auto w-10 print:w-auto text-center font-bold text-slate-700 bg-slate-50 print:bg-transparent rounded-lg py-1 border border-slate-100 print:border-none print:py-0">{{ item.quantity }}</div>
              </td>
              <td class="px-5 py-4 print:py-2 print:px-2 text-left font-medium tracking-tighter text-slate-500 font-mono text-sm print:text-xs">{{ fmt(item.price) }}</td>
              <td class="px-5 py-4 print:py-2 print:px-2 text-left font-bold text-slate-800 tracking-tighter font-mono bg-slate-50/30 print:bg-transparent print:text-xs">{{ fmt(item.price * item.quantity) }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Financial Summary & Notes -->
      <div class="flex flex-col md:flex-row gap-8 items-start justify-between">
        
        <!-- Order Notes (Left Side on LTR, Right Side visually since we are RTL) -->
        <div class="w-full md:w-1/2">
          <div v-if="order.notes" class="p-4 bg-slate-50 rounded-xl border border-slate-200 border-dashed text-slate-600">
            <span class="font-bold block mb-2 text-slate-700 text-sm">ملاحظات الطلبية:</span>
            <p class="text-sm leading-relaxed italic">{{ order.notes }}</p>
          </div>
        </div>

        <!-- Totals (Right Side on LTR, Left Side visually) -->
        <div class="w-full md:w-72 bg-slate-50 rounded-2xl p-5 border border-slate-200 shadow-sm print:shadow-none print:border-none print:bg-transparent print:p-0 print:break-inside-avoid">
          <div class="space-y-3.5 mb-4">
            <div class="flex justify-between items-center text-slate-600 text-sm">
              <span class="font-medium">المجموع الجزئي</span>
              <span class="font-bold font-mono">{{ fmt(subtotal) }}</span>
            </div>
            
            <div v-if="order.discount_amount > 0" class="flex justify-between items-center text-rose-600 bg-rose-50 px-2 py-1 -mx-2 rounded border border-rose-100/50 print:bg-transparent print:border-none print:px-0 print:mx-0">
              <span class="font-medium text-xs flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" /></svg>
                خصم {{ order.coupon?.code }}
              </span>
              <span class="font-bold font-mono">- {{ fmt(order.discount_amount) }}</span>
            </div>
          </div>
          
          <div class="flex justify-between items-end border-t border-slate-200/80 pt-4 print:border-slate-300">
            <span class="font-bold text-slate-800 text-sm">الإجمالي <span class="text-[10px] text-slate-400 font-normal block">المبلغ المستحق للدفع</span></span>
            <span class="font-black text-2xl text-primary-700 font-mono tracking-tighter">{{ fmt(order.total_price) }}</span>
          </div>
        </div>
      </div>

      <!-- Footer Signatures & QR -->
      <div class="mt-16 pt-6 border-t border-slate-200 flex items-center justify-between">
        <!-- QR Code smaller and sleeker -->
        <div class="text-center w-24 flex-shrink-0 relative group">
          <img :src="`https://api.qrserver.com/v1/create-qr-code/?data=${order.invoice_number}&size=120x120`"
            class="w-16 h-16 mx-auto border border-slate-200 p-0.5 rounded-lg bg-white group-hover:scale-110 transition-transform print:grayscale" alt="QR Code" />
          <p class="text-[9px] uppercase tracking-widest text-slate-400 mt-2 font-mono font-bold">{{ order.invoice_number }}</p>
        </div>
        
        <!-- Official Footer Info -->
        <div v-if="settings.invoice_footer" class="text-left text-slate-500 text-[11px] leading-relaxed max-w-[60%] print:text-black">
          <div class="flex flex-col gap-1 items-end">
             <div class="w-8 h-0.5 bg-slate-300 mb-1 rounded-full"></div>
             <p class="font-semibold">{{ settings.invoice_footer }}</p>
             <p class="text-slate-400 mt-2 text-[10px]">تم إنشاء هذه الوثيقة إلكترونياً ولا تحتاج إلى ختم حي.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Order Modal -->
    <Modal :show="showEditModal" @close="closeEditModal" max-width="lg">
      <div class="p-6">
        <h2 class="text-xl font-bold text-slate-800 mb-6 flex items-center gap-2">
          <svg class="w-6 h-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
          تعديل تفاصيل الطلب
        </h2>
        
        <form @submit.prevent="submitEdit">
          <div class="space-y-4">
            
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="form-label">المحافظة</label>
                <select v-model="form.governorate_id" class="form-input" required>
                  <option value="" disabled>اختر المحافظة...</option>
                  <option v-for="gov in governorates" :key="gov.id" :value="gov.id">{{ gov.name }}</option>
                </select>
                <p v-if="form.errors.governorate_id" class="text-red-500 text-xs mt-1">{{ form.errors.governorate_id }}</p>
              </div>
              
              <div>
                <label class="form-label">القضاء</label>
                <select v-model="form.district_id" class="form-input" :disabled="!form.governorate_id">
                  <option :value="null">بدون قضاء (اختياري)</option>
                  <option v-for="dist in availableDistricts" :key="dist.id" :value="dist.id">{{ dist.name }}</option>
                </select>
                <p v-if="form.errors.district_id" class="text-red-500 text-xs mt-1">{{ form.errors.district_id }}</p>
              </div>
            </div>

            <div>
              <label class="form-label">رقم الهاتف</label>
              <input v-model="form.phone" type="text" class="form-input font-mono min-w-full" dir="ltr" required />
              <p v-if="form.errors.phone" class="text-red-500 text-xs mt-1">{{ form.errors.phone }}</p>
            </div>

            <div>
              <label class="form-label">نقطة الدالة / التوصيل</label>
              <textarea v-model="form.delivery_point" rows="2" class="form-textarea" required></textarea>
              <p v-if="form.errors.delivery_point" class="text-red-500 text-xs mt-1">{{ form.errors.delivery_point }}</p>
            </div>

            <div>
              <label class="form-label">ملاحظات الطلب</label>
              <textarea v-model="form.notes" rows="3" class="form-textarea"></textarea>
              <p v-if="form.errors.notes" class="text-red-500 text-xs mt-1">{{ form.errors.notes }}</p>
            </div>

          </div>
          
          <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-slate-100">
            <button type="button" @click="closeEditModal" class="btn-secondary">إلغاء</button>
            <button type="submit" :disabled="form.processing" class="btn-primary flex items-center gap-2">
              <svg v-if="form.processing" class="animate-spin h-4 w-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
              حفظ التعديلات
            </button>
          </div>
        </form>
      </div>
    </Modal>

  </AdminLayout>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({ order: Object, settings: Object, statuses: Object, governorates: Array });

function fmt(v) { return Number(v ?? 0).toLocaleString('ar-IQ') + ' د.ع'; }

const subtotal = computed(() =>
  (props.order.items ?? []).reduce((s, i) => s + i.price * i.quantity, 0)
);

function updateStatus(e) {
  router.patch(route('admin.orders.status', props.order.id), { status: e.target.value }, { preserveScroll: true });
}

function printInvoice() {
  window.print();
}

// Edit functionality
const showEditModal = ref(false);

const form = useForm({
  governorate_id: props.order.governorate_id || props.order.district?.governorate_id || '',
  district_id: props.order.district_id || null,
  phone: props.order.phone,
  delivery_point: props.order.delivery_point,
  notes: props.order.notes || '',
});

const availableDistricts = computed(() => {
  const gov = (props.governorates || []).find(g => g.id === form.governorate_id);
  return gov ? gov.districts : [];
});

watch(() => form.governorate_id, (newVal, oldVal) => {
  if (oldVal && newVal !== oldVal) {
    form.district_id = null; // reset district when governorate changes
  }
});

function openEditModal() {
  form.governorate_id = props.order.governorate_id || props.order.district?.governorate_id || '';
  form.district_id = props.order.district_id || null;
  form.phone = props.order.phone;
  form.delivery_point = props.order.delivery_point;
  form.notes = props.order.notes || '';
  showEditModal.value = true;
}

function closeEditModal() {
  showEditModal.value = false;
  form.clearErrors();
}

function submitEdit() {
  form.put(route('admin.orders.update', props.order.id), {
    preserveScroll: true,
    onSuccess: () => closeEditModal(),
  });
}
</script>
