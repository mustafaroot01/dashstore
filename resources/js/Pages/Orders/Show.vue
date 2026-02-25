<template>
  <AdminLayout :title="'طلب #' + order.invoice_number">
    <!-- Topbar actions -->
    <div class="flex items-center gap-3 mb-6 no-print">
      <Link :href="route('admin.orders.index')" class="btn-secondary">
        ← الطلبات
      </Link>
      <button @click="printInvoice" class="btn-primary">
        🖨️ طباعة الفاتورة
      </button>
      <select :value="order.status" @change="updateStatus" class="form-select w-52">
        <option v-for="(s, key) in statuses" :key="key" :value="key">{{ s }}</option>
      </select>
    </div>

    <!-- ══════════════════════ INVOICE ═══════════════════════ -->
    <div id="invoice" class="bg-white rounded-2xl shadow-sm border border-slate-100 max-w-3xl mx-auto p-8">

      <!-- Invoice Header -->
      <div class="flex items-start justify-between border-b pb-6 mb-6">
        <div>
          <img v-if="settings.invoice_logo" :src="'/storage/' + settings.invoice_logo"
            class="h-14 mb-3 object-contain" />
          <h1 class="font-bold text-xl text-slate-800">{{ settings.invoice_name || 'أمواج ديالى' }}</h1>
          <p class="text-slate-500 text-sm">{{ settings.invoice_phone }}</p>
          <p class="text-slate-500 text-sm">{{ settings.invoice_address }}</p>
        </div>
        <div class="text-left">
          <p class="text-slate-500 text-sm mb-1">رقم الفاتورة</p>
          <p class="font-bold text-2xl text-primary-700 font-mono">{{ order.invoice_number }}</p>
          <p class="text-slate-500 text-sm mt-2">{{ new Date(order.created_at).toLocaleString('ar') }}</p>
          <StatusBadge :status="order.status" :label="order.status_label" class="mt-2" />
        </div>
      </div>

      <!-- Customer Info -->
      <div class="grid grid-cols-2 gap-4 bg-slate-50 rounded-xl p-4 mb-6">
        <div>
          <p class="text-xs text-slate-400 mb-1">اسم الزبون</p>
          <p class="font-semibold text-slate-800">{{ order.user?.first_name }} {{ order.user?.last_name }}</p>
          <p class="text-slate-500 text-sm">{{ order.phone }}</p>
        </div>
        <div>
          <p class="text-xs text-slate-400 mb-1">موقع التوصيل</p>
          <p class="font-semibold text-slate-800">{{ order.district?.name }}</p>
          <p class="text-slate-500 text-sm">{{ order.delivery_point }}</p>
        </div>
      </div>

      <!-- Items Table -->
      <table class="data-table mb-6">
        <thead>
          <tr>
            <th>المنتج</th>
            <th class="text-center">الكمية</th>
            <th class="text-left">سعر الوحدة</th>
            <th class="text-left">المجموع</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in order.items" :key="item.id">
            <td>
              <div class="flex items-center gap-2">
                <img v-if="item.product?.first_image" :src="item.product.first_image.url"
                  class="w-8 h-8 rounded-lg object-cover" />
                <span>{{ item.product?.name }}</span>
              </div>
            </td>
            <td class="text-center">{{ item.quantity }}</td>
            <td class="text-left">{{ fmt(item.price) }}</td>
            <td class="text-left font-semibold">{{ fmt(item.price * item.quantity) }}</td>
          </tr>
        </tbody>
      </table>

      <!-- Totals -->
      <div class="border-t pt-4 space-y-1.5">
        <div class="flex justify-between text-slate-600 text-sm">
          <span>المجموع الجزئي</span>
          <span>{{ fmt(subtotal) }}</span>
        </div>
        <div v-if="order.discount_amount > 0" class="flex justify-between text-red-600 text-sm">
          <span>الخصم ({{ order.coupon?.code }})</span>
          <span>- {{ fmt(order.discount_amount) }}</span>
        </div>
        <div class="flex justify-between font-bold text-lg text-slate-800 border-t pt-2">
          <span>الإجمالي</span>
          <span>{{ fmt(order.total_price) }}</span>
        </div>
      </div>

      <!-- Notes -->
      <div v-if="order.notes" class="mt-4 p-3 bg-amber-50 rounded-lg text-sm text-amber-700">
        <span class="font-medium">ملاحظات: </span>{{ order.notes }}
      </div>

      <!-- QR Code -->
      <div class="mt-6 flex items-center justify-center">
        <div class="text-center">
          <img :src="`https://api.qrserver.com/v1/create-qr-code/?data=${order.invoice_number}&size=100x100`"
            class="w-24 h-24 mx-auto" alt="QR Code" />
          <p class="text-xs text-slate-400 mt-1">{{ order.invoice_number }}</p>
        </div>
      </div>

      <!-- Footer -->
      <p v-if="settings.invoice_footer" class="text-center text-slate-400 text-sm mt-6 pt-4 border-t">
        {{ settings.invoice_footer }}
      </p>
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';

const props = defineProps({ order: Object, settings: Object, statuses: Object });

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
</script>
