<template>
  <AdminLayout title="سجل الجرد والتقارير">
    <div class="max-w-7xl mx-auto space-y-6 pb-12">
      
      <!-- Header Area & Filters -->
      <div class="card bg-slate-900 border-0 text-white shadow-xl relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-blue-600/20 to-transparent pointer-events-none"></div>
        <div class="relative flex flex-col items-start gap-6">
          <div class="flex items-center gap-4">
            <div class="bg-white/10 p-3 rounded-2xl flex-shrink-0">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
            <div>
              <h2 class="text-2xl font-bold font-mono">الجرد والتقارير التفصيلية</h2>
              <p class="text-slate-400 mt-1 text-sm">احصائيات شاملة للفترة المحددة مع إمكانية التصدير</p>
            </div>
          </div>

          <!-- Date Filters -->
          <form @submit.prevent="applyFilters" class="w-full bg-white/5 p-4 rounded-xl border border-white/10 flex flex-col sm:flex-row items-end gap-4">
            <div class="flex-1 w-full">
              <label class="block text-xs font-semibold text-slate-300 mb-1">من تاريخ</label>
              <input type="date" v-model="filterForm.start_date" class="form-input bg-slate-800 border-slate-700 text-white w-full rounded-lg px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
            </div>
            <div class="flex-1 w-full">
              <label class="block text-xs font-semibold text-slate-300 mb-1">إلى تاريخ</label>
              <input type="date" v-model="filterForm.end_date" class="form-input bg-slate-800 border-slate-700 text-white w-full rounded-lg px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto mt-2 sm:mt-0">
              <button type="submit" class="btn bg-blue-600 hover:bg-blue-500 text-white border-none w-full sm:w-auto px-6" :disabled="isLoading">
                {{ isLoading ? 'جاري الفلترة...' : 'عرض التقارير' }}
              </button>
              <button type="button" @click="exportCSV" class="btn bg-emerald-600 hover:bg-emerald-500 text-white border-none shrink-0 flex items-center justify-center gap-2 px-4 shadow-lg shadow-emerald-900/20" title="تصدير إلى Excel/CSV">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                تصدير
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Stats Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Revenue -->
        <div class="card p-6 flex flex-col justify-center relative overflow-hidden group shadow-sm hover:shadow-md transition-shadow">
          <div class="absolute -left-6 -top-6 w-24 h-24 bg-emerald-50 rounded-full group-hover:scale-150 transition-transform duration-500 z-0"></div>
          <div class="relative z-10">
            <div class="flex items-center justify-between mb-2">
              <h3 class="text-slate-500 font-semibold text-sm">إجمالي الإيرادات</h3>
              <div class="bg-emerald-100 p-2 rounded-xl">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
              </div>
            </div>
            <p class="text-3xl font-bold font-mono text-slate-800" dir="ltr">{{ Number(stats.total_revenue).toLocaleString() }} <span class="text-sm font-normal text-slate-500">د.ع</span></p>
          </div>
        </div>

        <!-- Net Profit (Same as revenue for now) -->
        <div class="card p-6 flex flex-col justify-center relative overflow-hidden group shadow-sm hover:shadow-md transition-shadow">
          <div class="absolute -left-6 -top-6 w-24 h-24 bg-blue-50 rounded-full group-hover:scale-150 transition-transform duration-500 z-0"></div>
          <div class="relative z-10">
            <div class="flex items-center justify-between mb-2">
              <h3 class="text-slate-500 font-semibold text-sm">صافي الأرباح</h3>
              <div class="bg-blue-100 p-2 rounded-xl">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
              </div>
            </div>
            <p class="text-3xl font-bold font-mono text-slate-800" dir="ltr">{{ Number(stats.net_profit).toLocaleString() }} <span class="text-sm font-normal text-slate-500">د.ع</span></p>
          </div>
        </div>

        <!-- Delivered Orders -->
        <div class="card p-6 flex flex-col justify-center relative overflow-hidden group shadow-sm hover:shadow-md transition-shadow">
          <div class="absolute -left-6 -top-6 w-24 h-24 bg-green-50 rounded-full group-hover:scale-150 transition-transform duration-500 z-0"></div>
          <div class="relative z-10">
            <div class="flex items-center justify-between mb-2">
              <h3 class="text-slate-500 font-semibold text-sm">الطلبات المستلمة (الناجحة)</h3>
              <div class="bg-green-100 p-2 rounded-xl">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
              </div>
            </div>
            <p class="text-3xl font-bold font-mono text-slate-800">{{ stats.total_delivered_count }} <span class="text-sm font-normal text-slate-500">طلب</span></p>
          </div>
        </div>

        <!-- Rejected Orders -->
        <div class="card p-6 flex flex-col justify-center relative overflow-hidden group shadow-sm hover:shadow-md transition-shadow">
          <div class="absolute -left-6 -top-6 w-24 h-24 bg-red-50 rounded-full group-hover:scale-150 transition-transform duration-500 z-0"></div>
          <div class="relative z-10">
            <div class="flex items-center justify-between mb-2">
              <h3 class="text-slate-500 font-semibold text-sm">الطلبات المرفوضة/الملغاة</h3>
              <div class="bg-red-100 p-2 rounded-xl">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
              </div>
            </div>
            <p class="text-3xl font-bold font-mono text-slate-800">{{ stats.total_rejected_count }} <span class="text-sm font-normal text-slate-500">طلب</span></p>
          </div>
        </div>
      </div>

      <!-- Detailed Table -->
      <div class="card p-0 overflow-hidden shadow-sm">
        <div class="p-5 border-b border-slate-100 bg-white flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
          <div class="flex items-center gap-3">
            <h3 class="font-bold text-slate-800 text-lg">سجل الطلبات للفترة المحددة</h3>
            <span class="text-xs font-semibold bg-slate-100 text-slate-600 px-3 py-1 rounded-full">إجمالي الريكوردات: {{ orders.total }}</span>
          </div>

          <!-- Column Toggles -->
          <div class="relative group">
            <button class="btn bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 text-sm flex items-center gap-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
              تخصيص الأعمدة
            </button>
            <div class="absolute left-0 mt-2 w-48 bg-white border border-slate-100 rounded-xl shadow-lg shadow-slate-200/50 p-3 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-20">
              <p class="text-xs font-bold text-slate-400 mb-2 pb-2 border-b border-slate-100">إظهار / إخفاء</p>
              <div class="space-y-2">
                <label v-for="(col, key) in columns" :key="key" class="flex items-center gap-2 cursor-pointer group/label">
                  <input type="checkbox" v-model="col.visible" class="rounded text-blue-600 focus:ring-blue-500 border-slate-300">
                  <span class="text-sm font-medium text-slate-600 group-hover/label:text-blue-600 transition-colors">{{ col.label }}</span>
                </label>
              </div>
            </div>
          </div>
        </div>
        
        <div class="overflow-x-auto">
          <table class="w-full text-right bg-white min-w-[800px]">
            <thead class="bg-slate-50 border-b border-slate-100 text-slate-500">
              <tr>
                <th class="p-4 font-bold rounded-tr-xl w-16 text-center">#</th>
                <th v-if="columns.orderNumber.visible" class="p-4 font-bold">رقم الفاتورة</th>
                <th v-if="columns.date.visible" class="p-4 font-bold">التاريخ</th>
                <th v-if="columns.customer.visible" class="p-4 font-bold">الزبون</th>
                <th v-if="columns.address.visible" class="p-4 font-bold">العنوان</th>
                <th v-if="columns.total.visible" class="p-4 font-bold">المبلغ النهائي</th>
                <th v-if="columns.status.visible" class="p-4 font-bold rounded-tl-xl text-center">الحالة</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100/80">
              <tr v-if="orders.data.length === 0">
                <td :colspan="visibleColumnsCount + 1" class="p-8 text-center text-slate-500">
                  لا توجد طلبات في هذه الفترة الزمنية.
                </td>
              </tr>
              <tr v-for="(order, index) in orders.data" :key="order.id" class="hover:bg-slate-50/50 transition duration-150">
                <!-- Sequence number: calculated based on current page and index -->
                <td class="p-4 text-center cursor-default text-slate-400 font-mono text-sm leading-none">
                  {{ (orders.current_page - 1) * orders.per_page + index + 1 }}
                </td>
                <td v-if="columns.orderNumber.visible" class="p-4 font-bold text-slate-800 font-mono">{{ order.order_number }}</td>
                <td v-if="columns.date.visible" class="p-4 text-slate-500 text-sm font-mono" dir="ltr">{{ order.created_at }}</td>
                <td v-if="columns.customer.visible" class="p-4">
                  <div class="font-bold text-slate-800">{{ order.customer_name }}</div>
                  <div class="text-xs text-slate-500 font-mono mt-0.5" dir="ltr">{{ order.customer_phone }}</div>
                </td>
                <td v-if="columns.address.visible" class="p-4 text-slate-600 text-sm truncate max-w-[150px]">{{ order.district }}</td>
                <td v-if="columns.total.visible" class="p-4">
                  <div class="font-bold text-emerald-600 font-mono" dir="ltr">{{ Number(order.final_total).toLocaleString() }} د.ع</div>
                  <div v-if="order.discount > 0" class="text-xs text-red-400 mt-0.5 font-mono">خصم: {{ Number(order.discount).toLocaleString() }}</div>
                </td>
                <td v-if="columns.status.visible" class="p-4 text-center">
                  <!-- Custom inline badge for different statuses -->
                  <span v-if="order.status === 'delivered'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800">{{ order.status_label }}</span>
                  <span v-else-if="order.status === 'rejected' || order.status === 'cancelled'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800">{{ order.status_label }}</span>
                  <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800">{{ order.status_label }}</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Pagination -->
        <div v-if="orders.links && orders.links.length > 3" class="p-4 border-t border-slate-100 bg-slate-50 flex items-center justify-center">
          <div class="flex flex-wrap justify-center gap-1">
            <template v-for="(link, i) in orders.links" :key="i">
              <button 
                v-if="link.url"
                @click.prevent="goToPage(link.url)"
                class="px-3 py-1 m-1 text-sm rounded-lg transition-colors border shadow-sm"
                :class="link.active ? 'bg-blue-600 text-white border-blue-600 font-bold' : 'bg-white text-slate-600 hover:bg-slate-100 border-slate-200'"
                v-html="link.label"
              ></button>
              <span 
                v-else
                class="px-3 py-1 m-1 text-sm text-slate-400 bg-slate-100 rounded-lg border border-slate-200"
                v-html="link.label"
              ></span>
            </template>
          </div>
        </div>
      </div>

    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps(['orders', 'stats', 'filters']);

const filterForm = ref({
  start_date: props.filters.start_date || '',
  end_date: props.filters.end_date || '',
});

const isLoading = ref(false);

// Dynamic Columns State
const columns = ref({
  orderNumber: { label: 'رقم الفاتورة', visible: true },
  date: { label: 'التاريخ', visible: true },
  customer: { label: 'الزبون', visible: true },
  address: { label: 'العنوان', visible: true },
  total: { label: 'المبلغ النهائي', visible: true },
  status: { label: 'الحالة', visible: true },
});

const visibleColumnsCount = computed(() => {
  return Object.values(columns.value).filter(c => c.visible).length;
});

function applyFilters() {
  isLoading.value = true;
  router.get(route('admin.inventory.index'), filterForm.value, {
    preserveState: true,
    preserveScroll: true,
    onFinish: () => isLoading.value = false
  });
}

function exportCSV() {
  // Directly hit the export endpoint, which downloads the file
  const queryString = new URLSearchParams(filterForm.value).toString();
  window.location.href = route('admin.inventory.export') + '?' + queryString;
}

function goToPage(url) {
  if (url) {
    router.visit(url, {
      preserveScroll: true,
      data: filterForm.value
    });
  }
}
</script>
