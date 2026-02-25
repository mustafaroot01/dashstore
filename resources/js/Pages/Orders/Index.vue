<template>
  <AdminLayout title="الطلبات">
    <!-- Filters -->
    <div class="flex flex-wrap items-center gap-3 mb-6">
      <input v-model="f.search" type="text" placeholder="رقم فاتورة / اسم زبون..."
        class="form-input w-56" @input="doFilter" />
      <select v-model="f.status" class="form-select w-44" @change="doFilter">
        <option value="">كل الحالات</option>
        <option v-for="(s, key) in statuses" :key="key" :value="key">{{ s.label }}</option>
      </select>
      <input v-model="f.date" type="date" class="form-input w-44" @change="doFilter" />
      <span class="text-slate-400 text-sm mr-auto">{{ orders.total }} طلب</span>
    </div>

    <!-- Table -->
    <div class="card overflow-hidden p-0">
      <!-- Added min-h-[300px] and pb-20 to ensure dropdowns don't get cut off by overflow-x-auto padding bounds -->
      <div class="overflow-x-auto min-h-[350px] pb-32">
        <table class="data-table">
          <thead>
            <tr>
              <th style="width:50px">#</th>
              <th>رقم الفاتورة</th>
              <th>الزبون</th>
              <th>القضاء</th>
              <th>المبلغ</th>
              <th>الحالة</th>
              <th>التاريخ</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(order, index) in orders.data" :key="order.id">
              <!-- # -->
              <td class="text-slate-400 text-xs font-mono text-center">
                {{ (orders.current_page - 1) * orders.per_page + index + 1 }}
              </td>
              <!-- Invoice -->
              <td>
                <span class="font-mono font-bold text-primary-700 tracking-wide">
                  {{ order.invoice_number }}
                </span>
              </td>
              <!-- Customer -->
              <td class="font-medium text-slate-800">
                {{ order.user?.first_name }} {{ order.user?.last_name }}
              </td>
              <!-- District -->
              <td class="text-slate-500 text-sm">{{ order.district?.name }}</td>
              <!-- Amount -->
              <td class="font-semibold text-slate-800">{{ fmt(order.total_price) }}</td>

              <!-- Status Badge + Dropdown -->
              <td>
                <div class="relative inline-block">
                  <!-- Badge -->
                  <button @click.stop="openDropdown = openDropdown === order.id ? null : order.id"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold border transition-all"
                    :class="statuses[order.status]?.cls ?? 'bg-slate-100 text-slate-600 border-slate-200'">
                    <span class="w-1.5 h-1.5 rounded-full" :class="statuses[order.status]?.dot"></span>
                    {{ statuses[order.status]?.label ?? order.status }}
                    <svg class="w-3 h-3 opacity-60" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                  </button>

                  <!-- Dropdown -->
                  <div v-if="openDropdown === order.id"
                    class="absolute z-50 top-full mt-1.5 right-0 bg-white rounded-xl shadow-xl border border-slate-100 w-52 py-2">
                    <p class="text-[10px] text-slate-400 font-semibold px-3 pb-1.5 uppercase tracking-wider border-b border-slate-50 mb-1">
                      تغيير الحالة
                    </p>
                    <button v-for="(s, key) in statuses" :key="key"
                      @click.stop="changeStatus(order, key)"
                      class="w-full flex items-center gap-3 px-3 py-2 hover:bg-slate-50 transition-colors text-sm text-right">
                      <span class="w-2 h-2 rounded-full flex-shrink-0" :class="s.dot"></span>
                      <span class="flex-1" :class="s.text">{{ s.label }}</span>
                      <svg v-if="order.status === key" class="w-4 h-4 text-primary-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                      </svg>
                    </button>
                  </div>
                </div>
              </td>

              <!-- Date -->
              <td class="text-slate-400 text-xs whitespace-nowrap">
                {{ new Date(order.created_at).toLocaleDateString('ar-IQ') }}
              </td>

              <!-- Details -->
              <td>
                <Link :href="route('admin.orders.show', order.id)"
                  class="btn-secondary py-1.5 px-3 text-xs">
                  📋 تفاصيل
                </Link>
              </td>
            </tr>
          </tbody>
        </table>
        <p v-if="!orders.data.length" class="text-center text-slate-400 text-sm py-10">لا توجد طلبات</p>
      </div>

      <!-- Pagination -->
      <div v-if="orders.last_page > 1"
        class="flex justify-between items-center px-5 py-3 border-t border-slate-100">
        <span class="text-slate-500 text-sm">
          {{ orders.from }}–{{ orders.to }} من {{ orders.total }} طلب
        </span>
        <div class="flex gap-1">
          <Link v-for="page in orders.links" :key="page.label" :href="page.url ?? '#'"
            class="px-3 py-1.5 rounded-lg text-sm transition"
            :class="page.active ? 'bg-primary-600 text-white font-semibold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'">
            <span v-html="page.label" />
          </Link>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { reactive, ref, onMounted, onUnmounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({ orders: Object, filters: Object });

// Status definitions — used for the badge AND filter select
const statuses = {
  pending:    { label: 'قيد الانتظار',    cls: 'bg-amber-50 text-amber-700 border-amber-200',       dot: 'bg-amber-400',   text: 'text-amber-700' },
  received:   { label: 'تم استلام الطلب', cls: 'bg-blue-50 text-blue-700 border-blue-200',          dot: 'bg-blue-400',    text: 'text-blue-700' },
  preparing:  { label: 'جاري التجهيز',    cls: 'bg-violet-50 text-violet-700 border-violet-200',   dot: 'bg-violet-400',  text: 'text-violet-700' },
  delivering: { label: 'جاري التوصيل',    cls: 'bg-cyan-50 text-cyan-700 border-cyan-200',          dot: 'bg-cyan-400',    text: 'text-cyan-700' },
  delivered:  { label: 'تم التسليم',      cls: 'bg-emerald-50 text-emerald-700 border-emerald-200', dot: 'bg-emerald-400', text: 'text-emerald-700' },
  rejected:   { label: 'تم رفض الطلب',    cls: 'bg-rose-50 text-rose-700 border-rose-200',          dot: 'bg-rose-500',    text: 'text-rose-700' },
};

const f = reactive({
  search: props.filters?.search ?? '',
  status: props.filters?.status ?? '',
  date:   props.filters?.date   ?? '',
});

const openDropdown = ref(null);

let filterTimer;
function doFilter() {
  clearTimeout(filterTimer);
  filterTimer = setTimeout(() => {
    router.get(route('admin.orders.index'), f, { preserveState: true });
  }, 400);
}

function changeStatus(order, status) {
  openDropdown.value = null;
  if (order.status === status) return;
  router.patch(route('admin.orders.status', order.id), { status }, { preserveScroll: true });
}

function fmt(v) { return Number(v ?? 0).toLocaleString('ar-IQ') + ' د.ع'; }

// Close dropdown when clicking outside
function onClickOutside() { openDropdown.value = null; }
onMounted(()   => document.addEventListener('click', onClickOutside));
onUnmounted(() => document.removeEventListener('click', onClickOutside));
</script>
