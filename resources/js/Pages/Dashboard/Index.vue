<template>
  <AdminLayout title="الرئيسية">
    <!-- ═══ Period Filter ════════════════════════════════════ -->
    <div class="flex items-center gap-2 mb-6">
      <span class="text-slate-500 text-sm">عرض:</span>
      <div class="flex bg-white border border-slate-200 rounded-lg p-1 gap-1">
        <button v-for="p in periods" :key="p.value" @click="changePeriod(p.value)"
          class="px-3 py-1.5 rounded-md text-sm font-medium transition-all"
          :class="period === p.value
            ? 'bg-primary-600 text-white shadow-sm'
            : 'text-slate-500 hover:text-slate-700'">
          {{ p.label }}
        </button>
      </div>
    </div>

    <!-- ═══ Stat Cards ════════════════════════════════════════ -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <StatCard
        label="إجمالي الطلبات"
        :value="totalOrders.toLocaleString('ar')"
        icon="orders"
        color="bg-blue-500"
        :href="route('admin.orders.index')"
      />
      <StatCard
        v-if="hasPermission('manage_financial')"
        label="الإيرادات"
        :value="formatMoney(totalRevenue)"
        icon="revenue"
        color="bg-emerald-500"
      />
      <StatCard
        v-if="hasPermission('manage_financial')"
        label="الربح الصافي"
        :value="formatMoney(totalProfit)"
        icon="profit"
        color="bg-violet-500"
      />
      <StatCard
        v-if="hasPermission('manage_users')"
        label="المستخدمون"
        :value="totalUsers.toLocaleString('ar')"
        :sub="`+${newUsersToday} اليوم`"
        icon="users"
        color="bg-orange-500"
        :href="route('admin.users.index')"
      />
    </div>

    <!-- ═══ Status Cards ═════════════════════════════════════ -->
    <div v-if="hasPermission('manage_orders')" class="grid grid-cols-2 lg:grid-cols-5 gap-3 mb-6">
      <Link v-for="(label, key) in statuses" :key="key"
        :href="route('admin.orders.index', { status: key })"
        class="card hover:shadow-md transition-all cursor-pointer text-center py-4">
        <p class="text-2xl font-bold text-slate-800">{{ (statusCounts[key] ?? 0).toLocaleString('ar') }}</p>
        <p class="text-xs text-slate-500 mt-0.5">{{ label }}</p>
        <div class="w-2 h-2 rounded-full mx-auto mt-2" :class="statusColor(key)"></div>
      </Link>
    </div>

    <!-- ═══ Charts Row ════════════════════════════════════════ -->
    <div class="grid grid-cols-1 gap-4 mb-6" :class="hasPermission('manage_financial') ? 'lg:grid-cols-3' : 'lg:grid-cols-1'">
      <!-- Revenue Chart -->
      <div v-if="hasPermission('manage_financial')" class="card lg:col-span-2">
        <div class="flex items-center justify-between mb-4">
          <h3 class="font-semibold text-slate-800">الإيرادات والأرباح</h3>
        </div>
        <Line v-if="chartData.labels.length" :data="lineChartData" :options="lineChartOptions" class="max-h-56" />
        <p v-else class="text-slate-400 text-sm text-center py-8">لا توجد بيانات</p>
      </div>

      <!-- Top Products -->
      <div class="card">
        <h3 class="font-semibold text-slate-800 mb-4">أكثر المنتجات طلباً</h3>
        <div class="space-y-3">
          <div v-for="(p, i) in topProducts" :key="i"
            class="flex items-center gap-3">
            <span class="w-6 h-6 rounded-full bg-primary-100 text-primary-700 text-xs font-bold flex items-center justify-center flex-shrink-0">
              {{ i + 1 }}
            </span>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-slate-700 truncate">{{ p.name }}</p>
              <div class="h-1.5 bg-slate-100 rounded-full mt-1">
                <div class="h-1.5 bg-primary-500 rounded-full transition-all"
                  :style="`width:${(p.total_qty / maxQty * 100).toFixed(0)}%`"></div>
              </div>
            </div>
            <span class="text-xs font-semibold text-slate-600">{{ p.total_qty }}</span>
          </div>
          <p v-if="!topProducts.length" class="text-slate-400 text-sm text-center py-4">لا توجد بيانات</p>
        </div>
      </div>
    </div>

    <!-- ═══ Latest Orders ════════════════════════════════════ -->
    <div class="card">
      <div class="flex items-center justify-between mb-4">
        <h3 class="font-semibold text-slate-800">آخر الطلبات</h3>
        <Link :href="route('admin.orders.index')" class="text-primary-600 text-sm font-medium hover:underline">
          عرض الكل
        </Link>
      </div>
      <div class="overflow-x-auto">
        <table class="data-table">
          <thead>
            <tr>
              <th>رقم الفاتورة</th>
              <th>الزبون</th>
              <th>القضاء</th>
              <th>المبلغ</th>
              <th>الحالة</th>
              <th>التاريخ</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="order in latestOrders" :key="order.id">
              <td>
                <Link :href="route('admin.orders.show', order.id)"
                  class="text-primary-600 font-medium hover:underline">
                  {{ order.invoice_number }}
                </Link>
              </td>
              <td>{{ order.user_name }}</td>
              <td>{{ order.district }}</td>
              <td>{{ formatMoney(order.total_price) }}</td>
              <td><StatusBadge :status="order.status" :label="order.status_label" /></td>
              <td class="text-slate-500 text-xs">{{ order.created_at }}</td>
            </tr>
          </tbody>
        </table>
        <p v-if="!latestOrders.length" class="text-slate-400 text-sm text-center py-6">لا توجد طلبات بعد</p>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { Line } from 'vue-chartjs';
import {
  Chart as ChartJS, CategoryScale, LinearScale, PointElement,
  LineElement, Title, Tooltip, Legend, Filler
} from 'chart.js';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const page = usePage();
const authAdmin = computed(() => page.props.auth?.admin);

function hasPermission(permission) {
  if (!authAdmin.value) return false;
  if (!authAdmin.value.role_id) return true;
  const role = authAdmin.value.role;
  return role && role.permissions && role.permissions.includes(permission);
}
import StatCard    from '@/Components/StatCard.vue';
import StatusBadge from '@/Components/StatusBadge.vue';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler);

const props = defineProps({
  totalOrders: Number, totalRevenue: Number, totalProfit: Number,
  totalUsers: Number, newUsersToday: Number, pendingOrders: Number,
  statusCounts: Object, chartData: Object, topProducts: Array,
  topDistricts: Array, latestOrders: Array, period: String,
  statuses: Object,
});

const periods = [
  { value: 'daily', label: 'اليوم' },
  { value: 'weekly', label: 'الأسبوع' },
  { value: 'monthly', label: 'الشهر' },
];

const maxQty = computed(() => Math.max(...(props.topProducts.map(p => p.total_qty) ?? [1])));

function formatMoney(val) {
  return Number(val ?? 0).toLocaleString('ar-IQ') + ' د.ع';
}

function changePeriod(p) {
  router.get(route('admin.dashboard'), { period: p }, { preserveState: true });
}

function statusColor(key) {
  const map = {
    pending: 'bg-amber-400', received: 'bg-blue-400',
    preparing: 'bg-purple-400', delivering: 'bg-cyan-400', delivered: 'bg-emerald-400',
    rejected: 'bg-rose-500',
  };
  return map[key] ?? 'bg-slate-300';
}

const lineChartData = computed(() => ({
  labels: props.chartData?.labels ?? [],
  datasets: [
    {
      label: 'الإيرادات',
      data: props.chartData?.revenue ?? [],
      borderColor: '#3b82f6',
      backgroundColor: 'rgba(59,130,246,0.1)',
      fill: true, tension: 0.4, pointRadius: 3,
    },
    {
      label: 'الأرباح',
      data: props.chartData?.profit ?? [],
      borderColor: '#10b981',
      backgroundColor: 'rgba(16,185,129,0.08)',
      fill: true, tension: 0.4, pointRadius: 3,
    },
  ],
}));

const lineChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { position: 'top', rtl: true } },
  scales: {
    x: { grid: { display: false } },
    y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
  },
};
</script>
